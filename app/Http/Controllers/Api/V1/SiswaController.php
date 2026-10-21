<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Kelas;
use App\Models\Siswa;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @group Siswa
 *
 * APIs for searching, viewing, and querying student data with ready-to-use WhatsApp bot formatting.
 */
class SiswaController extends Controller
{
    use ApiResponse;

    /**
     * List & Search Siswa
     *
     * Search and filter students by keyword (nama, NISN, NIS), class, or status.
     * Response includes formatted whatsapp_text for instant bot messaging.
     */
    public function index(Request $request): JsonResponse
    {
        $search = $request->input('search');
        $kelasId = $request->input('kelas_id');
        $tingkat = $request->input('tingkat');
        $status = $request->input('status', 'aktif');
        $perPage = min((int) $request->input('per_page', 15), 100);

        $query = Siswa::query()
            ->with(['kelasSaatIni:id,nama_kelas,tingkat']);

        if ($status && $status !== 'all') {
            $query->where('status_siswa', $status);
        }

        if ($kelasId) {
            $query->where('kelas_saat_ini_id', $kelasId);
        }

        if ($tingkat) {
            $query->whereHas('kelasSaatIni', fn ($q) => $q->where('tingkat', $tingkat));
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('nama_lengkap', 'like', "%{$search}%")
                  ->orWhere('nisn', 'like', "%{$search}%")
                  ->orWhere('nis_lokal', 'like', "%{$search}%");
            });
        }

        $paginator = $query->orderBy('nama_lengkap', 'asc')->paginate($perPage);

        // Build WhatsApp text
        $waLines = [];
        $total = $paginator->total();
        $waLines[] = "📚 *DATA SISWA SIMANSA*";
        if ($search) {
            $waLines[] = "🔍 Pencarian: _{$search}_";
        }
        $waLines[] = "📊 Total ditemukan: *{$total}* siswa";
        $waLines[] = "--------------------------------";

        if ($paginator->isEmpty()) {
            $waLines[] = "❌ _Tidak ada siswa yang cocok dengan kriteria pencarian._";
        } else {
            foreach ($paginator->items() as $idx => $s) {
                $num = ($paginator->currentPage() - 1) * $paginator->perPage() + $idx + 1;
                $kls = $s->kelasSaatIni?->nama ?? '-';
                $nisn = $s->nisn ? " (NISN: {$s->nisn})" : '';
                $waLines[] = "{$num}. *{$s->nama_lengkap}* | {$kls}{$nisn}";
            }

            if ($paginator->hasMorePages()) {
                $waLines[] = "--------------------------------";
                $waLines[] = "ℹ️ _Menampilkan halaman {$paginator->currentPage()} dari {$paginator->lastPage()}._";
            }
        }

        $whatsappText = implode("\n", $waLines);

        return $this->paginatedResponse($paginator, 'Data siswa berhasil dimuat.', $whatsappText);
    }

    /**
     * Get Detail Siswa
     *
     * Retrieve complete student profile by UUID, NISN, or NIS.
     */
    public function show(string $identifier): JsonResponse
    {
        $siswa = Siswa::with(['kelasSaatIni.waliKelas', 'jurusanPilihan'])
            ->where(function ($q) use ($identifier) {
                $q->where('id', $identifier)
                  ->orWhere('nisn', $identifier)
                  ->orWhere('nis_lokal', $identifier);
            })
            ->first();

        if (!$siswa) {
            return $this->errorResponse('Data siswa tidak ditemukan.', 404);
        }

        $kls = $siswa->kelasSaatIni?->nama ?? 'Belum ditentukan';
        $wali = $siswa->kelasSaatIni?->waliKelas?->nama_lengkap ?? '-';

        $waText = implode("\n", [
            "👤 *DETAIL SISWA SIMANSA*",
            "--------------------------------",
            "• *Nama Lengkap*: {$siswa->nama_lengkap}",
            "• *NISN*: " . ($siswa->nisn ?: '-'),
            "• *NIS Lokal*: " . ($siswa->nis_lokal ?: '-'),
            "• *Jenis Kelamin*: " . ($siswa->jenis_kelamin === 'L' ? 'Laki-laki' : 'Perempuan'),
            "• *Kelas*: {$kls}",
            "• *Wali Kelas*: {$wali}",
            "• *Status*: " . ucfirst($siswa->status_siswa ?? 'aktif'),
            "• *No. HP*: " . ($siswa->nomor_hp ?: '-'),
            "• *Alamat*: " . ($siswa->alamat_siswa ?: '-'),
            "--------------------------------",
            "🤖 _SIMANSA Helper Bot_",
        ]);

        return $this->successResponse($siswa, 'Detail siswa berhasil dimuat.', 200, [], $waText);
    }

    /**
     * Student Statistics Summary
     *
     * Get aggregate statistics on students (total active, breakdown by gender and level).
     */
    public function stats(): JsonResponse
    {
        $totalAktif = Siswa::where('status_siswa', 'aktif')->count();
        $totalLaki = Siswa::where('status_siswa', 'aktif')->where('jenis_kelamin', 'L')->count();
        $totalPerempuan = Siswa::where('status_siswa', 'aktif')->where('jenis_kelamin', 'P')->count();

        $tingkatCounts = Siswa::where('status_siswa', 'aktif')
            ->join('kelas', 'siswa.kelas_saat_ini_id', '=', 'kelas.id')
            ->selectRaw('kelas.tingkat, count(*) as count')
            ->groupBy('kelas.tingkat')
            ->pluck('count', 'tingkat')
            ->toArray();

        $data = [
            'total_aktif' => $totalAktif,
            'gender' => [
                'laki_laki' => $totalLaki,
                'perempuan' => $totalPerempuan,
            ],
            'per_tingkat' => [
                '10' => $tingkatCounts['10'] ?? ($tingkatCounts['X'] ?? 0),
                '11' => $tingkatCounts['11'] ?? ($tingkatCounts['XI'] ?? 0),
                '12' => $tingkatCounts['12'] ?? ($tingkatCounts['XII'] ?? 0),
            ],
        ];

        $waText = implode("\n", [
            "📊 *STATISTIK SISWA AKTIF SIMANSA*",
            "--------------------------------",
            "👥 *Total Siswa Aktif*: {$totalAktif}",
            "👦 Laki-laki: {$totalLaki}",
            "👧 Perempuan: {$totalPerempuan}",
            "",
            "🏫 *Rincian Tingkat*:",
            "• Kelas X  : " . ($data['per_tingkat']['10']) . " siswa",
            "• Kelas XI : " . ($data['per_tingkat']['11']) . " siswa",
            "• Kelas XII: " . ($data['per_tingkat']['12']) . " siswa",
            "--------------------------------",
            "🤖 _SIMANSA Helper Bot_",
        ]);

        return $this->successResponse($data, 'Statistik siswa berhasil dimuat.', 200, [], $waText);
    }
}
