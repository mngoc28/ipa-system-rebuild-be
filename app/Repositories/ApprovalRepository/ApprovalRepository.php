<?php

declare(strict_types=1);

namespace App\Repositories\ApprovalRepository;

use App\Models\ApprovalHistory;
use App\Models\ApprovalRequest;
use App\Models\ApprovalStep;
use App\Models\AdminUser;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

final class ApprovalRepository implements ApprovalRepositoryInterface
{
    /**
     * ApprovalRepository constructor.
     *
     * @param ApprovalRequest $model
     */
    public function __construct(private ApprovalRequest $model)
    {
    }

    /**
     * Get a paginated list of approval requests with filtering by status, type, and keyword.
     *
     * @param Request $request
     * @return LengthAwarePaginator
     */
    public function getPaginated(Request $request): LengthAwarePaginator
    {
        $query = $this->model->newQuery()->with([
            'requester:id,full_name,username',
            'steps' => static fn ($builder) => $builder->orderBy('step_order'),
            'history' => static fn ($builder) => $builder->orderByDesc('changed_at'),
        ]);

        if ($request->filled('status')) {
            $status = strtoupper(trim($request->string('status')->toString()));

            if ($status === 'PENDING' || $status === '0') {
                $query->whereNotIn('status', [1, 2]);
            } elseif ($status === 'APPROVED' || $status === '1') {
                $query->where('status', 1);
            } elseif ($status === 'REJECTED' || $status === '2') {
                $query->where('status', 2);
            }
        }

        if ($request->filled('type')) {
            $query->where('request_type', 'like', '%' . $request->string('type')->toString() . '%');
        }

        if ($request->filled('keyword')) {
            $keyword = $request->string('keyword')->toString();
            $query->where(function ($builder) use ($keyword): void {
                $builder->where('request_type', 'like', '%' . $keyword . '%')
                    ->orWhere('ref_table', 'like', '%' . $keyword . '%');

                if (ctype_digit($keyword)) {
                    $builder->orWhere('ref_id', (int) $keyword);
                }
            });
        }

        // --- Role-based Scoping ---
        /** @var AdminUser|null $user */
        $user = auth()->user();
        if ($user) {
            $isStaff   = $user->hasRole('STAFF') && !$user->hasRole(['ADMIN', 'DIRECTOR', 'MANAGER']);
            $isManager = $user->hasRole('MANAGER') && !$user->hasRole(['ADMIN', 'DIRECTOR']);
            if ($isManager && $request->filled('status')) {
                $status = strtoupper(trim($request->string('status')->toString()));
                if ($status === 'PENDING' || $status === '0') {
                    $this->syncPendingDelegationApprovalsForManager((int) $user->primary_unit_id);
                }
            }

            if ($isStaff) {
                $query->where('requester_user_id', $user->id);
            } elseif ($isManager) {
                $query->where(function ($q) use ($user) {
                    $q->where('requester_user_id', $user->id)
                      ->orWhereExists(function ($sub) use ($user) {
                          $sub->select(DB::raw(1))
                              ->from('ipa_approval_step as step')
                              ->whereColumn('step.approval_request_id', 'ipa_approval_request.id')
                              ->where('step.approver_user_id', $user->id)
                              ->whereColumn('step.step_order', 'ipa_approval_request.current_step');
                      });
                });
            }
        }

        $paginator = $query->orderByDesc('created_at')->paginate(
            (int) $request->integer('pageSize', 10),
            ['*'],
            'page',
            (int) $request->integer('page', 1)
        );

        $paginator->setCollection(
            $paginator->getCollection()->map(fn (ApprovalRequest $approvalRequest): array => $this->transformListItem($approvalRequest))
        );

        return $paginator;
    }

