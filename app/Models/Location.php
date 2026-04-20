<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Location
 *
 * Represents a geographical location (address, coordinates) used for events or offices.
 *
 * @package App\Models
 *
 * @property int $id
 * @property string $name
 * @property string|null $address
 * @property int|null $country_id
 * @property string|null $city
 * @property float|null $lat Latitude.
 * @property float|null $lng Longitude.
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
final class Location extends Model
{
    use HasFactory;

    protected $table = 'ipa_location';

    protected $guarded = [];

    protected $casts = [
        'country_id' => 'integer',
        'lat' => 'decimal:7',
        'lng' => 'decimal:7',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
