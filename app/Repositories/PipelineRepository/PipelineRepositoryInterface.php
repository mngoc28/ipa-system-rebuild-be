<?php

declare(strict_types=1);

namespace App\Repositories\PipelineRepository;

use App\Repositories\RepositoryInterface;
use Illuminate\Http\Request;

interface PipelineRepositoryInterface extends RepositoryInterface
{
    public function getPaginated(Request $request): array;

    public function summary(): array;

    public function createProject(array $attributes, ?int $ownerUserId = null): ?array;

    public function updateProject(string $projectId, array $attributes): ?array;

    public function deleteProject(string $projectId): bool;

    public function patchStage(string $projectId, string $newStageIdentifier, ?string $reason = null, ?int $changedBy = null): ?array;

    public function findProject(string $projectId): ?array;
}
