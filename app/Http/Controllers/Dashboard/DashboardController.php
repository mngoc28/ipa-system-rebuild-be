<?php

declare(strict_types=1);

namespace App\Http\Controllers\Dashboard;

use App\Enums\HttpStatus;
use App\Http\Controllers\Controller;
use App\Services\TaskService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Class DashboardController
 *
 * Provides aggregated statistics and summary data for various system modules,
 * tailored to the user's role and scope (Staff, Manager, Director, Admin).
 *
 * @package App\Http\Controllers\Dashboard
 */
final class DashboardController extends Controller
{
    /**
     * DashboardController constructor.
     *
     * @param TaskService $taskService
     */
    public function __construct(
        private TaskService $taskService,
    ) {
    }

    /**
     * Retrieve a summary of dashboard metrics based on the specified scope.
     *
     * @param Request $request Requires 'scope' parameter (staff|manager|director|admin).
     * @return JsonResponse
     */
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

    /**
     * Retrieve tasks relevant to the dashboard view.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function tasks(Request $request): JsonResponse
    {
        $result = $this->taskService->getAll($request);

        if (! $result['success']) {
            return $this->errorResponse($result['message'], 'FETCH_FAILED', HttpStatus::BAD_REQUEST);
        }

        return $this->successResponse($result['data'], $result['message'], HttpStatus::OK, $result['data']['meta'] ?? null);
    }

    /**
     * Build the summary data response based on the user's role and scope.
     * Aggregates delegation counts, task statuses, event schedules, and pipeline values.
     *
     * @param string $scope The requested data scope.
     * @return array The compiled summary data bundle.
     */
    private function buildSummary(string $scope): array
    {
        $user = auth()->user();
        if (!$user) {
            return [];
        }

        $isAdminOrDirector = $user->hasRole(['ADMIN', 'DIRECTOR']);
        $isManager = $user->hasRole('MANAGER') && !$isAdminOrDirector;
        $isStaff = $user->hasRole('STAFF') && !$isAdminOrDirector && !$isManager;

        // --- DELEGATION QUERY ---
        $delegationBaseQuery = DB::table('ipa_delegation');
        if ($isStaff) {
            $delegationBaseQuery->where('owner_user_id', $user->id);
        } elseif ($isManager) {
            $delegationBaseQuery->where('host_unit_id', $user->primary_unit_id);
        }

        // --- TASK QUERY ---
        $taskBaseQuery = DB::table('ipa_task as task');
        if ($isStaff) {
            $taskBaseQuery->where(function ($q) use ($user) {
                $q->where('task.created_by', $user->id)
                  ->orWhereExists(function ($sub) use ($user) {
                      $sub->select(DB::raw(1))
                          ->from('ipa_task_assignee')
                          ->whereColumn('task_id', 'task.id')
                          ->where('user_id', $user->id);
                  });
            });
        } elseif ($isManager) {
            $taskBaseQuery->leftJoin('ipa_user as u_task', 'u_task.id', '=', 'task.created_by')
                ->where(function ($q) use ($user) {
                    $q->where('u_task.primary_unit_id', $user->primary_unit_id)
                      ->orWhereExists(function ($sub) use ($user) {
                          $sub->select(DB::raw(1))
                              ->from('ipa_task_assignee as ta')
                              ->join('ipa_user as ua', 'ua.id', '=', 'ta.user_id')
                              ->whereColumn('ta.task_id', 'task.id')
                              ->where('ua.primary_unit_id', $user->primary_unit_id);
                      });
                });
        }

        // --- EVENT QUERY ---
        $eventBaseQuery = DB::table('ipa_event as event');
        if ($isStaff) {
            $eventBaseQuery->where(function ($q) use ($user) {
                $q->where('event.organizer_user_id', $user->id)
                  ->orWhere('event.staff_id', $user->id);
            });
        } elseif ($isManager) {
            $eventBaseQuery->leftJoin('ipa_user as u_event', 'u_event.id', '=', 'event.organizer_user_id')
                ->where('u_event.primary_unit_id', $user->primary_unit_id);
        }

        // --- AGGREGATE COUNTS & VALUES ---
        $delegationStats = (clone $delegationBaseQuery)
            ->select([
                DB::raw('COUNT(*) as total_count'),
                DB::raw('SUM(CASE WHEN status NOT IN (3, 4) THEN 1 ELSE 0 END) as active_count')
            ])
            ->first();

        $taskCount = (int) (clone $taskBaseQuery)->count();

        $eventStats = (clone $eventBaseQuery)
            ->select([
                DB::raw('COUNT(*) as total_count'),
                DB::raw('SUM(CASE WHEN event.start_at >= \'' . now()->toDateTimeString() . '\' THEN 1 ELSE 0 END) as upcoming_count')
            ])
            ->first();

        $pipelineStats = DB::table('ipa_pipeline_project as project')
            ->leftJoin('ipa_md_pipeline_stage as stage', 'stage.id', '=', 'project.stage_id')
            ->select([
                DB::raw('COUNT(project.id) as total_count'),
                DB::raw('SUM(project.estimated_value) as total_value'),
                DB::raw('SUM(CASE WHEN stage.code NOT IN (\'CLOSED_WON\', \'CLOSED_LOST\') THEN project.estimated_value ELSE 0 END) as active_value')
            ])
            ->first();

        $partnerCount = (int) DB::table('ipa_partner')->whereNull('deleted_at')->count();

        $delegationCount = (int) ($delegationStats->total_count ?? 0);
        $activeDelegationCount = (int) ($delegationStats->active_count ?? 0);
        $eventCount = (int) ($eventStats->total_count ?? 0);
        $upcomingEventCount = (int) ($eventStats->upcoming_count ?? 0);
        $pipelineCount = (int) ($pipelineStats->total_count ?? 0);
        $totalPipelineValue = (float) ($pipelineStats->total_value ?? 0);
        $activePipelineValue = (float) ($pipelineStats->active_value ?? 0);

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

        $upcomingEvents = (clone $eventBaseQuery)
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

        $overdueTasks = (clone $taskBaseQuery)
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

        return [
            'stats' => [
                'delegations' => $delegationCount,
                'tasks' => $taskCount,
                'events' => $eventCount,
            ],
            'city' => $isAdminOrDirector ? $cityData : null,
            'unit' => $isManager ? $cityData : null, // Reuse same structure for now
            'alerts' => $overdueTasks,
            'overdueTasks' => $overdueTasks,
        ];
    }
}
