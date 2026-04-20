<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Class EventExternalParticipant
 *
 * Represents an external stakeholder (partner, guest) participating in an event.
 *
 * @package App\Models
 *
 * @property int $id
 * @property int $event_id
 * @property string $name
 * @property string|null $email
 * @property string|null $phone
 * @property string|null $organization
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
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
