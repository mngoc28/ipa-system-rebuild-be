<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\HttpStatus;
use App\Http\Validations\DocumentValidation;
use App\Services\DocumentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

final class DocumentController extends Controller
{
    public function __construct(
        private DocumentService $documentService,
        private DocumentValidation $documentValidation,
    ) {
    }

    public function foldersIndex(Request $request): JsonResponse
    {
        $validator = $this->documentValidation->foldersIndexValidation($request);

        if ($validator->fails()) {
            return $this->validateError($validator->errors(), 'VALIDATION_FAILED', HttpStatus::VALIDATION_ERROR);
        }

        $result = $this->documentService->getFolders($request);

        if (! $result['success']) {
            return $this->errorResponse($result['message'], 'FETCH_FAILED', HttpStatus::BAD_REQUEST);
        }

        return $this->successResponse($result['data'], $result['message']);
    }

    public function foldersStore(Request $request): JsonResponse
    {
        $validator = $this->documentValidation->foldersStoreValidation($request);

        if ($validator->fails()) {
            return $this->validateError($validator->errors(), 'VALIDATION_FAILED', HttpStatus::VALIDATION_ERROR);
        }

        $scopeType = (string) $request->input('scopeType', 'GENERAL');
        $parentFolderId = $request->input('parentFolderId');

        $duplicateExists = DB::table('ipa_folder')
            ->when(
                $parentFolderId !== null,
                fn ($query) => $query->where('parent_folder_id', (int) $parentFolderId),
                fn ($query) => $query->whereNull('parent_folder_id')
            )
            ->where('folder_name', trim((string) $request->input('folderName')))
            ->where('scope_type', $this->resolveScopeType($scopeType))
            ->exists();

        if ($duplicateExists) {
            return $this->errorResponse(__('documents.messages.folder_duplicate'), 'FOLDER_DUPLICATE', HttpStatus::CONFLICT);
        }

        $ownerUserId = $this->resolveUserId($request);
        if ($ownerUserId <= 0) {
            return $this->errorResponse(__('auth.unauthenticated'), 'UNAUTHORIZED', HttpStatus::UNAUTHORIZED);
        }

        $result = $this->documentService->createFolder([
            'parent_folder_id' => $parentFolderId,
            'folder_name' => trim((string) $request->input('folderName')),
            'scope_type' => $scopeType,
            'owner_user_id' => $ownerUserId,
        ]);

        if (! $result) {
            return $this->errorResponse(__('documents.messages.folder_create_error'), 'CREATE_FAILED', HttpStatus::BAD_REQUEST);
        }

        return $this->createdResponse($result, __('documents.messages.folder_create_success'));
    }

    public function filesIndex(Request $request): JsonResponse
    {
        $validator = $this->documentValidation->filesIndexValidation($request);

        if ($validator->fails()) {
            return $this->validateError($validator->errors(), 'VALIDATION_FAILED', HttpStatus::VALIDATION_ERROR);
        }

        $result = $this->documentService->getFiles($request);

        if (! $result['success']) {
            return $this->errorResponse($result['message'], 'FETCH_FAILED', HttpStatus::BAD_REQUEST);
        }

        return $this->successResponse($result['data'], $result['message']);
    }

    public function filesShow(string $id): JsonResponse
    {
        $validator = $this->documentValidation->fileIdValidation($id);

        if ($validator->fails()) {
            return $this->validateError($validator->errors(), 'VALIDATION_FAILED', HttpStatus::VALIDATION_ERROR);
        }

        $result = $this->documentService->getFileById($id);

        if (! $result) {
            return $this->errorResponse(__('documents.messages.file_not_found'), 'FILE_NOT_FOUND', HttpStatus::NOT_FOUND);
        }

        return $this->successResponse($result, __('documents.messages.fetch_success'));
    }

