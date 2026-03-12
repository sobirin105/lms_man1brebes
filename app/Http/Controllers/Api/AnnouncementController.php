<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use Illuminate\Http\Request;

class AnnouncementController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        if (!$user || !$user->role) {
            return response()->json([
                'success' => false,
                'message' => 'User role not found'
            ], 403);
        }

        $role = $user->role->name;
        
        $query = Announcement::with('user')
            ->where('is_active', true);

        // Admins see all active announcements, others see targeted ones
        if ($role !== 'admin') {
            $query->where(function($q) use ($role) {
                $q->where('target_role', 'all')
                  ->orWhere('target_role', $role);
            });
        }

        $announcements = $query->latest()->get();

        return response()->json([
            'success' => true,
            'data' => $announcements
        ]);
    }
}
