<?php

declare(strict_types=1);

namespace App\Repositories\MinutesRepository;

use App\Repositories\RepositoryInterface;
use Illuminate\Http\Request;

/**
 * Interface MinutesRepositoryInterface
 *
 * Provides specialized data access for meeting minutes management, including versioning, commenting, and approvals.
 *
 * @package App\Repositories\MinutesRepository
 */
interface MinutesRepositoryInterface extends RepositoryInterface
{
    /**
     * Get a paginated list of minutes with filtering (keyword, status, time range).
     *
     * @param Request $request
     * @return array
     */
    public function getPaginated(Request $request): array;

    /**
     * Get detailed information for a specific meeting minutes, including latest version and comments.
     *
     * @param string $id
     * @return array|null
     */
    public function findDetail(string $id): ?array;

    /**
     * Create a new meeting minutes record and its initial content.
     *
     * @param array $attributes
     * @param int $ownerUserId
     * @return array|null
     */
    public function createMinutes(array $attributes, int $ownerUserId): ?array;

    /**
     * Create a new version of an existing meeting minutes.
     *
     * @param string $id
     * @param array $attributes
     * @param int $editedBy
     * @return array|null
     */
    public function createVersion(string $id, array $attributes, int $editedBy): ?array;

    /**
     * Add a comment to a specific meeting minutes.
     *
     * @param string $id
     * @param array $attributes
     * @param int $commenterUserId
     * @return array|null
     */
    public function createComment(string $id, array $attributes, int $commenterUserId): ?array;

    /**
     * Approve or update the status of meeting minutes.
     *
     * @param string $id
     * @param array $attributes
     * @param int $approverUserId
     * @return array|null
     */
    public function approve(string $id, array $attributes, int $approverUserId): ?array;
}
