<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
