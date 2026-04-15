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
        'outcome_text',
        'follow_up_action',
        'is_resolved',
    ];

    protected $casts = [
        'is_resolved' => 'boolean',
    ];

    public function delegation()
    {
        return $this->belongsTo(Delegation::class, 'delegation_id');
    }
}
