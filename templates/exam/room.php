<?php
/**
 * 실전 성결대 토론면접 — 조별 토론장 (8분 타이머 + 다중 발화자 그룹 채팅).
 *
 * @var array<string,mixed> $debate
 * @var array<int,array{role:string,speaker:?string,content:string}> $messages
 * @var string $csrf
 * @var string|null $error
 */

use App\Domain\DebateOptions;

$id          = (int) $debate['id'];
$stanceLabel = DebateOptions::STANCES[$debate['user_stance']] ?? (string) $debate['user_stance'];

$initial = array_map(
    static fn (array $m): array => ['role' => $m['role'], 'speaker' => $m['speaker'], 'content' => $m['content']],
    $messages,
);
?>
<script>
window.__ROOM__ = {
    debateId: <?= $id ?>,
    csrf: <?= json_encode($csrf, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT) ?>,
    messages: <?= json_encode($initial, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS) ?>
};
function examRoom() {
    return {
        ...window.__ROOM__,
        input: '', typing: false, analyzing: false, left: 480, tick: null,
        fmt() { const m = Math.floor(this.left/60), s = this.left%60; return m + ':' + String(s).padStart(2,'0'); },
        startTimer() { this.tick = setInterval(() => { if (this.left > 0) { this.left--; } else { clearInterval(this.tick); this.analyze(); } }, 1000); },
        scrollDown() { this.$nextTick(() => { const el = this.$refs.scroll; if (el) el.scrollTop = el.scrollHeight; window.lucide && lucide.createIcons(); }); },
        async send() {
            const text = this.input.trim();
            if (!text || this.typing || this.analyzing) return;
            this.messages.push({ role: 'user', speaker: '나', content: text });
            this.input = ''; this.typing = true; this.scrollDown();
            try {
                const body = new URLSearchParams({ _csrf: this.csrf, debate_id: this.debateId, message: text });
                const res = await fetch('/exam/messages', { method: 'POST', body });
                const data = await res.json();
                if (data.ok && Array.isArray(data.turns)) {
                    data.turns.forEach(t => this.messages.push({ role: 'ai', speaker: t.speaker, content: t.content }));
                } else {
                    this.messages.push({ role: 'ai', speaker: '시스템', content: '⚠️ ' + (data.error || '오류가 발생했습니다.') });
                }
            } catch (e) {
                this.messages.push({ role: 'ai', speaker: '시스템', content: '⚠️ 네트워크 오류가 발생했습니다.' });
            }
            this.typing = false; this.scrollDown();
        },
        analyze() {
            if (this.analyzing) return;
            this.analyzing = true;
            if (this.tick) clearInterval(this.tick);
            this.$refs.analyzeForm.submit();
        },
    };
}
</script>

