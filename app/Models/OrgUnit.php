<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

final class OrgUnit extends Model
{
    use HasFactory;

    protected $table = 'ipa_org_unit';

    protected $guarded = [];

    protected $casts = [
        'parent_unit_id' => 'integer',
        'manager_user_id' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
