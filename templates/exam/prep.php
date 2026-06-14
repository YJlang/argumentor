<?php
/**
 * 실전 성결대 토론면접 — 준비 시간 (7분 카운트다운 + A4 메모).
 *
 * @var array<string,mixed> $debate
 * @var string $csrf
 */

use App\Domain\DebateOptions;

$id          = (int) $debate['id'];
$stanceLabel = DebateOptions::STANCES[$debate['user_stance']] ?? (string) $debate['user_stance'];
?>
<div class="flex-1 flex items-start justify-center px-6 py-4">
    <form method="post" action="/exam/start"
          x-data="{ left: 420, submitting: false, tick: null,
                    fmt() { const m = Math.floor(this.left/60), s = this.left%60; return m + ':' + String(s).padStart(2,'0'); },
                    start() { this.tick = setInterval(() => { if (this.left > 0) { this.left--; } else { clearInterval(this.tick); this.go(); } }, 1000); },
                    go() { if (this.submitting) return; this.submitting = true; this.$el.submit(); } }"
          x-init="start()"
          class="w-full max-w-2xl space-y-5 fade-up">

        <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
        <input type="hidden" name="debate_id" value="<?= $id ?>">

        <div class="text-center">
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/[0.04] border border-white/[0.06] text-xs text-white/50 mb-3">준비 시간</div>
            <div class="text-5xl font-bold tabular-nums"
                 :class="left <= 60 ? 'text-rose-400' : 'text-white'" x-text="fmt()">7:00</div>
            <p class="text-white/40 text-sm mt-2">7분 동안 메모를 정리하세요. 실제 시험처럼 자료 검색은 권장하지 않습니다.</p>
        </div>

        <div class="rounded-2xl bg-white/[0.03] border border-white/[0.06] p-5">
            <div class="text-xs text-white/40 mb-1">토론 주제</div>
            <div class="text-white/90"><?= e((string) $debate['topic']) ?></div>
            <div class="text-xs text-white/40 mt-2">나의 입장: <span class="text-indigo-300"><?= e($stanceLabel) ?></span></div>
        </div>

        <div>
            <label class="text-sm text-white/60">A4 메모 (논거·근거·예상 반론)</label>
            <textarea name="memo" rows="10"
                      placeholder="• 핵심 주장:&#10;• 근거 1:&#10;• 근거 2:&#10;• 예상 반론과 대응:"
                      class="mt-2 w-full px-5 py-4 rounded-2xl bg-white/[0.04] border border-white/[0.08] text-white placeholder-white/20 focus:outline-none focus:border-indigo-500/50 transition-all leading-relaxed"></textarea>
        </div>

        <button type="button" @click="go()" :disabled="submitting"
                class="w-full py-4 rounded-2xl bg-gradient-to-r from-indigo-600 to-purple-600 text-white shadow-lg shadow-indigo-500/25 hover:scale-[1.01] transition-all flex items-center justify-center gap-2">
            <span x-show="!submitting" class="flex items-center gap-2"><i data-lucide="play" class="w-4 h-4"></i> 지금 토론 시작 (8분)</span>
            <span x-show="submitting" class="flex items-center gap-2"><span class="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin"></span> 입장 중…</span>
        </button>
    </form>
</div>
<script>document.addEventListener('alpine:initialized', () => window.lucide && lucide.createIcons());</script>
