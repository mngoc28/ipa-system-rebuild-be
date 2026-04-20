<?php

declare(strict_types=1);

namespace App\Repositories\SystemSettingRepository;

use App\Repositories\RepositoryInterface;

interface SystemSettingRepositoryInterface extends RepositoryInterface
{
    public function getAllowedGroups(): array;

    public function getAllByGroups(?array $groups = null): array;

    public function saveItems(array $items, ?int $updatedBy = null): int;

    public function getResolvedValue(string $key): ?string;

    public function hasValue(string $key): bool;
}
