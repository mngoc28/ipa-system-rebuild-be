<?php

declare(strict_types=1);

namespace App\Repositories\AuditLogRepository;

use App\Models\AuditLog;
use App\Repositories\BaseRepository;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

final class AuditLogRepository extends BaseRepository implements AuditLogRepositoryInterface
{
    public function getModel(): string
    {
        return AuditLog::class;
    }

    public function getPaginated(Request $request): array
    {
        $page = max(1, (int) $request->input('page', 1));
        $pageSize = max(1, min(100, (int) $request->input('pageSize', 5)));
        $keyword = trim((string) $request->input('keyword', ''));
        $type = trim((string) $request->input('type', ''));
        $actorUserId = trim((string) $request->input('actorUserId', ''));
        $action = trim((string) $request->input('action', ''));
        $resourceType = trim((string) $request->input('resourceType', ''));

        $query = DB::table('ipa_audit_log as log')
            ->leftJoin('ipa_user as user', 'user.id', '=', 'log.actor_user_id')
            ->select([
                'log.id',
                'log.actor_user_id',
                'log.action',
                'log.resource_type',
                'log.resource_id',
                'log.before_json',
                'log.after_json',
                'log.ip_address',
                'log.user_agent',
                'log.created_at',
                'user.full_name as actor_name',
            ]);

        if ($keyword !== '') {
            $query->where(function ($builder) use ($keyword): void {
                $builder->where('log.action', 'like', '%' . $keyword . '%')
                    ->orWhere('log.resource_type', 'like', '%' . $keyword . '%')
                    ->orWhere('user.full_name', 'like', '%' . $keyword . '%')
                    ->orWhere('log.ip_address', 'like', '%' . $keyword . '%')
                    ->orWhere('log.user_agent', 'like', '%' . $keyword . '%');
            });
        }

        if ($actorUserId !== '' && ctype_digit($actorUserId)) {
            $query->where('log.actor_user_id', (int) $actorUserId);
        }

        if ($action !== '') {
            $query->where('log.action', 'like', '%' . $action . '%');
        }

        if ($resourceType !== '') {
            $query->where('log.resource_type', 'like', '%' . $resourceType . '%');
        }

        $rows = $query
            ->orderByDesc('log.created_at')
            ->orderByDesc('log.id')
            ->get();

        $items = $rows
            ->map(function ($row): array {
                $resolvedType = $this->resolveType((string) $row->action, (string) $row->resource_type);

                return [
                    'id' => (string) $row->id,
                    'user' => $row->actor_name ?: 'System',
                    'action' => $this->formatAction((string) $row->action),
                    'detail' => $this->formatDetail($row),
                    'time' => $this->formatTime($row->created_at),
                    'type' => $resolvedType,
                ];
            })
            ->when($type !== '', static function ($collection) use ($type) {
                return $collection->filter(static fn (array $item): bool => $item['type'] === $type);
            })
            ->values();

        $total = $items->count();
        $items = $items->slice(($page - 1) * $pageSize, $pageSize)->values()->all();

        return [
            'items' => $items,
            'meta' => [
                'page' => $page,
                'pageSize' => $pageSize,
                'total' => $total,
                'totalPages' => (int) ceil($total / $pageSize),
                'sortBy' => 'created_at',
                'sortDir' => 'desc',
            ],
        ];
    }

    private function formatAction(string $action): string
    {
        return Str::headline(str_replace(['_', '-'], ' ', $action));
    }

    private function formatDetail(object $row): string
    {
        $resourceType = Str::headline(str_replace(['_', '-'], ' ', (string) $row->resource_type));
        $resourceId = $row->resource_id !== null ? ' #' . $row->resource_id : '';

        return trim($resourceType . $resourceId);
    }

    private function resolveType(string $action, string $resourceType): string
    {
        $action = Str::lower($action . ' ' . $resourceType);

        if (Str::contains($action, ['delete', 'xoa', 'remove', 'trash'])) {
            return 'system';
        }

        if (Str::contains($action, ['approve', 'duyet', 'success', 'create', 'update', 'save'])) {
            return 'success';
        }

        if (Str::contains($action, ['warning', 'risk', 'lock', 'blocked'])) {
            return 'warning';
        }

        return 'info';
    }

    private function formatTime(mixed $value): string
    {
        if ($value === null || $value === '') {
            return now()->timezone(config('app.timezone'))->format('H:i - d/m/Y');
        }

        return Carbon::parse((string) $value)
            ->timezone(config('app.timezone'))
            ->format('H:i - d/m/Y');
    }
}