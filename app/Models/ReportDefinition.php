<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Class ReportDefinition
 *
 * Defines the structure and query configuration for system-generated reports.
 *
 * @package App\Models
 *
 * @property int $id
 * @property string $name
 * @property string|null $description
 * @property int $scope_type 0: Global, 1: Department, 2: Individual.
 * @property int|null $owner_role_id Role required to run this report.
 * @property array $query_config JSON configuration for the report engine.
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
final class ReportDefinition extends Model
{
    use HasFactory;

    protected $table = 'ipa_report_definition';

    protected $guarded = [];

    protected $casts = [
        'scope_type' => 'integer',
        'owner_role_id' => 'integer',
        'query_config' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
