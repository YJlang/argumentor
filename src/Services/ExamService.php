<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\DebateRepository;

/**
 * 성결대 SKU 조별 토론면접 모드 오케스트레이션.
 *  - start:       세션 생성(mode=sungkyul) + 사회자 시작 멘트
 *  - saveMemo:    준비시간 A4 메모 저장
 *  - panelReply:  사용자 발화 → AI 조원 2명이 한 라운드 발언(JSON) → 저장 + 반환
 *  - analyze:     전체 조별 토론을 성결대 공식 4항목으로 채점
 *
 * 조 구성: 사용자 1명 + AI 조원 2명 = 3명(성결대 "3~4명 한 조" 범위).
 */
final class ExamService
{
    public function __construct(
        private readonly DeepSeekClient $llm,
        private readonly PromptBuilder $prompt,
        private readonly DebateRepository $repo,
    ) {
    }

    public static function make(): self
    {
        return new self(DeepSeekClient::fromEnv(), new PromptBuilder(), new DebateRepository());
    }

    /**
     * @param array{topic: string, user_stance: string, output_language: string} $input
     */
    public function start(array $input): int
    {
        $debateId = $this->repo->createDebate([
            'mode'            => 'sungkyul',
            'topic'           => $input['topic'],
            'user_stance'     => $input['user_stance'],
            'debate_style'    => 'logical',
            'output_language' => $input['output_language'],
        ]);

        $opening = "지금부터 \"{$input['topic']}\"에 대한 조별 토론을 시작하겠습니다. "
            . "제한 시간은 8분이며, 찬성과 반대 의견을 자유롭게 나눠 주세요. 먼저 의견을 말씀해 주시겠어요?";
        $this->repo->addMessage($debateId, 'ai', $opening, '사회자');

        return $debateId;
    }

    public function saveMemo(int $debateId, string $memo): void
    {
        $this->repo->updateMemo($debateId, $memo);
    }

    /**
     * @param array<string, mixed> $debate
     *
     * @return array<int, array{speaker: string, content: string}>
     */
    public function panelReply(array $debate, string $userText): array
    {
        $debateId = (int) $debate['id'];
        $this->repo->addMessage($debateId, 'user', $userText, '나');

        $panel    = $this->panel((string) $debate['user_stance']);
        $history  = array_map(
            static fn (array $m): array => ['role' => $m['role'], 'speaker' => $m['speaker'], 'content' => $m['content']],
            $this->repo->getMessages($debateId),
        );

        $messages = $this->prompt->examPanelMessages(
            (string) $debate['topic'],
            (string) $debate['output_language'],
            $panel,
            $history,
        );

        $response = $this->llm->chat($messages, ['json' => true, 'max_tokens' => 3000, 'temperature' => 0.8]);
        $parsed   = $this->parseJson($response['content']);

        $turns = is_array($parsed['turns'] ?? null) ? $parsed['turns'] : [];
        $out   = [];
        foreach ($turns as $t) {
            if (!is_array($t) || !isset($t['content'])) {
                continue;
            }
            $speaker = isset($t['speaker']) ? (string) $t['speaker'] : '수험생';
            $content = trim((string) $t['content']);
            if ($content === '') {
                continue;
            }
            $this->repo->addMessage($debateId, 'ai', $content, $speaker);
            $out[] = ['speaker' => $speaker, 'content' => $content];
        }

        if ($out === []) {
            throw new LlmException('조원 응답을 생성하지 못했습니다. 다시 시도해 주세요.');
        }

        return $out;
    }

    /**
     * @param array<string, mixed> $debate
     */
    public function analyze(array $debate): int
    {
        $debateId = (int) $debate['id'];
        $history  = array_map(
            static fn (array $m): array => ['role' => $m['role'], 'speaker' => $m['speaker'], 'content' => $m['content']],
            $this->repo->getMessages($debateId),
        );

        $messages = $this->prompt->examAnalysisMessages(
            (string) $debate['topic'],
            (string) $debate['output_language'],
            $history,
        );

        $response = $this->llm->chat($messages, ['json' => true, 'max_tokens' => 8000, 'temperature' => 0.4]);
        $parsed   = $this->parseJson($response['content']);
        $usage    = $response['usage'];

        return $this->repo->saveResult($debateId, [
            'ai_counter_argument'      => isset($parsed['counter_argument']) ? (string) $parsed['counter_argument'] : null,
            'ai_challenging_questions' => $parsed['challenging_questions'] ?? null,
            'logic_analysis'           => $parsed['logic_analysis'] ?? null,
            'weakness_analysis'        => $parsed['weakness_analysis'] ?? null,
            'rebuttal_summary'         => $parsed['rebuttal_summary'] ?? null,
            'improvement_strategy'     => $parsed['improvement_strategy'] ?? null,
            'model'                    => $response['model'],
            'prompt_tokens'            => isset($usage['prompt_tokens']) ? (int) $usage['prompt_tokens'] : null,
            'completion_tokens'        => isset($usage['completion_tokens']) ? (int) $usage['completion_tokens'] : null,
            'reasoning_tokens'         => isset($usage['completion_tokens_details']['reasoning_tokens'])
                ? (int) $usage['completion_tokens_details']['reasoning_tokens']
                : null,
            'raw_response'             => $response['content'],
        ]);
    }

    /**
     * @return array<int, array{name: string, stance: string}>
     */
    private function panel(string $userStance): array
    {
        $opposite = $userStance === 'for' ? 'against' : 'for';

        return [
            ['name' => '학생 B', 'stance' => $opposite],   // 사용자에 맞서는 반대측
            ['name' => '학생 C', 'stance' => $userStance], // 같은 입장이나 자기 논거를 더하는 조원
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function parseJson(string $content): array
    {
        $text = trim($content);
        if (str_starts_with($text, '```')) {
            $text = trim((string) preg_replace('/^```[a-zA-Z]*\s*|\s*```$/', '', $text));
        }
        $start = strpos($text, '{');
        $end   = strrpos($text, '}');
        if ($start !== false && $end !== false && $end > $start) {
            $text = substr($text, $start, $end - $start + 1);
        }

        $decoded = json_decode($text, true);
        if (!is_array($decoded)) {
            throw new LlmException('LLM 응답을 JSON으로 파싱하지 못했습니다.');
        }

        return $decoded;
    }
}
