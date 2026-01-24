<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Siswa;
use App\Models\ActivityLog;
use App\Models\TahunPelajaran;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        // Get tahun pelajaran aktif
        $tahunPelajaranAktif = TahunPelajaran::where('is_active', true)->first();
        
        $stats = [
            'total_siswa' => Siswa::count(),
            'total_admin' => User::where('role', '!=', 'siswa')->count(),
            'siswa_aktif' => Siswa::whereHas('user', function($q) {
                $q->where('is_first_login', false);
            })->count(),
            'recent_activities' => ActivityLog::with('user')
                ->latest()
                ->take(10)
                ->get(),
        ];

        return view('admin.dashboard', compact('stats', 'tahunPelajaranAktif'));
    }
}
