<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\AuditLogRepository\AuditLogRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Throwable;

final class AuditLogService
{
    public function __construct(
        private readonly AuditLogRepositoryInterface $auditLogRepository,
    ) {
    }

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