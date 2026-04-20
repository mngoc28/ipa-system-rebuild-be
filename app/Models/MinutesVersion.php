<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Class MinutesVersion
 *
 * Stores individual versions of meeting minutes content to support track changes and audit history.
 *
 * @package App\Models
 *
 * @property int $id
 * @property int $minutes_id
 * @property int $version_no Version sequence number.
 * @property int|null $edited_by User ID of the editor.
 * @property array $content_json Structured content of the minutes in JSON format.
 * @property \Illuminate\Support\Carbon|null $edited_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
final class MinutesVersion extends Model
{
    use HasFactory;

    protected $table = 'ipa_minutes_version';

    protected $guarded = [];

    protected $casts = [
        'minutes_id' => 'integer',
        'version_no' => 'integer',
        'edited_by' => 'integer',
        'content_json' => 'array',
        'edited_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
