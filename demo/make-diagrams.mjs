/**
 * 발표용 다이어그램 PNG 생성 (Mermaid + Playwright 스크린샷).
 * 다크 테마(앱과 동일 계열) · 고해상도(2x).
 * 출력: ~/Desktop/argumentor-diagrams/{erd,architecture,layers}.png
 */
import { chromium } from "playwright";
import path from "path";
import fs from "fs";
import os from "os";

const OUT = path.join(os.homedir(), "Desktop", "argumentor-diagrams");
fs.mkdirSync(OUT, { recursive: true });

const ERD = `erDiagram
  debates ||--o{ debate_messages : "1 : N (CASCADE)"
  debates ||--o{ debate_results : "1 : N (CASCADE)"
  debates {
    bigint id PK
    enum mode "free | sungkyul"
    varchar topic
    enum user_stance "for | against"
    enum debate_style
    enum output_language
    text memo
    datetime created_at
  }
  debate_messages {
    bigint id PK
    bigint debate_id FK
    enum role "user | ai"
    varchar speaker
    longtext content
    datetime created_at
  }
  debate_results {
    bigint id PK
    bigint debate_id FK
    int round_no
    json logic_analysis
    json weakness_analysis
    json rebuttal_summary
    json improvement_strategy
    varchar model
    int reasoning_tokens
  }`;

const ARCH = `flowchart LR
  B["🌐 브라우저<br/>(Tailwind + Alpine.js)"]
  FC["public/index.php<br/>Front Controller"]
  R["Router"]
  C["Controller<br/>Home · Setup · Debate · Exam"]
  S["Service<br/>PromptBuilder · DebateService<br/>ExamService · DeepSeekClient"]
  RP["Repository<br/>PDO"]
  DB[("MySQL 8.0")]
  LLM["DeepSeek API<br/>deepseek-v4-pro"]
  V["templates<br/>SSR 뷰"]
  B --> FC --> R --> C --> S --> RP --> DB
  S -. HTTPS .-> LLM
  C --> V -. HTML 응답 .-> B`;

const LAYERS = `flowchart TB
  subgraph L1["① Controller — HTTP 계층"]
    a["요청 수신 · 입력 검증 · CSRF · 응답/리다이렉트"]
  end
  subgraph L2["② Service — 비즈니스 로직"]
    b["프롬프트 생성 · DeepSeek 호출 · JSON 파싱 · 오케스트레이션"]
  end
  subgraph L3["③ Repository — 영속성"]
    c["PDO Prepared Statement → MySQL"]
  end
  L1 --> L2 --> L3`;

const cards = [
  { id: "erd", title: "데이터베이스 ERD", sub: "MySQL 8.0 · 3개 테이블 · 1:N 관계 (ON DELETE CASCADE)", code: ERD },
  { id: "architecture", title: "시스템 아키텍처 — 요청 처리 흐름", sub: "SSR 모놀리식 · 단일 진입점 · Controller→Service→Repository", code: ARCH },
  { id: "layers", title: "계층형 아키텍처 (관심사 분리)", sub: ".env 분리 · CSRF · 출력 이스케이프", code: LAYERS },
];

