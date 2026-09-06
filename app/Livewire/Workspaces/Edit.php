<?php

namespace App\Livewire\Workspaces;

use App\Livewire\Concerns\ResolvesCurrentWorkspace;
use App\Models\Workspace;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.guest')]
class Edit extends Component
{
    use ResolvesCurrentWorkspace;

    public string $name = '';

    public function mount(): void
    {
        $workspace = $this->currentWorkspace();

        if (! $workspace) {
            $this->redirectRoute('workspaces.create', navigate: true);

            return;
        }

        $this->name = $workspace->name;
    }

    public function update(): void
    {
        $workspace = $this->currentWorkspace();

        if (! $workspace) {
            return;
        }

        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        $workspace->update([
            'name' => $validated['name'],
            'slug' => $this->uniqueSlug($validated['name'], $workspace->id),
        ]);

        $this->redirectRoute('dashboard', navigate: true);
    }

    protected function uniqueSlug(string $name, int $ignoreId): string
    {
        $slug = Str::slug($name);
        $original = $slug ?: 'workspace';
        $counter = 1;

        while (Workspace::where('slug', $slug)->where('id', '!=', $ignoreId)->exists()) {
            $slug = $original.'-'.$counter;
            $counter++;
        }

        return $slug;
    }

    public function render()
    {
        return view('livewire.workspaces.edit');
    }
}
