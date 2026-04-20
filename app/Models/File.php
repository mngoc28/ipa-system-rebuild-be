<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Class File
 *
 * Represents a document or media file stored in the system.
 *
 * @package App\Models
 *
 * @property int $id
 * @property int|null $folder_id Parent folder ID.
 * @property string $name Original filename.
 * @property string $path Internal storage path.
 * @property string|null $mime_type
 * @property int $size_bytes
 * @property int|null $uploaded_by User ID of the uploader.
 * @property int|null $delegation_id Linked delegation project.
 * @property int|null $minutes_id Linked meeting minutes.
 * @property int|null $task_id Linked task.
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
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
