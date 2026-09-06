<?php

namespace App\Livewire\Workspaces;

use App\Livewire\Concerns\ResolvesCurrentWorkspace;
use App\Models\Status;
use App\Models\Task;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.guest')]
class ManageStatuses extends Component
{
    use ResolvesCurrentWorkspace;

    public string $name = '';

    public string $color = '#FB923C';

    /** @var array<int, string> */
    public array $statusNames = [];

    /** @var array<int, string> */
    public array $statusColors = [];

    public function mount(): void
    {
        if (! $this->currentWorkspace()) {
            $this->redirectRoute('workspaces.create', navigate: true);

            return;
        }

        $this->loadStatusFields();
    }

    public function addStatus(): void
    {
        $workspace = $this->currentWorkspace();

        if (! $workspace) {
            return;
        }

        $this->authorize('create', Status::class);

        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'color' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
        ]);

        $order = ((int) $workspace->statuses()->max('order')) + 1;

        Status::query()->create([
            'workspace_id' => $workspace->id,
            'name' => trim($validated['name']),
            'color' => $validated['color'],
            'order' => $order,
        ]);

        $this->reset(['name']);
        $this->color = '#FB923C';
        $this->loadStatusFields();
    }

    public function saveStatus(int $statusId): void
    {
        $status = Status::query()->findOrFail($statusId);
        $this->authorize('update', $status);

        $workspace = $this->currentWorkspace();

        if (! $workspace || $status->workspace_id !== $workspace->id) {
            return;
        }

        $name = trim($this->statusNames[$statusId] ?? $status->name);
        $color = $this->statusColors[$statusId] ?? $status->color;

        $status->update([
            'name' => $name,
            'color' => $color,
        ]);

        $this->loadStatusFields();
    }

    public function deleteStatus(int $statusId): void
    {
        $status = Status::query()->findOrFail($statusId);
        $this->authorize('delete', $status);

        $workspace = $this->currentWorkspace();

        if (! $workspace || $status->workspace_id !== $workspace->id) {
            return;
        }

        if ($workspace->statuses()->count() <= 1) {
            $this->addError('delete', 'At least one status column is required.');

            return;
        }

        $fallback = $workspace->statuses()
            ->whereKeyNot($status->id)
            ->orderBy('order')
            ->first();

        if (! $fallback) {
            return;
        }

        Task::query()
            ->where('status_id', $status->id)
            ->update(['status_id' => $fallback->id]);

        $deletedOrder = $status->order;
        $status->delete();

        $workspace->statuses()
            ->where('order', '>', $deletedOrder)
            ->decrement('order');

        $this->loadStatusFields();
    }

    public function moveStatus(int $statusId, string $direction): void
    {
        $status = Status::query()->findOrFail($statusId);
        $this->authorize('update', $status);

        $workspace = $this->currentWorkspace();

        if (! $workspace || $status->workspace_id !== $workspace->id) {
            return;
        }

        $swap = $workspace->statuses()
            ->when($direction === 'up', fn ($query) => $query->where('order', '<', $status->order)->orderByDesc('order'))
            ->when($direction === 'down', fn ($query) => $query->where('order', '>', $status->order)->orderBy('order'))
            ->first();

        if (! $swap) {
            return;
        }

        $statusOrder = $status->order;
        $status->update(['order' => $swap->order]);
        $swap->update(['order' => $statusOrder]);

        $this->loadStatusFields();
    }

    protected function loadStatusFields(): void
    {
        $workspace = $this->currentWorkspace();

        $this->statusNames = [];
        $this->statusColors = [];

        if (! $workspace) {
            return;
        }

        foreach ($workspace->statuses as $status) {
            $this->statusNames[$status->id] = $status->name;
            $this->statusColors[$status->id] = $status->color;
        }
    }

    public function render()
    {
        $workspace = $this->currentWorkspace();

        return view('livewire.workspaces.manage-statuses', [
            'statuses' => $workspace?->statuses ?? collect(),
            'colorPresets' => Status::colorPresets(),
        ]);
    }
}
