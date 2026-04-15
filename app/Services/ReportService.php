<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\ReportRepository\ReportRepositoryInterface;
use Illuminate\Support\Facades\Log;
use Throwable;

final class ReportService
{
    public function __construct(
        private readonly ReportRepositoryInterface $reportRepository,
    ) {
    }

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