    public function filesUpload(Request $request): JsonResponse
    {
        $validator = $this->documentValidation->uploadValidation($request);

        if ($validator->fails()) {
            return $this->validateError($validator->errors(), 'VALIDATION_FAILED', HttpStatus::VALIDATION_ERROR);
        }

        $uploadedBy = $this->resolveUserId($request);
        if ($uploadedBy <= 0) {
            return $this->errorResponse(__('auth.unauthenticated'), 'UNAUTHORIZED', HttpStatus::UNAUTHORIZED);
        }

        $result = $this->documentService->uploadFile([
            'file_name' => (string) $request->input('fileName'),
            'size_bytes' => (int) $request->input('sizeBytes'),
            'folder_id' => $request->input('folderId'),
            'delegation_id' => $request->input('delegationId'),
            'minutes_id' => $request->input('minutesId'),
            'task_id' => $request->input('taskId'),
            'uploaded_by' => $uploadedBy,
        ], $request->file('file'));

        if (! $result) {
            return $this->errorResponse(__('documents.messages.file_upload_error'), 'UPLOAD_FAILED', HttpStatus::BAD_REQUEST);
        }

        return $this->createdResponse($result, __('documents.messages.file_upload_success'));
    }

    public function filesPatch(string $id, Request $request): JsonResponse
    {
        $validator = $this->documentValidation->fileIdValidation($id);

        if ($validator->fails()) {
            return $this->validateError($validator->errors(), 'VALIDATION_FAILED', HttpStatus::VALIDATION_ERROR);
        }

        $payloadValidator = $this->documentValidation->patchValidation($request);

        if ($payloadValidator->fails()) {
            return $this->validateError($payloadValidator->errors(), 'VALIDATION_FAILED', HttpStatus::VALIDATION_ERROR);
        }

        $result = $this->documentService->updateFile($id, ['file_name' => (string) $request->input('fileName')]);

        if (! $result) {
            return $this->errorResponse(__('documents.messages.file_not_found'), 'FILE_NOT_FOUND', HttpStatus::NOT_FOUND);
        }

        return $this->successResponse(['updated' => true, 'file' => $result], __('documents.messages.file_update_success'));
    }

    public function filesDownloadUrl(string $id): JsonResponse
    {
        $validator = $this->documentValidation->fileIdValidation($id);

        if ($validator->fails()) {
            return $this->validateError($validator->errors(), 'VALIDATION_FAILED', HttpStatus::VALIDATION_ERROR);
        }

        $result = $this->documentService->createDownloadUrl($id);

        if (! $result) {
            return $this->errorResponse(__('documents.messages.file_not_found'), 'FILE_NOT_FOUND', HttpStatus::NOT_FOUND);
        }

        return $this->createdResponse($result, __('documents.messages.download_url_success'));
    }

    public function filesShare(string $id, Request $request): JsonResponse
    {
        $validator = $this->documentValidation->fileIdValidation($id);

        if ($validator->fails()) {
            return $this->validateError($validator->errors(), 'VALIDATION_FAILED', HttpStatus::VALIDATION_ERROR);
        }

        $payloadValidator = $this->documentValidation->shareValidation($request);

        if ($payloadValidator->fails()) {
            return $this->validateError($payloadValidator->errors(), 'VALIDATION_FAILED', HttpStatus::VALIDATION_ERROR);
        }

        $result = $this->documentService->shareFile($id, [
            'permission_level' => $request->input('permissionLevel'),
            'shared_with_user_id' => $request->input('sharedWithUserId'),
            'shared_with_role_id' => $request->input('sharedWithRoleId'),
            'expires_at' => $request->input('expiresAt'),
        ]);

        if (! $result) {
            return $this->errorResponse(__('documents.messages.file_not_found'), 'FILE_NOT_FOUND', HttpStatus::NOT_FOUND);
        }

        return $this->createdResponse($result, __('documents.messages.share_success'));
    }

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
            return (int) (DB::table('ipa_user')->value('id') ?? 0);
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

    private function resolveScopeType(string $scopeType): int
    {
        return match (strtoupper(trim($scopeType))) {
            'DELEGATION' => 1,
            'MINUTES' => 2,
            'TASK' => 3,
            'EVENT' => 4,
            'PARTNER' => 5,
            default => 0,
        };
    }
}
