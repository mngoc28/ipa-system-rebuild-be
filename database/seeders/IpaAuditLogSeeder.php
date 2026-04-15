<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

final class IpaAuditLogSeeder extends Seeder
{
    public function run(): void
    {
        if (DB::table('ipa_audit_log')->exists()) {
            return;
        }

        DB::table('ipa_audit_log')->insert([
                'actor_user_id' => DB::table('ipa_user')->value('id'),
                'action' => 'action_seed',
                'resource_type' => 'resource_type_seed',
                'resource_id' => null,
                'before_json' => json_encode(['seed' => true]),
                'after_json' => json_encode(['seed' => true]),
                'ip_address' => 'ip_address_seed',
                'user_agent' => 'user_agent seed text',
                'created_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
