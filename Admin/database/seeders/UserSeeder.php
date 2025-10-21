<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\{User, District, Province, Role};
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $districtIds = District::pluck('id')->all();
        $provinceIds = Province::pluck('id')->all();

        // Admin user
        $adminRoleId = Role::where('name', 'Admin')->value('id');
        User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Admin',
                'password' => Hash::make('password'),
                'phone' => '0900000000',
                'role_id' => $adminRoleId,
                'district_id' => $districtIds ? $districtIds[0] : null,
                'province_id' => $provinceIds ? $provinceIds[0] : null,
                'lat' => 21.0278,
                'lng' => 105.8342,
                'is_active' => true,
            ]
        );

        // Random users
        User::factory()
            ->count(10)
            ->create();
    }
}
