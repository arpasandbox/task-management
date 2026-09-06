<?php

namespace App\Livewire\Workspaces;

use App\Support\CurrentWorkspace;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Switcher extends Component
{
    public function switchWorkspace(int|string $workspaceId): void
    {
        $workspace = Auth::user()
            ->workspaces()
            ->whereKey((int) $workspaceId)
            ->firstOrFail();

        CurrentWorkspace::switch($workspace);

        $this->redirectRoute('dashboard', navigate: true);
    }

    public function render()
    {
        $user = Auth::user();
        $current = CurrentWorkspace::resolve();

        return view('livewire.workspaces.switcher', [
            'workspaces' => $user?->workspaces()
                ->withCount(['tasks as tasks_count' => fn ($query) => $query->whereNull('parent_task_id')])
                ->orderBy('name')
                ->get() ?? collect(),
            'current' => $current,
        ]);
    }
}
