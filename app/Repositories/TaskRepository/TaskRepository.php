<?php

declare(strict_types=1);

namespace App\Repositories\TaskRepository;

use App\Models\Task;
use App\Repositories\BaseRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

final class TaskRepository extends BaseRepository implements TaskRepositoryInterface
{
    public function getModel(): string
    {
        return Task::class;
    }

    public function getPaginated(Request $request): array
    {
        $page = max(1, (int) $request->input('page', 1));
        $pageSize = max(1, min(100, (int) $request->input('pageSize', 20)));
        $search = $request->input('search');
        $status = $request->input('status');
        $priority = $request->input('priority');

        $query = DB::table('ipa_task as t')
            ->select([
                't.*',
                'u.full_name as creator_name',
            ])
            ->leftJoin('ipa_user as u', 'u.id', '=', 't.created_by');

        if ($search) {
            $query->where('t.title', 'like', "%{$search}%");
        }

        if ($status !== null && $status !== '') {
            $query->where('t.status', (int) $status);
        }

        if ($priority !== null && $priority !== '') {
            $query->where('t.priority', (int) $priority);
        }

        $total = (clone $query)->count();

        $rows = $query
            ->orderBy('t.due_at', 'asc')
            ->offset(($page - 1) * $pageSize)
            ->limit($pageSize)
            ->get();

        $items = $rows->map(function (object $row): array {
            return [
                'id' => (string) $row->id,
                'title' => (string) $row->title,
                'description' => (string) $row->description,
                'status' => (int) $row->status,
                'priority' => (int) $row->priority,
                'dueAt' => (string) $row->due_at,
                'isOverdue' => (bool) $row->is_overdue_cache,
                'createdBy' => (int) $row->created_by,
                'creatorName' => (string) $row->creator_name,
                'createdAt' => (string) $row->created_at,
            ];
        })->all();

        return [
            'items' => $items,
            'meta' => [
                'page' => $page,
                'pageSize' => $pageSize,
                'total' => $total,
                'totalPages' => (int) ceil($total / $pageSize),
            ],
        ];
    }
}
