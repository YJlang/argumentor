# 성결대학교 토론면접 리서치 & 구현 계획

ArguMentor의 확장 모드 **"실전 성결대 토론면접 준비하기"**의 근거 자료와 설계. 일반 모드(1:1 AI 반박)는
그대로 두고, 성결대 SKU창의적인재전형 **조별 토론면접**을 실전처럼 재현하는 별도 흐름을 추가한다.

## 1. 리서치 (출처 명시)

성결대 **SKU창의적인재전형** 면접고사는 **조별 토론면접만** 진행하며 개별질문이 없다.

| 항목 | 내용 |
|---|---|
| 조 구성 | 수험생 **3~4명**이 한 조 |
| 진행 절차 | 대기장(가번호 조편성·휴대폰 OFF) → **1차 고사장**: 조별 **주제 1개 추첨 + 약 7분 준비**(학교 제공 A4 메모만 가능, 전자기기·검색 불가) → **2차 고사장**: **8분간 자유 찬반 토론** |
| 주제 | 사전 비공개. 고등학생 수준의 **시사·상식**. 지원학부(과)와 무관. **제시문 없이 주제만** 제시, 명확한 찬반 이분법 |
| 평가 4항목 | ① 주제의 이해력 ② 주장의 논리력 ③ 언어구사능력 ④ 성실성(태도) |
| 주의사항 | 인적사항(수험번호·성명·출신고교) 언급 금지(위반 시 불이익), 단순 교과지식 측정 없음 |
| 복장 | 자율복(교복·생활복 불가) |
| 예시 주제 | 코로나-19 국경봉쇄 / 난민 수용 정책 / 인터넷 실명제 / 코로나 이후 온라인 수업 유지 / 반려동물 보유세 / 유명인사 특례(BTS 군복무 면제) |

**출처**
- 성결대 입학처 — SKU창의적인재전형 문제 유형 예시: https://www.sungkyul.ac.kr/bbs/ipsi/21/23981/artclView.do
- 성결대 입학처 — SKU창의적인재전형 토론면접 FAQ: https://www.sungkyul.ac.kr/ipsi/96/subview.do
- 대학백과 — 성결대 합격후기/QnA: https://www.univ100.kr/admission/68

## 2. 일반 모드와의 차이 (왜 별도 모드인가)

| | 일반 모드(현행) | 성결대 모드(신규) |
|---|---|---|
| 상대 | AI 1명(반대) | **AI 조원 2~3명**(혼합 입장) = 조별 토론 |
| 시간 | 없음 | **준비 7분 + 토론 8분** 타이머(실전 동일) |
| 주제 | 사용자 입력 | **추첨**(시사·상식 풀) 또는 선택 |
| 준비 | 없음 | **A4 메모장**(준비시간) |
| 채점 | 일반 논리 4지표 | **공식 4항목**(이해력/논리력/언어구사/태도) + 성결대 팁 |

## 3. 구현 계획

### 데이터 (기존 테이블 재사용 + 최소 컬럼 추가)
- `debates.mode` ENUM('free','sungkyul') DEFAULT 'free' — 모드 구분
- `debates.memo` TEXT NULL — 준비시간 A4 메모
- `debate_messages.speaker` VARCHAR(40) NULL — 조별 토론에서 발화자 표시명(예: "학생 B")
- `debate_results`는 그대로 사용. 성결대 채점은 `logic_analysis.scores`에 **공식 4항목 라벨**로 채워 재사용.

### 백엔드
- `Domain/SungkyulTopics.php` — SKU식 시사·상식 주제 풀 + 랜덤 추첨
- `Services/PromptBuilder` 확장 — ⓐ **조별 패널** 프롬프트(AI 조원 2~3명, 각자 입장/페르소나, JSON 배열로 발화 반환) ⓑ **성결대 4항목 채점** 프롬프트
- `Services/ExamService` — `start`(세션 생성+사회자 멘트) / `saveMemo` / `panelReply`(다중 페르소나 1턴) / `analyze`(4항목)
- `Controllers/ExamController` — setup/create/prep/start/room/message(AJAX)/analyze
- 라우트: `GET /exam`, `POST /exam`, `GET /exam/prep`, `POST /exam/start`, `GET /exam/room`, `POST /exam/messages`, `POST /exam/analyze` → 결과는 기존 `/analysis?id=` 재사용(모드 인지 렌더)

### 프론트 (다크 테마 유지)
- 랜딩에 **"실전 성결대 토론면접" CTA** 추가(별도 진입)
- `exam/setup` — 형식 안내 + 주제 추첨/선택 + 내 입장 + 조원 수
- `exam/prep` — **7분 카운트다운** + A4 메모장(Alpine 타이머)
- `exam/room` — **8분 카운트다운** + 조별 그룹 채팅(발화자별 말풍선), 시간 종료 시 자동 채점 이동
- `analysis` — `mode='sungkyul'`이면 4항목 강조 + 성결대 대비 팁 블록

## 4. 리팩토링 메모 (agent 문서 동기화 대상)
- PLAN.md: §4 MVP에 "성결대 토론면접 모드"를 확장 기능으로 명시(본 문서 참조).
- CLAUDE.md: Current phase에 두 모드(free/sungkyul) 흐름과 신규 레이어(ExamService/ExamController, SungkyulTopics) 반영.
- 향후: 주제 풀을 DB/관리화면으로 분리, 타이머를 서버 권위(현재는 클라 타이머), 발화자 TTS·음성 등은 비범위.
