<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\OrgUnit;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

final class IpaOrgUnitSeeder extends Seeder
{
    public function run(): void
    {
        if (DB::table('ipa_org_unit')->exists()) {
            return;
        }

        $root = OrgUnit::factory()->create([
            'unit_code' => 'IPA-HQ',
            'unit_name' => 'Trung tâm Xúc tiến Đầu tư Đà Nẵng',
            'unit_type' => 'ROOT',
            'parent_unit_id' => null,
            'manager_user_id' => null,
        ]);

        $departments = [
            ['code' => 'IPA-FDI', 'name' => 'Phòng Xúc tiến FDI'],
            ['code' => 'IPA-SUPPORT', 'name' => 'Phòng Hỗ trợ Doanh nghiệp'],
            ['code' => 'IPA-ANALYSIS', 'name' => 'Phòng Phân tích Thị trường'],
            ['code' => 'IPA-ONE-STOP', 'name' => 'Phòng Dịch vụ Một cửa'],
            ['code' => 'IPA-ADMIN', 'name' => 'Phòng Hành chính - Tổng hợp'],
        ];

        $departmentIds = [];

        foreach ($departments as $department) {
            $unit = OrgUnit::factory()->create([
                'unit_code' => $department['code'],
                'unit_name' => $department['name'],
                'unit_type' => 'DEPARTMENT',
                'parent_unit_id' => $root->id,
                'manager_user_id' => null,
            ]);

            $departmentIds[] = $unit->id;
        }

        $teams = [
            ['Tổ công tác Hàn Quốc', 'TEAM', 0],
            ['Tổ công tác Nhật Bản', 'TEAM', 0],
            ['Tổ công tác Singapore', 'TEAM', 0],
            ['Tổ công tác Hoa Kỳ', 'TEAM', 0],
            ['Tổ công tác Công nghệ cao', 'TEAM', 0],
            ['Tổ công tác Bán dẫn', 'TEAM', 1],
            ['Tổ công tác Logistics', 'TEAM', 1],
            ['Tổ công tác Năng lượng tái tạo', 'TEAM', 1],
            ['Tổ công tác Chăm sóc Nhà đầu tư', 'TEAM', 1],
            ['Tổ công tác Pháp lý', 'TEAM', 1],
            ['Tổ công tác Dữ liệu', 'TEAM', 2],
            ['Tổ công tác Truyền thông', 'TEAM', 2],
            ['Tổ công tác Sự kiện', 'TEAM', 2],
            ['Tổ công tác Đối ngoại', 'TEAM', 2],
            ['Tổ công tác Hỗ trợ thủ tục', 'TEAM', 3],
            ['Tổ công tác Hạ tầng', 'TEAM', 3],
            ['Tổ công tác Đất đai', 'TEAM', 3],
            ['Tổ công tác Môi trường', 'TEAM', 4],
        ];

        foreach ($teams as [$name, $type, $departmentIndex]) {
            OrgUnit::factory()->create([
                'unit_code' => 'IPA-' . strtoupper(Str::slug($name)) . '-' . Str::random(4),
                'unit_name' => $name,
                'unit_type' => $type,
                'parent_unit_id' => $departmentIds[$departmentIndex],
                'manager_user_id' => null,
            ]);
        }
    }
}
