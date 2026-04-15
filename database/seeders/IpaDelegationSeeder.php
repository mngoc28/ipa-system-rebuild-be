<?php

namespace Database\Seeders;

use App\Models\Delegation;
use App\Models\DelegationMember;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class IpaDelegationSeeder extends Seeder
{
    public function run(): void
    {
        $delegations = [
            [
                'code' => 'DEL-2026-001',
                'name' => 'FDI Semiconductor Survey - Samsung Korea',
                'direction' => 1, // Inbound
                'status' => 1, // Preparation
                'priority' => 3, // High
                'country_id' => 1, // Mock Korea
                'host_unit_id' => 1, // IPA
                'owner_user_id' => 1,
                'start_date' => '2026-04-20',
                'end_date' => '2026-04-25',
                'participant_count' => 5,
                'objective' => 'Survey High-Tech Park for semiconductor plant expansion.',
                'description' => 'A senior delegation from Samsung Electronics visiting for infrastructure evaluation.',
            ],
            [
                'code' => 'DEL-2026-002',
                'name' => 'Investment Promotion - Singapore Fintech Hub',
                'direction' => 2, // Outbound
                'status' => 2, // Ongoing
                'priority' => 2, // Medium
                'country_id' => 2, // Singapore
                'host_unit_id' => 1,
                'owner_user_id' => 1,
                'start_date' => '2026-05-10',
                'end_date' => '2026-05-15',
                'participant_count' => 3,
                'objective' => 'Promoting Da Nang Fintech policies in Singapore Hub.',
                'description' => 'Delegation from IPA and Department of Finance.',
            ],
            [
                'code' => 'DEL-2026-003',
                'name' => 'Logistics Partnership - Japan Port Authority',
                'direction' => 1, // Inbound
                'status' => 3, // Completed
                'priority' => 3,
                'country_id' => 3, // Japan
                'host_unit_id' => 1,
                'owner_user_id' => 1,
                'start_date' => '2026-03-01',
                'end_date' => '2026-03-05',
                'participant_count' => 8,
                'objective' => 'Signing MoU for Lien Chieu Port development.',
                'description' => 'Follow-up from 2025 strategic meeting.',
            ],
        ];

        foreach ($delegations as $data) {
            $delegation = Delegation::updateOrCreate(
                ['code' => $data['code']],
                $data
            );

            // Add mock members if new
            if ($delegation->wasRecentlyCreated) {
                DelegationMember::create([
                    'delegation_id' => $delegation->id,
                    'full_name' => 'John Doe',
                    'title' => 'CEO',
                    'organization_name' => 'Global Partner Ltd',
                    'contact_email' => 'ceo@example.com',
                ]);
            }
        }
    }
}
