<div class="space-y-2">
    <div class="flex items-center justify-between gap-2">
        <p class="text-[12.1px] font-semibold uppercase tracking-wide text-ink/45">Workspace</p>

        <a
            href="{{ route('workspaces.create') }}"
            wire:navigate
            class="inline-grid size-7 shrink-0 place-items-center rounded-md bg-brand text-white transition-colors hover:bg-brand/90"
            aria-label="Add new workspace"
        >
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
            </svg>
        </a>
    </div>

    @if (session('workspace_error'))
        <p class="text-xs text-red-600">{{ session('workspace_error') }}</p>
    @endif

    <ul class="space-y-1">
        @foreach ($workspaces as $workspace)
            @php
                $canManage = $workspace->owner_id === auth()->id()
                    || $workspace->pivot?->role === 'owner';
            @endphp

            <li wire:key="workspace-{{ $workspace->id }}">
                <div
                    @class([
                        'flex w-full items-center gap-1 rounded-md px-1 py-1 text-sm font-medium transition-colors',
                        'bg-brand/20 text-ink' => $current?->id === $workspace->id,
                        'text-ink/70 hover:bg-brand-light/30 hover:text-ink' => $current?->id !== $workspace->id,
                    ])
                >
                    <button
                        type="button"
                        wire:click="switchWorkspace({{ $workspace->id }})"
                        class="flex min-w-0 flex-1 items-center rounded-md px-1 py-1 text-left"
                        @if ($current?->id === $workspace->id)
                            aria-current="true"
                        @endif
                    >
                        <span class="min-w-0 truncate">{{ $workspace->name }}</span>
                    </button>

                    <span @class([
                        'tms-circle-count shrink-0',
                        'bg-brand text-white' => $current?->id === $workspace->id,
                        'bg-brand-light/50 text-ink/70' => $current?->id !== $workspace->id,
                    ])>
                        {{ $workspace->tasks_count }}
                    </span>

                    <div class="relative shrink-0" x-data="{ open: false }" @click.outside="open = false">
                        <button
                            type="button"
                            @click="open = ! open"
                            class="rounded-md p-1 text-ink/40 transition-colors hover:bg-brand-light/40 hover:text-ink"
                            aria-label="Workspace actions"
                            aria-haspopup="menu"
                            :aria-expanded="open"
                        >
                            <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path d="M12 8c1.1 0 2-.9 2-2s-.9-2-2-2-2 .9-2 2 .9 2 2 2zm0 2c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2zm0 6c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2z" />
                            </svg>
                        </button>

                        <div
                            x-show="open"
                            x-cloak
                            x-transition
                            role="menu"
                            class="absolute right-0 top-full z-50 mt-1 min-w-[10.5rem] rounded-lg border border-brand-light bg-white py-1 shadow-lg"
                        >
                            <button
                                type="button"
                                role="menuitem"
                                wire:click="editWorkspace({{ $workspace->id }})"
                                @click="open = false"
                                class="block w-full px-3 py-2 text-left text-sm text-ink/80 transition-colors hover:bg-brand-light/30 hover:text-ink"
                            >
                                Edit
                            </button>

                            @if ($canManage)
                                <button
                                    type="button"
                                    role="menuitem"
                                    wire:click="deleteWorkspace({{ $workspace->id }})"
                                    wire:confirm="Delete this workspace? This cannot be undone."
                                    @click="open = false"
                                    class="block w-full px-3 py-2 text-left text-sm text-red-600 transition-colors hover:bg-red-50"
                                >
                                    Delete
                                </button>

                                <hr class="my-1 border-brand-light/60">

                                <button
                                    type="button"
                                    role="menuitem"
                                    wire:click="openAddMemberModal({{ $workspace->id }})"
                                    @click="open = false"
                                    class="block w-full px-3 py-2 text-left text-sm text-ink/80 transition-colors hover:bg-brand-light/30 hover:text-ink"
                                >
                                    Add Member
                                </button>

                                <button
                                    type="button"
                                    role="menuitem"
                                    wire:click="openRemoveMemberModal({{ $workspace->id }})"
                                    @click="open = false"
                                    class="block w-full px-3 py-2 text-left text-sm text-ink/80 transition-colors hover:bg-brand-light/30 hover:text-ink"
                                >
                                    Remove Member
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
            </li>
        @endforeach
    </ul>

    @if ($activeModal && $modalWorkspace)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <button
                type="button"
                wire:click="closeModal"
                class="absolute inset-0 bg-ink/40"
                aria-label="Close dialog"
            ></button>

            <div class="relative z-10 w-full max-w-sm rounded-xl border border-brand-light/60 bg-white p-5 shadow-xl">
                @if ($activeModal === 'add_member')
                    <h3 class="text-base font-semibold text-ink">Add member</h3>
                    <p class="mt-1 text-sm text-ink/60">Invite an existing user to {{ $modalWorkspace->name }} by email.</p>

                    <form wire:submit="addMember" class="mt-4 space-y-4">
                        <div>
                            <label for="member-email-{{ $modalWorkspace->id }}" class="mb-1 block text-sm font-medium">Email</label>
                            <input
                                id="member-email-{{ $modalWorkspace->id }}"
                                type="email"
                                wire:model="memberEmail"
                                class="tms-input"
                                placeholder="colleague@example.com"
                                required
                            >
                            @error('memberEmail')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="flex justify-end gap-2">
                            <button type="button" wire:click="closeModal" class="tms-btn-secondary">Cancel</button>
                            <button type="submit" class="tms-btn-primary">Add member</button>
                        </div>
                    </form>
                @endif

                @if ($activeModal === 'remove_member')
                    <h3 class="text-base font-semibold text-ink">Remove member</h3>
                    <p class="mt-1 text-sm text-ink/60">Remove someone from {{ $modalWorkspace->name }}.</p>

                    <form wire:submit="removeMember" class="mt-4 space-y-4">
                        <div>
                            <label for="remove-member-{{ $modalWorkspace->id }}" class="mb-1 block text-sm font-medium">Member</label>
                            <select
                                id="remove-member-{{ $modalWorkspace->id }}"
                                wire:model="removeMemberUserId"
                                class="tms-input"
                                required
                            >
                                <option value="">Select a member</option>
                                @foreach ($removableMembers as $member)
                                    <option value="{{ $member->id }}">{{ $member->name }} ({{ $member->email }})</option>
                                @endforeach
                            </select>
                            @error('removeMemberUserId')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror

                            @if ($removableMembers->isEmpty())
                                <p class="mt-2 text-sm text-ink/55">No members can be removed from this workspace.</p>
                            @endif
                        </div>

                        <div class="flex justify-end gap-2">
                            <button type="button" wire:click="closeModal" class="tms-btn-secondary">Cancel</button>
                            <button
                                type="submit"
                                class="tms-btn-primary"
                                @disabled($removableMembers->isEmpty())
                            >
                                Remove member
                            </button>
                        </div>
                    </form>
                @endif
            </div>
        </div>
    @endif
</div>
