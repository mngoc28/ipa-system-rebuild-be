<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DelegationChecklist extends Model
{
    use HasFactory;

    protected $table = 'ipa_delegation_checklist';

    protected $fillable = [
        'delegation_id',
        'task_name',
        'is_completed',
        'due_date',
        'assigned_to_user_id',
    ];

    protected $casts = [
        'is_completed' => 'boolean',
        'due_date' => 'date',
    ];

    public function delegation()
    {
        return $this->belongsTo(Delegation::class, 'delegation_id');
    }

    public function assignee()
    {
        return $this->belongsTo(User::class, 'assigned_to_user_id');
    }
}
