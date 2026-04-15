<?php

namespace App\Http\Validations;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class DelegationValidation
{
    public static function validateStore(Request $request)
    {
        return Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'direction' => 'required|integer|in:1,2', // 1: Inbound, 2: Outbound
            'status' => 'nullable|integer',
            'priority' => 'nullable|integer',
            'country_id' => 'required|integer',
            'host_unit_id' => 'required|integer',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'objective' => 'nullable|string',
            'description' => 'nullable|string',
        ]);
    }

    public static function validateUpdate(Request $request)
    {
        return Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'direction' => 'sometimes|required|integer|in:1,2',
            'status' => 'nullable|integer',
            'priority' => 'nullable|integer',
            'country_id' => 'sometimes|required|integer',
            'host_unit_id' => 'sometimes|required|integer',
            'start_date' => 'sometimes|required|date',
            'end_date' => 'sometimes|required|date|after_or_equal:start_date',
            'objective' => 'nullable|string',
            'description' => 'nullable|string',
        ]);
    }
}
