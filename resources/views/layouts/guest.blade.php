<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32x32.png') }}">
    <title>{{ config('app.name', 'TMS') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="min-h-screen bg-surface text-ink antialiased">
    <div class="flex min-h-screen items-center justify-center px-4 py-12">
        <div class="w-full max-w-md">
            <div class="rounded-xl border border-brand-light bg-white p-6 shadow-sm">
                <div class="mb-16 text-center">
                    <img
                        src="{{ asset('images/arpa-logo.png') }}"
                        alt="ARPA"
                        class="mx-auto h-8 w-auto"
                    >
                </div>

                {{ $slot }}
            </div>
        </div>
    </div>
    @livewireScripts
</body>
</html>
