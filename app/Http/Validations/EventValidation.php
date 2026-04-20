<?php

declare(strict_types=1);

namespace App\Http\Validations;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator as ValidationValidator;

final class EventValidation
{
    public function indexValidation(Request $request): ValidationValidator
    {
        return Validator::make($request->all(), [
            'page' => 'nullable|integer|min:1',
            'pageSize' => 'nullable|integer|min:1|max:100',
            'from' => 'nullable|date',
            'to' => 'nullable|date|after_or_equal:from',
            'delegationId' => 'nullable|string|max:255',
            'organizerId' => 'nullable|string|max:255',
            'unitId' => 'nullable|string|max:255',
        ]);
    }

    public function storeValidation(Request $request): ValidationValidator
    {
        return Validator::make($request->all(), [
            'delegationId' => 'nullable|string|max:255',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'eventType' => ['required', 'string', Rule::in(['MEETING', 'VISIT', 'WORKSHOP', 'CEREMONY'])],
            'status' => ['required', 'string', Rule::in(['PLANNED', 'CONFIRMED', 'DONE', 'CANCELLED'])],
            'startAt' => 'required|date',
            'endAt' => 'required|date|after:startAt',
            'locationId' => 'nullable|string|max:255',
            'organizerUserId' => 'required|string|max:255',
            'participantUserIds' => 'nullable|array',
            'participantUserIds.*' => 'string|max:255',
        ]);
    }

    public function updateValidation(Request $request): ValidationValidator
    {
        return Validator::make($request->all(), [
            'delegationId' => 'nullable|string|max:255',
            'title' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'eventType' => ['nullable', 'string', Rule::in(['MEETING', 'VISIT', 'WORKSHOP', 'CEREMONY'])],
            'status' => ['nullable', 'string', Rule::in(['PLANNED', 'CONFIRMED', 'DONE', 'CANCELLED'])],
            'startAt' => 'nullable|date',
            'endAt' => 'nullable|date',
            'locationId' => 'nullable|string|max:255',
            'participantUserIds' => 'nullable|array',
            'participantUserIds.*' => 'string|max:255',
        ]);
    }

    public function joinValidation(Request $request): ValidationValidator
    {
        return Validator::make($request->all(), [
            'joined' => 'required|boolean',
        ]);
    }

    public function rescheduleValidation(Request $request): ValidationValidator
    {
        return Validator::make($request->all(), [
            'proposedStartAt' => 'required|date',
            'proposedEndAt' => 'required|date|after:proposedStartAt',
            'reason' => 'required|string|max:1000',
        ]);
    }
}
