<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Subject;
use App\Models\ClassModel;
use Illuminate\Http\Request;

class PublicStatsController extends Controller
{
    public function index()
    {
        try {
            $stats = [
                'students' => User::whereHas('role', function($q) {
                    $q->where('name', 'siswa');
                })->count(),
                'teachers' => User::whereHas('role', function($q) {
                    $q->where('name', 'guru');
                })->count(),
                'subjects' => Subject::count(),
                'classes' => ClassModel::count(),
            ];

            return response()->json([
                'success' => true,
                'data' => $stats
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch statistics',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
