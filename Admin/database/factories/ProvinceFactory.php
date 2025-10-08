<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class ProvinceFactory extends Factory
{
    protected $model = \App\Models\Province::class;

    public function definition()
    {
        return [
            'name' => $this->faker->state,
            'code' => strtoupper($this->faker->lexify('??')), // VD: HN, HCM
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}