<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Class FileVersion
 *
 * Tracks individual versions of a file to support document history.
 *
 * @package App\Models
 *
 * @property int $id
 * @property int $file_id Parent file ID.
 * @property int $version_no Version sequence number.
 * @property string $path Internal storage path for this specific version.
 * @property int $size_bytes
 * @property int|null $updated_by User ID of the uploader.
 * @property string|null $change_log History of changes for this version.
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
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
