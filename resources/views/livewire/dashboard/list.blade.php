<div>
    {{-- Page header --}}
    <header class="mb-6 flex items-center justify-between gap-4 md:mb-8">
        <h1 class="min-w-0 truncate text-2xl font-bold tracking-tight text-ink sm:text-3xl lg:text-4xl">{{ $workspaceName }}</h1>

        <div class="flex shrink-0 items-center gap-3">
            <button
                type="button"
                wire:click="openCreateModal"
                class="tms-btn-primary inline-flex items-center gap-2 px-4 py-2.5"
            >
                <span class="tms-circle size-6 bg-white/25">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                    </svg>
                </span>
                Add task
            </button>

            @livewire('dashboard.notification-bell')
        </div>
    </header>

    {{-- Tasks card --}}
    <section class="rounded-2xl border border-brand-light/60 bg-white p-4 shadow-sm sm:p-6">
        {{-- Card header --}}
        <div class="mb-6 flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
            <div class="flex min-w-0 items-start gap-3">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-brand/15 text-brand">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 10h16M4 14h16M4 18h16" />
                    </svg>
                </div>
                <div>
                    <h2 class="text-lg font-semibold text-ink">Tasks</h2>
                    <p class="text-sm text-ink/55">Manage and track all tasks in this workspace.</p>
                </div>
            </div>

            <div class="flex w-full flex-col gap-3 sm:flex-row sm:items-center lg:w-auto">
                <label class="relative min-w-0 flex-1 sm:min-w-[220px] lg:min-w-[260px]">
                    <span class="sr-only">Search tasks</span>
                    <svg class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-ink/40" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                    <input
                        type="search"
                        wire:model.live.debounce.300ms="search"
                        placeholder="Search tasks..."
                        class="tms-input pl-9"
                    >
                </label>

                <label class="shrink-0">
                    <span class="sr-only">Sort tasks</span>
                    <select wire:model.live="sortBy" class="tms-input min-w-[10rem]">
                        <option value="due_date">Sort: Due date</option>
                        <option value="title">Sort: Title</option>
                        <option value="assignee">Sort: Assignee</option>
                        <option value="priority">Sort: Priority</option>
                        <option value="status">Sort: Status</option>
                    </select>
                </label>
            </div>
        </div>

        @if ($tasks->isNotEmpty())
            {{-- Desktop column headers --}}
            <div class="mb-3 hidden px-4 lg:grid lg:grid-cols-[minmax(0,2.2fr)_minmax(0,1.2fr)_minmax(0,1fr)_minmax(0,0.9fr)_minmax(0,0.9fr)_2.5rem] lg:gap-x-4 lg:pl-5">
                @foreach ([
                    'title' => 'Task',
                    'assignee' => 'Assignee',
                    'due_date' => 'Due',
                    'priority' => 'Priority',
                    'status' => 'Status',
                ] as $column => $label)
                    <button
                        type="button"
                        wire:click="sortByColumn('{{ $column }}')"
                        @class([
                            'flex items-center gap-1 text-left text-[11px] font-semibold uppercase tracking-wide text-ink/45 transition-colors hover:text-ink/70',
                            'lg:col-span-1' => true,
                        ])
                    >
                        {{ $label }}
                        @if ($sortBy === $column)
                            <svg @class(['h-3.5 w-3.5', 'rotate-180' => $sortDirection === 'desc']) fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 15l7-7 7 7" />
                            </svg>
                        @else
                            <svg class="h-3.5 w-3.5 opacity-40" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4" />
                            </svg>
                        @endif
                    </button>
                @endforeach
                <span class="sr-only">Actions</span>
            </div>

            <div class="space-y-3">
                @foreach ($tasks as $task)
                    <x-dashboard.list-task-row :task="$task" class="hidden lg:flex" />
                @endforeach
            </div>

            {{-- Mobile cards --}}
            <div class="space-y-3 lg:hidden">
                @foreach ($tasks as $task)
                    <button
                        type="button"
                        wire:key="task-mobile-{{ $task->id }}"
                        wire:click="openEditModal({{ $task->id }})"
                        class="relative flex w-full overflow-hidden rounded-lg border border-brand-light/80 bg-white text-left transition-colors hover:border-brand/50"
                    >
                        <div class="w-1 shrink-0 bg-brand" aria-hidden="true"></div>
                        <div class="min-w-0 flex-1 p-4">
                            <p @class([
                                'text-sm font-semibold text-ink',
                                'text-ink/45 line-through' => $task->isDone(),
                            ])>
                                {{ $task->title }}
                            </p>
                            @if ($task->description)
                                <p class="mt-1 line-clamp-2 text-xs text-ink/55">{{ $task->description }}</p>
                            @endif
                            <div class="mt-3 flex flex-wrap items-center gap-2">
                                <x-dashboard.priority-badge :priority="$task->priority" />
                                <x-dashboard.status-badge :status="$task->status" />
                            </div>
                            <div class="mt-2 flex flex-wrap gap-x-4 gap-y-1 text-xs text-ink/60">
                                <span>{{ $task->assignee?->name ?? 'Unassigned' }}</span>
                                <span>{{ $task->formattedDueDate() ?? 'No due date' }}</span>
                            </div>
                        </div>
                    </button>
                @endforeach
            </div>
        @else
            <div class="rounded-lg border border-dashed border-brand-light px-6 py-12 text-center">
                <p class="text-sm font-medium text-ink/70">
                    @if (trim($search) !== '')
                        No tasks match your search.
                    @else
                        No tasks yet.
                    @endif
                </p>
                @if (trim($search) === '')
                    <button type="button" wire:click="openCreateModal" class="tms-link mt-2 inline-block text-sm">
                        Create your first task
                    </button>
                @endif
            </div>
        @endif
    </section>

    @include('livewire.dashboard.partials.task-modal')
</div>
