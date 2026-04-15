<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\HttpStatus;
use App\Http\Validations\MasterDataValidation;
use App\Services\MasterDataService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class MasterDataController extends Controller
{
    public function __construct(
        private readonly MasterDataService $masterDataService,
        private readonly MasterDataValidation $masterDataValidation,
    ) {
    }

    public function index(string $domain, Request $request): JsonResponse
    {
        $validator = $this->masterDataValidation->indexValidation($request, $domain);

        if ($validator->fails()) {
            return $this->validateError($validator->errors(), 'VALIDATION_FAILED', HttpStatus::VALIDATION_ERROR);
        }

        $result = $this->masterDataService->getAll($request, $domain);

        if (! $result['success']) {
            return $this->errorResponse($result['message'], 'FETCH_FAILED', HttpStatus::BAD_REQUEST);
        }

        return $this->successResponse($result['data'], $result['message']);
    }

    public function show(string $domain, string $id): JsonResponse
    {
        $validator = $this->masterDataValidation->getByIdValidation($domain, $id);

        if ($validator->fails()) {
            return $this->validateError($validator->errors(), 'VALIDATION_FAILED', HttpStatus::VALIDATION_ERROR);
        }

        $result = $this->masterDataService->getById($domain, $id);

        if (! $result) {
            return $this->errorResponse(__('master_data.messages.not_found'), 'NOT_FOUND', HttpStatus::NOT_FOUND);
        }

        return $this->successResponse($result, __('master_data.messages.fetch_success'));
    }

    public function store(string $domain, Request $request): JsonResponse
    {
        $validator = $this->masterDataValidation->createValidation($request, $domain);

        if ($validator->fails()) {
            return $this->validateError($validator->errors(), 'VALIDATION_FAILED', HttpStatus::VALIDATION_ERROR);
        }

        $result = $this->masterDataService->create($domain, $request->only([
            'code',
            'name_vi',
            'name_en',
            'sort_order',
            'is_active',
        ]));

        if (! $result) {
            return $this->errorResponse(__('master_data.messages.create_error'), 'CREATE_FAILED', HttpStatus::BAD_REQUEST);
        }

        return $this->createdResponse($result, __('master_data.messages.create_success'));
    }

    public function update(string $domain, string $id, Request $request): JsonResponse
    {
        $validator = $this->masterDataValidation->updateValidation($domain, $id, $request);

        if ($validator->fails()) {
            return $this->validateError($validator->errors(), 'VALIDATION_FAILED', HttpStatus::VALIDATION_ERROR);
        }

        $result = $this->masterDataService->update($domain, $id, $request->only([
            'code',
            'name_vi',
            'name_en',
            'sort_order',
            'is_active',
        ]));

        if (! $result) {
            return $this->errorResponse(__('master_data.messages.update_error'), 'UPDATE_FAILED', HttpStatus::BAD_REQUEST);
        }

        return $this->successResponse($result, __('master_data.messages.update_success'));
    }

    public function destroy(string $domain, string $id): JsonResponse
    {
        $validator = $this->masterDataValidation->getByIdValidation($domain, $id);

        if ($validator->fails()) {
            return $this->validateError($validator->errors(), 'VALIDATION_FAILED', HttpStatus::VALIDATION_ERROR);
        }

        $result = $this->masterDataService->delete($domain, $id);

        if (! $result) {
            return $this->errorResponse(__('master_data.messages.delete_error'), 'DELETE_FAILED', HttpStatus::BAD_REQUEST);
        }

        return $this->successResponse(null, __('master_data.messages.delete_success'));
    }
}
