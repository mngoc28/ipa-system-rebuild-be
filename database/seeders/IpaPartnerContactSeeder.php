<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

final class IpaPartnerContactSeeder extends Seeder
{
    public function run(): void
    {
        $partners = DB::table('ipa_partner')->pluck('id', 'partner_code')->all();

        $contacts = [
            ['partner_code' => 'PARTNER-FPT-01', 'full_name' => 'Nguyễn Văn Minh', 'title' => 'Giám đốc đối ngoại', 'email' => 'minh.nguyen@fpt.com.vn', 'phone' => '0901110001', 'is_primary' => true],
            ['partner_code' => 'PARTNER-SAMSUNG-01', 'full_name' => 'Park Ji-hoon', 'title' => 'Head of Partnerships', 'email' => 'jihoon.park@samsung.com', 'phone' => '0901110002', 'is_primary' => true],
            ['partner_code' => 'PARTNER-INTEL-01', 'full_name' => 'Sarah Johnson', 'title' => 'Site Development Manager', 'email' => 'sarah.johnson@intel.com', 'phone' => '0901110003', 'is_primary' => true],
            ['partner_code' => 'PARTNER-TOYOTA-01', 'full_name' => 'Takashi Sato', 'title' => 'Supply Chain Director', 'email' => 'takashi.sato@toyota.com.vn', 'phone' => '0901110004', 'is_primary' => true],
            ['partner_code' => 'PARTNER-FOXCONN-01', 'full_name' => 'Chen Wei', 'title' => 'Operations Lead', 'email' => 'chen.wei@foxconn.com', 'phone' => '0901110005', 'is_primary' => true],
            ['partner_code' => 'PARTNER-BOSCH-01', 'full_name' => 'Markus Weber', 'title' => 'Regional Sales Director', 'email' => 'markus.weber@bosch.com.vn', 'phone' => '0901110006', 'is_primary' => true],
        ];

        foreach ($contacts as $contact) {
            $partnerId = $partners[$contact['partner_code']] ?? null;

            if ($partnerId === null) {
                continue;
            }

            DB::table('ipa_partner_contact')->updateOrInsert(
                [
                    'partner_id' => $partnerId,
                    'email' => $contact['email'],
                ],
                [
                    'full_name' => $contact['full_name'],
                    'title' => $contact['title'],
                    'phone' => $contact['phone'],
                    'is_primary' => $contact['is_primary'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }
}
