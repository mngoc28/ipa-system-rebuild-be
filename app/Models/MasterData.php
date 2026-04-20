<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class MasterData
 *
 * Represents a generic master data item used for drop-downs and categorizations.
 * Supports a hierarchical structure via codes.
 *
 * @package App\Models
 *
 * @property string $id Unique string identifier (slug/code).
 * @property string $group_code Category/Group of this item.
 * @property string $item_code Specific code for this item.
 * @property string $name_vi Vietnamese label.
 * @property string|null $name_en English label.
 * @property string|null $description
 * @property int $sort_order
 * @property bool $is_active
 * @property int|null $created_by
 * @property int|null $updated_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 */
final class MasterData extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'master_data_items';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $guarded = [];

    protected $casts = [
        'sort_order' => 'integer',
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
