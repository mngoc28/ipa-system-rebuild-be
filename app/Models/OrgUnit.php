<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Class OrgUnit
 *
 * Represents an organizational unit or department within the hosting organization.
 *
 * @package App\Models
 *
 * @property int $id
 * @property string $name
 * @property int|null $parent_unit_id ID of the parent department/unit.
 * @property int|null $manager_user_id ID of the primary manager/head of unit.
 * @property string|null $description
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
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
