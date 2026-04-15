<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\HttpStatus;
use App\Http\Validations\MinutesValidation;
use App\Services\MinutesService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

final class MinutesController extends Controller
{
    public function __construct(
        private readonly MinutesService $minutesService,
        private readonly MinutesValidation $minutesValidation,
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        $validator = $this->minutesValidation->indexValidation($request);

        if ($validator->fails()) {
            return $this->validateError($validator->errors(), 'VALIDATION_FAILED', HttpStatus::VALIDATION_ERROR);
        }

        $result = $this->minutesService->getAll($request);

        if (! $result['success']) {
            return $this->errorResponse($result['message'], 'FETCH_FAILED', HttpStatus::BAD_REQUEST);
        }

        return $this->successResponse($result['data'], $result['message'], HttpStatus::OK, $result['data']['meta'] ?? null);
    }

    public function show(string $id): JsonResponse
    {
        $result = $this->minutesService->getById($id);

        if (! $result['success']) {
            return $this->errorResponse($result['message'], 'NOT_FOUND', HttpStatus::NOT_FOUND);
        }

        return $this->successResponse($result['data'], $result['message']);
    }

    public function store(Request $request): JsonResponse
    {
        $validator = $this->minutesValidation->storeValidation($request);

        if ($validator->fails()) {
            return $this->validateError($validator->errors(), 'VALIDATION_FAILED', HttpStatus::VALIDATION_ERROR);
        }

        $result = $this->minutesService->create($request->all(), $this->resolveUserId($request));

        if (! $result['success']) {
            return $this->errorResponse($result['message'], 'CREATE_FAILED', HttpStatus::BAD_REQUEST);
        }

        return $this->createdResponse($result['data'], $result['message']);
    }

    public function createVersion(Request $request, string $id): JsonResponse
    {
        $validator = $this->minutesValidation->versionValidation($request);

        if ($validator->fails()) {
            return $this->validateError($validator->errors(), 'VALIDATION_FAILED', HttpStatus::VALIDATION_ERROR);
        }

        if (! $request->filled('contentText') && ! $request->filled('contentJson')) {
            return $this->validateError(['contentText' => [__('minutes.messages.version_content_required')]], 'VALIDATION_FAILED', HttpStatus::VALIDATION_ERROR);
        }

        $result = $this->minutesService->createVersion($id, $request->all(), $this->resolveUserId($request));

        if (! $result['success']) {
            return $this->errorResponse($result['message'], 'CREATE_FAILED', HttpStatus::BAD_REQUEST);
        }

        return $this->createdResponse($result['data'], $result['message']);
    }

    public function createComment(Request $request, string $id): JsonResponse
    {
        $validator = $this->minutesValidation->commentValidation($request);

        if ($validator->fails()) {
            return $this->validateError($validator->errors(), 'VALIDATION_FAILED', HttpStatus::VALIDATION_ERROR);
        }

        $result = $this->minutesService->createComment($id, $request->all(), $this->resolveUserId($request));

        if (! $result['success']) {
            return $this->errorResponse($result['message'], 'CREATE_FAILED', HttpStatus::BAD_REQUEST);
        }

        return $this->createdResponse($result['data'], $result['message']);
    }

    public function approve(Request $request, string $id): JsonResponse
    {
        $validator = $this->minutesValidation->approveValidation($request);

        if ($validator->fails()) {
            return $this->validateError($validator->errors(), 'VALIDATION_FAILED', HttpStatus::VALIDATION_ERROR);
        }

        $result = $this->minutesService->approve($id, $request->all(), $this->resolveUserId($request));

        if (! $result['success']) {
            return $this->errorResponse($result['message'], 'UPDATE_FAILED', HttpStatus::BAD_REQUEST);
        }

        return $this->successResponse($result['data'], $result['message']);
    }

    private function resolveUserId(Request $request): int
    {
        $authenticatedUserId = (int) ($request->user()?->id ?? 0);

        if ($authenticatedUserId > 0) {
            return $authenticatedUserId;
        }

        $mockUserId = (int) $request->header('X-Mock-User-Id', 0);
        if ($mockUserId > 0) {
            return $mockUserId;
        }

        $mockUsername = trim((string) $request->header('X-Mock-Username', ''));
        $mockEmail = trim((string) $request->header('X-Mock-Email', ''));

        if ($mockUsername === '' && $mockEmail === '') {
            return (int) (DB::table('ipa_user')->min('id') ?? 0);
        }

        $query = DB::table('ipa_user')->select('id');

        if ($mockUsername !== '' && $mockEmail !== '') {
            $query->where(function ($builder) use ($mockUsername, $mockEmail): void {
                $builder->where('username', $mockUsername)
                    ->orWhere('email', $mockEmail);
            });
        } elseif ($mockUsername !== '') {
            $query->where('username', $mockUsername);
        } else {
            $query->where('email', $mockEmail);
        }

        return (int) ($query->value('id') ?? DB::table('ipa_user')->min('id') ?? 0);
    }
}