<?php
/**
 * 랜딩 (LogicalAI/LandingPage.tsx 디자인 이식).
 */
$features = [
    ['icon' => 'brain', 'title' => 'AI 반대 논리 생성', 'desc' => 'AI가 자동으로 반대 입장의 논리를 구성합니다'],
    ['icon' => 'swords', 'title' => '실시간 반박 시뮬레이션', 'desc' => '실제 토론처럼 주고받는 반박을 경험하세요'],
    ['icon' => 'bar-chart-3', 'title' => '논리 구조 분석', 'desc' => '논증의 강점과 약점을 시각적으로 분석합니다'],
];
?>
<div class="flex-1 flex flex-col items-center justify-center px-6 text-center pb-16">
    <div class="max-w-3xl fade-up">
        <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full border border-indigo-500/30 bg-indigo-500/10 text-indigo-300 mb-8 text-sm">
            <i data-lucide="sparkles" class="w-3.5 h-3.5"></i>
            AI 기반 토론면접 훈련 도구
        </div>
        <h1 class="text-4xl md:text-6xl font-bold bg-gradient-to-r from-white via-indigo-200 to-purple-300 bg-clip-text text-transparent mb-6 tracking-tight" style="line-height:1.2">
            ArguMentor
        </h1>
        <p class="text-lg text-white/50 mb-10 max-w-xl mx-auto">
            AI와 함께 논리를 검증하고 사고력을 확장하세요.
            <br>어떤 주제든 깊이 있는 토론을 경험할 수 있습니다.
        </p>
        <div class="flex flex-col sm:flex-row items-center justify-center gap-3">
            <a href="/setup"
               class="inline-flex items-center gap-2 px-8 py-3.5 rounded-2xl bg-gradient-to-r from-indigo-600 to-purple-600 text-white shadow-lg shadow-indigo-500/25 hover:shadow-indigo-500/40 hover:scale-[1.03] active:scale-[0.97] transition-all">
                일반 토론 시작하기
                <i data-lucide="arrow-right" class="w-4 h-4"></i>
            </a>
            <a href="/exam"
               class="inline-flex items-center gap-2 px-8 py-3.5 rounded-2xl border border-indigo-400/40 bg-white/[0.03] text-indigo-200 hover:bg-indigo-500/10 hover:border-indigo-400/60 hover:scale-[1.03] active:scale-[0.97] transition-all">
                <i data-lucide="graduation-cap" class="w-4 h-4"></i>
                실전 성결대 토론면접
            </a>
        </div>
        <p class="text-white/30 text-xs mt-4">SKU창의적인재전형 조별 토론면접 · 준비 7분 + 토론 8분 · 공식 4항목 채점</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-5 mt-20 max-w-4xl w-full fade-up" style="animation-delay:.15s">
        <?php foreach ($features as $f): ?>
            <div class="p-6 rounded-2xl bg-white/[0.03] border border-white/[0.06] backdrop-blur-sm hover:border-indigo-500/30 hover:-translate-y-1 transition-all text-left">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-indigo-500/20 to-purple-500/20 flex items-center justify-center mb-4">
                    <i data-lucide="<?= e($f['icon']) ?>" class="w-5 h-5 text-indigo-400"></i>
                </div>
                <h3 class="text-white/90 mb-2 font-medium"><?= e($f['title']) ?></h3>
                <p class="text-sm text-white/40"><?= e($f['desc']) ?></p>
            </div>
        <?php endforeach; ?>
    </div>
</div>
