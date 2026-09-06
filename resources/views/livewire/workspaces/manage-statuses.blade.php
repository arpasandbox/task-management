<div>
    <h2 class="mb-1 text-lg font-medium">Board columns</h2>
    <p class="mb-2 text-sm tms-muted">Customize status columns for your workspace Kanban board.</p>

    <x-dashboard.back-link
        :href="route('workspaces.settings')"
        label="Workspace settings"
        class="mb-6"
    />

    @error('delete')
        <p class="mb-4 text-sm text-red-600">{{ $message }}</p>
    @enderror

    <div class="mb-8 space-y-3">
        @foreach ($statuses as $status)
            <div wire:key="status-row-{{ $status->id }}" class="rounded-lg border border-brand-light bg-white p-4">
                <div class="flex flex-wrap items-center gap-3">
                    <input
                        type="color"
                        wire:model.blur="statusColors.{{ $status->id }}"
                        wire:change="saveStatus({{ $status->id }})"
                        class="h-9 w-9 cursor-pointer rounded border border-brand-light"
                        aria-label="Color for {{ $status->name }}"
                    >

                    <input
                        type="text"
                        wire:model.blur="statusNames.{{ $status->id }}"
                        wire:change="saveStatus({{ $status->id }})"
                        class="min-w-0 flex-1 tms-input"
                    >

                    <div class="flex items-center gap-1">
                        <button type="button" wire:click="moveStatus({{ $status->id }}, 'up')" class="tms-btn-secondary px-2 py-1 text-xs">↑</button>
                        <button type="button" wire:click="moveStatus({{ $status->id }}, 'down')" class="tms-btn-secondary px-2 py-1 text-xs">↓</button>
                        <button
                            type="button"
                            wire:click="deleteStatus({{ $status->id }})"
                            wire:confirm="Delete this column? Tasks will move to another column."
                            class="rounded border border-red-300 px-2 py-1 text-xs text-red-700 hover:bg-red-50"
                        >
                            Delete
                        </button>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <h3 class="mb-3 text-sm font-medium">Add column</h3>
    <form wire:submit="addStatus" class="space-y-4">
        <div>
            <label for="status-name" class="mb-1 block text-sm font-medium">Name</label>
            <input id="status-name" type="text" wire:model="name" class="tms-input" placeholder="Review" required>
            @error('name')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="status-color" class="mb-1 block text-sm font-medium">Color</label>
            <div class="flex flex-wrap items-center gap-2">
                @foreach ($colorPresets as $preset)
                    <button
                        type="button"
                        wire:click="$set('color', '{{ $preset }}')"
                        class="h-8 w-8 rounded-full border-2 transition-transform hover:scale-110"
                        style="background-color: {{ $preset }}; border-color: {{ $color === $preset ? '#FB923C' : 'transparent' }}"
                        aria-label="Select color {{ $preset }}"
                    ></button>
                @endforeach
                <input id="status-color" type="color" wire:model="color" class="h-9 w-9 rounded border border-brand-light">
            </div>
        </div>

        <button type="submit" class="tms-btn-primary w-full">Add column</button>
    </form>
</div>
