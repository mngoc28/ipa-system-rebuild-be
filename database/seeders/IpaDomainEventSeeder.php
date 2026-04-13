<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

final class IpaDomainEventSeeder extends Seeder
{
    public function run(): void
    {
        if (DB::table('ipa_domain_event')->exists()) {
            return;
        }

        DB::table('ipa_domain_event')->insert([
                'event_name' => 'event_name_seed',
                'aggregate_type' => 'aggregate_type_seed',
                'aggregate_id' => 1,
                'payload_json' => json_encode(['seed' => true]),
                'occurred_at' => now(),
                'published_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
