<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

final class IpaPipelineProjectSeeder extends Seeder
{
    public function run(): void
    {
        $partnerIds = DB::table('ipa_partner')->pluck('id')->all();
        $countryIds = DB::table('ipa_country')->whereIn('code', ['COUNTRY_VIETNAM', 'COUNTRY_JAPAN', 'COUNTRY_KOREA', 'COUNTRY_SINGAPORE'])->pluck('id', 'code');
        $sectorIds = DB::table('ipa_md_sector')->whereIn('code', ['SECTOR_IT', 'SECTOR_LOGISTICS', 'SECTOR_FINTECH', 'SECTOR_RENEWABLE'])->pluck('id', 'code');
        $userId = DB::table('ipa_user')->value('id');
        $stages = DB::table('ipa_md_pipeline_stage')->orderBy('stage_order')->pluck('id')->all();
        $leadStageId = $stages[0] ?? null;
        $contactedStageId = $stages[1] ?? ($stages[0] ?? null);
        $proposalStageId = $stages[2] ?? ($stages[0] ?? null);
        $negotiationStageId = $stages[3] ?? ($stages[0] ?? null);
        $closedWonStageId = $stages[4] ?? ($stages[0] ?? null);

        $projects = [
            [
                'project_code' => 'CITY-PIPE-2026-IT-01',
                'project_name' => 'Da Nang Semiconductor Assembly Hub',
                'partner_id' => $partnerIds[0] ?? null,
                'country_id' => $countryIds['COUNTRY_JAPAN'] ?? null,
                'sector_id' => $sectorIds['SECTOR_IT'] ?? null,
                'stage_id' => $leadStageId,
                'delegation_id' => null,
                'estimated_value' => 18000000000,
                'success_probability' => 32,
                'expected_close_date' => now()->addMonths(6)->toDateString(),
                'owner_user_id' => $userId,
                'status' => 1,
            ],
            [
                'project_code' => 'CITY-PIPE-2026-LOG-01',
                'project_name' => 'Port Logistics Digital Gateway',
                'partner_id' => $partnerIds[1] ?? ($partnerIds[0] ?? null),
                'country_id' => $countryIds['COUNTRY_SINGAPORE'] ?? null,
                'sector_id' => $sectorIds['SECTOR_LOGISTICS'] ?? null,
                'stage_id' => $contactedStageId,
                'delegation_id' => null,
                'estimated_value' => 7200000000,
                'success_probability' => 48,
                'expected_close_date' => now()->addMonths(4)->toDateString(),
                'owner_user_id' => $userId,
                'status' => 1,
            ],
            [
                'project_code' => 'CITY-PIPE-2026-FIN-01',
                'project_name' => 'Smart Payment Corridor',
                'partner_id' => $partnerIds[2] ?? ($partnerIds[0] ?? null),
                'country_id' => $countryIds['COUNTRY_KOREA'] ?? null,
                'sector_id' => $sectorIds['SECTOR_FINTECH'] ?? null,
                'stage_id' => $proposalStageId,
                'delegation_id' => null,
                'estimated_value' => 6200000000,
                'success_probability' => 61,
                'expected_close_date' => now()->addMonths(3)->toDateString(),
                'owner_user_id' => $userId,
                'status' => 1,
            ],
            [
                'project_code' => 'CITY-PIPE-2026-GREEN-01',
                'project_name' => 'Renewable Energy Operations Center',
                'partner_id' => $partnerIds[3] ?? ($partnerIds[0] ?? null),
                'country_id' => $countryIds['COUNTRY_VIETNAM'] ?? null,
                'sector_id' => $sectorIds['SECTOR_RENEWABLE'] ?? null,
                'stage_id' => $negotiationStageId,
                'delegation_id' => null,
                'estimated_value' => 9100000000,
                'success_probability' => 79,
                'expected_close_date' => now()->addMonths(2)->toDateString(),
                'owner_user_id' => $userId,
                'status' => 1,
            ],
            [
                'project_code' => 'CITY-PIPE-2026-WON-01',
                'project_name' => 'Smart City Infrastructure',
                'partner_id' => $partnerIds[4] ?? ($partnerIds[0] ?? null),
                'country_id' => $countryIds['COUNTRY_SINGAPORE'] ?? null,
                'sector_id' => $sectorIds['SECTOR_IT'] ?? null,
                'stage_id' => $closedWonStageId,
                'delegation_id' => null,
                'estimated_value' => 25000000000,
                'success_probability' => 100,
                'expected_close_date' => now()->subDays(10)->toDateString(),
                'owner_user_id' => $userId,
                'status' => 1,
            ],
        ];

        foreach ($projects as $project) {
            if (DB::table('ipa_pipeline_project')->where('project_code', $project['project_code'])->exists()) {
                continue;
            }

            DB::table('ipa_pipeline_project')->insert([
                ...$project,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
