<?php

declare(strict_types=1);

namespace App\Repositories\TeamRepository;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

final class TeamRepository implements TeamRepositoryInterface
{
    /**
     * Get a list of organizational units with basic identifying information.
     *
     * @param Request $request
     * @return array
     */
    public function getUnits(Request $request): array
    {
        $rows = DB::table('ipa_org_unit')
            ->orderBy('unit_name')
            ->get(['id', 'unit_code', 'unit_name', 'unit_type', 'parent_unit_id', 'manager_user_id']);

        return [
            'items' => $rows->map(static function (object $row): array {
                return [
                    'id' => (string) $row->id,
                    'unitCode' => (string) $row->unit_code,
                    'unitName' => (string) $row->unit_name,
                    'unitType' => (string) $row->unit_type,
                    'parentUnitId' => $row->parent_unit_id ? (string) $row->parent_unit_id : null,
                    'managerUserId' => $row->manager_user_id ? (string) $row->manager_user_id : null,
                ];
            })->all(),
        ];
    }

    /**
     * Generate a comprehensive team dashboard including member stats, performance, and unit-scoped data.
     * Calculates performance based on task counts and overdue status.
     *
     * @param Request $request
     * @return array
     */
    public function getDashboard(Request $request): array
    {
        $unitId = $request->filled('unitId') ? (int) $request->input('unitId') : null;
        $page = max(1, (int) $request->integer('page', 1));
        $pageSize = max(1, min(100, (int) $request->integer('pageSize', 100)));
        $search = $request->input('search');

        // Force unit filtering for Manager/Staff roles if unitId is not provided
        $currentUser = auth()->user();
        if ($unitId === null && $currentUser && !$currentUser->hasRole(['ADMIN', 'DIRECTOR'])) {
            $unitId = $currentUser->primary_unit_id;
        }

        $taskStatsSubquery = DB::table('ipa_task_assignee as ta')
            ->leftJoin('ipa_task as t', 't.id', '=', 'ta.task_id')
            ->selectRaw('ta.user_id, COUNT(*) as total_tasks, SUM(CASE WHEN t.is_overdue_cache = 1 THEN 1 ELSE 0 END) as overdue_tasks')
            ->groupBy('ta.user_id');

        $baseQuery = DB::table('ipa_user as u')
            ->leftJoin('ipa_user_unit_assignment as ua', function ($join): void {
                $join->on('ua.user_id', '=', 'u.id')
                    ->where('ua.is_primary', '=', 1);
            })
            ->leftJoin('ipa_org_unit as ou', 'ou.id', '=', 'ua.unit_id')
            ->leftJoinSub($taskStatsSubquery, 'task_stats', 'task_stats.user_id', '=', 'u.id')
            ->select([
                'u.id',
                'u.full_name',
                'u.email',
                'u.avatar_url',
                'u.status',
                'u.last_login_at',
                'ua.position_title',
                'ou.unit_code',
                'ou.unit_name',
                DB::raw('COALESCE(task_stats.total_tasks, 0) as total_tasks'),
                DB::raw('COALESCE(task_stats.overdue_tasks, 0) as overdue_tasks'),
            ]);

        if ($search) {
            $baseQuery->where('u.full_name', 'like', '%' . $search . '%');
        }

        if ($unitId !== null) {
            $baseQuery->where('u.primary_unit_id', $unitId);
        }

        $baseQuery->orderBy('u.full_name');

        $allRows = (clone $baseQuery)
            ->orderBy('u.full_name')
            ->get();

        $total = $allRows->count();

        $rows = (clone $baseQuery)
            ->orderBy('u.full_name')
            ->offset(($page - 1) * $pageSize)
            ->limit($pageSize)
            ->get();

        $members = $rows->map(function (object $row): array {
            $totalTasks = (int) $row->total_tasks;
            $overdueTasks = (int) $row->overdue_tasks;
            $performance = max(0, min(100, 100 - ($overdueTasks * 15) - max(0, $totalTasks - $overdueTasks)));

            return [
                'id' => (string) $row->id,
                'name' => (string) $row->full_name,
                'role' => $this->resolveRoleName((string) $row->position_title, (string) $row->unit_name),
                'email' => (string) $row->email,
                'status' => $this->resolveStatus((int) $row->status, $row->last_login_at, $totalTasks, $overdueTasks),
                'tasks' => $totalTasks,
                'performance' => $performance,
                'unitName' => (string) ($row->unit_name ?? ''),
                'avatarUrl' => $row->avatar_url
                    ? (str_starts_with($row->avatar_url, 'http')
                        ? $row->avatar_url
                        : rtrim((string) config('app.url'), '/') . '/storage/' . $row->avatar_url)
                    : null,
            ];
        })->values()->all();

        $activities = $this->resolveActivities($unitId);
        $summary = $this->resolveSummary($allRows);

        return [
            'members' => $members,
            'activities' => $activities,
            'summary' => array_merge($summary, ['totalMembers' => $total]),
            'meta' => [
                'page' => $page,
                'pageSize' => $pageSize,
                'total' => $total,
                'totalPages' => (int) ceil($total / $pageSize),
            ],
        ];
    }

