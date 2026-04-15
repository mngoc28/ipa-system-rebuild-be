<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

final class PipelineStage extends Model
{
    use HasFactory;

    protected $table = 'ipa_md_pipeline_stage';

    protected $guarded = [];

    protected $casts = [
        'stage_order' => 'integer',
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}