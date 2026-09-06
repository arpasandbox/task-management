<?php

namespace App\Livewire\Dashboard;

use App\Livewire\Dashboard\Concerns\ManagesTasks;
use App\Models\Status;
use App\Models\Task;
use App\Support\TaskActivityLogger;
use App\Support\TaskNotifier;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app', ['activeNav' => 'board'])]
class Board extends Component
{
    use ManagesTasks;

    public function moveTask(int $taskId, int $statusId, int $position): void
    {
        $task = Task::query()->with(['status', 'assignee'])->findOrFail($taskId);
        $this->authorize('update', $task);

        $workspace = $this->currentWorkspace();

        if (! $workspace || $task->workspace_id !== $workspace->id) {
            return;
        }

        if (! $workspace->statuses()->whereKey($statusId)->exists()) {
            return;
        }

        $position = max(1, $position);
        $oldStatusId = $task->status_id;
        $oldStatusName = $task->status?->name;
        $oldPosition = $task->position;

        if ($oldStatusId === $statusId && $oldPosition === $position) {
            $this->skipRender();

            return;
        }

        DB::transaction(function () use ($task, $statusId, $position, $oldStatusId, $oldPosition): void {
            if ($oldStatusId === $statusId) {
                if ($position < $oldPosition) {
                    Task::query()
                        ->where('status_id', $statusId)
                        ->whereNull('parent_task_id')
                        ->whereKeyNot($task->id)
                        ->whereBetween('position', [$position, $oldPosition - 1])
                        ->increment('position');
                } else {
                    Task::query()
                        ->where('status_id', $statusId)
                        ->whereNull('parent_task_id')
                        ->whereKeyNot($task->id)
                        ->whereBetween('position', [$oldPosition + 1, $position])
                        ->decrement('position');
                }

                $task->update(['position' => $position]);
            } else {
                Task::query()
                    ->where('status_id', $oldStatusId)
                    ->whereNull('parent_task_id')
                    ->where('position', '>', $oldPosition)
                    ->decrement('position');

                Task::query()
                    ->where('status_id', $statusId)
                    ->whereNull('parent_task_id')
                    ->where('position', '>=', $position)
                    ->increment('position');

                $task->update([
                    'status_id' => $statusId,
                    'position' => $position,
                ]);
            }
        });

        if ($oldStatusId !== $statusId) {
            $newStatus = Status::query()->find($statusId);
            $task->refresh();

            TaskActivityLogger::log($task, 'status_changed', [
                'from' => $oldStatusName,
                'to' => $newStatus?->name,
            ]);

            TaskNotifier::notifyAssignee(
                $task->load('assignee'),
                Auth::user()->name.' moved "'.$task->title.'" to '.$newStatus?->name,
                Auth::user()
            );
        }

        $this->skipRender();
    }

    public function render()
    {
        $workspace = $this->currentWorkspace();

        $statuses = $workspace
            ? $workspace->statuses()->with(['topLevelTasks.assignee', 'topLevelTasks.tags'])->get()
            : collect();

        return view('livewire.dashboard.board', [
            'workspaceName' => $workspace?->name ?? 'Work Space Name',
            'statuses' => $statuses,
        ]);
    }
}
