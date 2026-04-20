<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\ApprovalRepository\ApprovalRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Log;

/**
 * Class ApprovalService
 *
 * Handles the business logic for managing approval requests, including retrieval and final decision making.
 *
 * @package App\Services
 */
final class ApprovalService
{
    /**
     * ApprovalService constructor.
     *
     * @param ApprovalRepositoryInterface $repository
     */
    public function __construct(private ApprovalRepositoryInterface $repository)
    {
    }

    /**
     * Retrieve a paginated list of approval requests based on filters.
     *
     * @param Request $request
     * @return LengthAwarePaginator
     */
    public function listApprovals(Request $request): LengthAwarePaginator
    {
        return $this->repository->getPaginated($request);
    }

    /**
     * Get the details of a specific approval request.
     *
     * @param int $id
     * @return array|null Normalized approval data or null if not found.
     */
    public function getApproval(int $id): ?array
    {
        return $this->repository->getById($id);
    }

    /**
     * Process a decision (Approve/Reject) on an approval request.
     *
     * @param int $id The approval request identifier.
     * @param array $data Contains decision details (status, comment).
     * @param int $userId The identifier of the user making the decision.
     * @return array|null The updated approval request data or null on failure.
     * @throws \Throwable If processing fails.
     */
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
