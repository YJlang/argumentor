<?php
/**
 * 기본 레이아웃 — LogicalAI 프로토타입의 다크 테마(#0a0a12) + 글래스모피즘.
 *
 * @var string      $content
 * @var string|null $title
 * @var bool|null   $fullHeight  채팅처럼 화면을 꽉 채우는 페이지면 true
 */
$fullHeight = $fullHeight ?? false;
?>
<!DOCTYPE html>
<html lang="ko" class="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($title ?? 'ArguMentor') ?></title>
    <meta name="description" content="AI 기반 토론면접 시뮬레이션 — AI와 실시간으로 토론하고 논리 약점을 분석받으세요.">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+KR:wght@400;500;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3/dist/cdn.min.js" defer></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        :root { color-scheme: dark; }
        body { font-family: 'Noto Sans KR', sans-serif; }
        @keyframes fadeUp { from { opacity: 0; transform: translateY(16px); } to { opacity: 1; transform: translateY(0); } }
        .fade-up { animation: fadeUp 0.6s ease both; }
        ::-webkit-scrollbar { width: 8px; height: 8px; }
        ::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.08); border-radius: 4px; }
    </style>
</head>
<body class="min-h-screen bg-[#0a0a12] text-white antialiased relative overflow-x-hidden">

    <!-- Glow -->
    <div class="fixed top-1/4 left-1/2 -translate-x-1/2 w-[600px] h-[600px] bg-indigo-600/10 rounded-full blur-[120px] pointer-events-none -z-10"></div>

    <div class="relative <?= $fullHeight ? 'h-screen flex flex-col' : 'min-h-screen flex flex-col' ?>">
        <!-- Nav -->
        <nav class="flex items-center justify-between px-6 py-4 md:px-12 shrink-0">
            <a href="/" class="flex items-center gap-2 group">
                <span class="w-8 h-8 rounded-lg bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center">
                    <i data-lucide="sparkles" class="w-4 h-4 text-white"></i>
                </span>
                <span class="text-white/90 font-medium group-hover:text-white transition-colors">ArguMentor</span>
            </a>
        </nav>

        <?= $content ?>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => window.lucide && lucide.createIcons());
    </script>
</body>
</html>
