<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\District;
use App\Models\Province;

class DistrictSeeder extends Seeder
{
    public function run()
    {
        $provinces = Province::all();

        foreach ($provinces as $province) {
            District::factory()->count(5)->create([
                'province_id' => $province->id
            ]); // mỗi tỉnh 5 quận/huyện
        }
    }
}