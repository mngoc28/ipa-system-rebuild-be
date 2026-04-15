<?php

declare(strict_types=1);

namespace App\Repositories\PipelineRepository;

use App\Models\PipelineProject;
use App\Repositories\BaseRepository;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

final class PipelineRepository extends BaseRepository implements PipelineRepositoryInterface
{
    public function getModel(): string
    {
        return PipelineProject::class;
    }

    public function getPaginated(Request $request): array
    {
        $page = max(1, (int) $request->input('page', 1));
        $perPage = max(1, min(100, (int) $request->input('per_page', $request->input('pageSize', 20))));
        $stageIdentifier = trim((string) $request->input('stage_id', ''));

        $query = DB::table('ipa_pipeline_project as project')
            ->join('ipa_md_pipeline_stage as stage', 'stage.id', '=', 'project.stage_id')
            ->leftJoin('ipa_delegation as delegation', 'delegation.id', '=', 'project.delegation_id')
            ->leftJoin('ipa_pipeline_stage_history as history', 'history.pipeline_project_id', '=', 'project.id')
            ->select([
                'project.id',
                'project.project_code',
                'project.project_name',
                'project.partner_id',
                'project.country_id',
                'project.sector_id',
                'project.delegation_id',
                'project.stage_id as stage_numeric_id',
                'project.estimated_value',
                'project.success_probability',
                'project.expected_close_date',
                'project.owner_user_id',
                'project.status',
                'project.updated_at',
                'stage.code as stage_code',
                'stage.name_vi as stage_name_vi',
                'delegation.name as delegation_name',
                DB::raw('MAX(history.changed_at) as changed_at'),
            ])
            ->groupBy([
                'project.id',
                'project.project_code',
                'project.project_name',
                'project.partner_id',
                'project.country_id',
                'project.sector_id',
                'project.delegation_id',
                'project.stage_id',
                'project.estimated_value',
                'project.success_probability',
                'project.expected_close_date',
                'project.owner_user_id',
                'project.status',
                'project.updated_at',
                'stage.code',
                'stage.name_vi',
                'delegation.name',
            ]);

        if ($stageIdentifier !== '') {
            $stageId = $this->resolveStageId($stageIdentifier);

            if ($stageId !== null) {
                $query->where('project.stage_id', $stageId);
            }
        }

        $total = DB::query()->fromSub(clone $query, 'pipeline_count')->count();

        $rows = $query
            ->orderByDesc('project.updated_at')
            ->orderByDesc('project.id')
            ->offset(($page - 1) * $perPage)
            ->limit($perPage)
            ->get();

        return [
            'items' => $rows->map(function (object $row): array {
                return $this->normalizeProject($row);
            })->all(),
            'meta' => [
                'page' => $page,
                'per_page' => $perPage,
                'total' => $total,
                'total_pages' => (int) ceil($total / $perPage),
            ],
        ];
    }