const html = `<!DOCTYPE html><html lang="ko"><head><meta charset="utf-8">
<link href="https://fonts.googleapis.com/css2?family=Noto+Sans+KR:wght@400;500;700&display=swap" rel="stylesheet">
<style>
  body{ background:#06060c; font-family:'Noto Sans KR',sans-serif; margin:0; padding:24px; }
  .card{ width:fit-content; min-width:760px; max-width:1700px; background:#0b0b16;
         border:1px solid rgba(129,140,248,.18);
         border-radius:24px; padding:44px 52px; margin:0 0 40px; box-sizing:border-box;
         box-shadow:0 0 80px rgba(99,102,241,.08) inset; }
  h2{ font-size:30px; font-weight:700; margin:0 0 6px;
      background:linear-gradient(90deg,#ffffff,#c7d2fe,#d8b4fe);
      -webkit-background-clip:text; background-clip:text; color:transparent; }
  p.sub{ color:rgba(255,255,255,.4); font-size:15px; margin:0 0 28px; }
  .mermaid{ display:flex; justify-content:center; }
  /* Mermaid v11 ER: 행 줄무늬(기본 흰색) 어둡게 강제 + HTML 라벨 글자 밝게 */
  .mermaid svg .row-rect-odd{ fill:#1b1933 !important; }
  .mermaid svg .row-rect-even{ fill:#12111f !important; }
  .mermaid svg .outer-path{ fill:#1e1b4b !important; stroke:#6366f1 !important; }
  .mermaid svg .divider{ stroke:#3b3a63 !important; }
  .mermaid svg foreignObject, .mermaid svg foreignObject *{ color:#e7e7ef !important; }
  .mermaid svg .attribute-keys{ color:#a5b4fc !important; }
</style></head><body>
${cards.map((c) => `<div class="card" id="card-${c.id}"><h2>${c.title}</h2><p class="sub">${c.sub}</p><pre class="mermaid">${c.code}</pre></div>`).join("\n")}
<script type="module">
  import mermaid from 'https://cdn.jsdelivr.net/npm/mermaid@11/dist/mermaid.esm.min.mjs';
  mermaid.initialize({
    startOnLoad:false, securityLevel:'loose', theme:'base',
    fontFamily:"'Noto Sans KR', sans-serif",
    er:{ useMaxWidth:false }, flowchart:{ useMaxWidth:false },
    themeVariables:{
      background:'#0b0b16', primaryColor:'#1e1b4b', primaryBorderColor:'#6366f1',
      primaryTextColor:'#e7e7ef', secondaryColor:'#312e81', tertiaryColor:'#15152400',
      lineColor:'#818cf8', textColor:'#e7e7ef', nodeTextColor:'#e7e7ef', fontSize:'16px',
      clusterBkg:'#13132100', clusterBorder:'#6366f1',
      nodeBorder:'#6366f1', mainBkg:'#1e1b4b',
      edgeLabelBackground:'#0b0b16'
    }
  });
  await document.fonts.ready;
  await mermaid.run({ querySelector: '.mermaid' });
  // ER(#card-erd): 행 배경이 class 없는 흰색 path → 밝은 fill을 직접 어둡게 강제
  document.querySelectorAll('#card-erd svg path, #card-erd svg rect').forEach((el) => {
    const m = getComputedStyle(el).fill.match(/\\d+/g);
    if (!m) return;
    const [r, g, bl] = m.map(Number);
    if (r > 190 && g > 190 && bl > 170) el.style.setProperty('fill', '#161430', 'important');
  });
  document.querySelectorAll('#card-erd foreignObject *').forEach((e) => e.style.setProperty('color', '#e7e7ef', 'important'));
  document.querySelectorAll('#card-erd .attribute-keys').forEach((e) => e.style.setProperty('color', '#a5b4fc', 'important'));
  window.__DONE__ = true;
</script></body></html>`;

(async () => {
  const browser = await chromium.launch({ headless: true });
  const context = await browser.newContext({ viewport: { width: 1900, height: 1200 }, deviceScaleFactor: 2 });
  const page = await context.newPage();
  await page.setContent(html, { waitUntil: "networkidle" });
  await page.waitForFunction(() => window.__DONE__ === true && document.querySelectorAll(".mermaid svg").length === 3, { timeout: 30000 });
  await page.waitForTimeout(600); // 폰트 안정화
  for (const c of cards) {
    const el = page.locator(`#card-${c.id}`);
    const out = path.join(OUT, `${c.id}.png`);
    await el.screenshot({ path: out });
    console.log("🖼  저장:", out);
  }
  await browser.close();
})();
