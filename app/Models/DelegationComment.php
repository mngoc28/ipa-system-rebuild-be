<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class DelegationComment
 *
 * Represents a comment or discussion thread entry within a delegation project.
 *
 * @package App\Models
 *
 * @property int $id
 * @property int $delegation_id
 * @property int $commenter_user_id
 * @property string $content
 * @property string|null $attachments_json JSON array of attached files.
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
 * @property-read \App\Models\Delegation $delegation
 * @property-read \App\Models\AdminUser $commenter
 */
final class DelegationComment extends Model
{
    use HasFactory;

    protected $table = 'ipa_delegation_comment';

    protected $guarded = [];

    protected $casts = [
        'delegation_id' => 'integer',
        'commenter_user_id' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function delegation(): BelongsTo
    {
        return $this->belongsTo(Delegation::class, 'delegation_id');
    }

    public function commenter(): BelongsTo
    {
        return $this->belongsTo(AdminUser::class, 'commenter_user_id');
    }
}
