<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

final class MinutesApproval extends Model
{
    use HasFactory;

    protected $table = 'ipa_minutes_approval';

    protected $guarded = [];

    protected $casts = [
        'minutes_id' => 'integer',
        'approver_user_id' => 'integer',
        'decision' => 'integer',
        'decided_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}