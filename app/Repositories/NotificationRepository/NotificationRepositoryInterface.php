<?php

declare(strict_types=1);

namespace App\Repositories\NotificationRepository;

use App\Repositories\RepositoryInterface;
use Illuminate\Http\Request;

/**
 * Interface NotificationRepositoryInterface
 *
 * Provides specialized data access for user notifications, including marking as read, counting unread, and creating notifications with recipients.
 *
 * @package App\Repositories\NotificationRepository
 */
interface NotificationRepositoryInterface extends RepositoryInterface
{
    /**
     * Get a paginated list of notifications for a specific user with filtering.
     *
     * @param Request $request
     * @param int $userId
     * @return array
     */
    public function getPaginatedForUser(Request $request, int $userId): array;

    /**
     * Mark a specific notification as read for a user.
     *
     * @param int $userId
     * @param string $notificationId
     * @return bool
     */
    public function markRead(int $userId, string $notificationId): bool;

    /**
     * Mark all notifications as read for a specific user.
     *
     * @param int $userId
     * @return int Number of rows updated.
     */
    public function markReadAll(int $userId): int;

    /**
     * Delete all read notifications for a specific user.
     *
     * @param int $userId
     * @return int Number of rows deleted.
     */
    public function deleteRead(int $userId): int;

    /**
     * Count the number of unread notifications for a specific user.
     *
     * @param int $userId
     * @return int
     */
    public function countUnread(int $userId): int;

    /**
     * Create a new notification and distribute it to multiple recipients.
     *
     * @param array $data
     * @param array $recipientUserIds
     * @return int|null The ID of the created notification record.
     */
    public function createWithRecipients(array $data, array $recipientUserIds): ?int;
}
