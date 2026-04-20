<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\NotificationRepository\NotificationRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Throwable;

final class NotificationService
{
    public function __construct(
        private NotificationRepositoryInterface $notificationRepository,
    ) {
    }

    public function getAll(Request $request, int $userId): array
    {
        try {
            return [
                'success' => true,
                'data' => $this->notificationRepository->getPaginatedForUser($request, $userId),
                'message' => __('notifications.messages.fetch_success'),
            ];
        } catch (Throwable $throwable) {
            Log::error('NotificationService::getAll', [
                'error' => $throwable->getMessage(),
            ]);

            return [
                'success' => false,
                'data' => null,
                'message' => __('notifications.messages.fetch_error'),
            ];
        }
    }

    public function read(int $userId, string $notificationId): array
    {
        try {
            $updated = $this->notificationRepository->markRead($userId, $notificationId);

            return [
                'success' => $updated,
                'data' => ['readAt' => now()->toIso8601String()],
                'message' => $updated ? __('notifications.messages.read_success') : __('notifications.messages.not_found'),
            ];
        } catch (Throwable $throwable) {
            Log::error('NotificationService::read', [
                'userId' => $userId,
                'notificationId' => $notificationId,
                'error' => $throwable->getMessage(),
            ]);

            return [
                'success' => false,
                'data' => null,
                'message' => __('notifications.messages.read_error'),
            ];
        }
    }

    public function readAll(int $userId): array
    {
        try {
            $updatedCount = $this->notificationRepository->markReadAll($userId);

            return [
                'success' => true,
                'data' => ['updatedCount' => $updatedCount],
                'message' => __('notifications.messages.read_all_success'),
            ];
        } catch (Throwable $throwable) {
            Log::error('NotificationService::readAll', [
                'userId' => $userId,
                'error' => $throwable->getMessage(),
            ]);

            return [
                'success' => false,
                'data' => null,
                'message' => __('notifications.messages.read_all_error'),
            ];
        }
    }

    public function deleteRead(int $userId): array
    {
        try {
            $deletedCount = $this->notificationRepository->deleteRead($userId);

            return [
                'success' => true,
                'data' => ['deletedCount' => $deletedCount],
                'message' => __('notifications.messages.delete_read_success'),
            ];
        } catch (Throwable $throwable) {
            Log::error('NotificationService::deleteRead', [
                'userId' => $userId,
                'error' => $throwable->getMessage(),
            ]);

            return [
                'success' => false,
                'data' => null,
                'message' => __('notifications.messages.delete_read_error'),
            ];
        }
    }

    public function getUnreadCount(int $userId): array
    {
        try {
            return [
                'success' => true,
                'data' => ['unreadCount' => $this->notificationRepository->countUnread($userId)],
                'message' => __('notifications.messages.fetch_success'),
            ];
        } catch (Throwable $throwable) {
            Log::error('NotificationService::getUnreadCount', [
                'userId' => $userId,
                'error' => $throwable->getMessage(),
            ]);

            return [
                'success' => false,
                'data' => null,
                'message' => __('notifications.messages.fetch_error'),
            ];
        }
    }

    public function notify(array $data, array|int $recipientIds): bool
    {
        try {
            $recipientIds = is_array($recipientIds) ? $recipientIds : [$recipientIds];
            if (empty($recipientIds)) {
                return false;
            }

            return (bool) $this->notificationRepository->createWithRecipients($data, $recipientIds);
        } catch (Throwable $throwable) {
            Log::error('NotificationService::notify', [
                'error' => $throwable->getMessage(),
            ]);

            return false;
        }
    }
}
