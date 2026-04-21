<?php

declare(strict_types=1);

namespace App\Http\Controllers\Team;

use App\Enums\HttpStatus;
use App\Http\Controllers\Controller;
use App\Http\Validations\TeamValidation;
use App\Services\TeamService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Class TeamController
 *
 * Manages organizational teams and units, including team hierarchies,
 * member assignments, and unit-level data retrieval.
 *
 * @package App\Http\Controllers\Team
 */
final class TeamController extends Controller
{
    /**
     * TeamController constructor.
     *
     * @param TeamService $teamService
     * @param TeamValidation $teamValidation
     */
    public function __construct(
        private TeamService $teamService,
        private TeamValidation $teamValidation,
    ) {
    }

    /**
     * List all teams and units for the dashboard overview.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $validator = $this->teamValidation->indexValidation($request);

        if ($validator->fails()) {
            return $this->validateError($validator->errors(), 'VALIDATION_FAILED', HttpStatus::VALIDATION_ERROR);
        }

        $result = $this->teamService->getDashboard($request);

        if (! $result['success']) {
            return $this->errorResponse($result['message'], 'FETCH_FAILED', HttpStatus::BAD_REQUEST);
        }

        return $this->successResponse($result['data'], $result['message'], HttpStatus::OK, $result['data']['meta'] ?? null);
    }

    /**
     * Retrieve a flat list of organizational units (Departments, Divisions).
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function units(Request $request): JsonResponse
    {
        $result = $this->teamService->getUnits($request);

        if (! $result['success']) {
            return $this->errorResponse($result['message'], 'FETCH_FAILED', HttpStatus::BAD_REQUEST);
        }

        return $this->successResponse($result['data'], $result['message']);
    }

    /**
     * Assign a new member to a team or update their assignment.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function storeMember(Request $request): JsonResponse
    {
        $validator = $this->teamValidation->storeValidation($request);

        if ($validator->fails()) {
            return $this->validateError($validator->errors(), 'VALIDATION_FAILED', HttpStatus::VALIDATION_ERROR);
        }

        $result = $this->teamService->createMember($request->all());

        if (! $result) {
            return $this->errorResponse(__('teams.messages.create_error'), 'CREATE_FAILED', HttpStatus::BAD_REQUEST);
        }

        return $this->createdResponse($result, __('teams.messages.create_success'));
    }
}
