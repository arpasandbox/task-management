@props([
    'status',
])

<span
    {{ $attributes->merge(['class' => 'inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-medium']) }}
    style="background-color: {{ $status->color }}22; color: {{ $status->color }}; border: 1px solid {{ $status->color }}44;"
>
    <span class="h-2 w-2 rounded-full border-2" style="border-color: {{ $status->color }}; background-color: transparent;"></span>
    {{ $status->name }}
</span>
