<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Siswa;
use App\Models\VerifikasiIjazah;
use App\Models\VerifikasiIjazahLog;
use App\Models\DokumenSiswa;
use App\Services\VerifikasiIjazahService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class VerifikasiIjazahController extends Controller
{
    public function __construct(private readonly VerifikasiIjazahService $service) {}

    /**
     * Daftar siswa dengan status verifikasi ijazah
     */
    public function index(Request $request)
    {
        $this->authorize('verifikasi-ijazah');

        $statusFilter = $request->get('status', 'semua');
        $kelasFilter  = $request->get('kelas_id', '');
        $search       = $request->get('search', '');

        $query = Siswa::with(['verifikasiIjazah', 'kelasSaatIni'])
            ->orderBy('nama_lengkap');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('nama_lengkap', 'like', "%{$search}%")
                  ->orWhere('nisn', 'like', "%{$search}%")
                  ->orWhere('nik', 'like', "%{$search}%");
            });
        }

        if ($kelasFilter) {
            $query->where('kelas_saat_ini_id', $kelasFilter);
        }

        if ($statusFilter !== 'semua') {
            if ($statusFilter === 'belum_diverifikasi') {
                $query->whereDoesntHave('verifikasiIjazah')
                      ->orWhereHas('verifikasiIjazah', fn($q) => $q->where('status', 'belum_diverifikasi'));
            } else {
                $query->whereHas('verifikasiIjazah', fn($q) => $q->where('status', $statusFilter));
            }
        }

        $siswaList = $query->paginate(25)->withQueryString();

        // Summary stats
        $stats = [
            'total'            => Siswa::count(),
            'belum'            => Siswa::whereDoesntHave('verifikasiIjazah')->count() +
                                   VerifikasiIjazah::where('status', 'belum_diverifikasi')->count(),
            'sesuai'           => VerifikasiIjazah::where('status', 'sesuai')->count(),
            'tidak_sesuai'     => VerifikasiIjazah::where('status', 'tidak_sesuai')->count(),
            'perlu_perbaikan'  => VerifikasiIjazah::where('status', 'perlu_perbaikan')->count(),
        ];

        $kelasOptions = \App\Models\Kelas::orderBy('tingkat')->orderBy('nama_kelas')->get();

        return view('admin.verifikasi-ijazah.index', compact('siswaList', 'stats', 'statusFilter', 'kelasFilter', 'search', 'kelasOptions'));
    }

    /**
     * Halaman detail verifikasi satu siswa
     */
    public function show(Siswa $siswa)
    {
        $this->authorize('verifikasi-ijazah');

        $siswa->load(['ortu', 'kelasSaatIni', 'dokumen']);

        // Ambil dokumen ijazah & KK
        $dokumenIjazah = $siswa->dokumen->filter(fn($d) => str_contains(strtolower($d->jenis_dokumen), 'ijazah'))->values();
        $dokumenKK     = $siswa->dokumen->filter(fn($d) => str_contains(strtolower($d->jenis_dokumen), 'kk') || str_contains(strtolower($d->jenis_dokumen), 'kartu keluarga'))->values();
        $dokumenLain   = $siswa->dokumen->filter(fn($d) => !str_contains(strtolower($d->jenis_dokumen), 'ijazah') && !str_contains(strtolower($d->jenis_dokumen), 'kk') && !str_contains(strtolower($d->jenis_dokumen), 'kartu keluarga'))->values();

        // Data verifikasi yang sudah ada
        $verifikasi = VerifikasiIjazah::with('logs')->where('siswa_id', $siswa->id)->first();

        // Data Simansa saat ini
        $dataSimansa = $this->service->getDataSimansa($siswa);

        // Jika sudah pernah diverifikasi, tampilkan data EMIS yang tersimpan
        // Jika belum, fetch dari API langsung
        $dataEmis    = null;
        $emisError   = null;

        if ($verifikasi && ($verifikasi->data_emis_kemdikbud || $verifikasi->data_emis_kemenag)) {
            $dataEmis = [
                'kemdikbud' => $verifikasi->data_emis_kemdikbud,
                'kemenag'   => $verifikasi->data_emis_kemenag,
                'error'     => null,
            ];
        } elseif ($siswa->nisn) {
            $dataEmis  = $this->service->fetchDataEmis($siswa->nisn);
            $emisError = $dataEmis['error'];
        } else {
            $emisError = 'Siswa tidak memiliki NISN, tidak bisa mengambil data EMIS.';
        }

        // Compare otomatis untuk highlight perbedaan
        $fieldBeda = [];
        if ($dataEmis && !$emisError) {
            $fieldBeda = $this->service->compareData(
                $dataSimansa,
                $dataEmis['kemdikbud'],
                $dataEmis['kemenag']
            );
        }

        $verifikasiFields = VerifikasiIjazahService::$verifikasiFields;

        return view('admin.verifikasi-ijazah.show', compact(
            'siswa',
            'verifikasi',
            'dataSimansa',
            'dataEmis',
            'emisError',
            'fieldBeda',
            'verifikasiFields',
            'dokumenIjazah',
            'dokumenKK',
            'dokumenLain'
        ));
    }

    /**
     * Simpan hasil verifikasi
     */
    public function store(Request $request, Siswa $siswa)
    {
        $this->authorize('verifikasi-ijazah');

        $request->validate([
            'status'              => 'required|in:sesuai,tidak_sesuai,perlu_perbaikan',
            'catatan'             => 'nullable|string|max:2000',
            'field_tidak_sesuai'  => 'nullable|array',
            'field_tidak_sesuai.*' => 'string',
            'saran_perbaikan'     => 'nullable|array',
            'data_emis_kemdikbud' => 'nullable|string',
            'data_emis_kemenag'   => 'nullable|string',
        ]);

        try {
            $dataEmisKemdikbud = null;
            $dataEmisKemenag   = null;

            if ($request->data_emis_kemdikbud) {
                $dataEmisKemdikbud = json_decode($request->data_emis_kemdikbud, true);
            }
            if ($request->data_emis_kemenag) {
                $dataEmisKemenag = json_decode($request->data_emis_kemenag, true);
            }

            $this->service->simpanVerifikasi(
                siswa:            $siswa,
                verifikator:      Auth::user(),
                status:           $request->status,
                fieldTidakSesuai: $request->field_tidak_sesuai ?? [],
                saranPerbaikan:   $request->saran_perbaikan ?? [],
                catatan:          $request->catatan ?? '',
                dataEmis: [
                    'kemdikbud' => $dataEmisKemdikbud,
                    'kemenag'   => $dataEmisKemenag,
                ]
            );

            return redirect()
                ->route('admin.verifikasi-ijazah.show', $siswa)
                ->with('success', 'Verifikasi berhasil disimpan.');
        } catch (\Exception $e) {
            Log::error('VerifikasiIjazah store error', ['error' => $e->getMessage()]);
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Refresh data EMIS dari API (AJAX)
     */
    public function refreshEmis(Request $request, Siswa $siswa)
    {
        $this->authorize('verifikasi-ijazah');

        if (!$siswa->nisn) {
            return response()->json(['success' => false, 'message' => 'Siswa tidak memiliki NISN.']);
        }

        $dataEmis = $this->service->fetchDataEmis($siswa->nisn);

        if ($dataEmis['error']) {
            return response()->json(['success' => false, 'message' => $dataEmis['error']]);
        }

        // Jika sudah ada record verifikasi, update data EMIS-nya
        $verifikasi = VerifikasiIjazah::where('siswa_id', $siswa->id)->first();
        if ($verifikasi) {
            $this->service->refreshDataEmis($verifikasi, Auth::user());
        }

        // Re-compare
        $dataSimansa = $this->service->getDataSimansa($siswa);
        $fieldBeda   = $this->service->compareData($dataSimansa, $dataEmis['kemdikbud'], $dataEmis['kemenag']);

        return response()->json([
            'success'    => true,
            'kemdikbud'  => $dataEmis['kemdikbud'],
            'kemenag'    => $dataEmis['kemenag'],
            'field_beda' => $fieldBeda,
        ]);
    }

    /**
     * Data DataTables untuk index (AJAX)
     */
    public function data(Request $request)
    {
        $this->authorize('verifikasi-ijazah');

        $query = Siswa::with(['verifikasiIjazah', 'kelasSaatIni'])
            ->select('siswa.*');

        if ($request->search_name) {
            $s = $request->search_name;
            $query->where(function ($q) use ($s) {
                $q->where('nama_lengkap', 'like', "%{$s}%")
                  ->orWhere('nisn', 'like', "%{$s}%");
            });
        }

        if ($request->status && $request->status !== 'semua') {
            if ($request->status === 'belum_diverifikasi') {
                $query->whereDoesntHave('verifikasiIjazah');
            } else {
                $query->whereHas('verifikasiIjazah', fn($q) => $q->where('status', $request->status));
            }
        }

        if ($request->kelas_id) {
            $query->where('kelas_saat_ini_id', $request->kelas_id);
        }

        $total   = $query->count();
        $siswa   = $query->orderBy('nama_lengkap')
            ->skip($request->start ?? 0)
            ->take($request->length ?? 25)
            ->get();

        $rows = $siswa->map(function ($s, $i) use ($request) {
            $verif  = $s->verifikasiIjazah;
            $status = $verif ? $verif->status : 'belum_diverifikasi';

            $badge = match ($status) {
                'sesuai'           => '<span class="badge badge-success">Sesuai</span>',
                'tidak_sesuai'     => '<span class="badge badge-danger">Tidak Sesuai</span>',
                'perlu_perbaikan'  => '<span class="badge badge-warning">Perlu Perbaikan</span>',
                default            => '<span class="badge badge-secondary">Belum</span>',
            };

            $kelas     = $s->kelasSaatIni?->nama_lengkap ?? '-';
            $verifikBy = $verif?->verifikator_nama ?? '-';
            $verifikAt = $verif?->verified_at?->format('d/m/Y H:i') ?? '-';

            $btnDetail = '<a href="' . route('admin.verifikasi-ijazah.show', $s->id) . '" class="btn btn-sm btn-primary"><i class="fas fa-search"></i> Verifikasi</a>';

            return [
                ($request->start ?? 0) + $i + 1,
                e($s->nama_lengkap),
                e($s->nisn ?? '-'),
                e($kelas),
                $badge,
                e($verifikBy),
                e($verifikAt),
                $btnDetail,
            ];
        });

        return response()->json([
            'draw'            => intval($request->draw),
            'recordsTotal'    => $total,
            'recordsFiltered' => $total,
            'data'            => $rows,
        ]);
    }
}
