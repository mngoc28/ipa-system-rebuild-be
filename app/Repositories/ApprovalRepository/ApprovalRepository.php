<?php

declare(strict_types=1);

namespace App\Repositories\ApprovalRepository;

use App\Models\ApprovalHistory;
use App\Models\ApprovalRequest;
use App\Models\ApprovalStep;
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
                    ->orWhere('ref_table', 'like', '%' . $keyword . '%')
                    ->orWhereRaw('CAST(ref_id AS CHAR) like ?', ['%' . $keyword . '%']);
            });
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
            'title' => $this->resolveTitle($approvalRequest),
            'requesterId' => (string) $approvalRequest->requester_user_id,
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
        $label = trim($approvalRequest->request_type . ' ' . $approvalRequest->ref_table);

        if ($label === '') {
            return 'Approval #' . $approvalRequest->id;
        }

        return $label . ' #' . $approvalRequest->ref_id;
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
