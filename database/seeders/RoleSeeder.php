<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            [
                'name' => 'admin',
                'display_name' => 'Administrator',
                'description' => 'Full system access and management'
            ],
            [
                'name' => 'guru',
                'display_name' => 'Guru',
                'description' => 'Teacher with access to teaching materials and student management'
            ],
            [
                'name' => 'siswa',
                'display_name' => 'Siswa',
                'description' => 'Student with access to learning materials and assignments'
            ],
        ];

        foreach ($roles as $role) {
            Role::create($role);
        }
    }
}
