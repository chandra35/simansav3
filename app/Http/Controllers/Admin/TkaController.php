<?php

namespace App\Http\Controllers\Admin;

use App\Exports\TkaPesertaExport;
use App\Http\Controllers\Controller;
use App\Models\Kelas;
use App\Models\TkaPeserta;
use App\Services\TkaImportService;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\StreamedResponse;

class TkaController extends Controller
{
    /**
     * Display a listing of TKA participants with search and filter capabilities.
     */
    public function index(Request $request)
    {
        $availableYears = TkaPeserta::getAvailableYears();
        $selectedYear = (int) $request->input('tahun', !empty($availableYears) ? $availableYears[0] : date('Y'));

        $query = TkaPeserta::query()
            ->with(['siswa.kelasSaatIni', 'siswa.kelasAktif'])
            ->where('tahun', $selectedYear);

        // Filter by keyword search (nama, nisn, username)
        if ($request->filled('search')) {
            $search = trim($request->input('search'));
            $query->where(function ($q) use ($search) {
                $q->where('nama_peserta', 'like', "%{$search}%")
                  ->orWhere('nisn', 'like', "%{$search}%")
                  ->orWhere('username', 'like', "%{$search}%")
                  ->orWhere('id_proktor', 'like', "%{$search}%");
            });
        }

        // Filter by Ruang
        if ($request->filled('ruang')) {
            $query->where('ruang', $request->input('ruang'));
        }

        // Filter by Gelombang
        if ($request->filled('gelombang')) {
            $query->where('gelombang', $request->input('gelombang'));
        }

        // Filter by Sesi
        if ($request->filled('sesi')) {
            $query->where('sesi', $request->input('sesi'));
        }

        // Filter by Kelas SIMANSA
        if ($request->filled('kelas_id')) {
            $kelasId = $request->input('kelas_id');
            $query->whereHas('siswa', function ($q) use ($kelasId) {
                $q->where('kelas_saat_ini_id', $kelasId)
                  ->orWhereHas('kelasAktif', function ($qk) use ($kelasId) {
                      $qk->where('kelas.id', $kelasId);
                  });
            });
        }

        // Overall stats for the selected year (unfiltered)
        $baseYearQuery = TkaPeserta::where('tahun', $selectedYear);
        $stats = [
            'total_peserta' => (clone $baseYearQuery)->count(),
            'total_ruang' => (clone $baseYearQuery)->whereNotNull('ruang')->where('ruang', '!=', '')->distinct()->count('ruang'),
            'total_sesi' => (clone $baseYearQuery)->whereNotNull('sesi')->where('sesi', '!=', '')->distinct()->count('sesi'),
            'matched_siswa' => (clone $baseYearQuery)->whereNotNull('siswa_id')->count(),
        ];

        // Filter dropdown options
        $ruangOptions = TkaPeserta::where('tahun', $selectedYear)
            ->whereNotNull('ruang')
            ->where('ruang', '!=', '')
            ->distinct()
            ->orderBy('ruang')
            ->pluck('ruang');

        $gelombangOptions = TkaPeserta::where('tahun', $selectedYear)
            ->whereNotNull('gelombang')
            ->where('gelombang', '!=', '')
            ->distinct()
            ->orderBy('gelombang')
            ->pluck('gelombang');

        $sesiOptions = TkaPeserta::where('tahun', $selectedYear)
            ->whereNotNull('sesi')
            ->where('sesi', '!=', '')
            ->distinct()
            ->orderBy('sesi')
            ->pluck('sesi');

        $kelasOptions = Kelas::where(function ($q) {
                $q->where('tingkat', 12)->orWhere('tingkat', 'XII');
            })
            ->where('is_active', true)
            ->orderBy('nama_kelas')
            ->get();

        $pesertaList = $query->orderBy('ruang')
            ->orderBy('sesi')
            ->orderBy('nama_peserta')
            ->paginate(50)
            ->withQueryString();

        return view('admin.tka.index', compact(
            'pesertaList',
            'availableYears',
            'selectedYear',
            'stats',
            'ruangOptions',
            'gelombangOptions',
            'sesiOptions',
            'kelasOptions'
        ));
    }

