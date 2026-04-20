<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Class ReportRun
 *
 * Tracks the execution and results of individual report generation jobs.
 *
 * @package App\Models
 *
 * @property int $id
 * @property int $report_definition_id
 * @property int|null $run_by User who initiated the report run.
 * @property array|null $params_json Parameters used for this specific run.
 * @property int|null $output_file_id Reference to the generated file.
 * @property int $status 0: Pending, 1: Running, 2: Completed, 3: Failed.
 * @property \Illuminate\Support\Carbon|null $started_at
 * @property \Illuminate\Support\Carbon|null $finished_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
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
