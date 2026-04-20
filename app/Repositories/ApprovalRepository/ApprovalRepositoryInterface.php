<?php

declare(strict_types=1);

namespace App\Repositories\ApprovalRepository;

use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Interface ApprovalRepositoryInterface
 *
 * Handles data access for approval requests and decision-making.
 *
 * @package App\Repositories\ApprovalRepository
 */
interface ApprovalRepositoryInterface
{
    /**
     * Get paginated list of approval requests.
     *
     * @param Request $request
     * @return LengthAwarePaginator
     */
    public function getPaginated(Request $request): LengthAwarePaginator;

    /**
     * Find an approval request by its ID.
     *
     * @param int $id
     * @return array|null
     */
    public function getById(int $id): ?array;

    /**
     * Capture a decision on an approval request.
     *
     * @param int $id
     * @param array $data Decision details (status, comment, etc.)
     * @param int $userId The ID of the user making the decision.
     * @return array|null
     */
    public function decide(int $id, array $data, int $userId): ?array;
}
