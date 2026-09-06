<div>
    <h2 class="mb-1 text-lg font-medium">Create your workspace</h2>
    <p class="mb-6 text-sm tms-muted">Give your team a name. You can create additional workspaces anytime from the sidebar.</p>

    <form wire:submit="store" class="space-y-4">
        <div>
            <label for="name" class="mb-1 block text-sm font-medium">Workspace name</label>
            <input id="name" type="text" wire:model="name" class="tms-input" placeholder="My Team" required>
            @error('name')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <button type="submit" class="tms-btn-primary w-full">Create workspace</button>
    </form>
</div>
