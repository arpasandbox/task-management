<div class="space-y-2">
    <div class="flex items-center justify-between gap-2">
        <p class="text-[12.1px] font-semibold uppercase tracking-wide text-ink/45">Workspace</p>

        <a
            href="{{ route('workspaces.create') }}"
            wire:navigate
            class="tms-circle size-7 shrink-0 bg-brand text-white transition-colors hover:bg-brand/90"
            aria-label="Add new workspace"
        >
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
            </svg>
        </a>
    </div>

    <ul class="space-y-1">
        @foreach ($workspaces as $workspace)
            <li wire:key="workspace-{{ $workspace->id }}">
                <button
                    type="button"
                    wire:click="switchWorkspace({{ $workspace->id }})"
                    @class([
                        'flex w-full items-center justify-between gap-2 rounded-md px-2 py-2 text-left text-sm font-medium transition-colors',
                        'bg-brand/20 text-ink' => $current?->id === $workspace->id,
                        'text-ink/70 hover:bg-brand-light/30 hover:text-ink' => $current?->id !== $workspace->id,
                    ])
                    @if ($current?->id === $workspace->id)
                        aria-current="true"
                    @endif
                >
                    <span class="min-w-0 truncate">{{ $workspace->name }}</span>
                    <span @class([
                        'tms-circle-count',
                        'bg-brand text-white' => $current?->id === $workspace->id,
                        'bg-brand-light/50 text-ink/70' => $current?->id !== $workspace->id,
                    ])>
                        {{ $workspace->tasks_count }}
                    </span>
                </button>
            </li>
        @endforeach
    </ul>
</div>
