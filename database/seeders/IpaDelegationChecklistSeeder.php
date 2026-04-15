<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

final class IpaDelegationChecklistSeeder extends Seeder
{
    public function run(): void
    {
        if (DB::table('ipa_delegation_checklist')->exists()) {
            return;
        }

        DB::table('ipa_delegation_checklist')->insert([
                'delegation_id' => DB::table('ipa_delegation')->value('id'),
                'item_name' => 'item_name_seed',
                'assignee_user_id' => DB::table('ipa_user')->value('id'),
                'due_date' => now()->toDateString(),
                'status' => 1,
                'priority' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
