<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Class FileShare
 *
 * Manages file sharing permissions with specific users or roles.
 *
 * @package App\Models
 *
 * @property int $id
 * @property int $file_id
 * @property int|null $shared_with_user_id
 * @property int|null $shared_with_role_id
 * @property int $permission_level 0: View, 1: Edit, etc.
 * @property \Illuminate\Support\Carbon|null $expires_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
final class FileShare extends Model
{
    use HasFactory;

    protected $table = 'ipa_file_share';

    protected $guarded = [];

    protected $casts = [
        'file_id' => 'integer',
        'shared_with_user_id' => 'integer',
        'shared_with_role_id' => 'integer',
        'permission_level' => 'integer',
        'expires_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
