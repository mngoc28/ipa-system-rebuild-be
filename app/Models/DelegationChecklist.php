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
        'item_name',
        'assignee_user_id',
        'due_date',
        'status',
        'priority',
    ];

    protected $casts = [
        'due_date' => 'date',
        'status' => 'integer',
        'priority' => 'integer',
    ];

    public function delegation()
    {
        return $this->belongsTo(Delegation::class, 'delegation_id');
    }

    public function assignee()
    {
        return $this->belongsTo(AdminUser::class, 'assignee_user_id');
    }
}
