<?php

declare(strict_types=1);

namespace App\Services;

use App\Support\Env;

/**
 * DeepSeek Chat Completions 클라이언트 (OpenAI 호환 API).
 *
 * deepseek-v4-pro는 thinking 모델이라 reasoning_content를 별도로 반환하며,
 * max_tokens가 작으면 추론에 모두 소모되어 content가 빌 수 있다 → 넉넉히 줄 것.
 */
final class DeepSeekClient
{
    public function __construct(
        private readonly string $apiKey,
        private readonly string $baseUrl,
        private readonly string $model,
        private readonly int $timeout = 120,
        private readonly int $maxRetries = 2,
    ) {
    }

    public static function fromEnv(): self
    {
        $apiKey = (string) Env::get('DEEPSEEK_API_KEY', '');
        if ($apiKey === '') {
            throw new LlmException('DEEPSEEK_API_KEY가 설정되지 않았습니다 (.env 확인).');
        }

        return new self(
            apiKey: $apiKey,
            baseUrl: rtrim((string) Env::get('DEEPSEEK_BASE_URL', 'https://api.deepseek.com'), '/'),
            model: (string) Env::get('DEEPSEEK_MODEL', 'deepseek-v4-pro'),
        );
    }

    /**
     * @param array<int, array{role: string, content: string}> $messages
     * @param array{json?: bool, max_tokens?: int, temperature?: float} $options
     *
     * @return array{content: string, reasoning: ?string, model: string, usage: array<string, mixed>}
     */
    public function chat(array $messages, array $options = []): array
    {
        $payload = [
            'model'       => $this->model,
            'messages'    => $messages,
            'stream'      => false,
            'max_tokens'  => $options['max_tokens'] ?? 8000,
            'temperature' => $options['temperature'] ?? 0.7,
        ];

        if (($options['json'] ?? false) === true) {
            $payload['response_format'] = ['type' => 'json_object'];
        }

        $response = $this->request('/chat/completions', $payload);

        $choice = $response['choices'][0]['message'] ?? null;
        if (!is_array($choice) || !isset($choice['content'])) {
            throw new LlmException('LLM 응답 형식이 올바르지 않습니다.');
        }

        return [
            'content'   => (string) $choice['content'],
            'reasoning' => isset($choice['reasoning_content']) ? (string) $choice['reasoning_content'] : null,
            'model'     => (string) ($response['model'] ?? $this->model),
            'usage'     => is_array($response['usage'] ?? null) ? $response['usage'] : [],
        ];
    }

    /**
     * @param array<string, mixed> $payload
     *
     * @return array<string, mixed>
     */
    private function request(string $path, array $payload): array
    {
        $url  = $this->baseUrl . $path;
        $body = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);

        $attempt   = 0;
        $lastError = '';

        while ($attempt <= $this->maxRetries) {
            $attempt++;

            $ch = curl_init($url);
            curl_setopt_array($ch, [
                CURLOPT_POST           => true,
                CURLOPT_POSTFIELDS     => $body,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT        => $this->timeout,
                CURLOPT_HTTPHEADER     => [
                    'Content-Type: application/json',
                    'Authorization: Bearer ' . $this->apiKey,
                ],
            ]);

            $raw     = curl_exec($ch);
            $status  = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
            $curlErr = curl_error($ch);
            unset($ch); // PHP 8.0+에서 curl_close()는 no-op (8.5 deprecated)

            // 네트워크 오류 또는 5xx → 재시도
            if ($raw === false || $status >= 500) {
                $lastError = $raw === false ? "cURL: {$curlErr}" : "HTTP {$status}";
                continue;
            }

            if ($status >= 400) {
                throw new LlmException("DeepSeek API 오류 (HTTP {$status}): " . (string) $raw);
            }

            try {
                /** @var array<string, mixed> $decoded */
                $decoded = json_decode((string) $raw, true, 512, JSON_THROW_ON_ERROR);
            } catch (\JsonException $e) {
                throw new LlmException('DeepSeek 응답 JSON 파싱 실패: ' . $e->getMessage());
            }

            return $decoded;
        }

        throw new LlmException("DeepSeek API 호출 실패 ({$this->maxRetries}회 재시도): {$lastError}");
    }
}
