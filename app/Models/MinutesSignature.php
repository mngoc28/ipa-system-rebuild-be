<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Class MinutesSignature
 *
 * Records digital signatures or sign-offs on meeting minutes.
 *
 * @package App\Models
 *
 * @property int $id
 * @property int $minutes_id
 * @property int $signer_user_id
 * @property int|null $signature_file_id Link to the signature image file.
 * @property string|null $signature_metadata Technical metadata for the signature.
 * @property \Illuminate\Support\Carbon|null $signed_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
final class MinutesSignature extends Model
{
    use HasFactory;

    protected $table = 'ipa_minutes_signature';

    protected $guarded = [];

    protected $casts = [
        'minutes_id' => 'integer',
        'signer_user_id' => 'integer',
        'signature_file_id' => 'integer',
        'signed_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
