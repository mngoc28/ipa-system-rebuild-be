<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
