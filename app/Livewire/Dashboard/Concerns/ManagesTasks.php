<?php

namespace App\Livewire\Dashboard\Concerns;

use App\Livewire\Concerns\ResolvesCurrentWorkspace;
use App\Models\Comment;
use App\Models\Status;
use App\Models\Tag;
use App\Models\Task;
use App\Models\User;
use App\Support\TaskActivityLogger;
use App\Support\TaskNotifier;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

trait ManagesTasks
{
    use ResolvesCurrentWorkspace;

    public bool $showTaskModal = false;

    public ?int $editingTaskId = null;

    public string $title = '';

    public string $description = '';

    public string $priority = 'medium';

    public ?string $due_date = null;

    public ?int $assignee_id = null;

    public ?int $status_id = null;

    /** @var list<int> */
    public array $tag_ids = [];

    public string $newTagName = '';

    public string $commentBody = '';

    public string $subtaskTitle = '';

    public bool $showActivity = false;

    public function toggleActivity(): void
    {
        $this->showActivity = ! $this->showActivity;
    }

    public function openCreateModal(): void
    {
        $this->resetTaskForm();
        $this->editingTaskId = null;
        $this->assignee_id = Auth::id();
        $this->status_id = $this->currentWorkspace()?->defaultStatus()?->id;
        $this->showTaskModal = true;
    }

    public function openEditModal(int $taskId): void
    {
        $task = Task::query()->with(['tags', 'status'])->findOrFail($taskId);

        $this->authorize('update', $task);

        $this->editingTaskId = $task->id;
        $this->title = $task->title;
        $this->description = $task->description ?? '';
        $this->priority = $task->priority;
        $this->due_date = $task->due_date?->format('Y-m-d');
        $this->assignee_id = $task->assignee_id;
        $this->status_id = $task->status_id;
        $this->tag_ids = $task->tags->pluck('id')->all();
        $this->commentBody = '';
        $this->subtaskTitle = '';
        $this->showActivity = false;
        $this->showTaskModal = true;
    }

    public function closeModal(): void
    {
        $this->showTaskModal = false;
        $this->resetTaskForm();
    }

