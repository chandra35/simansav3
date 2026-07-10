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
            'smart' => 'nullable|boolean',
        ]);

        try {
            $candidates = $this->service->searchCandidates(
                $request->get('q'),
                $request->get('tahun_pelajaran_id'),
                50,
                $request->boolean('include_all'),
                $request->boolean('smart')
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

    public function peserta(Request $request)
    {
        $validated = $request->validate([
            'draw' => 'nullable|integer|min:1',
            'start' => 'nullable|integer|min:0',
            'length' => 'nullable|integer|min:1|max:100',
            'search.value' => 'nullable|string|max:100',
            'tahun_pelajaran_id' => 'required|exists:tahun_pelajaran,id',
            'kelompok_id' => 'nullable|exists:matrikulasi_kelompoks,id',
        ]);

        $query = $this->service->pesertaFor($validated['tahun_pelajaran_id']);
        if ($request->filled('kelompok_id')) {
            $query->where('matrikulasi_kelompok_id', $request->get('kelompok_id'));
        }

        $recordsTotal = (clone $query)->count();
        $term = trim((string) data_get($validated, 'search.value', ''));
        if ($term !== '') {
            $like = '%' . str_replace(' ', '%', $term) . '%';
            $query->where(function ($q) use ($like) {
                $q->where('nama_lengkap', 'like', $like)
                    ->orWhere('nisn', 'like', $like)
                    ->orWhere('nomor_tes', 'like', $like)
                    ->orWhere('nik', 'like', $like);
            });
        }

        $recordsFiltered = (clone $query)->count();
        $rows = $query
            ->orderBy('nama_lengkap')
            ->skip((int) ($validated['start'] ?? 0))
            ->take((int) ($validated['length'] ?? 25))
            ->get();

        return response()->json([
            'draw' => (int) ($validated['draw'] ?? 1),
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data' => $rows->map(fn ($peserta) => [
                'id' => $peserta->id,
                'nama_lengkap' => $peserta->nama_lengkap,
                'nisn' => $peserta->nisn,
                'nik' => $peserta->nik,
                'nomor_tes' => $peserta->nomor_tes,
                'jenis_kelamin' => $peserta->jenis_kelamin,
                'jurusan' => $peserta->jurusan_final ?: $peserta->jurusan_awal,
                'kelompok_id' => $peserta->matrikulasi_kelompok_id,
                'kelompok' => $peserta->kelompok?->nama,
                'label_kelas' => $peserta->kelompok?->label_kelas,
                'akun' => (bool) $peserta->user_id,
                'username' => $peserta->user?->username,
                'last_login_at' => optional($peserta->user?->latestSession?->last_activity)->toDateTimeString(),
                'is_online' => (bool) ($peserta->user?->latestSession?->isStillOnline() ?? false),
                'dokumens_count' => $peserta->dokumens_count,
                'status' => $peserta->status,
                'status_pembayaran' => $peserta->status_pembayaran,
                'status_matrikulasi' => $peserta->status_matrikulasi,
                'tanggal_hadir_matrikulasi' => optional($peserta->tanggal_hadir_matrikulasi)->format('d/m/Y'),
                'catatan_validasi' => $peserta->catatan_validasi,
            ])->values(),
        ]);
    }

    public function updateValidation(Request $request)
    {
        $validated = $request->validate([
            'peserta_ids' => 'required|array|min:1',
            'peserta_ids.*' => 'required|exists:matrikulasi_pesertas,id',
            'tahun_pelajaran_id' => 'required|exists:tahun_pelajaran,id',
            'status_pembayaran' => 'nullable|in:sudah_bayar_ppdb,susulan_bayar,belum_bayar,dibebaskan',
            'status_matrikulasi' => 'nullable|in:terdaftar,hadir,tidak_hadir,mengundurkan_diri,siap_ditetapkan',
            'tanggal_hadir_matrikulasi' => 'nullable|date',
            'catatan_validasi' => 'nullable|string|max:1000',
        ]);

        if (!array_key_exists('status_pembayaran', $validated) && !array_key_exists('status_matrikulasi', $validated)) {
            return response()->json([
                'success' => false,
                'message' => 'Pilih minimal satu status yang akan diperbarui.',
            ], 422);
        }

        try {
            $count = $this->service->updateLocalValidation(
                $validated['peserta_ids'],
                $validated['tahun_pelajaran_id'],
                $validated
            );

            return response()->json([
                'success' => true,
                'message' => "{$count} peserta berhasil diperbarui.",
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui validasi: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function assignKelompok(Request $request)
    {
        $validated = $request->validate([
            'peserta_ids' => 'required|array|min:1',
            'peserta_ids.*' => 'required|exists:matrikulasi_pesertas,id',
            'tahun_pelajaran_id' => 'required|exists:tahun_pelajaran,id',
            'kelompok_id' => 'required|exists:matrikulasi_kelompoks,id',
        ]);

        try {
            $count = $this->service->assignKelompok(
                $validated['peserta_ids'],
                $validated['kelompok_id'],
                $validated['tahun_pelajaran_id']
            );

            return response()->json([
                'success' => true,
                'message' => "{$count} peserta berhasil diassign ke kelompok.",
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal assign kelompok: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function generateAccounts(Request $request)
    {
        $validated = $request->validate([
            'peserta_ids' => 'required|array|min:1',
            'peserta_ids.*' => 'required|exists:matrikulasi_pesertas,id',
            'tahun_pelajaran_id' => 'required|exists:tahun_pelajaran,id',
        ]);

        try {
            $result = $this->service->generateAccounts($validated['peserta_ids'], $validated['tahun_pelajaran_id']);

            return response()->json([
                'success' => true,
                'message' => "Akun dibuat: {$result['created']}, sudah ada: {$result['existing']}, gagal: {$result['failed']}.",
                'data' => $result,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal generate akun: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function promoteToSiswa(Request $request)
    {
        $validated = $request->validate([
            'peserta_ids' => 'required|array|min:1',
            'peserta_ids.*' => 'required|exists:matrikulasi_pesertas,id',
            'tahun_pelajaran_id' => 'required|exists:tahun_pelajaran,id',
        ]);

        try {
            $result = $this->service->promoteToSiswa($validated['peserta_ids'], $validated['tahun_pelajaran_id']);

            return response()->json([
                'success' => true,
                'message' => "Penetapan selesai: {$result['success']} berhasil, {$result['existing']} sudah siswa, {$result['failed']} gagal.",
                'data' => $result,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menetapkan siswa: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function import(Request $request)
    {
        $validated = $request->validate([
            'calon_siswa_ids' => 'required|array|min:1',
            'calon_siswa_ids.*' => 'required|string',
            'tahun_pelajaran_id' => 'required|exists:tahun_pelajaran,id',
            'kelompok_id' => 'nullable|exists:matrikulasi_kelompoks,id',
            'include_documents' => 'nullable|boolean',
            'allow_unpaid' => 'nullable|boolean',
        ]);

        try {
            Log::info('Mulai import matrikulasi PPDB', [
                'count' => count($validated['calon_siswa_ids']),
                'tahun_pelajaran_id' => $validated['tahun_pelajaran_id'],
                'kelompok_id' => $validated['kelompok_id'] ?? null,
                'include_documents' => (bool) ($validated['include_documents'] ?? false),
                'allow_unpaid' => (bool) ($validated['allow_unpaid'] ?? false),
                'user_id' => optional($request->user())->id,
            ]);

            $result = $this->service->import(
                $validated['calon_siswa_ids'],
                $validated['kelompok_id'] ?? null,
                (bool) ($validated['include_documents'] ?? false),
                $validated['tahun_pelajaran_id'],
                (bool) ($validated['allow_unpaid'] ?? false)
            );

            Log::info('Selesai import matrikulasi PPDB', [
                'count' => count($validated['calon_siswa_ids']),
                'success' => $result['success'] ?? 0,
                'failed' => $result['failed'] ?? 0,
                'documents_copied' => $result['documents_copied'] ?? 0,
                'tahun_pelajaran_id' => $validated['tahun_pelajaran_id'],
                'kelompok_id' => $validated['kelompok_id'] ?? null,
                'user_id' => optional($request->user())->id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Import PPDB selesai.',
                'data' => $result,
            ]);
        } catch (\Throwable $e) {
            Log::error('Gagal import matrikulasi PPDB', [
                'error' => $e->getMessage(),
                'count' => count($validated['calon_siswa_ids'] ?? []),
                'tahun_pelajaran_id' => $validated['tahun_pelajaran_id'] ?? null,
                'kelompok_id' => $validated['kelompok_id'] ?? null,
                'user_id' => optional($request->user())->id,
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal import: ' . $e->getMessage(),
            ], 500);
        }
    }
}
