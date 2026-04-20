<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Class NotificationType
 *
 * Defines the types of notifications available in the system (e.g., Task Assigned, Mention, System Alert).
 *
 * @package App\Models
 *
 * @property int $id
 * @property string $name
 * @property string|null $description
 * @property int $default_channel 0: Web, 1: Email, 2: Both.
 * @property bool $is_active
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
final class NotificationType extends Model
{
    use HasFactory;

    protected $table = 'ipa_md_notification_type';

    protected $guarded = [];

    protected $casts = [
        'default_channel' => 'integer',
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
