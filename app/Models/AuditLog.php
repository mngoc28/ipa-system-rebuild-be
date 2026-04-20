<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

final class AuditLog extends Model
{
    use HasFactory;

    protected $table = 'ipa_audit_log';

    protected $guarded = [];

    protected $casts = [
        'actor_user_id' => 'integer',
        'resource_id' => 'integer',
        'before_json' => 'array',
        'after_json' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
