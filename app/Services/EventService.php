<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\EventRepository\EventRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Class EventService
 *
 * Orchestrates business logic for calendar events, including registration,
 * rescheduling requests, and participation management.
 *
 * @package App\Services
 */
final class EventService
{
    /**
     * EventService constructor.
     *
     * @param EventRepositoryInterface $eventRepository
     */
    public function __construct(
        private EventRepositoryInterface $eventRepository,
    ) {
    }

    /**
     * Retrieve a paginated list of events, optionally scoped by a specific user's visibility.
     *
     * @param Request $request
     * @param int|null $authUserId Contextual user identifier for visibility filtering.
     * @return array Standard response structure with event data.
     */
    public function getAll(Request $request, ?int $authUserId = null): array
    {
        try {
            return [
                'success' => true,
                'data' => $this->eventRepository->getPaginated($request, $authUserId),
                'message' => __('events.messages.fetch_success'),
            ];
        } catch (Throwable $throwable) {
            Log::error('EventService::getAll', ['error' => $throwable->getMessage()]);

            return [
                'success' => false,
                'data' => null,
                'message' => __('events.messages.fetch_error'),
            ];
        }
    }

    /**
     * Retrieve details for a specific event by ID.
     *
     * @param string $id
     * @return array Standard response bundle.
     */
    public function getById(string $id): array
    {
        try {
            $event = $this->eventRepository->findById($id);

            if ($event === null) {
                return [
                    'success' => false,
                    'data' => null,
                    'message' => __('events.messages.not_found'),
                ];
            }

            return [
                'success' => true,
                'data' => $event,
                'message' => __('events.messages.fetch_success'),
            ];
        } catch (Throwable $throwable) {
            Log::error('EventService::getById', ['id' => $id, 'error' => $throwable->getMessage()]);

            return [
                'success' => false,
                'data' => null,
                'message' => __('events.messages.fetch_error'),
            ];
        }
    }

    /**
     * Create a new calendar event.
     *
     * @param array $attributes Event details (title, start, end, etc.).
     * @param int|null $requestedBy Identifier of the user initiating the creation.
     * @return array Standard response bundle with the created event.
     */
    public function create(array $attributes, ?int $requestedBy = null): array
    {
        try {
            $event = $this->eventRepository->createEvent($attributes, $requestedBy);

            if ($event === null) {
                return [
                    'success' => false,
                    'data' => null,
                    'message' => __('events.messages.not_found'),
                ];
            }

            return [
                'success' => true,
                'data' => $event,
                'message' => __('events.messages.create_success'),
            ];
        } catch (Throwable $throwable) {
            Log::error('EventService::create', ['error' => $throwable->getMessage()]);

            return [
                'success' => false,
                'data' => null,
                'message' => __('events.messages.create_error'),
            ];
        }
    }

    /**
     * Update an existing event's details.
     *
     * @param string $id
     * @param array $attributes Data to update.
     * @return array Standard response bundle with the updated event.
     */
    public function update(string $id, array $attributes): array
    {
        try {
            $event = $this->eventRepository->updateEvent($id, $attributes);

            if ($event === null) {
                return [
                    'success' => false,
                    'data' => null,
                    'message' => __('events.messages.not_found'),
                ];
            }

            return [
                'success' => true,
                'data' => $event,
                'message' => __('events.messages.update_success'),
            ];
        } catch (Throwable $throwable) {
            Log::error('EventService::update', ['id' => $id, 'error' => $throwable->getMessage()]);

            return [
                'success' => false,
                'data' => null,
                'message' => __('events.messages.update_error'),
            ];
        }
    }

    /**
     * Permanently remove an event and its associated attendance records.
     *
     * @param string $id
     * @return array Standard response bundle indicating success status.
     */
    public function delete(string $id): array
    {
        try {
            $deleted = $this->eventRepository->deleteEvent($id);

            return [
                'success' => $deleted,
                'data' => null,
                'message' => $deleted ? __('events.messages.delete_success') : __('events.messages.not_found'),
            ];
        } catch (Throwable $throwable) {
            Log::error('EventService::delete', ['id' => $id, 'error' => $throwable->getMessage()]);

            return [
                'success' => false,
                'data' => null,
                'message' => __('events.messages.delete_error'),
            ];
        }
    }

    /**
     * Handle user participation (joining or leaving) an event.
     *
     * @param string $id
     * @param int $userId The participant's identifier.
     * @param bool $joined True to join, false to leave.
     * @return array Standard response bundle.
     */
    public function join(string $id, int $userId, bool $joined): array
    {
        try {
            $result = $this->eventRepository->joinEvent($id, $userId, $joined);

            if ($result === null) {
                return [
                    'success' => false,
                    'data' => null,
                    'message' => __('events.messages.not_found'),
                ];
            }

            return [
                'success' => true,
                'data' => $result,
                'message' => $joined ? __('events.messages.join_success') : __('events.messages.leave_success'),
            ];
        } catch (Throwable $throwable) {
            Log::error('EventService::join', ['id' => $id, 'error' => $throwable->getMessage()]);

            return [
                'success' => false,
                'data' => null,
                'message' => __('events.messages.join_error'),
            ];
        }
    }

    /**
     * Submit a request to change the timing of an event.
     *
     * @param string $id
     * @param array $attributes Reschedule specifics (proposed_start, reason, etc.).
     * @param int $requestedBy Identifier of the user requesting the change.
     * @return array Standard response bundle.
     */
    public function requestReschedule(string $id, array $attributes, int $requestedBy): array
    {
        try {
            $result = $this->eventRepository->requestReschedule($id, $attributes, $requestedBy);

            if ($result === null) {
                return [
                    'success' => false,
                    'data' => null,
                    'message' => __('events.messages.not_found'),
                ];
            }

            return [
                'success' => true,
                'data' => $result,
                'message' => __('events.messages.reschedule_success'),
            ];
        } catch (Throwable $throwable) {
            Log::error('EventService::requestReschedule', ['id' => $id, 'error' => $throwable->getMessage()]);

            return [
                'success' => false,
                'data' => null,
                'message' => __('events.messages.reschedule_error'),
            ];
        }
    }
}
