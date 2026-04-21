<?php

declare(strict_types=1);

namespace App\Http\Controllers\Task;

use App\Enums\HttpStatus;
use App\Http\Controllers\Controller;
use App\Http\Validations\TaskValidation;
use App\Services\TaskService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Class TaskController
 *
 * Manages system tasks and assignments, including CRUD operations,
 * commenting, and file attachments for operational workflows.
 *
 * @package App\Http\Controllers\Task
 */
final class TaskController extends Controller
{
    /**
     * TaskController constructor.
     *
     * @param TaskService $taskService
     * @param TaskValidation $taskValidation
     */
    public function __construct(
        private TaskService $taskService,
        private TaskValidation $taskValidation,
    ) {
    }

    /**
     * List tasks with filtering and pagination.
     *
     * @param Request $request
     * @return JsonResponse
     */
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

    /**
     * Retrieve details for a specific task.
     *
     * @param int $id Task ID.
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        $result = $this->taskService->getById($id);

        if (!$result['success']) {
            return $this->errorResponse($result['message'], 'NOT_FOUND', HttpStatus::NOT_FOUND);
        }

        return $this->successResponse($result['data'], $result['message']);
    }

    /**
     * Create a new task and assign it to a user.
     *
     * @param Request $request
     * @return JsonResponse
     */
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

    /**
     * Update an existing task's information or status.
     *
     * @param Request $request
     * @param int $id Task ID.
     * @return JsonResponse
     */
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

    /**
     * Delete a task from the system.
     *
     * @param int $id Task ID.
     * @return JsonResponse
     */
    public function destroy(int $id): JsonResponse
    {
        $result = $this->taskService->delete($id);

        if (!$result['success']) {
            return $this->errorResponse($result['message'], 'DELETE_FAILED', HttpStatus::BAD_REQUEST);
        }

        return $this->successResponse(null, $result['message']);
    }

    /**
     * Retrieve all comments associated with a task.
     *
     * @param int $taskId
     * @return JsonResponse
     */
    public function getComments(int $taskId): JsonResponse
    {
        $result = $this->taskService->getComments($taskId);
        return response()->json($result);
    }

    /**
     * Add a new comment to a task.
     *
     * @param Request $request
     * @param int $taskId
     * @return JsonResponse
     */
    public function addComment(Request $request, int $taskId): JsonResponse
    {
        $request->validate(['content' => 'required|string']);
        $result = $this->taskService->addComment($taskId, $request->only('content'));
        return response()->json($result);
    }

    /**
     * List all file attachments for a task.
     *
     * @param int $taskId
     * @return JsonResponse
     */
    public function getAttachments(int $taskId): JsonResponse
    {
        $result = $this->taskService->getAttachments($taskId);
        return response()->json($result);
    }

    /**
     * Upload a new attachment to a task.
     *
     * @param Request $request
     * @param int $taskId
     * @return JsonResponse
     */
    public function uploadAttachment(Request $request, int $taskId): JsonResponse
    {
        $request->validate([
            'file' => 'required|file|max:10240|mimes:pdf,doc,docx,xls,xlsx,png,jpg,jpeg,zip',
        ]);

        $result = $this->taskService->addAttachment($taskId, $request->file('file'));
        return response()->json($result);
    }

    /**
     * Remove an attachment from a task.
     *
     * @param int $taskId
     * @param int $attachmentId
     * @return JsonResponse
     */
    public function deleteAttachment(int $taskId, int $attachmentId): JsonResponse
    {
        $result = $this->taskService->deleteAttachment($taskId, $attachmentId);
        return response()->json($result);
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
