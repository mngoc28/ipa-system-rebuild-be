<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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