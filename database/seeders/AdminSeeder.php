<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        $adminRole = Role::where('name', 'admin')->first();

        User::create([
            'role_id' => $adminRole->id,
            'name' => 'Administrator',
            'email' => 'admin@man1brebes.sch.id',
            'password' => Hash::make('admin123'),
            'is_active' => true,
        ]);
    }
}
