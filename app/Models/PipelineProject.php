<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
