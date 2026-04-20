<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Class PipelineStageHistory
 *
 * Tracks the movement of projects through the investment pipeline stages.
 *
 * @package App\Models
 *
 * @property int $id
 * @property int $pipeline_project_id
 * @property int $old_stage_id
 * @property int $new_stage_id
 * @property int|null $changed_by User ID who performed the move.
 * @property \Illuminate\Support\Carbon $changed_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
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