<div class="flex-1 flex flex-col min-h-0" x-data="examRoom()" x-init="startTimer(); scrollDown()">

    <!-- 헤더 -->
    <div class="flex items-center justify-between px-4 md:px-8 py-3 border-b border-white/[0.06] bg-white/[0.02] shrink-0">
        <div class="min-w-0">
            <h2 class="text-sm text-white/80 truncate max-w-[220px] md:max-w-[460px]"><?= e((string) $debate['topic']) ?></h2>
            <div class="text-xs text-white/30">성결대 조별 토론 · 나(<?= e($stanceLabel) ?>) · 학생 B · 학생 C</div>
        </div>
        <div class="flex items-center gap-3">
            <div class="text-lg font-bold tabular-nums" :class="left <= 60 ? 'text-rose-400' : 'text-white/80'" x-text="fmt()">8:00</div>
            <form method="post" action="/exam/analyze" x-ref="analyzeForm">
                <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
                <input type="hidden" name="debate_id" value="<?= $id ?>">
            </form>
            <button type="button" @click="analyze()" :disabled="analyzing || messages.length < 3"
                    class="flex items-center gap-1.5 px-4 py-2 rounded-xl bg-gradient-to-r from-indigo-600 to-purple-600 text-white text-sm transition-all hover:scale-[1.03] disabled:opacity-40 disabled:hover:scale-100">
                <template x-if="!analyzing"><span class="flex items-center gap-1.5"><i data-lucide="clipboard-check" class="w-3.5 h-3.5"></i> 채점 받기</span></template>
                <span x-show="analyzing" class="flex items-center gap-1.5"><span class="w-3.5 h-3.5 border-2 border-white/40 border-t-white rounded-full animate-spin"></span> 채점 중…</span>
            </button>
        </div>
    </div>

    <?php if (!empty($error)): ?>
        <div class="mx-4 md:mx-8 mt-3 rounded-xl border border-rose-500/30 bg-rose-500/10 text-rose-300 px-4 py-2 text-sm"><?= e($error) ?></div>
    <?php endif; ?>

    <!-- 메시지 -->
    <div x-ref="scroll" class="flex-1 overflow-y-auto px-4 md:px-8 py-6 space-y-4">
        <template x-for="(msg, i) in messages" :key="i">
            <div class="flex gap-3" :class="msg.role === 'user' ? 'justify-end' : 'justify-start'">
                <template x-if="msg.role === 'ai'">
                    <div class="w-8 h-8 rounded-xl flex items-center justify-center shrink-0 mt-1"
                         :class="msg.speaker === '사회자' ? 'bg-white/10' : 'bg-gradient-to-br from-indigo-500 to-purple-600'">
                        <i :data-lucide="msg.speaker === '사회자' ? 'mic' : 'user-round'" class="w-4 h-4 text-white"></i>
                    </div>
                </template>
                <div class="max-w-[85%] md:max-w-[600px]">
                    <div class="text-[11px] text-white/35 mb-1" :class="msg.role === 'user' ? 'text-right' : ''" x-text="msg.speaker"></div>
                    <div class="rounded-2xl px-5 py-3.5 text-sm whitespace-pre-line"
                         :class="msg.role === 'user' ? 'bg-indigo-600/20 border border-indigo-500/20 text-white/90' : (msg.speaker === '사회자' ? 'bg-white/[0.02] border border-white/[0.06] text-white/60 italic' : 'bg-white/[0.04] border border-white/[0.06] text-white/80')"
                         x-text="msg.content"></div>
                </div>
            </div>
        </template>

        <div x-show="typing" class="flex gap-3 items-start">
            <div class="w-8 h-8 rounded-xl bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center shrink-0">
                <i data-lucide="user-round" class="w-4 h-4 text-white"></i>
            </div>
            <div class="bg-white/[0.04] border border-white/[0.06] rounded-2xl px-5 py-4">
                <div class="flex gap-1.5">
                    <span class="w-2 h-2 rounded-full bg-indigo-400 animate-bounce" style="animation-delay:0s"></span>
                    <span class="w-2 h-2 rounded-full bg-indigo-400 animate-bounce" style="animation-delay:.15s"></span>
                    <span class="w-2 h-2 rounded-full bg-indigo-400 animate-bounce" style="animation-delay:.3s"></span>
                </div>
            </div>
        </div>
    </div>

    <!-- 입력 -->
    <div class="border-t border-white/[0.06] px-4 md:px-8 py-4 bg-white/[0.02] shrink-0">
        <div class="flex items-center gap-2 max-w-4xl mx-auto bg-white/[0.04] border border-white/[0.08] rounded-2xl px-4 py-2 focus-within:border-indigo-500/40 transition-colors">
            <input x-model="input" @keydown.enter="send()" :disabled="typing || analyzing"
                   placeholder="조별 토론에서 의견을 말하세요..."
                   class="flex-1 bg-transparent text-white placeholder-white/20 focus:outline-none py-1.5 text-sm disabled:opacity-50">
            <button type="button" @click="send()" :disabled="!input.trim() || typing || analyzing"
                    class="p-2 rounded-xl bg-indigo-600 hover:bg-indigo-500 disabled:opacity-30 transition-all">
                <i data-lucide="send" class="w-4 h-4 text-white"></i>
            </button>
        </div>
    </div>
</div>
