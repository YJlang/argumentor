/**
 * ArguMentor 시연 영상 녹화 (PHP 앱 @ http://localhost:8000)
 * 흐름: 랜딩 → 일반 토론(설정→채팅→분석) → 실전 성결대 토론면접(설정→준비→조별토론→채점)
 * 실제 DeepSeek 호출을 포함하므로 응답 대기(navigation/response)를 넉넉히 둔다.
 */
import { chromium } from "playwright";
import path from "path";
import fs from "fs";
import { fileURLToPath } from "url";

const __dirname = path.dirname(fileURLToPath(import.meta.url));
const OUT_DIR = path.join(__dirname, "output");
const BASE = "http://localhost:8000";
const sleep = (ms) => new Promise((r) => setTimeout(r, ms));

fs.mkdirSync(OUT_DIR, { recursive: true });

(async () => {
  const browser = await chromium.launch({ headless: true });
  const context = await browser.newContext({
    viewport: { width: 1280, height: 800 },
    recordVideo: { dir: OUT_DIR, size: { width: 1280, height: 800 } },
    locale: "ko-KR",
  });
  const page = await context.newPage();
  page.setDefaultTimeout(20000);

  try {
    // ── 1. 랜딩 ─────────────────────────────────────────────
    console.log("① 랜딩");
    await page.goto(BASE + "/", { waitUntil: "networkidle" });
    await sleep(3000);

    // ── 2. 일반 토론 ────────────────────────────────────────
    console.log("② 일반 토론 — 설정");
    await page.locator('a[href="/setup"]').click({ noWaitAfter: true });
    await page.waitForURL("**/setup");
    await sleep(1500);
    await page.fill('input[name="topic"]', "대학 수업에서 생성형 AI 사용을 허용해야 한다");
    await sleep(800);
    await page.getByRole("button", { name: "찬성", exact: true }).click();
    await sleep(600);
    await page.getByRole("button", { name: /논리적/ }).click();
    await sleep(1000);
    await page.locator('form[action="/debates"] button[type="submit"]').click({ noWaitAfter: true });

    console.log("②-2 시뮬레이션(채팅)");
    await page.waitForURL("**/simulation**", { timeout: 60000 });
    await sleep(2500); // AI 인사 표시
    await page.fill('input[placeholder*="주장을 입력"]', "AI는 반복적 정보 탐색 시간을 줄여 학생이 더 깊은 사고에 집중하게 합니다.");
    await sleep(800);
    const msg1 = page.waitForResponse((r) => r.url().includes("/messages") && r.status() === 200, { timeout: 180000 });
    await page.keyboard.press("Enter");
    await msg1;
    await sleep(3500); // AI 반박 렌더 + 읽기

    console.log("②-3 분석 대시보드");
    await page.locator('form[action="/analyze"] button[type="submit"]').click({ noWaitAfter: true });
    await page.waitForURL("**/analysis**", { timeout: 180000 });
    await sleep(2500);
    for (const tab of ["약점 분석", "AI 반박 요약", "개선 전략", "논리 구조 분석"]) {
      await page.getByRole("button", { name: tab }).click();
      await sleep(1800);
    }

    // ── 3. 실전 성결대 토론면접 ──────────────────────────────
    console.log("③ 성결대 모드 — 설정");
    await page.goto(BASE + "/exam", { waitUntil: "networkidle" });
    await sleep(2000);
    await page.getByRole("button", { name: /주제 추첨/ }).click();
    await sleep(1500);
    await page.getByRole("button", { name: "찬성", exact: true }).click();
    await sleep(1000);
    await page.locator('form[action="/exam"] button[type="submit"]').click({ noWaitAfter: true });

    console.log("③-2 준비 시간(7분 타이머 + 메모)");
    await page.waitForURL("**/exam/prep**", { timeout: 60000 });
    await sleep(2000);
    await page.fill('textarea[name="memo"]', "• 핵심 주장 정리\n• 근거 1, 2\n• 예상 반론과 대응");
    await sleep(2500);
    await page.getByRole("button", { name: /지금 토론 시작/ }).click({ noWaitAfter: true });

    console.log("③-3 조별 토론(8분 타이머 + AI 조원)");
    await page.waitForURL("**/exam/room**", { timeout: 60000 });
    await sleep(3000); // 사회자 멘트
    await page.fill('input[placeholder*="의견을 말하세요"]', "저는 찬성합니다. 핵심 근거는 학습 효율과 접근성 향상입니다.");
    await sleep(800);
    const msg2 = page.waitForResponse((r) => r.url().includes("/exam/messages") && r.status() === 200, { timeout: 180000 });
    await page.keyboard.press("Enter");
    await msg2;
    await sleep(4000); // 조원 2명 발언 렌더 + 읽기

    console.log("③-4 채점 결과(공식 4항목)");
    await page.getByRole("button", { name: /채점 받기/ }).click({ noWaitAfter: true });
    await page.waitForURL("**/analysis**", { timeout: 180000 });
    await sleep(2500);
    await page.evaluate(() => window.scrollTo({ top: document.body.scrollHeight, behavior: "smooth" }));
    await sleep(4000); // 4항목 + 성결대 팁

    console.log("✅ 녹화 흐름 완료");
  } catch (e) {
    console.error("⚠️ 흐름 중 오류:", e.message);
  } finally {
    const video = page.video();
    await context.close(); // 영상 파일 flush
    await browser.close();
    if (video) {
      const src = await video.path();
      const dest = path.join(__dirname, "argumentor-demo.webm");
      fs.copyFileSync(src, dest);
      console.log("🎬 영상 저장:", dest);
    }
  }
})();
