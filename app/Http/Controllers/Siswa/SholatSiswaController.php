<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use App\Models\SholatPetugasKelas;
use App\Models\SholatRecord;
use App\Models\SholatSession;
use App\Models\SholatUdzurRecord;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SholatSiswaController extends Controller
{
    public function __construct()
    {
        Carbon::setLocale('id');
    }

    /**
     * Dashboard Mutaba'ah Ibadah Siswa & Pengajuan Udzur Syar'i
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $siswa = $user->siswa;

        if (! $siswa) {
            abort(404, 'Data profil siswa tidak ditemukan.');
        }

        $bulan = $request->input('bulan', Carbon::now()->format('Y-m'));
        $carbonBulan = Carbon::createFromFormat('Y-m', $bulan);
        $startDate = $carbonBulan->copy()->startOfMonth()->toDateString();
        $endDate = $carbonBulan->copy()->endOfMonth()->toDateString();

        // Riwayat kehadiran sholat siswa pada bulan ini
        $records = SholatRecord::query()
            ->with(['session.sholatSetting', 'session.kelas'])
            ->where('siswa_id', $siswa->id)
            ->whereHas('session', function ($q) use ($startDate, $endDate) {
                $q->whereBetween('tanggal', [$startDate, $endDate]);
            })
            ->join('sholat_sessions', 'sholat_records.sholat_session_id', '=', 'sholat_sessions.id')
            ->orderByDesc('sholat_sessions.tanggal')
            ->select('sholat_records.*')
            ->get();

        // Hitung akumulasi status
        $summary = [
            'total' => $records->count(),
            'berjamaah' => $records->where('status', 'berjamaah')->count(),
            'masbuq' => $records->where('status', 'masbuq')->count(),
            'munfarid' => $records->where('status', 'munfarid')->count(),
            'udzur' => $records->where('status', 'udzur')->count(),
            'sakit' => $records->where('status', 'sakit')->count(),
            'izin' => $records->where('status', 'izin')->count(),
            'alpa' => $records->where('status', 'alpa')->count(),
        ];

        $hadir = $summary['berjamaah'] + $summary['masbuq'] + $summary['munfarid'];
        $wajib = max(0, $summary['total'] - ($summary['udzur'] + $summary['sakit'] + $summary['izin']));
        $persentase = $wajib > 0 ? round(($hadir / $wajib) * 100, 1) : ($summary['total'] > 0 ? 100 : 0);

        // Riwayat Udzur Syar'i (khusus siswi putri)
        $udzurList = collect();
        $activeUdzurToday = null;

        if ($siswa->jenis_kelamin === 'P') {
            $udzurList = SholatUdzurRecord::query()
                ->where('siswa_id', $siswa->id)
                ->orderByDesc('tanggal_mulai')
                ->limit(10)
                ->get();

            $activeUdzurToday = SholatUdzurRecord::query()
                ->where('siswa_id', $siswa->id)
                ->activeOnDate(Carbon::today()->toDateString())
                ->first();
        }

        // Cek apakah siswa ini ditugaskan sebagai Seksi Kerohanian Kelas
        $petugasKelas = SholatPetugasKelas::query()
            ->with('kelas')
            ->where('siswa_id', $siswa->id)
            ->where('is_active', true)
            ->first();

        $statusLabels = SholatRecord::statusLabels();

        return view('siswa.sholat.index', compact(
            'siswa',
            'bulan',
            'carbonBulan',
            'records',
            'summary',
            'persentase',
            'udzurList',
            'activeUdzurToday',
            'petugasKelas',
            'statusLabels'
        ));
    }

    /**
     * Ajukan Klaim Udzur Syar'i (Khusus Siswi Putri)
     */
    public function storeUdzur(Request $request)
    {
        $user = $request->user();
        $siswa = $user->siswa;

        if (! $siswa || $siswa->jenis_kelamin !== 'P') {
            abort(403, 'Fitur Udzur Syar\'i hanya dapat diakses oleh siswi putri.');
        }

        $data = $request->validate([
            'tanggal_mulai' => ['required', 'date'],
            'tanggal_selesai' => ['required', 'date', 'after_or_equal:tanggal_mulai'],
            'keterangan' => ['nullable', 'string', 'max:255'],
        ]);

        $start = Carbon::parse($data['tanggal_mulai']);
        $end = Carbon::parse($data['tanggal_selesai']);

        // Maksimal rentang haid dalam fiqih adalah 15 hari
        if ($end->diffInDays($start) > 15) {
            return back()->withInput()->with('error', 'Rentang masa udzur syar\'i maksimal adalah 15 hari berturut-turut.');
        }

        DB::transaction(function () use ($siswa, $data, $user) {
            SholatUdzurRecord::create([
                'siswa_id' => $siswa->id,
                'tanggal_mulai' => $data['tanggal_mulai'],
                'tanggal_selesai' => $data['tanggal_selesai'],
                'keterangan' => $data['keterangan'] ?? 'Udzur Syar\'i Mandiri',
                'created_by' => $user->id,
            ]);

            // Sinkronkan status sholat yang sudah ada di rentang tanggal ini menjadi 'udzur'
            $existingRecords = SholatRecord::query()
                ->where('siswa_id', $siswa->id)
                ->whereHas('session', function ($q) use ($data) {
                    $q->whereBetween('tanggal', [$data['tanggal_mulai'], $data['tanggal_selesai']]);
                })
                ->get();

            foreach ($existingRecords as $rec) {
                if ($rec->status === 'alpa' || $rec->status === 'berjamaah') {
                    $rec->update([
                        'status' => 'udzur',
                        'catatan' => 'Udzur Syar\'i (Klaim Siswi)',
                    ]);
                }
            }
        });

        return redirect()->route('siswa.sholat.index')
            ->with('success', 'Laporan Udzur Syar\'i berhasil disimpan. Kehadiran sholat Anda pada periode tersebut otomatis berstatus Udzur.');
    }

    /**
     * Hapus / Batalkan Klaim Udzur Syar'i (jika suci lebih awal)
     */
    public function destroyUdzur(Request $request, SholatUdzurRecord $record)
    {
        $user = $request->user();
        $siswa = $user->siswa;

        if (! $siswa || $record->siswa_id !== $siswa->id) {
            abort(403, 'Anda tidak berhak menghapus data ini.');
        }

        $record->delete();

        return redirect()->route('siswa.sholat.index')
            ->with('success', 'Data Udzur Syar\'i berhasil dibatalkan.');
    }
}