    /**
     * Import participants from official TKA PDF.
     */
    public function importPdf(Request $request, TkaImportService $importService)
    {
        $request->validate([
            'pdf_file' => 'required|file|mimes:pdf|max:51200', // max 50MB
            'tahun' => 'nullable|integer|min:2020|max:2050',
            'replace_existing' => 'nullable|boolean',
        ], [
            'pdf_file.required' => 'Pilih file PDF TKA terlebih dahulu.',
            'pdf_file.mimes' => 'Format file harus berupa PDF.',
            'pdf_file.max' => 'Ukuran file PDF maksimal 50MB.',
        ]);

        $file = $request->file('pdf_file');
        $tahun = $request->filled('tahun') ? (int) $request->input('tahun') : null;
        $replaceExisting = $request->boolean('replace_existing', false);

        try {
            $result = $importService->importFromPdf(
                $file->getRealPath(),
                $tahun,
                $replaceExisting
            );

            return redirect()->route('admin.tka.index', ['tahun' => $result['tahun']])
                ->with('success', sprintf(
                    'Berhasil mengimpor data TKA Tahun %d! Total %d kartu dibaca (%d disimpan/diperbarui, %d terhubung ke data siswa SIMANSA).',
                    $result['tahun'],
                    $result['total_parsed'],
                    $result['total_saved'],
                    $result['matched_siswa']
                ));
        } catch (\Throwable $e) {
            return redirect()->back()
                ->with('error', 'Gagal memproses file PDF TKA: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Print mass cards (4 cards per page) with current active filters.
     */
    public function cetakMassal(Request $request)
    {
        $selectedYear = (int) $request->input('tahun', date('Y'));

        $query = TkaPeserta::query()
            ->with(['siswa.kelasSaatIni', 'siswa.kelasAktif'])
            ->where('tahun', $selectedYear);

        if ($request->filled('search')) {
            $search = trim($request->input('search'));
            $query->where(function ($q) use ($search) {
                $q->where('nama_peserta', 'like', "%{$search}%")
                  ->orWhere('nisn', 'like', "%{$search}%")
                  ->orWhere('username', 'like', "%{$search}%");
            });
        }

        if ($request->filled('ruang')) {
            $query->where('ruang', $request->input('ruang'));
        }

        if ($request->filled('gelombang')) {
            $query->where('gelombang', $request->input('gelombang'));
        }

        if ($request->filled('sesi')) {
            $query->where('sesi', $request->input('sesi'));
        }

        if ($request->filled('kelas_id')) {
            $kelasId = $request->input('kelas_id');
            $query->whereHas('siswa', function ($q) use ($kelasId) {
                $q->where('kelas_saat_ini_id', $kelasId)
                  ->orWhereHas('kelasAktif', function ($qk) use ($kelasId) {
                      $qk->where('kelas.id', $kelasId);
                  });
            });
        }

        $cards = $query->orderBy('ruang')
            ->orderBy('sesi')
            ->orderBy('nama_peserta')
            ->get();

        return view('admin.tka.cetak-massal', compact('cards', 'selectedYear'));
    }

    /**
     * Print single participant card.
     */
    public function cetak(TkaPeserta $tka)
    {
        return view('siswa.tka.cetak', compact('tka'));
    }

    /**
     * Export TKA participant data as Excel (.xlsx).
     * Kolom: No, Nama Lengkap, Username, Password, Jenis Kelamin, Nama Ruang, Gelombang, Sesi.
     */
    public function exportExcel(Request $request)
    {
        $selectedYear = (int) $request->input('tahun', date('Y'));

        $query = TkaPeserta::query()
            ->with(['siswa.kelasSaatIni', 'siswa.kelasAktif'])
            ->where('tahun', $selectedYear);

        if ($request->filled('search')) {
            $search = trim($request->input('search'));
            $query->where(function ($q) use ($search) {
                $q->where('nama_peserta', 'like', "%{$search}%")
                  ->orWhere('nisn', 'like', "%{$search}%")
                  ->orWhere('username', 'like', "%{$search}%");
            });
        }
        if ($request->filled('ruang')) {
            $query->where('ruang', $request->input('ruang'));
        }
        if ($request->filled('gelombang')) {
            $query->where('gelombang', $request->input('gelombang'));
        }
        if ($request->filled('sesi')) {
            $query->where('sesi', $request->input('sesi'));
        }
        if ($request->filled('kelas_id')) {
            $kelasId = $request->input('kelas_id');
            $query->whereHas('siswa', function ($q) use ($kelasId) {
                $q->where('kelas_saat_ini_id', $kelasId)
                  ->orWhereHas('kelasAktif', function ($qk) use ($kelasId) {
                      $qk->where('kelas.id', $kelasId);
                  });
            });
        }

        $pesertas = $query->orderBy('ruang')
            ->orderBy('sesi')
            ->orderBy('nama_peserta')
            ->get();

        $filename = "Data_Akun_TKA_{$selectedYear}_" . date('Ymd_His') . ".xlsx";

        return Excel::download(new TkaPesertaExport($pesertas, $selectedYear), $filename);
    }

    /**
     * Export TKA participant data as CSV.
     */
    public function export(Request $request): StreamedResponse
    {
        $selectedYear = (int) $request->input('tahun', date('Y'));

        $query = TkaPeserta::query()
            ->with(['siswa.kelasSaatIni', 'siswa.kelasAktif'])
            ->where('tahun', $selectedYear);

        if ($request->filled('ruang')) {
            $query->where('ruang', $request->input('ruang'));
        }
        if ($request->filled('gelombang')) {
            $query->where('gelombang', $request->input('gelombang'));
        }
        if ($request->filled('sesi')) {
            $query->where('sesi', $request->input('sesi'));
        }
        if ($request->filled('kelas_id')) {
            $kelasId = $request->input('kelas_id');
            $query->whereHas('siswa', function ($q) use ($kelasId) {
                $q->where('kelas_saat_ini_id', $kelasId)
                  ->orWhereHas('kelasAktif', function ($qk) use ($kelasId) {
                      $qk->where('kelas.id', $kelasId);
                  });
            });
        }

        $pesertas = $query->orderBy('ruang')
            ->orderBy('sesi')
            ->orderBy('nama_peserta')
            ->get();

        $filename = "Data_TKA_{$selectedYear}_" . date('Ymd_His') . ".csv";

        return response()->streamDownload(function () use ($pesertas) {
            $handle = fopen('php://output', 'w');
            
            // UTF-8 BOM for Excel compatibility
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));

            fputcsv($handle, [
                'No',
                'Tahun',
                'NISN',
                'Nama Peserta',
                'Kelas SIMANSA',
                'Username TKA',
                'Password TKA',
                'ID Proktor',
                'Ruang',
                'Gelombang',
                'Sesi',
                'Tempat Tanggal Lahir',
            ]);

            $no = 1;
            foreach ($pesertas as $p) {
                $kelasNama = $p->siswa?->kelasSaatIni?->nama_kelas
                    ?? $p->siswa?->kelasAktif?->first()?->nama_kelas
                    ?? '-';

                fputcsv($handle, [
                    $no++,
                    $p->tahun,
                    "'" . $p->nisn,
                    $p->nama_peserta,
                    $kelasNama,
                    $p->username,
                    $p->password,
                    $p->id_proktor,
                    $p->ruang,
                    $p->gelombang,
                    $p->sesi,
                    $p->tempat_tanggal_lahir,
                ]);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    /**
     * Delete a single TKA record.
     */
    public function destroy(TkaPeserta $tka)
    {
        $nama = $tka->nama_peserta;
        $tka->delete();

        return redirect()->back()->with('success', "Data peserta TKA '{$nama}' berhasil dihapus.");
    }

    /**
     * Bulk delete all TKA records for a specific year.
     */
    public function destroyByYear(Request $request)
    {
        $request->validate([
            'tahun' => 'required|integer',
        ]);

        $tahun = (int) $request->input('tahun');
        $count = TkaPeserta::where('tahun', $tahun)->delete();

        return redirect()->route('admin.tka.index')
            ->with('success', "Sebanyak {$count} data TKA tahun {$tahun} berhasil dihapus.");
    }
}
