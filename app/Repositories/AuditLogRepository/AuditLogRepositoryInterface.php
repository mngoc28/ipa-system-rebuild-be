<?php

declare(strict_types=1);

namespace App\Repositories\AuditLogRepository;

use App\Repositories\RepositoryInterface;
use Illuminate\Http\Request;

/**
 * Interface AuditLogRepositoryInterface
 *
 * Provides data access for system audit logs.
 *
 * @package App\Repositories\AuditLogRepository
 */
interface AuditLogRepositoryInterface extends RepositoryInterface
{
    /**
     * Get a paginated list of audit logs with filtering.
     *
     * @param Request $request
     * @return array
     */
    public function getPaginated(Request $request): array;
}