    public function summary(): array
    {
        $projectQuery = DB::table('ipa_pipeline_project as project')
            ->join('ipa_md_pipeline_stage as stage', 'stage.id', '=', 'project.stage_id')
            ->leftJoin('ipa_country as country', 'country.id', '=', 'project.country_id')
            ->leftJoin('ipa_md_sector as sector', 'sector.id', '=', 'project.sector_id')
            ->leftJoin('ipa_partner as partner', 'partner.id', '=', 'project.partner_id')
            ->leftJoin('ipa_delegation as delegation', 'delegation.id', '=', 'project.delegation_id')
            ->select([
                'project.id',
                'project.project_code',
                'project.project_name',
                'project.partner_id',
                'project.country_id',
                'project.sector_id',
                'project.delegation_id',
                'project.stage_id as stage_numeric_id',
                'project.estimated_value',
                'project.success_probability',
                'project.expected_close_date',
                'project.owner_user_id',
                'project.status',
                'project.updated_at',
                'stage.code as stage_code',
                'stage.name_vi as stage_name_vi',
                'country.name_vi as country_name',
                'sector.name_vi as sector_name',
                'partner.partner_name as partner_name',
                'delegation.name as delegation_name',
                'stage.stage_order as stage_order',
            ]);

        $projects = $projectQuery->get();

        $stageBreakdown = $projects
            ->groupBy('stage_code')
            ->map(static function ($items): array {
                $first = $items->first();

                return [
                    'stageId' => (string) $first->stage_numeric_id,
                    'stageCode' => (string) $first->stage_code,
                    'stageName' => (string) $first->stage_name_vi,
                    'stageOrder' => (int) ($first->stage_order ?? 0),
                    'projectCount' => $items->count(),
                    'totalValue' => (float) $items->sum(fn (object $item): float => (float) ($item->estimated_value ?? 0)),
                ];
            })
            ->values()
            ->sortBy('stageOrder')
            ->values()
            ->all();

        $countryBreakdown = $projects
            ->groupBy('country_name')
            ->map(static function ($items): array {
                $first = $items->first();

                return [
                    'countryName' => (string) ($first->country_name ?? 'Chưa xác định'),
                    'projectCount' => $items->count(),
                    'totalValue' => (float) $items->sum(fn (object $item): float => (float) ($item->estimated_value ?? 0)),
                ];
            })
            ->values()
            ->sortByDesc('totalValue')
            ->take(4)
            ->values()
            ->all();

        $sectorBreakdown = $projects
            ->groupBy('sector_name')
            ->map(static function ($items): array {
                $first = $items->first();

                return [
                    'sectorName' => (string) ($first->sector_name ?? 'Chưa xác định'),
                    'projectCount' => $items->count(),
                    'totalValue' => (float) $items->sum(fn (object $item): float => (float) ($item->estimated_value ?? 0)),
                ];
            })
            ->values()
            ->sortByDesc('projectCount')
            ->take(4)
            ->values()
            ->all();

        $recentProjects = $projects
            ->sortByDesc('updated_at')
            ->take(5)
            ->values()
            ->map(fn (object $row): array => $this->normalizeProject($row))
            ->all();

        $totalProjects = $projects->count();
        $activeProjects = $projects->filter(static fn (object $project): bool => (int) $project->stage_order < 5)->count();
        $closedWonProjects = $projects->filter(static fn (object $project): bool => (int) $project->stage_order === 5)->count();
        $totalValue = (float) $projects->sum(fn (object $project): float => (float) ($project->estimated_value ?? 0));
        $activeValue = (float) $projects->filter(static fn (object $project): bool => (int) $project->stage_order < 5)->sum(fn (object $project): float => (float) ($project->estimated_value ?? 0));
        $averageProbability = $totalProjects > 0
            ? round($projects->avg(fn (object $project): float => (float) ($project->success_probability ?? 0)), 1)
            : 0.0;

        return [
            'stats' => [
                'projects' => $totalProjects,
                'activeProjects' => $activeProjects,
                'closedWonProjects' => $closedWonProjects,
                'averageProbability' => $averageProbability,
            ],
            'value' => [
                'total' => $totalValue,
                'active' => $activeValue,
            ],
            'stageBreakdown' => $stageBreakdown,
            'countryBreakdown' => $countryBreakdown,
            'sectorBreakdown' => $sectorBreakdown,
            'recentProjects' => $recentProjects,
        ];
    }

