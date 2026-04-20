<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\TaskComment;
use Illuminate\Database\Eloquent\Factories\Factory;

final class TaskCommentFactory extends Factory
{
    protected $model = TaskComment::class;

    public function definition(): array
    {
        return [
            'task_id' => 1,
            'commenter_user_id' => 1,
            'comment_text' => $this->faker->randomElement([
                'Đã rà soát hồ sơ và chờ phản hồi từ đối tác.',
                'Đã gửi công văn và cập nhật cho lãnh đạo.',
                'Cần bổ sung thêm tài liệu thuyết minh trước khi trình ký.',
                'Đã liên hệ xong, đang chờ xác nhận lịch làm việc.',
                'Nội dung đã phù hợp, có thể chuyển sang bước tiếp theo.',
                'Đề nghị phối hợp thêm với phòng chuyên môn để hoàn thiện.',
                'Tiến độ đang bám sát kế hoạch của đoàn công tác.',
                'Đã cập nhật biên bản và lưu ý các đầu việc còn tồn đọng.',
            ]),
        ];
    }
}