<?php

declare(strict_types=1);

namespace App\Http\Validations;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

final class MasterDataValidation
{
    public function indexValidation(Request $request, string $domain)
    {
        return Validator::make(
            array_merge($request->all(), ['domain' => $domain]),
            [
                'domain' => ['required', 'string', 'in:' . implode(',', $this->domains())],
                'q' => ['nullable', 'string', 'max:100'],
                'sort_field' => ['nullable', 'string', 'in:code,name_vi,name_en,sort_order,created_at,updated_at'],
                'sort_direction' => ['nullable', 'string', 'in:asc,desc'],
            ]
        );
    }

    public function getByIdValidation(string $domain, string $id)
    {
        return Validator::make(
            ['domain' => $domain, 'id' => $id],
            [
                'domain' => ['required', 'string', 'in:' . implode(',', $this->domains())],
                'id' => ['required', 'string'], // Bypassing hardcoded table check for dynamic models
            ]
        );
    }

    public function createValidation(Request $request, string $domain)
    {
        return Validator::make(
            array_merge($request->all(), ['domain' => $domain]),
            [
                'domain' => ['required', 'string', 'in:' . implode(',', $this->domains())],
                'code' => ['required', 'string', 'max:100', 'unique:master_data_items,code,NULL,id,domain,' . $domain],
                'name_vi' => ['required', 'string', 'max:255'],
                'name_en' => ['nullable', 'string', 'max:255'],
                'sort_order' => ['nullable', 'integer', 'min:0'],
                'is_active' => ['nullable', 'boolean'],
            ]
        );
    }

    public function updateValidation(string $domain, string $id, Request $request)
    {
        return Validator::make(
            array_merge($request->all(), ['domain' => $domain, 'id' => $id]),
            [
                'domain' => ['required', 'string', 'in:' . implode(',', $this->domains())],
                'id' => ['required', 'string'], // Bypassing hardcoded table check for dynamic models
                'code' => ['sometimes', 'string', 'max:100'], // Relaxing unique check for dynamic models
                'name_vi' => ['sometimes', 'string', 'max:255'],
                'name_en' => ['nullable', 'string', 'max:255'],
                'sort_order' => ['sometimes', 'integer', 'min:0'],
                'is_active' => ['sometimes', 'boolean'],
            ]
        );
    }

    private function domains(): array
    {
        return array_keys(config('master_data.domains', []));
    }
}
