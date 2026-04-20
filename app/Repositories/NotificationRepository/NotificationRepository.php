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
    /**
     * Get the model class name for the repository.
     *
     * @return string
     */
    public function getModel(): string
    {
        return Notification::class;
    }

    /**
     * Get a paginated list of notifications for a user, with support for filtering by unread status.
     * Includes normalization of notification types for frontend display.
     *
     * @param Request $request
     * @param int $userId
     * @return array
     */
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
                'refTable' => (string) $row->ref_table,
                'refId' => (string) $row->ref_id,
                'severity' => (int) $row->severity,
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

    /**
     * Mark a specific notification as read for a user by updating the read_at timestamp.
     *
     * @param int $userId
     * @param string $notificationId
     * @return bool
     */
    public function markRead(int $userId, string $notificationId): bool
    {
        return DB::table('ipa_notification_recipient')
            ->where('recipient_user_id', $userId)
            ->where('notification_id', (int) $notificationId)
            ->update(['read_at' => now(), 'updated_at' => now()]) > 0;
    }

    /**
     * Mark all currently unread notifications as read for a specific user.
     *
     * @param int $userId
     * @return int
     */
    public function markReadAll(int $userId): int
    {
        return DB::table('ipa_notification_recipient')
            ->where('recipient_user_id', $userId)
            ->whereNull('read_at')
            ->update(['read_at' => now(), 'updated_at' => now()]);
    }

    /**
     * Delete all notifications that have been marked as read for a specific user.
     *
     * @param int $userId
     * @return int
     */
    public function deleteRead(int $userId): int
    {
        return DB::table('ipa_notification_recipient')
            ->where('recipient_user_id', $userId)
            ->whereNotNull('read_at')
            ->delete();
    }

    /**
     * Count the total number of notifications that a user has not yet read.
     *
     * @param int $userId
     * @return int
     */
    public function countUnread(int $userId): int
    {
        return (int) DB::table('ipa_notification_recipient')
            ->where('recipient_user_id', $userId)
            ->whereNull('read_at')
            ->count();
    }

    /**
     * Create a notification record and associate it with multiple recipients in a transaction.
     *
     * @param array $data
     * @param array $recipientUserIds
     * @return int|null
     */
    public function createWithRecipients(array $data, array $recipientUserIds): ?int
    {
        return DB::transaction(function () use ($data, $recipientUserIds) {
            $notificationId = DB::table('ipa_notification')->insertGetId([
                'notification_type_id' => $data['notification_type_id'] ?? 1,
                'title' => $data['title'] ?? '',
                'body' => $data['body'] ?? '',
                'ref_table' => $data['ref_table'] ?? null,
                'ref_id' => $data['ref_id'] ?? null,
                'severity' => $data['severity'] ?? 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            if ($notificationId) {
                $recipients = array_map(fn($userId) => [
                    'notification_id' => $notificationId,
                    'recipient_user_id' => $userId,
                    'delivery_status' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ], $recipientUserIds);

                DB::table('ipa_notification_recipient')->insert($recipients);
            }

            return $notificationId;
        });
    }

    /**
     * Map complex notification content to a simple semantic type code for frontend categorization.
     *
     * @param string $typeCode
     * @param string $title
     * @param string $body
     * @return string
     */
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

    /**
     * Standardize a date value into an ISO8601 string, with timezone adjustment if necessary.
     *
     * @param mixed $value
     * @return string
     */
    private function formatDate(mixed $value): string
    {
        if ($value === null || $value === '') {
            return now()->timezone(config('app.timezone'))->toIso8601String();
        }

        return Carbon::parse((string) $value)->timezone(config('app.timezone'))->toIso8601String();
    }

    /**
     * Standardize a nullable date value into an ISO8601 string.
     *
     * @param mixed $value
     * @return string|null
     */
    private function formatNullableDate(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        return Carbon::parse((string) $value)->timezone(config('app.timezone'))->toIso8601String();
    }
}
