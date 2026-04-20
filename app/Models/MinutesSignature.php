<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
