# ArguMentor — AI 토론면접 시뮬레이터

주제와 입장을 입력하면 **AI가 반대 논리로 반박**하고, 대화가 끝나면 **논리 구조·약점·개선 전략을 분석**해 주는
토론 훈련 웹 서비스입니다. 단순 생성기가 아니라 **시뮬레이션 + 분석** 도구입니다.

## 주요 기능

- **일반 토론 모드** — 주제·입장·스타일을 정하고 AI와 1:1 실시간 채팅 토론 → 4탭 분석 대시보드
  (논리 구조 / 약점 / AI 반박 요약 / 개선 전략)
- **실전 성결대 토론면접 모드** — 성결대 SKU창의적인재전형 *조별 토론면접* 재현:
  주제 추첨 → 준비 7분(A4 메모) → AI 조원 2명과 8분 조별 토론 → 공식 **4항목**(주제 이해력·논리력·언어구사·태도) 채점

## 기술 스택

| 레이어 | 사용 기술 |
|---|---|
| 백엔드 | Vanilla **PHP 8.3+** (SSR, 프레임워크 없음 · Composer는 PSR-4 오토로딩) |
| DB | **MySQL 8.0** (PDO + prepared statement) |
| LLM | **DeepSeek `deepseek-v4-pro`** (OpenAI 호환 API) |
| 프론트 | 서버 렌더링 + Tailwind(CDN) · Alpine.js · lucide (다크 테마) |

계층: `Controllers` → `Services` → `Repositories`, 설정은 `.env`로 분리, 출력 이스케이프·CSRF 적용.

## 요구사항

- PHP 8.3 이상 (`pdo_mysql`, `curl`, `mbstring`, `json` 확장)
- Composer 2.x
- MySQL 8.0
- DeepSeek API 키 (<https://platform.deepseek.com>)

## 설치 및 실행

```bash
# 1) 의존성 설치 (PSR-4 오토로더 생성)
composer install

# 2) 데이터베이스 생성 + 스키마 적용
#    db/schema.sql 안에서 'argumentor' DB를 CREATE 하고 테이블을 만든다.
mysql -u root -p < db/schema.sql

# 3) 환경변수 설정: .env.example을 복사해 값 채우기
cp .env.example .env
#    .env 에서 아래 값을 본인 환경에 맞게 수정:
#      DB_USER / DB_PASS         — MySQL 접속 정보
#      DEEPSEEK_API_KEY          — 발급받은 DeepSeek API 키 (필수)

# 4) 개발 서버 실행 (PHP 내장 서버)
composer serve
#    → http://localhost:8000
```

`.env` 주요 항목:

```dotenv
DB_HOST=127.0.0.1
DB_PORT=3306
DB_NAME=argumentor
DB_USER=root
DB_PASS=
DB_CHARSET=utf8mb4

DEEPSEEK_API_KEY=          # 필수
DEEPSEEK_BASE_URL=https://api.deepseek.com
DEEPSEEK_MODEL=deepseek-v4-pro
```

> `.env`는 비밀정보라 git에 커밋되지 않습니다(`.gitignore`). 제출/배포 시에는 `.env.example`을 참고해 직접 채워야 합니다.

## 사용 흐름

1. 랜딩(`/`)에서 **일반 토론** 또는 **실전 성결대 토론면접** 선택
2. 일반: `/setup` 설정 → `/simulation` 채팅 토론 → `/analysis` 분석
3. 성결대: `/exam` 설정(주제 추첨) → `/exam/prep` 준비(7분) → `/exam/room` 조별 토론(8분) → `/analysis` 채점

## 디렉터리 구조

```
argumentor/
├── public/            # 프론트 컨트롤러(진입점), docroot
├── src/
│   ├── Controllers/   # Home, Setup, Debate(일반), Exam(성결대)
│   ├── Services/      # DeepSeekClient, PromptBuilder, DebateService, ExamService
│   ├── Repositories/  # DebateRepository (PDO)
│   ├── Domain/        # DebateOptions, SungkyulTopics
│   └── Support/       # Env, Database, Router, View, Csrf, helpers
├── templates/         # SSR 뷰 (layout, home, setup, simulation, analysis, exam/*)
├── db/schema.sql      # MySQL 스키마 (debates / debate_messages / debate_results)
├── docs/              # DB_SCHEMA, SUNGKYUL_INTERVIEW(리서치), SKILLS
└── demo/              # 시연 영상 녹화 스크립트(Playwright)
```

## 참고 문서

- [PLAN.md](PLAN.md) — 제품 기획서(범위·화면·데이터 모델)
- [docs/DB_SCHEMA.md](docs/DB_SCHEMA.md) — DB 스키마 상세 + ERD
- [docs/SUNGKYUL_INTERVIEW.md](docs/SUNGKYUL_INTERVIEW.md) — 성결대 토론면접 리서치 + 설계

## 데모 영상

`demo/argumentor-demo.webm` — 전체 흐름(일반 + 성결대 모드) 시연. 재녹화: 서버 실행 후 `cd demo && node record.mjs`.
