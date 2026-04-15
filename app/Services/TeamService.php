<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\TeamRepository\TeamRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Throwable;

final class TeamService
{
    public function __construct(
        private readonly TeamRepositoryInterface $teamRepository,
    ) {
    }

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