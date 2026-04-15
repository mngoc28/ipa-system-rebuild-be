<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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