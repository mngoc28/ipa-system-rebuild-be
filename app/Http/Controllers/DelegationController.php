<?php

namespace App\Http\Controllers;

use App\Http\Validations\DelegationValidation;
use App\Services\DelegationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class DelegationController extends Controller
{
    protected $service;

    public function __construct(DelegationService $service)
    {
        $this->service = $service;
    }

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
     * Resolve user ID from request (Mocking auth for development)
     */
    private function resolveUserId(Request $request)
    {
        return $request->header('X-Mock-User-Id', 1);
    }
}
