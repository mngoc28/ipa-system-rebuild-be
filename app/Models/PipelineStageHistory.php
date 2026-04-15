<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

final class PipelineStageHistory extends Model
{
    use HasFactory;

    protected $table = 'ipa_pipeline_stage_history';

    protected $guarded = [];

    protected $casts = [
        'pipeline_project_id' => 'integer',
        'old_stage_id' => 'integer',
        'new_stage_id' => 'integer',
        'changed_by' => 'integer',
        'changed_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}