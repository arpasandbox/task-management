@props([
    'task',
])

@php
    $gridCols = 'grid-cols-[minmax(0,2.2fr)_minmax(0,1.2fr)_minmax(0,1fr)_minmax(0,0.9fr)_minmax(0,0.9fr)_2.5rem]';
@endphp

<div
    {{ $attributes->merge(['class' => 'group relative flex cursor-pointer overflow-hidden rounded-lg border border-brand-light/80 bg-white transition-colors hover:border-brand/50 hover:shadow-sm']) }}
    wire:key="task-row-{{ $task->id }}"
    wire:click="openEditModal({{ $task->id }})"
    role="button"
    tabindex="0"
>
    <div class="w-1 shrink-0 bg-brand" aria-hidden="true"></div>

    <div @class(['grid min-w-0 flex-1 gap-x-4 gap-y-2 px-4 py-4', $gridCols])>
        <div class="min-w-0">
            <p @class([
                'truncate text-sm font-semibold text-ink',
                'text-ink/45 line-through' => $task->isDone(),
            ])>
                {{ $task->title }}
            </p>
            @if ($task->description)
                <p class="mt-1 line-clamp-2 text-xs text-ink/55">{{ $task->description }}</p>
            @endif
        </div>

        <div class="flex min-w-0 items-center gap-2 self-center">
            @if ($task->assignee)
                <x-dashboard.user-indicator :user="$task->assignee" size="sm" />
                <span class="truncate text-sm text-ink/80">{{ $task->assignee->name }}</span>
            @else
                <span class="text-sm text-ink/45">Unassigned</span>
            @endif
        </div>

        <div class="flex items-center gap-1.5 self-center text-sm text-ink/70">
            <svg class="h-4 w-4 shrink-0 text-ink/40" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
            </svg>
            <span class="truncate">{{ $task->formattedDueDate() ?? '—' }}</span>
        </div>

        <div class="self-center">
            <x-dashboard.priority-badge :priority="$task->priority" />
        </div>

        <div class="self-center">
            <x-dashboard.status-badge :status="$task->status" />
        </div>

        <div class="flex items-center justify-end self-center">
            <button
                type="button"
                wire:click.stop="openEditModal({{ $task->id }})"
                class="rounded-md p-1 text-ink/40 transition-colors hover:bg-brand-light/30 hover:text-ink"
                aria-label="Task actions"
            >
                <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path d="M12 8c1.1 0 2-.9 2-2s-.9-2-2-2-2 .9-2 2 .9 2 2 2zm0 2c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2zm0 6c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2z" />
                </svg>
            </button>
        </div>
    </div>
</div>
