<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Class SystemSetting
 *
 * Stores system-wide configuration parameters.
 *
 * @package App\Models
 *
 * @property int $id
 * @property string $setting_key Unique configuration key.
 * @property string|null $setting_value
 * @property string|null $description
 * @property bool $is_secret Whether the value should be masked in UI.
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
final class SystemSetting extends Model
{
    use HasFactory;

    protected $table = 'ipa_system_setting';

    protected $guarded = [];

    protected $casts = [
        'is_secret' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
