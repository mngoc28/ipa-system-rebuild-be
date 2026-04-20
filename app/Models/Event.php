<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
