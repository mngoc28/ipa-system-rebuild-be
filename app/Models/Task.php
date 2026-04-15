<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

final class Task extends Model
{
    use HasFactory;

    protected $table = 'ipa_task';

    protected $guarded = [];

    protected $casts = [
        'delegation_id' => 'integer',
        'event_id' => 'integer',
        'minutes_id' => 'integer',
        'status' => 'integer',
        'priority' => 'integer',
        'due_at' => 'datetime',
        'is_overdue_cache' => 'boolean',
        'created_by' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
