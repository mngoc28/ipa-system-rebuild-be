<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

final class IpaFolderSeeder extends Seeder
{
    public function run(): void
    {
        if (DB::table('ipa_folder')->exists()) {
            return;
        }

        DB::table('ipa_folder')->insert([
                'parent_folder_id' => DB::table('ipa_folder')->value('id'),
                'folder_name' => 'folder_name_seed',
                'owner_user_id' => DB::table('ipa_user')->value('id'),
                'scope_type' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
