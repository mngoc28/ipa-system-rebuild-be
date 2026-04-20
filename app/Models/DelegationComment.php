<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

final class DelegationComment extends Model
{
    use HasFactory;

    protected $table = 'ipa_delegation_comment';

    protected $guarded = [];

    protected $casts = [
        'delegation_id' => 'integer',
        'commenter_user_id' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function delegation()
    {
        return $this->belongsTo(Delegation::class, 'delegation_id');
    }

    public function commenter()
    {
        return $this->belongsTo(AdminUser::class, 'commenter_user_id');
    }
}
