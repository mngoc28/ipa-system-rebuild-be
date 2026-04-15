<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

final class IpaFileVersionSeeder extends Seeder
{
    public function run(): void
    {
        if (DB::table('ipa_file_version')->exists()) {
            return;
        }

        DB::table('ipa_file_version')->insert([
                'file_id' => DB::table('ipa_file')->value('id'),
                'version_no' => 1,
                'storage_key' => 'storage_key_seed',
                'size_bytes' => 1,
                'updated_by' => DB::table('ipa_user')->value('id'),
                'updated_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
