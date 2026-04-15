<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\HttpStatus;
use App\Http\Validations\SystemSettingValidation;
use App\Services\SystemSettingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class SystemSettingController extends Controller
{
    public function __construct(
        private readonly SystemSettingService $systemSettingService,
        private readonly SystemSettingValidation $systemSettingValidation,
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        $validator = $this->systemSettingValidation->indexValidation($request);

        if ($validator->fails()) {
            return $this->validateError($validator->errors(), 'VALIDATION_FAILED', HttpStatus::VALIDATION_ERROR);
        }

        $result = $this->systemSettingService->getAll($request);

        if (! $result['success']) {
            return $this->errorResponse($result['message'], 'FETCH_FAILED', HttpStatus::BAD_REQUEST);
        }

        return $this->successResponse($result['data'], $result['message']);
    }

    public function update(Request $request): JsonResponse
    {
        $validator = $this->systemSettingValidation->updateValidation($request);

        if ($validator->fails()) {
            return $this->validateError($validator->errors(), 'VALIDATION_FAILED', HttpStatus::VALIDATION_ERROR);
        }

        $payload = $request->input('items', []);
        $updatedBy = $request->user()?->id;

        $result = $this->systemSettingService->update($payload, is_numeric($updatedBy) ? (int) $updatedBy : null);

        if (! $result['success']) {
            return $this->errorResponse($result['message'], 'UPDATE_FAILED', HttpStatus::BAD_REQUEST);
        }

        return $this->successResponse($result['data'], $result['message']);
    }
}