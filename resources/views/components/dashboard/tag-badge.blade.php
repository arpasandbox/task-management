@props([
    'tag',
])

<span {{ $attributes->merge([
    'class' => 'inline-flex rounded-full px-2.5 py-0.5 text-xs font-medium text-white',
    'style' => "background-color: {$tag->color}",
]) }}>
    {{ $tag->name }}
</span>
