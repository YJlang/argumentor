-- =====================================================================
-- ArguMentor — AI 토론면접 시뮬레이션 시스템 스키마 (MySQL 8.0)
-- 출처: PLAN.md §8 데이터 모델 초안을 정규화 + MySQL 8.0 / DeepSeek 반영
--
-- 재현 방법:
--   mysql -u root -p < db/schema.sql
-- (DDL은 IF NOT EXISTS라 여러 번 실행해도 안전. 전체 초기화는 아래 RESET 블록 참고)
-- =====================================================================

CREATE DATABASE IF NOT EXISTS argumentor
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_0900_ai_ci;

USE argumentor;

-- ---------------------------------------------------------------------
-- (선택) 전체 초기화: 스키마를 깨끗이 다시 만들 때만 주석 해제.
-- FK 때문에 자식 → 부모 순으로 DROP.
-- ---------------------------------------------------------------------
-- DROP TABLE IF EXISTS debate_messages;
-- DROP TABLE IF EXISTS debate_results;
-- DROP TABLE IF EXISTS debates;

-- ---------------------------------------------------------------------
-- debates : 토론(면접) 세션 1건의 입력 정보
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS debates (
  id              BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  mode            ENUM('free','sungkyul') NOT NULL DEFAULT 'free' COMMENT 'free=1:1 일반, sungkyul=성결대 조별 토론면접',
  topic           VARCHAR(500)    NOT NULL                       COMMENT '토론/면접 주제',
  user_stance     ENUM('for','against') NOT NULL                COMMENT '사용자 입장 (for=찬성, against=반대)',
  debate_style    ENUM('logical','aggressive','academic')
                  NOT NULL DEFAULT 'logical'                     COMMENT '토론 스타일 (논리적/공격적/학술적)',
  user_argument   TEXT            NULL                           COMMENT '(선택) 1차 주장 요약. 실제 주장은 debate_messages에 턴별로 저장',
  output_language ENUM('ko','en','ja','zh') NOT NULL DEFAULT 'ko' COMMENT '응답 언어',
  memo            TEXT            NULL                           COMMENT '(성결대 모드) 준비시간 A4 메모',
  created_at      DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at      DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_debates_created_at (created_at),
  KEY idx_debates_language   (output_language)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci
  COMMENT='토론 세션 기본 정보';

-- ---------------------------------------------------------------------
-- debate_messages : 실시간 채팅형 토론의 턴별 메시지 (사용자 ↔ AI)
-- debates 1 : N debate_messages
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS debate_messages (
  id         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  debate_id  BIGINT UNSIGNED NOT NULL,
  role       ENUM('user','ai') NOT NULL                          COMMENT '발화 주체',
  speaker    VARCHAR(40)     NULL                                COMMENT '(성결대 조별) 발화자 표시명. 예: 학생 B',
  content    LONGTEXT        NOT NULL                            COMMENT '메시지 본문',
  created_at DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_messages_debate (debate_id, id),
  CONSTRAINT fk_messages_debate
    FOREIGN KEY (debate_id) REFERENCES debates(id)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci
  COMMENT='토론 채팅 메시지(턴)';

-- ---------------------------------------------------------------------
-- debate_results : AI 반박 + 분석 결과
-- debates 1 : N debate_results (재반박/재생성 라운드를 위해 1:N으로 둠)
-- 구조화 필드는 MySQL 8.0 네이티브 JSON 타입 사용 (PLAN.md의 "longtext JSON" 대체)
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS debate_results (
  id                       BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  debate_id                BIGINT UNSIGNED NOT NULL,
  round_no                 INT UNSIGNED    NOT NULL DEFAULT 1     COMMENT '재반박 라운드 번호 (1부터)',

  ai_counter_argument      LONGTEXT        NULL                   COMMENT 'AI 반대 논리 (서술형)',
  ai_challenging_questions JSON            NULL                   COMMENT 'AI 도전/꼬리질문 목록 (배열)',
  logic_analysis           JSON            NULL                   COMMENT '논리 구조 분석 (주장-근거-결론, 점수 등)',
  weakness_analysis        JSON            NULL                   COMMENT '약점 분석 (논리오류/근거부족/설득력 등)',
  rebuttal_summary         JSON            NULL                   COMMENT 'AI 반박 요약 (핵심 반박 포인트 목록)',
  improvement_strategy     JSON            NULL                   COMMENT '개선 전략 (근거 보강/구조 강화/설득력 향상)',

  -- LLM 호출 메타데이터 (재현성/비용 추적 — DeepSeek thinking 모델)
  model              VARCHAR(64)  NULL                            COMMENT '사용 모델명 (예: deepseek-v4-pro)',
  prompt_tokens      INT UNSIGNED NULL,
  completion_tokens  INT UNSIGNED NULL,
  reasoning_tokens   INT UNSIGNED NULL                            COMMENT 'DeepSeek reasoning_content 토큰 수',
  raw_response       LONGTEXT     NULL                            COMMENT 'LLM 원본 응답 (디버깅/감사용)',

  created_at         DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_results_debate_id (debate_id),
  CONSTRAINT fk_results_debate
    FOREIGN KEY (debate_id) REFERENCES debates(id)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci
  COMMENT='AI 반박 및 분석 결과';
