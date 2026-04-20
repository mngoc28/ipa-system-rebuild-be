<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Class PipelineStage
 *
 * Defines the stages of the investment pipeline (e.g., Lead, Qualified, Proposal, Won, Lost).
 *
 * @package App\Models
 *
 * @property int $id
 * @property string $name
 * @property int $stage_order Display and workflow order.
 * @property string|null $description
 * @property bool $is_active
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
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
