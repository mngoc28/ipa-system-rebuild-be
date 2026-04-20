<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Class NotificationRecipient
 *
 * Tracks individual delivery and read status of a notification for a specific user.
 *
 * @package App\Models
 *
 * @property int $id
 * @property int $notification_id
 * @property int $recipient_user_id
 * @property int $delivery_status 0: Pending, 1: Delivered, 2: Failed.
 * @property \Illuminate\Support\Carbon|null $read_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
final class NotificationRecipient extends Model
{
    use HasFactory;

    protected $table = 'ipa_notification_recipient';

    protected $guarded = [];

    protected $casts = [
        'notification_id' => 'integer',
        'recipient_user_id' => 'integer',
        'delivery_status' => 'integer',
        'read_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
