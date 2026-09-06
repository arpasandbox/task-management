<?php

namespace App\Support;

use App\Models\ActivityLog;
use App\Models\Task;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class TaskActivityLogger
{
    /**
     * @param  array<string, mixed>|null  $meta
     */
    public static function log(Task $task, string $action, ?array $meta = null, ?User $user = null): ActivityLog
    {
        return ActivityLog::query()->create([
            'task_id' => $task->id,
            'user_id' => ($user ?? Auth::user())?->id,
            'action' => $action,
            'meta' => $meta,
        ]);
    }
}
