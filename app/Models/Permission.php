<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Permission
 *
 * Represents an individual system permission that can be assigned to roles.
 *
 * @package App\Models
 *
 * @property int $id
 * @property string $code Unique permission identifier (e.g., 'USER_CREATE').
 * @property string $name Human-readable permission name.
 * @property string|null $category Grouping category for the permission.
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
final class Permission extends Model
{
    use HasFactory;

    protected $table = 'ipa_permission';

    protected $guarded = [];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
