<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
