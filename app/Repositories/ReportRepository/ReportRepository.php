<?php

declare(strict_types=1);

namespace App\Repositories\ReportRepository;

use App\Models\ReportRun;
use App\Repositories\BaseRepository;
use Carbon\Carbon;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

final class ReportRepository extends BaseRepository implements ReportRepositoryInterface
{
    /**
     * Get the model class name for the repository.
     *
     * @return string
     */
    public function getModel(): string
    {
        return ReportRun::class;
    }

    /**
     * Get a list of all report definitions including their codes, names, and query configurations.
     *
     * @return array
     */
    public function listDefinitions(): array
    {
        $rows = DB::table('ipa_report_definition as definition')
            ->select([
                'definition.id',
                'definition.report_code',
                'definition.report_name',
                'definition.scope_type',
                'definition.owner_role_id',
                'definition.query_config',
            ])
            ->orderBy('definition.id')
            ->get();

        return [
            'items' => $rows->map(static function (object $row): array {
                return [
                    'id' => (string) $row->id,
                    'report_code' => (string) $row->report_code,
                    'report_name' => (string) $row->report_name,
                    'scope_type' => (int) $row->scope_type,
                    'owner_role_id' => $row->owner_role_id !== null ? (string) $row->owner_role_id : null,
                    'query_config' => $row->query_config !== null ? json_decode((string) $row->query_config, true) : null,
                ];
            })->all(),
            'meta' => [
                'total' => $rows->count(),
            ],
        ];
    }

    /**
     * Generate a comprehensive summary of report generation activities, KPIs, and forecasts.
     * Includes data scoping to ensure users only see runs related to their role and unit.
     *
     * @return array
     */
    public function summary(): array
    {
        $metricValues = $this->resolveMetricValues();
        $user = auth()->user();
        $isStaffOnly = $user && $user->hasRole('STAFF') && !$user->hasRole(['ADMIN', 'DIRECTOR', 'MANAGER']);

        $recentRunsQuery = DB::table('ipa_report_run as run')
            ->join('ipa_report_definition as definition', 'definition.id', '=', 'run.report_definition_id')
            ->leftJoin('ipa_file as file', 'file.id', '=', 'run.output_file_id')
            ->select([
                'run.id',
                'run.status',
                'run.started_at',
                'run.finished_at',
                'run.output_file_id',
                'definition.report_code',
                'definition.report_name',
                'file.file_name as output_file_name',
                'file.size_bytes as output_file_size_bytes',
            ]);

        if ($user) {
            $isStaff = $user->hasRole('STAFF') && !$user->hasRole(['ADMIN', 'DIRECTOR', 'MANAGER']);
            $isManager = $user->hasRole('MANAGER') && !$user->hasRole(['ADMIN', 'DIRECTOR']);

            if ($isStaff) {
                $recentRunsQuery->where('run.run_by', $user->id);
            } elseif ($isManager) {
                $recentRunsQuery->whereExists(function ($sub) use ($user) {
                    $sub->select(DB::raw(1))
                        ->from('ipa_user as u')
                        ->whereColumn('u.id', 'run.run_by')
                        ->where('u.primary_unit_id', $user->primary_unit_id);
                });
            }
        }

        $recentRuns = $recentRunsQuery
            ->orderByDesc('run.started_at')
            ->orderByDesc('run.id')
            ->limit(20)
            ->get()
            ->map(static function (object $row): array {
                return [
                    'runId' => (string) $row->id,
                    'reportCode' => (string) $row->report_code,
                    'reportName' => (string) $row->report_name,
                    'status' => (int) $row->status,
                    'startedAt' => $row->started_at !== null ? Carbon::parse((string) $row->started_at)->toIso8601String() : null,
                    'finishedAt' => $row->finished_at !== null ? Carbon::parse((string) $row->finished_at)->toIso8601String() : null,
                    'outputFileId' => $row->output_file_id !== null ? (string) $row->output_file_id : null,
                    'outputFileName' => $row->output_file_name !== null ? (string) $row->output_file_name : null,
                    'outputFileSizeBytes' => $row->output_file_size_bytes !== null ? (int) $row->output_file_size_bytes : null,
                ];
            })
            ->all();

        $runCountQuery = DB::table('ipa_report_run as run');
        if ($user) {
            if ($isStaff) {
                $runCountQuery->where('run.run_by', $user->id);
            } elseif ($isManager) {
                $runCountQuery->whereExists(function ($sub) use ($user) {
                    $sub->select(DB::raw(1))
                        ->from('ipa_user as u')
                        ->whereColumn('u.id', 'run.run_by')
                        ->where('u.primary_unit_id', $user->primary_unit_id);
                });
            }
        }

        $definitionCount = (int) DB::table('ipa_report_definition')->count();
        $runCount = (int) (clone $runCountQuery)->count();
        $successfulRunCount = (int) (clone $runCountQuery)->where('status', 1)->count();

        $newProjects = $metricValues['CITY_NEW_PROJECTS_Q1_2026'] ?? 0.0;
        $fdiTotal = $metricValues['CITY_FDI_TOTAL_2026'] ?? 0.0;
        $domesticCapital = $metricValues['CITY_DOMESTIC_CAPITAL_2026'] ?? 0.0;
        $pciIndex = $metricValues['CITY_PCI_SATISFACTION_2026'] ?? 0.0;

        return [
            'stats' => [
                'definitions' => $definitionCount,
                'runs' => $runCount,
                'successfulRuns' => $successfulRunCount,
                'metrics' => count($metricValues),
            ],
            'kpis' => [
                'newProjects' => $newProjects,
                'fdiTotal' => $fdiTotal,
                'domesticCapital' => $domesticCapital,
                'pciIndex' => $pciIndex,
            ],
            'recentRuns' => $recentRuns,
            'forecast' => [
                'title' => 'Dự báo Tăng trưởng 2026',
                'headline' => sprintf(
                    'Dựa trên %.0f dự án mới và %.1f chỉ số PCI, báo cáo thành phố đang phản ánh đà tăng ổn định.',
                    $newProjects,
                    $pciIndex
                ),
                'detail' => sprintf(
                    'Tổng vốn FDI đang theo dõi: %s và vốn đăng ký nội địa: %s.',
                    $this->formatForecastCurrency($fdiTotal),
                    $this->formatForecastCurrency($domesticCapital)
                ),
            ],
        ];
    }

