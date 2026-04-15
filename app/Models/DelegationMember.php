<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
    ];

    protected $casts = [
        'member_type' => 'integer',
    ];

    public function delegation()
    {
        return $this->belongsTo(Delegation::class, 'delegation_id');
    }
}
