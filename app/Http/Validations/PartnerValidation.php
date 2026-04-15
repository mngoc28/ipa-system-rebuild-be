<?php

declare(strict_types=1);

namespace App\Http\Validations;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Validator as IlluminateValidator;

final class PartnerValidation
{
    public function indexValidation(Request $request): IlluminateValidator
    {
        return Validator::make($request->all(), [
            'page' => 'integer|min:1',
            'pageSize' => 'integer|min:1|max:100',
            'search' => 'nullable|string|max:255',
            'status' => 'nullable|integer',
            'sectorId' => 'nullable|integer',
            'countryId' => 'nullable|integer',
        ]);
    }

    public function storeValidation(Request $request): IlluminateValidator
    {
        return Validator::make($request->all(), [
            'partner_code' => 'required|string|max:255|unique:ipa_partner,partner_code',
            'partner_name' => 'required|string|max:255',
            'country_id' => 'required|integer',
            'sector_id' => 'required|integer',
            'status' => 'integer',
            'website' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);
    }

    public function updateValidation(Request $request, int $id): IlluminateValidator
    {
        return Validator::make($request->all(), [
            'partner_code' => "string|max:255|unique:ipa_partner,partner_code,{$id}",
            'partner_name' => 'string|max:255',
            'country_id' => 'integer',
            'sector_id' => 'integer',
            'status' => 'integer',
            'score' => 'numeric|min:0|max:5',
            'website' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);
    }

    public function contactValidation(Request $request): IlluminateValidator
    {
        return Validator::make($request->all(), [
            'fullName' => 'required|string|max:255',
            'title' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:30',
            'isPrimary' => 'nullable|boolean',
        ]);
    }

    public function interactionValidation(Request $request): IlluminateValidator
    {
        return Validator::make($request->all(), [
            'interactionType' => 'required|integer',
            'interactionAt' => 'required|date',
            'summary' => 'nullable|string',
        ]);
    }
}
