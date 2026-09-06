<?php

namespace App\Livewire\Dashboard;

use App\Livewire\Dashboard\Concerns\ManagesTasks;
use App\Models\Task;
use Illuminate\Support\Collection;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app', ['activeNav' => 'list'])]
class ListView extends Component
{
    use ManagesTasks;

    public string $search = '';

    public string $sortBy = 'due_date';

    public string $sortDirection = 'asc';

    public function sortByColumn(string $column): void
    {
        if (! in_array($column, ['title', 'assignee', 'due_date', 'priority', 'status'], true)) {
            return;
        }

        if ($this->sortBy === $column) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $column;
            $this->sortDirection = 'asc';
        }
    }

    public function updatedSortBy(): void
    {
        $this->sortDirection = 'asc';
    }

    /**
     * @param  Collection<int, Task>  $tasks
     * @return Collection<int, Task>
     */
    protected function filteredTasks(Collection $tasks): Collection
    {
        $search = trim($this->search);

        if ($search !== '') {
            $needle = strtolower($search);

            $tasks = $tasks->filter(function (Task $task) use ($needle): bool {
                return str_contains(strtolower($task->title), $needle)
                    || str_contains(strtolower($task->description ?? ''), $needle);
            });
        }

        $direction = $this->sortDirection === 'desc' ? -1 : 1;

        return $tasks
            ->sort(function (Task $a, Task $b) use ($direction): int {
                $result = match ($this->sortBy) {
                    'title' => strcasecmp($a->title, $b->title),
                    'assignee' => strcasecmp($a->assignee?->name ?? '', $b->assignee?->name ?? ''),
                    'due_date' => $this->compareDates($a->due_date, $b->due_date),
                    'priority' => (array_search($a->priority, ['low', 'medium', 'high'], true) ?: 0)
                        <=> (array_search($b->priority, ['low', 'medium', 'high'], true) ?: 0),
                    'status' => ($a->status->order <=> $b->status->order) ?: ($a->position <=> $b->position),
                    default => 0,
                };

                return $direction * $result;
            })
            ->values();
    }

    protected function compareDates(mixed $a, mixed $b): int
    {
        if ($a === null && $b === null) {
            return 0;
        }

        if ($a === null) {
            return 1;
        }

        if ($b === null) {
            return -1;
        }

        return $a <=> $b;
    }

    public function render()
    {
        $workspace = $this->currentWorkspace();

        $tasks = $workspace
            ? Task::query()
                ->where('workspace_id', $workspace->id)
                ->whereNull('parent_task_id')
                ->with(['status', 'assignee', 'tags'])
                ->get()
            : collect();

        $tasks = $this->filteredTasks($tasks);

        return view('livewire.dashboard.list', [
            'workspaceName' => $workspace?->name ?? 'Work Space Name',
            'tasks' => $tasks,
        ]);
    }
}
