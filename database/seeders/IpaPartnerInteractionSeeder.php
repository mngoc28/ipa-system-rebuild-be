<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

final class IpaPartnerInteractionSeeder extends Seeder
{
    public function run(): void
    {
        $partners = DB::table('ipa_partner')->pluck('id', 'partner_code')->all();
        $ownerUserIds = DB::table('ipa_user')->orderBy('id')->pluck('id')->all();

        $interactions = [
            ['partner_code' => 'PARTNER-FPT-01', 'interaction_type' => 1, 'summary' => 'Trao đổi nhu cầu mở rộng trung tâm chuyển đổi số tại Đà Nẵng.', 'days_ago' => 18],
            ['partner_code' => 'PARTNER-SAMSUNG-01', 'interaction_type' => 2, 'summary' => 'Cập nhật tiến độ khảo sát khu công nghệ cao và chuỗi cung ứng.', 'days_ago' => 12],
            ['partner_code' => 'PARTNER-INTEL-01', 'interaction_type' => 1, 'summary' => 'Thảo luận yêu cầu hạ tầng cho giai đoạn đầu tư tiếp theo.', 'days_ago' => 9],
            ['partner_code' => 'PARTNER-BOSCH-01', 'interaction_type' => 3, 'summary' => 'Gửi tài liệu giới thiệu chính sách và quỹ hỗ trợ.', 'days_ago' => 6],
            ['partner_code' => 'PARTNER-FOXCONN-01', 'interaction_type' => 2, 'summary' => 'Theo dõi phản hồi về vị trí nhà máy và logistics.', 'days_ago' => 3],
        ];

        foreach ($interactions as $index => $interaction) {
            $partnerId = $partners[$interaction['partner_code']] ?? null;
            $ownerUserId = $ownerUserIds[$index % count($ownerUserIds)] ?? null;

            if ($partnerId === null || $ownerUserId === null) {
                continue;
            }

            DB::table('ipa_partner_interaction')->updateOrInsert(
                [
                    'partner_id' => $partnerId,
                    'interaction_type' => $interaction['interaction_type'],
                    'summary' => $interaction['summary'],
                ],
                [
                    'interaction_at' => now()->subDays($interaction['days_ago']),
                    'owner_user_id' => $ownerUserId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }
}
