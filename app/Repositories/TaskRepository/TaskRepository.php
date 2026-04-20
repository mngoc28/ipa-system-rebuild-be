<?php

declare(strict_types=1);

namespace App\Repositories\TaskRepository;

use App\Models\Task;
use App\Repositories\BaseRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

final class TaskRepository extends BaseRepository implements TaskRepositoryInterface
{
    /**
     * Get the model class name for the repository.
     *
     * @return string
     */
    public function getModel(): string
    {
        return Task::class;
    }

    /**
     * Get a paginated list of tasks with complex data scoping and counting.
     * Scoping ensures users only see tasks they created, are assigned to, or involve their unit.
     *
     * @param Request $request
     * @return array
     */
    public function getPaginated(Request $request): array
    {
        $page = max(1, (int) $request->input('page', 1));
        $pageSize = max(1, min(100, (int) $request->input('pageSize', 20)));
        $search = $request->input('search');
        $status = $request->input('status');
        $priority = $request->input('priority');

        $query = Task::query()
            ->with(['assignees:id,full_name,avatar_url', 'creator:id,full_name'])
            ->withCount(['comments', 'attachments']);

        // Enforce data scoping
        $user = auth()->user();
        if ($user) {
            $isStaff = $user->hasRole('STAFF') && !$user->hasRole(['ADMIN', 'DIRECTOR', 'MANAGER']);
            $isManager = $user->hasRole('MANAGER') && !$user->hasRole(['ADMIN', 'DIRECTOR']);

            if ($isStaff) {
                $query->where(function ($q) use ($user) {
                    $q->where('created_by', $user->id)
                      ->orWhereHas('assignees', function ($sub) use ($user) {
                          $sub->where('user_id', $user->id);
                      });
                });
            } elseif ($isManager) {
                $query->where(function ($q) use ($user) {
                    // Created by someone in my unit
                    $q->whereHas('creator', function ($sub) use ($user) {
                        $sub->where('primary_unit_id', $user->primary_unit_id);
                    })
                    // OR assigned to someone in my unit
                      ->orWhereHas('assignees', function ($sub) use ($user) {
                          $sub->where('primary_unit_id', $user->primary_unit_id);
                      });
                });
            }
        }

        if ($search) {
            $query->where('title', 'like', "%{$search}%");
        }

        if ($status !== null && $status !== '') {
            $query->where('status', (int) $status);
        }

        if ($priority !== null && $priority !== '') {
            $query->where('priority', (int) $priority);
        }

        $paginator = $query->orderBy('due_at', 'asc')->paginate($pageSize);

        $items = collect($paginator->items())->map(function (Task $task): array {
            return [
                'id' => (string) $task->id,
                'title' => (string) $task->title,
                'description' => (string) $task->description,
                'status' => (int) $task->status,
                'priority' => (int) $task->priority,
                'dueAt' => $task->due_at ? $task->due_at->toIso8601String() : null,
                'isOverdue' => (bool) $task->is_overdue_cache,
                'createdBy' => (int) $task->created_by,
                'creatorName' => $task->creator?->full_name ?? 'N/A',
                'createdAt' => $task->created_at->toIso8601String(),
                'assignees' => $task->assignees->map(fn($u) => [
                    'id' => (int) $u->id,
                    'name' => (string) $u->full_name,
                    'avatar' => (string) $u->avatar_url,
                ])->all(),
                'commentsCount' => (int) $task->comments_count,
                'attachmentsCount' => (int) $task->attachments_count,
                'delegationId' => $task->delegation_id,
                'eventId' => $task->event_id,
            ];
        })->all();

        return [
            'items' => $items,
            'meta' => [
                'page' => $paginator->currentPage(),
                'pageSize' => $paginator->perPage(),
                'total' => $paginator->total(),
                'totalPages' => $paginator->lastPage(),
            ],
        ];
    }

    /**
     * Create a new task and synchronize its assignees within a transaction.
     *
     * @param array $attributes
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function create($attributes = [])
    {
        return DB::transaction(function () use ($attributes) {
            $assigneeIds = $attributes['assignee_ids'] ?? [];
            unset($attributes['assignee_ids']);

            /** @var Task $task */
            $task = parent::create($attributes);

            if (!empty($assigneeIds)) {
                $task->assignees()->sync($assigneeIds);
            }

            return $task->load('assignees');
        });
    }

    /**
     * Update an existing task and its assignees within a transaction.
     *
     * @param mixed $id
     * @param array $attributes
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function update($id, $attributes = [])
    {
        return DB::transaction(function () use ($id, $attributes) {
            $assigneeIds = $attributes['assignee_ids'] ?? null;
            unset($attributes['assignee_ids']);

            /** @var Task $task */
            $task = parent::update($id, $attributes);

            if ($assigneeIds !== null) {
                $task->assignees()->sync($assigneeIds);
            }

            return $task->load('assignees');
        });
    }
}
