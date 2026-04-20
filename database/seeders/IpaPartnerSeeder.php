<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Country;
use App\Models\Partner;
use App\Models\Sector;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

final class IpaPartnerSeeder extends Seeder
{
    public function run(): void
    {
        if (DB::table('ipa_partner')->exists()) {
            return;
        }

        $countryMap = Country::query()->pluck('id', 'code')->all();
        $sectorMap = Sector::query()->pluck('id', 'code')->all();

        if ($countryMap === [] || $sectorMap === []) {
            return;
        }

        $partners = [
            ['partner_code' => 'PARTNER-FPT-01', 'partner_name' => 'FPT Corporation', 'country_code' => 'COUNTRY_VIETNAM', 'sector_code' => 'SECTOR_IT', 'status' => 1, 'score' => 4.80, 'website' => 'https://fpt.com.vn', 'notes' => 'Đối tác công nghệ và chuyển đổi số.'],
            ['partner_code' => 'PARTNER-SAMSUNG-01', 'partner_name' => 'Samsung Electronics', 'country_code' => 'COUNTRY_KOREA', 'sector_code' => 'SECTOR_IT', 'status' => 1, 'score' => 4.95, 'website' => 'https://www.samsung.com', 'notes' => 'Quan tâm mở rộng chuỗi cung ứng tại Đà Nẵng.'],
            ['partner_code' => 'PARTNER-INTEL-01', 'partner_name' => 'Intel Corporation', 'country_code' => 'COUNTRY_USA', 'sector_code' => 'SECTOR_IT', 'status' => 1, 'score' => 4.90, 'website' => 'https://www.intel.com', 'notes' => 'Mảng bán dẫn và công nghệ cao.'],
            ['partner_code' => 'PARTNER-NIDEC-01', 'partner_name' => 'Nidec Vietnam', 'country_code' => 'COUNTRY_JAPAN', 'sector_code' => 'SECTOR_IT', 'status' => 1, 'score' => 4.50, 'website' => 'https://www.nidec.com', 'notes' => 'Đang theo dõi nhu cầu mở rộng nhà máy.'],
            ['partner_code' => 'PARTNER-TOYOTA-01', 'partner_name' => 'Toyota Motor Vietnam', 'country_code' => 'COUNTRY_JAPAN', 'sector_code' => 'SECTOR_LOGISTICS', 'status' => 1, 'score' => 4.35, 'website' => 'https://www.toyota.com.vn', 'notes' => 'Hợp tác logistics và sản xuất.'],
            ['partner_code' => 'PARTNER-LG-01', 'partner_name' => 'LG Electronics', 'country_code' => 'COUNTRY_KOREA', 'sector_code' => 'SECTOR_IT', 'status' => 1, 'score' => 4.40, 'website' => 'https://www.lg.com', 'notes' => 'Đối tác công nghệ điện tử.'],
            ['partner_code' => 'PARTNER-DAIKIN-01', 'partner_name' => 'Daikin Industries', 'country_code' => 'COUNTRY_JAPAN', 'sector_code' => 'SECTOR_RENEWABLE', 'status' => 1, 'score' => 4.15, 'website' => 'https://www.daikin.com', 'notes' => 'Quan tâm mảng sản xuất xanh.'],
            ['partner_code' => 'PARTNER-PANASONIC-01', 'partner_name' => 'Panasonic Vietnam', 'country_code' => 'COUNTRY_JAPAN', 'sector_code' => 'SECTOR_IT', 'status' => 1, 'score' => 4.20, 'website' => 'https://www.panasonic.com', 'notes' => 'Liên quan chuỗi cung ứng thiết bị điện tử.'],
            ['partner_code' => 'PARTNER-MITSUBISHI-01', 'partner_name' => 'Mitsubishi Corporation', 'country_code' => 'COUNTRY_JAPAN', 'sector_code' => 'SECTOR_LOGISTICS', 'status' => 1, 'score' => 4.10, 'website' => 'https://www.mitsubishicorp.com', 'notes' => 'Khả năng hợp tác hạ tầng và logistics.'],
            ['partner_code' => 'PARTNER-AEON-01', 'partner_name' => 'AEON Vietnam', 'country_code' => 'COUNTRY_JAPAN', 'sector_code' => 'SECTOR_LOGISTICS', 'status' => 1, 'score' => 4.05, 'website' => 'https://www.aeon.com.vn', 'notes' => 'Hệ thống bán lẻ và logistics.'],
            ['partner_code' => 'PARTNER-SUNGROUP-01', 'partner_name' => 'Sun Group', 'country_code' => 'COUNTRY_VIETNAM', 'sector_code' => 'SECTOR_RENEWABLE', 'status' => 1, 'score' => 4.60, 'website' => 'https://sungroup.com.vn', 'notes' => 'Phát triển hạ tầng và du lịch.'],
            ['partner_code' => 'PARTNER-VINFAST-01', 'partner_name' => 'VinFast', 'country_code' => 'COUNTRY_VIETNAM', 'sector_code' => 'SECTOR_IT', 'status' => 1, 'score' => 4.55, 'website' => 'https://vinfastauto.com', 'notes' => 'Cơ hội chuỗi cung ứng công nghiệp hỗ trợ.'],
            ['partner_code' => 'PARTNER-SHOPEE-01', 'partner_name' => 'Shopee Vietnam', 'country_code' => 'COUNTRY_SINGAPORE', 'sector_code' => 'SECTOR_FINTECH', 'status' => 1, 'score' => 4.10, 'website' => 'https://shopee.vn', 'notes' => 'Quan tâm thương mại số và fintech.'],
            ['partner_code' => 'PARTNER-MABUCHI-01', 'partner_name' => 'Mabuchi Motor', 'country_code' => 'COUNTRY_JAPAN', 'sector_code' => 'SECTOR_IT', 'status' => 1, 'score' => 4.00, 'website' => 'https://www.mabuchi-motor.co.jp', 'notes' => 'Mở rộng nhà máy linh kiện.'],
            ['partner_code' => 'PARTNER-SUMITOMO-01', 'partner_name' => 'Sumitomo Corporation', 'country_code' => 'COUNTRY_JAPAN', 'sector_code' => 'SECTOR_LOGISTICS', 'status' => 1, 'score' => 4.30, 'website' => 'https://www.sumitomocorp.com', 'notes' => 'Quan tâm logistics và đô thị thông minh.'],
            ['partner_code' => 'PARTNER-MARUBENI-01', 'partner_name' => 'Marubeni Corporation', 'country_code' => 'COUNTRY_JAPAN', 'sector_code' => 'SECTOR_LOGISTICS', 'status' => 1, 'score' => 4.12, 'website' => 'https://www.marubeni.com', 'notes' => 'Đối tác hạ tầng và thương mại.'],
            ['partner_code' => 'PARTNER-BOSCH-01', 'partner_name' => 'Bosch Vietnam', 'country_code' => 'COUNTRY_GERMANY', 'sector_code' => 'SECTOR_IT', 'status' => 1, 'score' => 4.22, 'website' => 'https://www.bosch.com.vn', 'notes' => 'Công nghệ và sản xuất thông minh.'],
            ['partner_code' => 'PARTNER-SCHNEIDER-01', 'partner_name' => 'Schneider Electric', 'country_code' => 'COUNTRY_GERMANY', 'sector_code' => 'SECTOR_RENEWABLE', 'status' => 1, 'score' => 4.18, 'website' => 'https://www.se.com', 'notes' => 'Giải pháp năng lượng và tự động hóa.'],
            ['partner_code' => 'PARTNER-ABB-01', 'partner_name' => 'ABB', 'country_code' => 'COUNTRY_GERMANY', 'sector_code' => 'SECTOR_RENEWABLE', 'status' => 1, 'score' => 4.08, 'website' => 'https://global.abb', 'notes' => 'Quan tâm giải pháp điện và tự động hóa.'],
            ['partner_code' => 'PARTNER-FOXCONN-01', 'partner_name' => 'Foxconn Technology Group', 'country_code' => 'COUNTRY_TAIWAN', 'sector_code' => 'SECTOR_IT', 'status' => 1, 'score' => 4.44, 'website' => 'https://www.foxconn.com', 'notes' => 'Mở rộng sản xuất công nghệ cao.'],
            ['partner_code' => 'PARTNER-CANON-01', 'partner_name' => 'Canon Vietnam', 'country_code' => 'COUNTRY_JAPAN', 'sector_code' => 'SECTOR_IT', 'status' => 1, 'score' => 4.02, 'website' => 'https://www.canon.com.vn', 'notes' => 'Thiết bị và điện tử.'],
            ['partner_code' => 'PARTNER-HITACHI-01', 'partner_name' => 'Hitachi Asia', 'country_code' => 'COUNTRY_JAPAN', 'sector_code' => 'SECTOR_LOGISTICS', 'status' => 1, 'score' => 4.21, 'website' => 'https://www.hitachi.com', 'notes' => 'Hạ tầng và logistics.'],
            ['partner_code' => 'PARTNER-DENSO-01', 'partner_name' => 'Denso Vietnam', 'country_code' => 'COUNTRY_JAPAN', 'sector_code' => 'SECTOR_IT', 'status' => 1, 'score' => 4.16, 'website' => 'https://www.denso.com', 'notes' => 'Công nghiệp hỗ trợ ô tô.'],
        ];

        foreach ($partners as $partner) {
            $countryId = $countryMap[$partner['country_code']] ?? $countryMap['COUNTRY_VIETNAM'];
            $sectorId = $sectorMap[$partner['sector_code']] ?? $sectorMap['SECTOR_IT'];

            Partner::factory()->create([
                'partner_code' => $partner['partner_code'],
                'partner_name' => $partner['partner_name'],
                'country_id' => $countryId,
                'sector_id' => $sectorId,
                'status' => $partner['status'],
                'score' => $partner['score'],
                'website' => $partner['website'],
                'notes' => $partner['notes'],
            ]);
        }
    }
}
