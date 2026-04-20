<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Class MinutesApproval
 *
 * Tracks individual approval decisions for meeting minutes.
 *
 * @package App\Models
 *
 * @property int $id
 * @property int $minutes_id
 * @property int $approver_user_id
 * @property int $decision 0: Pending, 1: Approved, 2: Rejected.
 * @property string|null $remark
 * @property \Illuminate\Support\Carbon|null $decided_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
final class MinutesApproval extends Model
{
    use HasFactory;

    protected $table = 'ipa_minutes_approval';

    protected $guarded = [];

    protected $casts = [
        'minutes_id' => 'integer',
        'approver_user_id' => 'integer',
        'decision' => 'integer',
        'decided_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
