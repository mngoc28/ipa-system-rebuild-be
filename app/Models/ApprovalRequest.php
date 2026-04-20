<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Class ApprovalRequest
 *
 * Represents an approval workflow request for a resource (e.g., meeting minutes).
 *
 * @package App\Models
 *
 * @property int $id
 * @property string $ref_type Type of the resource being approved.
 * @property int $ref_id Global ID of the resource being approved.
 * @property int $requester_user_id User who initiated the request.
 * @property int $current_step Current sequential step in the approval flow.
 * @property int $priority Priority level.
 * @property \Illuminate\Support\Carbon|null $due_at
 * @property int $status Overall workflow status (0: Pending, 1: Approved, 2: Rejected).
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\ApprovalStep[] $steps
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\ApprovalHistory[] $history
 */
final class ApprovalRequest extends Model
{
    use HasFactory;

    protected $table = 'ipa_approval_request';

    protected $guarded = [];

    protected $casts = [
        'ref_id' => 'integer',
        'requester_user_id' => 'integer',
        'current_step' => 'integer',
        'priority' => 'integer',
        'due_at' => 'datetime',
        'status' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function steps(): HasMany
    {
        return $this->hasMany(ApprovalStep::class, 'approval_request_id');
    }

    public function history(): HasMany
    {
        return $this->hasMany(ApprovalHistory::class, 'approval_request_id');
    }
}
