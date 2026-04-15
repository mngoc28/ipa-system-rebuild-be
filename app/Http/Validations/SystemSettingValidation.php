<?php

declare(strict_types=1);

namespace App\Http\Validations;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Validator as ValidationValidator;

final class SystemSettingValidation
{
    public function indexValidation(Request $request): ValidationValidator
    {
        return Validator::make($request->all(), [
            'group' => ['nullable', 'string', 'max:255'],
        ]);
    }

    public function updateValidation(Request $request): ValidationValidator
    {
        return Validator::make($request->all(), [
            'items' => ['required', 'array', 'min:1'],
            'items.*.key' => ['required', 'string', 'max:100'],
            'items.*.value' => ['nullable', 'string', 'max:5000'],
        ]);
    }
}