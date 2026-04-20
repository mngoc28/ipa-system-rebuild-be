<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Country;
use App\Models\Location;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

final class IpaLocationSeeder extends Seeder
{
    public function run(): void
    {
        if (DB::table('ipa_location')->exists()) {
            return;
        }

        $countryId = Country::query()->where('code', 'COUNTRY_VIETNAM')->value('id');

        if (! $countryId) {
            return;
        }

        $locations = [
            ['name' => 'Khu CNC Đà Nẵng', 'address_line' => 'Đường Hoàng Văn Thái', 'ward' => 'Hòa Hiệp Bắc', 'district' => 'Liên Chiểu', 'lat' => 16.0912311, 'lng' => 108.1412211],
            ['name' => 'Furama Resort', 'address_line' => '103 Võ Nguyên Giáp', 'ward' => 'Mỹ An', 'district' => 'Ngũ Hành Sơn', 'lat' => 16.0352211, 'lng' => 108.2481133],
            ['name' => 'UBND TP Đà Nẵng', 'address_line' => '24 Trần Phú', 'ward' => 'Thạch Thang', 'district' => 'Hải Châu', 'lat' => 16.0746211, 'lng' => 108.2221122],
            ['name' => 'Trung tâm Hành chính Đà Nẵng', 'address_line' => '24 Trần Phú', 'ward' => 'Thạch Thang', 'district' => 'Hải Châu', 'lat' => 16.0749911, 'lng' => 108.2231122],
            ['name' => 'Sân bay quốc tế Đà Nẵng', 'address_line' => 'Đường Duy Tân', 'ward' => 'Hòa Thuận Tây', 'district' => 'Hải Châu', 'lat' => 16.0432211, 'lng' => 108.1992211],
            ['name' => 'Cảng Tiên Sa', 'address_line' => 'P. Thọ Quang', 'ward' => 'Thọ Quang', 'district' => 'Sơn Trà', 'lat' => 16.1082211, 'lng' => 108.2642211],
            ['name' => 'InterContinental Danang Sun Peninsula', 'address_line' => 'Bán đảo Sơn Trà', 'ward' => 'Thọ Quang', 'district' => 'Sơn Trà', 'lat' => 16.1242211, 'lng' => 108.2982211],
            ['name' => 'Novotel Danang Premier Han River', 'address_line' => '36 Bạch Đằng', 'ward' => 'Thạch Thang', 'district' => 'Hải Châu', 'lat' => 16.0782211, 'lng' => 108.2232211],
            ['name' => 'Mikazuki Japanese Resorts & Spa', 'address_line' => 'Nguyễn Tất Thành', 'ward' => 'Hòa Hiệp Nam', 'district' => 'Liên Chiểu', 'lat' => 16.1042211, 'lng' => 108.1202211],
            ['name' => 'Công viên phần mềm Đà Nẵng', 'address_line' => '15 Quang Trung', 'ward' => 'Thạch Thang', 'district' => 'Hải Châu', 'lat' => 16.0692211, 'lng' => 108.2192211],
            ['name' => 'Cầu Rồng - Trung tâm sự kiện', 'address_line' => 'Đường Nguyễn Văn Linh', 'ward' => 'Phước Ninh', 'district' => 'Hải Châu', 'lat' => 16.0602211, 'lng' => 108.2282211],
            ['name' => 'Bà Nà Hills', 'address_line' => 'Xã Hòa Ninh', 'ward' => 'Hòa Ninh', 'district' => 'Hòa Vang', 'lat' => 15.9952211, 'lng' => 107.9982211],
            ['name' => 'Khu đô thị FPT City', 'address_line' => 'Hòa Hải', 'ward' => 'Hòa Hải', 'district' => 'Ngũ Hành Sơn', 'lat' => 16.0212211, 'lng' => 108.2592211],
            ['name' => 'Khu công nghiệp Hòa Khánh', 'address_line' => 'Hòa Khánh Bắc', 'ward' => 'Hòa Khánh Bắc', 'district' => 'Liên Chiểu', 'lat' => 16.1112211, 'lng' => 108.1502211],
            ['name' => 'Khu công nghiệp Liên Chiểu', 'address_line' => 'Hòa Hiệp Bắc', 'ward' => 'Hòa Hiệp Bắc', 'district' => 'Liên Chiểu', 'lat' => 16.1212211, 'lng' => 108.1442211],
            ['name' => 'Sun World Ba Na Hills', 'address_line' => 'Hòa Ninh', 'ward' => 'Hòa Ninh', 'district' => 'Hòa Vang', 'lat' => 15.9932211, 'lng' => 107.9992211],
            ['name' => 'UBND quận Sơn Trà', 'address_line' => '11 Mai Hắc Đế', 'ward' => 'An Hải Tây', 'district' => 'Sơn Trà', 'lat' => 16.0672211, 'lng' => 108.2462211],
            ['name' => 'Trung tâm Xúc tiến Du lịch', 'address_line' => '1020 Ngô Quyền', 'ward' => 'An Hải Bắc', 'district' => 'Sơn Trà', 'lat' => 16.0822211, 'lng' => 108.2352211],
            ['name' => 'Cảng hàng không Nội Bài - phòng họp đối tác', 'address_line' => 'Phòng chờ hợp tác', 'ward' => 'Ngoài thành phố', 'district' => 'Khác', 'lat' => 16.0432211, 'lng' => 108.1992211],
            ['name' => 'Khu đô thị ven sông Hàn', 'address_line' => 'Ven sông Hàn', 'ward' => 'Phước Ninh', 'district' => 'Hải Châu', 'lat' => 16.0712211, 'lng' => 108.2242211],
        ];

        foreach ($locations as $location) {
            Location::factory()->create([
                ...$location,
                'country_id' => $countryId,
            ]);
        }
    }
}
