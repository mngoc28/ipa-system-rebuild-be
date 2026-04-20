<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\MasterDataRepository\MasterDataRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Throwable;

final class MasterDataService
{
    public function __construct(
        private MasterDataRepositoryInterface $masterDataRepository,
    ) {
    }

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
