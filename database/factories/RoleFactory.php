<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Role;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

final class RoleFactory extends Factory
{
    protected $model = Role::class;

    public function definition(): array
    {
        $name = $this->faker->randomElement([
            'Quản trị hệ thống',
            'Lãnh đạo',
            'Chuyên viên',
            'Đối tác',
        ]);

        return [
            'code' => 'ROLE_' . strtoupper(Str::slug($name)),
            'name' => $name,
            'is_system' => true,
        ];
    }
}