<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\HttpStatus;
use App\Http\Validations\IntegrationValidation;
use App\Services\SystemSettingService;
use Illuminate\Http\JsonResponse;

final class AdminIntegrationController extends Controller
{
    public function __construct(
        private SystemSettingService $systemSettingService,
        private IntegrationValidation $integrationValidation,
    ) {
    }

    public function test(string $provider): JsonResponse
    {
        $validator = $this->integrationValidation->testValidation($provider);

        if ($validator->fails()) {
            return $this->validateError($validator->errors(), 'VALIDATION_FAILED', HttpStatus::VALIDATION_ERROR);
        }

        $result = $this->systemSettingService->testIntegration($provider);

        if (! $result['success']) {
            return $this->errorResponse($result['message'], 'TEST_FAILED', HttpStatus::BAD_REQUEST);
        }

        return $this->successResponse($result['data'], $result['message']);
    }
}
