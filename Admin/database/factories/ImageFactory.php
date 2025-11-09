<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Image;

class ImageFactory extends Factory
{
    protected $model = Image::class;

    public function definition(): array
    {
        return [
            'url' => 'https://picsum.photos/seed/' . $this->faker->unique()->word() . '/400/300',
            'description' => $this->faker->sentence(6),
            'is_primary' => false,
        ];
    }
}