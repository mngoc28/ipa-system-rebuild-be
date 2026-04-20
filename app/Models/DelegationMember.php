<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Class DelegationMember
 *
 * Represents an individual member/participant of a delegation.
 *
 * @package App\Models
 *
 * @property int $id
 * @property int $delegation_id
 * @property string $full_name
 * @property string|null $title
 * @property string|null $organization_name
 * @property string|null $contact_email
 * @property string|null $contact_phone
 * @property int $member_type 0: Host, 1: Guest, etc.
 * @property string|null $gender
 * @property string|null $identity_number Passport or ID card number.
 * @property bool $is_vip
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
 * @property-read \App\Models\Delegation $delegation
 */
class DelegationMember extends Model
{
    use HasFactory;

    protected $table = 'ipa_delegation_member';

    protected $fillable = [
        'delegation_id',
        'full_name',
        'title',
        'organization_name',
        'contact_email',
        'contact_phone',
        'member_type',
        'gender',
        'identity_number',
        'is_vip',
    ];

    protected $casts = [
        'member_type' => 'integer',
    ];

    public function delegation()
    {
        return $this->belongsTo(Delegation::class, 'delegation_id');
    }
}
