<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\EventRepository\EventRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Throwable;

final class EventService
{
    public function __construct(
        private readonly EventRepositoryInterface $eventRepository,
    ) {
    }

    public function getAll(Request $request): array
    {
        try {
            return [
                'success' => true,
                'data' => $this->eventRepository->getPaginated($request),
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