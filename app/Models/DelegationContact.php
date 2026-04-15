<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DelegationContact extends Model
{
    use HasFactory;

    protected $table = 'ipa_delegation_contact';

    protected $fillable = [
        'delegation_id',
        'contact_id',
        'role_in_delegation',
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
