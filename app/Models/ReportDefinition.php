<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

final class ReportDefinition extends Model
{
    use HasFactory;

    protected $table = 'ipa_report_definition';

    protected $guarded = [];

    protected $casts = [
        'scope_type' => 'integer',
        'owner_role_id' => 'integer',
        'query_config' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}