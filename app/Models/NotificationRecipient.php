<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
