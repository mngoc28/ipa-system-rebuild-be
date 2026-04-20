<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Class TaskStatusHistory
 *
 * Tracks the progression of a task's status over time.
 *
 * @package App\Models
 *
 * @property int $id
 * @property int $task_id
 * @property int $old_status
 * @property int $new_status
 * @property int|null $changed_by User ID who updated the status.
 * @property \Illuminate\Support\Carbon $changed_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
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
