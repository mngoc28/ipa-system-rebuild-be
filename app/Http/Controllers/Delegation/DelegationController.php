<?php

namespace App\Http\Controllers\Delegation;

use App\Http\Controllers\Controller;
use App\Http\Validations\DelegationValidation;
use App\Services\DelegationService;
use App\Enums\HttpStatus;
use Illuminate\Http\Request;

/**
 * Class DelegationController
 *
 * Manages the investment delegation lifecycle, including CRUD operations,
 * commenting (with mentions), and activity tracking for incoming/outgoing delegations.
 *
 * @package App\Http\Controllers\Delegation
 */
class DelegationController extends Controller
{
    protected $service;

    /**
     * DelegationController constructor.
     *
     * @param DelegationService $service
     */
    public function __construct(DelegationService $service)
    {
        $this->service = $service;
    }

    /**
     * List delegations with filtering and pagination.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $result = $this->service->listDelegations($request);

        return $this->successResponse($result, 'Lấy danh sách đoàn công tác thành công.');
    }

    /**
     * Retrieve details for a specific delegation.
     *
     * @param int|string $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $delegation = $this->service->getDelegation((int)$id);

        if (!$delegation) {
            return $this->errorResponse(
                'Đoàn công tác không tồn tại.',
                'NOT_FOUND',
                HttpStatus::NOT_FOUND
            );
        }

        return $this->successResponse($delegation->toArray(), 'Lấy thông tin đoàn công tác thành công.');
    }

    /**
     * Create a new delegation record.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $validator = DelegationValidation::validateStore($request);

        if ($validator->fails()) {
            return $this->validateError($validator->errors());
        }

        try {
            // Generate code if missing
            $data = $validator->validated();
            if (empty($data['code'])) {
                $data['code'] = 'DEL-' . time();
            }

            // Mock owner
            if (empty($data['owner_user_id'])) {
                $data['owner_user_id'] = $this->resolveUserId($request);
            }

            $delegation = $this->service->createDelegation($data);

            return $this->createdResponse(
                $delegation->toArray(),
                'Tạo hồ sơ đoàn công tác thành công.'
            );
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Lỗi khi tạo hồ sơ đoàn công tác.',
                'CORE_ERROR',
                HttpStatus::INTERNAL_SERVER_ERROR,
                $e->getMessage()
            );
        }
    }

    /**
     * Update an existing delegation's information.
     *
     * @param Request $request
     * @param int|string $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        $validator = DelegationValidation::validateUpdate($request);

        if ($validator->fails()) {
            return $this->validateError($validator->errors());
        }

        try {
            $delegation = $this->service->updateDelegation((int)$id, $validator->validated());

            if (!$delegation) {
                return $this->errorResponse(
                    'Đoàn công tác không tồn tại.',
                    'NOT_FOUND',
                    HttpStatus::NOT_FOUND
                );
            }

            return $this->successResponse(
                $delegation->toArray(),
                'Cập nhật hồ sơ đoàn thành công.'
            );
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Lỗi khi cập nhật hồ sơ đoàn.',
                'CORE_ERROR',
                HttpStatus::INTERNAL_SERVER_ERROR,
                $e->getMessage()
            );
        }
    }

    /**
     * Soft-delete a delegation record.
     *
     * @param int|string $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        try {
            $deleted = $this->service->deleteDelegation((int)$id);

            if (!$deleted) {
                return $this->errorResponse(
                    'Đoàn công tác không tồn tại.',
                    'NOT_FOUND',
                    HttpStatus::NOT_FOUND
                );
            }

            return $this->successResponse(
                null,
                'Xóa hồ sơ đoàn thành công.'
            );
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Lỗi khi xóa hồ sơ đoàn.',
                'CORE_ERROR',
                HttpStatus::INTERNAL_SERVER_ERROR,
                $e->getMessage()
            );
        }
    }

    /**
     * List all comments associated with a delegation.
     *
     * @param int|string $id Delegation ID.
     * @return \Illuminate\Http\JsonResponse
     */
    public function listComments($id)
    {
        $result = $this->service->listComments((int)$id);

        if (!$result['success']) {
            return $this->errorResponse(
                $result['message'],
                'NOT_FOUND',
                HttpStatus::NOT_FOUND
            );
        }

        return $this->successResponse($result['data'] ?? $result, 'Lấy danh sách bình luận thành công.');
    }

    /**
     * Add a new comment to a delegation, supporting @mentions.
     *
     * @param Request $request
     * @param int|string $id Delegation ID.
     * @return \Illuminate\Http\JsonResponse
     */
    public function addComment(Request $request, $id)
    {
        $request->validate([
            'content' => 'required|string',
        ]);

        $commenterId = $this->resolveUserId($request);

        $result = $this->service->addComment((int)$id, $request->input('content'), $commenterId);

        if (!$result['success']) {
            return $this->errorResponse(
                $result['message'],
                'BAD_REQUEST',
                HttpStatus::BAD_REQUEST
            );
        }

        return $this->createdResponse(
            $result['data'] ?? $result,
            'Thêm bình luận thành công.'
        );
    }

    /**
     * Update an existing comment's content.
     *
     * @param Request $request
     * @param int|string $id Delegation ID.
     * @param int|string $commentId Comment ID.
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateComment(Request $request, $id, $commentId)
    {
        $request->validate([
            'content' => 'required|string',
        ]);

        $userId = $this->resolveUserId($request);
        $result = $this->service->updateComment((int)$commentId, $request->input('content'), $userId);

        if (!$result['success']) {
            return $this->errorResponse(
                $result['message'],
                'BAD_REQUEST',
                HttpStatus::BAD_REQUEST
            );
        }

        return $this->successResponse($result['data'] ?? $result, 'Cập nhật bình luận thành công.');
    }

    /**
     * Remove a comment from a delegation.
     *
     * @param Request $request
     * @param int|string $id Delegation ID.
     * @param int|string $commentId Comment ID.
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteComment(Request $request, $id, $commentId)
    {
        $userId = $this->resolveUserId($request);
        $result = $this->service->deleteComment((int)$commentId, $userId);

        if (!$result['success']) {
            return $this->errorResponse(
                $result['message'],
                'BAD_REQUEST',
                HttpStatus::BAD_REQUEST
            );
        }

        return $this->successResponse(null, 'Xóa bình luận thành công.');
    }

    /**
     * Resolve user ID from request (Mocking auth for development).
     *
     * @param Request $request
     * @return int|string User ID.
     */
    private function resolveUserId(Request $request): int|string
    {
        return auth()->id() ?: 1;
    }
}
