<?php

namespace Tests\Feature;

use App\Livewire\Dashboard\Board;
use App\Livewire\Workspaces\Create;
use App\Livewire\Workspaces\ManageStatuses;
use App\Livewire\Workspaces\Switcher;
use App\Models\ActivityLog;
use App\Models\Task;
use App\Models\User;
use App\Models\Workspace;
use App\Notifications\TaskNotification;
use App\Support\CurrentWorkspace;
use App\Support\WorkspaceStatusSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;
use Tests\TestCase;

class PhaseEFeaturesTest extends TestCase
{
    use RefreshDatabase;

    protected function userWithWorkspace(): array
    {
        $user = User::factory()->create();
        $workspace = Workspace::create([
            'name' => 'Test Workspace',
            'slug' => 'test-workspace',
            'owner_id' => $user->id,
        ]);
        $workspace->members()->attach($user->id, ['role' => 'owner']);
        WorkspaceStatusSeeder::seedForWorkspace($workspace);

        return [$user, $workspace];
    }

    public function test_user_can_switch_workspace(): void
    {
        [$user, $firstWorkspace] = $this->userWithWorkspace();

        $secondWorkspace = Workspace::create([
            'name' => 'Second Workspace',
            'slug' => 'second-workspace',
            'owner_id' => $user->id,
        ]);
        $secondWorkspace->members()->attach($user->id, ['role' => 'owner']);
        WorkspaceStatusSeeder::seedForWorkspace($secondWorkspace);

        $this->actingAs($user);
        CurrentWorkspace::remember($firstWorkspace);

        Livewire::test(Switcher::class)
            ->call('switchWorkspace', $secondWorkspace->id);

        $this->assertEquals($secondWorkspace->id, CurrentWorkspace::resolve()?->id);
    }

    public function test_user_can_create_second_workspace(): void
    {
        [$user] = $this->userWithWorkspace();

        $this->actingAs($user);

        Livewire::test(Create::class)
            ->set('name', 'Another Team')
            ->call('store')
            ->assertRedirect(route('dashboard'));

        $this->assertDatabaseHas('workspaces', [
            'name' => 'Another Team',
            'owner_id' => $user->id,
        ]);

        $this->assertEquals(2, $user->fresh()->workspaces()->count());
    }

    public function test_task_create_logs_activity(): void
    {
        [$user, $workspace] = $this->userWithWorkspace();

        $this->actingAs($user);
        CurrentWorkspace::remember($workspace);

        Livewire::test(Board::class)
            ->call('openCreateModal')
            ->set('title', 'Logged task')
            ->call('saveTask');

        $task = Task::query()->where('title', 'Logged task')->first();

        $this->assertDatabaseHas('activity_logs', [
            'task_id' => $task->id,
            'user_id' => $user->id,
            'action' => 'created',
        ]);
    }

    public function test_assigning_task_notifies_assignee(): void
    {
        Notification::fake();

        [$user, $workspace] = $this->userWithWorkspace();
        $member = User::factory()->create();
        $workspace->members()->attach($member->id, ['role' => 'member']);

        $this->actingAs($user);
        CurrentWorkspace::remember($workspace);

        Livewire::test(Board::class)
            ->call('openCreateModal')
            ->set('title', 'Notify me')
            ->set('assignee_id', $member->id)
            ->call('saveTask');

        Notification::assertSentTo($member, TaskNotification::class);
    }

    public function test_user_can_add_subtask_to_task(): void
    {
        [$user, $workspace] = $this->userWithWorkspace();
        $toDoStatus = $workspace->statuses()->where('name', 'To Do')->first();

        $task = Task::factory()
            ->forWorkspace($workspace)
            ->createdBy($user)
            ->inStatus($toDoStatus)
            ->create([
                'title' => 'Parent task',
                'position' => 1,
            ]);

        $this->actingAs($user);
        CurrentWorkspace::remember($workspace);

        Livewire::test(Board::class)
            ->call('openEditModal', $task->id)
            ->set('subtaskTitle', 'Child step')
            ->call('addSubtask');

        $this->assertDatabaseHas('tasks', [
            'title' => 'Child step',
            'parent_task_id' => $task->id,
        ]);

        $this->assertDatabaseHas('activity_logs', [
            'task_id' => $task->id,
            'action' => 'subtask_added',
        ]);
    }

    public function test_subtasks_do_not_appear_on_board(): void
    {
        [$user, $workspace] = $this->userWithWorkspace();
        $toDoStatus = $workspace->statuses()->where('name', 'To Do')->first();

        Task::factory()
            ->forWorkspace($workspace)
            ->createdBy($user)
            ->inStatus($toDoStatus)
            ->create([
                'title' => 'Parent only',
                'position' => 1,
            ]);

        Task::factory()
            ->forWorkspace($workspace)
            ->createdBy($user)
            ->inStatus($toDoStatus)
            ->create([
                'title' => 'Hidden subtask',
                'parent_task_id' => Task::first()->id,
                'position' => 1,
            ]);

        $this->actingAs($user);
        CurrentWorkspace::remember($workspace);

        Livewire::test(Board::class)
            ->assertSee('Parent only')
            ->assertDontSee('Hidden subtask');
    }

    public function test_user_can_add_custom_status_column(): void
    {
        [$user, $workspace] = $this->userWithWorkspace();

        $this->actingAs($user);
        CurrentWorkspace::remember($workspace);

        Livewire::test(ManageStatuses::class)
            ->set('name', 'Review')
            ->set('color', '#BA7517')
            ->call('addStatus');

        $this->assertDatabaseHas('statuses', [
            'workspace_id' => $workspace->id,
            'name' => 'Review',
            'color' => '#BA7517',
        ]);
    }

    public function test_move_task_logs_status_change_activity(): void
    {
        [$user, $workspace] = $this->userWithWorkspace();
        $toDoStatus = $workspace->statuses()->where('name', 'To Do')->first();
        $inProgressStatus = $workspace->statuses()->where('name', 'In Progress')->first();

        $task = Task::factory()
            ->forWorkspace($workspace)
            ->createdBy($user)
            ->inStatus($toDoStatus)
            ->create([
                'title' => 'Track move',
                'position' => 1,
            ]);

        $this->actingAs($user);
        CurrentWorkspace::remember($workspace);

        Livewire::test(Board::class)
            ->call('moveTask', $task->id, $inProgressStatus->id, 1);

        $this->assertDatabaseHas('activity_logs', [
            'task_id' => $task->id,
            'action' => 'status_changed',
        ]);

        $log = ActivityLog::query()->where('task_id', $task->id)->where('action', 'status_changed')->first();
        $this->assertEquals('To Do', $log->meta['from']);
        $this->assertEquals('In Progress', $log->meta['to']);
    }

    public function test_user_can_change_task_status_from_modal(): void
    {
        [$user, $workspace] = $this->userWithWorkspace();
        $toDoStatus = $workspace->statuses()->where('name', 'To Do')->first();
        $doneStatus = $workspace->statuses()->where('name', 'Done')->first();

        $task = Task::factory()
            ->forWorkspace($workspace)
            ->createdBy($user)
            ->inStatus($toDoStatus)
            ->create([
                'title' => 'Change status',
                'position' => 1,
            ]);

        $this->actingAs($user);

        Livewire::test(Board::class)
            ->call('openEditModal', $task->id)
            ->set('status_id', $doneStatus->id)
            ->call('saveTask')
            ->assertSet('showTaskModal', false);

        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'status_id' => $doneStatus->id,
        ]);

        $this->assertDatabaseHas('activity_logs', [
            'task_id' => $task->id,
            'action' => 'status_changed',
        ]);
    }
}
