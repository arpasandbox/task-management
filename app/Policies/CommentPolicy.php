<?php

namespace App\Policies;

use App\Models\Comment;
use App\Models\Task;
use App\Models\User;

class CommentPolicy
{
    public function create(User $user, Task $task): bool
    {
        return $this->belongsToWorkspace($user, $task);
    }

    public function delete(User $user, Comment $comment): bool
    {
        return $comment->user_id === $user->id;
    }

    protected function belongsToWorkspace(User $user, Task $task): bool
    {
        return $user->workspaces()
            ->where('workspaces.id', $task->workspace_id)
            ->exists();
    }
}
