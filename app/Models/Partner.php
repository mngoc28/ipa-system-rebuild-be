<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Class Partner
 *
 * Represents an external investment partner (VC, Angel, IB, etc.).
 *
 * @package App\Models
 *
 * @property int $id
 * @property string $name
 * @property string|null $code
 * @property string|null $description
 * @property string|null $address
 * @property string|null $website
 * @property string|null $email
 * @property string|null $phone
 * @property int|null $country_id
 * @property int|null $sector_id
 * @property int $status 0: Inactive, 1: Active
 * @property float $score Weighted partner score.
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 *
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\PartnerContact[] $contacts
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\PartnerInteraction[] $interactions
 */
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
