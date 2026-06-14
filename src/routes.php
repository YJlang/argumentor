<?php

declare(strict_types=1);

use App\Controllers\DebateController;
use App\Controllers\ExamController;
use App\Controllers\HomeController;
use App\Controllers\SetupController;
use App\Support\Router;

/** @var Router $router */

$router->get('/', static fn (): string => (new HomeController())->index());
$router->get('/setup', static fn (): string => (new SetupController())->index());

// 설정 제출 → 세션 생성 → 시뮬레이션
$router->post('/debates', static fn (): string => (new DebateController())->create());
// 채팅형 토론 화면: /simulation?id=123
$router->get('/simulation', static fn (): string => (new DebateController())->simulation());
// (AJAX) 사용자 발화 → AI 반박
$router->post('/messages', static fn (): string => (new DebateController())->message());
// 전체 대화 분석 → 대시보드
$router->post('/analyze', static fn (): string => (new DebateController())->analyze());
// 분석 대시보드: /analysis?id=123 (두 모드 공용)
$router->get('/analysis', static fn (): string => (new DebateController())->show());

// === 실전 성결대 토론면접 모드 ===
$router->get('/exam', static fn (): string => (new ExamController())->setup());
$router->post('/exam', static fn (): string => (new ExamController())->create());
$router->get('/exam/prep', static fn (): string => (new ExamController())->prep());
$router->post('/exam/start', static fn (): string => (new ExamController())->startDebate());
$router->get('/exam/room', static fn (): string => (new ExamController())->room());
$router->post('/exam/messages', static fn (): string => (new ExamController())->message());
$router->post('/exam/analyze', static fn (): string => (new ExamController())->analyze());

// 추후: GET /history (기록)
