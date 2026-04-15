<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\ApprovalRepository\ApprovalRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Log;

final class ApprovalService
{
    public function __construct(private readonly ApprovalRepositoryInterface $repository)
    {
    }

    public function listApprovals(Request $request): LengthAwarePaginator
    {
        return $this->repository->getPaginated($request);
    }

    public function getApproval(int $id): ?array
    {
        return $this->repository->getById($id);
    }

    public function decideApproval(int $id, array $data, int $userId): ?array
    {
        try {
            return $this->repository->decide($id, $data, $userId);
        } catch (\Throwable $throwable) {
            Log::error('Failed to decide approval: ' . $throwable->getMessage());

            throw $throwable;
        }
    }
}