<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Delegation extends Model
{
    use HasFactory;
    use SoftDeletes;

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
        'investment_potential',
        'approval_remark',
    ];

    protected $casts = [
        'direction' => 'integer',
        'status' => 'integer',
        'priority' => 'integer',
        'start_date' => 'date',
        'end_date' => 'date',
        'participant_count' => 'integer',
    ];

    public function country()
    {
        return $this->belongsTo(Country::class, 'country_id');
    }

    public function partners()
    {
        return $this->belongsToMany(Partner::class, 'ipa_delegation_partner_link', 'delegation_id', 'partner_id');
    }

    public function owner()
    {
        return $this->belongsTo(AdminUser::class, 'owner_user_id');
    }

    public function hostUnit()
    {
        return $this->belongsTo(OrgUnit::class, 'host_unit_id');
    }

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

    public function tasks()
    {
        return $this->hasMany(Task::class, 'delegation_id');
    }

    public function sectors()
    {
        return $this->belongsToMany(Sector::class, 'ipa_delegation_sector_link', 'delegation_id', 'sector_id');
    }

    public function contacts()
    {
        return $this->hasMany(DelegationContact::class, 'delegation_id');
    }

    public function checklist()
    {
        return $this->hasMany(DelegationChecklist::class, 'delegation_id');
    }

    public function comments()
    {
        return $this->hasMany(DelegationComment::class, 'delegation_id');
    }
}
