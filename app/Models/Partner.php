<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class Partner extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'ipa_partner';

    protected $guarded = [];

    protected $casts = [
        'country_id' => 'integer',
        'sector_id' => 'integer',
        'status' => 'integer',
        'score' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function contacts(): HasMany
    {
        return $this->hasMany(PartnerContact::class, 'partner_id')->orderByDesc('is_primary')->orderBy('full_name');
    }

    public function interactions(): HasMany
    {
        return $this->hasMany(PartnerInteraction::class, 'partner_id')->orderByDesc('interaction_at');
    }
}
