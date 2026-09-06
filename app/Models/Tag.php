<?php

namespace App\Models;

use Database\Factories\TagFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Tag extends Model
{
    /** @use HasFactory<TagFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'workspace_id',
        'name',
        'color',
    ];

    /**
     * @var list<string>
     */
    public const COLORS = [
        '#FB923C',
        '#FDBA74',
        '#1F2937',
        '#FFF7ED',
    ];

    public function workspace(): BelongsTo
    {
        return $this->belongsTo(Workspace::class);
    }

    public function tasks(): BelongsToMany
    {
        return $this->belongsToMany(Task::class, 'task_tag')->withTimestamps();
    }

    public static function defaultColor(): string
    {
        return self::COLORS[array_rand(self::COLORS)];
    }
}
