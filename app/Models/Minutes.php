<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Minutes
 *
 * Represents meeting minutes or a formal record of a meeting associated with a delegation or event.
 *
 * @package App\Models
 *
 * @property int $id
 * @property int|null $delegation_id Linked delegation project.
 * @property int|null $event_id Linked event.
 * @property string $title
 * @property int $current_version_no Latest version sequence number.
 * @property int $status 0: Draft, 1: Under Review, 2: Finalized.
 * @property int|null $owner_user_id
 * @property \Illuminate\Support\Carbon|null $approved_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
final class Minutes extends Model
{
    use HasFactory;

    protected $table = 'ipa_minutes';

    protected $guarded = [];

    protected $casts = [
        'delegation_id' => 'integer',
        'event_id' => 'integer',
        'current_version_no' => 'integer',
        'status' => 'integer',
        'owner_user_id' => 'integer',
        'approved_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
