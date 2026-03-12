<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\{AcademicYear, Department, Subject};

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        // Create Academic Year
        AcademicYear::create([
            'year' => '2025/2026',
            'start_date' => '2025-07-01',
            'end_date' => '2026-06-30',
            'is_active' => true,
        ]);

        // Create Departments
        $departments = [
            ['name' => 'Ilmu Pengetahuan Alam', 'code' => 'IPA', 'description' => 'Jurusan IPA'],
            ['name' => 'Ilmu Pengetahuan Sosial', 'code' => 'IPS', 'description' => 'Jurusan IPS'],
            ['name' => 'Ilmu Keagamaan', 'code' => 'AGAMA', 'description' => 'Jurusan Keagamaan'],
        ];

        foreach ($departments as $dept) {
            Department::create($dept);
        }

        // Create Subjects
        $subjects = [
            ['name' => 'Matematika', 'code' => 'MAT', 'credit_hours' => 4],
            ['name' => 'Bahasa Indonesia', 'code' => 'BIN', 'credit_hours' => 4],
            ['name' => 'Bahasa Inggris', 'code' => 'BING', 'credit_hours' => 3],
            ['name' => 'Fisika', 'code' => 'FIS', 'credit_hours' => 4],
            ['name' => 'Kimia', 'code' => 'KIM', 'credit_hours' => 4],
            ['name' => 'Biologi', 'code' => 'BIO', 'credit_hours' => 4],
            ['name' => 'Sejarah', 'code' => 'SEJ', 'credit_hours' => 2],
            ['name' => 'Geografi', 'code' => 'GEO', 'credit_hours' => 3],
            ['name' => 'Ekonomi', 'code' => 'EKO', 'credit_hours' => 4],
            ['name' => 'Sosiologi', 'code' => 'SOS', 'credit_hours' => 3],
            ['name' => 'Al-Quran Hadits', 'code' => 'QH', 'credit_hours' => 2],
            ['name' => 'Akidah Akhlak', 'code' => 'AA', 'credit_hours' => 2],
            ['name' => 'Fiqih', 'code' => 'FQH', 'credit_hours' => 2],
            ['name' => 'Bahasa Arab', 'code' => 'ARAB', 'credit_hours' => 2],
        ];

        foreach ($subjects as $subject) {
            Subject::create($subject);
        }
    }
}
