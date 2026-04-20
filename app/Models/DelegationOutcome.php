<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Class DelegationOutcome
 *
 * Records the results, summary, and ratings of a delegation project.
 *
 * @package App\Models
 *
 * @property int $id
 * @property int $delegation_id
 * @property string|null $summary Executive summary of the delegation.
 * @property string|null $next_steps Planned future actions.
 * @property int|null $rating Satisfaction rating (1-5).
 * @property int $progress_percent Completion percentage.
 * @property bool $is_resolved Whether the outcome has been finalized.
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
 * @property-read \App\Models\Delegation $delegation
 */
class DelegationOutcome extends Model
{
    use HasFactory;

    protected $table = 'ipa_delegation_outcome';

    protected $fillable = [
        'delegation_id',
        'summary',
        'next_steps',
        'rating',
        'progress_percent',
    ];

    protected $casts = [
        'is_resolved' => 'boolean',
    ];

    public function delegation()
    {
        return $this->belongsTo(Delegation::class, 'delegation_id');
    }
}
