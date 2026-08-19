<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AlumniProfile;
use Illuminate\Http\Request;

class AlumniController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('view-siswa');

        $query = AlumniProfile::query()->with('siswa:id,nama_lengkap,foto_profile');
        if ($request->filled('q')) {
            $term = trim((string) $request->q);
            $query->where(fn ($q) => $q->where('nama_lengkap', 'like', "%{$term}%")
                ->orWhere('nisn', 'like', "%{$term}%")->orWhere('nik', 'like', "%{$term}%"));
        }
        foreach (['angkatan', 'status_setelah_lulus', 'status_verifikasi'] as $filter) {
            if ($request->filled($filter)) $query->where($filter, $request->input($filter));
        }

        $alumni = $query->orderByDesc('tahun_lulus')->orderBy('nama_lengkap')->paginate(25)->withQueryString();
        $base = AlumniProfile::query();
        $byYear = (clone $base)->selectRaw('angkatan, COUNT(*) total')->whereNotNull('angkatan')
            ->groupBy('angkatan')->orderBy('angkatan')->get();

        return view('admin.alumni.index', [
            'alumni' => $alumni,
            'angkatanList' => (clone $base)->whereNotNull('angkatan')->distinct()->orderByDesc('angkatan')->pluck('angkatan'),
            'stats' => [
                'total' => (clone $base)->count(),
                'terverifikasi' => (clone $base)->where('status_verifikasi', 'terverifikasi')->count(),
                'kontak' => (clone $base)->whereNotNull('nomor_hp')->count(),
                'historis' => (clone $base)->where('sumber_data', 'historis')->count(),
                'labels' => $byYear->pluck('angkatan')->values(),
                'values' => $byYear->pluck('total')->values(),
            ],
        ]);
    }

    public function store(Request $request)
    {
        $this->authorize('edit-siswa');
        AlumniProfile::create($this->validated($request) + [
            'sumber_data' => 'manual',
            'status_verifikasi' => 'belum_diverifikasi',
        ]);
        return back()->with('success', 'Profil alumni historis berhasil ditambahkan.');
    }

    public function show(AlumniProfile $alumni)
    {
        $this->authorize('view-siswa');
        $alumni->load(['siswa.user', 'siswa.ortu', 'siswa.sekolahAsal', 'siswa.siswaKelasRecords.tahunPelajaran']);
        $canExportLegger = $alumni->siswa
            && $alumni->siswa->siswaKelasRecords->contains(fn ($record) => $record->tingkat === 12);

        return view('admin.alumni.show', compact('alumni', 'canExportLegger'));
    }

    public function update(Request $request, AlumniProfile $alumni)
    {
        $this->authorize('edit-siswa');
        $alumni->update($this->validated($request) + [
            'last_profile_update_at' => now(),
        ]);
        return back()->with('success', 'Profil alumni berhasil diperbarui.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'angkatan' => ['nullable', 'string', 'max:30'], 'tahun_lulus' => ['nullable', 'integer', 'min:1900', 'max:2100'],
            'nama_lengkap' => ['required', 'string', 'max:160'], 'nisn' => ['nullable', 'string', 'max:20'], 'nik' => ['nullable', 'string', 'max:20'],
            'jenis_kelamin' => ['nullable', 'in:L,P'], 'tempat_lahir' => ['nullable', 'string', 'max:100'], 'tanggal_lahir' => ['nullable', 'date'],
            'nomor_hp' => ['nullable', 'string', 'max:30'], 'email' => ['nullable', 'email', 'max:255'], 'alamat' => ['nullable', 'string'],
            'kabupaten_kota' => ['nullable', 'string', 'max:120'], 'provinsi' => ['nullable', 'string', 'max:120'],
            'status_setelah_lulus' => ['nullable', 'in:kuliah,bekerja,wirausaha,pesantren,belum_terdata,lainnya'],
            'institusi_lanjutan' => ['nullable', 'string', 'max:180'], 'program_studi' => ['nullable', 'string', 'max:180'],
            'pekerjaan' => ['nullable', 'string', 'max:160'], 'instansi' => ['nullable', 'string', 'max:180'],
            'status_verifikasi' => ['nullable', 'in:belum_diverifikasi,terverifikasi,perlu_tinjau'], 'catatan' => ['nullable', 'string', 'max:2000'],
        ]);
    }
}
