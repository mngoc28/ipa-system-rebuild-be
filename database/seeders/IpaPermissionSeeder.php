<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

final class IpaPermissionSeeder extends Seeder
{
    public function run(): void
    {
        if (DB::table('ipa_permission')->exists()) {
            return;
        }

        DB::table('ipa_permission')->insert([
                'code' => 'IPA_PERMISSION_CODE',
                'module' => 'module_seed',
                'action' => 'action_seed',
                'name' => 'name_seed',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
