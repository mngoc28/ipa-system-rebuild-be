<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\MinutesRepository\MinutesRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Throwable;

final class MinutesService
{
    public function __construct(
        private MinutesRepositoryInterface $minutesRepository,
    ) {
    }

    public function getAll(Request $request): array
    {
        try {
            return [
                'success' => true,
                'data' => $this->minutesRepository->getPaginated($request),
                'message' => __('minutes.messages.fetch_success'),
            ];
        } catch (Throwable $throwable) {
            Log::error('MinutesService::getAll', ['error' => $throwable->getMessage()]);

            return [
                'success' => false,
                'data' => null,
                'message' => __('minutes.messages.fetch_error'),
            ];
        }
    }

    public function getById(string $id): array
    {
        try {
            $result = $this->minutesRepository->findDetail($id);

            if ($result === null) {
                return [
                    'success' => false,
                    'data' => null,
                    'message' => __('minutes.messages.not_found'),
                ];
            }

            return [
                'success' => true,
                'data' => $result,
                'message' => __('minutes.messages.fetch_success'),
            ];
        } catch (Throwable $throwable) {
            Log::error('MinutesService::getById', ['id' => $id, 'error' => $throwable->getMessage()]);

            return [
                'success' => false,
                'data' => null,
                'message' => __('minutes.messages.fetch_error'),
            ];
        }
    }

    public function create(array $attributes, int $ownerUserId): array
    {
        try {
            $result = $this->minutesRepository->createMinutes($attributes, $ownerUserId);

            if ($result === null) {
                return [
                    'success' => false,
                    'data' => null,
                    'message' => __('minutes.messages.create_error'),
                ];
            }

            return [
                'success' => true,
                'data' => $result,
                'message' => __('minutes.messages.create_success'),
            ];
        } catch (Throwable $throwable) {
            Log::error('MinutesService::create', ['error' => $throwable->getMessage()]);

            return [
                'success' => false,
                'data' => null,
                'message' => __('minutes.messages.create_error'),
            ];
        }
    }

    public function createVersion(string $id, array $attributes, int $editedBy): array
    {
        try {
            $result = $this->minutesRepository->createVersion($id, $attributes, $editedBy);

            if ($result === null) {
                return [
                    'success' => false,
                    'data' => null,
                    'message' => __('minutes.messages.version_error'),
                ];
            }

            return [
                'success' => true,
                'data' => $result,
                'message' => __('minutes.messages.version_success'),
            ];
        } catch (Throwable $throwable) {
            Log::error('MinutesService::createVersion', ['id' => $id, 'error' => $throwable->getMessage()]);

            return [
                'success' => false,
                'data' => null,
                'message' => __('minutes.messages.version_error'),
            ];
        }
    }

    public function createComment(string $id, array $attributes, int $commenterUserId): array
    {
        try {
            $result = $this->minutesRepository->createComment($id, $attributes, $commenterUserId);

            if ($result === null) {
                return [
                    'success' => false,
                    'data' => null,
                    'message' => __('minutes.messages.comment_error'),
                ];
            }

            return [
                'success' => true,
                'data' => $result,
                'message' => __('minutes.messages.comment_success'),
            ];
        } catch (Throwable $throwable) {
            Log::error('MinutesService::createComment', ['id' => $id, 'error' => $throwable->getMessage()]);

            return [
                'success' => false,
                'data' => null,
                'message' => __('minutes.messages.comment_error'),
            ];
        }
    }

    public function approve(string $id, array $attributes, int $approverUserId): array
    {
        try {
            $result = $this->minutesRepository->approve($id, $attributes, $approverUserId);

            if ($result === null) {
                return [
                    'success' => false,
                    'data' => null,
                    'message' => __('minutes.messages.approve_error'),
                ];
            }

            return [
                'success' => true,
                'data' => $result,
                'message' => __('minutes.messages.approve_success'),
            ];
        } catch (Throwable $throwable) {
            Log::error('MinutesService::approve', ['id' => $id, 'error' => $throwable->getMessage()]);

            return [
                'success' => false,
                'data' => null,
                'message' => __('minutes.messages.approve_error'),
            ];
        }
    }
}
