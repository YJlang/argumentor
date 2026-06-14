<?php
/**
 * 404 — 독립 HTML (레이아웃 없이 렌더). 다크 테마.
 */
?>
<!DOCTYPE html>
<html lang="ko" class="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 — ArguMentor</title>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+KR:wght@400;500;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>:root{color-scheme:dark} body{font-family:'Noto Sans KR',sans-serif}</style>
</head>
<body class="min-h-screen bg-[#0a0a12] text-white relative overflow-hidden">
    <div class="fixed top-1/3 left-1/2 -translate-x-1/2 w-[500px] h-[500px] bg-indigo-600/10 rounded-full blur-[120px] pointer-events-none"></div>
    <div class="min-h-screen flex items-center justify-center px-6 text-center relative">
        <div>
            <h1 class="text-7xl font-bold bg-gradient-to-r from-white via-indigo-200 to-purple-300 bg-clip-text text-transparent mb-4">404</h1>
            <p class="text-white/50 mb-8">요청하신 페이지를 찾을 수 없습니다.</p>
            <a href="/" class="inline-flex items-center gap-2 px-6 py-3 rounded-2xl bg-gradient-to-r from-indigo-600 to-purple-600 text-white shadow-lg shadow-indigo-500/25 hover:scale-[1.03] transition-all">홈으로</a>
        </div>
    </div>
</body>
</html>
