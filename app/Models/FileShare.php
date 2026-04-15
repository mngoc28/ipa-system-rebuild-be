<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

final class FileShare extends Model
{
    use HasFactory;

    protected $table = 'ipa_file_share';

    protected $guarded = [];

    protected $casts = [
        'file_id' => 'integer',
        'shared_with_user_id' => 'integer',
        'shared_with_role_id' => 'integer',
        'permission_level' => 'integer',
        'expires_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}