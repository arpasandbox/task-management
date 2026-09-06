<?php

namespace App\Policies;

use App\Models\Status;
use App\Models\User;

class StatusPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->workspaces()->exists();
    }

    public function create(User $user): bool
    {
        return $user->workspaces()->exists();
    }

    public function update(User $user, Status $status): bool
    {
        return $this->belongsToWorkspace($user, $status);
    }

    public function delete(User $user, Status $status): bool
    {
        return $this->belongsToWorkspace($user, $status);
    }

    protected function belongsToWorkspace(User $user, Status $status): bool
    {
        return $user->workspaces()
            ->where('workspaces.id', $status->workspace_id)
            ->exists();
    }
}
