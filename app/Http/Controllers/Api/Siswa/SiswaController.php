<?php

namespace App\Http\Controllers\Api\Siswa;

use App\Http\Controllers\Controller;
use App\Models\Schedule;
use App\Models\Material;
use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\Grade;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

use Carbon\Carbon;

class SiswaController extends Controller
{
    public function getDashboardStats()
    {
        $studentId = Auth::id();
        $classId = DB::table('student_class')->where('student_id', $studentId)->value('class_id');

        if (!$classId) {
            return response()->json([
                'success' => true,
                'data' => [
                    'subjects_count' => 0,
                    'pending_assignments' => 0,
                    'completed_assignments' => 0,
                    'average_grade' => 0,
                    'upcoming_assignments' => [],
                    'recent_grades' => []
                ]
            ]);
        }

        // 1. Total Subjects
        $subjectsCount = Schedule::where('class_id', $classId)
            ->distinct('subject_id')
            ->count('subject_id');

        // 2. Assignments Stats
        $allAssignments = Assignment::where('class_id', $classId)->pluck('id');
        $submittedCount = AssignmentSubmission::where('student_id', $studentId)
            ->whereIn('assignment_id', $allAssignments)
            ->count();
        $pendingCount = count($allAssignments) - $submittedCount;

        // 3. Average Grade
        $averageGrade = Grade::where('student_id', $studentId)
            ->avg('score') ?: 0;

        // 4. Upcoming Assignments (Top 4)
        $upcomingAssignments = Assignment::with(['subject'])
            ->where('class_id', $classId)
            ->where('due_date', '>=', Carbon::now())
            ->orderBy('due_date', 'asc')
            ->limit(4)
            ->get()
            ->map(function($assignment) use ($studentId) {
                $isSubmitted = AssignmentSubmission::where('assignment_id', $assignment->id)
                    ->where('student_id', $studentId)
                    ->exists();
                $assignment->is_submitted = $isSubmitted;
                
                // Calculate days remaining
                $now = Carbon::now();
                $due = Carbon::parse($assignment->due_date);
                $assignment->days_remaining = $now->diffInDays($due, false);
                
                return $assignment;
            });

        // 5. Recent Grades (Top 5)
        $recentGrades = Grade::with(['subject'])
            ->where('student_id', $studentId)
            ->latest()
            ->limit(5)
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'subjects_count' => $subjectsCount,
                'pending_assignments' => $pendingCount,
                'completed_assignments' => $submittedCount,
                'average_grade' => round($averageGrade, 1),
                'upcoming_assignments' => $upcomingAssignments,
                'recent_grades' => $recentGrades
            ]
        ]);
    }

    public function getSchedules()
    {
        $studentId = Auth::id();
        // Get class_id of this student
        $classId = DB::table('student_class')->where('student_id', $studentId)->value('class_id');

        if (!$classId) {
            return response()->json([
                'success' => true,
                'data' => []
            ]);
        }

        $schedules = Schedule::with(['subject', 'teacher'])
            ->where('class_id', $classId)
            ->orderByRaw("FIELD(day, 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu')")
            ->orderBy('start_time')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $schedules
        ]);
    }

    public function getMaterials()
    {
        $studentId = Auth::id();
        $classId = DB::table('student_class')->where('student_id', $studentId)->value('class_id');

        if (!$classId) {
            return response()->json([
                'success' => true,
                'data' => []
            ]);
        }

        $materials = Material::with(['subject', 'teacher'])
            ->where('class_id', $classId)
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'data' => $materials
        ]);
    }

    public function getAssignments()
    {
        $studentId = Auth::id();
        $classId = DB::table('student_class')->where('student_id', $studentId)->value('class_id');

        if (!$classId) {
            return response()->json([
                'success' => true,
                'data' => []
            ]);
        }

        $assignments = Assignment::with(['subject', 'teacher'])
            ->where('class_id', $classId)
            ->latest()
            ->get()
            ->map(function ($assignment) use ($studentId) {
                $submission = AssignmentSubmission::where('assignment_id', $assignment->id)
                    ->where('student_id', $studentId)
                    ->first();
                $assignment->is_submitted = (bool)$submission;
                $assignment->submission = $submission;
                return $assignment;
            });

        return response()->json([
            'success' => true,
            'data' => $assignments
        ]);
    }

    public function getGrades()
    {
        $studentId = Auth::id();
        $grades = Grade::with(['subject'])
            ->where('student_id', $studentId)
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'data' => $grades
        ]);
    }

    public function getAttendance()
    {
        // For now, return empty or dummy data until attendance table is ready
        return response()->json([
            'success' => true,
            'data' => [],
            'summary' => [
                'hadir' => 0,
                'izin' => 0,
                'sakit' => 0,
                'alpa' => 0,
                'total' => 0
            ]
        ]);
    }
}
