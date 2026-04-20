<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

final class EventExternalParticipant extends Model
{
    use HasFactory;

    protected $table = 'ipa_event_external_participant';

    protected $guarded = [];

    protected $casts = [
        'event_id' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
