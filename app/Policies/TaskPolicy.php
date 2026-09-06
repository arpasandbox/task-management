<?php

namespace App\Policies;

use App\Models\Task;
use App\Models\User;

class TaskPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->workspaces()->exists();
    }

    public function view(User $user, Task $task): bool
    {
        return $this->belongsToWorkspace($user, $task);
    }

    public function create(User $user): bool
    {
        return $user->workspaces()->exists();
    }

    public function update(User $user, Task $task): bool
    {
        return $this->belongsToWorkspace($user, $task);
    }

    public function delete(User $user, Task $task): bool
    {
        return $this->belongsToWorkspace($user, $task);
    }

    protected function belongsToWorkspace(User $user, Task $task): bool
    {
        return $user->workspaces()
            ->where('workspaces.id', $task->workspace_id)
            ->exists();
    }
}
