<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

final class SystemSetting extends Model
{
    use HasFactory;

    protected $table = 'ipa_system_setting';

    protected $guarded = [];

    protected $casts = [
        'is_secret' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
