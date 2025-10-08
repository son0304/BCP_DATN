<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Province;

class ProvinceSeeder extends Seeder
{
    public function run()
    {
        Province::factory()->count(5)->create(); // tạo 5 tỉnh
    }
}