    public function createProject(array $attributes, ?int $ownerUserId = null): ?array
    {
        return DB::transaction(function () use ($attributes, $ownerUserId): ?array {
            $stageId = $this->resolveStageId((string) Arr::get($attributes, 'stage_id'));

            if ($stageId === null) {
                return null;
            }

            $projectId = (int) DB::table('ipa_pipeline_project')->insertGetId([
                'project_code' => (string) Arr::get($attributes, 'project_code', $this->generateProjectCode()),
                'project_name' => (string) Arr::get($attributes, 'project_name'),
                'partner_id' => $this->resolveNullableForeignId('ipa_partner', Arr::get($attributes, 'partner_id')),
                'country_id' => $this->resolveForeignId('ipa_country', Arr::get($attributes, 'country_id')),
                'sector_id' => $this->resolveForeignId('ipa_md_sector', Arr::get($attributes, 'sector_id')),
                'delegation_id' => $this->resolveNullableForeignId('ipa_delegation', Arr::get($attributes, 'delegation_id')),
                'stage_id' => $stageId,
                'estimated_value' => Arr::get($attributes, 'estimated_value'),
                'success_probability' => Arr::get($attributes, 'success_probability'),
                'expected_close_date' => Arr::get($attributes, 'expected_close_date'),
                'owner_user_id' => $ownerUserId ?? $this->resolveForeignId('ipa_user', Arr::get($attributes, 'owner_user_id')),
                'status' => (int) Arr::get($attributes, 'status', 1),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return $this->findProject((string) $projectId);
        });
    }

    public function updateProject(string $projectId, array $attributes): ?array
    {
        return DB::transaction(function () use ($projectId, $attributes): ?array {
            $project = DB::table('ipa_pipeline_project')->where('id', $projectId)->first();

            if (! $project) {
                return null;
            }

            $updateData = [
                'project_name' => (string) Arr::get($attributes, 'project_name', $project->project_name),
                'partner_id' => $this->resolveNullableForeignId('ipa_partner', Arr::get($attributes, 'partner_id', $project->partner_id)),
                'country_id' => $this->resolveForeignId('ipa_country', Arr::get($attributes, 'country_id', $project->country_id)),
                'sector_id' => $this->resolveForeignId('ipa_md_sector', Arr::get($attributes, 'sector_id', $project->sector_id)),
                'delegation_id' => $this->resolveNullableForeignId('ipa_delegation', Arr::get($attributes, 'delegation_id', $project->delegation_id)),
                'estimated_value' => Arr::get($attributes, 'estimated_value', $project->estimated_value),
                'success_probability' => Arr::get($attributes, 'success_probability', $project->success_probability),
                'expected_close_date' => Arr::get($attributes, 'expected_close_date', $project->expected_close_date),
                'status' => (int) Arr::get($attributes, 'status', $project->status),
                'updated_at' => now(),
            ];

            // If stage_id is provided and different, use patchStage logic
            if (isset($attributes['stage_id'])) {
                $newStageId = $this->resolveStageId((string) $attributes['stage_id']);
                if ($newStageId !== null && $newStageId !== (int) $project->stage_id) {
                    $this->patchStage($projectId, (string) $newStageId);
                }
            }

            DB::table('ipa_pipeline_project')
                ->where('id', $projectId)
                ->update($updateData);

            return $this->findProject($projectId);
        });
    }

    public function deleteProject(string $projectId): bool
    {
        return DB::transaction(function () use ($projectId): bool {
            DB::table('ipa_pipeline_stage_history')->where('pipeline_project_id', $projectId)->delete();

            return (bool) DB::table('ipa_pipeline_project')->where('id', $projectId)->delete();
        });
    }

    public function patchStage(string $projectId, string $newStageIdentifier, ?string $reason = null, ?int $changedBy = null): ?array
    {
        return DB::transaction(function () use ($projectId, $newStageIdentifier, $reason, $changedBy): ?array {
            $project = DB::table('ipa_pipeline_project')->where('id', $projectId)->first();

            if (! $project) {
                return null;
            }

            $newStageId = $this->resolveStageId($newStageIdentifier);

            if ($newStageId === null) {
                return null;
            }

            $oldStageId = (int) $project->stage_id;

            DB::table('ipa_pipeline_stage_history')->insert([
                'pipeline_project_id' => (int) $projectId,
                'old_stage_id' => $oldStageId,
                'new_stage_id' => $newStageId,
                'changed_by' => $changedBy ?? $this->resolveForeignId('ipa_user', null),
                'changed_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('ipa_pipeline_project')
                ->where('id', $projectId)
                ->update([
                    'stage_id' => $newStageId,
                    'updated_at' => now(),
                ]);

            return $this->findProject($projectId);
        });
    }

    public function findProject(string $projectId): ?array
    {
        $row = DB::table('ipa_pipeline_project as project')
            ->join('ipa_md_pipeline_stage as stage', 'stage.id', '=', 'project.stage_id')
            ->leftJoin('ipa_delegation as delegation', 'delegation.id', '=', 'project.delegation_id')
            ->leftJoin('ipa_pipeline_stage_history as history', 'history.pipeline_project_id', '=', 'project.id')
            ->select([
                'project.id',
                'project.project_code',
                'project.project_name',
                'project.partner_id',
                'project.country_id',
                'project.sector_id',
                'project.delegation_id',
                'project.stage_id as stage_numeric_id',
                'project.estimated_value',
                'project.success_probability',
                'project.expected_close_date',
                'project.owner_user_id',
                'project.status',
                'project.updated_at',
                'stage.code as stage_code',
                'stage.name_vi as stage_name_vi',
                'delegation.name as delegation_name',
                DB::raw('MAX(history.changed_at) as changed_at'),
            ])
            ->groupBy([
                'project.id',
                'project.project_code',
                'project.project_name',
                'project.partner_id',
                'project.country_id',
                'project.sector_id',
                'project.delegation_id',
                'project.stage_id',
                'project.estimated_value',
                'project.success_probability',
                'project.expected_close_date',
                'project.owner_user_id',
                'project.status',
                'project.updated_at',
                'stage.code',
                'stage.name_vi',
                'delegation.name',
            ])
            ->where('project.id', $projectId)
            ->first();

        if (! $row) {
            return null;
        }

        return $this->normalizeProject($row);
    }

    private function normalizeProject(object $row): array
    {
        return [
            'id' => (string) $row->id,
            'project_code' => (string) $row->project_code,
            'project_name' => (string) $row->project_name,
            'partner_id' => $row->partner_id !== null ? (string) $row->partner_id : null,
            'country_id' => (string) $row->country_id,
            'sector_id' => (string) $row->sector_id,
            'delegation_id' => $row->delegation_id !== null ? (string) $row->delegation_id : null,
            'delegation_name' => $row->delegation_name !== null ? (string) $row->delegation_name : null,
            'stage_id' => (string) $row->stage_code,
            'stage_name' => (string) $row->stage_name_vi,
            'estimated_value' => $row->estimated_value !== null ? (float) $row->estimated_value : null,
            'success_probability' => $row->success_probability !== null ? (float) $row->success_probability : null,
            'expected_close_date' => $row->expected_close_date !== null ? (string) $row->expected_close_date : null,
            'owner_user_id' => (string) $row->owner_user_id,
            'status' => (int) $row->status,
            'changed_at' => $this->formatNullableDate($row->changed_at ?? $row->updated_at ?? null),
        ];
    }

    private function resolveStageId(string $identifier): ?int
    {
        $identifier = trim($identifier);

        if ($identifier === '') {
            return $this->fallbackStageId();
        }

        if (ctype_digit($identifier)) {
            $stage = DB::table('ipa_md_pipeline_stage')->where('id', (int) $identifier)->first();

            if ($stage) {
                return (int) $stage->id;
            }
        }

        $stage = DB::table('ipa_md_pipeline_stage')
            ->where('code', $identifier)
            ->first();

        if ($stage) {
            return (int) $stage->id;
        }

        return $this->fallbackStageId();
    }

    private function fallbackStageId(): ?int
    {
        $stageId = DB::table('ipa_md_pipeline_stage')->value('id');

        return $stageId !== null ? (int) $stageId : null;
    }

    private function resolveForeignId(string $table, mixed $value): int
    {
        if ($value !== null && $value !== '' && is_numeric($value)) {
            return (int) $value;
        }

        return (int) (DB::table($table)->value('id') ?? 1);
    }

    private function resolveNullableForeignId(string $table, mixed $value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_numeric($value)) {
            return (int) $value;
        }

        return (int) (DB::table($table)->value('id') ?? 1);
    }

    private function generateProjectCode(): string
    {
        return 'PIPE-' . Str::upper(Str::random(8));
    }

    private function formatNullableDate(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        return Carbon::parse((string) $value)->toIso8601String();
    }
}