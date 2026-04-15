<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

final class FileVersion extends Model
{
    use HasFactory;

    protected $table = 'ipa_file_version';

    protected $guarded = [];

    protected $casts = [
        'file_id' => 'integer',
        'version_no' => 'integer',
        'size_bytes' => 'integer',
        'updated_by' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}