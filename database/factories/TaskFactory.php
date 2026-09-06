<?php

namespace Database\Factories;

use App\Models\Status;
use App\Models\Task;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Task>
 */
class TaskFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => fake()->sentence(4),
            'description' => fake()->optional()->paragraph(),
            'priority' => fake()->randomElement(['low', 'medium', 'high']),
            'due_date' => fake()->optional()->dateTimeBetween('now', '+1 month'),
            'position' => 0,
        ];
    }

    public function forWorkspace(Workspace $workspace): static
    {
        return $this->state(function (array $attributes) use ($workspace) {
            $status = $workspace->defaultStatus() ?? $workspace->statuses()->first();

            return [
                'workspace_id' => $workspace->id,
                'status_id' => $status?->id,
            ];
        });
    }

    public function createdBy(User $user): static
    {
        return $this->state(fn (array $attributes) => [
            'created_by' => $user->id,
            'assignee_id' => $user->id,
        ]);
    }

    public function inStatus(Status $status): static
    {
        return $this->state(fn (array $attributes) => [
            'workspace_id' => $status->workspace_id,
            'status_id' => $status->id,
        ]);
    }
}
