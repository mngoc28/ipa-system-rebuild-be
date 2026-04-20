<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class ApprovalHistory
 *
 * Tracks changes in the status of approval requests for audit purposes.
 *
 * @package App\Models
 *
 * @property int $id
 * @property int $approval_request_id
 * @property int $old_status
 * @property int $new_status
 * @property int|null $changed_by User ID who updated the status.
 * @property \Illuminate\Support\Carbon $changed_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
 * @property-read \App\Models\ApprovalRequest $request
 */
final class ApprovalHistory extends Model
{
    use HasFactory;

    protected $table = 'ipa_approval_history';

    protected $guarded = [];

    protected $casts = [
        'approval_request_id' => 'integer',
        'old_status' => 'integer',
        'new_status' => 'integer',
        'changed_by' => 'integer',
        'changed_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function request(): BelongsTo
    {
        return $this->belongsTo(ApprovalRequest::class, 'approval_request_id');
    }
}
