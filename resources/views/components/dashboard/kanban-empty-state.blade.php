@props([
    'statusName',
])

@php
    $variant = match (strtolower($statusName)) {
        'done' => 'done',
        'in progress' => 'progress',
        default => 'todo',
    };
@endphp

<div {{ $attributes->merge(['class' => 'flex flex-col items-center px-4 py-8 text-center']) }}>
    @if ($variant === 'done')
        <svg class="mb-4 h-16 w-16 text-brand/25" fill="none" viewBox="0 0 64 64" aria-hidden="true">
            <circle cx="32" cy="32" r="28" stroke="currentColor" stroke-width="2" />
            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M20 33l8 8 16-18" />
        </svg>
    @elseif ($variant === 'progress')
        <svg class="mb-4 h-16 w-16 text-brand/25" fill="none" viewBox="0 0 64 64" aria-hidden="true">
            <rect x="14" y="10" width="28" height="36" rx="3" stroke="currentColor" stroke-width="2" />
            <rect x="22" y="18" width="28" height="36" rx="3" stroke="currentColor" stroke-width="2" />
            <path stroke="currentColor" stroke-linecap="round" stroke-width="2" d="M22 28h20M22 36h16M22 44h12" />
        </svg>
    @else
        <svg class="mb-4 h-16 w-16 text-brand/25" fill="none" viewBox="0 0 64 64" aria-hidden="true">
            <rect x="12" y="14" width="40" height="44" rx="4" stroke="currentColor" stroke-width="2" />
            <path stroke="currentColor" stroke-linecap="round" stroke-width="2" d="M22 26h20M22 34h20M22 42h12" />
        </svg>
    @endif

    <p class="text-sm font-medium text-ink/70">No tasks yet.</p>
    <p class="mt-1 text-xs text-ink/45">Drag and drop tasks here</p>
</div>
