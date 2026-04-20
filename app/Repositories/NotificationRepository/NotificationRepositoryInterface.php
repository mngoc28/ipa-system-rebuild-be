<?php

declare(strict_types=1);

namespace App\Repositories\NotificationRepository;

use App\Repositories\RepositoryInterface;
use Illuminate\Http\Request;

interface NotificationRepositoryInterface extends RepositoryInterface
{
    public function getPaginatedForUser(Request $request, int $userId): array;

    public function markRead(int $userId, string $notificationId): bool;

    public function markReadAll(int $userId): int;

    public function deleteRead(int $userId): int;

    public function countUnread(int $userId): int;

    public function createWithRecipients(array $data, array $recipientUserIds): ?int;
}