    /**
     * Create a new team member and assign them to a primary organizational unit.
     *
     * @param array $attributes
     * @return array
     */
    public function createMember(array $attributes): array
    {
        $nextIndex = ((int) DB::table('ipa_user')->count()) + 1;
        $fullName = trim((string) Arr::get($attributes, 'fullName', 'Nhân sự mới #' . $nextIndex));
        $username = (string) Arr::get($attributes, 'username', 'team.' . Str::slug($fullName) . '.' . Str::lower(Str::random(4)));
        $email = (string) Arr::get($attributes, 'email', 'team.' . Str::slug($fullName) . '.' . $nextIndex . '@danang.gov.vn');
        $positionTitle = trim((string) Arr::get($attributes, 'positionTitle', 'Chuyên viên'));
        $unitId = Arr::get($attributes, 'unitId');

        if ($unitId === null || $unitId === '') {
            $unitId = DB::table('ipa_org_unit')->orderBy('id')->value('id');
        }

        $existingUserId = DB::table('ipa_user')->where('email', $email)->value('id');
        if ($existingUserId !== null) {
            return $this->normalizeMember((int) $existingUserId);
        }

        $userId = DB::table('ipa_user')->insertGetId([
            'username' => $username,
            'email' => $email,
            'full_name' => $fullName,
            'phone' => Arr::get($attributes, 'phone', '0900000000'),
            'avatar_url' => Arr::get($attributes, 'avatarUrl'),
            'status' => 1,
            'primary_unit_id' => $unitId,
            'last_login_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('ipa_user_unit_assignment')->updateOrInsert(
            [
                'user_id' => $userId,
                'unit_id' => $unitId,
                'is_primary' => 1,
            ],
            [
                'position_title' => $positionTitle,
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );

        return $this->normalizeMember($userId);
    }

    /**
     * Standardize a team member's information into a response array.
     *
     * @param int $userId
     * @return array
     */
    private function normalizeMember(int $userId): array
    {
        $row = DB::table('ipa_user as u')
            ->leftJoin('ipa_user_unit_assignment as ua', function ($join): void {
                $join->on('ua.user_id', '=', 'u.id')
                    ->where('ua.is_primary', '=', 1);
            })
            ->leftJoin('ipa_org_unit as ou', 'ou.id', '=', 'ua.unit_id')
            ->leftJoinSub(
                DB::table('ipa_task_assignee as ta')
                    ->leftJoin('ipa_task as t', 't.id', '=', 'ta.task_id')
                    ->selectRaw('ta.user_id, COUNT(*) as total_tasks, SUM(CASE WHEN t.is_overdue_cache = 1 THEN 1 ELSE 0 END) as overdue_tasks')
                    ->groupBy('ta.user_id'),
                'task_stats',
                'task_stats.user_id',
                '=',
                'u.id'
            )
            ->select([
                'u.id',
                'u.full_name',
                'u.email',
                'u.avatar_url',
                'u.status',
                'u.last_login_at',
                'ua.position_title',
                'ou.unit_name',
                DB::raw('COALESCE(task_stats.total_tasks, 0) as total_tasks'),
                DB::raw('COALESCE(task_stats.overdue_tasks, 0) as overdue_tasks'),
            ])
            ->where('u.id', $userId)
            ->first();

        if ($row === null) {
            return [];
        }

        $totalTasks = (int) $row->total_tasks;
        $overdueTasks = (int) $row->overdue_tasks;

        return [
            'id' => (string) $row->id,
            'name' => (string) $row->full_name,
            'role' => $this->resolveRoleName((string) $row->position_title, (string) $row->unit_name),
            'email' => (string) $row->email,
            'status' => $this->resolveStatus((int) $row->status, $row->last_login_at, $totalTasks, $overdueTasks),
            'tasks' => $totalTasks,
            'performance' => max(0, min(100, 100 - ($overdueTasks * 15) - max(0, $totalTasks - $overdueTasks))),
            'unitName' => (string) ($row->unit_name ?? ''),
            'avatarUrl' => $row->avatar_url
                ? (str_starts_with($row->avatar_url, 'http')
                    ? $row->avatar_url
                    : rtrim((string) config('app.url'), '/') . '/storage/' . $row->avatar_url)
                : null,
        ];
    }

    /**
     * Fetch recent team activities, combining task updates and login events.
     *
     * @param int|null $unitId
     * @return array
     */
    private function resolveActivities(?int $unitId): array
    {
        $taskActivities = DB::table('ipa_task_status_history as tsh')
            ->join('ipa_task as t', 't.id', '=', 'tsh.task_id')
            ->join('ipa_user as u', 'u.id', '=', 'tsh.changed_by')
            ->leftJoin('ipa_user_unit_assignment as ua', function ($join): void {
                $join->on('ua.user_id', '=', 'u.id')
                    ->where('ua.is_primary', '=', 1);
            })
            ->when($unitId !== null, fn ($query) => $query->where('ua.unit_id', $unitId))
            ->orderByDesc('tsh.changed_at')
            ->limit(3)
            ->get([
                'u.full_name as user_name',
                't.title as task_title',
                'tsh.changed_at',
            ]);

        $activities = $taskActivities->map(function (object $row): array {
            return [
                'user' => (string) $row->user_name,
                'action' => 'Cập nhật công việc: ' . (string) $row->task_title,
                'time' => $this->relativeTime($row->changed_at),
            ];
        })->all();

        if (count($activities) >= 3) {
            return $activities;
        }

        $loginActivities = DB::table('ipa_user as u')
            ->leftJoin('ipa_user_unit_assignment as ua', function ($join): void {
                $join->on('ua.user_id', '=', 'u.id')
                    ->where('ua.is_primary', '=', 1);
            })
            ->when($unitId !== null, fn ($query) => $query->where('ua.unit_id', $unitId))
            ->whereNotNull('u.last_login_at')
            ->orderByDesc('u.last_login_at')
            ->limit(3)
            ->get(['u.full_name as user_name', 'u.last_login_at']);

        foreach ($loginActivities as $row) {
            if (count($activities) >= 3) {
                break;
            }

            $activities[] = [
                'user' => (string) $row->user_name,
                'action' => 'Đăng nhập gần nhất',
                'time' => $this->relativeTime($row->last_login_at),
            ];
        }

        return array_slice($activities, 0, 3);
    }

    /**
     * Map user metrics and status to a human-readable attendance or workflow state.
     *
     * @param int $userStatus
     * @param mixed $lastLoginAt
     * @param int $totalTasks
     * @param int $overdueTasks
     * @return string
     */
    private function resolveStatus(int $userStatus, mixed $lastLoginAt, int $totalTasks, int $overdueTasks): string
    {
        if ($userStatus !== 1) {
            return 'On Leave';
        }

        $loginAt = $lastLoginAt ? Carbon::parse((string) $lastLoginAt) : null;

        if ($loginAt !== null && $loginAt->greaterThan(now()->subHours(8))) {
            return 'In Office';
        }

        if ($overdueTasks > 0) {
            return 'On Field';
        }

        return $totalTasks === 0 ? 'On Leave' : 'In Office';
    }

    /**
     * Resolve a descriptive role name for a user based on their position and unit.
     *
     * @param string|null $positionTitle
     * @param string|null $unitName
     * @return string
     */
    private function resolveRoleName(?string $positionTitle, ?string $unitName): string
    {
        $positionTitle = trim((string) $positionTitle);

        if ($positionTitle !== '') {
            return $positionTitle;
        }

        return $unitName !== null && trim($unitName) !== '' ? trim($unitName) : 'Chuyên viên';
    }

    /**
     * Convert a datetime value into a Vietnamese relative time string (e.g., "10p trước").
     *
     * @param mixed $dateTime
     * @return string
     */
    private function relativeTime(mixed $dateTime): string
    {
        if ($dateTime === null) {
            return 'Vừa xong';
        }

        $time = Carbon::parse((string) $dateTime);
        $minutes = max(0, now()->diffInMinutes($time));

        if ($minutes < 60) {
            return $minutes . 'p trước';
        }

        $hours = intdiv($minutes, 60);

        if ($hours < 24) {
            return $hours . 'h trước';
        }

        $days = intdiv($hours, 24);

        return $days . ' ngày trước';
    }

    /**
     * Aggregate member statuses into a summary counts object.
     *
     * @param \Illuminate\Support\Collection<int, mixed> $rows
     * @return array
     */
    private function resolveSummary($rows): array
    {
        $members = $rows->map(function (object $row): string {
            return $this->resolveStatus((int) $row->status, $row->last_login_at, (int) $row->total_tasks, (int) $row->overdue_tasks);
        })->all();

        return [
            'inOffice' => count(array_filter($members, static fn (string $status): bool => $status === 'In Office')),
            'onField' => count(array_filter($members, static fn (string $status): bool => $status === 'On Field')),
            'onLeave' => count(array_filter($members, static fn (string $status): bool => $status === 'On Leave')),
        ];
    }
}
