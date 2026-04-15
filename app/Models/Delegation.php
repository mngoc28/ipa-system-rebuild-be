<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Delegation extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'ipa_delegation';

    protected $fillable = [
        'code',
        'name',
        'direction',
        'status',
        'priority',
        'country_id',
        'host_unit_id',
        'owner_user_id',
        'start_date',
        'end_date',
        'participant_count',
        'objective',
        'description',
    ];

    protected $casts = [
        'direction' => 'integer',
        'status' => 'integer',
        'priority' => 'integer',
        'start_date' => 'date',
        'end_date' => 'date',
        'participant_count' => 'integer',
    ];

    public function members()
    {
        return $this->hasMany(DelegationMember::class, 'delegation_id');
    }

    public function events()
    {
        return $this->hasMany(Event::class, 'delegation_id');
    }

    public function outcomes()
    {
        return $this->hasMany(DelegationOutcome::class, 'delegation_id');
    }
}
