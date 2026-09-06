@if ($showTaskModal)
    @php
        $selectedStatus = $this->workspaceStatuses()->firstWhere('id', $status_id);
    @endphp

    <div class="fixed inset-0 z-40 flex items-end justify-center p-4 sm:items-center">
        <button
            type="button"
            wire:click="closeModal"
            class="absolute inset-0 bg-ink/40"
            aria-label="Close task form"
        ></button>

        <div class="relative z-10 flex max-h-[92vh] w-full max-w-xl flex-col overflow-hidden rounded-2xl border border-brand-light/60 bg-white shadow-xl">
            <form wire:submit="saveTask" class="flex min-h-0 flex-1 flex-col">
                {{-- Header --}}
                <div class="flex shrink-0 items-start justify-between gap-4 border-b border-brand-light/60 px-5 py-5 sm:px-6">
                    <div class="flex min-w-0 items-center gap-3">
                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-brand text-white">
                            @if ($editingTaskId)
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                            @else
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                                </svg>
                            @endif
                        </div>
                        <h2 class="text-lg font-semibold text-ink">
                            {{ $editingTaskId ? 'Edit task' : 'New task' }}
                        </h2>
                    </div>

                    <button
                        type="button"
                        wire:click="closeModal"
                        class="shrink-0 rounded-md p-1.5 text-ink/50 transition-colors hover:bg-brand-light/30 hover:text-ink"
                        aria-label="Close"
                    >
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                {{-- Body --}}
                <div class="min-h-0 flex-1 overflow-y-auto px-5 py-5 sm:px-6">
                    <div class="space-y-5">
                        {{-- Title --}}
                        <div>
                            <label for="task-title" class="mb-1.5 block text-sm font-medium text-ink">
                                Title <span class="text-brand">*</span>
                            </label>
                            <input
                                id="task-title"
                                type="text"
                                wire:model="title"
                                class="tms-input"
                                placeholder="Task title"
                                required
                            >
                            @error('title')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Description --}}
                        <div>
                            <label for="task-description" class="mb-1.5 block text-sm font-medium text-ink">Description</label>
                            <textarea
                                id="task-description"
                                wire:model="description"
                                rows="3"
                                class="tms-input resize-none"
                                placeholder="Optional details"
                            ></textarea>
                            @error('description')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Priority & Status --}}
                        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                            <div>
                                <label for="task-priority" class="mb-1.5 block text-sm font-medium text-ink">
                                    Priority <span class="text-brand">*</span>
                                </label>
                                <div class="relative">
                                    @if ($priority === 'high')
                                        <svg class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-brand" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 15l7-7 7 7" />
                                        </svg>
                                    @endif
                                    <select
                                        id="task-priority"
                                        wire:model.live="priority"
                                        @class(['tms-input', 'pl-9' => $priority === 'high'])
                                    >
                                        <option value="low">Low</option>
                                        <option value="medium">Medium</option>
                                        <option value="high">High</option>
                                    </select>
                                </div>
                                @error('priority')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="task-status" class="mb-1.5 block text-sm font-medium text-ink">
                                    Status <span class="text-brand">*</span>
                                </label>
                                <div class="relative">
                                    @if ($selectedStatus)
                                        <span
                                            class="pointer-events-none absolute left-3 top-1/2 h-2.5 w-2.5 -translate-y-1/2 rounded-full border-2"
                                            style="border-color: {{ $selectedStatus->color }}; background-color: transparent;"
                                            aria-hidden="true"
                                        ></span>
                                    @endif
                                    <select
                                        id="task-status"
                                        wire:model.live="status_id"
                                        class="tms-input pl-8"
                                    >
                                        @foreach ($this->workspaceStatuses() as $status)
                                            <option value="{{ $status->id }}">{{ $status->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                @error('status_id')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        {{-- Due date --}}
                        <div>
                            <label for="task-due-date" class="mb-1.5 block text-sm font-medium text-ink">
                                Due date <span class="text-brand">*</span>
                            </label>
                            <div class="relative">
                                <svg class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-ink/40" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                <input
                                    id="task-due-date"
                                    type="date"
                                    wire:model="due_date"
                                    class="tms-input pl-9 pr-9"
                                >
                                <svg class="pointer-events-none absolute right-3 top-1/2 h-4 w-4 -translate-y-1/2 text-ink/40" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                            </div>
                            @error('due_date')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Assignee --}}
                        <div>
                            <label for="task-assignee" class="mb-1.5 block text-sm font-medium text-ink">
                                Assignee <span class="text-brand">*</span>
                            </label>
                            <div class="relative flex items-center rounded-md border border-brand-light bg-white shadow-sm focus-within:border-brand focus-within:ring-1 focus-within:ring-brand">
                                <select
                                    id="task-assignee"
                                    wire:model.live="assignee_id"
                                    class="min-w-0 flex-1 appearance-none border-0 bg-transparent py-2 pl-3 pr-8 text-sm text-ink focus:outline-none focus:ring-0"
                                >
                                    <option value="">Unassigned</option>
                                    @foreach ($this->workspaceMembers() as $member)
                                        <option value="{{ $member->id }}">{{ $member->name }}</option>
                                    @endforeach
                                </select>
                                <svg class="pointer-events-none absolute right-3 h-4 w-4 text-ink/40" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                                </svg>
                            </div>
                            @error('assignee_id')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Tags --}}
                        <div>
                            <p class="mb-1.5 text-sm font-medium text-ink">Tags</p>

                            <div class="flex gap-2">
                                <input
                                    type="text"
                                    wire:model="newTagName"
                                    wire:keydown.enter.prevent="addTag"
                                    class="min-w-0 flex-1 tms-input"
                                    placeholder="New tag name"
                                >
                                <button
                                    type="button"
                                    wire:click="addTag"
                                    class="tms-btn-secondary shrink-0 border-brand text-brand hover:bg-brand/10"
                                >
                                    Add
                                </button>
                            </div>

                            @if ($this->selectedTags()->isNotEmpty())
                                <div class="mt-3 flex flex-wrap gap-2">
                                    @foreach ($this->selectedTags() as $tag)
                                        <span
                                            wire:key="selected-tag-{{ $tag->id }}"
                                            class="inline-flex items-center gap-1 rounded-full bg-brand-light/40 px-2.5 py-1 text-xs font-medium text-ink"
                                        >
                                            {{ $tag->name }}
                                            <button
                                                type="button"
                                                wire:click="toggleTag({{ $tag->id }})"
                                                class="rounded-full p-0.5 text-ink/50 transition-colors hover:bg-brand-light/60 hover:text-ink"
                                                aria-label="Remove {{ $tag->name }} tag"
                                            >
                                                <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" aria-hidden="true">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                                </svg>
                                            </button>
                                        </span>
                                    @endforeach
                                </div>
                            @endif

                            @error('newTagName')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                            @error('tag_ids')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        @if ($editingTaskId)
                            {{-- Subtasks --}}
                            <div class="border-t border-brand-light/60 pt-5">
                                <p class="mb-3 text-sm font-medium text-ink">Subtasks</p>

                                @if ($this->editingSubtasks()->isEmpty())
                                    <div class="mb-4 flex flex-col items-center rounded-lg border border-dashed border-brand-light/80 px-4 py-8 text-center">
                                        <svg class="mb-3 h-10 w-10 text-brand/30" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                                        </svg>
                                        <p class="text-sm font-medium text-ink/70">No subtasks yet.</p>
                                        <p class="mt-1 text-xs text-ink/45">Break this task into smaller steps.</p>
                                    </div>
                                @else
                                    <div class="mb-4 space-y-2">
                                        @foreach ($this->editingSubtasks() as $subtask)
                                            <div wire:key="subtask-{{ $subtask->id }}" class="flex items-center gap-2 rounded-lg border border-brand-light/60 bg-surface/50 px-3 py-2.5">
                                                <input
                                                    type="checkbox"
                                                    wire:click="toggleSubtaskDone({{ $subtask->id }})"
                                                    @checked($subtask->isDone())
                                                    class="rounded border-brand-light text-brand focus:ring-brand"
                                                >
                                                <span @class([
                                                    'min-w-0 flex-1 text-sm',
                                                    'text-ink/45 line-through' => $subtask->isDone(),
                                                ])>
                                                    {{ $subtask->title }}
                                                </span>
                                                <button
                                                    type="button"
                                                    wire:click="deleteSubtask({{ $subtask->id }})"
                                                    wire:confirm="Remove this subtask?"
                                                    class="rounded p-1 text-ink/40 transition-colors hover:bg-red-50 hover:text-red-600"
                                                    aria-label="Remove subtask"
                                                >
                                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                                    </svg>
                                                </button>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif

                                <div class="flex gap-2">
                                    <input
                                        type="text"
                                        wire:model="subtaskTitle"
                                        wire:keydown.enter.prevent="addSubtask"
                                        class="min-w-0 flex-1 tms-input"
                                        placeholder="Add a subtask"
                                    >
                                    <button
                                        type="button"
                                        wire:click="addSubtask"
                                        class="tms-btn-secondary shrink-0 border-brand text-brand hover:bg-brand/10"
                                    >
                                        Add
                                    </button>
                                </div>
                                @error('subtaskTitle')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Activity --}}
                            <div class="border-t border-brand-light/60 pt-5">
                                <button
                                    type="button"
                                    wire:click="toggleActivity"
                                    class="flex w-full items-center justify-between gap-2 text-left text-sm font-medium text-ink"
                                    aria-expanded="{{ $showActivity ? 'true' : 'false' }}"
                                >
                                    <span class="flex items-center gap-2">
                                        <svg class="h-4 w-4 text-ink/50" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        Activity
                                    </span>
                                    <svg
                                        @class(['h-4 w-4 shrink-0 text-ink/50 transition-transform', 'rotate-180' => $showActivity])
                                        fill="none"
                                        viewBox="0 0 24 24"
                                        stroke="currentColor"
                                        stroke-width="2"
                                        aria-hidden="true"
                                    >
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                                    </svg>
                                </button>

                                @if ($showActivity)
                                    <div class="mt-3 max-h-36 space-y-2 overflow-y-auto">
                                        @forelse ($this->editingTaskActivity() as $entry)
                                            <div wire:key="activity-{{ $entry->id }}" class="text-sm">
                                                <span class="font-medium text-ink">{{ $entry->user?->name ?? 'System' }}</span>
                                                <span class="text-ink/70"> {{ $entry->description() }}</span>
                                                <span class="text-xs text-ink/45"> · {{ $entry->created_at->diffForHumans() }}</span>
                                            </div>
                                        @empty
                                            <p class="text-sm tms-muted">No activity yet.</p>
                                        @endforelse
                                    </div>
                                @endif
                            </div>

                            {{-- Comments --}}
                            <div class="border-t border-brand-light/60 pt-5">
                                <p class="mb-3 text-sm font-medium text-ink">Comments</p>

                                @if ($this->editingTaskComments()->isEmpty())
                                    <div class="mb-4 flex flex-col items-center rounded-lg border border-dashed border-brand-light/80 px-4 py-8 text-center">
                                        <svg class="mb-3 h-10 w-10 text-brand/30" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                                        </svg>
                                        <p class="text-sm font-medium text-ink/70">No comments yet.</p>
                                        <p class="mt-1 text-xs text-ink/45">Start the conversation.</p>
                                    </div>
                                @else
                                    <div class="mb-4 max-h-48 space-y-3 overflow-y-auto">
                                        @foreach ($this->editingTaskComments() as $comment)
                                            <div wire:key="comment-{{ $comment->id }}" class="rounded-lg border border-brand-light/60 bg-surface/50 p-3">
                                                <div class="mb-1 flex items-start justify-between gap-2">
                                                    <div>
                                                        <p class="text-sm font-medium text-ink">{{ $comment->user->name }}</p>
                                                        <p class="text-xs text-ink/45">{{ $comment->created_at->diffForHumans() }}</p>
                                                    </div>

                                                    @if ($comment->user_id === auth()->id())
                                                        <button
                                                            type="button"
                                                            wire:click="deleteComment({{ $comment->id }})"
                                                            wire:confirm="Delete this comment?"
                                                            class="text-xs text-red-600 hover:text-red-800"
                                                        >
                                                            Delete
                                                        </button>
                                                    @endif
                                                </div>
                                                <p class="text-sm text-ink/80">{{ $comment->body }}</p>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif

                                <div class="space-y-2">
                                    <label for="task-comment" class="sr-only">Add comment</label>
                                    <textarea
                                        id="task-comment"
                                        wire:model="commentBody"
                                        rows="3"
                                        class="tms-input resize-none"
                                        placeholder="Write a comment..."
                                    ></textarea>
                                    @error('commentBody')
                                        <p class="text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                    <button
                                        type="button"
                                        wire:click="addComment"
                                        class="tms-btn-primary"
                                    >
                                        Post comment
                                    </button>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Footer --}}
                <div class="flex shrink-0 flex-wrap items-center justify-between gap-3 border-t border-brand-light/60 bg-white px-5 py-4 sm:px-6">
                    @if ($editingTaskId)
                        <button
                            type="button"
                            wire:click="deleteTask"
                            wire:confirm="Delete this task?"
                            class="inline-flex items-center gap-2 rounded-md border border-red-500 px-3 py-2 text-sm font-medium text-red-600 transition-colors hover:bg-red-50"
                        >
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                            </svg>
                            Delete
                        </button>
                    @else
                        <span></span>
                    @endif

                    <div class="flex items-center gap-2">
                        <button
                            type="button"
                            wire:click="closeModal"
                            class="tms-btn-secondary"
                        >
                            Cancel
                        </button>

                        <button type="submit" class="tms-btn-primary">
                            {{ $editingTaskId ? 'Save changes' : 'Create task' }}
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endif
