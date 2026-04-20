<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
