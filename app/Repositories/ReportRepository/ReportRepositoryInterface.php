<?php

declare(strict_types=1);

namespace App\Repositories\ReportRepository;

use App\Repositories\RepositoryInterface;

interface ReportRepositoryInterface extends RepositoryInterface
{
    public function listDefinitions(): array;

    public function summary(): array;

    public function createRun(array $attributes, ?int $runBy = null): ?array;

    public function findRun(string $runId): ?array;
}