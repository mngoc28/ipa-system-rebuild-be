<?php

declare(strict_types=1);

namespace App\Repositories\TeamRepository;

use Illuminate\Http\Request;

/**
 * Interface TeamRepositoryInterface
 *
 * Provides operations for team and organizational unit management, including dashboard stats and unit hierarchies.
 *
 * @package App\Repositories\TeamRepository
 */
interface TeamRepositoryInterface
{
    /**
     * Get a comprehensive dashboard view of team members and organizational structure.
     *
     * @param Request $request
     * @return array
     */
    public function getDashboard(Request $request): array;

    /**
     * Get a list of organizational units (departments/divisions) filtered by request parameters.
     *
     * @param Request $request
     * @return array
     */
    public function getUnits(Request $request): array;

    /**
     * Create a new team member record and return the normalized result.
     *
     * @param array $attributes
     * @return array
     */
    public function createMember(array $attributes): array;
}
