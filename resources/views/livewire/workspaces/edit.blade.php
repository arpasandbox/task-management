<div>
    <h2 class="mb-1 text-lg font-medium">Workspace settings</h2>
    <p class="mb-2 text-sm tms-muted">Update your workspace name.</p>

    <div class="mb-6 mt-[20px] flex items-center justify-between gap-4 text-sm">
        <x-dashboard.back-link
            :href="route('dashboard')"
            label="Back to board"
        />

        <a href="{{ route('workspaces.statuses') }}" wire:navigate class="tms-link inline-flex shrink-0 items-center gap-1">
            Manage board columns
            <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
            </svg>
        </a>
    </div>

    <form wire:submit="update" class="space-y-4">
        <div>
            <label for="name" class="mb-1 block text-sm font-medium">Workspace name</label>
            <input id="name" type="text" wire:model="name" class="tms-input" placeholder="My Team" required>
            @error('name')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <button type="submit" class="tms-btn-primary w-full">Save changes</button>
    </form>
</div>
