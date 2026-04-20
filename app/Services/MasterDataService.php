<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\MasterDataRepository\MasterDataRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Class MasterDataService
 *
 * Provides a unified entry point for managing "Master Data" across various domains
 * (e.g., units, categories, statuses) with standardized CRUD operations.
 *
 * @package App\Services
 */
final class MasterDataService
{
    /**
     * MasterDataService constructor.
     *
     * @param MasterDataRepositoryInterface $masterDataRepository
     */
    public function __construct(
        private MasterDataRepositoryInterface $masterDataRepository,
    ) {
    }

    /**
     * Retrieve all items within a specific master data domain, supporting search.
     *
     * @param Request $request Search and filter parameters.
     * @param string $domain The specific master data category (e.g., 'ipa_org_unit').
     * @return array Standard response bundle.
     */
    public function getAll(Request $request, string $domain): array
    {
        try {
            return [
                'success' => true,
                'data' => $this->masterDataRepository->getAllOrSearch($domain, $request),
                'message' => __('master_data.messages.fetch_success'),
            ];
        } catch (Throwable $throwable) {
            Log::error('MasterDataService::getAll', [
                'domain' => $domain,
                'error' => $throwable->getMessage(),
            ]);

            return [
                'success' => false,
                'data' => null,
                'message' => __('master_data.messages.fetch_error'),
            ];
        }
    }

    /**
     * Find a single master data record by ID within a specific domain.
     *
     * @param string $domain The domain to search in.
     * @param string $id The unique identifier of the record.
     * @return array|null Normalized record data or null if not found.
     */
    public function getById(string $domain, string $id): ?array
    {
        try {
            return $this->masterDataRepository->findByDomainAndId($domain, $id);
        } catch (Throwable $throwable) {
            Log::error('MasterDataService::getById', [
                'domain' => $domain,
                'id' => $id,
                'error' => $throwable->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Create a new record in the specified master data domain.
     *
     * @param string $domain The domain to insert into.
     * @param array $attributes Data attributes for the new record.
     * @return array|null Created record data or null on failure.
     */
    public function create(string $domain, array $attributes): ?array
    {
        try {
            return $this->masterDataRepository->createItem($domain, $attributes);
        } catch (Throwable $throwable) {
            Log::error('MasterDataService::create', [
                'domain' => $domain,
                'error' => $throwable->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Update an existing record in the specified master data domain.
     *
     * @param string $domain The domain where the record resides.
     * @param string $id The record identifier.
     * @param array $attributes New attributes to apply.
     * @return array|null Updated record data or null on failure.
     */
    public function update(string $domain, string $id, array $attributes): ?array
    {
        try {
            return $this->masterDataRepository->updateItem($domain, $id, $attributes);
        } catch (Throwable $throwable) {
            Log::error('MasterDataService::update', [
                'domain' => $domain,
                'id' => $id,
                'error' => $throwable->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Remove a record from a master data domain.
     *
     * @param string $domain The domain where the record resides.
     * @param string $id The record identifier.
     * @return bool|null True if deleted, false if soft-delete failed, null on exception.
     */
    public function delete(string $domain, string $id): bool|null
    {
        try {
            return $this->masterDataRepository->deleteItem($domain, $id);
        } catch (Throwable $throwable) {
            Log::error('MasterDataService::delete', [
                'domain' => $domain,
                'id' => $id,
                'error' => $throwable->getMessage(),
            ]);

            return null;
        }
    }
}
