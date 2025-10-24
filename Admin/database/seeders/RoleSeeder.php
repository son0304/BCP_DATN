<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = ['Admin', 'Manager', 'Customer'];
        foreach ($roles as $name) {
            Role::firstOrCreate(
                ['name' => $name],
                ['description' => $name . ' role']
            );
        }
    }
}
