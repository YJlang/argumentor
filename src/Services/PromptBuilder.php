<?php

declare(strict_types=1);

namespace App\Services;

use App\Domain\DebateOptions;

/**
 * 토론 시뮬레이션(대화형) + 분석(JSON)용 프롬프트 생성기.
 *
 * 보안: 사용자 입력(topic/주장)은 system 프롬프트에 보간하지 않고 user 메시지로만 전달한다
 * (prompt injection 방지). system에는 통제된 ENUM 값(스타일/언어/입장)만 보간한다.
 */
final class PromptBuilder
{
    private const STYLE_TONE = [
        'logical'    => '논리적이고 체계적인',
        'aggressive' => '날카롭고 공격적인',
        'academic'   => '학술적이고 근거 중심의',
    ];

    /**
     * 채팅형 실시간 토론용 system 프롬프트. AI는 사용자와 반대 입장에서 대화로 반박한다.
     */
    public function simulationSystem(string $topic, string $stance, string $style, string $language): string
    {
        $languageLabel = DebateOptions::LANGUAGES[$language] ?? '한국어';
        $tone          = self::STYLE_TONE[$style] ?? self::STYLE_TONE['logical'];
        $opposing      = DebateOptions::opposingStanceLabel($stance);
        $topicSafe     = $this->sanitize($topic);

        return <<<SYS
            당신은 숙련된 토론면접관입니다. 주제 "{$topicSafe}"에 대해 사용자와 **반대 입장**({$opposing})에서
            {$tone} 태도로 실시간 토론을 진행합니다.

            진행 규칙:
            - 사용자의 직전 발화에서 논리적 허점(인과/상관 혼동, 성급한 일반화, 근거 부족, 반례 미고려 등)을 한두 개 짚어 반박합니다.
            - 반박은 2~4문장으로 간결하게. 장황하게 늘어놓지 마세요.
            - 매 답변 끝에 사용자가 답하기 어려운 날카로운 후속 질문 1개를 "🔍 질문: ..." 형식으로 덧붙입니다.
            - 메타발언(예: "JSON으로 답하겠습니다") 없이, 토론 상대로서 자연스러운 대화체로만 답합니다.
            - 반드시 "{$languageLabel}"로 답합니다.
            SYS;
    }

    /**
     * AI의 첫 인사 메시지 (LLM 호출 없이 결정적으로 생성).
     */
    public function openingMessage(string $topic, string $stance, string $style): string
    {
        $opposing   = DebateOptions::opposingStanceLabel($stance);
        $styleLabel = DebateOptions::STYLES[$style] ?? '논리적';

        return "안녕하세요. 저는 \"{$topic}\"에 대해 {$opposing} 입장에서 토론하겠습니다.\n\n"
            . "{$styleLabel} 스타일로 진행합니다. 먼저 당신의 주장을 들려주세요.";
    }

    /**
     * 대화 전체를 분석하여 JSON으로 반환받기 위한 messages.
     *
     * @param array<int, array{role: string, content: string}> $history
     *
     * @return array<int, array{role: string, content: string}>
     */
    public function analysisMessages(
        string $topic,
        string $stance,
        string $style,
        string $language,
        array $history,
    ): array {
        $languageLabel = DebateOptions::LANGUAGES[$language] ?? '한국어';

        $system = <<<SYS
            당신은 토론 논리 분석 전문가입니다. 아래는 한 사용자와 AI 면접관의 토론 대화입니다.
            **사용자(USER)의 발화**에 담긴 논증을 객관적으로 분석하세요.

            반드시 아래 JSON 스키마에 정확히 맞는 **유효한 JSON 객체 하나만** 출력합니다. 그 외 텍스트/마크다운/코드펜스는 금지합니다.
            모든 문자열 값(라벨 포함)은 "{$languageLabel}"로 작성합니다.

            {
              "counter_argument": "사용자 주장에 대한 핵심 반대 논리 요약 (3~5문장)",
              "challenging_questions": ["사용자가 보완해야 할 날카로운 질문", "..."],
              "logic_analysis": {
                "structure": [
                  {"label": "주장", "content": "사용자 주장의 핵심"},
                  {"label": "근거", "content": "사용자가 제시한 근거"},
                  {"label": "결론", "content": "도출된 결론"}
                ],
                "scores": [
                  {"label": "논리적 일관성", "value": 0-100 정수},
                  {"label": "근거의 타당성", "value": 0-100 정수},
                  {"label": "결론의 설득력", "value": 0-100 정수},
                  {"label": "반박 대비력", "value": 0-100 정수}
                ]
              },
              "weakness_analysis": [
                {"type": "약점 유형(예: 논리적 오류)", "items": ["구체적 약점 설명", "..."]}
              ],
              "rebuttal_summary": [
                {"point": "핵심 반박 포인트", "detail": "상세 설명", "severity": "high|medium|low"}
              ],
              "improvement_strategy": [
                {"title": "개선 전략 제목", "items": ["실행 가능한 조언", "..."]}
              ]
            }

            challenging_questions 2~4개, weakness_analysis 2~3개, rebuttal_summary 3~4개, improvement_strategy 3개를 권장합니다.
            SYS;

        $transcript = $this->renderTranscript($topic, $stance, $history);

        return [
            ['role' => 'system', 'content' => $system],
            ['role' => 'user', 'content' => $transcript],
        ];
    }

