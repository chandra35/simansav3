<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MutasiSiswa;
use App\Models\Siswa;
use App\Models\TahunPelajaran;
use App\Models\User;
use App\Services\KemendikbudApiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class MutasiSiswaController extends Controller
{
    /**
     * Daftar mutasi siswa
     */
    public function index(Request $request)
    {
        $this->authorize('view-mutasi');

        $query = MutasiSiswa::with([
            'siswa.siswaKelasRecords.kelas',
            'tahunPelajaran',
            'verifikator',
        ])
            ->orderByDesc('created_at');

        if ($request->filled('jenis')) {
            $query->where('jenis_mutasi', $request->jenis);
        }

        if ($request->filled('status')) {
            $query->where('status_verifikasi', $request->status);
        }

        if ($request->filled('tahun_pelajaran_id')) {
            $query->where('tahun_pelajaran_id', $request->tahun_pelajaran_id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('siswa', function ($q) use ($search) {
                $q->where('nama_lengkap', 'like', "%{$search}%")
                    ->orWhere('nisn', 'like', "%{$search}%");
            });
        }

        $mutasiList = $query->paginate(20)->withQueryString();

        $tahunPelajarans = TahunPelajaran::orderByDesc('tahun_mulai')->get();

        $stats = [
            'total' => MutasiSiswa::count(),
            'masuk' => MutasiSiswa::masuk()->count(),
            'keluar' => MutasiSiswa::keluar()->count(),
            'pending' => MutasiSiswa::pending()->count(),
            'approved' => MutasiSiswa::approved()->count(),
            'rejected' => MutasiSiswa::rejected()->count(),
        ];

        return view('admin.mutasi-siswa.index', compact('mutasiList', 'tahunPelajarans', 'stats'));
    }

    /**
     * AJAX: cari siswa untuk Select2
     */
    public function searchSiswa(Request $request)
    {
        $q = trim($request->get('q', ''));
        if (strlen($q) < 2) {
            return response()->json([]);
        }

        $results = Siswa::where(function ($query) use ($q) {
            $query->where('nama_lengkap', 'like', "%{$q}%")
                ->orWhere('nisn', 'like', "%{$q}%");
        })
            ->limit(25)
            ->get(['id', 'nama_lengkap', 'nisn', 'status_siswa'])
            ->map(fn ($s) => [
            'id' => $s->id,
            'nama_lengkap' => $s->nama_lengkap,
            'nisn' => $s->nisn ?? '-',
            'status_siswa' => $s->status_siswa ?? 'aktif',
        ]);

        return response()->json($results);
    }

    /**
     * Lookup data sekolah berdasarkan NPSN (via KemendikbudApiService)
     */
    public function lookupNpsn(Request $request)
    {
        $npsn = trim($request->get('npsn', ''));

        if (! preg_match('/^\d{8}$/', $npsn)) {
            return response()->json(['success' => false, 'message' => 'NPSN harus 8 digit angka']);
        }

        $service = new KemendikbudApiService;
        $result = $service->getSekolah($npsn);

        if (! $result['success']) {
            return response()->json(['success' => false, 'message' => $result['message'] ?? 'Data tidak ditemukan']);
        }

        $s = $result['data'];

        return response()->json([
            'success' => true,
            'nama' => $s->nama ?? '',
            'alamat' => implode(', ', array_filter([
                $s->alamat_jalan ?? null,
                $s->desa_kelurahan ?? null,
                $s->kecamatan ?? null,
                $s->kabupaten_kota ?? null,
                $s->provinsi ?? null,
            ])),
            'kota' => $s->kabupaten_kota ?? '',
            'provinsi' => $s->provinsi ?? '',
        ]);
    }

    /**
     * Form tambah mutasi
     */
    public function create(Request $request)
    {
        $this->authorize('create-mutasi');

        // Siswa dicari via AJAX (searchSiswa), tidak load semua
        $tahunPelajarans = TahunPelajaran::orderByDesc('tahun_mulai')->get();
        $tahunAktif = TahunPelajaran::where('is_active', true)->first();
        $alasanMutasiKeluarOptions = config('simansa.alasan_mutasi_keluar', []);

        // Jika ada siswa_id di query string (dari halaman siswa)
        $selectedSiswa = null;
        if ($request->filled('siswa_id')) {
            $selectedSiswa = Siswa::select('id', 'nama_lengkap', 'nisn', 'status_siswa')->find($request->siswa_id);
        }

        return view('admin.mutasi-siswa.create', compact(
            'tahunPelajarans',
            'tahunAktif',
            'selectedSiswa',
            'alasanMutasiKeluarOptions'
        ));
    }

    /**
     * Simpan mutasi baru
     */
    public function store(Request $request)
    {
        $this->authorize('create-mutasi');

        $jenis = $request->jenis_mutasi;

        $rules = [
            'jenis_mutasi' => 'required|in:masuk,keluar',
            'tahun_pelajaran_id' => 'required|exists:tahun_pelajaran,id',
            'tanggal_mutasi' => 'required|date',
            'nomor_surat_mutasi' => 'nullable|string|max:100',
            'catatan' => 'nullable|string',
            'file_surat_mutasi' => 'nullable|file|mimes:pdf|max:5120',
        ];

        if ($jenis === 'masuk') {
            $rules['nisn_siswa_baru'] = 'required|string|digits:10|unique:siswa,nisn|unique:users,username';
            $rules['nama_lengkap_baru'] = 'required|string|max:255';
            $rules['jenis_kelamin_baru'] = 'required|in:L,P';
            $rules['sekolah_asal'] = 'required|string|max:200';
            $rules['npsn_sekolah_asal'] = 'nullable|string|max:20';
            $rules['alamat_sekolah_asal'] = 'nullable|string';
            $rules['kelas_asal'] = 'nullable|string|max:50';
            $rules['alasan_mutasi_masuk'] = 'nullable|string';
        } else {
            $rules['siswa_id'] = 'required|exists:siswa,id';
            $rules['sekolah_tujuan'] = 'nullable|string|max:200';
            $rules['npsn_sekolah_tujuan'] = 'nullable|string|max:20';
            $rules['alamat_sekolah_tujuan'] = 'nullable|string';
            $rules['alasan_mutasi_keluar'] = [
                'nullable',
                Rule::in(config('simansa.alasan_mutasi_keluar', [])),
            ];
        }

        $validated = $request->validate($rules);

        DB::beginTransaction();
        try {
            // Mutasi Masuk: buat siswa baru terlebih dahulu
            if ($jenis === 'masuk') {
                $user = User::create([
                    'name' => $validated['nama_lengkap_baru'],
                    'username' => $validated['nisn_siswa_baru'],
                    'email' => $validated['nisn_siswa_baru'].'@student.man1metro.sch.id',
                    'password' => Hash::make($validated['nisn_siswa_baru']),
                    'role' => 'siswa',
                    'is_first_login' => true,
                ]);
                $user->readable_password = $validated['nisn_siswa_baru'];
                $user->save();

                $siswa = Siswa::create([
                    'user_id' => $user->id,
                    'nisn' => $validated['nisn_siswa_baru'],
                    'nama_lengkap' => $validated['nama_lengkap_baru'],
                    'jenis_kelamin' => $validated['jenis_kelamin_baru'],
                ]);

                \App\Models\Ortu::create(['siswa_id' => $siswa->id]);

                // Ganti field siswa-baru dengan siswa_id yang baru dibuat
                unset($validated['nisn_siswa_baru'], $validated['nama_lengkap_baru'], $validated['jenis_kelamin_baru']);
                $validated['siswa_id'] = $siswa->id;
            } else {
                // Simpan snapshot kelas sebelum kelas aktif dilepas saat mutasi disetujui.
                $siswa = Siswa::with('kelasSaatIni')->findOrFail($validated['siswa_id']);
                $validated['kelas_asal'] = $siswa->kelasSaatIni?->nama_lengkap
                    ?? $siswa->kelasSaatIni?->nama_kelas;
            }

            $filePath = null;
            if ($request->hasFile('file_surat_mutasi')) {
                $filePath = $request->file('file_surat_mutasi')->store('mutasi-siswa/surat', 'public');
            }

            $mutasi = MutasiSiswa::create(array_merge(
                $validated,
                ['file_surat_mutasi' => $filePath, 'status_verifikasi' => 'pending']
            ));

            activity()
                ->performedOn($mutasi)
                ->causedBy(Auth::user())
                ->log('Menambahkan mutasi '.$mutasi->jenisMutasiText.' untuk siswa: '.$mutasi->siswa->nama_lengkap);

            DB::commit();

            return redirect()->route('admin.mutasi-siswa.show', $mutasi)
                ->with('success', 'Data mutasi berhasil ditambahkan.');

        } catch (\Exception $e) {
            DB::rollBack();

            return back()->withInput()->with('error', 'Gagal menyimpan mutasi: '.$e->getMessage());
        }
    }

    /**
     * Detail mutasi
     */
    public function show(MutasiSiswa $mutasiSiswa)
    {
        $this->authorize('view-mutasi');
        $mutasiSiswa->load(['siswa', 'tahunPelajaran', 'verifikator']);

        return view('admin.mutasi-siswa.show', compact('mutasiSiswa'));
    }

    /**
     * Form edit mutasi (hanya pending)
     */
    public function edit(MutasiSiswa $mutasiSiswa)
    {
        $this->authorize('edit-mutasi');

        if (! $mutasiSiswa->isPending()) {
            return redirect()->route('admin.mutasi-siswa.show', $mutasiSiswa)
                ->with('error', 'Mutasi yang sudah diverifikasi tidak dapat diedit.');
        }

        $siswaList = Siswa::orderBy('nama_lengkap')->get(['id', 'nama_lengkap', 'nisn', 'status_siswa']);
        $tahunPelajarans = TahunPelajaran::orderByDesc('tahun_mulai')->get();
        $alasanMutasiKeluarOptions = collect(config('simansa.alasan_mutasi_keluar', []))
            ->when(
                filled($mutasiSiswa->alasan_mutasi_keluar)
                    && ! in_array($mutasiSiswa->alasan_mutasi_keluar, config('simansa.alasan_mutasi_keluar', []), true),
                fn ($options) => $options->push($mutasiSiswa->alasan_mutasi_keluar)
            )
            ->values()
            ->all();

        return view('admin.mutasi-siswa.edit', compact(
            'mutasiSiswa',
            'siswaList',
            'tahunPelajarans',
            'alasanMutasiKeluarOptions'
        ));
    }

    /**
     * Update mutasi
     */
    public function update(Request $request, MutasiSiswa $mutasiSiswa)
    {
        $this->authorize('edit-mutasi');

        if (! $mutasiSiswa->isPending()) {
            return response()->json(['success' => false, 'message' => 'Mutasi yang sudah diverifikasi tidak dapat diedit.'], 422);
        }

        $jenis = $mutasiSiswa->jenis_mutasi;

        $rules = [
            'tahun_pelajaran_id' => 'required|exists:tahun_pelajaran,id',
            'tanggal_mutasi' => 'required|date',
            'nomor_surat_mutasi' => 'nullable|string|max:100',
            'catatan' => 'nullable|string',
            'file_surat_mutasi' => 'nullable|file|mimes:pdf|max:5120',
        ];

        if ($jenis === 'masuk') {
            $rules['sekolah_asal'] = 'required|string|max:200';
            $rules['npsn_sekolah_asal'] = 'nullable|string|max:20';
            $rules['alamat_sekolah_asal'] = 'nullable|string';
            $rules['kelas_asal'] = 'nullable|string|max:50';
            $rules['alasan_mutasi_masuk'] = 'nullable|string';
        } else {
            $rules['sekolah_tujuan'] = 'nullable|string|max:200';
            $rules['npsn_sekolah_tujuan'] = 'nullable|string|max:20';
            $rules['alamat_sekolah_tujuan'] = 'nullable|string';
            $rules['alasan_mutasi_keluar'] = [
                'nullable',
                Rule::in(array_merge(
                    config('simansa.alasan_mutasi_keluar', []),
                    array_filter([$mutasiSiswa->alasan_mutasi_keluar])
                )),
            ];
        }

        $validated = $request->validate($rules);

        DB::beginTransaction();
        try {
            if ($request->hasFile('file_surat_mutasi')) {
                // Hapus file lama
                if ($mutasiSiswa->file_surat_mutasi) {
                    Storage::disk('public')->delete($mutasiSiswa->file_surat_mutasi);
                }
                $validated['file_surat_mutasi'] = $request->file('file_surat_mutasi')
                    ->store('mutasi-siswa/surat', 'public');
            }

            $mutasiSiswa->update($validated);

            activity()
                ->performedOn($mutasiSiswa)
                ->causedBy(Auth::user())
                ->log('Mengubah data mutasi untuk siswa: '.$mutasiSiswa->siswa->nama_lengkap);

            DB::commit();

            return redirect()->route('admin.mutasi-siswa.show', $mutasiSiswa)
                ->with('success', 'Data mutasi berhasil diperbarui.');

        } catch (\Exception $e) {
            DB::rollBack();

            return back()->withInput()->with('error', 'Gagal memperbarui mutasi: '.$e->getMessage());
        }
    }

    /**
     * Hapus mutasi (hanya pending)
     */
    public function destroy(MutasiSiswa $mutasiSiswa)
    {
        $this->authorize('delete-mutasi');

        if (! $mutasiSiswa->isPending()) {
            return response()->json(['success' => false, 'message' => 'Mutasi yang sudah diverifikasi tidak dapat dihapus.'], 422);
        }

        DB::beginTransaction();
        try {
            $siswaName = $mutasiSiswa->siswa->nama_lengkap ?? '-';

            if ($mutasiSiswa->file_surat_mutasi) {
                Storage::disk('public')->delete($mutasiSiswa->file_surat_mutasi);
            }

            $mutasiSiswa->delete();

            activity()
                ->causedBy(Auth::user())
                ->log('Menghapus data mutasi untuk siswa: '.$siswaName);

            DB::commit();

            return response()->json(['success' => true, 'message' => 'Data mutasi berhasil dihapus.']);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json(['success' => false, 'message' => 'Gagal menghapus: '.$e->getMessage()], 500);
        }
    }

    /**
     * Approve mutasi
     */
    public function approve(Request $request, MutasiSiswa $mutasiSiswa)
    {
        $this->authorize('approve-mutasi');
        $validated = $request->validate([
            'catatan' => 'nullable|string|max:2000',
        ]);

        try {
            DB::transaction(function () use ($mutasiSiswa, $validated): void {
                $lockedMutation = MutasiSiswa::query()
                    ->with('siswa')
                    ->lockForUpdate()
                    ->findOrFail($mutasiSiswa->getKey());

                if (! $lockedMutation->isPending()) {
                    throw new \DomainException('Mutasi ini sudah diverifikasi oleh pengguna lain.');
                }

                if (! $lockedMutation->siswa) {
                    throw new \DomainException('Data siswa pada mutasi ini tidak ditemukan.');
                }

                $before = [
                    'status_verifikasi' => $lockedMutation->status_verifikasi,
                    'status_siswa' => $lockedMutation->siswa->status_siswa,
                    'kelas_saat_ini_id' => $lockedMutation->siswa->kelas_saat_ini_id,
                ];

                $lockedMutation->approveMutasi(Auth::user(), $validated['catatan'] ?? null);
                $lockedMutation->refresh()->load('siswa');

                activity()
                    ->performedOn($lockedMutation)
                    ->causedBy(Auth::user())
                    ->withProperties([
                        'before' => $before,
                        'after' => [
                            'status_verifikasi' => $lockedMutation->status_verifikasi,
                            'status_siswa' => $lockedMutation->siswa->status_siswa,
                            'kelas_saat_ini_id' => $lockedMutation->siswa->kelas_saat_ini_id,
                        ],
                        'jenis_mutasi' => $lockedMutation->jenis_mutasi,
                    ])
                    ->log('Menyetujui mutasi untuk siswa: '.$lockedMutation->siswa->nama_lengkap);
            }, 3);

            $message = 'Mutasi berhasil disetujui. Status siswa, riwayat kelas, dan akses akun telah diperbarui.';

            if ($request->expectsJson()) {
                return response()->json(['success' => true, 'message' => $message]);
            }

            return redirect()
                ->route('admin.mutasi-siswa.show', $mutasiSiswa)
                ->with('success', $message);
        } catch (\DomainException $exception) {
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => $exception->getMessage()], 422);
            }

            return back()->with('error', $exception->getMessage());
        } catch (\Throwable $exception) {
            report($exception);

            $message = 'Mutasi belum dapat disetujui. Silakan muat ulang halaman dan coba kembali.';

            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => $message], 500);
            }

            return back()->with('error', $message);
        }
    }

    /**
     * Reject mutasi
     */
    public function reject(Request $request, MutasiSiswa $mutasiSiswa)
    {
        $this->authorize('reject-mutasi');

        if (! $mutasiSiswa->isPending()) {
            return response()->json(['success' => false, 'message' => 'Mutasi ini sudah diverifikasi.'], 422);
        }

        $request->validate(['alasan' => 'required|string|min:10']);

        DB::beginTransaction();
        try {
            $mutasiSiswa->rejectMutasi(Auth::user(), $request->alasan);

            activity()
                ->performedOn($mutasiSiswa)
                ->causedBy(Auth::user())
                ->log('Menolak mutasi untuk siswa: '.$mutasiSiswa->siswa->nama_lengkap);

            DB::commit();

            return response()->json(['success' => true, 'message' => 'Mutasi berhasil ditolak.']);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json(['success' => false, 'message' => 'Gagal menolak: '.$e->getMessage()], 500);
        }
    }

    /**
     * Upload dokumen surat mutasi
     */
    public function uploadDokumen(Request $request, MutasiSiswa $mutasiSiswa)
    {
        $this->authorize('upload-dokumen-mutasi');

        $request->validate([
            'file_surat_mutasi' => 'required|file|mimes:pdf|max:5120',
        ]);

        try {
            if ($mutasiSiswa->file_surat_mutasi) {
                Storage::disk('public')->delete($mutasiSiswa->file_surat_mutasi);
            }

            $path = $request->file('file_surat_mutasi')->store('mutasi-siswa/surat', 'public');
            $mutasiSiswa->update(['file_surat_mutasi' => $path]);

            return response()->json(['success' => true, 'message' => 'Dokumen berhasil diunggah.']);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Gagal mengunggah: '.$e->getMessage()], 500);
        }
    }
}
