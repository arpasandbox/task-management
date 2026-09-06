@props([
    'task',
])

<div {{ $attributes->merge(['class' => 'flex min-w-0 overflow-hidden rounded-lg border border-brand-light/80 bg-white shadow-sm transition-colors hover:border-brand/50 hover:shadow-md']) }}>
    <div class="w-1 shrink-0 bg-brand" aria-hidden="true"></div>

    <div class="min-w-0 flex-1 p-4">
        <p @class([
            'truncate text-sm font-semibold text-ink',
            'text-ink/45 line-through' => $task->isDone(),
        ])>
            {{ $task->title }}
        </p>

        <div class="mt-3 flex items-center justify-between gap-3">
            <div class="flex min-w-0 items-center gap-2">
                @if ($task->assignee)
                    <x-dashboard.user-indicator :user="$task->assignee" size="sm" />
                @endif

                <x-dashboard.priority-badge :priority="$task->priority" />
            </div>

            @if ($task->formattedDueDate())
                <div class="flex shrink-0 items-center gap-1.5 text-xs text-ink/70">
                    <svg class="h-4 w-4 shrink-0 text-ink/40" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    <span>{{ $task->formattedDueDate() }}</span>
                </div>
            @endif
        </div>
    </div>
</div>
