<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\PipelineRepository\PipelineRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Class PipelineService
 *
 * Orchestrates business logic for investment pipeline projects, including lifecycle transitions,
 * summary aggregation, and project details management.
 *
 * @package App\Services
 */
final class PipelineService
{
    /**
     * PipelineService constructor.
     *
     * @param PipelineRepositoryInterface $pipelineRepository
     */
    public function __construct(
        private PipelineRepositoryInterface $pipelineRepository,
    ) {
    }

    /**
     * Retrieve a paginated list of investment projects with filtering support.
     *
     * @param Request $request
     * @return array Standard response bundle.
     */
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

    /**
     * Get a summary of projects across different stages (Kanban board overview).
     *
     * @return array Standard response bundle.
     */
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

    /**
     * Create a new investment pipeline project.
     *
     * @param array $attributes Project details.
     * @param int|null $ownerUserId Identifier of the project owner.
     * @return array Standard response bundle.
     */
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

    /**
     * Transition a project to a new stage in the pipeline.
     *
     * @param string $projectId
     * @param string $newStageIdentifier
     * @param string|null $reason Optional reason for the stage change.
     * @param int|null $changedBy Identifier of the user making the change.
     * @return array Standard response bundle.
     */
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

    /**
     * Update an existing project's metadata.
     *
     * @param string $projectId
     * @param array $attributes New data to apply.
     * @return array Standard response bundle.
     */
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

    /**
     * Permanently remove a project and its history from the system.
     *
     * @param string $projectId
     * @return array Standard response bundle.
     */
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

    /**
     * Find specific project details by identifier.
     *
     * @param string $projectId
     * @return array Standard response bundle.
     */
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
