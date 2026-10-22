<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Gtk;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @group GTK (Guru & Tenaga Kependidikan)
 *
 * APIs for querying teacher and staff data with WhatsApp-ready responses.
 */
class GtkController extends Controller
{
    use ApiResponse;

    /**
     * List & Search GTK
     *
     * Retrieve list of GTK with filters for search query (name, NIP, NUPTK, kode_gtk), active status, and category.
     */
    public function index(Request $request): JsonResponse
    {
        $search = $request->input('search');
        $statusAktif = $request->input('status_aktif', '1');
        $kategori = $request->input('kategori'); // guru, tendik
        $perPage = min((int) $request->input('per_page', 15), 100);

        $query = Gtk::query()->with('mataPelajaran');

        if ($statusAktif !== 'all') {
            $query->where('status_aktif', $statusAktif === '1' || $statusAktif === 'true');
        }

        if ($kategori) {
            $query->where('kategori_ptk', $kategori);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('nama_lengkap', 'like', "%{$search}%")
                  ->orWhere('nip', 'like', "%{$search}%")
                  ->orWhere('nuptk', 'like', "%{$search}%")
                  ->orWhere('kode_gtk', 'like', "%{$search}%")
                  ->orWhereHas('mataPelajaran', function ($mq) use ($search) {
                      $mq->where('nama_mapel', 'like', "%{$search}%")
                        ->orWhere('kode_mapel', 'like', "%{$search}%");
                  });
            });
        }

        $paginator = $query->orderBy('nama_lengkap', 'asc')->paginate($perPage);

        // Build WhatsApp text
        $waLines = [];
        $total = $paginator->total();
        $waLines[] = "👨‍🏫 *DATA GTK SIMANSA*";
        if ($search) {
            $waLines[] = "🔍 Pencarian: _{$search}_";
        }
        $waLines[] = "📊 Total ditemukan: *{$total}* GTK";
        $waLines[] = "--------------------------------";

        if ($paginator->isEmpty()) {
            $waLines[] = "❌ _Tidak ada GTK yang cocok dengan kriteria pencarian._";
        } else {
            foreach ($paginator->items() as $idx => $g) {
                $num = ($paginator->currentPage() - 1) * $paginator->perPage() + $idx + 1;
                $kode = $g->kode_gtk ? "[{$g->kode_gtk}] " : '';
                $nip = $g->nip ? " (NIP: {$g->nip})" : '';
                $jabatan = $g->jabatan ?: ($g->kategori_ptk ?: 'GTK');
                $mapels = $g->mataPelajaran ? $g->mataPelajaran->pluck('nama_mapel')->filter()->unique()->implode(', ') : '';
                $mapelInfo = $mapels ? " | Mapel: {$mapels}" : '';
                $waLines[] = "{$num}. {$kode}*{$g->nama_lengkap}* - _{$jabatan}_{$nip}{$mapelInfo}";
            }

            if ($paginator->hasMorePages()) {
                $waLines[] = "--------------------------------";
                $waLines[] = "ℹ️ _Menampilkan halaman {$paginator->currentPage()} dari {$paginator->lastPage()}._";
            }
        }

        $whatsappText = implode("\n", $waLines);

        return $this->paginatedResponse($paginator, 'Data GTK berhasil dimuat.', $whatsappText);
    }

    /**
     * Get Detail GTK
     *
     * Retrieve full details of a GTK by UUID, NIP, or Kode GTK.
     */
    public function show(string $identifier): JsonResponse
    {
        $gtk = Gtk::with('mataPelajaran')
            ->where(function ($q) use ($identifier) {
                $q->where('id', $identifier)
                  ->orWhere('nip', $identifier)
                  ->orWhere('kode_gtk', $identifier);
            })
            ->first();

        if (!$gtk) {
            return $this->errorResponse('Data GTK tidak ditemukan.', 404);
        }

        $mapels = $gtk->mataPelajaran ? $gtk->mataPelajaran->pluck('nama_mapel')->filter()->unique()->implode(', ') : '';

        $waLines = [
            "👨‍🏫 *DETAIL GTK SIMANSA*",
            "--------------------------------",
            "• *Nama Lengkap*: {$gtk->nama_lengkap}",
            "• *Kode GTK*: " . ($gtk->kode_gtk ?: '-'),
            "• *NIP*: " . ($gtk->nip ?: '-'),
            "• *NUPTK*: " . ($gtk->nuptk ?: '-'),
            "• *Jabatan*: " . ($gtk->jabatan ?: '-'),
            "• *Kategori*: " . ucfirst($gtk->kategori_ptk ?? '-'),
        ];

        if ($mapels) {
            $waLines[] = "• *Mata Pelajaran*: {$mapels}";
        }

        $waLines = array_merge($waLines, [
            "• *Status Kepegawaian*: " . ($gtk->status_kepegawaian ?: '-'),
            "• *Status Sertifikasi*: " . ($gtk->status_sertifikasi ?: '-'),
            "• *Status Aktif*: " . ($gtk->status_aktif ? 'Aktif' : 'Non-Aktif'),
            "• *No. HP*: " . ($gtk->nomor_hp ?: '-'),
            "• *Email*: " . ($gtk->email ?: '-'),
            "--------------------------------",
            "🤖 _SIMANSA Helper Bot_",
        ]);

        $waText = implode("\n", $waLines);

        return $this->successResponse($gtk, 'Detail GTK berhasil dimuat.', 200, [], $waText);
    }

    /**
     * GTK Statistics Summary
     *
     * Aggregated statistics of teachers and staff.
     */
    public function stats(): JsonResponse
    {
        $totalAktif = Gtk::where('status_aktif', true)->count();
        $totalGuru = Gtk::where('status_aktif', true)
            ->where(function ($q) {
                $q->where('kategori_ptk', 'Pendidik')
                  ->orWhere('kategori_ptk', 'guru')
                  ->orWhere('kategori_ptk', 'Guru');
            })->count();
        $totalTendik = Gtk::where('status_aktif', true)
            ->where(function ($q) {
                $q->where('kategori_ptk', 'Tenaga Kependidikan')
                  ->orWhere('kategori_ptk', 'tendik')
                  ->orWhere('kategori_ptk', 'Tendik');
            })->count();
        $sertifikasi = Gtk::where('status_aktif', true)->where('status_sertifikasi', 'like', '%Sudah%')->count();
        $belumSertifikasi = Gtk::where('status_aktif', true)->where('status_sertifikasi', 'like', '%Belum%')->count();

        $data = [
            'total_aktif' => $totalAktif,
            'kategori' => [
                'guru' => $totalGuru,
                'tendik' => $totalTendik,
            ],
            'sertifikasi' => [
                'sudah' => $sertifikasi,
                'belum' => $belumSertifikasi,
            ],
        ];

        $waText = implode("\n", [
            "📊 *STATISTIK GTK SIMANSA*",
            "--------------------------------",
            "👥 *Total GTK Aktif*: {$totalAktif}",
            "👨‍🏫 Guru: {$totalGuru}",
            "💼 Tenaga Kependidikan: {$totalTendik}",
            "",
            "📜 *Sertifikasi*:",
            "• Sudah Sertifikasi: {$sertifikasi}",
            "• Belum Sertifikasi: {$belumSertifikasi}",
            "--------------------------------",
            "🤖 _SIMANSA Helper Bot_",
        ]);

        return $this->successResponse($data, 'Statistik GTK berhasil dimuat.', 200, [], $waText);
    }
}
