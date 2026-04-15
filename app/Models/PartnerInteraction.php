<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

final class PartnerInteraction extends Model
{
    use HasFactory;

    protected $table = 'ipa_partner_interaction';

    protected $guarded = [];

    protected $casts = [
        'partner_id' => 'integer',
        'interaction_type' => 'integer',
        'interaction_at' => 'datetime',
        'owner_user_id' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
