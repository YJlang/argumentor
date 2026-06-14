<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Domain\DebateOptions;
use App\Support\Csrf;
use App\Support\View;

final class SetupController
{
    /**
     * 토론 설정 폼 (PLAN.md §6.2).
     *
     * @param array<string, string> $errors 필드별 오류 메시지
     * @param array<string, string> $old    재표시용 이전 입력값
     */
    public function index(array $errors = [], array $old = []): string
    {
        return View::render('setup', [
            'title'     => '토론 설정 — ArguMentor',
            'stances'   => DebateOptions::STANCES,
            'styles'    => DebateOptions::STYLES,
            'languages' => DebateOptions::LANGUAGES,
            'errors'    => $errors,
            'old'       => $old,
            'csrf'      => Csrf::token(),
        ]);
    }
}
