<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

final class ReportRun extends Model
{
    use HasFactory;

    protected $table = 'ipa_report_run';

    protected $guarded = [];

    protected $casts = [
        'report_definition_id' => 'integer',
        'run_by' => 'integer',
        'params_json' => 'array',
        'output_file_id' => 'integer',
        'status' => 'integer',
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}