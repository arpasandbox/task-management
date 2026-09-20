<?php

namespace App\Livewire\Workspaces;

use App\Models\User;
use App\Models\Workspace;
use App\Support\CurrentWorkspace;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Switcher extends Component
{
    public ?int $modalWorkspaceId = null;

    public ?string $activeModal = null;

    public string $memberEmail = '';

    public ?int $removeMemberUserId = null;

    public function switchWorkspace(int|string $workspaceId): void
    {
        $workspace = $this->findUserWorkspace((int) $workspaceId);

        CurrentWorkspace::switch($workspace);

        $this->redirectRoute('dashboard', navigate: true);
    }

    public function editWorkspace(int $workspaceId): void
    {
        $workspace = $this->findUserWorkspace($workspaceId);

        CurrentWorkspace::switch($workspace);

        $this->redirectRoute('workspaces.settings', navigate: true);
    }

    public function deleteWorkspace(int $workspaceId): void
    {
        $workspace = $this->authorizeManage($workspaceId);
        $user = Auth::user();

        if ($user->workspaces()->count() <= 1) {
            session()->flash('workspace_error', 'You must keep at least one workspace.');

            return;
        }

        $wasCurrent = CurrentWorkspace::resolve()?->id === $workspace->id;

        $workspace->delete();

        if ($wasCurrent) {
            $next = $user->workspaces()->orderBy('workspaces.id')->first();

            if ($next) {
                CurrentWorkspace::remember($next);
            } else {
                session()->forget(CurrentWorkspace::SESSION_KEY);
            }
        }

        $this->redirectRoute('dashboard', navigate: true);
    }

    public function openAddMemberModal(int $workspaceId): void
    {
        $this->authorizeManage($workspaceId);

        $this->modalWorkspaceId = $workspaceId;
        $this->activeModal = 'add_member';
        $this->memberEmail = '';
        $this->removeMemberUserId = null;
        $this->resetValidation();
    }

    public function openRemoveMemberModal(int $workspaceId): void
    {
        $this->authorizeManage($workspaceId);

        $this->modalWorkspaceId = $workspaceId;
        $this->activeModal = 'remove_member';
        $this->memberEmail = '';
        $this->removeMemberUserId = null;
        $this->resetValidation();
    }

    public function closeModal(): void
    {
        $this->activeModal = null;
        $this->modalWorkspaceId = null;
        $this->memberEmail = '';
        $this->removeMemberUserId = null;
        $this->resetValidation();
    }

    public function addMember(): void
    {
        if (! $this->modalWorkspaceId) {
            return;
        }

        $workspace = $this->authorizeManage($this->modalWorkspaceId);

        $validated = $this->validate([
            'memberEmail' => ['required', 'email'],
        ]);

        $member = User::query()->where('email', $validated['memberEmail'])->first();

        if (! $member) {
            $this->addError('memberEmail', 'No user found with that email.');

            return;
        }

        if ($workspace->members()->whereKey($member->id)->exists()) {
            $this->addError('memberEmail', 'That user is already a member.');

            return;
        }

        $workspace->members()->attach($member->id, ['role' => 'member']);

        $this->closeModal();
    }

    public function removeMember(): void
    {
        if (! $this->modalWorkspaceId) {
            return;
        }

        $workspace = $this->authorizeManage($this->modalWorkspaceId);

        $this->validate([
            'removeMemberUserId' => ['required', 'integer'],
        ]);

        $member = $workspace->members()->whereKey($this->removeMemberUserId)->first();

        if (! $member) {
            $this->addError('removeMemberUserId', 'Select a member to remove.');

            return;
        }

        if ($this->memberIsOwner($workspace, $member)) {
            $this->addError('removeMemberUserId', 'The workspace owner cannot be removed.');

            return;
        }

        $workspace->members()->detach($member->id);

        $this->closeModal();
    }

    public function render()
    {
        $user = Auth::user();
        $current = CurrentWorkspace::resolve();

        $modalWorkspace = null;
        $removableMembers = collect();

        if ($this->modalWorkspaceId && $user) {
            $modalWorkspace = $user->workspaces()
                ->whereKey($this->modalWorkspaceId)
                ->with('members')
                ->first();

            if ($modalWorkspace) {
                $removableMembers = $modalWorkspace->members->filter(
                    fn (User $member) => ! $this->memberIsOwner($modalWorkspace, $member)
                );
            }
        }

        return view('livewire.workspaces.switcher', [
            'workspaces' => $user?->workspaces()
                ->withCount(['tasks as tasks_count' => fn ($query) => $query->whereNull('parent_task_id')])
                ->orderBy('name')
                ->get() ?? collect(),
            'current' => $current,
            'modalWorkspace' => $modalWorkspace,
            'removableMembers' => $removableMembers,
        ]);
    }

    protected function findUserWorkspace(int $workspaceId): Workspace
    {
        return Auth::user()
            ->workspaces()
            ->whereKey($workspaceId)
            ->firstOrFail();
    }

    protected function authorizeManage(int $workspaceId): Workspace
    {
        $workspace = $this->findUserWorkspace($workspaceId);
        $user = Auth::user();

        $isOwner = $workspace->owner_id === $user->id
            || $workspace->members()
                ->where('users.id', $user->id)
                ->wherePivot('role', 'owner')
                ->exists();

        if (! $isOwner) {
            abort(403);
        }

        return $workspace;
    }

    protected function memberIsOwner(Workspace $workspace, User $member): bool
    {
        return $workspace->owner_id === $member->id
            || $member->pivot?->role === 'owner';
    }
}