    public function saveTask(): void
    {
        $workspace = $this->currentWorkspace();

        if (! $workspace) {
            return;
        }

        $validated = $this->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'priority' => ['required', 'in:low,medium,high'],
            'due_date' => ['nullable', 'date'],
            'assignee_id' => [
                'nullable',
                'integer',
                Rule::exists('workspace_user', 'user_id')->where('workspace_id', $workspace->id),
            ],
            'status_id' => [
                'required',
                'integer',
                Rule::exists('statuses', 'id')->where('workspace_id', $workspace->id),
            ],
            'tag_ids' => ['array'],
            'tag_ids.*' => [
                'integer',
                Rule::exists('tags', 'id')->where('workspace_id', $workspace->id),
            ],
        ]);

        if ($this->editingTaskId) {
            $task = Task::query()->with(['assignee', 'status'])->findOrFail($this->editingTaskId);
            $this->authorize('update', $task);

            $oldAssigneeId = $task->assignee_id;
            $oldStatusId = $task->status_id;
            $oldStatusName = $task->status?->name;
            $oldTagIds = $task->tags()->pluck('tags.id')->all();
            $newStatusId = (int) $validated['status_id'];

            $task->update([
                'title' => $validated['title'],
                'description' => $validated['description'] ?: null,
                'priority' => $validated['priority'],
                'due_date' => $validated['due_date'],
                'assignee_id' => $validated['assignee_id'],
            ]);

            if ($oldStatusId !== $newStatusId) {
                $this->moveTaskToStatus($task, $newStatusId);

                $newStatus = Status::query()->find($newStatusId);
                $task->refresh()->load('assignee');

                TaskActivityLogger::log($task, 'status_changed', [
                    'from' => $oldStatusName,
                    'to' => $newStatus?->name,
                ]);

                TaskNotifier::notifyAssignee(
                    $task,
                    Auth::user()->name.' moved "'.$task->title.'" to '.$newStatus?->name,
                    Auth::user()
                );
            } else {
                $task->refresh()->load('assignee');
            }

            $newTagIds = $validated['tag_ids'] ?? [];
            $task->tags()->sync($newTagIds);

            if ($oldStatusId === $newStatusId) {
                if ($oldAssigneeId !== $task->assignee_id) {
                    TaskActivityLogger::log($task, 'assigned', [
                        'from' => $this->assigneeLabel($oldAssigneeId),
                        'to' => $this->assigneeLabel($task->assignee_id),
                    ]);
                    TaskNotifier::notifyAssignee(
                        $task,
                        Auth::user()->name.' assigned you to "'.$task->title.'"',
                        Auth::user()
                    );
                } else {
                    TaskActivityLogger::log($task, 'updated');
                }
            } elseif ($oldAssigneeId !== $task->assignee_id) {
                TaskActivityLogger::log($task, 'assigned', [
                    'from' => $this->assigneeLabel($oldAssigneeId),
                    'to' => $this->assigneeLabel($task->assignee_id),
                ]);
                TaskNotifier::notifyAssignee(
                    $task,
                    Auth::user()->name.' assigned you to "'.$task->title.'"',
                    Auth::user()
                );
            }

            sort($oldTagIds);
            $sortedNewTagIds = $newTagIds;
            sort($sortedNewTagIds);

            if ($oldTagIds !== $sortedNewTagIds) {
                TaskActivityLogger::log($task, 'tags_updated');
            }
        } else {
            $this->authorize('create', Task::class);

            $statusId = (int) $validated['status_id'];

            $position = ((int) Task::query()
                ->where('status_id', $statusId)
                ->whereNull('parent_task_id')
                ->max('position')) + 1;

            $task = Task::create([
                'workspace_id' => $workspace->id,
                'status_id' => $statusId,
                'title' => $validated['title'],
                'description' => $validated['description'] ?: null,
                'priority' => $validated['priority'],
                'due_date' => $validated['due_date'],
                'assignee_id' => $validated['assignee_id'],
                'created_by' => Auth::id(),
                'position' => $position,
            ]);

            $task->tags()->sync($validated['tag_ids'] ?? []);
            $task->load('assignee');

            TaskActivityLogger::log($task, 'created');

            if ($task->assignee_id && $task->assignee_id !== Auth::id()) {
                TaskNotifier::notifyAssignee(
                    $task,
                    Auth::user()->name.' assigned you to "'.$task->title.'"',
                    Auth::user()
                );
            }
        }

        $this->closeModal();
    }

    public function deleteTask(): void
    {
        if (! $this->editingTaskId) {
            return;
        }

        $task = Task::query()->findOrFail($this->editingTaskId);
        $this->authorize('delete', $task);
        $task->delete();

        $this->closeModal();
    }

    public function toggleTag(int $tagId): void
    {
        $workspace = $this->currentWorkspace();

        if (! $workspace || ! $workspace->tags()->whereKey($tagId)->exists()) {
            return;
        }

        if (in_array($tagId, $this->tag_ids, true)) {
            $this->tag_ids = array_values(array_filter(
                $this->tag_ids,
                fn (int $id): bool => $id !== $tagId
            ));
        } else {
            $this->tag_ids[] = $tagId;
        }
    }

    public function addTag(): void
    {
        $workspace = $this->currentWorkspace();

        if (! $workspace) {
            return;
        }

        $validated = $this->validate([
            'newTagName' => ['required', 'string', 'max:50'],
        ]);

        $name = trim($validated['newTagName']);

        $tag = Tag::query()->firstOrCreate(
            [
                'workspace_id' => $workspace->id,
                'name' => $name,
            ],
            [
                'color' => Tag::defaultColor(),
            ]
        );

        if (! in_array($tag->id, $this->tag_ids, true)) {
            $this->tag_ids[] = $tag->id;
        }

        $this->newTagName = '';
        $this->resetValidation('newTagName');
    }

    public function addComment(): void
    {
        if (! $this->editingTaskId) {
            return;
        }

        $task = Task::query()->with('assignee')->findOrFail($this->editingTaskId);
        $this->authorize('create', [Comment::class, $task]);

        $validated = $this->validate([
            'commentBody' => ['required', 'string', 'max:2000'],
        ]);

        Comment::query()->create([
            'task_id' => $task->id,
            'user_id' => Auth::id(),
            'body' => trim($validated['commentBody']),
        ]);

        TaskActivityLogger::log($task, 'commented');
        TaskNotifier::notifyAssignee(
            $task,
            Auth::user()->name.' commented on "'.$task->title.'"',
            Auth::user()
        );

        $this->commentBody = '';
        $this->resetValidation('commentBody');
    }

    public function deleteComment(int $commentId): void
    {
        $comment = Comment::query()->findOrFail($commentId);
        $this->authorize('delete', $comment);
        $comment->delete();
    }

    public function addSubtask(): void
    {
        if (! $this->editingTaskId) {
            return;
        }

        $parent = Task::query()->findOrFail($this->editingTaskId);
        $this->authorize('update', $parent);

        $workspace = $this->currentWorkspace();

        if (! $workspace) {
            return;
        }

        $validated = $this->validate([
            'subtaskTitle' => ['required', 'string', 'max:255'],
        ]);

        $defaultStatus = $workspace->defaultStatus();

        if (! $defaultStatus) {
            return;
        }

        $position = ((int) Task::query()
            ->where('parent_task_id', $parent->id)
            ->max('position')) + 1;

        Task::query()->create([
            'workspace_id' => $workspace->id,
            'status_id' => $defaultStatus->id,
            'parent_task_id' => $parent->id,
            'title' => trim($validated['subtaskTitle']),
            'priority' => 'medium',
            'created_by' => Auth::id(),
            'assignee_id' => Auth::id(),
            'position' => $position,
        ]);

        TaskActivityLogger::log($parent, 'subtask_added', [
            'title' => trim($validated['subtaskTitle']),
        ]);

        $this->subtaskTitle = '';
        $this->resetValidation('subtaskTitle');
    }

    public function toggleSubtaskDone(int $subtaskId): void
    {
        if (! $this->editingTaskId) {
            return;
        }

        $parent = Task::query()->findOrFail($this->editingTaskId);
        $this->authorize('update', $parent);

        $subtask = Task::query()
            ->where('parent_task_id', $parent->id)
            ->findOrFail($subtaskId);

        $workspace = $this->currentWorkspace();

        if (! $workspace) {
            return;
        }

        $doneStatus = $workspace->statuses()->where('name', 'Done')->first()
            ?? $workspace->statuses()->orderByDesc('order')->first();
        $todoStatus = $workspace->defaultStatus();

        if (! $doneStatus || ! $todoStatus) {
            return;
        }

        $markDone = $subtask->status_id !== $doneStatus->id;

        $subtask->update([
            'status_id' => $markDone ? $doneStatus->id : $todoStatus->id,
        ]);

        TaskActivityLogger::log($parent, $markDone ? 'subtask_completed' : 'subtask_reopened', [
            'title' => $subtask->title,
        ]);
    }

    public function deleteSubtask(int $subtaskId): void
    {
        if (! $this->editingTaskId) {
            return;
        }

        $parent = Task::query()->findOrFail($this->editingTaskId);
        $this->authorize('update', $parent);

        $subtask = Task::query()
            ->where('parent_task_id', $parent->id)
            ->findOrFail($subtaskId);

        TaskActivityLogger::log($parent, 'subtask_deleted', [
            'title' => $subtask->title,
        ]);

        $subtask->delete();
    }

    protected function resetTaskForm(): void
    {
        $this->reset([
            'editingTaskId',
            'title',
            'description',
            'priority',
            'due_date',
            'assignee_id',
            'status_id',
            'tag_ids',
            'newTagName',
            'commentBody',
            'subtaskTitle',
            'showActivity',
        ]);
        $this->priority = 'medium';
        $this->showActivity = false;
    }

    /**
     * @return Collection<int, User>
     */
    public function workspaceMembers(): Collection
    {
        $workspace = $this->currentWorkspace();

        if (! $workspace) {
            return collect();
        }

        return $workspace->members()
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get();
    }

    /**
     * @return Collection<int, Tag>
     */
    public function workspaceTags(): Collection
    {
        $workspace = $this->currentWorkspace();

        if (! $workspace) {
            return collect();
        }

        return $workspace->tags()->orderBy('name')->get();
    }

    /**
     * @return Collection<int, Tag>
     */
    public function selectedTags(): Collection
    {
        if ($this->tag_ids === []) {
            return collect();
        }

        return $this->workspaceTags()->whereIn('id', $this->tag_ids)->values();
    }

    /**
     * @return Collection<int, Comment>
     */
    public function editingTaskComments(): Collection
    {
        if (! $this->editingTaskId) {
            return collect();
        }

        return Comment::query()
            ->where('task_id', $this->editingTaskId)
            ->with('user')
            ->latest()
            ->get();
    }

    /**
     * @return Collection<int, \App\Models\ActivityLog>
     */
    public function editingTaskActivity(): Collection
    {
        if (! $this->editingTaskId) {
            return collect();
        }

        return \App\Models\ActivityLog::query()
            ->where('task_id', $this->editingTaskId)
            ->with('user')
            ->latest()
            ->limit(20)
            ->get();
    }

    /**
     * @return Collection<int, Task>
     */
    public function editingSubtasks(): Collection
    {
        if (! $this->editingTaskId) {
            return collect();
        }

        return Task::query()
            ->where('parent_task_id', $this->editingTaskId)
            ->with('status')
            ->orderBy('position')
            ->get();
    }

    /**
     * @return Collection<int, Status>
     */
    public function workspaceStatuses(): Collection
    {
        $workspace = $this->currentWorkspace();

        if (! $workspace) {
            return collect();
        }

        return $workspace->statuses;
    }

    protected function moveTaskToStatus(Task $task, int $newStatusId): void
    {
        if ($task->status_id === $newStatusId) {
            return;
        }

        DB::transaction(function () use ($task, $newStatusId): void {
            $oldStatusId = $task->status_id;
            $oldPosition = $task->position;

            Task::query()
                ->where('status_id', $oldStatusId)
                ->whereNull('parent_task_id')
                ->where('position', '>', $oldPosition)
                ->decrement('position');

            $newPosition = ((int) Task::query()
                ->where('status_id', $newStatusId)
                ->whereNull('parent_task_id')
                ->max('position')) + 1;

            $task->update([
                'status_id' => $newStatusId,
                'position' => $newPosition,
            ]);
        });
    }

    protected function assigneeLabel(?int $assigneeId): string
    {
        if (! $assigneeId) {
            return 'Unassigned';
        }

        return User::query()->find($assigneeId)?->name ?? 'Unknown';
    }
}
