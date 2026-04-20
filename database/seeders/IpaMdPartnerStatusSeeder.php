<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

final class IpaMdPartnerStatusSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            ['code' => 'LEAD', 'name_vi' => 'Lead'],
            ['code' => 'PARTNER', 'name_vi' => 'Partner'],
            ['code' => 'ACTIVE', 'name_vi' => 'Active'],
        ];

        foreach ($rows as $row) {
            DB::table('ipa_md_partner_status')->updateOrInsert(
                ['code' => $row['code']],
                [
                    'name_vi' => $row['name_vi'],
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }
}
