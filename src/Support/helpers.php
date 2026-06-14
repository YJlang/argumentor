<?php

declare(strict_types=1);

if (!function_exists('e')) {
    /**
     * 동적 출력 이스케이프 (XSS 방지). 모든 템플릿 내 변수 출력에 사용.
     */
    function e(?string $value): string
    {
        return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
    }
}
