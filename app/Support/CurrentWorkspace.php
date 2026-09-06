<?php

namespace App\Support;

use App\Models\User;
use App\Models\Workspace;
use Illuminate\Support\Facades\Auth;

class CurrentWorkspace
{
    public const SESSION_KEY = 'current_workspace_id';

    public static function resolve(?User $user = null): ?Workspace
    {
        $user ??= Auth::user();

        if (! $user) {
            return null;
        }

        $workspaceId = session(self::SESSION_KEY);

        if ($workspaceId) {
            $workspace = $user->workspaces()->whereKey($workspaceId)->first();

            if ($workspace) {
                return $workspace;
            }
        }

        $workspace = $user->workspaces()->orderBy('workspaces.id')->first();

        if ($workspace) {
            session([self::SESSION_KEY => $workspace->id]);
        }

        return $workspace;
    }

    public static function switch(Workspace $workspace, ?User $user = null): void
    {
        $user ??= Auth::user();

        if (! $user?->workspaces()->whereKey($workspace->id)->exists()) {
            abort(403);
        }

        session([self::SESSION_KEY => $workspace->id]);
    }

    public static function remember(Workspace $workspace): void
    {
        session([self::SESSION_KEY => $workspace->id]);
    }
}
