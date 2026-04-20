<?php

declare(strict_types=1);

namespace App\Repositories\EventRepository;

use App\Repositories\RepositoryInterface;
use Illuminate\Http\Request;

/**
 * Interface EventRepositoryInterface
 *
 * Provides specialized data access for system events and scheduling.
 *
 * @package App\Repositories\EventRepository
 */
interface EventRepositoryInterface extends RepositoryInterface
{
    /**
     * Get a paginated list of events with filtering (scope, type, dates, etc.).
     *
     * @param Request $request
     * @return array
     */
    public function getPaginated(Request $request): array;

    /**
     * Find a specific event by ID and return detailed information.
     *
     * @param string $id
     * @return array|null
     */
    public function findById(string $id): ?array;

    /**
     * Create a new event with optional requester tracking.
     *
     * @param array $attributes
     * @param int|null $requestedBy
     * @return array|null
     */
    public function createEvent(array $attributes, ?int $requestedBy = null): ?array;

    /**
     * Update an existing event's details.
     *
     * @param string $id
     * @param array $attributes
     * @return array|null
     */
    public function updateEvent(string $id, array $attributes): ?array;

    /**
     * Delete an event by its ID.
     *
     * @param string $id
     * @return bool
     */
    public function deleteEvent(string $id): bool;

    /**
     * Logic for joining/unjoining an event by a specific user.
     *
     * @param string $id
     * @param int $userId
     * @param bool $joined
     * @return array|null
     */
    public function joinEvent(string $id, int $userId, bool $joined): ?array;

    /**
     * Record a formal reschedule request for an event.
     *
     * @param string $id
     * @param array $attributes
     * @param int $requestedBy
     * @return array|null
     */
    public function requestReschedule(string $id, array $attributes, int $requestedBy): ?array;
}
