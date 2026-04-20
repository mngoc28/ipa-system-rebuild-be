<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Class AuditLog
 *
 * Tracks administrative and system-wide changes for auditing purposes.
 *
 * @package App\Models
 *
 * @property int $id
 * @property int|null $actor_user_id User who performed the action.
 * @property string $action Name of the action performed.
 * @property string|null $resource_type Type of resource affected.
 * @property int|null $resource_id ID of the resource affected.
 * @property array|null $before_json State before change.
 * @property array|null $after_json State after change.
 * @property string|null $ip_address
 * @property string|null $user_agent
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
final class AuditLog extends Model
{
    use HasFactory;

    protected $table = 'ipa_audit_log';

    protected $guarded = [];

    protected $casts = [
        'actor_user_id' => 'integer',
        'resource_id' => 'integer',
        'before_json' => 'array',
        'after_json' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
