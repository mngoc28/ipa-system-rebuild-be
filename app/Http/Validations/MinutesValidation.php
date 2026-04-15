<?php

declare(strict_types=1);

namespace App\Http\Validations;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Validator as ValidationValidator;

final class MinutesValidation
{
    public function indexValidation(Request $request): ValidationValidator
    {
        return Validator::make($request->all(), [
            'page' => 'nullable|integer|min:1',
            'pageSize' => 'nullable|integer|min:1|max:100',
            'delegationId' => 'nullable|string|max:255',
            'status' => 'nullable|string|max:50',
            'keyword' => 'nullable|string|max:255',
        ]);
    }

    public function storeValidation(Request $request): ValidationValidator
    {
        return Validator::make($request->all(), [
            'delegationId' => 'required|string|max:255',
            'eventId' => 'nullable|string|max:255',
            'title' => 'required|string|max:255',
            'content' => 'nullable',
        ]);
    }

    public function versionValidation(Request $request): ValidationValidator
    {
        return Validator::make($request->all(), [
            'contentText' => 'nullable|string',
            'contentJson' => 'nullable',
            'changeSummary' => 'required|string|max:1000',
        ]);
    }

    public function commentValidation(Request $request): ValidationValidator
    {
        return Validator::make($request->all(), [
            'versionId' => 'nullable|string|max:255',
            'commentText' => 'required|string|max:2000',
            'parentCommentId' => 'nullable|string|max:255',
        ]);
    }

    public function approveValidation(Request $request): ValidationValidator
    {
        return Validator::make($request->all(), [
            'decision' => 'required|string|in:APPROVE,REJECT',
            'decisionNote' => 'nullable|string|max:1000',
        ]);
    }
}