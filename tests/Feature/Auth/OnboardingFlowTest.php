<?php

namespace Tests\Feature\Auth;

use App\Livewire\Auth\Login;
use App\Livewire\Auth\Register;
use App\Livewire\Workspaces\Create;
use App\Models\User;
use App\Models\Workspace;
use App\Support\WorkspaceStatusSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class OnboardingFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_register_redirects_to_workspace_create(): void
    {
        Livewire::test(Register::class)
            ->set('first_name', 'Test')
            ->set('last_name', 'User')
            ->set('email', 'test@example.com')
            ->set('password', 'password123')
            ->set('password_confirmation', 'password123')
            ->call('register')
            ->assertRedirect(route('workspaces.create'));

        $this->assertAuthenticated();
        $this->assertFalse(auth()->user()->must_change_password);
    }

    public function test_login_redirects_to_workspace_create_when_no_workspace(): void
    {
        $user = User::factory()->create([
            'email' => 'new@example.com',
            'password' => 'password',
        ]);

        Livewire::test(Login::class)
            ->set('email', 'new@example.com')
            ->set('password', 'password')
            ->call('login')
            ->assertRedirect(route('workspaces.create'));
    }

    public function test_dashboard_redirects_when_no_workspace(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertRedirect(route('workspaces.create'));
    }

    public function test_workspace_create_redirects_to_dashboard(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        Livewire::test(Create::class)
            ->set('name', 'My Workspace')
            ->call('store')
            ->assertRedirect(route('dashboard'));

        $this->assertDatabaseHas('workspaces', [
            'name' => 'My Workspace',
            'owner_id' => $user->id,
        ]);

        $this->assertDatabaseHas('workspace_user', [
            'user_id' => $user->id,
            'role' => 'owner',
        ]);
    }

    public function test_login_goes_to_dashboard_when_onboarding_complete(): void
    {
        $user = User::factory()->create([
            'email' => 'returning@example.com',
            'password' => 'password',
        ]);

        $workspace = Workspace::create([
            'name' => 'Existing Workspace',
            'slug' => 'existing-workspace',
            'owner_id' => $user->id,
        ]);

        $workspace->members()->attach($user->id, ['role' => 'owner']);
        WorkspaceStatusSeeder::seedForWorkspace($workspace);

        Livewire::test(Login::class)
            ->set('email', 'returning@example.com')
            ->set('password', 'password')
            ->call('login')
            ->assertRedirect(route('dashboard'));
    }

    public function test_workspace_create_page_is_accessible_without_redirect_loop(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('workspaces.create'))
            ->assertOk();
    }

    public function test_guest_is_redirected_from_dashboard(): void
    {
        $this->get(route('dashboard'))
            ->assertRedirect(route('login'));
    }
}
