<?php
/**
 * 채팅형 실시간 토론 (LogicalAI/Simulation.tsx 디자인 이식).
 *
 * @var array<string,mixed> $debate
 * @var array<int,array{role:string,content:string}> $messages
 * @var string $csrf
 * @var string|null $error
 */

use App\Domain\DebateOptions;

$id          = (int) $debate['id'];
$stance      = (string) $debate['user_stance'];
$stanceLabel = DebateOptions::STANCES[$stance] ?? $stance;
$aiLabel     = DebateOptions::opposingStanceLabel($stance);
$styleLabel  = DebateOptions::STYLES[$debate['debate_style']] ?? (string) $debate['debate_style'];
$myColor     = $stance === 'for' ? 'text-emerald-400' : 'text-rose-400';
$aiColor     = $stance === 'for' ? 'text-rose-400' : 'text-emerald-400';

$initial = array_map(
    static fn (array $m): array => ['role' => $m['role'], 'content' => $m['content']],
    $messages,
);
?>
<script>
window.__CHAT__ = {
    debateId: <?= $id ?>,
    csrf: <?= json_encode($csrf, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT) ?>,
    messages: <?= json_encode($initial, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS) ?>
};
function chat() {
    return {
        ...window.__CHAT__,
        input: '',
        typing: false,
        analyzing: false,
        scrollDown() { this.$nextTick(() => { const el = this.$refs.scroll; if (el) el.scrollTop = el.scrollHeight; window.lucide && lucide.createIcons(); }); },
        async send(text) {
            text = (text ?? this.input).trim();
            if (!text || this.typing) return;
            this.messages.push({ role: 'user', content: text });
            this.input = '';
            this.typing = true;
            this.scrollDown();
            try {
                const body = new URLSearchParams({ _csrf: this.csrf, debate_id: this.debateId, message: text });
                const res = await fetch('/messages', { method: 'POST', body });
                const data = await res.json();
                this.messages.push({ role: 'ai', content: data.ok ? data.content : ('⚠️ ' + (data.error || '오류가 발생했습니다.')) });
            } catch (e) {
                this.messages.push({ role: 'ai', content: '⚠️ 네트워크 오류가 발생했습니다.' });
            }
            this.typing = false;
            this.scrollDown();
        },
        nextRebuttal() { if (!this.typing) this.send('제 주장의 약점을 한 번 더 짚어 반박해 주세요.'); },
    };
}
</script>

<div class="flex-1 flex flex-col min-h-0" x-data="chat()" x-init="scrollDown()">

    <!-- 헤더 -->
    <div class="flex items-center justify-between px-4 md:px-8 py-3 border-b border-white/[0.06] bg-white/[0.02] shrink-0">
        <div class="flex items-center gap-3 min-w-0">
            <a href="/setup" class="p-2 rounded-xl hover:bg-white/5 transition-colors">
                <i data-lucide="arrow-left" class="w-4 h-4 text-white/50"></i>
            </a>
            <div class="min-w-0">
                <h2 class="text-sm text-white/80 truncate max-w-[220px] md:max-w-[480px]"><?= e((string) $debate['topic']) ?></h2>
                <div class="flex items-center gap-2 text-xs text-white/30">
                    <span class="<?= $myColor ?>">나: <?= e($stanceLabel) ?></span><span>•</span>
                    <span class="<?= $aiColor ?>">AI: <?= e($aiLabel) ?></span><span>•</span>
                    <span><?= e($styleLabel) ?></span>
                </div>
            </div>
        </div>
        <form method="post" action="/analyze" @submit="analyzing = true">
            <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
            <input type="hidden" name="debate_id" value="<?= $id ?>">
            <button type="submit" :disabled="analyzing || messages.length < 3"
                    class="flex items-center gap-1.5 px-4 py-2 rounded-xl bg-gradient-to-r from-indigo-600 to-purple-600 text-white text-sm transition-all hover:scale-[1.03] disabled:opacity-40 disabled:hover:scale-100">
                <template x-if="!analyzing"><span class="flex items-center gap-1.5"><i data-lucide="bar-chart-3" class="w-3.5 h-3.5"></i> 분석 보기</span></template>
                <span x-show="analyzing" class="flex items-center gap-1.5">
                    <span class="w-3.5 h-3.5 border-2 border-white/40 border-t-white rounded-full animate-spin"></span> 분석 중…
                </span>
            </button>
        </form>
    </div>

    <?php if (!empty($error)): ?>
        <div class="mx-4 md:mx-8 mt-3 rounded-xl border border-rose-500/30 bg-rose-500/10 text-rose-300 px-4 py-2 text-sm"><?= e($error) ?></div>
    <?php endif; ?>

    <!-- 메시지 -->
    <div x-ref="scroll" class="flex-1 overflow-y-auto px-4 md:px-8 py-6 space-y-4">
        <template x-for="(msg, i) in messages" :key="i">
            <div class="flex gap-3" :class="msg.role === 'user' ? 'justify-end' : 'justify-start'">
                <template x-if="msg.role === 'ai'">
                    <div class="w-8 h-8 rounded-xl bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center shrink-0 mt-1">
                        <i data-lucide="bot" class="w-4 h-4 text-white"></i>
                    </div>
                </template>
                <div class="max-w-[85%] md:max-w-[600px] rounded-2xl px-5 py-4 text-sm whitespace-pre-line"
                     :class="msg.role === 'user' ? 'bg-indigo-600/20 border border-indigo-500/20 text-white/90' : 'bg-white/[0.04] border border-white/[0.06] text-white/80'"
                     x-text="msg.content"></div>
                <template x-if="msg.role === 'user'">
                    <div class="w-8 h-8 rounded-xl bg-white/10 flex items-center justify-center shrink-0 mt-1">
                        <i data-lucide="user" class="w-4 h-4 text-white/60"></i>
                    </div>
                </template>
            </div>
        </template>

        <!-- 타이핑 -->
        <div x-show="typing" class="flex gap-3 items-start">
            <div class="w-8 h-8 rounded-xl bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center shrink-0">
                <i data-lucide="bot" class="w-4 h-4 text-white"></i>
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
        <div class="flex items-center gap-3 max-w-4xl mx-auto">
            <button type="button" @click="nextRebuttal()" :disabled="typing || messages.length < 2"
                    class="hidden md:flex items-center gap-1.5 px-4 py-3 rounded-xl border border-white/[0.08] text-sm text-white/40 hover:text-white/70 hover:border-indigo-500/30 transition-all whitespace-nowrap disabled:opacity-30">
                다음 반박 보기 <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i>
            </button>
            <div class="flex-1 flex items-center gap-2 bg-white/[0.04] border border-white/[0.08] rounded-2xl px-4 py-2 focus-within:border-indigo-500/40 transition-colors">
                <input x-model="input" @keydown.enter="send()" :disabled="typing"
                       placeholder="나의 주장을 입력하세요..."
                       class="flex-1 bg-transparent text-white placeholder-white/20 focus:outline-none py-1.5 text-sm disabled:opacity-50">
                <button type="button" @click="send()" :disabled="!input.trim() || typing"
                        class="p-2 rounded-xl bg-indigo-600 hover:bg-indigo-500 disabled:opacity-30 transition-all">
                    <i data-lucide="send" class="w-4 h-4 text-white"></i>
                </button>
            </div>
        </div>
    </div>
</div>
