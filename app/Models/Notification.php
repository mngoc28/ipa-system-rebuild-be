<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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