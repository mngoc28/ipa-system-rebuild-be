<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

final class AdminUser extends Model
{
    use HasFactory;

    protected $table = 'ipa_user';

    protected $guarded = [];

    protected $casts = [
        'status' => 'integer',
        'primary_unit_id' => 'integer',
        'last_login_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}