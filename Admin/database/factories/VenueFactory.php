<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\{User, District, Province};

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Venue>
 */
class VenueFactory extends Factory
{
    /**
     * Định nghĩa dữ liệu mặc định cho model Venue.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // Giờ bắt đầu ngẫu nhiên từ 5h đến 10h sáng
        $startTime = $this->faker->time('H:i', rand(strtotime('05:00'), strtotime('10:00')));

        // Giờ kết thúc ngẫu nhiên, sau giờ bắt đầu từ 8-12 tiếng
        $endTime = date('H:i', strtotime($startTime . ' +' . rand(8, 12) . ' hours'));

        return [
            'owner_id' => User::inRandomOrder()->first()?->id ?? User::factory(),
            'name' => $this->faker->company(),
            'address_detail' => $this->faker->address(),
            'district_id' => District::inRandomOrder()->first()?->id ?? District::factory(),
            'province_id' => Province::inRandomOrder()->first()?->id ?? Province::factory(),
            'lat' => $this->faker->latitude(),
            'lng' => $this->faker->longitude(),
            'phone' => $this->faker->phoneNumber(),
            'is_active' => $this->faker->boolean(90),
            'start_time' => $startTime,
            'end_time' => $endTime,
        ];
    }
}
