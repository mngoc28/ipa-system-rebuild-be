<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\HttpStatus;
use App\Http\Validations\NotificationValidation;
use App\Services\NotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Class NotificationController
 *
 * Manages user notifications, including retrieval, marking as read,
 * deleting read notifications, and counting unread alerts.
 *
 * @package App\Http\Controllers
 */
final class NotificationController extends Controller
{
    /**
     * NotificationController constructor.
     *
     * @param NotificationService $notificationService
     * @param NotificationValidation $notificationValidation
     */
    public function __construct(
        private NotificationService $notificationService,
        private NotificationValidation $notificationValidation,
    ) {
    }

    /**
     * List user notifications with filtering and pagination.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $validator = $this->notificationValidation->indexValidation($request);

        if ($validator->fails()) {
            return $this->validateError($validator->errors(), 'VALIDATION_FAILED', HttpStatus::VALIDATION_ERROR);
        }

        $userId = $this->resolveUserId($request);

        if ($userId <= 0) {
            return $this->errorResponse(__('auth.unauthenticated'), 'UNAUTHORIZED', HttpStatus::UNAUTHORIZED);
        }

        $result = $this->notificationService->getAll($request, $userId);

        if (! $result['success']) {
            return $this->errorResponse($result['message'], 'FETCH_FAILED', HttpStatus::BAD_REQUEST);
        }

        return $this->successResponse($result['data'], $result['message'], HttpStatus::OK, $result['data']['meta'] ?? null);
    }

    /**
     * Mark a specific notification as read.
     *
     * @param Request $request
     * @param string $id Notification ID.
     * @return JsonResponse
     */
    public function read(Request $request, string $id): JsonResponse
    {
        $userId = $this->resolveUserId($request);

        if ($userId <= 0) {
            return $this->errorResponse(__('auth.unauthenticated'), 'UNAUTHORIZED', HttpStatus::UNAUTHORIZED);
        }

        $result = $this->notificationService->read($userId, $id);

        if (! $result['success']) {
            return $this->errorResponse($result['message'], 'READ_FAILED', HttpStatus::BAD_REQUEST);
        }

        return $this->successResponse($result['data'], $result['message']);
    }

    /**
     * Mark all notifications as read for the current user.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function readAll(Request $request): JsonResponse
    {
        $userId = $this->resolveUserId($request);

        if ($userId <= 0) {
            return $this->errorResponse(__('auth.unauthenticated'), 'UNAUTHORIZED', HttpStatus::UNAUTHORIZED);
        }

        $result = $this->notificationService->readAll($userId);

        if (! $result['success']) {
            return $this->errorResponse($result['message'], 'READ_ALL_FAILED', HttpStatus::BAD_REQUEST);
        }

        return $this->successResponse($result['data'], $result['message']);
    }

    /**
     * Delete all read notifications for the current user.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function deleteRead(Request $request): JsonResponse
    {
        $userId = $this->resolveUserId($request);

        if ($userId <= 0) {
            return $this->errorResponse(__('auth.unauthenticated'), 'UNAUTHORIZED', HttpStatus::UNAUTHORIZED);
        }

        $result = $this->notificationService->deleteRead($userId);

        if (! $result['success']) {
            return $this->errorResponse($result['message'], 'DELETE_READ_FAILED', HttpStatus::BAD_REQUEST);
        }

        return $this->successResponse($result['data'], $result['message']);
    }

    /**
     * Get the total count of unread notifications for the current user.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function count(Request $request): JsonResponse
    {
        $userId = $this->resolveUserId($request);

        if ($userId <= 0) {
            return $this->errorResponse(__('auth.unauthenticated'), 'UNAUTHORIZED', HttpStatus::UNAUTHORIZED);
        }

        $result = $this->notificationService->getUnreadCount($userId);

        if (! $result['success']) {
            return $this->errorResponse($result['message'], 'COUNT_FAILED', HttpStatus::BAD_REQUEST);
        }

        return $this->successResponse($result['data'], $result['message']);
    }

    /**
     * Resolves the user identity for the current request.
     * Supports authenticated users and mock overrides for development.
     *
     * @param Request $request
     * @return int User ID.
     */
    private function resolveUserId(Request $request): int
    {
        $authenticatedUserId = (int) ($request->user()?->id ?? 0);

        if ($authenticatedUserId > 0) {
            return $authenticatedUserId;
        }

        if (! app()->environment(['local', 'development', 'testing'])) {
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
