<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\HttpStatus;
use App\Http\Validations\AdminUserValidation;
use App\Http\Validations\ProfileValidation;
use App\Services\AdminUserService;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

final class AdminUserController extends Controller
{
    public function __construct(
        private AdminUserService $adminUserService,
        private AdminUserValidation $adminUserValidation,
        private ProfileValidation $profileValidation,
    ) {
    }

    // ... (index, show, store, update, lock, destroy methods omitted for space in target content, but I'll replace everything from imports onwards)

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
            return $this->errorResponse(__('admin_users.messages.not_found'), 'CREATE_FAILED', HttpStatus::BAD_REQUEST);
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

    public function destroy(string $userId): JsonResponse
    {
        $validator = $this->adminUserValidation->getByIdValidation($userId);

        if ($validator->fails()) {
            return $this->validateError($validator->errors(), 'VALIDATION_FAILED', HttpStatus::VALIDATION_ERROR);
        }

        $deleted = $this->adminUserService->delete($userId);

        if (! $deleted) {
            return $this->errorResponse(__('user.delete_failed'), 'DELETE_FAILED', HttpStatus::BAD_REQUEST);
        }

        return $this->successResponse([
            'deleted' => true,
        ], __('user.delete_success'));
    }

    public function updateAvatar(string $userId, Request $request): JsonResponse
    {
        $v1 = $this->adminUserValidation->getByIdValidation($userId);
        if ($v1->fails()) {
            return $this->validateError($v1->errors(), 'VALIDATION_FAILED', HttpStatus::VALIDATION_ERROR);
        }

        $v2 = $this->profileValidation->updateAvatarValidation($request);
        if ($v2->fails()) {
            return $this->validateError($v2->errors(), 'VALIDATION_FAILED', HttpStatus::VALIDATION_ERROR);
        }

        $userRecord = $this->adminUserService->getById($userId);
        if (!$userRecord) {
            return $this->errorResponse(__('admin_users.messages.not_found'), 'USER_NOT_FOUND', HttpStatus::NOT_FOUND);
        }

        $file = $request->file('avatar');

        // Upload to Cloudinary
        $uploadResult = Cloudinary::upload($file->getRealPath(), [
            'folder' => 'avatars',
            'public_id' => $userId . '_' . Str::random(5)
        ]);

        $path = $uploadResult->getSecurePath();

        $result = $this->adminUserService->updateAvatar($userId, $path);

        if (!$result) {
             return $this->errorResponse(__('profile.messages.avatar_update_error'), 'UPDATE_FAILED', HttpStatus::BAD_REQUEST);
        }

        $result['avatar_url'] = $path;

        return $this->successResponse($result, __('profile.messages.avatar_update_success'));
    }
}
