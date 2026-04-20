<?php

declare(strict_types=1);

namespace App\Http\Validations;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Validator as ValidationValidator;

final class ProfileValidation
{
    public function updateAvatarValidation(Request $request): ValidationValidator
    {
        return Validator::make($request->all(), [
            'avatar' => ['required', 'image', 'mimes:jpeg,png,jpg,gif', 'max:5120'], // Max 5MB
        ]);
    }

    public function updateProfileValidation(Request $request, int|string $userId): ValidationValidator
    {
        return Validator::make($request->all(), [
            'fullName' => ['sometimes', 'string', 'max:255'],
            'email' => ['sometimes', 'email', 'max:255', 'unique:ipa_user,email,' . $userId],
            'phone' => ['nullable', 'string', 'max:20'],
        ]);
    }
}
