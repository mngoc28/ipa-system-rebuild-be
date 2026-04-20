<?php

declare(strict_types=1);

namespace App\Repositories\PipelineRepository;

use App\Repositories\RepositoryInterface;
use Illuminate\Http\Request;

/**
 * Interface PipelineRepositoryInterface
 *
 * Provides specialized data access for investment pipeline/project management, including stage transitions and summaries.
 *
 * @package App\Repositories\PipelineRepository
 */
interface PipelineRepositoryInterface extends RepositoryInterface
{
    /**
     * Get a paginated list of pipeline projects with filtering and search.
     *
     * @param Request $request
     * @return array
     */
    public function getPaginated(Request $request): array;

    /**
     * Get a summary of projects grouped by their current pipeline stage.
     *
     * @return array
     */
    public function summary(): array;

    /**
     * Create a new investment project in the pipeline.
     *
     * @param array $attributes
     * @param int|null $ownerUserId
     * @return array|null
     */
    public function createProject(array $attributes, ?int $ownerUserId = null): ?array;

    /**
     * Update an existing pipeline project's details.
     *
     * @param string $projectId
     * @param array $attributes
     * @return array|null
     */
    public function updateProject(string $projectId, array $attributes): ?array;

    /**
     * Delete a project from the pipeline.
     *
     * @param string $projectId
     * @return bool
     */
    public function deleteProject(string $projectId): bool;

    /**
     * Transition a project to a new pipeline stage.
     *
     * @param string $projectId
     * @param string $newStageIdentifier
     * @param string|null $reason
     * @param int|null $changedBy
     * @return array|null
     */
    public function patchStage(string $projectId, string $newStageIdentifier, ?string $reason = null, ?int $changedBy = null): ?array;

    /**
     * Find a specific pipeline project by ID and return its full details.
     *
     * @param string $projectId
     * @return array|null
     */
    public function findProject(string $projectId): ?array;
}
