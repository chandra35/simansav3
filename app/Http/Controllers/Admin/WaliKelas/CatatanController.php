<?php

namespace App\Http\Controllers\Admin\WaliKelas;

use App\Models\CatatanWaliKelas;
use Illuminate\Http\Request;

class CatatanController extends BaseWaliKelasController
{
    public function index(Request $request)
    {
        $kelas = $this->resolveKelas($request->input('kelas_id'));
        $students = $kelas->siswaAktif()
            ->wherePivot('tahun_pelajaran_id', $kelas->tahun_pelajaran_id)
            ->orderByRaw('COALESCE(siswa_kelas.nomor_urut_absen, 9999)')
            ->orderBy('nama_lengkap')
            ->get();

        $query = CatatanWaliKelas::query()
            ->with(['siswa', 'pembacaBk'])
            ->where('kelas_id', $kelas->id)
            ->where('created_by', auth()->id());

        if ($request->filled('siswa_id')) {
            $query->where('siswa_id', $request->input('siswa_id'));
        }
        if ($request->filled('kategori')) {
            $query->where('kategori', $request->input('kategori'));
        }

        $catatan = $query->latest('tanggal')->latest('created_at')->paginate(20)->withQueryString();

        return view('admin.gtk.wali.catatan.index', [
            'kelas' => $kelas,
            'kelasList' => $this->waliClasses(),
            'students' => $students,
            'catatan' => $catatan,
            'kategoriList' => CatatanWaliKelas::KATEGORI,
            'filterSiswa' => $request->input('siswa_id'),
            'filterKategori' => $request->input('kategori'),
        ]);
    }

    public function store(Request $request)
    {
        $kelas = $this->resolveKelas($request->input('kelas_id'));

        $validated = $request->validate([
            'siswa_id' => ['required'],
            'tanggal' => ['required', 'date', 'before_or_equal:today'],
            'kategori' => ['nullable', 'in:' . implode(',', array_keys(CatatanWaliKelas::KATEGORI))],
            'catatan' => ['required', 'string', 'max:2000'],
            'is_penting' => ['nullable', 'boolean'],
        ]);

        // Pastikan siswa memang di rombel wali (scope keamanan).
        $this->resolveSiswa($validated['siswa_id']);

        CatatanWaliKelas::create([
            'siswa_id' => $validated['siswa_id'],
            'kelas_id' => $kelas->id,
            'tahun_pelajaran_id' => $kelas->tahun_pelajaran_id,
            'created_by' => auth()->id(),
            'tanggal' => $validated['tanggal'],
            'kategori' => $validated['kategori'] ?? null,
            'catatan' => $validated['catatan'],
            'is_penting' => $request->boolean('is_penting'),
        ]);

        return back()->with('success', 'Catatan siswa berhasil ditambahkan.');
    }

    public function update(Request $request, CatatanWaliKelas $catatan)
    {
        $this->authorizeCatatan($catatan);

        $validated = $request->validate([
            'tanggal' => ['required', 'date', 'before_or_equal:today'],
            'kategori' => ['nullable', 'in:' . implode(',', array_keys(CatatanWaliKelas::KATEGORI))],
            'catatan' => ['required', 'string', 'max:2000'],
            'is_penting' => ['nullable', 'boolean'],
        ]);

        $catatan->update([
            'tanggal' => $validated['tanggal'],
            'kategori' => $validated['kategori'] ?? null,
            'catatan' => $validated['catatan'],
            'is_penting' => $request->boolean('is_penting'),
        ]);

        return back()->with('success', 'Catatan siswa berhasil diperbarui.');
    }

    public function destroy(CatatanWaliKelas $catatan)
    {
        $this->authorizeCatatan($catatan);
        $catatan->delete();

        return back()->with('success', 'Catatan siswa dihapus.');
    }

    /**
     * Hanya penulis (wali kelas) & rombel miliknya yang boleh mengubah.
     */
    private function authorizeCatatan(CatatanWaliKelas $catatan): void
    {
        abort_unless(
            $catatan->created_by === auth()->id() && $this->waliClassIds()->contains($catatan->kelas_id),
            403
        );
    }
}
