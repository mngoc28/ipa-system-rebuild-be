<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
