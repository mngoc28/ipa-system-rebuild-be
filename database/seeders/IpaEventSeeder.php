<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

final class IpaEventSeeder extends Seeder
{
    public function run(): void
    {
        $delegationId = DB::table('ipa_delegation')->value('id');
        $locationId = DB::table('ipa_location')->value('id');
        $organizerId = DB::table('ipa_user')->where('username', 'staff')->value('id') ?? DB::table('ipa_user')->value('id');

        $events = [
            [
                'title' => 'Làm việc với Samsung Electronics',
                'description' => 'Buổi trao đổi về kế hoạch mở rộng đầu tư năm 2026.',
                'event_type' => 1,
                'status' => 0,
                'start_at' => now()->addDay()->setTime(9, 0),
                'end_at' => now()->addDay()->setTime(10, 30),
            ],
            [
                'title' => 'Thăm khu công nghệ cao',
                'description' => 'Khảo sát hạ tầng cùng đoàn công tác.',
                'event_type' => 2,
                'status' => 1,
                'start_at' => now()->addDays(2)->setTime(13, 30),
                'end_at' => now()->addDays(2)->setTime(15, 0),
            ],
            [
                'title' => 'Hội thảo xúc tiến đầu tư',
                'description' => 'Sự kiện giới thiệu chính sách và ưu đãi đầu tư.',
                'event_type' => 3,
                'status' => 0,
                'start_at' => now()->addDays(4)->setTime(8, 30),
                'end_at' => now()->addDays(4)->setTime(11, 0),
            ],
        ];

        foreach ($events as $event) {
            DB::table('ipa_event')->updateOrInsert(
                ['title' => $event['title']],
                [
                    'delegation_id' => $delegationId,
                    'title' => $event['title'],
                    'description' => $event['description'],
                    'event_type' => $event['event_type'],
                    'status' => $event['status'],
                    'start_at' => $event['start_at'],
                    'end_at' => $event['end_at'],
                    'location_id' => $locationId,
                    'organizer_user_id' => $organizerId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }
}
