<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Absensi;
use App\Models\AbsensiSiswaRecord;
use App\Models\Kelas;
use App\Models\SholatRecord;
use App\Models\SholatSession;
use App\Models\SholatSetting;
use App\Traits\ApiResponse;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @group Presensi & Sholat
 *
 * APIs for daily student/teacher attendance and prayer (sholat) tracking with formatted WhatsApp summaries.
 */
class PresensiController extends Controller
{
    use ApiResponse;

    /**
     * Today's Prayer Attendance Summary
     *
     * Retrieve aggregate prayer attendance (Dzuhur, Ashar, Dhuha) for today or a specific date.
     */
    public function sholatSummary(Request $request): JsonResponse
    {
        $tanggal = $request->input('tanggal', Carbon::today()->toDateString());
        $sholatId = $request->input('sholat_setting_id');

        $query = SholatSession::with(['sholatSetting', 'records', 'kelas:id,nama'])
            ->whereDate('tanggal', $tanggal);

        if ($sholatId) {
            $query->where('sholat_setting_id', $sholatId);
        }

        $sessions = $query->get();

        $stats = [
            'berjamaah' => 0,
            'masbuq' => 0,
            'munfarid' => 0,
            'udzur' => 0,
            'sakit' => 0,
            'izin' => 0,
            'alpa' => 0,
            'total_siswa' => 0,
        ];

        foreach ($sessions as $session) {
            foreach ($session->records as $r) {
                $status = $r->status;
                if (isset($stats[$status])) {
                    $stats[$status]++;
                }
                $stats['total_siswa']++;
            }
        }

        $totalKelasInput = $sessions->pluck('kelas_id')->unique()->count();
        $totalKelasSemua = Kelas::where('is_active', true)->count();

        $waLines = [];
        $waLines[] = "🕌 *REKAP PRESENSI SHOLAT SISWA*";
        $waLines[] = "🗓️ Tanggal: " . Carbon::parse($tanggal)->translatedFormat('d F Y');
        $waLines[] = "🏫 Progres Kelas: *{$totalKelasInput} / {$totalKelasSemua}* kelas";
        $waLines[] = "--------------------------------";
        $waLines[] = "• 🟢 Berjamaah: *{$stats['berjamaah']}*";
        $waLines[] = "• 🔵 Masbuq   : *{$stats['masbuq']}*";
        $waLines[] = "• ⚪ Munfarid : *{$stats['munfarid']}*";
        $waLines[] = "• 🌸 Udzur    : *{$stats['udzur']}*";
        $waLines[] = "• 🟡 Sakit/Izin: *" . ($stats['sakit'] + $stats['izin']) . "*";
        $waLines[] = "• 🔴 Alpa     : *{$stats['alpa']}*";
        $waLines[] = "--------------------------------";
        $waLines[] = "📊 Total Siswa Tercatat: *{$stats['total_siswa']}*";
        $waLines[] = "🤖 _SIMANSA Helper Bot_";

        return $this->successResponse([
            'tanggal' => $tanggal,
            'kelas_input_count' => $totalKelasInput,
            'total_kelas' => $totalKelasSemua,
            'rekap' => $stats,
        ], 'Ringkasan presensi sholat berhasil dimuat.', 200, [], implode("\n", $waLines));
    }

