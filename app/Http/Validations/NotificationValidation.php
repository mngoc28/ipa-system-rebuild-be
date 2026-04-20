<?php

declare(strict_types=1);

namespace App\Http\Validations;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Validator as ValidationValidator;

final class NotificationValidation
{
    public function indexValidation(Request $request): ValidationValidator
    {
        return Validator::make($request->all(), [
            'unreadOnly' => ['nullable', 'in:true,false,1,0,True,False'],
            'page' => ['nullable', 'integer', 'min:1'],
            'pageSize' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);
    }
}
