<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AbsensiSiswaSession;
use App\Models\AbsensiSetting;
use App\Models\Kelas;
use App\Models\SholatSession;
use App\Models\SholatSetting;
use App\Models\TahunPelajaran;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StudentQrKioskController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('view-student-attendance');

        $date = $request->input('tanggal', now()->toDateString());
        $dailySessions = AbsensiSiswaSession::query()
            ->with('kelas:id,nama_kelas')
            ->whereDate('tanggal', $date)
            ->where('mode', 'harian')
            ->where('status', '!=', 'final')
            ->orderBy('kelas_snapshot')
            ->get();
        $sholatSessions = SholatSession::query()
            ->with(['kelas:id,nama_kelas', 'sholatSetting:id,nama'])
            ->whereDate('tanggal', $date)
            ->where('status', '!=', 'finalized')
            ->orderBy('created_at')
            ->get();

        return view('admin.absensi.qr-kiosk', [
            'date' => $date,
            'dailySessions' => $dailySessions,
            'sholatSessions' => $sholatSessions,
            'dailyEnabled' => AbsensiSetting::getValue('qr_daily_enabled', true),
            'sholatEnabled' => AbsensiSetting::getValue('qr_sholat_enabled', true),
            'defaultTtl' => AbsensiSetting::getValue('qr_token_ttl_minutes', 10),
        ]);
    }
}
