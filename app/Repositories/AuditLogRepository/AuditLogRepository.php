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
    /**
     * Get the model class name for the repository.
     *
     * @return string
     */
    public function getModel(): string
    {
        return AuditLog::class;
    }

    /**
     * Get a paginated list of audit logs with extensive filtering by keyword, type, action, and resource.
     * Includes join with users table to resolve actor names.
     *
     * @param Request $request
     * @return array
     */
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
                DB::raw("
                    CASE 
                        WHEN log.action ILIKE '%delete%' OR log.action ILIKE '%xoa%' 
                            OR log.action ILIKE '%remove%' OR log.action ILIKE '%trash%' THEN 'system'
                        WHEN log.action ILIKE '%approve%' OR log.action ILIKE '%duyet%' 
                            OR log.action ILIKE '%success%' OR log.action ILIKE '%create%' 
                            OR log.action ILIKE '%update%' OR log.action ILIKE '%save%' THEN 'success'
                        WHEN log.action ILIKE '%warning%' OR log.action ILIKE '%risk%' 
                            OR log.action ILIKE '%lock%' OR log.action ILIKE '%blocked%' THEN 'warning'
                        ELSE 'info'
                    END as resolved_type
                ")
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

        if ($type !== '') {
            $query->whereRaw("
                CASE 
                    WHEN log.action ILIKE '%delete%' OR log.action ILIKE '%xoa%' 
                        OR log.action ILIKE '%remove%' OR log.action ILIKE '%trash%' THEN 'system'
                    WHEN log.action ILIKE '%approve%' OR log.action ILIKE '%duyet%' 
                        OR log.action ILIKE '%success%' OR log.action ILIKE '%create%' 
                        OR log.action ILIKE '%update%' OR log.action ILIKE '%save%' THEN 'success'
                    WHEN log.action ILIKE '%warning%' OR log.action ILIKE '%risk%' 
                        OR log.action ILIKE '%lock%' OR log.action ILIKE '%blocked%' THEN 'warning'
                    ELSE 'info'
                END = ?
            ", [$type]);
        }

        $paginator = $query->orderByDesc('log.created_at')
            ->orderByDesc('log.id')
            ->paginate($pageSize);

        $items = collect($paginator->items())
            ->map(function ($row): array {
                return [
                    'id' => (string) $row->id,
                    'user' => $row->actor_name ?: 'System',
                    'action' => $this->formatAction((string) $row->action),
                    'detail' => $this->formatDetail($row),
                    'time' => $this->formatTime($row->created_at),
                    'type' => (string) $row->resolved_type,
                ];
            })
            ->all();

        return [
            'items' => $items,
            'meta' => [
                'page' => $paginator->currentPage(),
                'pageSize' => $paginator->perPage(),
                'total' => $paginator->total(),
                'totalPages' => $paginator->lastPage(),
                'sortBy' => 'created_at',
                'sortDir' => 'desc',
            ],
        ];
    }

    /**
     * Format a raw action string into a human-readable headline.
     *
     * @param string $action
     * @return string
     */
    private function formatAction(string $action): string
    {
        return Str::headline(str_replace(['_', '-'], ' ', $action));
    }

    /**
     * Format the resource detail string (Resource Name + ID).
     *
     * @param object $row
     * @return string
     */
    private function formatDetail(object $row): string
    {
        $resourceType = Str::headline(str_replace(['_', '-'], ' ', (string) $row->resource_type));
        $resourceId = $row->resource_id !== null ? ' #' . $row->resource_id : '';

        return trim($resourceType . $resourceId);
    }


    /**
     * Format a timestamp into the system's preferred display format.
     *
     * @param mixed $value
     * @return string
     */
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
