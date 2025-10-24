<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\VenueType;

class VenueTypeSeeder extends Seeder
{
    public function run(): void
    {
        $types = ['Sân bóng đá', 'Sân cầu lông', 'Sân tennis'];
        foreach ($types as $name) {
            VenueType::firstOrCreate(
                ['name' => $name],
                ['description' => 'Loại sân: ' . $name]
            );
        }
    }
}
