<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\ReportRepository\ReportRepositoryInterface;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Class ReportService
 *
 * Orchestrates business logic for system reports, including definition lookups,
 * summary aggregation, and report execution runs.
 *
 * @package App\Services
 */
final class ReportService
{
    /**
     * ReportService constructor.
     *
     * @param ReportRepositoryInterface $reportRepository
     */
    public function __construct(
        private ReportRepositoryInterface $reportRepository,
    ) {
    }

    /**
     * Retrieve all available report definitions.
     *
     * @return array Standard response bundle.
     */
    public function getDefinitions(): array
    {
        try {
            return [
                'success' => true,
                'data' => $this->reportRepository->listDefinitions(),
                'message' => __('reports.messages.fetch_definitions_success'),
            ];
        } catch (Throwable $throwable) {
            Log::error('ReportService::getDefinitions', [
                'error' => $throwable->getMessage(),
            ]);

            return [
                'success' => false,
                'data' => null,
                'message' => __('reports.messages.fetch_definitions_error'),
            ];
        }
    }

    /**
     * Get a summary of metrics and KPIs (dashboard overview).
     *
     * @return array Standard response bundle.
     */
    public function getSummary(): array
    {
        try {
            return [
                'success' => true,
                'data' => $this->reportRepository->summary(),
                'message' => __('reports.messages.fetch_definitions_success'),
            ];
        } catch (Throwable $throwable) {
            Log::error('ReportService::getSummary', [
                'error' => $throwable->getMessage(),
            ]);

            return [
                'success' => false,
                'data' => null,
                'message' => __('reports.messages.fetch_definitions_error'),
            ];
        }
    }

    /**
     * Execute a new report run based on provided definitions and filters.
     *
     * @param array $attributes Report parameters (definition_id, filters, etc.).
     * @param int|null $runBy Identifier of the user initiating the report.
     * @return array Standard response bundle with the created run results.
     */
    public function createRun(array $attributes, ?int $runBy = null): array
    {
        try {
            $result = $this->reportRepository->createRun($attributes, $runBy);

            if ($result === null) {
                return [
                    'success' => false,
                    'data' => null,
                    'message' => __('reports.messages.not_found'),
                ];
            }

            return [
                'success' => true,
                'data' => $result,
                'message' => __('reports.messages.create_run_success'),
            ];
        } catch (Throwable $throwable) {
            Log::error('ReportService::createRun', [
                'error' => $throwable->getMessage(),
            ]);

            return [
                'success' => false,
                'data' => null,
                'message' => __('reports.messages.create_run_error'),
            ];
        }
    }

    /**
     * Retrieve the results of a specific report run.
     *
     * @param string $runId
     * @return array Standard response bundle with run data.
     */
    public function getRun(string $runId): array
    {
        try {
            $result = $this->reportRepository->findRun($runId);

            if ($result === null) {
                return [
                    'success' => false,
                    'data' => null,
                    'message' => __('reports.messages.not_found'),
                ];
            }

            return [
                'success' => true,
                'data' => $result,
                'message' => __('reports.messages.fetch_run_success'),
            ];
        } catch (Throwable $throwable) {
            Log::error('ReportService::getRun', [
                'runId' => $runId,
                'error' => $throwable->getMessage(),
            ]);

            return [
                'success' => false,
                'data' => null,
                'message' => __('reports.messages.fetch_run_error'),
            ];
        }
    }
}
