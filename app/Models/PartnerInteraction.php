<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Class PartnerInteraction
 *
 * Records an interaction or engagement with a partner (meeting, call, email, etc.).
 *
 * @package App\Models
 *
 * @property int $id
 * @property int $partner_id
 * @property int $interaction_type
 * @property \Illuminate\Support\Carbon $interaction_at
 * @property string|null $subject
 * @property string|null $content
 * @property int|null $owner_user_id User who performed the interaction.
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
final class PartnerInteraction extends Model
{
    use HasFactory;

    protected $table = 'ipa_partner_interaction';

    protected $guarded = [];

    protected $casts = [
        'partner_id' => 'integer',
        'interaction_type' => 'integer',
        'interaction_at' => 'datetime',
        'owner_user_id' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
