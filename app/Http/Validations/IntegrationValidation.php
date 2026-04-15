<?php

declare(strict_types=1);

namespace App\Http\Validations;

use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator as ValidationValidator;

final class IntegrationValidation
{
    public function testValidation(string $provider): ValidationValidator
    {
        return Validator::make([
            'provider' => $provider,
        ], [
            'provider' => ['required', Rule::in(['zalo'])],
        ]);
    }
}