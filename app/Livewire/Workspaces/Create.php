<?php

namespace App\Livewire\Workspaces;

use App\Models\Workspace;
use App\Support\CurrentWorkspace;
use App\Support\WorkspaceStatusSeeder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.guest')]
class Create extends Component
{
    public string $name = '';

    public function mount(): void
    {
        //
    }

    public function store(): void
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        $slug = $this->uniqueSlug($validated['name']);

        $workspace = Workspace::create([
            'name' => $validated['name'],
            'slug' => $slug,
            'owner_id' => Auth::id(),
        ]);

        $workspace->members()->attach(Auth::id(), ['role' => 'owner']);

        WorkspaceStatusSeeder::seedForWorkspace($workspace);

        CurrentWorkspace::remember($workspace);

        $this->redirectRoute('dashboard', navigate: true);
    }

    protected function uniqueSlug(string $name): string
    {
        $slug = Str::slug($name);
        $original = $slug ?: 'workspace';
        $counter = 1;

        while (Workspace::where('slug', $slug)->exists()) {
            $slug = $original.'-'.$counter;
            $counter++;
        }

        return $slug;
    }

    public function render()
    {
        return view('livewire.workspaces.create');
    }
}