    /**
     * Create a record for a new report run, identifying the definition and assigning a default user if none provided.
     *
     * @param array $attributes
     * @param int|null $runBy
     * @return array|null
     */
    public function createRun(array $attributes, ?int $runBy = null): ?array
    {
        return DB::transaction(function () use ($attributes, $runBy): ?array {
            $definition = $this->resolveDefinition((string) Arr::get($attributes, 'report_code'));

            if ($definition === null) {
                return null;
            }

            $resolvedRunBy = $runBy ?? $this->resolveDefaultUserId();

            $runId = (int) DB::table('ipa_report_run')->insertGetId([
                'report_definition_id' => (int) $definition->id,
                'run_by' => $resolvedRunBy,
                'params_json' => json_encode(Arr::get($attributes, 'params', [])),
                'output_file_id' => null,
                'status' => 1,
                'started_at' => now(),
                'finished_at' => now(),
                'error_message' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return $this->findRun((string) $runId);
        });
    }

    /**
     * Find a specific report run by ID and return its normalized details.
     *
     * @param string $runId
     * @return array|null
     */
    public function findRun(string $runId): ?array
    {
        $row = DB::table('ipa_report_run as run')
            ->join('ipa_report_definition as definition', 'definition.id', '=', 'run.report_definition_id')
            ->select([
                'run.id',
                'run.report_definition_id',
                'run.run_by',
                'run.params_json',
                'run.output_file_id',
                'run.status',
                'run.started_at',
                'run.finished_at',
                'run.error_message',
                'definition.report_code',
                'definition.report_name',
                'definition.scope_type',
            ])
            ->where('run.id', $runId)
            ->first();

        if (! $row) {
            return null;
        }

        return $this->normalizeRun($row);
    }

    /**
     * Resolve a report definition from a code or numeric string ID.
     *
     * @param string $reportCode
     * @return mixed
     */
    private function resolveDefinition(string $reportCode): mixed
    {
        if ($reportCode === '') {
            return null;
        }

        $definition = DB::table('ipa_report_definition')
            ->where('report_code', $reportCode)
            ->first();

        if ($definition) {
            return $definition;
        }

        if (ctype_digit($reportCode)) {
            return DB::table('ipa_report_definition')->where('id', (int) $reportCode)->first();
        }

        return null;
    }

    /**
     * provide a fallback user ID for report generation if none is explicitly specified.
     *
     * @return int
     */
    private function resolveDefaultUserId(): int
    {
        return (int) (DB::table('ipa_user')->value('id') ?? 1);
    }

    /**
     * Transform a report run row into a standardized response array.
     *
     * @param object $row
     * @return array
     */
    private function normalizeRun(object $row): array
    {
        return [
            'run_id' => (string) $row->id,
            'report_code' => (string) $row->report_code,
            'report_name' => (string) $row->report_name,
            'scope_type' => (int) $row->scope_type,
            'status' => $this->statusToLabel((int) $row->status),
            'started_at' => $this->formatNullableDate($row->started_at ?? null),
            'finished_at' => $this->formatNullableDate($row->finished_at ?? null),
            'output_file_id' => $row->output_file_id !== null ? (string) $row->output_file_id : null,
            'error_message' => $row->error_message !== null ? (string) $row->error_message : null,
        ];
    }

    /**
     * Map a numeric status integer to a human-readable semantic label.
     *
     * @param int $status
     * @return string
     */
    private function statusToLabel(int $status): string
    {
        return match ($status) {
            1 => 'done',
            2 => 'running',
            3 => 'failed',
            default => 'queued',
        };
    }

    /**
     * Standardize a nullable date value into an ISO8601 string.
     *
     * @param mixed $value
     * @return string|null
     */
    private function formatNullableDate(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        return Carbon::parse((string) $value)->toIso8601String();
    }

    /**
     * Fetch the latest numeric values for predefined KPI metrics from snapshots.
     *
     * @return array
     */
    private function resolveMetricValues(): array
    {
        $codes = [
            'CITY_NEW_PROJECTS_Q1_2026',
            'CITY_FDI_TOTAL_2026',
            'CITY_DOMESTIC_CAPITAL_2026',
            'CITY_PCI_SATISFACTION_2026',
        ];

        $values = [];

        foreach ($codes as $code) {
            $row = DB::table('ipa_kpi_metric as metric')
                ->leftJoin('ipa_kpi_snapshot as snapshot', 'snapshot.metric_id', '=', 'metric.id')
                ->select([
                    'metric.id',
                    'metric.metric_code',
                    'snapshot.value_numeric',
                    'snapshot.snapshot_date',
                ])
                ->where('metric.metric_code', $code)
                ->orderByDesc('snapshot.snapshot_date')
                ->orderByDesc('snapshot.id')
                ->first();

            if ($row && $row->value_numeric !== null) {
                $values[$code] = (float) $row->value_numeric;
            }
        }

        return $values;
    }

    /**
     * Format a numeric value as a currency string for forecast headlines.
     *
     * @param float $value
     * @return string
     */
    private function formatForecastCurrency(float $value): string
    {
        return number_format($value, 0, ',', '.') . ' ₫';
    }
}
