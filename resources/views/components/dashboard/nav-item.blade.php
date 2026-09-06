@props([
    'label',
    'active' => false,
    'href' => null,
])

@php
    $itemClasses = $active
        ? 'bg-brand/20 text-ink'
        : 'text-ink/70 hover:bg-brand-light/30 hover:text-ink';
@endphp

<div class="group relative">
    @if ($href)
        <a
            href="{{ $href }}"
            @class([
                'flex items-center gap-3 rounded-md px-2 py-2 text-sm font-medium transition-colors',
                $itemClasses,
            ])
            :class="tucked ? 'justify-center' : ''"
        >
            {!! $icon !!}

            <span x-show="!tucked" x-cloak>{{ $label }}</span>

            <span
                x-show="tucked"
                role="tooltip"
                class="pointer-events-none absolute left-[calc(100%+0.5rem)] top-1/2 z-50 -translate-y-1/2 whitespace-nowrap rounded-md bg-ink px-2 py-1 text-xs font-medium text-white opacity-0 shadow-sm transition-opacity group-hover:opacity-100 group-focus-within:opacity-100"
                x-cloak
            >
                {{ $label }}
            </span>
        </a>
    @else
        <span
            @class([
                'flex items-center gap-3 rounded-md px-2 py-2 text-sm font-medium',
                $itemClasses,
            ])
            :class="tucked ? 'justify-center' : ''"
        >
            {!! $icon !!}

            <span x-show="!tucked" x-cloak>{{ $label }}</span>

            <span
                x-show="tucked"
                role="tooltip"
                class="pointer-events-none absolute left-[calc(100%+0.5rem)] top-1/2 z-50 -translate-y-1/2 whitespace-nowrap rounded-md bg-ink px-2 py-1 text-xs font-medium text-white opacity-0 shadow-sm transition-opacity group-hover:opacity-100"
                x-cloak
            >
                {{ $label }}
            </span>
        </span>
    @endif
</div>
