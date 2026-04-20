<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\TeamRepository\TeamRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Class TeamService
 *
 * Orchestrates business logic for team management and organizational hierarchy.
 * Handles dashboard statistics and member management.
 *
 * @package App\Services
 */
final class TeamService
{
    /**
     * TeamService constructor.
     *
     * @param TeamRepositoryInterface $teamRepository
     */
    public function __construct(
        private TeamRepositoryInterface $teamRepository,
    ) {
    }

    /**
     * Retrieve aggregated statistics and metrics for the teams dashboard.
     *
     * @param Request $request Filter and period parameters.
     * @return array Standard response bundle.
     */
    public function getDashboard(Request $request): array
    {
        try {
            return [
                'success' => true,
                'data' => $this->teamRepository->getDashboard($request),
                'message' => __('teams.messages.fetch_success'),
            ];
        } catch (Throwable $throwable) {
            Log::error('TeamService::getDashboard', ['error' => $throwable->getMessage()]);

            return ['success' => false, 'data' => null, 'message' => __('teams.messages.fetch_error')];
        }
    }

    /**
     * Retrieve a list of organizational units (teams/departments).
     *
     * @param Request $request Filtering parameters.
     * @return array Standard response bundle.
     */
    public function getUnits(Request $request): array
    {
        try {
            return [
                'success' => true,
                'data' => $this->teamRepository->getUnits($request),
                'message' => __('teams.messages.fetch_success'),
            ];
        } catch (Throwable $throwable) {
            Log::error('TeamService::getUnits', ['error' => $throwable->getMessage()]);

            return ['success' => false, 'data' => null, 'message' => __('teams.messages.fetch_error')];
        }
    }

    /**
     * Add a new member to a team or organizational unit.
     *
     * @param array $attributes Member details (user_id, team_id, role, etc.).
     * @return array|null Normalized member data or null on failure.
     */
    public function createMember(array $attributes): ?array
    {
        try {
            return $this->teamRepository->createMember($attributes);
        } catch (Throwable $throwable) {
            Log::error('TeamService::createMember', ['error' => $throwable->getMessage()]);

            return null;
        }
    }
}
