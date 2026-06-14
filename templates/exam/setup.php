<?php
/**
 * 실전 성결대 토론면접 — 설정 (주제 추첨/선택 + 입장).
 *
 * @var array<int,string>    $topics
 * @var string               $drawn
 * @var array<string,string> $languages
 * @var array<string,string> $errors
 * @var array<string,string> $old
 * @var string               $csrf
 */
$init = htmlspecialchars((string) json_encode([
    'topic'  => $old['topic'] ?? $drawn,
    'stance' => $old['user_stance'] ?? '',
], JSON_UNESCAPED_UNICODE), ENT_QUOTES, 'UTF-8');
$topicsJson = htmlspecialchars((string) json_encode(array_values($topics), JSON_UNESCAPED_UNICODE), ENT_QUOTES, 'UTF-8');
?>
<div class="flex-1 flex items-start justify-center px-6 py-4">
    <form method="post" action="/exam"
          x-data="Object.assign(<?= $init ?>, { topics: <?= $topicsJson ?>, submitting: false, draw() { this.topic = this.topics[Math.floor(Math.random()*this.topics.length)]; } })"
          @submit="if (!(topic.trim() && stance)) { $event.preventDefault(); return; } submitting = true"
          class="w-full max-w-2xl space-y-7 fade-up">

        <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
        <input type="hidden" name="topic" :value="topic">
        <input type="hidden" name="user_stance" :value="stance">

        <div class="text-center">
            <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full border border-indigo-500/30 bg-indigo-500/10 text-indigo-300 mb-4 text-sm">
                <i data-lucide="graduation-cap" class="w-3.5 h-3.5"></i> 실전 성결대 토론면접
            </div>
            <h1 class="text-3xl font-bold bg-gradient-to-r from-white to-white/70 bg-clip-text text-transparent mb-2">조별 토론면접 준비</h1>
            <p class="text-white/40 text-sm">SKU창의적인재전형 방식 — 주제 추첨 → 준비 7분 → 조별 토론 8분 → 공식 4항목 채점</p>
        </div>

        <!-- 형식 안내 -->
        <div class="grid grid-cols-3 gap-3 text-center">
            <?php
            $steps = [
                ['clock', '준비 7분', 'A4 메모 작성'],
                ['users', '조별 토론 8분', '나 + AI 조원 2명'],
                ['clipboard-check', '공식 4항목', '이해력·논리력·언어·태도'],
            ];
            foreach ($steps as [$icon, $t, $d]):
            ?>
                <div class="p-4 rounded-2xl bg-white/[0.03] border border-white/[0.06]">
                    <i data-lucide="<?= e($icon) ?>" class="w-5 h-5 text-indigo-400 mx-auto mb-2"></i>
                    <div class="text-sm text-white/80"><?= e($t) ?></div>
                    <div class="text-xs text-white/35 mt-1"><?= e($d) ?></div>
                </div>
            <?php endforeach; ?>
        </div>

        <?php if (isset($errors['_general'])): ?>
            <div class="rounded-2xl border border-rose-500/30 bg-rose-500/10 text-rose-300 px-5 py-3 text-sm"><?= e($errors['_general']) ?></div>
        <?php endif; ?>

        <!-- 주제 -->
        <div class="space-y-3">
            <div class="flex items-center justify-between">
                <label class="text-sm text-white/60">토론 주제</label>
                <button type="button" @click="draw()" class="inline-flex items-center gap-1.5 text-xs px-3 py-1.5 rounded-full bg-indigo-500/10 border border-indigo-500/30 text-indigo-300 hover:bg-indigo-500/20 transition-all">
                    <i data-lucide="shuffle" class="w-3.5 h-3.5"></i> 주제 추첨
                </button>
            </div>
            <div class="px-5 py-4 rounded-2xl bg-white/[0.04] border <?= isset($errors['topic']) ? 'border-rose-500/40' : 'border-white/[0.08]' ?> text-white" x-text="topic || '주제를 추첨하거나 아래에서 선택하세요'"></div>
            <?php if (isset($errors['topic'])): ?><p class="text-rose-400 text-xs"><?= e($errors['topic']) ?></p><?php endif; ?>
            <div class="flex flex-wrap gap-2">
                <?php foreach ($topics as $t): ?>
                    <button type="button" @click="topic = <?= htmlspecialchars((string) json_encode($t, JSON_UNESCAPED_UNICODE), ENT_QUOTES, 'UTF-8') ?>"
                            :class="topic === <?= htmlspecialchars((string) json_encode($t, JSON_UNESCAPED_UNICODE), ENT_QUOTES, 'UTF-8') ?> ? 'border-indigo-500/50 text-white/80' : 'border-white/[0.06] text-white/40'"
                            class="text-xs px-3 py-1.5 rounded-full bg-white/[0.04] border hover:text-white/70 transition-all"><?= e($t) ?></button>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- 입장 -->
        <div class="space-y-3">
            <label class="text-sm text-white/60">나의 입장</label>
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
            <?php if (isset($errors['user_stance'])): ?><p class="text-rose-400 text-xs"><?= e($errors['user_stance']) ?></p><?php endif; ?>
        </div>

        <!-- 언어 -->
        <div class="space-y-3">
            <label class="text-sm text-white/60">응답 언어</label>
            <select name="output_language" class="px-4 py-3 rounded-2xl bg-white/[0.04] border border-white/[0.08] text-white focus:outline-none focus:border-indigo-500/50 transition-all">
                <?php foreach ($languages as $key => $label): ?>
                    <option value="<?= e($key) ?>" class="bg-[#0a0a12]" <?= ($old['output_language'] ?? 'ko') === $key ? 'selected' : '' ?>><?= e($label) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <button type="submit" :disabled="!(topic.trim() && stance) || submitting"
                :class="(topic.trim() && stance) ? 'bg-gradient-to-r from-indigo-600 to-purple-600 text-white shadow-lg shadow-indigo-500/25 hover:scale-[1.01]' : 'bg-white/[0.04] text-white/20 cursor-not-allowed'"
                class="w-full py-4 rounded-2xl flex items-center justify-center gap-2 transition-all">
            <span x-show="!submitting" class="flex items-center gap-2"><i data-lucide="play" class="w-4 h-4"></i> 준비 시간 시작 (7분)</span>
            <span x-show="submitting" class="flex items-center gap-2"><span class="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin"></span> 입장 중…</span>
        </button>
        <a href="/" class="block text-center text-xs text-white/30 hover:text-white/50">← 홈으로</a>
    </form>
</div>
<script>document.addEventListener('alpine:initialized', () => window.lucide && lucide.createIcons());</script>
