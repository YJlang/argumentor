<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Domain\DebateOptions;
use App\Domain\SungkyulTopics;
use App\Repositories\DebateRepository;
use App\Services\ExamService;
use App\Services\LlmException;
use App\Support\Csrf;
use App\Support\View;

/**
 * 실전 성결대 토론면접(조별 토론) 모드.
 */
final class ExamController
{
    private DebateRepository $repo;

    public function __construct()
    {
        $this->repo = new DebateRepository();
    }

    /** GET /exam — 형식 안내 + 주제 추첨/선택 */
    public function setup(): string
    {
        return View::render('exam/setup', [
            'title'     => '실전 성결대 토론면접 — ArguMentor',
            'topics'    => SungkyulTopics::TOPICS,
            'drawn'     => SungkyulTopics::draw(),
            'languages' => DebateOptions::LANGUAGES,
            'errors'    => [],
            'old'       => [],
            'csrf'      => Csrf::token(),
        ]);
    }

    /** POST /exam — 세션 생성 → 준비실 */
    public function create(): string
    {
        if (!Csrf::check($_POST['_csrf'] ?? null)) {
            http_response_code(400);

            return $this->setupWithError('세션이 만료되었습니다. 다시 시도해 주세요.');
        }

        $input = [
            'topic'           => trim((string) ($_POST['topic'] ?? '')),
            'user_stance'     => trim((string) ($_POST['user_stance'] ?? '')),
            'output_language' => trim((string) ($_POST['output_language'] ?? 'ko')),
        ];

        $errors = [];
        if (mb_strlen($input['topic']) < 5) {
            $errors['topic'] = '토론 주제를 선택하거나 입력해 주세요.';
        }
        if (!DebateOptions::isValidStance($input['user_stance'])) {
            $errors['user_stance'] = '입장을 선택해 주세요.';
        }
        if (!DebateOptions::isValidLanguage($input['output_language'])) {
            $errors['output_language'] = '올바른 언어를 선택해 주세요.';
        }

        if ($errors !== []) {
            http_response_code(422);

            return View::render('exam/setup', [
                'title'     => '실전 성결대 토론면접 — ArguMentor',
                'topics'    => SungkyulTopics::TOPICS,
                'drawn'     => SungkyulTopics::draw(),
                'languages' => DebateOptions::LANGUAGES,
                'errors'    => $errors,
                'old'       => $input,
                'csrf'      => Csrf::token(),
            ]);
        }

        $id = ExamService::make()->start($input);

        header('Location: /exam/prep?id=' . $id, true, 303);

        return '';
    }

    /** GET /exam/prep?id=N — 7분 준비 + A4 메모 */
    public function prep(): string
    {
        $debate = $this->requireExam();
        if (is_string($debate)) {
            return $debate;
        }

        return View::render('exam/prep', [
            'title'  => '준비 시간 — 실전 성결대 토론면접',
            'debate' => $debate,
            'csrf'   => Csrf::token(),
        ]);
    }

    /** POST /exam/start — 메모 저장 → 토론장 */
    public function startDebate(): string
    {
        if (!Csrf::check($_POST['_csrf'] ?? null)) {
            http_response_code(400);

            return View::render('errors/404', ['path' => '/exam/start']);
        }

        $id     = (int) ($_POST['debate_id'] ?? 0);
        $debate = $id > 0 ? $this->repo->findDebate($id) : null;
        if ($debate === null || ($debate['mode'] ?? '') !== 'sungkyul') {
            http_response_code(404);

            return View::render('errors/404', ['path' => '/exam/start']);
        }

        ExamService::make()->saveMemo($id, trim((string) ($_POST['memo'] ?? '')));

        header('Location: /exam/room?id=' . $id, true, 303);

        return '';
    }

    /** GET /exam/room?id=N — 8분 조별 토론 */
    public function room(): string
    {
        $debate = $this->requireExam();
        if (is_string($debate)) {
            return $debate;
        }

        return View::render('exam/room', [
            'title'      => '조별 토론 — 실전 성결대 토론면접',
            'fullHeight' => true,
            'debate'     => $debate,
            'messages'   => $this->repo->getMessages((int) $debate['id']),
            'csrf'       => Csrf::token(),
        ]);
    }

    /** POST /exam/messages — (AJAX) 사용자 발화 → AI 조원 라운드 */
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

        if ($debate === null || ($debate['mode'] ?? '') !== 'sungkyul') {
            http_response_code(404);

            return (string) json_encode(['ok' => false, 'error' => '토론을 찾을 수 없습니다.']);
        }
        if ($text === '') {
            http_response_code(422);

            return (string) json_encode(['ok' => false, 'error' => '발언을 입력해 주세요.']);
        }

        set_time_limit(180);

        try {
            $turns = ExamService::make()->panelReply($debate, $text);
        } catch (LlmException $e) {
            http_response_code(502);

            return (string) json_encode(['ok' => false, 'error' => $e->getMessage()]);
        }

        return (string) json_encode(['ok' => true, 'turns' => $turns], JSON_UNESCAPED_UNICODE);
    }

    /** POST /exam/analyze — 채점 → 결과 대시보드 */
    public function analyze(): string
    {
        if (!Csrf::check($_POST['_csrf'] ?? null)) {
            http_response_code(400);

            return View::render('errors/404', ['path' => '/exam/analyze']);
        }

        $id     = (int) ($_POST['debate_id'] ?? 0);
        $debate = $id > 0 ? $this->repo->findDebate($id) : null;
        if ($debate === null || ($debate['mode'] ?? '') !== 'sungkyul') {
            http_response_code(404);

            return View::render('errors/404', ['path' => '/exam/analyze']);
        }

        set_time_limit(180);

        try {
            ExamService::make()->analyze($debate);
        } catch (LlmException $e) {
            http_response_code(502);

            return View::render('exam/room', [
                'title'      => '조별 토론 — 실전 성결대 토론면접',
                'fullHeight' => true,
                'debate'     => $debate,
                'messages'   => $this->repo->getMessages($id),
                'csrf'       => Csrf::token(),
                'error'      => '채점 중 오류: ' . $e->getMessage(),
            ]);
        }

        header('Location: /analysis?id=' . $id, true, 303);

        return '';
    }

    /**
     * 공통: sungkyul 모드 debate 로드. 실패 시 렌더된 404 문자열을 반환.
     *
     * @return array<string, mixed>|string
     */
    private function requireExam(): array|string
    {
        $id     = (int) ($_GET['id'] ?? 0);
        $debate = $id > 0 ? $this->repo->findDebate($id) : null;

        if ($debate === null || ($debate['mode'] ?? '') !== 'sungkyul') {
            http_response_code(404);

            return View::render('errors/404', ['path' => '/exam']);
        }

        return $debate;
    }

    private function setupWithError(string $message): string
    {
        return View::render('exam/setup', [
            'title'     => '실전 성결대 토론면접 — ArguMentor',
            'topics'    => SungkyulTopics::TOPICS,
            'drawn'     => SungkyulTopics::draw(),
            'languages' => DebateOptions::LANGUAGES,
            'errors'    => ['_general' => $message],
            'old'       => [],
            'csrf'      => Csrf::token(),
        ]);
    }
}
