<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\HttpStatus;
use App\Services\TaskService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

final class DashboardController extends Controller
{
    public function __construct(
        private readonly TaskService $taskService,
    ) {
    }

    public function summary(Request $request): JsonResponse
    {
        $scope = strtolower(trim((string) $request->input('scope', 'director')));

        if (! in_array($scope, ['staff', 'manager', 'director', 'admin'], true)) {
            return $this->errorResponse(__('auth.failed'), 'INVALID_SCOPE', HttpStatus::BAD_REQUEST);
        }

        return $this->successResponse(
            $this->buildSummary($scope),
            'Dashboard summary fetched successfully',
            HttpStatus::OK
        );
    }

    public function tasks(Request $request): JsonResponse
    {
        $result = $this->taskService->getAll($request);

        if (! $result['success']) {
            return $this->errorResponse($result['message'], 'FETCH_FAILED', HttpStatus::BAD_REQUEST);
        }

        return $this->successResponse($result['data'], $result['message'], HttpStatus::OK, $result['data']['meta'] ?? null);
    }

    private function buildSummary(string $scope): array
    {
        $delegationCount = (int) DB::table('ipa_delegation')->count();
        $taskCount = (int) DB::table('ipa_task')->count();
        $eventCount = (int) DB::table('ipa_event')->count();
        $partnerCount = (int) DB::table('ipa_partner')->whereNull('deleted_at')->count();
        $pipelineCount = (int) DB::table('ipa_pipeline_project')->count();
        $activeDelegationCount = (int) DB::table('ipa_delegation')
            ->whereNotIn('status', [3, 4])
            ->count();
        $upcomingEventCount = (int) DB::table('ipa_event')
            ->where('start_at', '>=', now())
            ->count();
        $totalPipelineValue = (float) DB::table('ipa_pipeline_project')->sum('estimated_value');
        $activePipelineValue = (float) DB::table('ipa_pipeline_project as project')
            ->join('ipa_md_pipeline_stage as stage', 'stage.id', '=', 'project.stage_id')
            ->whereNotIn('stage.code', ['CLOSED_WON', 'CLOSED_LOST'])
            ->sum('project.estimated_value');

        $stageBreakdown = DB::table('ipa_pipeline_project as project')
            ->join('ipa_md_pipeline_stage as stage', 'stage.id', '=', 'project.stage_id')
            ->select([
                'stage.id as stage_id',
                'stage.code as stage_code',
                'stage.name_vi as stage_name',
                'stage.stage_order',
                DB::raw('COUNT(project.id) as project_count'),
                DB::raw('COALESCE(SUM(project.estimated_value), 0) as total_value'),
            ])
            ->groupBy('stage.id', 'stage.code', 'stage.name_vi', 'stage.stage_order')
            ->orderBy('stage.stage_order')
            ->get()
            ->map(static function (object $row): array {
                return [
                    'stageId' => (string) $row->stage_id,
                    'stageCode' => (string) $row->stage_code,
                    'stageName' => (string) $row->stage_name,
                    'stageOrder' => (int) $row->stage_order,
                    'projectCount' => (int) $row->project_count,
                    'totalValue' => (float) $row->total_value,
                ];
            })
            ->all();

        $recentProjects = DB::table('ipa_pipeline_project as project')
            ->join('ipa_md_pipeline_stage as stage', 'stage.id', '=', 'project.stage_id')
            ->leftJoin('ipa_partner as partner', 'partner.id', '=', 'project.partner_id')
            ->leftJoin('ipa_delegation as delegation', 'delegation.id', '=', 'project.delegation_id')
            ->select([
                'project.id',
                'project.project_code',
                'project.project_name',
                'project.estimated_value',
                'project.success_probability',
                'project.expected_close_date',
                'project.updated_at',
                'stage.code as stage_code',
                'stage.name_vi as stage_name',
                'partner.partner_name',
                'delegation.name as delegation_name',
            ])
            ->orderByDesc('project.updated_at')
            ->orderByDesc('project.id')
            ->limit(5)
            ->get()
            ->map(static function (object $row): array {
                return [
                    'id' => (string) $row->id,
                    'projectCode' => (string) $row->project_code,
                    'projectName' => (string) $row->project_name,
                    'partnerName' => $row->partner_name !== null ? (string) $row->partner_name : null,
                    'delegationName' => $row->delegation_name !== null ? (string) $row->delegation_name : null,
                    'stageCode' => (string) $row->stage_code,
                    'stageName' => (string) $row->stage_name,
                    'estimatedValue' => $row->estimated_value !== null ? (float) $row->estimated_value : null,
                    'successProbability' => $row->success_probability !== null ? (float) $row->success_probability : null,
                    'expectedCloseDate' => $row->expected_close_date !== null ? (string) $row->expected_close_date : null,
                    'updatedAt' => $row->updated_at !== null ? (string) $row->updated_at : null,
                ];
            })
            ->all();

        $upcomingEvents = DB::table('ipa_event as event')
            ->leftJoin('ipa_delegation as delegation', 'delegation.id', '=', 'event.delegation_id')
            ->leftJoin('ipa_location as location', 'location.id', '=', 'event.location_id')
            ->select([
                'event.id',
                'event.title',
                'event.start_at',
                'event.end_at',
                'event.status',
                'delegation.name as delegation_name',
                'location.name as location_name',
            ])
            ->where('event.start_at', '>=', now())
            ->orderBy('event.start_at')
            ->limit(5)
            ->get()
            ->map(static function (object $row): array {
                return [
                    'id' => (string) $row->id,
                    'title' => (string) $row->title,
                    'startAt' => $row->start_at !== null ? (string) $row->start_at : null,
                    'endAt' => $row->end_at !== null ? (string) $row->end_at : null,
                    'status' => (int) $row->status,
                    'delegationName' => $row->delegation_name !== null ? (string) $row->delegation_name : null,
                    'locationName' => $row->location_name !== null ? (string) $row->location_name : null,
                ];
            })
            ->all();

        $topPartners = DB::table('ipa_partner as partner')
            ->leftJoin('ipa_pipeline_project as project', 'project.partner_id', '=', 'partner.id')
            ->whereNull('partner.deleted_at')
            ->select([
                'partner.id',
                'partner.partner_name',
                'partner.score',
                DB::raw('COUNT(project.id) as project_count'),
            ])
            ->groupBy('partner.id', 'partner.partner_name', 'partner.score')
            ->orderByDesc(DB::raw('partner.score'))
            ->orderByDesc(DB::raw('COUNT(project.id)'))
            ->limit(5)
            ->get()
            ->map(static function (object $row): array {
                return [
                    'id' => (string) $row->id,
                    'partnerName' => (string) $row->partner_name,
                    'score' => $row->score !== null ? (float) $row->score : null,
                    'projectCount' => (int) $row->project_count,
                ];
            })
            ->all();

        $overdueTasks = DB::table('ipa_task as task')
            ->select([
                'task.id',
                'task.title',
                'task.due_at',
                'task.priority',
                'task.status',
            ])
            ->whereNotNull('task.due_at')
            ->where('task.due_at', '<', now())
            ->where('task.status', '!=', 2)
            ->orderBy('task.due_at')
            ->limit(5)
            ->get()
            ->map(static function (object $row): array {
                return [
                    'id' => (string) $row->id,
                    'title' => (string) $row->title,
                    'dueAt' => $row->due_at !== null ? (string) $row->due_at : null,
                    'priority' => (int) $row->priority,
                    'status' => (int) $row->status,
                ];
            })
            ->all();

        $cityData = [
            'partners' => $partnerCount,
            'pipelineProjects' => $pipelineCount,
            'activeDelegations' => $activeDelegationCount,
            'upcomingEvents' => $upcomingEventCount,
            'totalPipelineValue' => $totalPipelineValue,
            'activePipelineValue' => $activePipelineValue,
            'stageBreakdown' => $stageBreakdown,
            'recentProjects' => $recentProjects,
            'upcomingEventsList' => $upcomingEvents,
            'topPartners' => $topPartners,
        ];

        if ($scope !== 'director' && $scope !== 'admin') {
            return [
                'stats' => [
                    'delegations' => $delegationCount,
                    'tasks' => $taskCount,
                    'events' => $eventCount,
                ],
                'alerts' => $overdueTasks,
                'overdueTasks' => $overdueTasks,
            ];
        }

        return [
            'stats' => [
                'delegations' => $delegationCount,
                'tasks' => $taskCount,
                'events' => $eventCount,
            ],
            'city' => $cityData,
            'alerts' => $overdueTasks,
            'overdueTasks' => $overdueTasks,
        ];
    }
}