<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\AuditLogRepository\AuditLogRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Class AuditLogService
 *
 * Provides business logic for retrieving and searching system audit logs.
 *
 * @package App\Services
 */
final class AuditLogService
{
    /**
     * AuditLogService constructor.
     *
     * @param AuditLogRepositoryInterface $auditLogRepository
     */
    public function __construct(
        private AuditLogRepositoryInterface $auditLogRepository,
    ) {
    }

    /**
     * Retrieve a paginated list of audit logs based on search and filter criteria.
     *
     * @param Request $request
     * @return array Response structure with success status, data, and translated message.
     */
    public function getAll(Request $request): array
    {
        try {
            return [
                'success' => true,
                'data' => $this->auditLogRepository->getPaginated($request),
                'message' => __('audit_log.messages.fetch_success'),
            ];
        } catch (Throwable $throwable) {
            Log::error('AuditLogService::getAll', [
                'error' => $throwable->getMessage(),
            ]);

            return [
                'success' => false,
                'data' => null,
                'message' => __('audit_log.messages.fetch_error'),
            ];
        }
    }
}
