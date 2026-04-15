<?php

declare(strict_types=1);

namespace App\Repositories\MasterDataRepository;

use App\Repositories\RepositoryInterface;
use Illuminate\Http\Request;

interface MasterDataRepositoryInterface extends RepositoryInterface
{
    public function getAllowedDomains(): array;

    public function getAllOrSearch(string $domain, Request $request): array;

    public function findByDomainAndId(string $domain, string $id): ?array;

    public function createItem(string $domain, array $attributes): array;

    public function updateItem(string $domain, string $id, array $attributes): ?array;

    public function deleteItem(string $domain, string $id): bool;
}
