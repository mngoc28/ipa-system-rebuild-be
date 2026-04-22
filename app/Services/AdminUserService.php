<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\AdminUserRepository\AdminUserRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Class AdminUserService
 *
 * Orchestrates business logic for administrative user management, wrapping repository calls with error handling and logging.
 *
 * @package App\Services
 */
final class AdminUserService
{
    /**
     * AdminUserService constructor.
     *
     * @param AdminUserRepositoryInterface $adminUserRepository
     */
    public function __construct(
        private AdminUserRepositoryInterface $adminUserRepository,
    ) {
    }

    /**
     * Get a paginated list of all administrative users.
     *
     * @param Request $request
     * @return array Response structure with success status, data, and translated message.
     */
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

    /**
     * Retrieve a specific administrative user by their identifier.
     *
     * @param string $userId
     * @return array|null Normalized user data or null on failure.
     */
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

    /**
     * Create a new administrative user record.
     *
     * @param array $attributes User details to persist.
     * @return array|null Created user data or null on failure.
     */
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

    /**
     * Update an existing administrative user's information.
     *
     * @param string $userId Identifier of the user to update.
     * @param array $attributes New attributes to apply.
     * @return array|null Updated user data or null on failure.
     */
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

    /**
     * Toggle the locked status of an administrative user.
     *
     * @param string $userId
     * @param bool $locked Whether to lock (true) or unlock (false) the user.
     * @return array|null Updated user data or null on failure.
     */
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

    /**
     * Permanently delete an administrative user.
     *
     * @param string $userId
     * @return bool True if deleted successfully, false otherwise.
     */
    public function delete(string $userId): bool
    {
        try {
            return (bool) $this->adminUserRepository->delete($userId);
        } catch (Throwable $throwable) {
            Log::error('AdminUserService::delete', [
                'userId' => $userId,
                'error' => $throwable->getMessage(),
            ]);

            return false;
        }
    }
    /**
     * Update the avatar path for a specific user.
     *
     * @param string $userId
     * @param string $path Local or remote path to the avatar image.
     * @return array|null Updated user data or null on failure.
     */
    public function updateAvatar(string $userId, string $path): ?array
    {
        try {
            return $this->adminUserRepository->updateAvatar($userId, $path);
        } catch (Throwable $throwable) {
            Log::error('AdminUserService::updateAvatar', [
                'userId' => $userId,
                'path' => $path,
                'error' => $throwable->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Get a list of all available system roles.
     *
     * @return array
     */
    public function getRoles(): array
    {
        try {
            return $this->adminUserRepository->getAvailableRoles();
        } catch (Throwable $throwable) {
            Log::error('AdminUserService::getRoles', [
                'error' => $throwable->getMessage(),
            ]);

            return [];
        }
    }

    /**
     * Reset a user's password to the default value.
     *
     * @param string $userId
     * @return bool
     */
    public function resetPassword(string $userId): bool
    {
        try {
            return $this->adminUserRepository->resetPassword($userId);
        } catch (Throwable $throwable) {
            Log::error('AdminUserService::resetPassword', [
                'userId' => $userId,
                'error' => $throwable->getMessage(),
            ]);

            return false;
        }
    }
}
