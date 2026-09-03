<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Institution;
use App\Models\User;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'total_institutions' => Institution::count(),
            'active_institutions' => Institution::where('status', 'active')->count(),
            'total_admins' => User::role('admin')->count(),
            'total_teachers' => User::role('teacher')->count(),
            // Mengambil total siswa hanya yang aktif (status 'active')
            'total_students' => User::role('siswa')->where('status', 'active')->count(),
        ];
        
        return view('super_admin.dashboard', compact('stats'));
    }
}
