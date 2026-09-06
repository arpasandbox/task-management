<?php

namespace App\Support;

use App\Models\Status;
use App\Models\Workspace;

class WorkspaceStatusSeeder
{
    /**
     * @return array<int, array{name: string, color: string, order: int}>
     */
    public static function defaults(): array
    {
        return [
            ['name' => 'To Do', 'color' => '#FDBA74', 'order' => 0],
            ['name' => 'In Progress', 'color' => '#FB923C', 'order' => 1],
            ['name' => 'Done', 'color' => '#1F2937', 'order' => 2],
        ];
    }

    public static function seedForWorkspace(Workspace $workspace): void
    {
        foreach (self::defaults() as $status) {
            Status::create([
                'workspace_id' => $workspace->id,
                ...$status,
            ]);
        }
    }
}
