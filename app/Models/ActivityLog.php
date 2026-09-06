<?php

namespace App\Models;

use Database\Factories\ActivityLogFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActivityLog extends Model
{
    /** @use HasFactory<ActivityLogFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'task_id',
        'user_id',
        'action',
        'meta',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'meta' => 'array',
        ];
    }

    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function description(): string
    {
        $meta = $this->meta ?? [];

        return match ($this->action) {
            'created' => 'created this task',
            'updated' => 'updated task details',
            'status_changed' => 'moved task from '.($meta['from'] ?? '?').' to '.($meta['to'] ?? '?'),
            'assigned' => isset($meta['to'])
                ? 'assigned task to '.($meta['to'] === 'Unassigned' ? 'no one' : $meta['to'])
                : 'updated the assignee',
            'commented' => 'added a comment',
            'tags_updated' => 'updated tags',
            'subtask_added' => 'added subtask "'.($meta['title'] ?? '').'"',
            'subtask_completed' => 'completed subtask "'.($meta['title'] ?? '').'"',
            'subtask_reopened' => 'reopened subtask "'.($meta['title'] ?? '').'"',
            'subtask_deleted' => 'removed subtask "'.($meta['title'] ?? '').'"',
            default => str_replace('_', ' ', $this->action),
        };
    }
}
