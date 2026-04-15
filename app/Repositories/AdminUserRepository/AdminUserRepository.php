<?php

declare(strict_types=1);

namespace App\Repositories\AdminUserRepository;

use App\Models\AdminUser;
use App\Repositories\BaseRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Throwable;

final class AdminUserRepository extends BaseRepository implements AdminUserRepositoryInterface
{
    public function getModel(): string
    {
        return AdminUser::class;
    }

    public function getPaginated(Request $request): array
    {
        $page = max(1, (int) $request->input('page', 1));
        $pageSize = max(1, min(100, (int) $request->input('pageSize', 5)));
        $sortField = (string) $request->input('sortField', 'created_at');
        $sortDirection = strtolower((string) $request->input('sortDirection', 'desc'));

        $allowedSortFields = ['id', 'username', 'email', 'full_name', 'status', 'created_at', 'updated_at'];

        if (! in_array($sortField, $allowedSortFields, true)) {
            $sortField = 'created_at';
        }

        if (! in_array($sortDirection, ['asc', 'desc'], true)) {
            $sortDirection = 'desc';
        }

        $query = DB::table('ipa_user as user')
            ->select([
                'user.id',
                'user.username',
                'user.email',
                'user.full_name',
                'user.phone',
                'user.avatar_url',
                'user.status',
                'user.primary_unit_id',
                'user.last_login_at',
                'user.created_at',
                'user.updated_at',
            ]);

        $keyword = trim((string) $request->input('keyword', ''));

        if ($keyword !== '') {
            $query->where(function ($builder) use ($keyword): void {
                $builder->where('user.username', 'like', '%' . $keyword . '%')
                    ->orWhere('user.email', 'like', '%' . $keyword . '%')
                    ->orWhere('user.full_name', 'like', '%' . $keyword . '%')
                    ->orWhere('user.phone', 'like', '%' . $keyword . '%');
            });
        }

        $status = (string) $request->input('status', '');
        if (in_array($status, ['active', 'inactive'], true)) {
            $query->where('user.status', $status === 'active' ? 1 : 0);
        }

        $roleId = trim((string) $request->input('roleId', ''));
        if ($roleId !== '') {
            $resolvedRoleIds = $this->resolveRoleIds([$roleId]);
            if ($resolvedRoleIds !== []) {
                $query->whereExists(function ($builder) use ($resolvedRoleIds): void {
                    $builder->select(DB::raw(1))
                        ->from('ipa_user_role as user_role')
                        ->whereColumn('user_role.user_id', 'user.id')
                        ->whereIn('user_role.role_id', $resolvedRoleIds);
                });
            }
        }

        $unitId = trim((string) $request->input('unitId', ''));
        if ($unitId !== '') {
            $resolvedUnitId = $this->resolveUnitId($unitId);
            if ($resolvedUnitId !== null) {
                $query->where(function ($builder) use ($resolvedUnitId): void {
                    $builder->where('user.primary_unit_id', $resolvedUnitId)
                        ->orWhereExists(function ($subQuery) use ($resolvedUnitId): void {
                            $subQuery->select(DB::raw(1))
                                ->from('ipa_user_unit_assignment as assignment')
                                ->whereColumn('assignment.user_id', 'user.id')
                                ->where('assignment.unit_id', $resolvedUnitId);
                        });
                });
            }
        }

        $total = (clone $query)->count();

        $rows = $query
            ->orderBy('user.' . $sortField, $sortDirection)
            ->offset(($page - 1) * $pageSize)
            ->limit($pageSize)
            ->get();

        $userIds = $rows->pluck('id')->map(static fn ($id): int => (int) $id)->all();

        $roleMap = $this->loadRoleMap($userIds);
        $unitMap = $this->loadUnitMap($userIds);

        $items = $rows->map(function ($row) use ($roleMap, $unitMap): array {
            $userId = (int) $row->id;

            return $this->normalizeUser($row, $roleMap[$userId] ?? [], $unitMap[$userId] ?? null);
        })->all();

        return [
            'items' => $items,
            'meta' => [
                'page' => $page,
                'pageSize' => $pageSize,
                'total' => $total,
                'totalPages' => (int) ceil($total / $pageSize),
                'sortBy' => $sortField,
                'sortDir' => $sortDirection,
            ],
        ];
    }

