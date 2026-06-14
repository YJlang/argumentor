<?php
/**
 * 분석 대시보드 (LogicalAI/AnalysisDashboard.tsx 디자인 이식).
 *
 * @var array<string,mixed>      $debate
 * @var array<string,mixed>|null $result
 */

use App\Domain\DebateOptions;

$id          = (int) $debate['id'];
$isExam      = ($debate['mode'] ?? 'free') === 'sungkyul';
$backUrl     = $isExam ? '/exam' : '/simulation?id=' . $id;
$stanceLabel = DebateOptions::STANCES[$debate['user_stance']] ?? (string) $debate['user_stance'];
$styleLabel  = DebateOptions::STYLES[$debate['debate_style']] ?? (string) $debate['debate_style'];

$questions  = is_array($result['ai_challenging_questions'] ?? null) ? $result['ai_challenging_questions'] : [];
$logic      = is_array($result['logic_analysis'] ?? null) ? $result['logic_analysis'] : [];
$structure  = is_array($logic['structure'] ?? null) ? $logic['structure'] : [];
$scores     = is_array($logic['scores'] ?? null) ? $logic['scores'] : [];
$weaknesses = is_array($result['weakness_analysis'] ?? null) ? $result['weakness_analysis'] : [];
$rebuttals  = is_array($result['rebuttal_summary'] ?? null) ? $result['rebuttal_summary'] : [];
$strategies = is_array($result['improvement_strategy'] ?? null) ? $result['improvement_strategy'] : [];