    /**
     * Get detailed information for a specific approval request, including steps and history.
     *
     * @param int $id
     * @return array|null
     */
    public function getById(int $id): ?array
    {
        $approvalRequest = $this->model->newQuery()
            ->with([
                'requester:id,full_name,username',
                'steps' => static fn ($builder) => $builder->orderBy('step_order'),
                'history' => static fn ($builder) => $builder->orderByDesc('changed_at'),
            ])
            ->find($id);

        if ($approvalRequest === null) {
            return null;
        }

        return [
            'request' => $this->transformDetailRequest($approvalRequest),
            'steps' => $this->transformSteps($approvalRequest->steps),
            'history' => $this->transformHistory($approvalRequest->history),
        ];
    }
    /**
     * Backfill pending delegation approvals for a manager's unit.
     *
     * This keeps existing delegation submissions visible in the shared approval queue
     * even if they were created before the workflow record existed.
     *
     * @param int $unitId
     * @return void
     */
    private function syncPendingDelegationApprovalsForManager(int $unitId): void
    {
        if ($unitId <= 0) {
            return;
        }

        DB::transaction(function () use ($unitId): void {
            $pendingDelegations = DB::table('ipa_delegation as delegation')
                ->join('ipa_user as owner', 'owner.id', '=', 'delegation.owner_user_id')
                ->leftJoin('ipa_approval_request as request', function ($join): void {
                    $join->on('request.ref_id', '=', 'delegation.id')
                        ->where('request.ref_table', '=', 'ipa_delegation')
                        ->where('request.status', '=', 0);
                })
                ->whereNull('delegation.deleted_at')
                ->where('delegation.status', 1)
                ->where('owner.primary_unit_id', $unitId)
                ->whereNull('request.id')
                ->select([
                    'delegation.id',
                    'delegation.priority',
                    'delegation.owner_user_id',
                ])
                ->get();

            if ($pendingDelegations->isEmpty()) {
                return;
            }

            $managerIds = DB::table('ipa_user as user')
                ->join('ipa_user_role as user_role', 'user_role.user_id', '=', 'user.id')
                ->join('ipa_role as role', 'role.id', '=', 'user_role.role_id')
                ->where('user.primary_unit_id', $unitId)
                ->where('role.code', 'MANAGER')
                ->where('user.status', 1)
                ->pluck('user.id')
                ->map(static fn ($id): int => (int) $id)
                ->all();

            if ($managerIds === []) {
                return;
            }

            $now = now();

            foreach ($pendingDelegations as $delegation) {
                $requestId = (int) DB::table('ipa_approval_request')->insertGetId([
                    'request_type' => 'DELEGATION_APPROVAL',
                    'ref_table' => 'ipa_delegation',
                    'ref_id' => (int) $delegation->id,
                    'requester_user_id' => (int) $delegation->owner_user_id,
                    'current_step' => 1,
                    'priority' => (int) $delegation->priority,
                    'due_at' => $now->copy()->addDays(3),
                    'status' => 0,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);

                $stepRows = array_map(static function (int $managerId) use ($requestId, $now): array {
                    return [
                        'approval_request_id' => $requestId,
                        'approver_user_id' => $managerId,
                        'step_order' => 1,
                        'decision' => 0,
                        'decision_note' => null,
                        'decided_at' => null,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }, $managerIds);

                DB::table('ipa_approval_step')->insert($stepRows);
            }
        });
    }

    /**
     * Process a decision for an approval request within a transaction.
     * Updates the status, logs history, and triggers notifications.
     *
     * @param int $id
     * @param array $data
     * @param int $userId
     * @return array|null
     */
    public function decide(int $id, array $data, int $userId): ?array
    {
        return DB::transaction(function () use ($id, $data, $userId): ?array {
            /** @var ApprovalRequest|null $approvalRequest */
            $approvalRequest = $this->model->newQuery()
                ->with(['steps' => static fn ($builder) => $builder->orderBy('step_order')])
                ->lockForUpdate()
                ->find($id);

            if ($approvalRequest === null) {
                return null;
            }

            $currentStatus = (int) $approvalRequest->status;
            if (in_array($currentStatus, [1, 2], true)) {
                return $this->getById($approvalRequest->id);
            }

            /** @var Collection<int, ApprovalStep> $steps */
            $steps = $approvalRequest->steps;
            $currentStepOrder = max(1, (int) $approvalRequest->current_step);
            $currentStep = $steps->firstWhere('step_order', $currentStepOrder) ?? $steps->first();

            if ($currentStep === null) {
                $currentStep = ApprovalStep::query()->create([
                    'approval_request_id' => $approvalRequest->id,
                    'approver_user_id' => $userId,
                    'step_order' => $currentStepOrder,
                    'decision' => 0,
                    'decision_note' => null,
                    'decided_at' => null,
                ]);

                $steps = collect([$currentStep]);
            }

            if ($currentStep === null) {
                return null;
            }

            $decision = strtoupper((string) $data['decision']);
            $decisionNote = $data['decisionNote'] ?? null;
            $newStatus = $decision === 'REJECT' ? 2 : 1;

            $currentStep->decision = $newStatus;
            $currentStep->decision_note = $decisionNote;
            $currentStep->decided_at = now();
            $currentStep->save();

            $oldStatus = (int) $approvalRequest->status;
            $approvalRequest->status = $newStatus;

            if ($newStatus === 1 && $steps->count() > $currentStepOrder) {
                $approvalRequest->current_step = $currentStepOrder + 1;
            }

            $approvalRequest->save();

            ApprovalHistory::query()->create([
                'approval_request_id' => $approvalRequest->id,
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
                'changed_by' => $userId,
                'changed_at' => now(),
            ]);

            if ($approvalRequest->ref_table === 'ipa_delegation') {
                $delegationUpdate = [
                    'status' => $newStatus === 1 ? 3 : 6,
                    'updated_at' => now(),
                ];

                if ($newStatus === 2) {
                    $delegationUpdate['approval_remark'] = $decisionNote;
                }

                DB::table('ipa_delegation')
                    ->where('id', $approvalRequest->ref_id)
                    ->update($delegationUpdate);
            }

            // Trigger notification to requester
            try {
                $statusText = $newStatus === 1 ? 'được phê duyệt' : 'bị từ chối';
                $notificationService = app(\App\Services\NotificationService::class);
                $notificationService->notify([
                    'notification_type_id' => 2, // approval
                    'title' => "Yêu cầu {$approvalRequest->request_type} {$statusText}",
                    'body' => "Yêu cầu phê duyệt của bạn cho \"{$approvalRequest->request_type}\" đã {$statusText}.",
                    'ref_table' => $approvalRequest->ref_table,
                    'ref_id' => $approvalRequest->ref_id,
                    'severity' => $newStatus === 1 ? 1 : 2,
                ], (int) $approvalRequest->requester_user_id);
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('Failed to send approval decision notification: ' . $e->getMessage());
            }

            return $this->getById($approvalRequest->id);
        });
    }

    /**
     * Transform an approval request model into a list item array.
     *
     * @param ApprovalRequest $approvalRequest
     * @return array
     */
    private function transformListItem(ApprovalRequest $approvalRequest): array
    {
        $currentStep = $approvalRequest->steps->firstWhere('step_order', $approvalRequest->current_step) ?? $approvalRequest->steps->first();

        return [
            'id' => (string) $approvalRequest->id,
            'type' => (string) $approvalRequest->request_type,
            'typeLabel' => $this->resolveTypeLabel($approvalRequest),
            'title' => $this->resolveTitle($approvalRequest),
            'requesterId' => (string) $approvalRequest->requester_user_id,
            'requesterName' => $approvalRequest->requester?->full_name
                ?: $approvalRequest->requester?->username
                ?: (string) $approvalRequest->requester_user_id,
            'approverId' => $currentStep?->approver_user_id !== null ? (string) $currentStep->approver_user_id : null,
            'status' => $this->statusToText((int) $approvalRequest->status),
            'createdAt' => optional($approvalRequest->created_at)?->toIso8601String(),
            'dueAt' => optional($approvalRequest->due_at)?->toIso8601String(),
        ];
    }

    /**
     * Transform an approval request model into a detailed response array.
     *
     * @param ApprovalRequest $approvalRequest
     * @return array
     */
    private function transformDetailRequest(ApprovalRequest $approvalRequest): array
    {
        return array_merge($this->transformListItem($approvalRequest), [
            'refTable' => $approvalRequest->ref_table,
            'refId' => (string) $approvalRequest->ref_id,
            'currentStep' => $approvalRequest->current_step,
            'priority' => $approvalRequest->priority,
        ]);
    }

    /**
     * Transform a collection of approval steps into a standardized array.
     *
     * @param Collection $steps
     * @return array
     */
    private function transformSteps(Collection $steps): array
    {
        return $steps->map(static function (ApprovalStep $step): array {
            return [
                'id' => (string) $step->id,
                'status' => match ((int) $step->decision) {
                    1 => 'APPROVED',
                    2 => 'REJECTED',
                    default => 'PENDING',
                },
                'approverId' => (string) $step->approver_user_id,
                'stepOrder' => $step->step_order,
                'decisionNote' => $step->decision_note,
                'decidedAt' => optional($step->decided_at)?->toIso8601String(),
            ];
        })->all();
    }

    /**
     * Transform a collection of approval history logs into a standardized array.
     *
     * @param Collection<int, ApprovalHistory> $history
     * @return array
     */
    private function transformHistory(Collection $history): array
    {
        return $history->map(static function (ApprovalHistory $item): array {
            return [
                'decision' => match ((int) $item->new_status) {
                    1 => 'APPROVE',
                    2 => 'REJECT',
                    default => 'PENDING',
                },
                'decisionNote' => null,
                'decidedAt' => optional($item->changed_at)?->toIso8601String(),
                'deciderUserId' => (string) $item->changed_by,
                'oldStatus' => self::statusToText((int) $item->old_status),
                'newStatus' => self::statusToText((int) $item->new_status),
            ];
        })->all();
    }

    /**
     * Resolve a human-readable title for an approval request.
     *
     * @param ApprovalRequest $approvalRequest
     * @return string
     */
    private function resolveTitle(ApprovalRequest $approvalRequest): string
    {
        $label = $this->resolveTypeLabel($approvalRequest);

        if ($approvalRequest->ref_table === 'ipa_delegation') {
            return $label . ' #' . $approvalRequest->ref_id;
        }

        if ($approvalRequest->ref_table === 'ipa_minutes') {
            return $label . ' #' . $approvalRequest->ref_id;
        }

        if ($approvalRequest->ref_table === 'ipa_event') {
            return $label . ' #' . $approvalRequest->ref_id;
        }

        return $label !== '' ? $label . ' #' . $approvalRequest->ref_id : 'Approval #' . $approvalRequest->id;
    }

    /**
     * Resolve a human-readable approval type label.
     *
     * @param ApprovalRequest $approvalRequest
     * @return string
     */
    private function resolveTypeLabel(ApprovalRequest $approvalRequest): string
    {
        return match (strtoupper(trim((string) $approvalRequest->request_type))) {
            'DELEGATION_APPROVAL' => 'Đoàn công tác',
            'MINUTES_APPROVAL' => 'Biên bản',
            'EVENT_APPROVAL' => 'Lịch làm việc',
            default => 'Phê duyệt',
        };
    }

    /**
     * Map a numeric status to its human-readable text representation.
     *
     * @param int $status
     * @return string
     */
    private static function statusToText(int $status): string
    {
        return match ($status) {
            1 => 'APPROVED',
            2 => 'REJECTED',
            default => 'PENDING',
        };
    }
}
