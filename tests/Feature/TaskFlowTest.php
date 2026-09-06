<?php

namespace Tests\Feature;

use App\Livewire\Dashboard\Board;
use App\Livewire\Dashboard\ListView;
use App\Models\Task;
use App\Models\User;
use App\Models\Workspace;
use App\Support\WorkspaceStatusSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class TaskFlowTest extends TestCase
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

    public function test_create_task_lands_in_to_do_column(): void
    {
        [$user, $workspace] = $this->userWithWorkspace();
        $toDoStatus = $workspace->statuses()->where('name', 'To Do')->first();

        $this->actingAs($user);

        Livewire::test(Board::class)
            ->call('openCreateModal')
            ->set('title', 'Write documentation')
            ->set('priority', 'high')
            ->set('due_date', '2026-09-01')
            ->call('saveTask')
            ->assertSet('showTaskModal', false);

        $this->assertDatabaseHas('tasks', [
            'workspace_id' => $workspace->id,
            'status_id' => $toDoStatus->id,
            'title' => 'Write documentation',
            'priority' => 'high',
            'created_by' => $user->id,
            'assignee_id' => $user->id,
            'position' => 1,
        ]);
    }

    public function test_dashboard_displays_tasks_grouped_by_status(): void
    {
        [$user, $workspace] = $this->userWithWorkspace();
        $toDoStatus = $workspace->statuses()->where('name', 'To Do')->first();

        Task::factory()
            ->forWorkspace($workspace)
            ->createdBy($user)
            ->inStatus($toDoStatus)
            ->create([
                'title' => 'Visible task',
                'position' => 1,
            ]);

        $this->actingAs($user);

        Livewire::test(Board::class)
            ->assertSee('Visible task')
            ->assertSee('To Do');
    }

    public function test_user_can_edit_task(): void
    {
        [$user, $workspace] = $this->userWithWorkspace();
        $toDoStatus = $workspace->statuses()->where('name', 'To Do')->first();

        $task = Task::factory()
            ->forWorkspace($workspace)
            ->createdBy($user)
            ->inStatus($toDoStatus)
            ->create([
                'title' => 'Old title',
                'position' => 1,
            ]);

        $this->actingAs($user);

        Livewire::test(Board::class)
            ->call('openEditModal', $task->id)
            ->set('title', 'Updated title')
            ->call('saveTask')
            ->assertSet('showTaskModal', false);

        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'title' => 'Updated title',
        ]);
    }

    public function test_user_can_delete_task(): void
    {
        [$user, $workspace] = $this->userWithWorkspace();
        $toDoStatus = $workspace->statuses()->where('name', 'To Do')->first();

        $task = Task::factory()
            ->forWorkspace($workspace)
            ->createdBy($user)
            ->inStatus($toDoStatus)
            ->create([
                'title' => 'Task to delete',
                'position' => 1,
            ]);

        $this->actingAs($user);

        Livewire::test(Board::class)
            ->call('openEditModal', $task->id)
            ->call('deleteTask')
            ->assertSet('showTaskModal', false);

        $this->assertDatabaseMissing('tasks', [
            'id' => $task->id,
        ]);
    }

    public function test_list_view_displays_tasks_in_table(): void
    {
        [$user, $workspace] = $this->userWithWorkspace();
        $toDoStatus = $workspace->statuses()->where('name', 'To Do')->first();
        $doneStatus = $workspace->statuses()->where('name', 'Done')->first();

        Task::factory()
            ->forWorkspace($workspace)
            ->createdBy($user)
            ->inStatus($toDoStatus)
            ->create([
                'title' => 'Review API docs',
                'priority' => 'high',
                'position' => 1,
            ]);

        Task::factory()
            ->forWorkspace($workspace)
            ->createdBy($user)
            ->inStatus($doneStatus)
            ->create([
                'title' => 'Set up staging environment',
                'priority' => 'low',
                'position' => 1,
            ]);

        $this->actingAs($user);

        Livewire::test(ListView::class)
            ->assertSee('Review API docs')
            ->assertSee('Set up staging environment')
            ->assertSee('High')
            ->assertSee('Low')
            ->assertSee('To Do')
            ->assertSee('Done');
    }

    public function test_list_view_route_is_accessible(): void
    {
        [$user] = $this->userWithWorkspace();

        $this->actingAs($user)
            ->get(route('tasks.list'))
            ->assertOk();
    }

    public function test_user_can_move_task_to_another_status(): void
    {
        [$user, $workspace] = $this->userWithWorkspace();
        $toDoStatus = $workspace->statuses()->where('name', 'To Do')->first();
        $inProgressStatus = $workspace->statuses()->where('name', 'In Progress')->first();

        $task = Task::factory()
            ->forWorkspace($workspace)
            ->createdBy($user)
            ->inStatus($toDoStatus)
            ->create([
                'title' => 'Move me',
                'position' => 1,
            ]);

        $this->actingAs($user);

        Livewire::test(Board::class)
            ->call('moveTask', $task->id, $inProgressStatus->id, 1);

        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'status_id' => $inProgressStatus->id,
            'position' => 1,
        ]);
    }

    public function test_user_can_reorder_task_within_column(): void
    {
        [$user, $workspace] = $this->userWithWorkspace();
        $toDoStatus = $workspace->statuses()->where('name', 'To Do')->first();

        $firstTask = Task::factory()
            ->forWorkspace($workspace)
            ->createdBy($user)
            ->inStatus($toDoStatus)
            ->create([
                'title' => 'First',
                'position' => 1,
            ]);

        $secondTask = Task::factory()
            ->forWorkspace($workspace)
            ->createdBy($user)
            ->inStatus($toDoStatus)
            ->create([
                'title' => 'Second',
                'position' => 2,
            ]);

        $this->actingAs($user);

        Livewire::test(Board::class)
            ->call('moveTask', $secondTask->id, $toDoStatus->id, 1);

        $this->assertDatabaseHas('tasks', [
            'id' => $secondTask->id,
            'position' => 1,
        ]);

        $this->assertDatabaseHas('tasks', [
            'id' => $firstTask->id,
            'position' => 2,
        ]);
    }

    public function test_move_task_rejects_foreign_status(): void
    {
        [$user, $workspace] = $this->userWithWorkspace();
        $otherUser = User::factory()->create();
        $otherWorkspace = Workspace::create([
            'name' => 'Other Workspace',
            'slug' => 'other-workspace',
            'owner_id' => $otherUser->id,
        ]);
        $otherWorkspace->members()->attach($otherUser->id, ['role' => 'owner']);
        WorkspaceStatusSeeder::seedForWorkspace($otherWorkspace);

        $toDoStatus = $workspace->statuses()->where('name', 'To Do')->first();
        $foreignStatus = $otherWorkspace->statuses()->where('name', 'In Progress')->first();

        $task = Task::factory()
            ->forWorkspace($workspace)
            ->createdBy($user)
            ->inStatus($toDoStatus)
            ->create([
                'title' => 'Stay here',
                'position' => 1,
            ]);

        $this->actingAs($user);

        Livewire::test(Board::class)
            ->call('moveTask', $task->id, $foreignStatus->id, 1);

        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'status_id' => $toDoStatus->id,
            'position' => 1,
        ]);
    }

    public function test_user_can_assign_task_to_workspace_member(): void
    {
        [$user, $workspace] = $this->userWithWorkspace();
        $member = User::factory()->create([
            'first_name' => 'Jane',
            'last_name' => 'Doe',
        ]);
        $workspace->members()->attach($member->id, ['role' => 'member']);
        $toDoStatus = $workspace->statuses()->where('name', 'To Do')->first();

        $this->actingAs($user);

        Livewire::test(Board::class)
            ->call('openCreateModal')
            ->set('title', 'Delegated task')
            ->set('assignee_id', $member->id)
            ->call('saveTask')
            ->assertSet('showTaskModal', false);

        $this->assertDatabaseHas('tasks', [
            'workspace_id' => $workspace->id,
            'title' => 'Delegated task',
            'assignee_id' => $member->id,
            'status_id' => $toDoStatus->id,
        ]);
    }

    public function test_user_can_update_task_assignee(): void
    {
        [$user, $workspace] = $this->userWithWorkspace();
        $member = User::factory()->create([
            'first_name' => 'Alex',
            'last_name' => 'Smith',
        ]);
        $workspace->members()->attach($member->id, ['role' => 'member']);
        $toDoStatus = $workspace->statuses()->where('name', 'To Do')->first();

        $task = Task::factory()
            ->forWorkspace($workspace)
            ->createdBy($user)
            ->inStatus($toDoStatus)
            ->create([
                'title' => 'Reassign me',
                'position' => 1,
            ]);

        $this->actingAs($user);

        Livewire::test(Board::class)
            ->call('openEditModal', $task->id)
            ->set('assignee_id', $member->id)
            ->call('saveTask')
            ->assertSet('showTaskModal', false);

        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'assignee_id' => $member->id,
        ]);
    }

    public function test_assignee_must_be_workspace_member(): void
    {
        [$user, $workspace] = $this->userWithWorkspace();
        $outsider = User::factory()->create();
        $toDoStatus = $workspace->statuses()->where('name', 'To Do')->first();

        $task = Task::factory()
            ->forWorkspace($workspace)
            ->createdBy($user)
            ->inStatus($toDoStatus)
            ->create([
                'title' => 'Protected task',
                'position' => 1,
            ]);

        $this->actingAs($user);

        Livewire::test(Board::class)
            ->call('openEditModal', $task->id)
            ->set('assignee_id', $outsider->id)
            ->call('saveTask')
            ->assertHasErrors(['assignee_id']);

        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'assignee_id' => $user->id,
        ]);
    }

    public function test_user_can_unassign_task(): void
    {
        [$user, $workspace] = $this->userWithWorkspace();
        $toDoStatus = $workspace->statuses()->where('name', 'To Do')->first();

        $task = Task::factory()
            ->forWorkspace($workspace)
            ->createdBy($user)
            ->inStatus($toDoStatus)
            ->create([
                'title' => 'Clear assignee',
                'position' => 1,
            ]);

        $this->actingAs($user);

        Livewire::test(Board::class)
            ->call('openEditModal', $task->id)
            ->set('assignee_id', null)
            ->call('saveTask')
            ->assertSet('showTaskModal', false);

        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'assignee_id' => null,
        ]);
    }
}
