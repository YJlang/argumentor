<?php

declare(strict_types=1);

namespace App\Support;

/**
 * 세션 기반 CSRF 토큰. POST 폼에 hidden 필드로 포함하고 서버에서 검증한다.
 */
final class Csrf
{
    public static function token(): string
    {
        if (empty($_SESSION['_csrf'])) {
            $_SESSION['_csrf'] = bin2hex(random_bytes(32));
        }

        return (string) $_SESSION['_csrf'];
    }

    public static function check(?string $token): bool
    {
        return is_string($token)
            && $token !== ''
            && !empty($_SESSION['_csrf'])
            && hash_equals((string) $_SESSION['_csrf'], $token);
    }
}
