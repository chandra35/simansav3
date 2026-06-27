<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TahunPelajaran;
use App\Services\PpdbMatrikulasiImportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MatrikulasiPpdbController extends Controller
{
    public function __construct(private readonly PpdbMatrikulasiImportService $service)
    {
    }

    public function index(Request $request)
    {
        $tahunPelajaran = TahunPelajaran::orderByDesc('tahun_mulai')->get();
        $selectedTahunId = $request->get('tahun_pelajaran_id') ?: optional($tahunPelajaran->first())->id;
        $kelompokMatrikulasi = $this->service->kelompokFor($selectedTahunId);
        $stats = $this->service->stats($selectedTahunId);

        return view('admin.matrikulasi-ppdb.index', compact(
            'tahunPelajaran',
            'selectedTahunId',
            'kelompokMatrikulasi',
            'stats'
        ));
    }

    public function storeKelompok(Request $request)
    {
        $validated = $request->validate([
            'tahun_pelajaran_id' => 'required|exists:tahun_pelajaran,id',
            'nama' => 'required|string|max:120',
            'kode' => 'nullable|string|max:30',
            'tingkat_kelas' => 'nullable|string|max:30',
            'jenis_kelompok' => 'nullable|in:reguler,asrama',
            'kapasitas' => 'nullable|integer|min:1|max:500',
        ]);

        try {
            $kelompok = $this->service->storeKelompok($validated['tahun_pelajaran_id'], $validated);

            return response()->json([
                'success' => true,
                'message' => 'Kelompok matrikulasi berhasil dibuat.',
                'data' => [
                    'id' => $kelompok->id,
                    'text' => $kelompok->nama . ' - ' . $kelompok->label_kelas,
                ],
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat kelompok: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function candidates(Request $request)
    {
        $request->validate([
            'q' => 'nullable|string|max:100',
            'tahun_pelajaran_id' => 'nullable|exists:tahun_pelajaran,id',
            'include_all' => 'nullable|boolean',
        ]);

        try {
            $candidates = $this->service->searchCandidates(
                $request->get('q'),
                $request->get('tahun_pelajaran_id'),
                50,
                $request->boolean('include_all')
            );

            return response()->json([
                'results' => $candidates->map(fn ($candidate) => [
                    'id' => $candidate->id,
                    'text' => $candidate->label,
                    'status' => $candidate->import_status,
                    'documents_count' => $candidate->documents_count,
                    'tahun' => $candidate->ppdb_tahun_nama,
                    'nama_lengkap' => $candidate->nama_lengkap,
                    'nisn' => $candidate->nisn,
                    'nomor_tes' => $candidate->nomor_tes,
                    'jurusan' => $candidate->jurusan_final ?: $candidate->jurusan_awal ?: $candidate->pilihan_program,
                    'is_lulus' => $candidate->is_lulus,
                    'has_registrasi_komite' => $candidate->has_registrasi_komite,
                ])->values(),
            ]);
        } catch (\Throwable $e) {
            Log::error('Gagal mencari pendaftar PPDB untuk matrikulasi', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'results' => [],
                'message' => 'Koneksi PPDB belum siap atau query gagal: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function browserCandidates(Request $request)
    {
        $validated = $request->validate([
            'draw' => 'nullable|integer|min:1',
            'start' => 'nullable|integer|min:0',
            'length' => 'nullable|integer|min:1|max:100',
            'search.value' => 'nullable|string|max:100',
            'tahun_pelajaran_id' => 'nullable|exists:tahun_pelajaran,id',
        ]);

        $length = (int) ($validated['length'] ?? 25);
        $start = (int) ($validated['start'] ?? 0);
        $page = (int) floor($start / max($length, 1)) + 1;
        $term = data_get($validated, 'search.value');

        try {
            $result = $this->service->browseCandidates(
                $term,
                $request->get('tahun_pelajaran_id'),
                $page,
                $length
            );
            $total = (int) ($result['meta']['total'] ?? $result['data']->count());

            return response()->json([
                'draw' => (int) ($validated['draw'] ?? 1),
                'recordsTotal' => $total,
                'recordsFiltered' => $total,
                'data' => $result['data']->map(fn ($candidate) => [
                    'id' => $candidate->id,
                    'nama_lengkap' => $candidate->nama_lengkap,
                    'nisn' => $candidate->nisn,
                    'nik' => $candidate->nik,
                    'nomor_tes' => $candidate->nomor_tes,
                    'tahun' => $candidate->ppdb_tahun_nama,
                    'jurusan' => $candidate->jurusan_final ?: $candidate->jurusan_awal ?: $candidate->pilihan_program,
                    'documents_count' => $candidate->documents_count,
                    'import_status' => $candidate->import_status,
                    'is_lulus' => $candidate->is_lulus,
                    'has_registrasi_komite' => $candidate->has_registrasi_komite,
                ])->values(),
            ]);
        } catch (\Throwable $e) {
            Log::error('Gagal browse pendaftar PPDB untuk matrikulasi', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'draw' => (int) ($validated['draw'] ?? 1),
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => [],
                'error' => 'Gagal mengambil data PPDB: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function preview(Request $request)
    {
        $validated = $request->validate([
            'calon_siswa_ids' => 'required|array|min:1',
            'calon_siswa_ids.*' => 'required|string',
            'tahun_pelajaran_id' => 'nullable|exists:tahun_pelajaran,id',
            'include_all' => 'nullable|boolean',
        ]);

        try {
            $preview = $this->service->preview(
                $validated['calon_siswa_ids'],
                $validated['tahun_pelajaran_id'] ?? null,
                false,
                $request->boolean('include_all')
            );

            return response()->json([
                'success' => true,
                'data' => $preview->map(fn ($candidate) => [
                    'id' => $candidate->id,
                    'nama_lengkap' => $candidate->nama_lengkap,
                    'nisn' => $candidate->nisn,
                    'nik' => $candidate->nik,
                    'nomor_registrasi' => $candidate->nomor_registrasi,
                    'nomor_tes' => $candidate->nomor_tes,
                    'tahun_ppdb' => $candidate->ppdb_tahun_nama,
                    'jalur' => $candidate->jalur_nama,
                    'gelombang' => $candidate->gelombang_nama,
                    'jurusan_awal' => $candidate->jurusan_awal,
                    'jurusan_final' => $candidate->jurusan_final,
                    'documents_count' => $candidate->documents_count,
                    'import_status' => $candidate->import_status,
                    'is_lulus' => $candidate->is_lulus,
                    'has_registrasi_komite' => $candidate->has_registrasi_komite,
                ])->values(),
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat preview: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function previewAll(Request $request)
    {
        $validated = $request->validate([
            'tahun_pelajaran_id' => 'required|exists:tahun_pelajaran,id',
        ]);

        try {
            $preview = $this->service->previewAll($validated['tahun_pelajaran_id']);

            return response()->json([
                'success' => true,
                'data' => $preview->map(fn ($candidate) => [
                    'id' => $candidate->id,
                    'nama_lengkap' => $candidate->nama_lengkap,
                    'nisn' => $candidate->nisn,
                    'nik' => $candidate->nik,
                    'nomor_registrasi' => $candidate->nomor_registrasi,
                    'nomor_tes' => $candidate->nomor_tes,
                    'tahun_ppdb' => $candidate->ppdb_tahun_nama,
                    'jalur' => $candidate->jalur_nama,
                    'gelombang' => $candidate->gelombang_nama,
                    'jurusan_awal' => $candidate->jurusan_awal,
                    'jurusan_final' => $candidate->jurusan_final,
                    'documents_count' => $candidate->documents_count,
                    'import_status' => $candidate->import_status,
                    'is_lulus' => $candidate->is_lulus,
                    'has_registrasi_komite' => $candidate->has_registrasi_komite,
                ])->values(),
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil semua data PPDB: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function import(Request $request)
    {
        $validated = $request->validate([
            'calon_siswa_ids' => 'required|array|min:1',
            'calon_siswa_ids.*' => 'required|string',
            'tahun_pelajaran_id' => 'required|exists:tahun_pelajaran,id',
            'kelompok_id' => 'required|exists:matrikulasi_kelompoks,id',
            'include_documents' => 'nullable|boolean',
            'allow_unpaid' => 'nullable|boolean',
        ]);

        try {
            $result = $this->service->import(
                $validated['calon_siswa_ids'],
                $validated['kelompok_id'],
                (bool) ($validated['include_documents'] ?? false),
                $validated['tahun_pelajaran_id'],
                (bool) ($validated['allow_unpaid'] ?? false)
            );

            return response()->json([
                'success' => true,
                'message' => 'Import PPDB selesai.',
                'data' => $result,
            ]);
        } catch (\Throwable $e) {
            Log::error('Gagal import matrikulasi PPDB', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal import: ' . $e->getMessage(),
            ], 500);
        }
    }
}
