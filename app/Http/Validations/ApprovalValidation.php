<?php

declare(strict_types=1);

namespace App\Http\Validations;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Validator as ValidationValidator;

final class ApprovalValidation
{
    public function indexValidation(Request $request): ValidationValidator
    {
        return Validator::make($request->all(), [
            'page' => 'nullable|integer|min:1',
            'pageSize' => 'nullable|integer|min:1|max:100',
            'status' => 'nullable|string|max:50',
            'type' => 'nullable|string|max:100',
            'keyword' => 'nullable|string|max:255',
        ]);
    }

    public function decisionValidation(Request $request): ValidationValidator
    {
        return Validator::make($request->all(), [
            'decision' => 'required|string|in:APPROVE,REJECT',
            'decisionNote' => 'nullable|string|max:1000',
        ]);
    }
}