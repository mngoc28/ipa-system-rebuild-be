<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

final class TaskAssignee extends Model
{
    use HasFactory;

    protected $table = 'ipa_task_assignee';

    protected $guarded = [];

    protected $casts = [
        'task_id' => 'integer',
        'user_id' => 'integer',
        'assignment_type' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
