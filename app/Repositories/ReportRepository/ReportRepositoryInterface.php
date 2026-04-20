<?php

declare(strict_types=1);

namespace App\Repositories\ReportRepository;

use App\Repositories\RepositoryInterface;

/**
 * Interface ReportRepositoryInterface
 *
 * Provides specialized data access for system reports, including definition lookups and run tracking.
 *
 * @package App\Repositories\ReportRepository
 */
interface ReportRepositoryInterface extends RepositoryInterface
{
    /**
     * Get a list of all defined report templates available in the system.
     *
     * @return array
     */
    public function listDefinitions(): array;

    /**
     * Get an overall summary of report generation activities and stats.
     *
     * @return array
     */
    public function summary(): array;

    /**
     * Create a record for a specific report execution (run).
     *
     * @param array $attributes
     * @param int|null $runBy
     * @return array|null
     */
    public function createRun(array $attributes, ?int $runBy = null): ?array;

    /**
     * Find a specific report run by its unique identifier.
     *
     * @param string $runId
     * @return array|null
     */
    public function findRun(string $runId): ?array;
}
