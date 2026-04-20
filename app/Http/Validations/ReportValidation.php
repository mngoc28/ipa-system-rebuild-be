<?php

declare(strict_types=1);

namespace App\Http\Validations;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Validator as ValidationValidator;

final class ReportValidation
{
    public function definitionsValidation(Request $request): ValidationValidator
    {
        return Validator::make($request->all(), []);
    }

    public function createRunValidation(Request $request): ValidationValidator
    {
        return Validator::make($request->all(), [
            'report_code' => ['required', 'string', 'max:255'],
            'params' => ['nullable', 'array'],
        ]);
    }

    public function showRunValidation(string $runId): ValidationValidator
    {
        return Validator::make(['runId' => $runId], [
            'runId' => ['required', 'string', 'max:255'],
        ]);
    }
}
