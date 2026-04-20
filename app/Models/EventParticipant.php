<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

final class EventParticipant extends Model
{
    use HasFactory;

    protected $table = 'ipa_event_participant';

    protected $guarded = [];

    protected $casts = [
        'event_id' => 'integer',
        'user_id' => 'integer',
        'participation_status' => 'integer',
        'invited_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
