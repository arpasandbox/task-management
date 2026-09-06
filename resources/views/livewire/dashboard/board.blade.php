<div>
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

    <div
        class="grid grid-cols-1 gap-6 md:grid-cols-3 md:gap-8"
        data-kanban-board
        data-livewire-id="{{ $this->getId() }}"
    >
        @forelse ($statuses as $status)
            <section class="flex min-w-0 flex-col" wire:key="status-{{ $status->id }}">
                <div class="mb-4 flex items-center justify-between gap-2">
                    <div class="flex min-w-0 items-center gap-2">
                        <h2 class="truncate text-sm font-semibold text-ink sm:text-base">{{ $status->name }}</h2>
                        <span class="tms-circle-count shrink-0 bg-brand text-white">
                            {{ $status->topLevelTasks->count() }}
                        </span>
                    </div>

                    <a
                        href="{{ route('workspaces.statuses') }}"
                        wire:navigate
                        class="shrink-0 rounded-md p-1 text-ink/40 transition-colors hover:bg-brand-light/30 hover:text-ink"
                        aria-label="Manage {{ $status->name }} column"
                    >
                        <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path d="M12 8c1.1 0 2-.9 2-2s-.9-2-2-2-2 .9-2 2 .9 2 2 2zm0 2c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2zm0 6c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2z" />
                        </svg>
                    </a>
                </div>

                <div class="flex min-h-[12rem] flex-1 flex-col">
                    <div
                        class="kanban-column flex min-h-[8rem] flex-1 flex-col"
                        data-kanban-column
                        data-status-id="{{ $status->id }}"
                    >
                        @forelse ($status->topLevelTasks as $task)
                            <div
                                wire:key="task-{{ $task->id }}"
                                data-task-id="{{ $task->id }}"
                                class="mb-3 cursor-grab active:cursor-grabbing"
                            >
                                <div
                                    role="button"
                                    tabindex="0"
                                    wire:click="openEditModal({{ $task->id }})"
                                    class="block w-full text-left"
                                >
                                    <x-dashboard.task-card :task="$task" />
                                </div>
                            </div>
                        @empty
                            <x-dashboard.kanban-empty-state :status-name="$status->name" />
                        @endforelse
                    </div>

                    <div
                        class="mt-3 flex items-center justify-center rounded-lg border border-dashed border-brand-light/80 px-4 py-6 text-center"
                        aria-hidden="true"
                    >
                        <div class="flex items-center gap-2 text-xs text-ink/40">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 14l-7 7m0 0l-7-7m7 7V3" />
                            </svg>
                            <span>Drop tasks here</span>
                        </div>
                    </div>
                </div>
            </section>
        @empty
            <p class="text-sm tms-muted">No board columns configured for this workspace.</p>
        @endforelse
    </div>

    @include('livewire.dashboard.partials.task-modal')
</div>
