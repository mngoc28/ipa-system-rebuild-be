<?php

declare(strict_types=1);

namespace App\Http\Validations;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Validator as ValidationValidator;

final class PipelineValidation
{
    public function indexValidation(Request $request): ValidationValidator
    {
        return Validator::make($request->all(), [
            'stage_id' => ['nullable', 'string', 'max:255'],
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
            'pageSize' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);
    }

    public function createValidation(Request $request): ValidationValidator
    {
        return Validator::make($request->all(), [
            'project_name' => ['required', 'string', 'max:255'],
            'country_id' => ['required', 'string', 'max:255'],
            'sector_id' => ['required', 'string', 'max:255'],
            'stage_id' => ['required', 'string', 'max:255'],
            'owner_user_id' => ['required', 'string', 'max:255'],
            'project_code' => ['nullable', 'string', 'max:255'],
            'partner_id' => ['nullable', 'string', 'max:255'],
            'delegation_id' => ['nullable', 'string', 'max:255'],
            'estimated_value' => ['nullable', 'numeric'],
            'success_probability' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'expected_close_date' => ['nullable', 'date'],
        ]);
    }

    public function updateValidation(Request $request): ValidationValidator
    {
        return Validator::make($request->all(), [
            'project_name' => ['nullable', 'string', 'max:255'],
            'country_id' => ['nullable', 'string', 'max:255'],
            'sector_id' => ['nullable', 'string', 'max:255'],
            'stage_id' => ['nullable', 'string', 'max:255'],
            'owner_user_id' => ['nullable', 'string', 'max:255'],
            'partner_id' => ['nullable', 'string', 'max:255'],
            'delegation_id' => ['nullable', 'string', 'max:255'],
            'estimated_value' => ['nullable', 'numeric'],
            'success_probability' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'expected_close_date' => ['nullable', 'date'],
            'status' => ['nullable', 'string', 'in:active,hidden'],
        ]);
    }

    public function stageValidation(Request $request): ValidationValidator
    {
        return Validator::make($request->all(), [
            'new_stage_id' => ['required', 'string', 'max:255'],
            'reason' => ['nullable', 'string', 'max:1000'],
        ]);
    }
}
