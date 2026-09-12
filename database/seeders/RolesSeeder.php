<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RolesSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            'super-admin',
            'institute-admin',
            'teacher',
            'accountant',
            'guardian',
        ];

        foreach ($roles as $role) {
            Role::create(['name' => $role]);
        }
    }
}
