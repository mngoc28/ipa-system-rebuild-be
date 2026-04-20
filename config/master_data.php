<?php

return [
    'domains' => [
        'countries' => [
            ['id' => 'country_vn', 'code' => 'VN', 'name_vi' => 'Việt Nam', 'name_en' => 'Vietnam', 'sort_order' => 1, 'is_active' => true],
            ['id' => 'country_jp', 'code' => 'JP', 'name_vi' => 'Nhật Bản', 'name_en' => 'Japan', 'sort_order' => 2, 'is_active' => true],
            ['id' => 'country_kr', 'code' => 'KR', 'name_vi' => 'Hàn Quốc', 'name_en' => 'South Korea', 'sort_order' => 3, 'is_active' => true],
            ['id' => 'country_sg', 'code' => 'SG', 'name_vi' => 'Singapore', 'name_en' => 'Singapore', 'sort_order' => 4, 'is_active' => true],
        ],
        'delegation-types' => [
            ['id' => 'delegation_type_official', 'code' => 'OFFICIAL', 'name_vi' => 'Đoàn công tác chính thức', 'name_en' => 'Official delegation', 'sort_order' => 1, 'is_active' => true],
            ['id' => 'delegation_type_working', 'code' => 'WORKING', 'name_vi' => 'Đoàn làm việc', 'name_en' => 'Working delegation', 'sort_order' => 2, 'is_active' => true],
            ['id' => 'delegation_type_exchange', 'code' => 'EXCHANGE', 'name_vi' => 'Đoàn trao đổi', 'name_en' => 'Exchange delegation', 'sort_order' => 3, 'is_active' => true],
        ],
        'priorities' => [
            ['id' => 'priority_low', 'code' => 'LOW', 'name_vi' => 'Thấp', 'name_en' => 'Low', 'sort_order' => 1, 'is_active' => true],
            ['id' => 'priority_medium', 'code' => 'MEDIUM', 'name_vi' => 'Trung bình', 'name_en' => 'Medium', 'sort_order' => 2, 'is_active' => true],
            ['id' => 'priority_high', 'code' => 'HIGH', 'name_vi' => 'Cao', 'name_en' => 'High', 'sort_order' => 3, 'is_active' => true],
            ['id' => 'priority_urgent', 'code' => 'URGENT', 'name_vi' => 'Khẩn', 'name_en' => 'Urgent', 'sort_order' => 4, 'is_active' => true],
        ],
        'event-types' => [
            ['id' => 'event_type_meeting', 'code' => 'MEETING', 'name_vi' => 'Họp', 'name_en' => 'Meeting', 'sort_order' => 1, 'is_active' => true],
            ['id' => 'event_type_visit', 'code' => 'VISIT', 'name_vi' => 'Thăm hỏi', 'name_en' => 'Visit', 'sort_order' => 2, 'is_active' => true],
            ['id' => 'event_type_workshop', 'code' => 'WORKSHOP', 'name_vi' => 'Hội thảo', 'name_en' => 'Workshop', 'sort_order' => 3, 'is_active' => true],
            ['id' => 'event_type_ceremony', 'code' => 'CEREMONY', 'name_vi' => 'Lễ nghi', 'name_en' => 'Ceremony', 'sort_order' => 4, 'is_active' => true],
        ],
        'sector' => [],
        'location' => [],
    ],
];
