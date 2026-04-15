<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

final class TaskAttachment extends Model
{
    use HasFactory;

    protected $table = 'ipa_task_attachment';

    protected $guarded = [];

    protected $casts = [
        'task_id' => 'integer',
        'file_id' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
