<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\HttpStatus;
use App\Http\Validations\ApprovalValidation;
use App\Services\ApprovalService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

final class ApprovalController extends Controller
{
    public function __construct(
        private readonly ApprovalService $approvalService,
        private readonly ApprovalValidation $approvalValidation,
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        $validator = $this->approvalValidation->indexValidation($request);

        if ($validator->fails()) {
            return $this->validateError($validator->errors(), 'VALIDATION_FAILED', HttpStatus::VALIDATION_ERROR);
        }

        $result = $this->approvalService->listApprovals($request);

        $meta = [
            'page' => $result->currentPage(),
            'pageSize' => $result->perPage(),
            'total' => $result->total(),
            'totalPages' => $result->lastPage(),
        ];

        return $this->successResponse(['items' => $result->items()], __('approvals.messages.fetch_success'), HttpStatus::OK, $meta);
    }

    public function show(string $id): JsonResponse
    {
        $result = $this->approvalService->getApproval((int) $id);

        if ($result === null) {
            return $this->errorResponse(__('approvals.messages.not_found'), 'NOT_FOUND', HttpStatus::NOT_FOUND);
        }

        return $this->successResponse($result, __('approvals.messages.fetch_success'));
    }

    public function decision(Request $request, string $id): JsonResponse
    {
        $validator = $this->approvalValidation->decisionValidation($request);

        if ($validator->fails()) {
            return $this->validateError($validator->errors(), 'VALIDATION_FAILED', HttpStatus::VALIDATION_ERROR);
        }

        $result = $this->approvalService->decideApproval((int) $id, $request->all(), $this->resolveUserId($request));

        if ($result === null) {
            return $this->errorResponse(__('approvals.messages.not_found'), 'NOT_FOUND', HttpStatus::NOT_FOUND);
        }

        return $this->successResponse($result, __('approvals.messages.decision_success'));
    }

    private function resolveUserId(Request $request): int
    {
        $authenticatedUserId = (int) ($request->user()?->id ?? 0);

        if ($authenticatedUserId > 0) {
            return $authenticatedUserId;
        }

        $mockUserId = (int) $request->header('X-Mock-User-Id', 0);
        if ($mockUserId > 0) {
            return $mockUserId;
        }

        $mockUsername = trim((string) $request->header('X-Mock-Username', ''));
        $mockEmail = trim((string) $request->header('X-Mock-Email', ''));

        if ($mockUsername === '' && $mockEmail === '') {
            return (int) (DB::table('ipa_user')->min('id') ?? 0);
        }

        $query = DB::table('ipa_user')->select('id');

        if ($mockUsername !== '' && $mockEmail !== '') {
            $query->where(function ($builder) use ($mockUsername, $mockEmail): void {
                $builder->where('username', $mockUsername)
                    ->orWhere('email', $mockEmail);
            });
        } elseif ($mockUsername !== '') {
            $query->where('username', $mockUsername);
        } else {
            $query->where('email', $mockEmail);
        }

        return (int) ($query->value('id') ?? DB::table('ipa_user')->min('id') ?? 0);
    }
}