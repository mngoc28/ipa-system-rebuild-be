<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\HttpStatus;
use App\Http\Validations\PipelineValidation;
use App\Services\PipelineService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class PipelineController extends Controller
{
    public function __construct(
        private PipelineService $pipelineService,
        private PipelineValidation $pipelineValidation,
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        $validator = $this->pipelineValidation->indexValidation($request);

        if ($validator->fails()) {
            return $this->validateError($validator->errors(), 'VALIDATION_FAILED', HttpStatus::VALIDATION_ERROR);
        }

        $result = $this->pipelineService->getProjects($request);

        if (! $result['success']) {
            return $this->errorResponse($result['message'], 'FETCH_FAILED', HttpStatus::BAD_REQUEST);
        }

        return $this->successResponse($result['data'], $result['message'], HttpStatus::OK, $result['data']['meta'] ?? null);
    }

    public function summary(Request $request): JsonResponse
    {
        $result = $this->pipelineService->getSummary();

        if (! $result['success']) {
            return $this->errorResponse($result['message'], 'FETCH_FAILED', HttpStatus::BAD_REQUEST);
        }

        return $this->successResponse($result['data'], $result['message']);
    }

    public function store(Request $request): JsonResponse
    {
        $validator = $this->pipelineValidation->createValidation($request);

        if ($validator->fails()) {
            return $this->validateError($validator->errors(), 'VALIDATION_FAILED', HttpStatus::VALIDATION_ERROR);
        }

        $result = $this->pipelineService->createProject(
            $request->only([
                'project_code',
                'project_name',
                'partner_id',
                'country_id',
                'sector_id',
                'delegation_id',
                'stage_id',
                'estimated_value',
                'success_probability',
                'expected_close_date',
                'owner_user_id',
            ]),
            (int) ($request->user()?->id ?? 0) ?: null,
        );

        if (! $result['success']) {
            return $this->errorResponse($result['message'], 'CREATE_FAILED', HttpStatus::BAD_REQUEST);
        }

        return $this->createdResponse($result['data'], $result['message']);
    }

    public function show(string $id): JsonResponse
    {
        $result = $this->pipelineService->findProject($id);

        if (! $result['success']) {
            return $this->errorResponse($result['message'], 'FETCH_FAILED', HttpStatus::NOT_FOUND);
        }

        return $this->successResponse($result['data'], $result['message']);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $validator = $this->pipelineValidation->updateValidation($request);

        if ($validator->fails()) {
            return $this->validateError($validator->errors(), 'VALIDATION_FAILED', HttpStatus::VALIDATION_ERROR);
        }

        $result = $this->pipelineService->updateProject(
            $id,
            $request->only([
                'project_name',
                'partner_id',
                'country_id',
                'sector_id',
                'delegation_id',
                'stage_id',
                'estimated_value',
                'success_probability',
                'expected_close_date',
                'owner_user_id',
                'status',
            ])
        );

        if (! $result['success']) {
            return $this->errorResponse($result['message'], 'UPDATE_FAILED', HttpStatus::BAD_REQUEST);
        }

        return $this->successResponse($result['data'], $result['message']);
    }

    public function destroy(string $id): JsonResponse
    {
        $result = $this->pipelineService->deleteProject($id);

        if (! $result['success']) {
            return $this->errorResponse($result['message'], 'DELETE_FAILED', HttpStatus::BAD_REQUEST);
        }

        return $this->successResponse([], $result['message']);
    }

    public function patchStage(Request $request, string $id): JsonResponse
    {
        $validator = $this->pipelineValidation->stageValidation($request);

        if ($validator->fails()) {
            return $this->validateError($validator->errors(), 'VALIDATION_FAILED', HttpStatus::VALIDATION_ERROR);
        }

        $result = $this->pipelineService->patchStage(
            $id,
            (string) $request->input('new_stage_id'),
            $request->input('reason'),
            (int) ($request->user()?->id ?? 0) ?: null,
        );

        if (! $result['success']) {
            return $this->errorResponse($result['message'], 'UPDATE_FAILED', HttpStatus::BAD_REQUEST);
        }

        return $this->successResponse($result['data'], $result['message']);
    }
}
