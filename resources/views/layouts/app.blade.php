<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32x32.png') }}">
    <title>{{ config('app.name', 'TMS') }} — Dashboard</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="min-h-screen overflow-x-hidden bg-surface text-ink antialiased">
    <div class="flex min-h-screen flex-col md:flex-row">
        <x-dashboard.sidebar :active="$activeNav ?? 'board'" />

        <div class="flex min-h-screen min-w-0 flex-1 flex-col">
            <header class="sticky top-0 z-20 flex shrink-0 items-center justify-center border-b border-brand-light/60 bg-surface px-4 py-3 md:hidden">
                <img
                    src="{{ asset('images/arpa-logo.png') }}"
                    alt="ARPA"
                    class="h-7 w-auto max-w-[120px] object-contain"
                >
            </header>

            <main class="min-w-0 flex-1 px-4 py-4 pb-[calc(4.5rem+env(safe-area-inset-bottom))] sm:px-6 sm:py-6 md:px-10 md:py-8 md:pb-8">
                {{ $slot }}
            </main>
        </div>
    </div>

    <x-dashboard.mobile-nav :active="$activeNav ?? 'board'" />

    @livewireScripts
</body>
</html>
