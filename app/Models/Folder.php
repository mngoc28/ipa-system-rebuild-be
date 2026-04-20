<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Folder
 *
 * Represents a virtual directory for organizing files.
 *
 * @package App\Models
 *
 * @property int $id
 * @property string $name
 * @property int|null $parent_folder_id
 * @property int|null $owner_user_id
 * @property int $scope_type 0: Private, 1: Shared, 2: System, etc.
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
final class Folder extends Model
{
    use HasFactory;

    protected $table = 'ipa_folder';

    protected $guarded = [];

    protected $casts = [
        'parent_folder_id' => 'integer',
        'owner_user_id' => 'integer',
        'scope_type' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
