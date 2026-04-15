<?php

declare(strict_types=1);

namespace App\Http\Validations;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

final class TeamValidation
{
    public function indexValidation(Request $request)
    {
        return Validator::make($request->all(), [
            'unitId' => ['nullable', 'integer', 'exists:ipa_org_unit,id'],
            'page' => ['nullable', 'integer', 'min:1'],
            'pageSize' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);
    }

    public function storeValidation(Request $request)
    {
        return Validator::make($request->all(), [
            'fullName' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'username' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'positionTitle' => ['nullable', 'string', 'max:255'],
            'unitId' => ['nullable', 'integer', 'exists:ipa_org_unit,id'],
        ]);
    }
}