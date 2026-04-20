<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\AdminUser;
use App\Models\OrgUnit;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

final class IpaUserSeeder extends Seeder
{
    public function run(): void
    {
        if (DB::table('ipa_user')->exists()) {
            return;
        }

        $primaryUnitIds = OrgUnit::orderBy('id')->pluck('id')->all();

        if ($primaryUnitIds === []) {
            return;
        }

        $seedUsers = [
            [
                'username' => 'admin',
                'email' => 'admin@ipa-danang.gov.vn',
                'full_name' => 'Nguyễn Văn Quản Trị',
                'phone' => '0905000001',
            ],
            [
                'username' => 'director',
                'email' => 'director@ipa-danang.gov.vn',
                'full_name' => 'Hồ Kỳ Minh',
                'phone' => '0905000002',
            ],
            [
                'username' => 'manager',
                'email' => 'manager@ipa-danang.gov.vn',
                'full_name' => 'Nguyễn Minh Châu',
                'phone' => '0905000003',
            ],
            [
                'username' => 'staff',
                'email' => 'staff@ipa-danang.gov.vn',
                'full_name' => 'Trần Thu Hà',
                'phone' => '0905000004',
            ],
        ];

        $staffProfiles = [
            ['Nguyễn Văn An', 'nv.an', '0905000011'],
            ['Trần Thị Bích', 'tt.bich', '0905000012'],
            ['Lê Minh Tuấn', 'lm.tuan', '0905000013'],
            ['Phạm Quốc Khánh', 'pq.khanh', '0905000014'],
            ['Võ Thị Hồng', 'vt.hong', '0905000015'],
            ['Đặng Anh Khoa', 'dakhoa', '0905000016'],
            ['Hoàng Thu Hà', 'ht.ha', '0905000017'],
            ['Bùi Thanh Sơn', 'btson', '0905000018'],
            ['Nguyễn Thảo Vy', 'nt.vy', '0905000019'],
            ['Trịnh Gia Huy', 'tghuy', '0905000020'],
            ['Lê Thị Mai', 'lt.mai', '0905000021'],
            ['Phan Đức Long', 'pdlong', '0905000022'],
            ['Dương Ngọc Anh', 'dna', '0905000023'],
            ['Cao Minh Quân', 'cm.quan', '0905000024'],
            ['Vũ Thanh Tâm', 'vttam', '0905000025'],
            ['Nguyễn Quang Huy', 'nqhuy', '0905000026'],
            ['Trần Gia Bảo', 'tgbao', '0905000027'],
            ['Lê Khánh Linh', 'lklinh', '0905000028'],
            ['Phạm Thị Lan Anh', 'ptlananh', '0905000029'],
            ['Đỗ Nhật Nam', 'dnam', '0905000030'],
            ['Huỳnh Bảo Trâm', 'hbtram', '0905000031'],
            ['Nguyễn Thành Đạt', 'ntdat', '0905000032'],
            ['Tạ Minh Châu', 'tmchau', '0905000033'],
            ['Lâm Gia Hân', 'lghan', '0905000034'],
            ['Phùng Đức Minh', 'pdminh', '0905000035'],
            ['Nguyễn Hữu Phúc', 'nhphuc', '0905000036'],
            ['Bùi Quỳnh Anh', 'bqanh', '0905000037'],
            ['Cao Thị Ngọc', 'ct.ngoc', '0905000038'],
            ['Lê Đức Anh', 'lda', '0905000039'],
            ['Trần Minh Hoàng', 'tmhoang', '0905000040'],
            ['Võ Hoàng Yến', 'vhyen', '0905000041'],
            ['Nguyễn Nhật Trường', 'njtruong', '0905000042'],
            ['Đinh Thị Phương', 'dt.phuong', '0905000043'],
            ['Phạm Gia Hân', 'pghan', '0905000044'],
            ['Huỳnh Quốc Việt', 'hqviet', '0905000045'],
            ['Trần Thị Hương', 'tthuong', '0905000046'],
        ];

        $createdUsers = [];

        foreach ($seedUsers as $index => $user) {
            $createdUsers[] = AdminUser::create([
                ...$user,
                'avatar_url' => null,
                'status' => 1,
                'primary_unit_id' => $primaryUnitIds[$index % count($primaryUnitIds)],
                'last_login_at' => now()->subDays($index),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        for ($index = 0; $index < 36; $index++) {
            [$fullName, $username, $phone] = $staffProfiles[$index];

            $createdUsers[] = AdminUser::factory()->create([
                'username' => $username,
                'email' => $username . '@ipa-danang.gov.vn',
                'full_name' => $fullName,
                'phone' => $phone,
                'primary_unit_id' => $primaryUnitIds[$index % count($primaryUnitIds)],
            ]);
        }

        $managerUnits = OrgUnit::orderBy('id')->take(6)->get();

        foreach ($managerUnits as $index => $unit) {
            if (! isset($createdUsers[$index])) {
                break;
            }

            $unit->update([
                'manager_user_id' => $createdUsers[$index]->id,
            ]);
        }
    }
}
