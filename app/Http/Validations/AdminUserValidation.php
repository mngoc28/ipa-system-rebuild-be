<?php

declare(strict_types=1);

namespace App\Http\Validations;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator as ValidationValidator;

final class AdminUserValidation
{
    public function indexValidation(Request $request): ValidationValidator
    {
        return Validator::make($request->all(), [
            'keyword' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', Rule::in(['active', 'inactive'])],
            'roleId' => ['nullable', 'string', 'max:255'],
            'unitId' => ['nullable', 'string', 'max:255'],
            'page' => ['nullable', 'integer', 'min:1'],
            'pageSize' => ['nullable', 'integer', 'min:1', 'max:100'],
            'sortField' => ['nullable', 'string', 'max:50'],
            'sortDirection' => ['nullable', Rule::in(['asc', 'desc'])],
        ]);
    }

    public function getByIdValidation(string $userId): ValidationValidator
    {
        return Validator::make([
            'userId' => $userId,
        ], [
            'userId' => ['required', 'integer', 'exists:ipa_user,id'],
        ]);
    }

    public function createValidation(Request $request): ValidationValidator
    {
        return Validator::make($request->all(), [
            'username' => ['required', 'string', 'max:100', 'unique:ipa_user,username'],
            'fullName' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:ipa_user,email'],
            'phone' => ['nullable', 'string', 'max:30'],
            'unitId' => ['nullable', 'string', 'max:255'],
            'roleIds' => ['nullable', 'array'],
            'roleIds.*' => ['string', 'max:255'],
        ]);
    }

    public function updateValidation(string $userId, Request $request): ValidationValidator
    {
        return Validator::make($request->all(), [
            'fullName' => ['sometimes', 'string', 'max:255'],
            'phone' => ['sometimes', 'nullable', 'string', 'max:30'],
            'unitId' => ['sometimes', 'nullable', 'string', 'max:255'],
            'roleIds' => ['sometimes', 'array'],
            'roleIds.*' => ['string', 'max:255'],
            'status' => ['sometimes', Rule::in(['active', 'inactive'])],
            'email' => ['sometimes', 'email', 'max:255', Rule::unique('ipa_user', 'email')->ignore($userId)],
            'username' => ['sometimes', 'string', 'max:100', Rule::unique('ipa_user', 'username')->ignore($userId)],
        ]);
    }

    public function lockValidation(Request $request): ValidationValidator
    {
        return Validator::make($request->all(), [
            'locked' => ['required', 'boolean'],
        ]);
    }
}