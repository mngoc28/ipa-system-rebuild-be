<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class TaskComment
 *
 * Represents a comment on a specific task.
 *
 * @package App\Models
 *
 * @property int $id
 * @property int $task_id
 * @property int $commenter_user_id
 * @property string $content
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
 * @property-read \App\Models\Task $task
 * @property-read \App\Models\AdminUser $commenter
 */
final class TaskComment extends Model
{
    use HasFactory;

    protected $table = 'ipa_task_comment';

    protected $guarded = [];

    protected $casts = [
        'task_id' => 'integer',
        'commenter_user_id' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class, 'task_id');
    }

    public function commenter(): BelongsTo
    {
        return $this->belongsTo(AdminUser::class, 'commenter_user_id');
    }
}
