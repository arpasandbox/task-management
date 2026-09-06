@props(['active' => 'board'])

@php
    $activeItemClasses = 'bg-brand/20 text-ink';
    $inactiveItemClasses = 'text-ink/70 hover:bg-brand-light/30 hover:text-ink';
@endphp

<nav
    aria-label="Mobile navigation"
    class="fixed inset-x-0 bottom-0 z-30 border-t border-brand-light/60 bg-surface md:hidden"
>
    <div class="mx-auto flex max-w-lg items-stretch justify-around px-2 pb-[max(0.5rem,env(safe-area-inset-bottom))] pt-2">
        <div class="flex min-w-0 flex-1 items-center justify-center px-2 py-2">
            <x-dashboard.user-indicator size="sm" />
        </div>

        <a
            href="{{ route('dashboard') }}"
            aria-label="Board"
            aria-current="{{ $active === 'board' ? 'page' : 'false' }}"
            @class([
                'flex min-w-0 flex-1 items-center justify-center rounded-md px-3 py-2.5 transition-colors',
                $active === 'board' ? $activeItemClasses : $inactiveItemClasses,
            ])
        >
            <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" @if($active === 'board') stroke-width="2.25" @else stroke-width="1.75" @endif aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 0v10" />
            </svg>
        </a>

        <a
            href="{{ route('tasks.list') }}"
            aria-label="List"
            aria-current="{{ $active === 'list' ? 'page' : 'false' }}"
            @class([
                'flex min-w-0 flex-1 items-center justify-center rounded-md px-3 py-2.5 transition-colors',
                $active === 'list' ? $activeItemClasses : $inactiveItemClasses,
            ])
        >
            <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" @if($active === 'list') stroke-width="2.25" @else stroke-width="1.75" @endif aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 10h16M4 14h16M4 18h16" />
            </svg>
        </a>

        <form method="POST" action="{{ route('logout') }}" class="flex min-w-0 flex-1">
            @csrf
            <button
                type="submit"
                aria-label="Log out"
                class="flex w-full items-center justify-center rounded-md px-3 py-2.5 text-ink/70 transition-colors hover:bg-brand-light/30 hover:text-ink"
            >
                <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                </svg>
            </button>
        </form>
    </div>
</nav>
