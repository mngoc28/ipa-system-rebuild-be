<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Class DelegationChecklist
 *
 * Represents an operational checklist item for delegation preparation or execution.
 *
 * @package App\Models
 *
 * @property int $id
 * @property int $delegation_id
 * @property string $item_name
 * @property int|null $assignee_user_id
 * @property \Illuminate\Support\Carbon|null $due_date
 * @property int $status 0: Pending, 1: In Progress, 2: Completed.
 * @property int $priority 0: Low, 1: Medium, 2: High.
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
 * @property-read \App\Models\Delegation $delegation
 * @property-read \App\Models\AdminUser|null $assignee
 */
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
