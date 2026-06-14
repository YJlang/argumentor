<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\DebateRepository;

/**
 * 토론 오케스트레이션:
 *  - startSession: 세션 생성 + AI 첫 인사
 *  - reply:        사용자 발화 저장 → 대화 맥락으로 LLM 반박 → AI 발화 저장
 *  - analyze:      전체 대화를 분석(JSON)하여 결과 저장
 */
final class DebateService
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
     * @param array{topic: string, user_stance: string, debate_style: string, output_language: string} $input
     */
    public function startSession(array $input): int
    {
        $debateId = $this->repo->createDebate($input);

        $opening = $this->prompt->openingMessage(
            $input['topic'],
            $input['user_stance'],
            $input['debate_style'],
        );
        $this->repo->addMessage($debateId, 'ai', $opening);

        return $debateId;
    }

    /**
     * 사용자 발화에 대한 AI 반박 한 턴.
     *
     * @param array<string, mixed> $debate debates 행
     *
     * @return string AI 반박 텍스트
     */
    public function reply(array $debate, string $userText): string
    {
        $debateId = (int) $debate['id'];

        $this->repo->addMessage($debateId, 'user', $userText);

        $system   = $this->prompt->simulationSystem(
            (string) $debate['topic'],
            (string) $debate['user_stance'],
            (string) $debate['debate_style'],
            (string) $debate['output_language'],
        );
        $messages = [['role' => 'system', 'content' => $system]];
        foreach ($this->repo->getMessages($debateId) as $m) {
            $messages[] = [
                'role'    => $m['role'] === 'user' ? 'user' : 'assistant',
                'content' => $m['content'],
            ];
        }

        $response = $this->llm->chat($messages, [
            'max_tokens'  => 2000,
            'temperature' => 0.7,
        ]);

        $aiText = trim($response['content']);
        if ($aiText === '') {
            throw new LlmException('AI가 빈 응답을 반환했습니다. 다시 시도해 주세요.');
        }

        $this->repo->addMessage($debateId, 'ai', $aiText);

        return $aiText;
    }

    /**
     * 전체 대화를 분석하여 debate_results에 저장.
     *
     * @param array<string, mixed> $debate debates 행
     */
    public function analyze(array $debate): int
    {
        $debateId = (int) $debate['id'];

        $history  = array_map(
            static fn (array $m): array => ['role' => $m['role'], 'content' => $m['content']],
            $this->repo->getMessages($debateId),
        );

        $messages = $this->prompt->analysisMessages(
            (string) $debate['topic'],
            (string) $debate['user_stance'],
            (string) $debate['debate_style'],
            (string) $debate['output_language'],
            $history,
        );

        $response = $this->llm->chat($messages, [
            'json'        => true,
            'max_tokens'  => 8000,
            'temperature' => 0.4,
        ]);

        $parsed = $this->parseContent($response['content']);
        $usage  = $response['usage'];

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
     * @return array<string, mixed>
     */
    private function parseContent(string $content): array
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
            throw new LlmException('LLM 분석 응답을 JSON으로 파싱하지 못했습니다.');
        }

        return $decoded;
    }
}
