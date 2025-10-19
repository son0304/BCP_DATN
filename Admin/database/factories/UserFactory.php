<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Models\{Role, District, Province};

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $roleId = Role::inRandomOrder()->value('id') ?? Role::factory()->create()->id;
        $districtId = District::inRandomOrder()->value('id');
        $provinceId = Province::inRandomOrder()->value('id');

        return [
            'role_id' => $roleId,
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'password' => static::$password ??= Hash::make('password'),
            'phone' => fake()->phoneNumber(),
            'district_id' => $districtId,
            'province_id' => $provinceId,
            'lat' => fake()->latitude(10, 22),
            'lng' => fake()->longitude(104, 108),
            'is_active' => true,
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn(array $attributes) => [
            // no-op: column not present in schema
        ]);
    }
}
