<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Status extends Model
{
    use HasFactory;

    protected $fillable = [
        'workspace_id',
        'name',
        'color',
        'order',
    ];

    public function workspace(): BelongsTo
    {
        return $this->belongsTo(Workspace::class);
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class)->orderBy('position');
    }

    public function topLevelTasks(): HasMany
    {
        return $this->hasMany(Task::class)
            ->whereNull('parent_task_id')
            ->orderBy('position');
    }

    /**
     * @return list<string>
     */
    public static function colorPresets(): array
    {
        return [
            '#FDBA74',
            '#FB923C',
            '#1F2937',
            '#FFF7ED',
        ];
    }
}
