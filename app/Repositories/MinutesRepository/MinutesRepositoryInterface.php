<?php

declare(strict_types=1);

namespace App\Repositories\MinutesRepository;

use App\Repositories\RepositoryInterface;
use Illuminate\Http\Request;

interface MinutesRepositoryInterface extends RepositoryInterface
{
    public function getPaginated(Request $request): array;

    public function findDetail(string $id): ?array;

    public function createMinutes(array $attributes, int $ownerUserId): ?array;

    public function createVersion(string $id, array $attributes, int $editedBy): ?array;

    public function createComment(string $id, array $attributes, int $commenterUserId): ?array;

    public function approve(string $id, array $attributes, int $approverUserId): ?array;
}