    public function findByUserId(string $userId): ?array
    {
        $row = DB::table('ipa_user as user')
            ->select([
                'user.id',
                'user.username',
                'user.email',
                'user.full_name',
                'user.phone',
                'user.avatar_url',
                'user.status',
                'user.primary_unit_id',
                'user.last_login_at',
                'user.created_at',
                'user.updated_at',
            ])
            ->where('user.id', $userId)
            ->first();

        if (! $row) {
            return null;
        }

        $roles = $this->loadRoleMap([(int) $row->id]);
        $units = $this->loadUnitMap([(int) $row->id]);

        return $this->normalizeUser($row, $roles[(int) $row->id] ?? [], $units[(int) $row->id] ?? null);
    }

    public function createUser(array $attributes): array
    {
        return DB::transaction(function () use ($attributes): array {
            $now = now();

            $payload = [
                'username' => Arr::get($attributes, 'username'),
                'email' => Arr::get($attributes, 'email'),
                'full_name' => Arr::get($attributes, 'fullName'),
                'phone' => Arr::get($attributes, 'phone'),
                'avatar_url' => null,
                'status' => 1,
                'primary_unit_id' => $this->resolveUnitId((string) Arr::get($attributes, 'unitId')),
                'last_login_at' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ];

            $userId = (int) DB::table('ipa_user')->insertGetId($payload);

            $this->syncUserRoles($userId, (array) Arr::get($attributes, 'roleIds', []));
            $this->syncUserUnit($userId, Arr::get($attributes, 'unitId'));

            return $this->findByUserId((string) $userId) ?? [];
        });
    }

    public function updateUser(string $userId, array $attributes): ?array
    {
        return DB::transaction(function () use ($userId, $attributes): ?array {
            $record = DB::table('ipa_user')->where('id', $userId)->first();

            if (! $record) {
                return null;
            }

            $updates = [];

            if (Arr::has($attributes, 'fullName')) {
                $updates['full_name'] = Arr::get($attributes, 'fullName');
            }

            if (Arr::has($attributes, 'phone')) {
                $updates['phone'] = Arr::get($attributes, 'phone');
            }

            if (Arr::has($attributes, 'status')) {
                $updates['status'] = Arr::get($attributes, 'status') === 'active' ? 1 : 0;
            }

            if (Arr::has($attributes, 'unitId')) {
                $updates['primary_unit_id'] = $this->resolveUnitId((string) Arr::get($attributes, 'unitId'));
            }

            if ($updates !== []) {
                $updates['updated_at'] = now();
                DB::table('ipa_user')->where('id', $userId)->update($updates);
            }

            if (Arr::has($attributes, 'roleIds')) {
                $this->syncUserRoles((int) $userId, (array) Arr::get($attributes, 'roleIds', []));
            }

            if (Arr::has($attributes, 'unitId')) {
                $this->syncUserUnit((int) $userId, Arr::get($attributes, 'unitId'));
            }

            return $this->findByUserId($userId);
        });
    }

    public function lockUser(string $userId, bool $locked): ?array
    {
        return DB::transaction(function () use ($userId, $locked): ?array {
            $record = DB::table('ipa_user')->where('id', $userId)->first();

            if (! $record) {
                return null;
            }

            DB::table('ipa_user')->where('id', $userId)->update([
                'status' => $locked ? 0 : 1,
                'updated_at' => now(),
            ]);

            return [
                'locked' => $locked,
            ];
        });
    }

    private function loadRoleMap(array $userIds): array
    {
        if ($userIds === []) {
            return [];
        }

        $rows = DB::table('ipa_user_role as user_role')
            ->join('ipa_role as role', 'role.id', '=', 'user_role.role_id')
            ->whereIn('user_role.user_id', $userIds)
            ->select([
                'user_role.user_id',
                'role.id as role_id',
                'role.code as role_code',
                'role.name as role_name',
            ])
            ->orderBy('user_role.id', 'asc')
            ->get();

        $grouped = [];

        foreach ($rows as $row) {
            $grouped[(int) $row->user_id][] = (string) ($row->role_code ?: $row->role_name ?: $row->role_id);
        }

        return $grouped;
    }

