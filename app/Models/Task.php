<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Class Task
 *
 * Represents an operational task associated with a delegation, event, or minutes.
 *
 * @package App\Models
 *
 * @property int $id
 * @property int|null $delegation_id
 * @property int|null $event_id
 * @property int|null $minutes_id
 * @property string $title
 * @property string|null $description
 * @property int $status Current task status (e.g., 0: Todo, 1: Doing, 2: Done).
 * @property int $priority Priority level.
 * @property \Illuminate\Support\Carbon|null $due_at
 * @property bool $is_overdue_cache Cached overdue status.
 * @property int|null $created_by User ID of the creator.
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
 * @property-read \App\Models\AdminUser|null $creator
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\AdminUser[] $assignees
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\TaskComment[] $comments
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\TaskAttachment[] $attachments
 * @property-read \App\Models\Delegation|null $delegation
 * @property-read \App\Models\Event|null $event
 */
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

    public function creator(): BelongsTo
    {
        return $this->belongsTo(AdminUser::class, 'created_by');
    }

    public function assignees(): BelongsToMany
    {
        return $this->belongsToMany(AdminUser::class, 'ipa_task_assignee', 'task_id', 'user_id')
            ->withTimestamps();
    }

    public function comments(): HasMany
    {
        return $this->hasMany(TaskComment::class, 'task_id')->orderBy('created_at', 'desc');
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(TaskAttachment::class, 'task_id');
    }

    public function delegation(): BelongsTo
    {
        return $this->belongsTo(Delegation::class, 'delegation_id');
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class, 'event_id');
    }
}
