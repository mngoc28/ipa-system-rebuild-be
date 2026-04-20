<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Class PartnerProject
 *
 * Links a partner to a specific delegation project and tracks investment details.
 *
 * @package App\Models
 *
 * @property int $id
 * @property int $partner_id
 * @property int $delegation_id
 * @property int|null $stage_id Pipeline stage ID.
 * @property float|null $estimated_value Estimated investment value.
 * @property float|null $success_probability Investment success probability (0-100).
 * @property string|null $notes
 * @property int $status Project status.
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
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
