<!DOCTYPE html>
<html lang="fa" dir="rtl" class="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'DailyFlow') }}</title>
    <link rel="icon" type="image/svg+xml" href="/favicon.svg">
    @vite(['resources/css/app.css', 'resources/js/main.ts'])
</head>
<body class="min-h-screen bg-surface-light dark:bg-surface-dark text-slate-800 dark:text-zinc-100 transition-colors duration-200 antialiased selection:bg-brand-500 selection:text-white">
    <div id="app"></div>
</body>
</html>