    /**
     * (성결대 모드) 조별 토론 — AI가 여러 수험생을 연기해 한 라운드를 JSON 배열로 반환.
     *
     * @param array<int, array{name: string, stance: string}>           $panel   AI 조원 명단(이름+입장)
     * @param array<int, array{role: string, speaker: ?string, content: string}> $history
     *
     * @return array<int, array{role: string, content: string}>
     */
    public function examPanelMessages(string $topic, string $language, array $panel, array $history): array
    {
        $languageLabel = DebateOptions::LANGUAGES[$language] ?? '한국어';
        $topicSafe     = $this->sanitize($topic);

        $roster = [];
        foreach ($panel as $p) {
            $stanceLabel = DebateOptions::STANCES[$p['stance']] ?? $p['stance'];
            $roster[]    = "- {$p['name']} (입장: {$stanceLabel})";
        }
        $rosterText = implode("\n", $roster);
        $count      = count($panel);

        $system = <<<SYS
            여기는 성결대학교 SKU창의적인재전형 **조별 토론면접** 현장입니다. 주제: "{$topicSafe}".
            당신은 사용자(USER) 외의 **다른 수험생 {$count}명**을 동시에 연기합니다. 명단과 각자의 입장:
            {$rosterText}

            규칙:
            - 사용자의 직전 발언에 반응하여, 각 수험생이 자신의 입장에서 **순서대로 1회씩** 발언합니다.
            - 각 발언은 고등학생 수준의 시사·상식에 기반해 2~3문장으로 간결하게. 다른 수험생 발언도 받아 자연스럽게 이어갑니다.
            - 때로 사용자에게 반론·질문을 던져 토론을 활성화합니다.
            - 인적사항(이름·학교 등 실제 신상) 언급 금지. 차분하고 예의 있는 토론 태도.
            - 반드시 "{$languageLabel}"로.

            아래 JSON만 출력합니다(코드펜스·여타 텍스트 금지):
            {"turns": [{"speaker": "수험생 이름", "content": "발언"}, ...]}
            turns는 명단 순서대로 정확히 {$count}개여야 합니다.
            SYS;

        $transcript = $this->renderGroupTranscript($topicSafe, $history);

        return [
            ['role' => 'system', 'content' => $system],
            ['role' => 'user', 'content' => $transcript],
        ];
    }

    /**
     * (성결대 모드) 사용자 발화를 공식 4항목으로 채점.
     *
     * @param array<int, array{role: string, speaker: ?string, content: string}> $history
     *
     * @return array<int, array{role: string, content: string}>
     */
    public function examAnalysisMessages(string $topic, string $language, array $history): array
    {
        $languageLabel = DebateOptions::LANGUAGES[$language] ?? '한국어';

        $system = <<<SYS
            당신은 성결대학교 SKU창의적인재전형 **조별 토론면접 평가위원**입니다. 아래 조별 토론에서
            **사용자(USER)의 발화**만을 대상으로, 성결대 공식 평가 4항목으로 채점하고 피드백합니다.

            반드시 아래 JSON 객체 하나만 출력합니다(코드펜스·여타 텍스트 금지). 모든 문자열은 "{$languageLabel}"로.
            {
              "counter_argument": "사용자 주장에 대한 평가위원의 총평(3~5문장)",
              "challenging_questions": ["면접관이 추가로 물을 법한 날카로운 질문", "..."],
              "logic_analysis": {
                "structure": [
                  {"label": "주장", "content": "..."},
                  {"label": "근거", "content": "..."},
                  {"label": "결론", "content": "..."}
                ],
                "scores": [
                  {"label": "주제의 이해력", "value": 0-100 정수},
                  {"label": "주장의 논리력", "value": 0-100 정수},
                  {"label": "언어구사능력", "value": 0-100 정수},
                  {"label": "성실성(태도)", "value": 0-100 정수}
                ]
              },
              "weakness_analysis": [
                {"type": "약점 유형", "items": ["구체적 약점", "..."]}
              ],
              "rebuttal_summary": [
                {"point": "토론에서 드러난 핵심 약점", "detail": "설명", "severity": "high|medium|low"}
              ],
              "improvement_strategy": [
                {"title": "성결대 토론면접 대비 전략", "items": ["실행 가능한 조언", "..."]}
              ]
            }

            scores의 4개 라벨은 위 4항목을 그대로 사용합니다. 개선 전략은 성결대 조별 토론면접(경청·협력·시간 내 명료한 발언 등) 맥락으로 작성합니다.
            SYS;

        $transcript = $this->renderGroupTranscript($this->sanitize($topic), $history);

        return [
            ['role' => 'system', 'content' => $system],
            ['role' => 'user', 'content' => $transcript],
        ];
    }

    /**
     * @param array<int, array{role: string, speaker: ?string, content: string}> $history
     */
    private function renderGroupTranscript(string $topic, array $history): string
    {
        $lines = ["[주제] {$topic}", '', '[조별 토론 내용]'];
        foreach ($history as $m) {
            $who = $m['role'] === 'user' ? 'USER' : ($m['speaker'] ?? 'AI');
            $lines[] = "{$who}: " . $m['content'];
        }

        return implode("\n", $lines);
    }

    /**
     * @param array<int, array{role: string, content: string}> $history
     */
    private function renderTranscript(string $topic, string $stance, array $history): string
    {
        $stanceLabel = DebateOptions::STANCES[$stance] ?? $stance;
        $lines       = ["[주제] {$this->sanitize($topic)}", "[사용자 입장] {$stanceLabel}", '', '[대화 내용]'];

        foreach ($history as $m) {
            $who = ($m['role'] === 'user') ? 'USER' : 'AI';
            $lines[] = "{$who}: " . $m['content'];
        }

        return implode("\n", $lines);
    }

    /**
     * 프롬프트 구조를 깨뜨릴 수 있는 제어 토큰 제거(가벼운 방어).
     */
    private function sanitize(string $text): string
    {
        return trim(str_replace(['```', "\r"], '', $text));
    }
}
