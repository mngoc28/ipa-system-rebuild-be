<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Class PartnerScoreHistory
 *
 * Tracks changes to a partner's investment score over time.
 *
 * @package App\Models
 *
 * @property int $id
 * @property int $partner_id
 * @property float $old_score
 * @property float $new_score
 * @property string|null $reason
 * @property int|null $changed_by User ID who updated the score.
 * @property \Illuminate\Support\Carbon $changed_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
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
