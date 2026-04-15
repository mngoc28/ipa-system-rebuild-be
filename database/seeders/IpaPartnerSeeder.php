<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

final class IpaPartnerSeeder extends Seeder
{
    public function run(): void
    {
        $countryIds = DB::table('ipa_country')->pluck('id')->toArray();
        $sectorIds = DB::table('ipa_md_sector')->pluck('id')->toArray();

        if (empty($countryIds) || empty($sectorIds)) {
            return;
        }

        $partners = [
            [
                'partner_code' => 'SAMSUNG_VN',
                'partner_name' => 'Samsung Electronics Việt Nam',
                'country_id' => $countryIds[0],
                'sector_id' => $sectorIds[0],
                'status' => 1,
                'score' => 4.50,
                'website' => 'https://www.samsung.com/vn/',
                'notes' => 'Đối tác chiến lược mảng điện tử.',
            ],
            [
                'partner_code' => 'TOYOTA_JP',
                'partner_name' => 'Toyota Motor Corporation',
                'country_id' => $countryIds[0],
                'sector_id' => $sectorIds[1],
                'status' => 1,
                'score' => 4.20,
                'website' => 'https://www.toyota.jp/',
                'notes' => 'Tìm hiểu cơ hội đầu tư nhà máy lắp ráp.',
            ],
            [
                'partner_code' => 'INTEL_US',
                'partner_name' => 'Intel Corporation',
                'country_id' => $countryIds[0],
                'sector_id' => $sectorIds[2],
                'status' => 1,
                'score' => 4.80,
                'website' => 'https://www.intel.com/',
                'notes' => 'Đối tác tiềm năng mảng bán dẫn.',
            ],
            [
                'partner_code' => 'LG_KR',
                'partner_name' => 'LG Electronics',
                'country_id' => $countryIds[0],
                'sector_id' => $sectorIds[0],
                'status' => 1,
                'score' => 4.10,
                'website' => 'https://www.lg.com/',
                'notes' => 'Quan tâm đến khu công nghiệp công nghệ cao.',
            ],
            [
                'partner_code' => 'DAIKIN_JP',
                'partner_name' => 'Daikin Industries',
                'country_id' => $countryIds[0],
                'sector_id' => $sectorIds[3],
                'status' => 1,
                'score' => 3.90,
                'website' => 'https://www.daikin.com/',
                'notes' => 'Đối tác mảng điện lạnh.',
            ],
            [
                'partner_code' => 'SIEMENS_DE',
                'partner_name' => 'Siemens AG',
                'country_id' => $countryIds[0],
                'sector_id' => $sectorIds[4],
                'status' => 1,
                'score' => 4.60,
                'website' => 'https://www.siemens.com/',
                'notes' => 'Hợp tác mảng hạ tầng năng lượng.',
            ],
            [
                'partner_code' => 'FOXCONN_TW',
                'partner_name' => 'Foxconn Technology Group',
                'country_id' => $countryIds[0],
                'sector_id' => $sectorIds[0],
                'status' => 1,
                'score' => 4.30,
                'website' => 'https://www.foxconn.com/',
                'notes' => 'Mở rộng quy mô sản xuất tại Đà Nẵng.',
            ],
            [
                'partner_code' => 'NESTLE_CH',
                'partner_name' => 'Nestlé S.A.',
                'country_id' => $countryIds[0],
                'sector_id' => $sectorIds[1],
                'status' => 1,
                'score' => 4.00,
                'website' => 'https://www.nestle.com/',
                'notes' => 'Mảng thực phẩm và đồ uống.',
            ],
            [
                'partner_code' => 'HONDA_JP',
                'partner_name' => 'Honda Motor Co., Ltd.',
                'country_id' => $countryIds[0],
                'sector_id' => $sectorIds[1],
                'status' => 1,
                'score' => 4.25,
                'website' => 'https://global.honda/',
                'notes' => 'Hợp tác mảng xe máy điện.',
            ],
            [
                'partner_code' => 'APPLE_US',
                'partner_name' => 'Apple Inc.',
                'country_id' => $countryIds[0],
                'sector_id' => $sectorIds[0],
                'status' => 0,
                'score' => 4.90,
                'website' => 'https://www.apple.com/',
                'notes' => 'Đang trong quá trình đàm phán chuỗi cung ứng.',
            ],
        ];

        foreach ($partners as $partner) {
            DB::table('ipa_partner')->updateOrInsert(
                ['partner_code' => $partner['partner_code']],
                array_merge($partner, [
                    'created_at' => now(),
                    'updated_at' => now(),
                ])
            );
        }
    }
}
