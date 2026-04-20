<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Class DelegationContact
 *
 * Links an investment partner contact to a specific delegation project.
 *
 * @package App\Models
 *
 * @property int $id
 * @property int $delegation_id
 * @property int|null $partner_contact_id
 * @property string|null $name Name of the contact.
 * @property string|null $role_name Role of the contact in the delegation.
 * @property string|null $email
 * @property string|null $phone
 * @property bool $is_primary
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
 * @property-read \App\Models\Delegation $delegation
 * @property-read \App\Models\PartnerContact|null $contact
 */
class DelegationContact extends Model
{
    use HasFactory;

    protected $table = 'ipa_delegation_contact';

    protected $fillable = [
        'delegation_id',
        'partner_contact_id',
        'name',
        'role_name',
        'email',
        'phone',
        'is_primary',
    ];

    protected $casts = [
        'is_primary' => 'boolean',
    ];

    public function delegation()
    {
        return $this->belongsTo(Delegation::class, 'delegation_id');
    }

    public function contact()
    {
        return $this->belongsTo(PartnerContact::class, 'contact_id');
    }
}
