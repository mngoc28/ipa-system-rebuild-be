<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Class TaskAssignee
 *
 * Pivot model linking users to tasks with specific assignment types.
 *
 * @package App\Models
 *
 * @property int $id
 * @property int $task_id
 * @property int $user_id
 * @property int $assignment_type 0: Primary, 1: Secondary, etc.
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
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
