<?php

declare(strict_types=1);

namespace App\Repositories\MasterDataRepository;

use App\Repositories\RepositoryInterface;
use Illuminate\Http\Request;

/**
 * Interface MasterDataRepositoryInterface
 *
 * Provides specialized data access for system-wide master data (domain-based lookup tables).
 *
 * @package App\Repositories\MasterDataRepository
 */
interface MasterDataRepositoryInterface extends RepositoryInterface
{
    /**
     * Get a list of all valid master data domains (e.g., COUNTRIES, SECTORS).
     *
     * @return array
     */
    public function getAllowedDomains(): array;

    /**
     * Get all items or search within a specific domain.
     *
     * @param string $domain
     * @param Request $request
     * @return array
     */
    public function getAllOrSearch(string $domain, Request $request): array;

    /**
     * Find a specific item by domain and ID.
     *
     * @param string $domain
     * @param string $id
     * @return array|null
     */
    public function findByDomainAndId(string $domain, string $id): ?array;

    /**
     * Create a new item within a specific domain.
     *
     * @param string $domain
     * @param array $attributes
     * @return array
     */
    public function createItem(string $domain, array $attributes): array;

    /**
     * Update an existing item within a specific domain.
     *
     * @param string $domain
     * @param string $id
     * @param array $attributes
     * @return array|null
     */
    public function updateItem(string $domain, string $id, array $attributes): ?array;

    /**
     * Delete an item from a specific domain.
     *
     * @param string $domain
     * @param string $id
     * @return bool
     */
    public function deleteItem(string $domain, string $id): bool;
}
