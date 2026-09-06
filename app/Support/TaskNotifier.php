<?php

namespace App\Support;

use App\Models\Task;
use App\Models\User;
use App\Notifications\TaskNotification;

class TaskNotifier
{
    public static function notifyAssignee(Task $task, string $message, ?User $except = null): void
    {
        if (! $task->assignee_id) {
            return;
        }

        if ($except && $task->assignee_id === $except->id) {
            return;
        }

        $assignee = $task->relationLoaded('assignee')
            ? $task->assignee
            : User::query()->find($task->assignee_id);

        if ($assignee) {
            $assignee->notify(new TaskNotification($message, $task->id));
        }
    }
}
