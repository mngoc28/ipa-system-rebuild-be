<?php

declare(strict_types=1);

namespace App\Repositories\SystemSettingRepository;

use App\Repositories\RepositoryInterface;

/**
 * Interface SystemSettingRepositoryInterface
 *
 * Provides specialized data access for system-wide configuration settings, typically stored as key-value pairs grouped by functional area.
 *
 * @package App\Repositories\SystemSettingRepository
 */
interface SystemSettingRepositoryInterface extends RepositoryInterface
{
    /**
     * Get a list of all valid configuration groups (e.g., GENERAL, SECURITY, SMTP).
     *
     * @return array
     */
    public function getAllowedGroups(): array;

    /**
     * Get all configuration items, optionally filtered by a set of group names.
     *
     * @param array|null $groups
     * @return array
     */
    public function getAllByGroups(?array $groups = null): array;

    /**
     * Batch save configuration items, updating their values and recording the user responsible.
     *
     * @param array $items
     * @param int|null $updatedBy
     * @return int Number of successfully saved items.
     */
    public function saveItems(array $items, ?int $updatedBy = null): int;

    /**
     * Retrieve the resolved value of a specific setting by its unique key.
     *
     * @param string $key
     * @return string|null
     */
    public function getResolvedValue(string $key): ?string;

    /**
     * Check if a specific setting key exists in the system.
     *
     * @param string $key
     * @return bool
     */
    public function hasValue(string $key): bool;
}
