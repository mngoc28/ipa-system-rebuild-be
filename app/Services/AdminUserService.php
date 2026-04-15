<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\AdminUserRepository\AdminUserRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Throwable;

final class AdminUserService
{
    public function __construct(
        private readonly AdminUserRepositoryInterface $adminUserRepository,
    ) {
    }

    public function getAll(Request $request): array
    {
        try {
            return [
                'success' => true,
                'data' => $this->adminUserRepository->getPaginated($request),
                'message' => __('admin_users.messages.fetch_success'),
            ];
        } catch (Throwable $throwable) {
            Log::error('AdminUserService::getAll', [
                'error' => $throwable->getMessage(),
            ]);

            return [
                'success' => false,
                'data' => null,
                'message' => __('admin_users.messages.fetch_error'),
            ];
        }
    }

    public function getById(string $userId): ?array
    {
        try {
            return $this->adminUserRepository->findByUserId($userId);
        } catch (Throwable $throwable) {
            Log::error('AdminUserService::getById', [
                'userId' => $userId,
                'error' => $throwable->getMessage(),
            ]);

            return null;
        }
    }

    public function create(array $attributes): ?array
    {
        try {
            return $this->adminUserRepository->createUser($attributes);
        } catch (Throwable $throwable) {
            Log::error('AdminUserService::create', [
                'error' => $throwable->getMessage(),
            ]);

            return null;
        }
    }

    public function update(string $userId, array $attributes): ?array
    {
        try {
            return $this->adminUserRepository->updateUser($userId, $attributes);
        } catch (Throwable $throwable) {
            Log::error('AdminUserService::update', [
                'userId' => $userId,
                'error' => $throwable->getMessage(),
            ]);

            return null;
        }
    }

    public function lock(string $userId, bool $locked): ?array
    {
        try {
            return $this->adminUserRepository->lockUser($userId, $locked);
        } catch (Throwable $throwable) {
            Log::error('AdminUserService::lock', [
                'userId' => $userId,
                'locked' => $locked,
                'error' => $throwable->getMessage(),
            ]);

            return null;
        }
    }
}