    private function loadUnitMap(array $userIds): array
    {
        if ($userIds === []) {
            return [];
        }

        $rows = DB::table('ipa_user_unit_assignment as assignment')
            ->join('ipa_org_unit as unit', 'unit.id', '=', 'assignment.unit_id')
            ->whereIn('assignment.user_id', $userIds)
            ->select([
                'assignment.user_id',
                'unit.id as unit_id',
                'unit.unit_code',
                'unit.unit_name',
                'assignment.position_title',
                'assignment.is_primary',
            ])
            ->orderByDesc('assignment.is_primary')
            ->orderBy('assignment.id', 'asc')
            ->get();

        $grouped = [];

        foreach ($rows as $row) {
            $userId = (int) $row->user_id;

            if (! isset($grouped[$userId])) {
                $grouped[$userId] = [
                    'id' => (string) $row->unit_id,
                    'unit_code' => (string) $row->unit_code,
                    'unit_name' => (string) $row->unit_name,
                ];
            }
        }

        return $grouped;
    }

    private function normalizeUser(object $row, array $roles = [], ?array $unit = null): array
    {
        return [
            'id' => (string) $row->id,
            'fullName' => (string) $row->full_name,
            'email' => (string) $row->email,
            'roles' => array_values($roles),
            'permissions' => [],
            'unit' => $unit,
            'status' => ((int) $row->status === 1 ? 'active' : 'inactive'),
            'locked' => (int) $row->status !== 1,
        ];
    }

    private function syncUserRoles(int $userId, array $roleIds): void
    {
        DB::table('ipa_user_role')->where('user_id', $userId)->delete();

        $resolvedRoleIds = $this->resolveRoleIds($roleIds);

        if ($resolvedRoleIds === []) {
            $fallbackRoleId = DB::table('ipa_role')->value('id');
            if ($fallbackRoleId !== null) {
                $resolvedRoleIds = [(int) $fallbackRoleId];
            }
        }

        if ($resolvedRoleIds === []) {
            return;
        }

        $now = now();
        $rows = array_map(static function (int $roleId) use ($userId, $now): array {
            return [
                'user_id' => $userId,
                'role_id' => $roleId,
                'effective_from' => $now,
                'effective_to' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }, $resolvedRoleIds);

        DB::table('ipa_user_role')->insert($rows);
    }

    private function syncUserUnit(int $userId, mixed $unitId): void
    {
        $resolvedUnitId = $this->resolveUnitId((string) $unitId);

        DB::table('ipa_user_unit_assignment')->where('user_id', $userId)->delete();

        if ($resolvedUnitId === null) {
            $fallbackUnitId = DB::table('ipa_org_unit')->value('id');
            $resolvedUnitId = $fallbackUnitId !== null ? (int) $fallbackUnitId : null;
        }

        if ($resolvedUnitId === null) {
            return;
        }

        DB::table('ipa_user_unit_assignment')->insert([
            'user_id' => $userId,
            'unit_id' => $resolvedUnitId,
            'position_title' => null,
            'is_primary' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function resolveUnitId(string $unitId): ?int
    {
        $trimmed = trim($unitId);

        if ($trimmed === '') {
            $fallbackUnitId = DB::table('ipa_org_unit')->value('id');

            return $fallbackUnitId !== null ? (int) $fallbackUnitId : null;
        }

        if (ctype_digit($trimmed)) {
            $existing = DB::table('ipa_org_unit')->where('id', (int) $trimmed)->value('id');

            return $existing !== null ? (int) $existing : null;
        }

        $matched = DB::table('ipa_org_unit')->where('unit_code', $trimmed)->value('id');

        if ($matched !== null) {
            return (int) $matched;
        }

        $fallbackUnitId = DB::table('ipa_org_unit')->value('id');

        return $fallbackUnitId !== null ? (int) $fallbackUnitId : null;
    }

    private function resolveRoleIds(array $roleIds): array
    {
        if ($roleIds === []) {
            return [];
        }

        $numericIds = collect($roleIds)
            ->map(static fn ($roleId): string => trim((string) $roleId))
            ->filter(static fn (string $roleId): bool => $roleId !== '')
            ->all();

        $resolved = [];

        foreach ($numericIds as $roleId) {
            if (ctype_digit($roleId)) {
                $existing = DB::table('ipa_role')->where('id', (int) $roleId)->value('id');
                if ($existing !== null) {
                    $resolved[] = (int) $existing;
                }

                continue;
            }

            $matched = DB::table('ipa_role')->where('code', $roleId)->value('id');
            if ($matched !== null) {
                $resolved[] = (int) $matched;
            }
        }

        return array_values(array_unique($resolved));
    }
}