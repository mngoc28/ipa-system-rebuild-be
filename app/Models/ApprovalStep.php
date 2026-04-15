<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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