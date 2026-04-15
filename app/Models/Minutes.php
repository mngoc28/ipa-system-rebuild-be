<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

final class Minutes extends Model
{
    use HasFactory;

    protected $table = 'ipa_minutes';

    protected $guarded = [];

    protected $casts = [
        'delegation_id' => 'integer',
        'event_id' => 'integer',
        'current_version_no' => 'integer',
        'status' => 'integer',
        'owner_user_id' => 'integer',
        'approved_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}