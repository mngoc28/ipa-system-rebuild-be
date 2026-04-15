<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

final class PartnerScoreHistory extends Model
{
    use HasFactory;

    protected $table = 'ipa_partner_score_history';

    protected $guarded = [];

    protected $casts = [
        'partner_id' => 'integer',
        'old_score' => 'decimal:2',
        'new_score' => 'decimal:2',
        'changed_by' => 'integer',
        'changed_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
