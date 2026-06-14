<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Domain\DebateOptions;
use App\Repositories\DebateRepository;
use App\Services\DebateService;
use App\Services\LlmException;
use App\Support\Csrf;
use App\Support\View;

final class DebateController
{
    private DebateRepository $repo;

    public function __construct()
    {
        $this->repo = new DebateRepository();
    }

    /**
     * POST /debates — 설정 검증 → 세션 생성(+AI 인사) → 시뮬레이션으로 이동.
     */
    public function create(): string
    {
        if (!Csrf::check($_POST['_csrf'] ?? null)) {
            http_response_code(400);

            return (new SetupController())->index(['_general' => '세션이 만료되었습니다. 다시 시도해 주세요.'], []);
        }

        $input = [
            'topic'           => trim((string) ($_POST['topic'] ?? '')),
            'user_stance'     => trim((string) ($_POST['user_stance'] ?? '')),
            'debate_style'    => trim((string) ($_POST['debate_style'] ?? 'logical')),
            'output_language' => trim((string) ($_POST['output_language'] ?? 'ko')),
        ];

        $errors = $this->validateSetup($input);
        if ($errors !== []) {
            http_response_code(422);

            return (new SetupController())->index($errors, $input);
        }

        $id = DebateService::make()->startSession($input);

        header('Location: /simulation?id=' . $id, true, 303);

        return '';
    }

    /**
     * GET /simulation?id=N — 채팅형 토론 화면.
     */
    public function simulation(): string
    {
        $id     = (int) ($_GET['id'] ?? 0);
        $debate = $id > 0 ? $this->repo->findDebate($id) : null;

        if ($debate === null) {
            http_response_code(404);

            return View::render('errors/404', ['path' => '/simulation']);
        }

        return View::render('simulation', [
            'title'      => '토론 진행 중 — ArguMentor',
            'fullHeight' => true,
            'debate'     => $debate,
            'messages'   => $this->repo->getMessages($id),
            'csrf'       => Csrf::token(),
        ]);
    }

    /**
     * POST /messages — (AJAX) 사용자 발화 → AI 반박. JSON 반환.
     */
    public function message(): string
    {
        header('Content-Type: application/json; charset=utf-8');

        if (!Csrf::check($_POST['_csrf'] ?? null)) {
            http_response_code(400);

            return (string) json_encode(['ok' => false, 'error' => '세션이 만료되었습니다.']);
        }

        $id     = (int) ($_POST['debate_id'] ?? 0);
        $text   = trim((string) ($_POST['message'] ?? ''));
        $debate = $id > 0 ? $this->repo->findDebate($id) : null;

        if ($debate === null) {
            http_response_code(404);

            return (string) json_encode(['ok' => false, 'error' => '토론을 찾을 수 없습니다.']);
        }
        if ($text === '') {
            http_response_code(422);

            return (string) json_encode(['ok' => false, 'error' => '메시지를 입력해 주세요.']);
        }

        set_time_limit(180); // thinking 모델 응답 대기

        try {
            $aiText = DebateService::make()->reply($debate, $text);
        } catch (LlmException $e) {
            http_response_code(502);

            return (string) json_encode(['ok' => false, 'error' => $e->getMessage()]);
        }

        return (string) json_encode(['ok' => true, 'content' => $aiText], JSON_UNESCAPED_UNICODE);
    }

    /**
     * POST /analyze — 전체 대화 분석 → 대시보드로 이동.
     */
    public function analyze(): string
    {
        if (!Csrf::check($_POST['_csrf'] ?? null)) {
            http_response_code(400);

            return View::render('errors/404', ['path' => '/analyze']);
        }

        $id     = (int) ($_POST['debate_id'] ?? 0);
        $debate = $id > 0 ? $this->repo->findDebate($id) : null;

        if ($debate === null) {
            http_response_code(404);

            return View::render('errors/404', ['path' => '/analyze']);
        }

        set_time_limit(180);

        try {
            DebateService::make()->analyze($debate);
        } catch (LlmException $e) {
            http_response_code(502);

            return View::render('simulation', [
                'title'      => '토론 진행 중 — ArguMentor',
                'fullHeight' => true,
                'debate'     => $debate,
                'messages'   => $this->repo->getMessages($id),
                'csrf'       => Csrf::token(),
                'error'      => '분석 생성 중 오류: ' . $e->getMessage(),
            ]);
        }

        header('Location: /analysis?id=' . $id, true, 303);

        return '';
    }

    /**
     * GET /analysis?id=N — 분석 대시보드.
     */
    public function show(): string
    {
        $id     = (int) ($_GET['id'] ?? 0);
        $debate = $id > 0 ? $this->repo->findDebate($id) : null;

        if ($debate === null) {
            http_response_code(404);

            return View::render('errors/404', ['path' => '/analysis']);
        }

        return View::render('analysis', [
            'title'  => '분석 대시보드 — ArguMentor',
            'debate' => $debate,
            'result' => $this->repo->findLatestResult($id),
        ]);
    }

    /**
     * @param array{topic: string, user_stance: string, debate_style: string, output_language: string} $input
     *
     * @return array<string, string>
     */
    private function validateSetup(array $input): array
    {
        $errors = [];

        $topicLen = mb_strlen($input['topic']);
        if ($topicLen < 5) {
            $errors['topic'] = '토론 주제를 5자 이상 입력해 주세요.';
        } elseif ($topicLen > 500) {
            $errors['topic'] = '토론 주제는 500자 이하로 입력해 주세요.';
        }

        if (!DebateOptions::isValidStance($input['user_stance'])) {
            $errors['user_stance'] = '입장을 선택해 주세요.';
        }
        if (!DebateOptions::isValidStyle($input['debate_style'])) {
            $errors['debate_style'] = '올바른 토론 스타일을 선택해 주세요.';
        }
        if (!DebateOptions::isValidLanguage($input['output_language'])) {
            $errors['output_language'] = '올바른 언어를 선택해 주세요.';
        }

        return $errors;
    }
}
