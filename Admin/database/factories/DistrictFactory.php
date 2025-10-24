<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class DistrictFactory extends Factory
{
    protected $model = \App\Models\District::class;

    public function definition()
    {
        return [
            'province_id' => \App\Models\Province::factory(), // Tự động tạo province nếu chưa có
            'name' => $this->faker->city,
            'code' => strtoupper($this->faker->bothify('??#')), // VD: Q1, D2
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}