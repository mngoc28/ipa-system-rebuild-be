<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Role
 *
 * Defines a user role which groups a set of permissions.
 *
 * @package App\Models
 *
 * @property int $id
 * @property string $code Unique role code (e.g., 'ADMIN', 'MANAGER').
 * @property string $name Human-readable role name.
 * @property string|null $description
 * @property bool $is_system Whether the role is a protected system role.
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Permission[] $permissions
 */
final class Role extends Model
{
    use HasFactory;

    public function permissions()
    {
        return $this->belongsToMany(Permission::class, 'ipa_role_permission', 'role_id', 'permission_id')->withTimestamps();
    }

    protected $table = 'ipa_role';

    protected $guarded = [];

    protected $casts = [
        'is_system' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
