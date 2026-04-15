<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

final class IpaFileSeeder extends Seeder
{
    public function run(): void
    {
        if (DB::table('ipa_file')->exists()) {
            return;
        }

        DB::table('ipa_file')->insert([
                'folder_id' => DB::table('ipa_folder')->value('id'),
                'file_name' => 'file_name_seed',
                'file_ext' => 'file_ext_seed',
                'mime_type' => 'mime_type_seed',
                'size_bytes' => 1,
                'storage_key' => 'storage_key_seed',
                'checksum' => 'checksum_seed',
                'uploaded_by' => DB::table('ipa_user')->value('id'),
                'delegation_id' => DB::table('ipa_delegation')->value('id'),
                'minutes_id' => DB::table('ipa_minutes')->value('id'),
                'task_id' => DB::table('ipa_task')->value('id'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