    /**
     * Check Classes Prayer Submission Status
     *
     * Check which classes have submitted prayer attendance and which are still pending for bot alerts.
     */
    public function sholatStatusKelas(Request $request): JsonResponse
    {
        $tanggal = $request->input('tanggal', Carbon::today()->toDateString());
        $sholatCode = strtolower((string) $request->input('sholat', 'dzuhur'));

        $setting = SholatSetting::where('nama', 'like', "%{$sholatCode}%")->first()
            ?? SholatSetting::where('is_active', true)->first();

        $submittedKelasIds = SholatSession::whereDate('tanggal', $tanggal)
            ->when($setting, fn ($q) => $q->where('sholat_setting_id', $setting->id))
            ->pluck('kelas_id')
            ->toArray();

        $allKelas = Kelas::where('is_active', true)->orderBy('nama_kelas')->get();

        $sudah = [];
        $belum = [];

        foreach ($allKelas as $kls) {
            $namaKls = $kls->nama_kelas ?? $kls->nama;
            if (in_array($kls->id, $submittedKelasIds)) {
                $sudah[] = $namaKls;
            } else {
                $belum[] = $namaKls;
            }
        }

        $sholatNama = $setting ? $setting->nama : ucfirst($sholatCode);

        $waLines = [];
        $waLines[] = "🕌 *STATUS INPUT SHOLAT: " . strtoupper($sholatNama) . "*";
        $waLines[] = "🗓️ Tanggal: " . Carbon::parse($tanggal)->translatedFormat('d F Y');
        $waLines[] = "📊 Progres: *" . count($sudah) . " / " . count($allKelas) . "* Kelas";
        $waLines[] = "--------------------------------";

        if (!empty($belum)) {
            $waLines[] = "⚠️ *Kelas Belum Input (" . count($belum) . "):*";
            $chunks = array_chunk($belum, 4);
            foreach ($chunks as $chunk) {
                $waLines[] = "• " . implode(', ', $chunk);
            }
        } else {
            $waLines[] = "🎉 *Alhamdulillah, semua kelas sudah menginput presensi sholat!*";
        }

        $waLines[] = "--------------------------------";
        $waLines[] = "🤖 _SIMANSA Helper Bot_";

        return $this->successResponse([
            'tanggal' => $tanggal,
            'sholat' => $sholatNama,
            'total_kelas' => count($allKelas),
            'sudah_count' => count($sudah),
            'belum_count' => count($belum),
            'sudah_input' => $sudah,
            'belum_input' => $belum,
        ], 'Status input presensi sholat berhasil dimuat.', 200, [], implode("\n", $waLines));
    }

    /**
     * Daily School Attendance Overview
     *
     * Daily attendance statistics for students and GTK.
     */
    public function dailyOverview(Request $request): JsonResponse
    {
        $tanggal = $request->input('tanggal', Carbon::today()->toDateString());

        // GTK Attendance
        $gtkHadir = Absensi::whereDate('tanggal', $tanggal)
            ->whereNotNull('waktu_masuk')
            ->count();
        $gtkTepatWaktu = Absensi::whereDate('tanggal', $tanggal)
            ->where('status', 'hadir')
            ->count();
        $gtkTerlambat = Absensi::whereDate('tanggal', $tanggal)
            ->where('status', 'terlambat')
            ->count();

        // Student Attendance
        $siswaRecords = AbsensiSiswaRecord::whereHas('session', fn ($q) => $q->whereDate('tanggal', $tanggal))->get();
        $siswaHadir = $siswaRecords->whereIn('status', ['H', 'hadir'])->count();
        $siswaSakit = $siswaRecords->whereIn('status', ['S', 'sakit'])->count();
        $siswaIzin = $siswaRecords->whereIn('status', ['I', 'izin'])->count();
        $siswaAlpa = $siswaRecords->whereIn('status', ['A', 'alpa'])->count();

        $data = [
            'tanggal' => $tanggal,
            'gtk' => [
                'hadir' => $gtkHadir,
                'tepat_waktu' => $gtkTepatWaktu,
                'terlambat' => $gtkTerlambat,
            ],
            'siswa' => [
                'hadir' => $siswaHadir,
                'sakit' => $siswaSakit,
                'izin' => $siswaIzin,
                'alpa' => $siswaAlpa,
                'total_tercatat' => $siswaRecords->count(),
            ],
        ];

        $waLines = [
            "🏫 *RINGKASAN KEHADIRAN HARIAN*",
            "🗓️ Tanggal: " . Carbon::parse($tanggal)->translatedFormat('d F Y'),
            "--------------------------------",
            "👨‍🏫 *Kehadiran GTK*:",
            "• Hadir: {$gtkHadir} orang (Tepat: {$gtkTepatWaktu}, Telat: {$gtkTerlambat})",
            "",
            "🎒 *Kehadiran Siswa*:",
            "• Hadir: {$siswaHadir}",
            "• Sakit: {$siswaSakit}",
            "• Izin : {$siswaIzin}",
            "• Alpa : {$siswaAlpa}",
            "--------------------------------",
            "🤖 _SIMANSA Helper Bot_",
        ];

        return $this->successResponse($data, 'Ringkasan kehadiran harian berhasil dimuat.', 200, [], implode("\n", $waLines));
    }
}
