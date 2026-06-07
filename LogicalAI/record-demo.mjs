/**
 * LogicalAI 프로토타입 데모 녹화 스크립트
 * Playwright를 사용하여 전체 실행 흐름을 영상으로 녹화합니다.
 *
 * 흐름: Landing → Debate Setup → Simulation → Analysis Dashboard
 */
import { chromium } from "playwright";
import path from "path";
import { fileURLToPath } from "url";

const __dirname = path.dirname(fileURLToPath(import.meta.url));
const IMG_DIR = path.join(__dirname, "img");

async function sleep(ms) {
  return new Promise((resolve) => setTimeout(resolve, ms));
}

(async () => {
  console.log("🎬 녹화를 시작합니다...");

  const browser = await chromium.launch({ headless: false });
  const context = await browser.newContext({
    viewport: { width: 1280, height: 800 },
    recordVideo: {
      dir: IMG_DIR,
      size: { width: 1280, height: 800 },
    },
    locale: "ko-KR",
  });

  const page = await context.newPage();

  try {
    // ============================================
    // Step 1: Landing Page
    // ============================================
    console.log("📄 Step 1: Landing Page");
    await page.goto("http://localhost:5173/", { waitUntil: "networkidle" });
    await sleep(3000); // 애니메이션 완료 대기

    // Feature 카드 호버 효과
    const featureCards = page.locator(
      ".grid.grid-cols-1.md\\:grid-cols-3 > div"
    );
    const cardCount = await featureCards.count();
    for (let i = 0; i < cardCount; i++) {
      await featureCards.nth(i).hover();
      await sleep(600);
    }
    await sleep(800);

    // "토론 시작하기" 버튼 클릭
    console.log('   → "토론 시작하기" 클릭');
    await page.click('button:has-text("토론 시작하기")');
    await sleep(2000);

    // ============================================
    // Step 2: Debate Setup Page
    // ============================================
    console.log("📄 Step 2: Debate Setup Page");
    await sleep(2000); // 페이지 로드 및 애니메이션

    // 샘플 주제 선택
    console.log("   → 샘플 주제 선택");
    await page.click(
      'button:has-text("인공지능이 인간의 일자리를 대체해야 하는가")'
    );
    await sleep(1000);

    // "찬성" 입장 선택
    console.log('   → "찬성" 입장 선택');
    await page.click('button:has-text("찬성")');
    await sleep(1000);

    // 토론 스타일 변경 (호버 + 클릭)
    console.log("   → 토론 스타일 변경");
    const attackStyle = page.locator('button:has-text("공격적")');
    await attackStyle.hover();
    await sleep(600);
    await page.click('button:has-text("학술적")');
    await sleep(1000);

    // "AI 토론 시작" 클릭
    console.log('   → "AI 토론 시작" 클릭');
    await page.click('button:has-text("AI 토론 시작")');
    await sleep(2500);

    // ============================================
    // Step 3: Simulation Page
    // ============================================
    console.log("📄 Step 3: Simulation Page");
    await sleep(3000); // AI 초기 메시지 표시 대기

    // 메시지 입력
    console.log("   → 사용자 메시지 입력");
    const inputField = page.locator(
      'input[placeholder="나의 주장을 입력하세요..."]'
    );
    await inputField.click();
    await sleep(500);

    // 한 글자씩 타이핑 효과
    const userMessage =
      "인공지능은 반복적이고 단순한 업무를 자동화하여 인간이 더 창의적인 일에 집중할 수 있게 합니다.";
    await inputField.type(userMessage, { delay: 30 });
    await sleep(1000);

    // 전송 버튼 클릭
    console.log("   → 메시지 전송");
    const sendButton = page.locator(
      "button.p-2.rounded-xl.bg-indigo-600"
    );
    await sendButton.click();
    await sleep(4500); // AI 타이핑 애니메이션 + 응답 대기

    // 스크롤 다운 (AI 응답 전체 보기)
    const scrollArea = page.locator(".flex-1.overflow-y-auto");
    await scrollArea.evaluate((el) =>
      el.scrollTo({ top: el.scrollHeight, behavior: "smooth" })
    );
    await sleep(2000);

    // "분석 보기" 버튼 클릭
    console.log('   → "분석 보기" 클릭');
    await page.click('button:has-text("분석 보기")');
    await sleep(2500);

    // ============================================
    // Step 4: Analysis Dashboard
    // ============================================
    console.log("📄 Step 4: Analysis Dashboard");
    await sleep(2000);

    // 스크롤 다운 (논리 구조 분석 전체 보기)
    await page.evaluate(() =>
      window.scrollTo({ top: 400, behavior: "smooth" })
    );
    await sleep(2000);
    await page.evaluate(() =>
      window.scrollTo({ top: 0, behavior: "smooth" })
    );
    await sleep(1500);

    // 탭 전환 - 약점 분석
    console.log('   → "약점 분석" 탭');
    await page.click('button:has-text("약점 분석")');
    await sleep(2500);

    // 스크롤 다운
    await page.evaluate(() =>
      window.scrollTo({ top: 300, behavior: "smooth" })
    );
    await sleep(1500);
    await page.evaluate(() =>
      window.scrollTo({ top: 0, behavior: "smooth" })
    );
    await sleep(1000);

    // 탭 전환 - AI 반박 요약
    console.log('   → "AI 반박 요약" 탭');
    await page.click('button:has-text("AI 반박 요약")');
    await sleep(2500);

    // 탭 전환 - 개선 전략
    console.log('   → "개선 전략" 탭');
    await page.click('button:has-text("개선 전략")');
    await sleep(3000);

    console.log("✅ 모든 흐름 녹화 완료!");
  } catch (err) {
    console.error("❌ 에러 발생:", err.message);
  } finally {
    // 컨텍스트 닫기 → 비디오 자동 저장
    await page.close();
    const videoPath = await page.video()?.path();
    console.log(`🎥 비디오 저장 위치: ${videoPath}`);
    await context.close();
    await browser.close();
    console.log(`📁 img 폴더에 영상이 저장되었습니다: ${IMG_DIR}`);
  }
})();
