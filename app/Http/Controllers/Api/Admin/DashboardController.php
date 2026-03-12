<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Department;
use App\Models\Subject;
use App\Models\ClassModel;
use App\Models\Schedule;
use App\Models\Quiz;
use App\Models\Announcement;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function stats()
    {
        try {
            $stats = [
                'users_count' => User::count(),
                'teachers_count' => User::whereHas('role', function($q) {
                    $q->where('name', 'guru');
                })->count(),
                'students_count' => User::whereHas('role', function($q) {
                    $q->where('name', 'siswa');
                })->count(),
                'departments_count' => Department::count(),
                'subjects_count' => Subject::count(),
                'classes_count' => ClassModel::count(),
                'schedules_count' => Schedule::count(),
                'quizzes_count' => Quiz::count(),
                'students_per_class' => ClassModel::withCount('students')
                    ->get()
                    ->map(function($class) {
                        return [
                            'name' => $class->grade_level . ' ' . $class->name,
                            'count' => $class->students_count
                        ];
                    }),
                'latest_announcements' => Announcement::with('user')
                    ->where('is_active', true)
                    ->latest()
                    ->limit(5)
                    ->get(),
            ];

            return response()->json([
                'success' => true,
                'data' => $stats
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil statistik dashboard',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
