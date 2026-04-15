<?php

declare(strict_types=1);

namespace App\Repositories\NotificationRepository;

use App\Models\Notification;
use App\Repositories\BaseRepository;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

final class NotificationRepository extends BaseRepository implements NotificationRepositoryInterface
{
    public function getModel(): string
    {
        return Notification::class;
    }

    public function getPaginatedForUser(Request $request, int $userId): array
    {
        $page = max(1, (int) $request->input('page', 1));
        $pageSize = max(1, min(100, (int) $request->input('pageSize', 20)));
        $unreadOnly = $request->boolean('unreadOnly', false);

        $query = DB::table('ipa_notification_recipient as recipient')
            ->join('ipa_notification as notification', 'notification.id', '=', 'recipient.notification_id')
            ->leftJoin('ipa_md_notification_type as type', 'type.id', '=', 'notification.notification_type_id')
            ->where('recipient.recipient_user_id', $userId)
            ->select([
                'recipient.id as recipient_id',
                'recipient.notification_id',
                'recipient.read_at',
                'recipient.delivery_status',
                'recipient.created_at as recipient_created_at',
                'recipient.updated_at as recipient_updated_at',
                'notification.id as notification_id',
                'notification.title',
                'notification.body',
                'notification.ref_table',
                'notification.ref_id',
                'notification.severity',
                'notification.created_at',
                'notification.updated_at',
                'type.code as type_code',
                'type.name_vi as type_name_vi',
            ]);

        if ($unreadOnly) {
            $query->whereNull('recipient.read_at');
        }

        $total = (clone $query)->count();

        $rows = $query
            ->orderByDesc('notification.created_at')
            ->orderByDesc('notification.id')
            ->offset(($page - 1) * $pageSize)
            ->limit($pageSize)
            ->get();

        $items = $rows->map(function (object $row): array {
            $type = $this->normalizeType((string) ($row->type_code ?? ''), (string) $row->title, (string) $row->body);

            return [
                'id' => (string) $row->notification_id,
                'type' => $type,
                'title' => (string) $row->title,
                'description' => (string) $row->body,
                'message' => (string) $row->body,
                'createdAt' => $this->formatDate($row->created_at ?? null),
                'readAt' => $this->formatNullableDate($row->read_at ?? null),
                'read' => $row->read_at !== null,
            ];
        })->all();

        return [
            'items' => $items,
            'unreadCount' => $this->countUnread($userId),
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

    public function markRead(int $userId, string $notificationId): bool
    {
        return DB::table('ipa_notification_recipient')
            ->where('recipient_user_id', $userId)
            ->where('notification_id', (int) $notificationId)
            ->update(['read_at' => now(), 'updated_at' => now()]) > 0;
    }

    public function markReadAll(int $userId): int
    {
        return DB::table('ipa_notification_recipient')
            ->where('recipient_user_id', $userId)
            ->whereNull('read_at')
            ->update(['read_at' => now(), 'updated_at' => now()]);
    }

    public function deleteRead(int $userId): int
    {
        return DB::table('ipa_notification_recipient')
            ->where('recipient_user_id', $userId)
            ->whereNotNull('read_at')
            ->delete();
    }

    private function countUnread(int $userId): int
    {
        return (int) DB::table('ipa_notification_recipient')
            ->where('recipient_user_id', $userId)
            ->whereNull('read_at')
            ->count();
    }

    private function normalizeType(string $typeCode, string $title, string $body): string
    {
        $value = Str::lower(trim($typeCode . ' ' . $title . ' ' . $body));

        if (Str::contains($value, ['approval', 'duyet', 'approve'])) {
            return 'approval';
        }

        if (Str::contains($value, ['meeting', 'hop', 'calendar', 'schedule'])) {
            return 'meeting';
        }

        if (Str::contains($value, ['assignment', 'task', 'nhiem vu', 'phân công', 'phan cong'])) {
            return 'assignment';
        }

        return 'system';
    }

    private function formatDate(mixed $value): string
    {
        if ($value === null || $value === '') {
            return now()->timezone(config('app.timezone'))->toIso8601String();
        }

        return Carbon::parse((string) $value)->timezone(config('app.timezone'))->toIso8601String();
    }

    private function formatNullableDate(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        return Carbon::parse((string) $value)->timezone(config('app.timezone'))->toIso8601String();
    }
}