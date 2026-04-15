<?php

declare(strict_types=1);

namespace App\Repositories\AuditLogRepository;

use App\Repositories\RepositoryInterface;
use Illuminate\Http\Request;

interface AuditLogRepositoryInterface extends RepositoryInterface
{
    public function getPaginated(Request $request): array;
}