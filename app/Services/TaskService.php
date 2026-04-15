<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\TaskRepository\TaskRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Throwable;

final class TaskService
{
    public function __construct(
        private readonly TaskRepositoryInterface $taskRepository,
    ) {
    }

    public function getAll(Request $request): array
    {
        try {
            return [
                'success' => true,
                'data' => $this->taskRepository->getPaginated($request),
                'message' => __('tasks.messages.fetch_success'),
            ];
        } catch (Throwable $throwable) {
            Log::error('TaskService::getAll', [
                'error' => $throwable->getMessage(),
            ]);

            return [
                'success' => false,
                'data' => null,
                'message' => __('tasks.messages.fetch_error'),
            ];
        }
    }

    public function getById(int $id): array
    {
        try {
            $task = $this->taskRepository->find($id);

            if (!$task) {
                return [
                    'success' => false,
                    'data' => null,
                    'message' => __('tasks.messages.not_found'),
                ];
            }

            return [
                'success' => true,
                'data' => $task,
                'message' => __('tasks.messages.fetch_success'),
            ];
        } catch (Throwable $throwable) {
            Log::error('TaskService::getById', [
                'id' => $id,
                'error' => $throwable->getMessage(),
            ]);

            return [
                'success' => false,
                'data' => null,
                'message' => __('tasks.messages.fetch_error'),
            ];
        }
    }

    public function create(array $data): array
    {
        try {
            $task = $this->taskRepository->create($data);

            return [
                'success' => true,
                'data' => $task,
                'message' => __('tasks.messages.create_success'),
            ];
        } catch (Throwable $throwable) {
            Log::error('TaskService::create', [
                'data' => $data,
                'error' => $throwable->getMessage(),
            ]);

            return [
                'success' => false,
                'data' => null,
                'message' => __('tasks.messages.create_error'),
            ];
        }
    }

    public function update(int $id, array $data): array
    {
        try {
            $task = $this->taskRepository->update($id, $data);

            return [
                'success' => true,
                'data' => $task,
                'message' => __('tasks.messages.update_success'),
            ];
        } catch (Throwable $throwable) {
            Log::error('TaskService::update', [
                'id' => $id,
                'data' => $data,
                'error' => $throwable->getMessage(),
            ]);

            return [
                'success' => false,
                'data' => null,
                'message' => __('tasks.messages.update_error'),
            ];
        }
    }

    public function delete(int $id): array
    {
        try {
            $deleted = $this->taskRepository->delete($id);

            return [
                'success' => $deleted,
                'data' => null,
                'message' => $deleted ? __('tasks.messages.delete_success') : __('tasks.messages.not_found'),
            ];
        } catch (Throwable $throwable) {
            Log::error('TaskService::delete', [
                'id' => $id,
                'error' => $throwable->getMessage(),
            ]);

            return [
                'success' => false,
                'data' => null,
                'message' => __('tasks.messages.delete_error'),
            ];
        }
    }
}
