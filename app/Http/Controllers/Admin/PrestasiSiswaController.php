<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PrestasiSiswa;
use App\Models\Siswa;
use App\Models\TahunPelajaran;
use App\Models\Gtk;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Facades\DataTables;

class PrestasiSiswaController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = PrestasiSiswa::with(['siswa', 'tahunPelajaran', 'pembina']);

            return DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('siswa_nama', function ($row) {
                    return $row->siswa?->nama ?? '-';
                })
                ->addColumn('tingkat_label', function ($row) {
                    return '<span class="badge badge-' . $row->tingkat_badge . '">' . $row->tingkat_label . '</span>';
                })
                ->addColumn('peringkat_label', function ($row) {
                    return '<span class="badge badge-' . $row->peringkat_badge . '">' . $row->peringkat_label . '</span>';
                })
                ->addColumn('verified', function ($row) {
                    return $row->is_verified
                        ? '<span class="badge badge-success"><i class="fas fa-check"></i> Terverifikasi</span>'
                        : '<span class="badge badge-warning"><i class="fas fa-clock"></i> Pending</span>';
                })
                ->addColumn('action', function ($row) {
                    $btn = '<div class="btn-group">';
                    $btn .= '<a href="' . route('admin.prestasi-siswa.show', $row->id) . '" class="btn btn-sm btn-info" title="Lihat"><i class="fas fa-eye"></i></a>';
                    if (auth()->user()->can('edit-prestasi-siswa')) {
                        $btn .= '<a href="' . route('admin.prestasi-siswa.edit', $row->id) . '" class="btn btn-sm btn-warning" title="Edit"><i class="fas fa-edit"></i></a>';
                    }
                    if (auth()->user()->can('verify-prestasi-siswa') && !$row->is_verified) {
                        $btn .= '<button type="button" class="btn btn-sm btn-success btn-verify" data-id="' . $row->id . '" title="Verifikasi"><i class="fas fa-check"></i></button>';
                    }
                    if (auth()->user()->can('delete-prestasi-siswa')) {
                        $btn .= '<button type="button" class="btn btn-sm btn-danger btn-delete" data-id="' . $row->id . '" title="Hapus"><i class="fas fa-trash"></i></button>';
                    }
                    $btn .= '</div>';
                    return $btn;
                })
                ->rawColumns(['tingkat_label', 'peringkat_label', 'verified', 'action'])
                ->make(true);
        }

        return view('admin.prestasi-siswa.index');
    }

    public function create()
    {
        $siswa = Siswa::aktif()->orderBy('nama')->get();
        $tahunPelajaran = TahunPelajaran::orderBy('tahun_mulai', 'desc')->get();
        $pembina = Gtk::aktif()->orderBy('nama')->get();
        $jenis = PrestasiSiswa::JENIS;
        $tingkat = PrestasiSiswa::TINGKAT;
        $peringkat = PrestasiSiswa::PERINGKAT;

        return view('admin.prestasi-siswa.create', compact(
            'siswa', 'tahunPelajaran', 'pembina', 'jenis', 'tingkat', 'peringkat'
        ));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'siswa_id' => 'required|exists:siswa,id',
            'tahun_pelajaran_id' => 'required|exists:tahun_pelajaran,id',
            'nama_prestasi' => 'required|string|max:255',
            'jenis_prestasi' => 'required|in:akademik,non_akademik,olahraga,seni,keagamaan,lainnya',
            'tingkat' => 'required|in:sekolah,kecamatan,kabupaten,provinsi,nasional,internasional',
            'peringkat' => 'required|in:juara_1,juara_2,juara_3,harapan_1,harapan_2,harapan_3,peserta,lainnya',
            'penyelenggara' => 'required|string|max:255',
            'tanggal_prestasi' => 'required|date',
            'tempat' => 'nullable|string|max:255',
            'pembina_id' => 'nullable|exists:gtk,id',
            'deskripsi' => 'nullable|string',
            'sertifikat' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'foto' => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('sertifikat')) {
            $validated['sertifikat'] = $request->file('sertifikat')->store('prestasi/sertifikat', 'public');
        }

        if ($request->hasFile('foto')) {
            $validated['foto'] = $request->file('foto')->store('prestasi/foto', 'public');
        }

        PrestasiSiswa::create($validated);

        return redirect()->route('admin.prestasi-siswa.index')
            ->with('success', 'Prestasi siswa berhasil ditambahkan');
    }

    public function show(PrestasiSiswa $prestasiSiswa)
    {
        $prestasiSiswa->load(['siswa', 'tahunPelajaran', 'pembina', 'verifiedBy']);

        return view('admin.prestasi-siswa.show', compact('prestasiSiswa'));
    }

    public function edit(PrestasiSiswa $prestasiSiswa)
    {
        $siswa = Siswa::aktif()->orderBy('nama')->get();
        $tahunPelajaran = TahunPelajaran::orderBy('tahun_mulai', 'desc')->get();
        $pembina = Gtk::aktif()->orderBy('nama')->get();
        $jenis = PrestasiSiswa::JENIS;
        $tingkat = PrestasiSiswa::TINGKAT;
        $peringkat = PrestasiSiswa::PERINGKAT;

        return view('admin.prestasi-siswa.edit', compact(
            'prestasiSiswa', 'siswa', 'tahunPelajaran', 'pembina', 'jenis', 'tingkat', 'peringkat'
        ));
    }

    public function update(Request $request, PrestasiSiswa $prestasiSiswa)
    {
        $validated = $request->validate([
            'siswa_id' => 'required|exists:siswa,id',
            'tahun_pelajaran_id' => 'required|exists:tahun_pelajaran,id',
            'nama_prestasi' => 'required|string|max:255',
            'jenis_prestasi' => 'required|in:akademik,non_akademik,olahraga,seni,keagamaan,lainnya',
            'tingkat' => 'required|in:sekolah,kecamatan,kabupaten,provinsi,nasional,internasional',
            'peringkat' => 'required|in:juara_1,juara_2,juara_3,harapan_1,harapan_2,harapan_3,peserta,lainnya',
            'penyelenggara' => 'required|string|max:255',
            'tanggal_prestasi' => 'required|date',
            'tempat' => 'nullable|string|max:255',
            'pembina_id' => 'nullable|exists:gtk,id',
            'deskripsi' => 'nullable|string',
            'sertifikat' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'foto' => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('sertifikat')) {
            if ($prestasiSiswa->sertifikat) {
                \Storage::disk('public')->delete($prestasiSiswa->sertifikat);
            }
            $validated['sertifikat'] = $request->file('sertifikat')->store('prestasi/sertifikat', 'public');
        }

        if ($request->hasFile('foto')) {
            if ($prestasiSiswa->foto) {
                \Storage::disk('public')->delete($prestasiSiswa->foto);
            }
            $validated['foto'] = $request->file('foto')->store('prestasi/foto', 'public');
        }

        $prestasiSiswa->update($validated);

        return redirect()->route('admin.prestasi-siswa.index')
            ->with('success', 'Prestasi siswa berhasil diperbarui');
    }

    public function destroy(PrestasiSiswa $prestasiSiswa)
    {
        $prestasiSiswa->delete();

        return response()->json(['success' => true, 'message' => 'Prestasi siswa berhasil dihapus']);
    }

    public function verify(PrestasiSiswa $prestasiSiswa)
    {
        $prestasiSiswa->update([
            'is_verified' => true,
            'verified_by' => Auth::id(),
            'verified_at' => now(),
        ]);

        return response()->json(['success' => true, 'message' => 'Prestasi siswa berhasil diverifikasi']);
    }
}
