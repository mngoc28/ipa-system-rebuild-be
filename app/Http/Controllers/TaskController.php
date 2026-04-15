<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\HttpStatus;
use App\Http\Validations\TaskValidation;
use App\Services\TaskService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

final class TaskController extends Controller
{
    public function __construct(
        private readonly TaskService $taskService,
        private readonly TaskValidation $taskValidation,
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        $validator = $this->taskValidation->indexValidation($request);

        if ($validator->fails()) {
            return $this->validateError($validator->errors(), 'VALIDATION_FAILED', HttpStatus::VALIDATION_ERROR);
        }

        $result = $this->taskService->getAll($request);

        if (!$result['success']) {
            return $this->errorResponse($result['message'], 'FETCH_FAILED', HttpStatus::BAD_REQUEST);
        }

        return $this->successResponse($result['data'], $result['message'], HttpStatus::OK, $result['data']['meta']);
    }

    public function show(int $id): JsonResponse
    {
        $result = $this->taskService->getById($id);

        if (!$result['success']) {
            return $this->errorResponse($result['message'], 'NOT_FOUND', HttpStatus::NOT_FOUND);
        }

        return $this->successResponse($result['data'], $result['message']);
    }

    public function store(Request $request): JsonResponse
    {
        $validator = $this->taskValidation->storeValidation($request);

        if ($validator->fails()) {
            return $this->validateError($validator->errors(), 'VALIDATION_FAILED', HttpStatus::VALIDATION_ERROR);
        }

        $userId = $this->resolveUserId($request);
        if ($userId <= 0) {
            return $this->errorResponse(__('auth.unauthenticated'), 'UNAUTHORIZED', HttpStatus::UNAUTHORIZED);
        }

        $data = array_merge($request->all(), ['created_by' => $userId]);
        $result = $this->taskService->create($data);

        if (!$result['success']) {
            return $this->errorResponse($result['message'], 'CREATE_FAILED', HttpStatus::BAD_REQUEST);
        }

        return $this->successResponse($result['data'], $result['message'], HttpStatus::CREATED);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $validator = $this->taskValidation->updateValidation($request);

        if ($validator->fails()) {
            return $this->validateError($validator->errors(), 'VALIDATION_FAILED', HttpStatus::VALIDATION_ERROR);
        }

        $result = $this->taskService->update($id, $request->all());

        if (!$result['success']) {
            return $this->errorResponse($result['message'], 'UPDATE_FAILED', HttpStatus::BAD_REQUEST);
        }

        return $this->successResponse($result['data'], $result['message']);
    }

    public function destroy(int $id): JsonResponse
    {
        $result = $this->taskService->delete($id);

        if (!$result['success']) {
            return $this->errorResponse($result['message'], 'DELETE_FAILED', HttpStatus::BAD_REQUEST);
        }

        return $this->successResponse(null, $result['message']);
    }

    private function resolveUserId(Request $request): int
    {
        $authenticatedUserId = (int) ($request->user()?->id ?? 0);

        if ($authenticatedUserId > 0) {
            return $authenticatedUserId;
        }

        if (!app()->environment(['local', 'development', 'testing'])) {
            return 0;
        }

        $mockUsername = trim((string) $request->header('X-Mock-Username', ''));
        $mockEmail = trim((string) $request->header('X-Mock-Email', ''));

        if ($mockUsername === '' && $mockEmail === '') {
            return 0;
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

        return (int) ($query->value('id') ?? 0);
    }
}
