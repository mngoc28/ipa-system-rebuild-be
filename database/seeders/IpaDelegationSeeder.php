<?php

namespace Database\Seeders;

use App\Models\AdminUser;
use App\Models\Country;
use App\Models\Delegation;
use App\Models\OrgUnit;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class IpaDelegationSeeder extends Seeder
{
    public function run(): void
    {
        if (DB::table('ipa_delegation')->exists()) {
            return;
        }

        $countryMap = Country::query()->pluck('id', 'code')->all();
        $unitIds = OrgUnit::orderBy('id')->pluck('id')->all();
        $userIds = AdminUser::orderBy('id')->pluck('id')->all();

        if ($countryMap === [] || $unitIds === [] || $userIds === []) {
            return;
        }

        $delegations = [
            ['code' => 'DEL-2026-001', 'name' => 'Đón đoàn Samsung Hàn Quốc', 'country_code' => 'COUNTRY_KOREA', 'direction' => 1, 'status' => 0, 'priority' => 3, 'start_date' => '2026-04-20', 'duration' => 4, 'participant_count' => 9, 'objective' => 'Khảo sát hạ tầng khu công nghệ cao và thảo luận nhu cầu mở rộng nhà máy.', 'description' => 'Đoàn cấp cao từ Samsung làm việc với IPA Đà Nẵng về chuỗi cung ứng và nhân lực.'],
            ['code' => 'DEL-2026-002', 'name' => 'Xúc tiến đầu tư Fintech Singapore', 'country_code' => 'COUNTRY_SINGAPORE', 'direction' => 2, 'status' => 1, 'priority' => 2, 'start_date' => '2026-05-10', 'duration' => 5, 'participant_count' => 7, 'objective' => 'Giới thiệu chính sách hỗ trợ doanh nghiệp công nghệ tài chính tại Đà Nẵng.', 'description' => 'Đoàn công tác ra nước ngoài để kết nối hệ sinh thái fintech và đổi mới sáng tạo.'],
            ['code' => 'DEL-2026-003', 'name' => 'Làm việc với đối tác Nhật Bản', 'country_code' => 'COUNTRY_JAPAN', 'direction' => 1, 'status' => 3, 'priority' => 3, 'start_date' => '2026-03-01', 'duration' => 3, 'participant_count' => 10, 'objective' => 'Trao đổi kế hoạch hợp tác công nghiệp hỗ trợ và logistics.', 'description' => 'Phối hợp với nhiều doanh nghiệp Nhật Bản đang tìm kiếm địa điểm đầu tư mới.'],
            ['code' => 'DEL-2026-004', 'name' => 'Khảo sát chuỗi cung ứng bán dẫn', 'country_code' => 'COUNTRY_USA', 'direction' => 1, 'status' => 0, 'priority' => 3, 'start_date' => '2026-06-08', 'duration' => 5, 'participant_count' => 8, 'objective' => 'Thảo luận yêu cầu hạ tầng cho các dự án bán dẫn và công nghệ cao.', 'description' => 'Tập trung vào nhu cầu data, logistics và nhân lực chất lượng cao.'],
            ['code' => 'DEL-2026-005', 'name' => 'Hội nghị xúc tiến logistics', 'country_code' => 'COUNTRY_VIETNAM', 'direction' => 2, 'status' => 1, 'priority' => 2, 'start_date' => '2026-07-15', 'duration' => 2, 'participant_count' => 6, 'objective' => 'Quảng bá hệ sinh thái logistics và cảng biển của Đà Nẵng.', 'description' => 'Chương trình làm việc với các nhà đầu tư trong nước và khu vực.'],
            ['code' => 'DEL-2026-006', 'name' => 'Trao đổi năng lượng tái tạo', 'country_code' => 'COUNTRY_GERMANY', 'direction' => 1, 'status' => 0, 'priority' => 2, 'start_date' => '2026-08-03', 'duration' => 4, 'participant_count' => 5, 'objective' => 'Thu hút dự án công nghệ xanh và giải pháp năng lượng sạch.', 'description' => 'Làm việc với nhóm đầu tư châu Âu về các dự án năng lượng tái tạo.'],
            ['code' => 'DEL-2026-007', 'name' => 'Hợp tác giáo dục Pháp', 'country_code' => 'COUNTRY_FRANCE', 'direction' => 1, 'status' => 0, 'priority' => 1, 'start_date' => '2026-09-12', 'duration' => 3, 'participant_count' => 4, 'objective' => 'Liên kết đào tạo nguồn nhân lực chất lượng cao.', 'description' => 'Đoàn các trường đại học Pháp khảo sát môi trường giáo dục.'],
            ['code' => 'DEL-2026-008', 'name' => 'Xúc tiến bán dẫn Đài Loan', 'country_code' => 'COUNTRY_TAIWAN', 'direction' => 1, 'status' => 0, 'priority' => 3, 'start_date' => '2026-10-05', 'duration' => 5, 'participant_count' => 12, 'objective' => 'Kêu gọi đầu tư vào hệ sinh thái chip và bán dẫn.', 'description' => 'Đoàn doanh nghiệp công nghệ hàng đầu từ Đài Loan.'],
            ['code' => 'DEL-2026-009', 'name' => 'Phát triển đô thị thông minh Hà Lan', 'country_code' => 'COUNTRY_NETHERLANDS', 'direction' => 1, 'status' => 0, 'priority' => 2, 'start_date' => '2026-11-20', 'duration' => 4, 'participant_count' => 6, 'objective' => 'Trao đổi kinh nghiệm và giải pháp Smart City.', 'description' => 'Chuyên gia Hà Lan tư vấn về hạ tầng đô thị bền vững.'],
            ['code' => 'DEL-2026-010', 'name' => 'Kế hoạch CNTT Ấn Độ', 'country_code' => 'COUNTRY_INDIA', 'direction' => 1, 'status' => 0, 'priority' => 2, 'start_date' => '2026-12-10', 'duration' => 3, 'participant_count' => 15, 'objective' => 'Mở rộng thị trường outsourcing và phần mềm.', 'description' => 'Kết nối doanh nghiệp CNTT Ấn Độ với các đối tác Đà Nẵng.'],
        ];

        foreach ($delegations as $delegationIndex => $data) {
            $countryId = $countryMap[$data['country_code']] ?? $countryMap['COUNTRY_VIETNAM'];
            $durationDays = $data['duration'];
            $startDate = Carbon::parse($data['start_date']);
            $endDate = $startDate->copy()->addDays($durationDays - 1)->format('Y-m-d');

            Delegation::factory()->create([
                'code' => $data['code'],
                'name' => $data['name'],
                'direction' => $data['direction'],
                'status' => $data['status'],
                'priority' => $data['priority'],
                'country_id' => $countryId,
                'host_unit_id' => $unitIds[$delegationIndex % count($unitIds)],
                'owner_user_id' => $userIds[$delegationIndex % count($userIds)],
                'start_date' => $startDate->format('Y-m-d'),
                'end_date' => $endDate,
                'participant_count' => $data['participant_count'],
                'objective' => $data['objective'],
                'description' => $data['description'],
            ]);
        }
    }
}
