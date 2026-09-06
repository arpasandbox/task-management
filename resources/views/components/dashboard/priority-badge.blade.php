@props([
    'priority',
])

@php
    $classes = match ($priority) {
        'high' => 'bg-brand/15 text-brand border-brand/30',
        'low' => 'border border-brand-light bg-surface text-ink/70',
        default => 'bg-brand-light/40 text-ink border-brand-light/60',
    };

    $label = match ($priority) {
        'high' => 'High',
        'low' => 'Low',
        default => 'Medium',
    };
@endphp

<span {{ $attributes->merge(['class' => "inline-flex items-center gap-1 rounded-full border px-2.5 py-1 text-xs font-medium {$classes}"]) }}>
    @if ($priority === 'high')
        <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M5 15l7-7 7 7" />
        </svg>
    @endif
    {{ $label }}
</span>
