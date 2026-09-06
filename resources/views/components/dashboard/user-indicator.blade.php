@props([
    'user' => auth()->user(),
    'size' => 'md',
])

@php
    $sizeClasses = match ($size) {
        'sm' => 'h-8 w-8 text-[11px]',
        'lg' => 'h-[60px] w-[60px] text-base',
        default => 'h-9 w-9 text-xs',
    };
@endphp

@if ($user)
    <span
        {{ $attributes->merge(['class' => "tms-circle shrink-0 bg-brand font-semibold text-white {$sizeClasses}"]) }}
        aria-label="{{ $user->name }}"
    >
        {{ $user->initials() }}
    </span>
@endif
