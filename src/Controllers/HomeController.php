<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Support\View;

final class HomeController
{
    public function index(): string
    {
        return View::render('home', [
            'title' => 'ArguMentor — AI 토론면접 시뮬레이션',
        ]);
    }
}
