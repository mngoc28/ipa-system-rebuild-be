<?php

declare(strict_types=1);

namespace App\Http\Validations;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator as ValidationValidator;

final class AuditLogValidation
{
    public function indexValidation(Request $request): ValidationValidator
    {
        return Validator::make($request->all(), [
            'keyword' => ['nullable', 'string', 'max:255'],
            'type' => ['nullable', Rule::in(['info', 'success', 'warning', 'system'])],
            'actorUserId' => ['nullable', 'integer', 'min:1'],
            'action' => ['nullable', 'string', 'max:255'],
            'resourceType' => ['nullable', 'string', 'max:255'],
            'page' => ['nullable', 'integer', 'min:1'],
            'pageSize' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);
    }
}
