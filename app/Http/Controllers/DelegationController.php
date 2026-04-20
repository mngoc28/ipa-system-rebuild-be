<?php

namespace App\Http\Controllers;

use App\Http\Validations\DelegationValidation;
use App\Services\DelegationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Class DelegationController
 *
 * Manages the investment delegation lifecycle, including CRUD operations,
 * commenting (with mentions), and activity tracking for incoming/outgoing delegations.
 *
 * @package App\Http\Controllers
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
        $delegations = $this->service->listDelegations($request);

        return response()->json([
            'success' => true,
            'data' => [
                'items' => $delegations->items(),
                'pagination' => [
                    'current_page' => $delegations->currentPage(),
                    'per_page' => $delegations->perPage(),
                    'total' => $delegations->total(),
                    'last_page' => $delegations->lastPage(),
                ]
            ]
        ]);
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
            return response()->json([
                'success' => false,
                'error' => [
                    'message' => 'Đoàn công tác không tồn tại.'
                ]
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $delegation
        ]);
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
            return response()->json([
                'success' => false,
                'error' => [
                    'message' => 'Dữ liệu không hợp lệ.',
                    'details' => $validator->errors()
                ]
            ], 422);
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

            return response()->json([
                'success' => true,
                'data' => $delegation,
                'message' => 'Tạo hồ sơ đoàn công tác thành công.'
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => [
                    'message' => 'Lỗi khi tạo hồ sơ đoàn công tác.',
                    'system_error' => $e->getMessage()
                ]
            ], 500);
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
            return response()->json([
                'success' => false,
                'error' => [
                    'message' => 'Dữ liệu không hợp lệ.',
                    'details' => $validator->errors()
                ]
            ], 422);
        }

        try {
            $delegation = $this->service->updateDelegation((int)$id, $validator->validated());

            if (!$delegation) {
                return response()->json([
                    'success' => false,
                    'error' => ['message' => 'Đoàn công tác không tồn tại.']
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $delegation,
                'message' => 'Cập nhật hồ sơ đoàn thành công.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => ['message' => 'Lỗi khi cập nhật hồ sơ đoàn.']
            ], 500);
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
                return response()->json([
                    'success' => false,
                    'error' => ['message' => 'Đoàn công tác không tồn tại.']
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Xóa hồ sơ đoàn thành công.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => ['message' => 'Lỗi khi xóa hồ sơ đoàn.']
            ], 500);
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
            return response()->json([
                'success' => false,
                'error' => ['message' => $result['message']]
            ], 404);
        }

        return response()->json($result);
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
            return response()->json([
                'success' => false,
                'error' => ['message' => $result['message']]
            ], 400); // Bad Request or Not Found
        }

        return response()->json($result, 201);
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
            return response()->json([
                'success' => false,
                'error' => ['message' => $result['message']]
            ], 400);
        }

        return response()->json($result);
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
            return response()->json([
                'success' => false,
                'error' => ['message' => $result['message']]
            ], 400);
        }

        return response()->json($result);
    }

    /**
     * Resolve user ID from request (Mocking auth for development)
     */
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
