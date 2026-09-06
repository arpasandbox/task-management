<?php

namespace Tests\Feature;

use App\Livewire\Dashboard\Board;
use App\Models\Comment;
use App\Models\Tag;
use App\Models\Task;
use App\Models\User;
use App\Models\Workspace;
use App\Support\WorkspaceStatusSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CommentTagFlowTest extends TestCase
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

    public function test_user_can_create_task_with_tags(): void
    {
        [$user, $workspace] = $this->userWithWorkspace();
        $tag = Tag::factory()->forWorkspace($workspace)->create(['name' => 'frontend']);

        $this->actingAs($user);

        Livewire::test(Board::class)
            ->call('openCreateModal')
            ->set('title', 'Tagged task')
            ->set('tag_ids', [$tag->id])
            ->call('saveTask')
            ->assertSet('showTaskModal', false);

        $task = Task::query()->where('title', 'Tagged task')->first();

        $this->assertNotNull($task);
        $this->assertTrue($task->tags->contains($tag));
    }

    public function test_user_can_add_new_tag_from_task_modal(): void
    {
        [$user, $workspace] = $this->userWithWorkspace();

        $this->actingAs($user);

        Livewire::test(Board::class)
            ->call('openCreateModal')
            ->set('newTagName', 'design')
            ->call('addTag')
            ->assertSet('newTagName', '')
            ->assertCount('tag_ids', 1);

        $this->assertDatabaseHas('tags', [
            'workspace_id' => $workspace->id,
            'name' => 'design',
        ]);
    }

    public function test_user_can_add_comment_to_task(): void
    {
        [$user, $workspace] = $this->userWithWorkspace();
        $toDoStatus = $workspace->statuses()->where('name', 'To Do')->first();

        $task = Task::factory()
            ->forWorkspace($workspace)
            ->createdBy($user)
            ->inStatus($toDoStatus)
            ->create([
                'title' => 'Discuss me',
                'position' => 1,
            ]);

        $this->actingAs($user);

        Livewire::test(Board::class)
            ->call('openEditModal', $task->id)
            ->set('commentBody', 'Assets are ready in the shared drive.')
            ->call('addComment')
            ->assertSet('commentBody', '');

        $this->assertDatabaseHas('comments', [
            'task_id' => $task->id,
            'user_id' => $user->id,
            'body' => 'Assets are ready in the shared drive.',
        ]);
    }

    public function test_user_can_delete_own_comment(): void
    {
        [$user, $workspace] = $this->userWithWorkspace();
        $toDoStatus = $workspace->statuses()->where('name', 'To Do')->first();

        $task = Task::factory()
            ->forWorkspace($workspace)
            ->createdBy($user)
            ->inStatus($toDoStatus)
            ->create([
                'title' => 'Commented task',
                'position' => 1,
            ]);

        $comment = Comment::factory()
            ->forTask($task)
            ->byUser($user)
            ->create(['body' => 'Remove me']);

        $this->actingAs($user);

        Livewire::test(Board::class)
            ->call('openEditModal', $task->id)
            ->call('deleteComment', $comment->id);

        $this->assertDatabaseMissing('comments', [
            'id' => $comment->id,
        ]);
    }

    public function test_user_cannot_delete_another_users_comment(): void
    {
        [$user, $workspace] = $this->userWithWorkspace();
        $member = User::factory()->create();
        $workspace->members()->attach($member->id, ['role' => 'member']);
        $toDoStatus = $workspace->statuses()->where('name', 'To Do')->first();

        $task = Task::factory()
            ->forWorkspace($workspace)
            ->createdBy($user)
            ->inStatus($toDoStatus)
            ->create([
                'title' => 'Shared task',
                'position' => 1,
            ]);

        $comment = Comment::factory()
            ->forTask($task)
            ->byUser($member)
            ->create(['body' => 'Member comment']);

        $this->actingAs($user);

        Livewire::test(Board::class)
            ->call('openEditModal', $task->id)
            ->call('deleteComment', $comment->id)
            ->assertForbidden();

        $this->assertDatabaseHas('comments', [
            'id' => $comment->id,
        ]);
    }
}
