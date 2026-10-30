<?php

namespace App\Http\Controllers\Admin\WaliKelas;

use App\Models\CatatanWaliKelas;
use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\SiswaKelas;
use App\Services\ActivityLogService;
use App\Services\StudentBiodataPdfService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;

class SiswaController extends BaseWaliKelasController
{
    /**
     * Daftar siswa rombel wali (read-only, tabel lengkap client-side).
     */
    public function index()
    {
        $kelas = $this->resolveKelas(request('kelas_id'));
        $siswa = $this->classStudents($kelas);

        return view('admin.gtk.wali.siswa.index', [
            'kelas' => $kelas,
            'kelasList' => $this->waliClasses(),
            'siswa' => $siswa,
            'stats' => [
                'total' => $siswa->count(),
                'laki_laki' => $siswa->where('jenis_kelamin', 'L')->count(),
                'perempuan' => $siswa->where('jenis_kelamin', 'P')->count(),
                'data_lengkap' => $siswa->filter(fn ($item) => $item->isDataComplete())->count(),
            ],
            'kategoriList' => CatatanWaliKelas::KATEGORI,
        ]);
    }

    /** Tetapkan ketua atau sekretaris hanya untuk rombel wali kelas sendiri. */
    public function assignPengurus(Request $request)
    {
        $validated = $request->validate([
            'kelas_id' => ['required', 'uuid'],
            'jabatan' => ['required', 'in:ketua,sekretaris'],
            'siswa_id' => ['nullable', 'uuid', 'exists:siswa,id'],
        ]);

        $kelas = $this->resolveKelas($validated['kelas_id']);
        $jabatan = $validated['jabatan'];
        $prefix = $jabatan === 'ketua' ? 'ketua_kelas' : 'sekretaris_kelas';
        $label = $jabatan === 'ketua' ? 'Ketua Kelas' : 'Sekretaris Kelas';

        try {
            $result = DB::transaction(function () use ($kelas, $validated, $prefix, $label) {
                $records = SiswaKelas::query()->with('siswa')
                    ->where('kelas_id', $kelas->id)->where('tahun_pelajaran_id', $kelas->tahun_pelajaran_id)
                    ->where('status', 'aktif')->lockForUpdate()->get();
                $current = $records->first(fn (SiswaKelas $record) => $record->{$prefix} && $record->{"{$prefix}_selesai_at"} === null);
                $selectedId = $validated['siswa_id'] ?? null;
                $selected = filled($selectedId) ? $records->firstWhere('siswa_id', $selectedId) : null;

                if (filled($selectedId) && ! $selected) {
                    throw new \DomainException("{$label} harus dipilih dari siswa aktif pada rombel Anda.");
                }
                if ($current && $selected && $current->is($selected)) {
                    return ['message' => "{$selected->siswa->nama_lengkap} sudah menjadi {$label}."];
                }
                if ($current) {
                    $current->update(["{$prefix}_selesai_at" => now()]);
                    ActivityLogService::log(['activity_type' => "selesai_jabatan_{$prefix}", 'model_type' => Siswa::class, 'model_id' => $current->siswa_id, 'description' => "Mengakhiri jabatan {$label} {$kelas->nama_lengkap}", 'properties' => ['kelas_id' => $kelas->id, 'tahun_pelajaran_id' => $kelas->tahun_pelajaran_id]]);
                }
                if (! $selected) {
                    return ['message' => "Penugasan {$label} berhasil dikosongkan."];
                }
                $selected->update([$prefix => true, "{$prefix}_mulai_at" => now(), "{$prefix}_selesai_at" => null, "{$prefix}_ditetapkan_by" => Auth::id()]);
                ActivityLogService::log(['activity_type' => "penetapan_{$prefix}", 'model_type' => Siswa::class, 'model_id' => $selected->siswa_id, 'description' => "Ditetapkan sebagai {$label} {$kelas->nama_lengkap}", 'properties' => ['kelas_id' => $kelas->id, 'tahun_pelajaran_id' => $kelas->tahun_pelajaran_id, 'ditetapkan_by' => Auth::id()]]);

                return ['message' => "{$selected->siswa->nama_lengkap} berhasil ditetapkan sebagai {$label}."];
            });

            return response()->json(['success' => true] + $result);
        } catch (\DomainException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    /**
     * Detail siswa (read-only) — hanya siswa di rombel wali.
     */
    public function show(string $siswa)
    {
        $siswa = $this->resolveSiswa($siswa);
        $siswa->load([
            'user',
            'ortu.provinsi',
            'ortu.kabupaten',
            'ortu.kecamatan',
            'ortu.kelurahan',
            'provinsiSiswa',
            'kabupatenSiswa',
            'kecamatanSiswa',
            'kelurahanSiswa',
            'sekolahAsal',
            'kelasTahunAktif',
            'dokumen' => fn ($query) => $query->latest(),
        ]);

        $catatan = CatatanWaliKelas::query()
            ->where('siswa_id', $siswa->id)
            ->where('created_by', auth()->id())
            ->latest('tanggal')
            ->latest('created_at')
            ->limit(10)
            ->get();

        $viewData = [
            'siswa' => $siswa,
            'catatan' => $catatan,
        ];

        if (request()->ajax()) {
            return response()->json([
                'title' => $siswa->nama_lengkap,
                'html' => view('admin.gtk.wali.siswa.partials.detail', $viewData)->render(),
            ]);
        }

        return view('admin.gtk.wali.siswa.show', $viewData);
    }

    /** Cetak biodata siswa yang benar-benar berada di rombel wali kelas aktif. */
    public function printBiodata(string $siswa, StudentBiodataPdfService $biodataPdf)
    {
        $this->authorize('print-siswa-biodata');

        return $biodataPdf->stream($this->resolveSiswa($siswa));
    }

    /**
     * Ambil siswa aktif suatu rombel, terurut alfabetis berdasarkan nama.
     */
    protected function classStudents(Kelas $kelas): Collection
    {
        return $kelas->siswaAktif()
            ->wherePivot('tahun_pelajaran_id', $kelas->tahun_pelajaran_id)
            ->orderBy('nama_lengkap')
            ->get();
    }
}
