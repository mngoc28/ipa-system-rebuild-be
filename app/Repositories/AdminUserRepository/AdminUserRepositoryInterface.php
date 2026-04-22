<?php

declare(strict_types=1);

namespace App\Repositories\AdminUserRepository;

use App\Repositories\RepositoryInterface;
use Illuminate\Http\Request;

/**
 * Interface AdminUserRepositoryInterface
 *
 * Provides specialized data access methods for administrative users.
 *
 * @package App\Repositories\AdminUserRepository
 */
interface AdminUserRepositoryInterface extends RepositoryInterface
{
    /**
     * Get paginated list of administrative users with filtering.
     *
     * @param Request $request
     * @return array
     */
    public function getPaginated(Request $request): array;

    /**
     * Find an administrative user by their UUID/UserID.
     *
     * @param string $userId
     * @return array|null
     */
    public function findByUserId(string $userId): ?array;

    /**
     * Create a new administrative user with roles.
     *
     * @param array $attributes
     * @return array
     */
    public function createUser(array $attributes): array;

    /**
     * Update an administrative user and their roles.
     *
     * @param string $userId
     * @param array $attributes
     * @return array|null
     */
    public function updateUser(string $userId, array $attributes): ?array;

    /**
     * Lock or unlock an administrative user account.
     *
     * @param string $userId
     * @param bool $locked
     * @return array|null
     */
    public function lockUser(string $userId, bool $locked): ?array;

    /**
     * Delete an administrative user.
     *
     * @param string $userId
     * @return bool
     */
    public function deleteUser(string $userId): bool;

    /**
     * Reset a user's password to the system default.
     *
     * @param string $userId
     * @return bool
     */
    public function resetPassword(string $userId): bool;

    /**
     * Get user IDs filtered by role code and unit ID.
     *
     * @param string $roleCode
     * @param int $unitId
     * @return array
     */
    public function getIdsByRoleAndUnit(string $roleCode, int $unitId): array;

    /**
     * Update the avatar path for an administrative user.
     *
     * @param string $userId
     * @param string $path
     * @return array|null
     */
    public function updateAvatar(string $userId, string $path): ?array;

    /**
     * Retrieve all available system roles.
     *
     * @return array
     */
    public function getAvailableRoles(): array;
}
