<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * Class Delegation
 *
 * Represents an incoming or outgoing delegation project.
 *
 * @package App\Models
 *
 * @property int $id
 * @property string $code Unique delegation project code.
 * @property string $name
 * @property int $direction 0: Incoming, 1: Outgoing.
 * @property int $status Current workflow status.
 * @property int $priority Priority level.
 * @property int|null $country_id Target country for outgoing or origin country for incoming.
 * @property int|null $host_unit_id Internal organizational unit hosting the delegation.
 * @property int|null $owner_user_id Primary owner/manager of the project.
 * @property \Illuminate\Support\Carbon|null $start_date
 * @property \Illuminate\Support\Carbon|null $end_date
 * @property int $participant_count
 * @property string|null $objective
 * @property string|null $description
 * @property string|null $investment_potential
 * @property string|null $approval_remark
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 *
 * @property-read \App\Models\Country|null $country
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Partner[] $partners
 * @property-read \App\Models\AdminUser|null $owner
 * @property-read \App\Models\OrgUnit|null $hostUnit
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\DelegationMember[] $members
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Event[] $events
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\DelegationOutcome[] $outcomes
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Task[] $tasks
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Sector[] $sectors
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\DelegationContact[] $contacts
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\DelegationChecklist[] $checklist
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\DelegationComment[] $comments
 */
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

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class, 'country_id');
    }

    public function partners(): BelongsToMany
    {
        return $this->belongsToMany(Partner::class, 'ipa_delegation_partner_link', 'delegation_id', 'partner_id')->withTimestamps();
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(AdminUser::class, 'owner_user_id');
    }

    public function hostUnit(): BelongsTo
    {
        return $this->belongsTo(OrgUnit::class, 'host_unit_id');
    }

    public function members(): HasMany
    {
        return $this->hasMany(DelegationMember::class, 'delegation_id');
    }

    public function events(): HasMany
    {
        return $this->hasMany(Event::class, 'delegation_id');
    }

    public function outcomes(): HasMany
    {
        return $this->hasMany(DelegationOutcome::class, 'delegation_id');
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class, 'delegation_id');
    }

    public function sectors(): BelongsToMany
    {
        return $this->belongsToMany(Sector::class, 'ipa_delegation_sector_link', 'delegation_id', 'sector_id')->withTimestamps();
    }

    public function contacts(): HasMany
    {
        return $this->hasMany(DelegationContact::class, 'delegation_id');
    }

    public function checklist(): HasMany
    {
        return $this->hasMany(DelegationChecklist::class, 'delegation_id');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(DelegationComment::class, 'delegation_id');
    }
}
