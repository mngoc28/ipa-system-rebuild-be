<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

final class PartnerContact extends Model
{
    use HasFactory;

    protected $table = 'ipa_partner_contact';

    protected $guarded = [];

    protected $casts = [
        'partner_id' => 'integer',
        'is_primary' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
