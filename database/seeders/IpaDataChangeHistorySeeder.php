<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

final class IpaDataChangeHistorySeeder extends Seeder
{
    public function run(): void
    {
        if (DB::table('ipa_data_change_history')->exists()) {
            return;
        }

        DB::table('ipa_data_change_history')->insert([
                'table_name' => 'table_name_seed',
                'row_id' => 1,
                'operation' => 1,
                'diff_json' => json_encode(['seed' => true]),
                'changed_by' => DB::table('ipa_user')->value('id'),
                'changed_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
