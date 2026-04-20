<?php

namespace App\Repositories\DelegationRepository;

use Illuminate\Http\Request;

/**
 * Interface DelegationRepositoryInterface
 *
 * Provides specialized data access for delegation management.
 *
 * @package App\Repositories\DelegationRepository
 */
interface DelegationRepositoryInterface
{
    /**
     * Get paginated delegations with filtering.
     *
     * @param Request $request
     * @return mixed
     */
    public function getPaginated(Request $request);

    /**
     * Get delegation details by ID.
     *
     * @param int $id
     * @return mixed
     */
    public function getById(int $id);

    /**
     * Create a new delegation.
     *
     * @param array $data
     * @return mixed
     */
    public function create(array $data);

    /**
     * Update an existing delegation.
     *
     * @param int $id
     * @param array $data
     * @return mixed
     */
    public function update(int $id, array $data);

    /**
     * Delete a delegation.
     *
     * @param int $id
     * @return mixed
     */
    public function delete(int $id);

    /**
     * Update a specific delegation comment.
     *
     * @param int $commentId
     * @param array $data
     * @return mixed
     */
    public function updateComment(int $commentId, array $data);

    /**
     * Delete a specific delegation comment.
     *
     * @param int $commentId
     * @return mixed
     */
    public function deleteComment(int $commentId);
}
