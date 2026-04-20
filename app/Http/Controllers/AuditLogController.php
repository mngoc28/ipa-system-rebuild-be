<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\HttpStatus;
use App\Http\Validations\AuditLogValidation;
use App\Services\AuditLogService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Class AuditLogController
 *
 * Provides access to system audit logs, allowing administrators to track
 * changes and activities within the application.
 *
 * @package App\Http\Controllers
 */
final class AuditLogController extends Controller
{
    /**
     * AuditLogController constructor.
     *
     * @param AuditLogService $auditLogService
     * @param AuditLogValidation $auditLogValidation
     */
    public function __construct(
        private AuditLogService $auditLogService,
        private AuditLogValidation $auditLogValidation,
    ) {
    }

    /**
     * Retrieve a filtered list of audit logs with pagination.
     *
     * @param Request $request Filtering (user_id, action, context) and paging parameters.
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $validator = $this->auditLogValidation->indexValidation($request);

        if ($validator->fails()) {
            return $this->validateError($validator->errors(), 'VALIDATION_FAILED', HttpStatus::VALIDATION_ERROR);
        }

        $result = $this->auditLogService->getAll($request);

        if (! $result['success']) {
            return $this->errorResponse($result['message'], 'FETCH_FAILED', HttpStatus::BAD_REQUEST);
        }

        return $this->successResponse($result['data'], $result['message'], HttpStatus::OK, $result['data']['meta'] ?? null);
    }
}
