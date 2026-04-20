<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Country
 *
 * Represents a country or region for investment categorization.
 *
 * @package App\Models
 *
 * @property int $id
 * @property string $name
 * @property string $iso_code ISO 3166-1 alpha-2 or alpha-3 code.
 * @property bool $is_active
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
final class Country extends Model
{
    use HasFactory;

    protected $table = 'ipa_country';

    protected $guarded = [];

    protected $casts = [
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
