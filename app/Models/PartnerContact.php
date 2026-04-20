<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Class PartnerContact
 *
 * Represents a contact person associated with an investment partner.
 *
 * @package App\Models
 *
 * @property int $id
 * @property int $partner_id
 * @property string $full_name
 * @property string|null $position
 * @property string|null $email
 * @property string|null $phone
 * @property string|null $notes
 * @property bool $is_primary Whether this is the main contact for the partner.
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
final class PartnerContact extends Model
{
    use HasFactory;

    protected $table = 'ipa_partner_contact';

    protected $guarded = [];

    protected $casts = [
        'partner_id' => 'integer',
        'is_primary' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
