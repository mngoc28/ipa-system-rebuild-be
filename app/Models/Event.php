<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Event
 *
 * Represents a calendar event, meeting, or scheduled activity within a delegation.
 *
 * @package App\Models
 *
 * @property int $id
 * @property int|null $delegation_id Linked delegation project.
 * @property string $title
 * @property int $event_type 0: Meeting, 1: Site Visit, 2: Banquet, etc.
 * @property int $status 0: Proposed, 1: Confirmed, 2: Cancelled.
 * @property \Illuminate\Support\Carbon $start_at
 * @property \Illuminate\Support\Carbon $end_at
 * @property int|null $location_id
 * @property int|null $organizer_user_id
 * @property string|null $description
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
 * @property-read \App\Models\Location|null $location
 * @property-read \App\Models\User|null $staff
 */
final class Event extends Model
{
    use HasFactory;

    protected $table = 'ipa_event';

    protected $guarded = [];

    protected $casts = [
        'delegation_id' => 'integer',
        'event_type' => 'integer',
        'status' => 'integer',
        'start_at' => 'datetime',
        'end_at' => 'datetime',
        'location_id' => 'integer',
        'organizer_user_id' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function location()
    {
        return $this->belongsTo(Location::class, 'location_id');
    }

    public function staff()
    {
        return $this->belongsTo(User::class, 'staff_id');
    }
}
