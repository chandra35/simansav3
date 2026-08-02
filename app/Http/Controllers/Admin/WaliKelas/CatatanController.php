<?php

namespace App\Http\Controllers\Admin\WaliKelas;

use App\Models\CatatanWaliKelas;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

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

        $baseQuery = CatatanWaliKelas::query()
            ->with(['siswa', 'pembacaBk'])
            ->where('kelas_id', $kelas->id)
            ->where('created_by', auth()->id());

        $selectedStudent = null;
        if ($request->filled('siswa_id')) {
            $selectedStudent = $students->firstWhere('id', $request->string('siswa_id')->toString());
            abort_if($selectedStudent === null, 404, 'Siswa tidak ditemukan pada rombel Anda.');
        }

        $stats = [
            'total_siswa' => $students->count(),
            'sudah_dicatat' => (clone $baseQuery)->distinct()->count('siswa_id'),
            'total_catatan' => (clone $baseQuery)->count(),
            'penting' => (clone $baseQuery)->where('is_penting', true)->count(),
        ];

        $query = clone $baseQuery;

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
            'selectedStudent' => $selectedStudent,
            'stats' => $stats,
        ]);
    }

    public function store(Request $request)
    {
        $kelas = $this->resolveKelas($request->input('kelas_id'));

        $validated = $request->validate([
            'siswa_id' => ['required'],
            'tanggal' => ['required', 'date', 'before_or_equal:today'],
            'kategori' => ['nullable', 'in:'.implode(',', array_keys(CatatanWaliKelas::KATEGORI))],
            'catatan' => ['required', 'string', 'max:5000'],
            'is_penting' => ['nullable', 'boolean'],
        ]);

        // Pastikan siswa memang di rombel wali (scope keamanan).
        $this->resolveSiswa($validated['siswa_id']);
        $studentBelongsToClass = $kelas->siswaAktif()
            ->wherePivot('tahun_pelajaran_id', $kelas->tahun_pelajaran_id)
            ->where('siswa.id', $validated['siswa_id'])
            ->exists();
        abort_unless($studentBelongsToClass, 404, 'Siswa tidak ditemukan pada rombel yang dipilih.');

        $content = $this->validatedContent($validated['catatan']);

        CatatanWaliKelas::create([
            'siswa_id' => $validated['siswa_id'],
            'kelas_id' => $kelas->id,
            'tahun_pelajaran_id' => $kelas->tahun_pelajaran_id,
            'created_by' => auth()->id(),
            'tanggal' => $validated['tanggal'],
            'kategori' => $validated['kategori'] ?? null,
            'catatan' => $content,
            'is_penting' => $request->boolean('is_penting'),
        ]);

        return back()->with('success', 'Catatan siswa berhasil ditambahkan.');
    }

    public function update(Request $request, CatatanWaliKelas $catatan)
    {
        $this->authorizeCatatan($catatan);

        $validated = $request->validate([
            'tanggal' => ['required', 'date', 'before_or_equal:today'],
            'kategori' => ['nullable', 'in:'.implode(',', array_keys(CatatanWaliKelas::KATEGORI))],
            'catatan' => ['required', 'string', 'max:5000'],
            'is_penting' => ['nullable', 'boolean'],
        ]);

        $content = $this->validatedContent($validated['catatan']);

        $catatan->update([
            'tanggal' => $validated['tanggal'],
            'kategori' => $validated['kategori'] ?? null,
            'catatan' => $content,
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

    private function validatedContent(string $content): string
    {
        $content = CatatanWaliKelas::sanitizeContent($content);
        $plainText = trim(html_entity_decode(strip_tags($content), ENT_QUOTES | ENT_HTML5, 'UTF-8'));

        if ($plainText === '') {
            throw ValidationException::withMessages([
                'catatan' => 'Catatan wajib berisi teks, emoji, atau simbol.',
            ]);
        }

        return $content;
    }
}
