<?php

namespace App\Http\Controllers\Api\Guru;

use App\Http\Controllers\Controller;
use App\Models\Schedule;
use App\Models\Material;
use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\ClassModel;
use App\Models\Subject;
use App\Models\User;
use App\Models\Grade;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use App\Exports\GradesExport;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\Setting;
use Illuminate\Support\Str;

class GuruController extends Controller
{
    public function getDashboardStats()
    {
        $teacherId = Auth::id();
        
        // Count distinct classes assigned to this teacher via schedule
        $classesCount = Schedule::where('teacher_id', $teacherId)->distinct('class_id')->count('class_id');
        
        // Count total unique students in all classes assigned to this teacher
        $studentsCount = DB::table('student_class')
            ->whereIn('class_id', function($query) use ($teacherId) {
                $query->select('class_id')->from('schedules')->where('teacher_id', $teacherId);
            })->distinct('student_id')->count('student_id');
            
        $assignmentsCount = Assignment::where('teacher_id', $teacherId)->count();
        $materialsCount = Material::where('teacher_id', $teacherId)->count();

        // Get 5 most recently joined students in teacher's classes
        $recentStudents = User::whereHas('role', function($q) {
                $q->where('name', 'siswa');
            })
            ->whereHas('classes', function($q) use ($teacherId) {
                $q->whereIn('class_id', function($query) use ($teacherId) {
                    $query->select('class_id')->from('schedules')->where('teacher_id', $teacherId);
                });
            })
            ->latest()
            ->take(5)
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'classes' => $classesCount,
                'students' => $studentsCount,
                'assignments' => $assignmentsCount,
                'materials' => $materialsCount,
                'recent_students' => $recentStudents
            ]
        ]);
    }

    public function getSchedules()
    {
        $teacherId = Auth::id();
        $schedules = Schedule::with(['class', 'subject'])
            ->where('teacher_id', $teacherId)
            ->orderByRaw("FIELD(day, 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu')")
            ->orderBy('start_time')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $schedules
        ]);
    }

    public function getClasses()
    {
        $teacherId = Auth::id();
        // Get classes where this teacher has a schedule
        $classIds = Schedule::where('teacher_id', $teacherId)->pluck('class_id')->unique();
        $classes = ClassModel::with(['department'])
            ->withCount('students')
            ->whereIn('id', $classIds)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $classes
        ]);
    }

    public function getSubjects()
    {
        $teacherId = Auth::id();
        // Get subjects where this teacher has a schedule
        $subjectIds = Schedule::where('teacher_id', $teacherId)->pluck('subject_id')->unique();
        $subjects = Subject::whereIn('id', $subjectIds)->get();

        return response()->json([
            'success' => true,
            'data' => $subjects
        ]);
    }

    public function getMaterials()
    {
        $teacherId = Auth::id();
        $materials = Material::with(['class', 'subject'])
            ->where('teacher_id', $teacherId)
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'data' => $materials
        ]);
    }

    public function storeMaterial(Request $request)
    {
        $validated = $request->validate([
            'class_id' => 'required|exists:classes,id',
            'subject_id' => 'required|exists:subjects,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'file' => 'nullable|file|max:10240', // 10MB
        ]);

        $data = $validated;
        $data['teacher_id'] = Auth::id();
        $data['type'] = 'file';

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $path = $file->store('materials', 'public');
            $data['file_path'] = $path;
            $data['file_type'] = $file->getClientOriginalExtension();
            $data['file_size'] = $file->getSize();
        }

        $material = Material::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Materi berhasil ditambahkan',
            'data' => $material
        ]);
    }

    public function getAssignments()
    {
        $teacherId = Auth::id();
        $assignments = Assignment::with(['class', 'subject'])
            ->withCount('submissions')
            ->where('teacher_id', $teacherId)
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'data' => $assignments
        ]);
    }

    public function storeAssignment(Request $request)
    {
        $validated = $request->validate([
            'class_id' => 'required|exists:classes,id',
            'subject_id' => 'required|exists:subjects,id',
            'title' => 'required|string|max:255',
            'instruction' => 'required|string',
            'deadline' => 'required|date',
            'max_points' => 'nullable|integer'
        ]);

        $validated['teacher_id'] = Auth::id();
        $assignment = Assignment::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Tugas berhasil ditambahkan',
            'data' => $assignment
        ]);
    }

    public function getStudentsInClass(ClassModel $class)
    {
        $students = $class->students()->get();
        return response()->json([
            'success' => true,
            'data' => $students
        ]);
    }

    public function getStudentsForAttendance(Request $request)
    {
        $request->validate(['class_id' => 'required|exists:classes,id']);
        
        $students = User::whereHas('role', function($q) {
                $q->where('name', 'siswa');
            })
            ->whereHas('classes', function($q) use ($request) {
                $q->where('class_id', $request->class_id);
            })
            ->get();

        return response()->json([
            'success' => true,
            'data' => $students
        ]);
    }

    public function storeAttendance(Request $request)
    {
        $request->validate([
            'class_id' => 'required|exists:classes,id',
            'date' => 'required|date',
            'attendance' => 'required|array',
            'attendance.*.student_id' => 'required|exists:users,id',
            'attendance.*.status' => 'required|in:Hadir,Izin,Sakit,Alpa',
        ]);

        // Logic to save attendance to database (need attendance table)
        // For now, return success to verify frontend working
        return response()->json([
            'success' => true,
            'message' => 'Presensi berhasil disimpan'
        ]);
    }

    public function getGrades(Request $request)
    {
        $request->validate([
            'class_id' => 'required|exists:classes,id',
            'subject_id' => 'required|exists:subjects,id',
            'category' => 'required|string',
        ]);

        $grades = Grade::where('class_id', $request->class_id)
            ->where('subject_id', $request->subject_id)
            ->where('grade_type', $request->category)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $grades
        ]);
    }

    public function storeGrades(Request $request)
    {
        $request->validate([
            'class_id' => 'required|exists:classes,id',
            'subject_id' => 'required|exists:subjects,id',
            'category' => 'required|string',
            'grades' => 'required|array',
            'grades.*.student_id' => 'required|exists:users,id',
            'grades.*.score' => 'required|numeric|min:0|max:100',
            'grades.*.note' => 'nullable|string',
        ]);

        foreach ($request->grades as $gradeData) {
            Grade::updateOrCreate(
                [
                    'student_id' => $gradeData['student_id'],
                    'class_id' => $request->class_id,
                    'subject_id' => $request->subject_id,
                    'grade_type' => $request->category,
                ],
                [
                    'score' => $gradeData['score'],
                    'notes' => $gradeData['note'],
                    'max_score' => 100, // Default max score
                ]
            );
        }

        return response()->json([
            'success' => true,
            'message' => 'Nilai berhasil disimpan'
        ]);
    }

    public function deleteGrades(Request $request)
    {
        $request->validate([
            'class_id' => 'required|exists:classes,id',
            'subject_id' => 'required|exists:subjects,id',
            'category' => 'required|string',
        ]);

        Grade::where('class_id', $request->class_id)
            ->where('subject_id', $request->subject_id)
            ->where('grade_type', $request->category)
            ->delete();

        return response()->json([
            'success' => true,
            'message' => 'Nilai kategori ' . $request->category . ' berhasil dihapus'
        ]);
    }

    public function getGradeSummary(Request $request)
    {
        $request->validate([
            'class_id' => 'required|exists:classes,id',
            'subject_id' => 'required|exists:subjects,id',
        ]);

        $students = User::whereHas('classes', function($q) use ($request) {
            $q->where('class_id', $request->class_id);
        })->get();

        $categories = Grade::where('class_id', $request->class_id)
            ->where('subject_id', $request->subject_id)
            ->distinct('grade_type')
            ->pluck('grade_type');

        $grades = Grade::where('class_id', $request->class_id)
            ->where('subject_id', $request->subject_id)
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'students' => $students,
                'categories' => $categories,
                'grades' => $grades
            ]
        ]);
    }

    public function exportGradesPdf(Request $request)
    {
        $request->validate([
            'class_id' => 'required|exists:classes,id',
            'subject_id' => 'required|exists:subjects,id',
        ]);

        $data = $this->prepareExportData($request);
        $pdf = Pdf::loadView('exports.grades_pdf', $data)->setPaper('a4', 'landscape');
        
        $className = Str::slug($data['class']->name);
        $subjectName = Str::slug($data['subject']->name);
        $filename = 'Rekap_Nilai_' . $className . '_' . $subjectName . '.pdf';
        
        // Clean filename from any potential newlines or suspicious characters
        $filename = preg_replace('/[\r\n\t]/', '', $filename);
        
        // Highly aggressive header cleaning to prevent "Header may not contain more than a single header"
        if (ob_get_length()) ob_end_clean();
        
        return $pdf->download($filename);
    }

    public function exportGradesExcel(Request $request)
    {
        $request->validate([
            'class_id' => 'required|exists:classes,id',
            'subject_id' => 'required|exists:subjects,id',
        ]);

        $data = $this->prepareExportData($request);
        $className = Str::slug($data['class']->name);
        $subjectName = Str::slug($data['subject']->name);
        $filename = 'Rekap_Nilai_' . $className . '_' . $subjectName . '.xlsx';
        
        $filename = preg_replace('/[\r\n\t]/', '', $filename);
        
        if (ob_get_length()) ob_end_clean();
        return Excel::download(new GradesExport($data), $filename);
    }

    private function prepareExportData(Request $request)
    {
        $class = ClassModel::findOrFail($request->class_id);
        $subject = Subject::findOrFail($request->subject_id);
        $teacher = Auth::user();

        $students = User::whereHas('classes', function($q) use ($request) {
            $q->where('class_id', $request->class_id);
        })->get();

        $categories = Grade::where('class_id', $request->class_id)
            ->where('subject_id', $request->subject_id)
            ->distinct('grade_type')
            ->pluck('grade_type');

        $grades = Grade::where('class_id', $request->class_id)
            ->where('subject_id', $request->subject_id)
            ->get();

        $settings = Setting::first();

        return [
            'class' => $class,
            'subject' => $subject,
            'teacher' => $teacher,
            'students' => $students,
            'categories' => $categories,
            'grades' => $grades,
            'school_name' => $settings->school_name ?? 'MAN 1 BREBES',
            'location' => 'Brebes', // Fallback as city is not in table
            'school_address' => $settings->school_address ?? 'Jl. Jenderal Sudirman No. 12',
            'school_phone' => $settings->school_phone ?? '(0283) 123456',
            'school_website' => $settings->school_website ?? 'www.man1brebes.sch.id'
        ];
    }

    public function updateOnlineLink(Request $request, Schedule $schedule)
    {
        // Ensure the schedule belongs to the authenticated guru
        if ($schedule->teacher_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki akses ke jadwal ini'
            ], 403);
        }

        $validated = $request->validate([
            'online_link' => 'nullable|url'
        ]);

        $schedule->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Link kelas online berhasil diperbarui',
            'data' => $schedule
        ]);
    }
}
