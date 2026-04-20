<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Delegation;
use App\Models\DelegationMember;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

final class IpaDelegationMemberSeeder extends Seeder
{
    public function run(): void
    {
        if (DB::table('ipa_delegation_member')->exists()) {
            return;
        }

        $delegations = Delegation::orderBy('id')->get();

        if ($delegations->isEmpty()) {
            return;
        }

        $names = [
            'Nguyễn Văn Minh',
            'Trần Thị Lan',
            'Lê Minh Tuấn',
            'Phạm Quốc Khánh',
            'Võ Thị Hồng',
            'Đặng Anh Khoa',
            'Hoàng Thu Hà',
            'Bùi Thanh Sơn',
            'Nguyễn Thảo Vy',
            'Trịnh Gia Huy',
            'Lê Thị Mai',
            'Phan Đức Long',
            'Dương Ngọc Anh',
            'Cao Minh Quân',
            'Vũ Thanh Tâm',
            'Nguyễn Quang Huy',
            'Trần Gia Bảo',
            'Lê Khánh Linh',
        ];

        $titles = ['Giám đốc', 'Phó giám đốc', 'Trưởng phòng', 'Chuyên viên', 'Cố vấn'];
        $organizations = ['FPT', 'Samsung', 'Intel', 'Nidec', 'UBND TP Đà Nẵng', 'Khu CNC Đà Nẵng', 'Sở Kế hoạch và Đầu tư', 'Hiệp hội doanh nghiệp'];

        foreach ($delegations as $delegationIndex => $delegation) {
            for ($index = 0; $index < 9; $index++) {
                $fullName = $names[($delegationIndex + $index) % count($names)];

                DelegationMember::factory()->create([
                    'delegation_id' => $delegation->id,
                    'full_name' => $fullName,
                    'title' => $titles[($delegationIndex + $index) % count($titles)],
                    'organization_name' => $organizations[($delegationIndex + $index) % count($organizations)],
                    'contact_email' => Str::slug($fullName) . '@ipa-danang.gov.vn',
                    'contact_phone' => '09' . str_pad((string) ($delegationIndex * 9 + $index + 1), 8, '0', STR_PAD_LEFT),
                    'member_type' => $index < 6 ? 0 : 1,
                ]);
            }
        }
    }
}
