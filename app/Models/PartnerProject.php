<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

final class PartnerProject extends Model
{
    use HasFactory;

    protected $table = 'ipa_partner_project';

    protected $guarded = [];

    protected $casts = [
        'partner_id' => 'integer',
        'delegation_id' => 'integer',
        'stage_id' => 'integer',
        'estimated_value' => 'decimal:2',
        'success_probability' => 'decimal:2',
        'status' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
