<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Notification
 *
 * Represents an in-system notification or alert.
 *
 * @package App\Models
 *
 * @property int $id
 * @property int $notification_type_id
 * @property string $title
 * @property string $content
 * @property string|null $ref_type Type of the referenced resource.
 * @property int|null $ref_id ID of the referenced resource.
 * @property int $severity 0: Info, 1: Success, 2: Warning, 3: Danger.
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
final class Notification extends Model
{
    use HasFactory;

    protected $table = 'ipa_notification';

    protected $guarded = [];

    protected $casts = [
        'notification_type_id' => 'integer',
        'ref_id' => 'integer',
        'severity' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
