<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\PipelineRepository\PipelineRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Throwable;

final class PipelineService
{
    public function __construct(
        private readonly PipelineRepositoryInterface $pipelineRepository,
    ) {
    }

    public function getProjects(Request $request): array
    {
        try {
            return [
                'success' => true,
                'data' => $this->pipelineRepository->getPaginated($request),
                'message' => __('pipeline.messages.fetch_success'),
            ];
        } catch (Throwable $throwable) {
            Log::error('PipelineService::getProjects', [
                'error' => $throwable->getMessage(),
            ]);

            return [
                'success' => false,
                'data' => null,
                'message' => __('pipeline.messages.fetch_error'),
            ];
        }
    }

    public function getSummary(): array
    {
        try {
            return [
                'success' => true,
                'data' => $this->pipelineRepository->summary(),
                'message' => __('pipeline.messages.fetch_success'),
            ];
        } catch (Throwable $throwable) {
            Log::error('PipelineService::getSummary', [
                'error' => $throwable->getMessage(),
            ]);

            return [
                'success' => false,
                'data' => null,
                'message' => __('pipeline.messages.fetch_error'),
            ];
        }
    }

    public function createProject(array $attributes, ?int $ownerUserId = null): array
    {
        try {
            $result = $this->pipelineRepository->createProject($attributes, $ownerUserId);

            if ($result === null) {
                return [
                    'success' => false,
                    'data' => null,
                    'message' => __('pipeline.messages.not_found'),
                ];
            }

            return [
                'success' => true,
                'data' => $result,
                'message' => __('pipeline.messages.create_success'),
            ];
        } catch (Throwable $throwable) {
            Log::error('PipelineService::createProject', [
                'error' => $throwable->getMessage(),
            ]);

            return [
                'success' => false,
                'data' => null,
                'message' => __('pipeline.messages.create_error'),
            ];
        }
    }

    public function patchStage(string $projectId, string $newStageIdentifier, ?string $reason = null, ?int $changedBy = null): array
    {
        try {
            $result = $this->pipelineRepository->patchStage($projectId, $newStageIdentifier, $reason, $changedBy);

            if ($result === null) {
                return [
                    'success' => false,
                    'data' => null,
                    'message' => __('pipeline.messages.not_found'),
                ];
            }

            return [
                'success' => true,
                'data' => $result,
                'message' => __('pipeline.messages.stage_update_success'),
            ];
        } catch (Throwable $throwable) {
            Log::error('PipelineService::patchStage', [
                'projectId' => $projectId,
                'error' => $throwable->getMessage(),
            ]);

            return [
                'success' => false,
                'data' => null,
                'message' => __('pipeline.messages.stage_update_error'),
            ];
        }
    }

    public function updateProject(string $projectId, array $attributes): array
    {
        try {
            $result = $this->pipelineRepository->updateProject($projectId, $attributes);

            if ($result === null) {
                return [
                    'success' => false,
                    'data' => null,
                    'message' => __('pipeline.messages.not_found'),
                ];
            }

            return [
                'success' => true,
                'data' => $result,
                'message' => __('pipeline.messages.update_success'),
            ];
        } catch (Throwable $throwable) {
            Log::error('PipelineService::updateProject', [
                'projectId' => $projectId,
                'error' => $throwable->getMessage(),
            ]);

            return [
                'success' => false,
                'data' => null,
                'message' => __('pipeline.messages.update_error'),
            ];
        }
    }

    public function deleteProject(string $projectId): array
    {
        try {
            $result = $this->pipelineRepository->deleteProject($projectId);

            if (! $result) {
                return [
                    'success' => false,
                    'message' => __('pipeline.messages.delete_error'),
                ];
            }

            return [
                'success' => true,
                'message' => __('pipeline.messages.delete_success'),
            ];
        } catch (Throwable $throwable) {
            Log::error('PipelineService::deleteProject', [
                'projectId' => $projectId,
                'error' => $throwable->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => __('pipeline.messages.delete_error'),
            ];
        }
    }

    public function findProject(string $projectId): array
    {
        try {
            $result = $this->pipelineRepository->findProject($projectId);

            if ($result === null) {
                return [
                    'success' => false,
                    'data' => null,
                    'message' => __('pipeline.messages.not_found'),
                ];
            }

            return [
                'success' => true,
                'data' => $result,
                'message' => __('pipeline.messages.fetch_success'),
            ];
        } catch (Throwable $throwable) {
            Log::error('PipelineService::findProject', [
                'projectId' => $projectId,
                'error' => $throwable->getMessage(),
            ]);

            return [
                'success' => false,
                'data' => null,
                'message' => __('pipeline.messages.fetch_error'),
            ];
        }
    }
}