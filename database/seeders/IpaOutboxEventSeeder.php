<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

final class IpaOutboxEventSeeder extends Seeder
{
    public function run(): void
    {
        if (DB::table('ipa_outbox_event')->exists()) {
            return;
        }

        DB::table('ipa_outbox_event')->insert([
                'event_type' => 'event_type_seed',
                'payload_json' => json_encode(['seed' => true]),
                'status' => 1,
                'retry_count' => 1,
                'next_retry_at' => now(),
                'created_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
