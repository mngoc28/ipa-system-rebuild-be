<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Class MinutesComment
 *
 * Represents a comment or feedback entry on meeting minutes.
 *
 * @package App\Models
 *
 * @property int $id
 * @property int $minutes_id
 * @property int|null $version_id Linked version of the minutes.
 * @property int $commenter_user_id
 * @property int|null $parent_comment_id For threaded discussions.
 * @property string $content
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
final class MinutesComment extends Model
{
    use HasFactory;

    protected $table = 'ipa_minutes_comment';

    protected $guarded = [];

    protected $casts = [
        'minutes_id' => 'integer',
        'version_id' => 'integer',
        'commenter_user_id' => 'integer',
        'parent_comment_id' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
