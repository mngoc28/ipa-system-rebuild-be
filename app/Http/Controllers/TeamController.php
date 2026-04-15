<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\HttpStatus;
use App\Http\Validations\TeamValidation;
use App\Services\TeamService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class TeamController extends Controller
{
    public function __construct(
        private readonly TeamService $teamService,
        private readonly TeamValidation $teamValidation,
    ) {
    }

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

    public function units(Request $request): JsonResponse
    {
        $result = $this->teamService->getUnits($request);

        if (! $result['success']) {
            return $this->errorResponse($result['message'], 'FETCH_FAILED', HttpStatus::BAD_REQUEST);
        }

        return $this->successResponse($result['data'], $result['message']);
    }

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