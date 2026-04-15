<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\HttpStatus;
use App\Http\Validations\ReportValidation;
use App\Services\ReportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class ReportController extends Controller
{
    public function __construct(
        private readonly ReportService $reportService,
        private readonly ReportValidation $reportValidation,
    ) {
    }

    public function definitions(Request $request): JsonResponse
    {
        $validator = $this->reportValidation->definitionsValidation($request);

        if ($validator->fails()) {
            return $this->validateError($validator->errors(), 'VALIDATION_FAILED', HttpStatus::VALIDATION_ERROR);
        }

        $result = $this->reportService->getDefinitions();

        if (! $result['success']) {
            return $this->errorResponse($result['message'], 'FETCH_FAILED', HttpStatus::BAD_REQUEST);
        }

        return $this->successResponse($result['data'], $result['message'], HttpStatus::OK, $result['data']['meta'] ?? null);
    }

    public function summary(Request $request): JsonResponse
    {
        $result = $this->reportService->getSummary();

        if (! $result['success']) {
            return $this->errorResponse($result['message'], 'FETCH_FAILED', HttpStatus::BAD_REQUEST);
        }

        return $this->successResponse($result['data'], $result['message'], HttpStatus::OK);
    }

    public function store(Request $request): JsonResponse
    {
        $validator = $this->reportValidation->createRunValidation($request);

        if ($validator->fails()) {
            return $this->validateError($validator->errors(), 'VALIDATION_FAILED', HttpStatus::VALIDATION_ERROR);
        }

        $result = $this->reportService->createRun(
            $request->only(['report_code', 'params']),
            (int) ($request->user()?->id ?? 0) ?: null,
        );

        if (! $result['success']) {
            return $this->errorResponse($result['message'], 'CREATE_FAILED', HttpStatus::BAD_REQUEST);
        }

        return $this->createdResponse($result['data'], $result['message']);
    }

    public function show(Request $request, string $runId): JsonResponse
    {
        $validator = $this->reportValidation->showRunValidation($runId);

        if ($validator->fails()) {
            return $this->validateError($validator->errors(), 'VALIDATION_FAILED', HttpStatus::VALIDATION_ERROR);
        }

        $result = $this->reportService->getRun($runId);

        if (! $result['success']) {
            return $this->errorResponse($result['message'], 'NOT_FOUND', HttpStatus::NOT_FOUND);
        }

        return $this->successResponse($result['data'], $result['message']);
    }
}