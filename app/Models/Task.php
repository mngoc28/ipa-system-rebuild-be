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

    public function creator()
    {
        return $this->belongsTo(AdminUser::class, 'created_by');
    }

    public function assignees()
    {
        return $this->belongsToMany(AdminUser::class, 'ipa_task_assignee', 'task_id', 'user_id')
            ->withTimestamps();
    }

    public function comments()
    {
        return $this->hasMany(TaskComment::class, 'task_id')->orderBy('created_at', 'desc');
    }

    public function attachments()
    {
        return $this->hasMany(TaskAttachment::class, 'task_id');
    }

    public function delegation()
    {
        return $this->belongsTo(Delegation::class, 'delegation_id');
    }

    public function event()
    {
        return $this->belongsTo(Event::class, 'event_id');
    }
}
