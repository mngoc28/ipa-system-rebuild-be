<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

final class MinutesVersion extends Model
{
    use HasFactory;

    protected $table = 'ipa_minutes_version';

    protected $guarded = [];

    protected $casts = [
        'minutes_id' => 'integer',
        'version_no' => 'integer',
        'edited_by' => 'integer',
        'content_json' => 'array',
        'edited_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}