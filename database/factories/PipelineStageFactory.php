<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\PipelineStage;
use Illuminate\Database\Eloquent\Factories\Factory;

final class PipelineStageFactory extends Factory
{
    protected $model = PipelineStage::class;

    public function definition(): array
    {
        return [
            'code' => $this->faker->unique()->bothify('STAGE_##'),
            'name_vi' => $this->faker->randomElement([
                'Tiềm năng',
                'Đã liên hệ',
                'Đề xuất/Báo giá',
                'Thương thảo',
                'Thành công',
                'Thất bại',
            ]),
            'stage_order' => $this->faker->numberBetween(1, 10),
            'is_active' => true,
        ];
    }
}