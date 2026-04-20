<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Class EventParticipant
 *
 * Represents an internal staff member participating in an event.
 *
 * @package App\Models
 *
 * @property int $id
 * @property int $event_id
 * @property int $user_id
 * @property int $participation_status 0: Invited, 1: Accepted, 2: Declined.
 * @property \Illuminate\Support\Carbon|null $invited_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
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
