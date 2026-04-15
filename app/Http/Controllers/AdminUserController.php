<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\HttpStatus;
use App\Http\Validations\AdminUserValidation;
use App\Services\AdminUserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class AdminUserController extends Controller
{
    public function __construct(
        private readonly AdminUserService $adminUserService,
        private readonly AdminUserValidation $adminUserValidation,
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        $validator = $this->adminUserValidation->indexValidation($request);

        if ($validator->fails()) {
            return $this->validateError($validator->errors(), 'VALIDATION_FAILED', HttpStatus::VALIDATION_ERROR);
        }

        $result = $this->adminUserService->getAll($request);

        if (! $result['success']) {
            return $this->errorResponse($result['message'], 'FETCH_FAILED', HttpStatus::BAD_REQUEST);
        }

        return $this->successResponse($result['data'], $result['message'], HttpStatus::OK, $result['data']['meta'] ?? null);
    }

    public function show(string $userId): JsonResponse
    {
        $validator = $this->adminUserValidation->getByIdValidation($userId);

        if ($validator->fails()) {
            return $this->validateError($validator->errors(), 'VALIDATION_FAILED', HttpStatus::VALIDATION_ERROR);
        }

        $result = $this->adminUserService->getById($userId);

        if (! $result) {
            return $this->errorResponse(__('admin_users.messages.not_found'), 'USER_NOT_FOUND', HttpStatus::NOT_FOUND);
        }

        return $this->successResponse($result, __('admin_users.messages.fetch_success'));
    }

    public function store(Request $request): JsonResponse
    {
        $validator = $this->adminUserValidation->createValidation($request);

        if ($validator->fails()) {
            return $this->validateError($validator->errors(), 'VALIDATION_FAILED', HttpStatus::VALIDATION_ERROR);
        }

        $result = $this->adminUserService->create($request->only([
            'username',
            'fullName',
            'email',
            'phone',
            'unitId',
            'roleIds',
        ]));

        if (! $result) {
            return $this->errorResponse(__('admin_users.messages.create_error'), 'CREATE_FAILED', HttpStatus::BAD_REQUEST);
        }

        return $this->createdResponse($result, __('admin_users.messages.create_success'));
    }

    public function update(string $userId, Request $request): JsonResponse
    {
        $validator = $this->adminUserValidation->updateValidation($userId, $request);

        if ($validator->fails()) {
            return $this->validateError($validator->errors(), 'VALIDATION_FAILED', HttpStatus::VALIDATION_ERROR);
        }

        $result = $this->adminUserService->update($userId, $request->only([
            'fullName',
            'phone',
            'unitId',
            'roleIds',
            'status',
            'email',
            'username',
        ]));

        if (! $result) {
            return $this->errorResponse(__('admin_users.messages.update_error'), 'UPDATE_FAILED', HttpStatus::BAD_REQUEST);
        }

        return $this->successResponse($result, __('admin_users.messages.update_success'));
    }

    public function lock(string $userId, Request $request): JsonResponse
    {
        $validator = $this->adminUserValidation->lockValidation($request);

        if ($validator->fails()) {
            return $this->validateError($validator->errors(), 'VALIDATION_FAILED', HttpStatus::VALIDATION_ERROR);
        }

        $result = $this->adminUserService->lock($userId, (bool) $request->boolean('locked'));

        if (! $result) {
            return $this->errorResponse(__('admin_users.messages.lock_error'), 'LOCK_FAILED', HttpStatus::BAD_REQUEST);
        }

        return $this->successResponse($result, __('admin_users.messages.lock_success'));
    }
}