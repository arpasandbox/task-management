<?php

namespace Tests\Feature;

use App\Livewire\Workspaces\Create;
use App\Livewire\Workspaces\Edit;
use App\Models\User;
use App\Models\Workspace;
use App\Support\WorkspaceStatusSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class WorkspaceStatusFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_workspace_create_seeds_statuses_and_redirects_to_dashboard(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        Livewire::test(Create::class)
            ->set('name', 'My Workspace')
            ->call('store')
            ->assertRedirect(route('dashboard'));

        $workspace = Workspace::first();

        $this->assertDatabaseHas('workspaces', [
            'name' => 'My Workspace',
            'owner_id' => $user->id,
        ]);

        $this->assertDatabaseHas('statuses', [
            'workspace_id' => $workspace->id,
            'name' => 'To Do',
            'order' => 0,
        ]);

        $this->assertDatabaseHas('statuses', [
            'workspace_id' => $workspace->id,
            'name' => 'In Progress',
            'order' => 1,
        ]);

        $this->assertDatabaseHas('statuses', [
            'workspace_id' => $workspace->id,
            'name' => 'Done',
            'order' => 2,
        ]);
    }

    public function test_workspace_settings_updates_name(): void
    {
        $user = User::factory()->create();
        $workspace = Workspace::create([
            'name' => 'Old Name',
            'slug' => 'old-name',
            'owner_id' => $user->id,
        ]);
        $workspace->members()->attach($user->id, ['role' => 'owner']);
        WorkspaceStatusSeeder::seedForWorkspace($workspace);

        $this->actingAs($user);

        Livewire::test(Edit::class)
            ->set('name', 'New Name')
            ->call('update')
            ->assertRedirect(route('dashboard'));

        $this->assertDatabaseHas('workspaces', [
            'id' => $workspace->id,
            'name' => 'New Name',
            'slug' => 'new-name',
        ]);
    }
}
