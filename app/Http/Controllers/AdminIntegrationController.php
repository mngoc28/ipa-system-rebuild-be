<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\HttpStatus;
use App\Http\Validations\IntegrationValidation;
use App\Services\SystemSettingService;
use Illuminate\Http\JsonResponse;

/**
 * Class AdminIntegrationController
 *
 * Manages administrative tasks related to third-party integrations,
 * such as testing connectivity and configuration for external services.
 *
 * @package App\Http\Controllers
 */
final class AdminIntegrationController extends Controller
{
    /**
     * AdminIntegrationController constructor.
     *
     * @param SystemSettingService $systemSettingService
     * @param IntegrationValidation $integrationValidation
     */
    public function __construct(
        private SystemSettingService $systemSettingService,
        private IntegrationValidation $integrationValidation,
    ) {
    }

    /**
     * Test the integration with a specific third-party provider (e.g., Zalo).
     *
     * @param string $provider The provider identifier.
     * @return JsonResponse
     */
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
