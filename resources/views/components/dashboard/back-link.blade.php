@props([
    'href',
    'label',
])

<a {{ $attributes->merge(['href' => $href, 'class' => 'tms-link inline-flex items-center gap-1.5 text-sm']) }} wire:navigate>
    <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
    </svg>
    {{ $label }}
</a>
