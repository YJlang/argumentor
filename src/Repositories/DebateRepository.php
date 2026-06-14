<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Support\Database;

/**
 * debates / debate_messages / debate_results 영속성. PDO prepared statement만 사용.
 */
final class DebateRepository
{
    /**
     * @param array{topic: string, user_stance: string, debate_style: string, output_language: string, mode?: string, memo?: ?string} $input
     */
    public function createDebate(array $input): int
    {
        $sql = 'INSERT INTO debates (mode, topic, user_stance, debate_style, output_language, memo)
                VALUES (:mode, :topic, :user_stance, :debate_style, :output_language, :memo)';
        $stmt = Database::connection()->prepare($sql);
        $stmt->execute([
            ':mode'            => $input['mode'] ?? 'free',
            ':topic'           => $input['topic'],
            ':user_stance'     => $input['user_stance'],
            ':debate_style'    => $input['debate_style'],
            ':output_language' => $input['output_language'],
            ':memo'            => $input['memo'] ?? null,
        ]);

        return (int) Database::connection()->lastInsertId();
    }

    public function updateMemo(int $debateId, string $memo): void
    {
        $stmt = Database::connection()->prepare('UPDATE debates SET memo = :memo WHERE id = :id');
        $stmt->execute([':memo' => $memo, ':id' => $debateId]);
    }

    public function addMessage(int $debateId, string $role, string $content, ?string $speaker = null): int
    {
        $stmt = Database::connection()->prepare(
            'INSERT INTO debate_messages (debate_id, role, speaker, content)
             VALUES (:debate_id, :role, :speaker, :content)'
        );
        $stmt->execute([
            ':debate_id' => $debateId,
            ':role'      => $role,
            ':speaker'   => $speaker,
            ':content'   => $content,
        ]);

        return (int) Database::connection()->lastInsertId();
    }

    /**
     * @return array<int, array{id: int, role: string, speaker: ?string, content: string, created_at: string}>
     */
    public function getMessages(int $debateId): array
    {
        $stmt = Database::connection()->prepare(
            'SELECT id, role, speaker, content, created_at FROM debate_messages
             WHERE debate_id = :id ORDER BY id ASC'
        );
        $stmt->execute([':id' => $debateId]);

        /** @var array<int, array{id: int, role: string, speaker: ?string, content: string, created_at: string}> $rows */
        $rows = $stmt->fetchAll();

        return $rows;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function findDebate(int $id): ?array
    {
        $stmt = Database::connection()->prepare('SELECT * FROM debates WHERE id = :id');
        $stmt->execute([':id' => $id]);
        $debate = $stmt->fetch();

        return $debate === false ? null : $debate;
    }

    /**
     * @return array<string, mixed>|null 최신 분석 결과 (JSON 컬럼 디코드됨)
     */
    public function findLatestResult(int $debateId): ?array
    {
        $stmt = Database::connection()->prepare(
            'SELECT * FROM debate_results WHERE debate_id = :id ORDER BY round_no DESC, id DESC LIMIT 1'
        );
        $stmt->execute([':id' => $debateId]);
        $result = $stmt->fetch();

        if (!is_array($result)) {
            return null;
        }

        foreach (
            ['ai_challenging_questions', 'logic_analysis', 'weakness_analysis',
                'rebuttal_summary', 'improvement_strategy'] as $col
        ) {
            $result[$col] = is_string($result[$col] ?? null)
                ? json_decode((string) $result[$col], true)
                : null;
        }

        return $result;
    }

    /**
     * @param array{
     *   ai_counter_argument: ?string, ai_challenging_questions: mixed, logic_analysis: mixed,
     *   weakness_analysis: mixed, rebuttal_summary: mixed, improvement_strategy: mixed,
     *   model: ?string, prompt_tokens: ?int, completion_tokens: ?int, reasoning_tokens: ?int,
     *   raw_response: ?string
     * } $result
     */
    public function saveResult(int $debateId, array $result): int
    {
        $json = static fn (mixed $v): ?string => $v === null
            ? null
            : (string) json_encode($v, JSON_UNESCAPED_UNICODE);

        $sql = 'INSERT INTO debate_results
                (debate_id, ai_counter_argument, ai_challenging_questions, logic_analysis,
                 weakness_analysis, rebuttal_summary, improvement_strategy,
                 model, prompt_tokens, completion_tokens, reasoning_tokens, raw_response)
                VALUES
                (:debate_id, :ai_counter_argument, :ai_challenging_questions, :logic_analysis,
                 :weakness_analysis, :rebuttal_summary, :improvement_strategy,
                 :model, :prompt_tokens, :completion_tokens, :reasoning_tokens, :raw_response)';

        $stmt = Database::connection()->prepare($sql);
        $stmt->execute([
            ':debate_id'                => $debateId,
            ':ai_counter_argument'      => $result['ai_counter_argument'],
            ':ai_challenging_questions' => $json($result['ai_challenging_questions']),
            ':logic_analysis'           => $json($result['logic_analysis']),
            ':weakness_analysis'        => $json($result['weakness_analysis']),
            ':rebuttal_summary'         => $json($result['rebuttal_summary']),
            ':improvement_strategy'     => $json($result['improvement_strategy']),
            ':model'                    => $result['model'],
            ':prompt_tokens'            => $result['prompt_tokens'],
            ':completion_tokens'        => $result['completion_tokens'],
            ':reasoning_tokens'         => $result['reasoning_tokens'],
            ':raw_response'             => $result['raw_response'],
        ]);

        return (int) Database::connection()->lastInsertId();
    }
}