$scoreText = static fn (int $v): string => $v >= 70 ? 'text-emerald-400' : ($v >= 50 ? 'text-amber-400' : 'text-rose-400');
$scoreBar  = static fn (int $v): string => $v >= 70 ? 'bg-emerald-500' : ($v >= 50 ? 'bg-amber-500' : 'bg-rose-500');
$sevBar    = static fn (string $s): string => $s === 'high' ? 'bg-rose-500' : ($s === 'medium' ? 'bg-amber-500' : 'bg-emerald-500');
?>
<div class="flex-1 px-4 md:px-8 py-2">
    <div class="max-w-5xl mx-auto">

        <!-- 헤더 -->
        <div class="flex items-center gap-3 mb-6">
            <a href="<?= e($backUrl) ?>" class="p-2 rounded-xl hover:bg-white/5 transition-colors">
                <i data-lucide="arrow-left" class="w-4 h-4 text-white/50"></i>
            </a>
            <div class="min-w-0">
                <div class="text-xs <?= $isExam ? 'text-indigo-300' : 'text-white/30' ?>">
                    <?= $isExam ? '🎓 성결대 토론면접 채점 결과 (공식 4항목)' : '분석 대시보드' ?>
                </div>
                <h1 class="text-lg text-white/90 font-medium truncate"><?= e((string) $debate['topic']) ?></h1>
            </div>
        </div>

        <?php if ($result === null): ?>
            <div class="rounded-2xl border border-white/[0.06] bg-white/[0.03] p-10 text-center">
                <p class="text-white/60 mb-4">아직 분석 결과가 없습니다. 토론을 진행한 뒤 "분석 보기"를 눌러주세요.</p>
                <a href="/simulation?id=<?= $id ?>" class="inline-flex items-center gap-2 px-6 py-3 rounded-2xl bg-gradient-to-r from-indigo-600 to-purple-600 text-white">토론으로 돌아가기</a>
            </div>
        <?php else: ?>

            <!-- AI 반박 요약 + 도전 질문 -->
            <div class="rounded-2xl bg-white/[0.03] border border-white/[0.06] p-6 mb-4 fade-up">
                <h2 class="<?= $isExam ? 'text-indigo-300' : 'text-rose-400' ?> font-medium mb-2"><?= $isExam ? '평가위원 총평' : 'AI 핵심 반박' ?></h2>
                <p class="text-sm text-white/70 whitespace-pre-line"><?= e((string) ($result['ai_counter_argument'] ?? '')) ?></p>
                <?php if ($questions !== []): ?>
                    <div class="border-t border-white/[0.06] my-4"></div>
                    <h3 class="text-sm text-white/60 mb-2">보완이 필요한 질문</h3>
                    <ul class="space-y-2">
                        <?php foreach ($questions as $q): ?>
                            <li class="flex gap-2 text-sm text-white/70"><span class="text-indigo-400">🔍</span><span><?= e((string) $q) ?></span></li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>

            <!-- 탭 -->
            <div x-data="{ tab: 0 }" x-init="$nextTick(() => window.lucide && lucide.createIcons())">
                <div class="flex gap-1 overflow-x-auto pb-1 mb-5">
                    <?php foreach (['논리 구조 분석', '약점 분석', 'AI 반박 요약', '개선 전략'] as $ti => $tname): ?>
                        <button @click="tab = <?= $ti ?>"
                                :class="tab === <?= $ti ?> ? 'bg-indigo-600/20 text-indigo-300 border-indigo-500/30' : 'text-white/40 hover:text-white/60 hover:bg-white/[0.04] border-transparent'"
                                class="px-4 py-2 rounded-xl text-sm whitespace-nowrap border transition-all"><?= e($tname) ?></button>
                    <?php endforeach; ?>
                </div>

                <!-- 논리 구조 -->
                <div x-show="tab === 0" class="space-y-8">
                    <?php if ($structure !== []): ?>
                        <div>
                            <h3 class="text-white/60 text-sm mb-4">주장 흐름도</h3>
                            <div class="flex flex-col md:flex-row gap-3">
                                <?php foreach ($structure as $i => $s): ?>
                                    <div class="flex-1 p-4 rounded-2xl bg-white/[0.03] border border-white/[0.06]">
                                        <span class="inline-block text-xs px-2 py-0.5 rounded-full bg-gradient-to-r from-indigo-500 to-purple-600 text-white mb-2"><?= e((string) ($s['label'] ?? '')) ?></span>
                                        <p class="text-sm text-white/70"><?= e((string) ($s['content'] ?? '')) ?></p>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                    <?php if ($scores !== []): ?>
                        <div>
                            <h3 class="text-white/60 text-sm mb-4">논증 점수</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <?php foreach ($scores as $sc): $v = (int) ($sc['value'] ?? 0); ?>
                                    <div class="p-4 rounded-2xl bg-white/[0.03] border border-white/[0.06]">
                                        <div class="flex justify-between text-sm mb-2">
                                            <span class="text-white/60"><?= e((string) ($sc['label'] ?? '')) ?></span>
                                            <span class="<?= $scoreText($v) ?>"><?= $v ?>점</span>
                                        </div>
                                        <div class="h-2 rounded-full bg-white/[0.06] overflow-hidden">
                                            <div class="h-full rounded-full <?= $scoreBar($v) ?>" style="width: <?= $v ?>%"></div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- 약점 -->
                <div x-show="tab === 1" class="space-y-5" style="display:none">
                    <?php foreach ($weaknesses as $w): ?>
                        <div class="p-5 rounded-2xl bg-white/[0.03] border border-white/[0.06]">
                            <div class="flex items-center gap-2 mb-4">
                                <i data-lucide="alert-triangle" class="w-5 h-5 text-amber-400"></i>
                                <h3 class="text-white/80"><?= e((string) ($w['type'] ?? '')) ?></h3>
                            </div>
                            <div class="space-y-3">
                                <?php foreach ((is_array($w['items'] ?? null) ? $w['items'] : []) as $item): ?>
                                    <div class="flex items-start gap-3 pl-2">
                                        <span class="w-1.5 h-1.5 rounded-full mt-2 shrink-0 bg-amber-400"></span>
                                        <p class="text-sm text-white/60"><?= e((string) $item) ?></p>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- AI 반박 요약 -->
                <div x-show="tab === 2" class="space-y-4" style="display:none">
                    <?php foreach ($rebuttals as $r): $sev = (string) ($r['severity'] ?? 'low'); ?>
                        <div class="flex gap-4 p-5 rounded-2xl bg-white/[0.03] border border-white/[0.06] hover:border-indigo-500/20 transition-colors">
                            <div class="w-1 rounded-full shrink-0 <?= $sevBar($sev) ?>"></div>
                            <div>
                                <h4 class="text-white/80 text-sm mb-1"><?= e((string) ($r['point'] ?? '')) ?></h4>
                                <p class="text-sm text-white/45"><?= e((string) ($r['detail'] ?? '')) ?></p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- 개선 전략 -->
                <div x-show="tab === 3" class="grid grid-cols-1 md:grid-cols-3 gap-5" style="display:none">
                    <?php foreach ($strategies as $st): ?>
                        <div class="p-5 rounded-2xl bg-white/[0.03] border border-white/[0.06]">
                            <i data-lucide="lightbulb" class="w-6 h-6 text-indigo-400 mb-3"></i>
                            <h3 class="text-white/80 mb-3"><?= e((string) ($st['title'] ?? '')) ?></h3>
                            <ul class="space-y-2.5">
                                <?php foreach ((is_array($st['items'] ?? null) ? $st['items'] : []) as $item): ?>
                                    <li class="flex items-start gap-2 text-sm text-white/50">
                                        <i data-lucide="arrow-right" class="w-3.5 h-3.5 mt-0.5 shrink-0 text-white/20"></i>
                                        <?= e((string) $item) ?>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="text-xs text-white/30 mt-6 text-right">
                <?= e((string) ($result['model'] ?? '')) ?><?php if (!empty($result['reasoning_tokens'])): ?> · reasoning <?= (int) $result['reasoning_tokens'] ?>토큰<?php endif; ?>
            </div>
        <?php endif; ?>

        <?php if ($isExam): ?>
            <div class="mt-6 rounded-2xl border border-indigo-500/20 bg-indigo-500/[0.06] p-5">
                <h3 class="text-indigo-300 text-sm font-medium mb-2 flex items-center gap-2"><i data-lucide="graduation-cap" class="w-4 h-4"></i> 성결대 조별 토론면접 팁</h3>
                <ul class="space-y-1.5 text-sm text-white/55">
                    <li>• 결론(주장) → 근거 → 사례 순으로 <b>짧고 명료하게</b> 말하세요. 8분 안에 여러 번 발언 기회를 잡는 게 유리합니다.</li>
                    <li>• 다른 조원의 말을 <b>경청·인정</b>한 뒤 반박하면 ‘성실성(태도)’ 점수에 유리합니다.</li>
                    <li>• 인적사항(이름·학교) 언급은 <b>감점</b> 요인입니다. 절대 말하지 마세요.</li>
                    <li>• 감정적 표현보다 <b>근거·수치·사례</b>로 ‘논리력’과 ‘언어구사능력’을 보여주세요.</li>
                </ul>
            </div>
        <?php endif; ?>

        <div class="mt-8 flex gap-2 pb-10">
            <a href="<?= $isExam ? '/exam' : '/setup' ?>" class="px-4 py-2 rounded-xl border border-white/[0.08] text-sm text-white/60 hover:text-white/90 hover:border-indigo-500/30 transition-all"><?= $isExam ? '새 면접' : '새 토론' ?></a>
            <a href="/" class="px-4 py-2 rounded-xl text-sm text-white/40 hover:text-white/70 transition-all">홈</a>
        </div>
    </div>
</div>
