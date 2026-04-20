<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

final class File extends Model
{
    use HasFactory;

    protected $table = 'ipa_file';

    protected $guarded = [];

    protected $casts = [
        'folder_id' => 'integer',
        'size_bytes' => 'integer',
        'uploaded_by' => 'integer',
        'delegation_id' => 'integer',
        'minutes_id' => 'integer',
        'task_id' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
