<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

final class TaskStatusHistory extends Model
{
    use HasFactory;

    protected $table = 'ipa_task_status_history';

    protected $guarded = [];

    protected $casts = [
        'task_id' => 'integer',
        'old_status' => 'integer',
        'new_status' => 'integer',
        'changed_by' => 'integer',
        'changed_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
