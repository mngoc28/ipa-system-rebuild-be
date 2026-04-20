<?php

declare(strict_types=1);

namespace App\Repositories\EventRepository;

use App\Repositories\RepositoryInterface;
use Illuminate\Http\Request;

interface EventRepositoryInterface extends RepositoryInterface
{
    public function getPaginated(Request $request): array;

    public function findById(string $id): ?array;

    public function createEvent(array $attributes, ?int $requestedBy = null): ?array;

    public function updateEvent(string $id, array $attributes): ?array;

    public function deleteEvent(string $id): bool;

    public function joinEvent(string $id, int $userId, bool $joined): ?array;

    public function requestReschedule(string $id, array $attributes, int $requestedBy): ?array;
}
