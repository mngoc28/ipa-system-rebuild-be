<?php

declare(strict_types=1);

namespace App\Http\Validations;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Validator as IlluminateValidator;

final class TaskValidation
{
    public function indexValidation(Request $request): IlluminateValidator
    {
        return Validator::make($request->all(), [
            'page' => 'integer|min:1',
            'pageSize' => 'integer|min:1|max:100',
            'search' => 'nullable|string|max:255',
            'status' => 'nullable|integer',
            'priority' => 'nullable|integer',
        ]);
    }

    public function storeValidation(Request $request): IlluminateValidator
    {
        return Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'integer',
            'priority' => 'integer',
            'due_at' => 'nullable|date',
            'delegation_id' => 'nullable|integer',
            'event_id' => 'nullable|integer',
            'minutes_id' => 'nullable|integer',
            'assignee_ids' => 'nullable|array',
            'assignee_ids.*' => 'integer|exists:ipa_user,id',
        ]);
    }

    public function updateValidation(Request $request): IlluminateValidator
    {
        return Validator::make($request->all(), [
            'title' => 'string|max:255',
            'description' => 'nullable|string',
            'status' => 'integer',
            'priority' => 'integer',
            'due_at' => 'nullable|date',
            'assignee_ids' => 'nullable|array',
            'assignee_ids.*' => 'integer|exists:ipa_user,id',
        ]);
    }
}
