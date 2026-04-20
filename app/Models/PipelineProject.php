<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Class PipelineProject
 *
 * Represents an investment project within the CRM pipeline.
 *
 * @package App\Models
 *
 * @property int $id
 * @property string $name
 * @property int|null $partner_id Associated partner.
 * @property int|null $country_id Project origin/target country.
 * @property int|null $sector_id Industry sector.
 * @property int $stage_id Current pipeline stage ID.
 * @property float|null $estimated_value Estimated investment amount.
 * @property float|null $success_probability Probability of success (0-100).
 * @property \Illuminate\Support\Carbon|null $expected_close_date
 * @property int|null $owner_user_id User responsible for the project.
 * @property int $status Overall project status.
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
final class PipelineProject extends Model
{
    use HasFactory;

    protected $table = 'ipa_pipeline_project';

    protected $guarded = [];

    protected $casts = [
        'partner_id' => 'integer',
        'country_id' => 'integer',
        'sector_id' => 'integer',
        'stage_id' => 'integer',
        'estimated_value' => 'float',
        'success_probability' => 'float',
        'expected_close_date' => 'date',
        'owner_user_id' => 'integer',
        'status' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
