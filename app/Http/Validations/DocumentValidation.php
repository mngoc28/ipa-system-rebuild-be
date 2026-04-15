<?php

declare(strict_types=1);

namespace App\Http\Validations;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

final class DocumentValidation
{
    public function foldersIndexValidation(Request $request)
    {
        return Validator::make($request->all(), [
            'parentId' => ['nullable', 'integer', 'exists:ipa_folder,id'],
            'scopeType' => ['nullable', 'string', 'max:50'],
        ]);
    }

    public function foldersStoreValidation(Request $request)
    {
        return Validator::make($request->all(), [
            'parentFolderId' => ['nullable', 'integer', 'exists:ipa_folder,id'],
            'folderName' => ['required', 'string', 'max:255'],
            'scopeType' => ['required', 'string', 'max:50'],
        ]);
    }

    public function filesIndexValidation(Request $request)
    {
        return Validator::make($request->all(), [
            'folderId' => ['nullable', 'integer', 'exists:ipa_folder,id'],
        ]);
    }

    public function fileIdValidation(string $id)
    {
        return Validator::make(['id' => $id], [
            'id' => ['required', 'string', 'exists:ipa_file,id'],
        ]);
    }

    public function uploadValidation(Request $request)
    {
        return Validator::make($request->all(), [
            'fileName' => ['required', 'string', 'max:255'],
            'sizeBytes' => ['required', 'integer', 'min:0'],
            'folderId' => ['nullable', 'integer', 'exists:ipa_folder,id'],
            'delegationId' => ['nullable', 'integer', 'exists:ipa_delegation,id'],
            'minutesId' => ['nullable', 'integer', 'exists:ipa_minutes,id'],
            'taskId' => ['nullable', 'integer', 'exists:ipa_task,id'],
            'file' => ['nullable', 'file'],
        ]);
    }

    public function patchValidation(Request $request)
    {
        return Validator::make($request->all(), [
            'fileName' => ['required', 'string', 'max:255'],
        ]);
    }

    public function shareValidation(Request $request)
    {
        return Validator::make($request->all(), [
            'permissionLevel' => ['required', 'string', 'in:VIEW,EDIT'],
            'sharedWithUserId' => ['nullable', 'string', 'max:255', 'required_without:sharedWithRoleId'],
            'sharedWithRoleId' => ['nullable', 'string', 'max:255', 'required_without:sharedWithUserId'],
            'expiresAt' => ['nullable', 'date'],
        ]);
    }
}