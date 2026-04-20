<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\AdminUser;
use App\Models\Country;
use App\Models\Delegation;
use App\Models\Event;
use App\Models\Location;
use App\Models\OrgUnit;
use App\Models\PipelineProject;
use App\Models\Task;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

final class IpaScopedDataSeeder extends Seeder
{
    public function run(): void
    {
        $targetUsernames = ['staff1', 'staff2', 'manager1'];
        $users = AdminUser::whereIn('username', $targetUsernames)->get();

        if ($users->isEmpty()) {
            $this->command->error('Target users (staff1, staff2, manager1) not found. Run IpaRbacFakeDataSeeder first.');
            return;
        }

        $countries = Country::limit(10)->get();
        $locations = Location::limit(5)->get();
        $units = OrgUnit::limit(5)->get();
        $pipelineStages = DB::table('ipa_md_pipeline_stage')->pluck('id')->all();
        $sectors = DB::table('ipa_md_sector')->pluck('id')->all();
        $partners = DB::table('ipa_partner')->limit(10)->get();

        $delegationPrefixes = ['Đoàn khảo sát', 'Xúc tiến đầu tư', 'Đàm phán hợp tác', 'Tìm hiểu thị trường', 'Trao đổi kỹ thuật'];
        $delegationSuffixes = ['Hàn Quốc', 'Nhật Bản', 'Hoa Kỳ', 'Singapore', 'Châu Âu', 'Tập đoàn Intel', 'Samsung Group', 'LG Electronics'];
        
        $projectPrefixes = ['Nhà máy sản xuất', 'Trung tâm R&D', 'Hệ thống Logistics', 'Khu công nghiệp xanh', 'Công viên phần mềm'];
        $projectSuffixes = ['Giai đoạn 2', 'Mở rộng quy mô', 'Chi nhánh miền Trung', 'Công nghệ cao'];

        foreach ($users as $user) {
            $this->command->info("Seeding data for user: {$user->username}");

            // 1. Delegations (25+ per user)
            for ($i = 1; $i <= 27; $i++) {
                $name = $delegationPrefixes[array_rand($delegationPrefixes)] . ' ' . $delegationSuffixes[array_rand($delegationSuffixes)] . " ($i)";
                $country = $countries->random();
                $startDate = Carbon::now()->addDays(rand(-30, 60));
                
                $delegation = Delegation::create([
                    'code' => 'DEL-2026-' . strtoupper(Str::random(4)) . '-' . str_pad((string)$i, 3, '0', STR_PAD_LEFT),
                    'name' => $name,
                    'direction' => rand(1, 2),
                    'status' => rand(1, 3),
                    'priority' => rand(1, 4),
                    'country_id' => $country->id,
                    'host_unit_id' => $user->primary_unit_id ?? $units->random()->id,
                    'owner_user_id' => $user->id,
                    'start_date' => $startDate->format('Y-m-d'),
                    'end_date' => $startDate->copy()->addDays(rand(2, 7))->format('Y-m-d'),
                    'participant_count' => rand(3, 15),
                    'objective' => "Mục tiêu: Thúc đẩy quan hệ hợp tác và tìm kiếm cơ hội đầu tư tại Đà Nẵng đối với lĩnh vực {$name}.",
                    'description' => "Đoàn công tác làm việc với IPA về các chính sách ưu đãi và hạ tầng tại khu công nghệ cao.",
                ]);

                // 2. Tasks for this delegation (some created by, some assigned to)
                for ($j = 1; $j <= 2; $j++) {
                    $task = Task::create([
                        'delegation_id' => $delegation->id,
                        'title' => "Chuẩn bị tài liệu cho $name - Bước $j",
                        'description' => "Cần rà soát kỹ các hồ sơ pháp lý và tài liệu thuyết minh cho đoàn.",
                        'status' => rand(0, 2),
                        'priority' => rand(1, 3),
                        'due_at' => Carbon::now()->addDays(rand(-5, 15)),
                        'created_by' => $user->id,
                    ]);

                    // Assign to user if not already considered assigned by creator
                    DB::table('ipa_task_assignee')->insert([
                        'task_id' => $task->id,
                        'user_id' => $user->id,
                        'assignment_type' => 1,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }

                // 3. Events for this delegation
                if ($i % 3 === 0) {
                    $eventStartAt = $startDate->copy()->addHours(9 + rand(0, 4));
                    Event::create([
                        'delegation_id' => $delegation->id,
                        'title' => "Phiên làm việc chính thức: $name",
                        'description' => "Trao đổi chi tiết về các cam kết đầu tư và hỗ trợ từ thành phố.",
                        'event_type' => rand(1, 5),
                        'status' => $eventStartAt->isPast() ? 1 : 0,
                        'start_at' => $eventStartAt,
                        'end_at' => (clone $eventStartAt)->addHours(2),
                        'location_id' => $locations->random()->id,
                        'organizer_user_id' => $user->id,
                    ]);
                }
            }

            // 4. Pipeline Projects (15+ per user)
            for ($k = 1; $k <= 16; $k++) {
                $projectName = $projectPrefixes[array_rand($projectPrefixes)] . ' ' . $projectSuffixes[array_rand($projectSuffixes)] . " ($k)";
                PipelineProject::create([
                    'project_code' => 'PIPE-' . date('Y') . '-' . strtoupper(Str::random(4)) . '-' . str_pad((string)$k, 3, '0', STR_PAD_LEFT),
                    'project_name' => $projectName,
                    'partner_id' => $partners->random()->id,
                    'country_id' => $countries->random()->id,
                    'sector_id' => $sectors[array_rand($sectors)],
                    'stage_id' => $pipelineStages[array_rand($pipelineStages)],
                    'estimated_value' => rand(10, 500) * 1000000000, // 10B to 500B VND
                    'success_probability' => rand(20, 90),
                    'expected_close_date' => Carbon::now()->addMonths(rand(1, 12))->format('Y-m-d'),
                    'owner_user_id' => $user->id,
                    'status' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        $this->command->info('Scoped test data seeded successfully!');
    }
}
