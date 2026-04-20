<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\HttpStatus;
use App\Http\Validations\ProfileValidation;
use App\Models\AdminUser;
use App\Services\AdminUserService;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

final class ProfileController extends Controller
{
    public function __construct(
        private ProfileValidation $profileValidation,
        private AdminUserService $adminUserService
    ) {
    }

    public function updateAvatar(Request $request): JsonResponse
    {
        $validator = $this->profileValidation->updateAvatarValidation($request);

        if ($validator->fails()) {
            return $this->validateError($validator->errors(), 'VALIDATION_FAILED', HttpStatus::VALIDATION_ERROR);
        }

        /** @var AdminUser $user */
        $user = $request->user();

        if (!$user) {
            return $this->errorResponse(__('auth.unauthenticated'), 'UNAUTHORIZED', HttpStatus::UNAUTHORIZED);
        }

        $userId = (string) $user->id;
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

    public function update(Request $request): JsonResponse
    {
        /** @var AdminUser $user */
        $user = $request->user();

        if (!$user) {
            return $this->errorResponse(__('auth.unauthenticated'), 'UNAUTHORIZED', HttpStatus::UNAUTHORIZED);
        }

        $userId = (string) $user->id;

        $validator = $this->profileValidation->updateProfileValidation($request, $userId);

        if ($validator->fails()) {
            return $this->validateError($validator->errors(), 'VALIDATION_FAILED', HttpStatus::VALIDATION_ERROR);
        }

        $attributes = $validator->validated();

        $result = $this->adminUserService->update($userId, $attributes);

        if (!$result) {
             return $this->errorResponse('Không thể cập nhật hồ sơ', 'UPDATE_FAILED', HttpStatus::BAD_REQUEST);
        }

        return $this->successResponse($result, 'Đã cập nhật hồ sơ thành công');
    }
}
