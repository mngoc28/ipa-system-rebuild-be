<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Class EventRescheduleRequest
 *
 * Represents a request to change the timing of an event.
 *
 * @package App\Models
 *
 * @property int $id
 * @property int $event_id
 * @property int $requested_by User ID who initiated the reschedule request.
 * @property \Illuminate\Support\Carbon $proposed_start_at
 * @property \Illuminate\Support\Carbon $proposed_end_at
 * @property string|null $reason
 * @property int $status 0: Pending, 1: Approved, 2: Rejected.
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
final class EventRescheduleRequest extends Model
{
    use HasFactory;

    protected $table = 'ipa_event_reschedule_request';

    protected $guarded = [];

    protected $casts = [
        'event_id' => 'integer',
        'requested_by' => 'integer',
        'proposed_start_at' => 'datetime',
        'proposed_end_at' => 'datetime',
        'status' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
