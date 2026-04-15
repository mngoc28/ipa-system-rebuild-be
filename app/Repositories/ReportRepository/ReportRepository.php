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
    public function getModel(): string
    {
        return ReportRun::class;
    }

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

    public function summary(): array
    {
        $metricValues = $this->resolveMetricValues();

        $recentRuns = DB::table('ipa_report_run as run')
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
            ])
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

        $definitionCount = (int) DB::table('ipa_report_definition')->count();
        $runCount = (int) DB::table('ipa_report_run')->count();
        $successfulRunCount = (int) DB::table('ipa_report_run')->where('status', 1)->count();

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
                'headline' => sprintf('Dựa trên %.0f dự án mới và %.1f chỉ số PCI, báo cáo thành phố đang phản ánh đà tăng ổn định.', $newProjects, $pciIndex),
                'detail' => sprintf('Tổng vốn FDI đang theo dõi: %s và vốn đăng ký nội địa: %s.', $this->formatForecastCurrency($fdiTotal), $this->formatForecastCurrency($domesticCapital)),
            ],
        ];
    }

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

    private function resolveDefaultUserId(): int
    {
        return (int) (DB::table('ipa_user')->value('id') ?? 1);
    }

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

    private function statusToLabel(int $status): string
    {
        return match ($status) {
            1 => 'done',
            2 => 'running',
            3 => 'failed',
            default => 'queued',
        };
    }

    private function formatNullableDate(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        return Carbon::parse((string) $value)->toIso8601String();
    }

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

    private function formatForecastCurrency(float $value): string
    {
        return number_format($value, 0, ',', '.') . ' ₫';
    }
}