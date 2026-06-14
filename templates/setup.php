<?php
/**
 * 토론 설정 (LogicalAI/DebateSetup.tsx 디자인 이식).
 *
 * @var array<string,string> $styles
 * @var array<string,string> $languages
 * @var array<string,string> $errors
 * @var array<string,string> $old
 * @var string               $csrf
 */
$sampleTopics = [
    '인공지능이 인간의 일자리를 대체해야 하는가',
    '사형제도는 폐지되어야 하는가',
    '기본소득제는 도입되어야 하는가',
    '소셜 미디어는 사회에 해로운가',
];
$styleMeta = [
    'logical'    => ['icon' => 'message-square', 'desc' => '체계적이고 구조화된 반박'],
    'aggressive' => ['icon' => 'swords', 'desc' => '날카롭고 직접적인 반박'],
    'academic'   => ['icon' => 'graduation-cap', 'desc' => '학문적 근거 기반 반박'],
];
$init = htmlspecialchars((string) json_encode([
    'topic'      => $old['topic'] ?? '',
    'stance'     => $old['user_stance'] ?? '',
    'style'      => $old['debate_style'] ?? 'logical',
    'submitting' => false,
], JSON_UNESCAPED_UNICODE), ENT_QUOTES, 'UTF-8');
?>
<div class="flex-1 flex items-start justify-center px-6 py-4">
    <form method="post" action="/debates" x-data="<?= $init ?>"
          @submit="if (!(topic.trim() && stance)) { $event.preventDefault(); return; } submitting = true"
          class="w-full max-w-2xl space-y-8 fade-up">

        <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
        <input type="hidden" name="user_stance" :value="stance">
        <input type="hidden" name="debate_style" :value="style">

        <div class="text-center mb-2">
            <h1 class="text-3xl font-bold bg-gradient-to-r from-white to-white/70 bg-clip-text text-transparent mb-2">토론 준비</h1>
            <p class="text-white/40 text-sm">주제와 입장을 선택하고 AI와 토론을 시작하세요</p>
        </div>

        <?php if (isset($errors['_general'])): ?>
            <div class="rounded-2xl border border-rose-500/30 bg-rose-500/10 text-rose-300 px-5 py-3 text-sm"><?= e($errors['_general']) ?></div>
        <?php endif; ?>

        <!-- 주제 -->
        <div class="space-y-3">
            <label class="text-sm text-white/60">토론 주제 입력</label>
            <input type="text" name="topic" maxlength="500" x-model="topic"
                   placeholder="예: 인공지능이 인간의 일자리를 대체해야 하는가"
                   class="w-full px-5 py-4 rounded-2xl bg-white/[0.04] border <?= isset($errors['topic']) ? 'border-rose-500/40' : 'border-white/[0.08]' ?> text-white placeholder-white/20 focus:outline-none focus:border-indigo-500/50 focus:bg-white/[0.06] transition-all">
            <?php if (isset($errors['topic'])): ?>
                <p class="text-rose-400 text-xs"><?= e($errors['topic']) ?></p>
            <?php endif; ?>
            <div class="flex flex-wrap gap-2">
                <?php foreach ($sampleTopics as $t): ?>
                    <button type="button" @click="topic = <?= htmlspecialchars((string) json_encode($t, JSON_UNESCAPED_UNICODE), ENT_QUOTES, 'UTF-8') ?>"
                            class="text-xs px-3 py-1.5 rounded-full bg-white/[0.04] border border-white/[0.06] text-white/40 hover:text-white/70 hover:border-indigo-500/30 transition-all">
                        <?= e($t) ?>
                    </button>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- 입장 -->
        <div class="space-y-3">
            <label class="text-sm text-white/60">나의 입장 선택</label>
            <div class="grid grid-cols-2 gap-4">
                <button type="button" @click="stance='for'"
                        :class="stance==='for' ? 'bg-emerald-500/10 border-emerald-500/40' : 'bg-white/[0.02] border-white/[0.06] hover:border-white/[0.12]'"
                        class="p-5 rounded-2xl border-2 transition-all">
                    <span :class="stance==='for' ? 'text-white' : 'text-white/50'" class="text-lg">찬성</span>
                </button>
                <button type="button" @click="stance='against'"
                        :class="stance==='against' ? 'bg-rose-500/10 border-rose-500/40' : 'bg-white/[0.02] border-white/[0.06] hover:border-white/[0.12]'"
                        class="p-5 rounded-2xl border-2 transition-all">
                    <span :class="stance==='against' ? 'text-white' : 'text-white/50'" class="text-lg">반대</span>
                </button>
            </div>
            <?php if (isset($errors['user_stance'])): ?>
                <p class="text-rose-400 text-xs"><?= e($errors['user_stance']) ?></p>
            <?php endif; ?>
        </div>

        <!-- 스타일 -->
        <div class="space-y-3">
            <label class="text-sm text-white/60">토론 스타일 선택 <span class="text-white/30">(선택)</span></label>
            <div class="grid grid-cols-3 gap-3">
                <?php foreach ($styles as $key => $label): ?>
                    <?php $meta = $styleMeta[$key] ?? ['icon' => 'message-square', 'desc' => '']; ?>
                    <button type="button" @click="style=<?= htmlspecialchars((string) json_encode($key), ENT_QUOTES, 'UTF-8') ?>"
                            :class="style==='<?= e($key) ?>' ? 'bg-indigo-500/10 border-indigo-500/40' : 'bg-white/[0.02] border-white/[0.06] hover:border-white/[0.12]'"
                            class="p-4 rounded-2xl border text-left transition-all">
                        <i data-lucide="<?= e($meta['icon']) ?>" class="w-5 h-5 mb-2"
                           :class="style==='<?= e($key) ?>' ? 'text-indigo-400' : 'text-white/30'"></i>
                        <div class="text-sm" :class="style==='<?= e($key) ?>' ? 'text-white' : 'text-white/50'"><?= e($label) ?></div>
                        <div class="text-xs text-white/30 mt-1"><?= e($meta['desc']) ?></div>
                    </button>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- 언어 -->
        <div class="space-y-3">
            <label class="text-sm text-white/60">응답 언어</label>
            <select name="output_language"
                    class="px-4 py-3 rounded-2xl bg-white/[0.04] border border-white/[0.08] text-white focus:outline-none focus:border-indigo-500/50 transition-all">
                <?php foreach ($languages as $key => $label): ?>
                    <option value="<?= e($key) ?>" class="bg-[#0a0a12]" <?= ($old['output_language'] ?? 'ko') === $key ? 'selected' : '' ?>><?= e($label) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- CTA -->
        <button type="submit"
                :disabled="!(topic.trim() && stance) || submitting"
                :class="(topic.trim() && stance) ? 'bg-gradient-to-r from-indigo-600 to-purple-600 text-white shadow-lg shadow-indigo-500/25 hover:scale-[1.01]' : 'bg-white/[0.04] text-white/20 cursor-not-allowed'"
                class="w-full py-4 rounded-2xl flex items-center justify-center gap-2 transition-all">
            <template x-if="!submitting">
                <span class="flex items-center gap-2">
                    <i data-lucide="zap" class="w-4 h-4"></i> AI 토론 시작 <i data-lucide="arrow-right" class="w-4 h-4"></i>
                </span>
            </template>
            <span x-show="submitting" class="flex items-center gap-2">
                <span class="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin"></span>
                토론을 준비 중…
            </span>
        </button>
    </form>
</div>

<script>document.addEventListener('alpine:initialized', () => window.lucide && lucide.createIcons());</script>
