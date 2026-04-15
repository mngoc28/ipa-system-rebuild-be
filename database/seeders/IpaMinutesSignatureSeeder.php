<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

final class IpaMinutesSignatureSeeder extends Seeder
{
    public function run(): void
    {
        if (DB::table('ipa_minutes_signature')->exists()) {
            return;
        }

        DB::table('ipa_minutes_signature')->insert([
                'minutes_id' => DB::table('ipa_minutes')->value('id'),
                'signer_user_id' => DB::table('ipa_user')->value('id'),
                'signer_name' => 'signer_name_seed',
                'signer_role' => 'signer_role_seed',
                'signature_file_id' => DB::table('ipa_file')->value('id'),
                'signed_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
