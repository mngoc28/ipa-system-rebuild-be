<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class ApprovalStep
 *
 * Represents an individual step in an approval request workflow, assigned to a specific user.
 *
 * @package App\Models
 *
 * @property int $id
 * @property int $approval_request_id
 * @property int $approver_user_id
 * @property int $step_order Sequential order of the step.
 * @property int $decision Current decision status (0: Pending, 1: Approved, 2: Rejected).
 * @property string|null $remark Optional feedback from the approver.
 * @property \Illuminate\Support\Carbon|null $decided_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
 * @property-read \App\Models\ApprovalRequest $request
 */
final class ApprovalStep extends Model
{
    use HasFactory;

    protected $table = 'ipa_approval_step';

    protected $guarded = [];

    protected $casts = [
        'approval_request_id' => 'integer',
        'approver_user_id' => 'integer',
        'step_order' => 'integer',
        'decision' => 'integer',
        'decided_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function request(): BelongsTo
    {
        return $this->belongsTo(ApprovalRequest::class, 'approval_request_id');
    }
}
