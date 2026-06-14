<?php

declare(strict_types=1);

namespace App\Support;

use RuntimeException;

/**
 * 간단한 PHP 템플릿 렌더러. 비즈니스 로직은 두지 말 것 (표현만 담당).
 */
final class View
{
    private const TEMPLATE_DIR = __DIR__ . '/../../templates';

    /**
     * 템플릿을 레이아웃으로 감싸 렌더링한다.
     *
     * @param array<string, mixed> $data
     */
    public static function render(string $template, array $data = [], ?string $layout = 'layout'): string
    {
        $content = self::renderPartial($template, $data);

        if ($layout === null) {
            return $content;
        }

        return self::renderPartial($layout, [...$data, 'content' => $content]);
    }

    /**
     * 레이아웃 없이 단일 파셜만 렌더링한다.
     *
     * @param array<string, mixed> $data
     */
    public static function renderPartial(string $template, array $data = []): string
    {
        $file = self::TEMPLATE_DIR . '/' . $template . '.php';

        if (!is_file($file)) {
            throw new RuntimeException("View not found: {$template}");
        }

        extract($data, EXTR_SKIP);
        ob_start();
        require $file;

        return (string) ob_get_clean();
    }
}
