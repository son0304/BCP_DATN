<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class DistrictFactory extends Factory
{
    protected $model = \App\Models\District::class;

    public function definition()
    {
        // Generate a unique code to avoid duplicates
        do {
            $code = strtoupper($this->faker->bothify('??#'));
        } while (\App\Models\District::where('code', $code)->exists());
        
        return [
            'province_id' => \App\Models\Province::factory(), // Tự động tạo province nếu chưa có
            'name' => $this->faker->city,
            'code' => $code,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}