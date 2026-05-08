<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\JadwalPelajaran;
use App\Models\Kelas;
use App\Models\Mapel;
use App\Models\Gtk;
use App\Models\TahunPelajaran;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class JadwalPelajaranController extends Controller
{
    public function index(Request $request)
    {
        $tahunPelajaran = TahunPelajaran::orderBy('tahun_mulai', 'desc')->get();
        $kelas = Kelas::with('tingkat')->orderBy('nama')->get();
        $hari = JadwalPelajaran::HARI;

        if ($request->ajax()) {
            $query = JadwalPelajaran::with(['kelas', 'mapel', 'gtk', 'tahunPelajaran'])
                ->when($request->tahun_pelajaran_id, function ($q) use ($request) {
                    return $q->where('tahun_pelajaran_id', $request->tahun_pelajaran_id);
                })
                ->when($request->kelas_id, function ($q) use ($request) {
                    return $q->where('kelas_id', $request->kelas_id);
                })
                ->when($request->hari, function ($q) use ($request) {
                    return $q->where('hari', $request->hari);
                })
                ->orderBy('hari')
                ->orderBy('jam_ke');

            return DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('kelas_nama', function ($row) {
                    return $row->kelas?->nama ?? '-';
                })
                ->addColumn('mapel_nama', function ($row) {
                    return $row->mapel?->nama ?? '-';
                })
                ->addColumn('guru_nama', function ($row) {
                    return $row->gtk?->nama ?? '-';
                })
                ->addColumn('hari_label', function ($row) {
                    return $row->hari_label;
                })
                ->addColumn('waktu', function ($row) {
                    return $row->jam;
                })
                ->addColumn('status', function ($row) {
                    return $row->is_aktif
                        ? '<span class="badge badge-success">Aktif</span>'
                        : '<span class="badge badge-secondary">Tidak Aktif</span>';
                })
                ->addColumn('action', function ($row) {
                    $btn = '<div class="btn-group">';
                    $btn .= '<button type="button" class="btn btn-sm btn-warning btn-edit" data-id="' . $row->id . '" title="Edit"><i class="fas fa-edit"></i></button>';
                    $btn .= '<button type="button" class="btn btn-sm btn-danger btn-delete" data-id="' . $row->id . '" title="Hapus"><i class="fas fa-trash"></i></button>';
                    $btn .= '</div>';
                    return $btn;
                })
                ->rawColumns(['status', 'action'])
                ->make(true);
        }

        return view('admin.jadwal-pelajaran.index', compact('tahunPelajaran', 'kelas', 'hari'));
    }

    public function create()
    {
        $tahunPelajaran = TahunPelajaran::orderBy('tahun_mulai', 'desc')->get();
        $kelas = Kelas::with('tingkat')->orderBy('nama')->get();
        $mapel = Mapel::orderBy('nama')->get();
        $guru = Gtk::aktif()->orderBy('nama')->get();
        $hari = JadwalPelajaran::HARI;

        return view('admin.jadwal-pelajaran.create', compact(
            'tahunPelajaran', 'kelas', 'mapel', 'guru', 'hari'
        ));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'tahun_pelajaran_id' => 'required|exists:tahun_pelajaran,id',
            'kelas_id' => 'required|exists:kelas,id',
            'mapel_id' => 'required|exists:mapel,id',
            'gtk_id' => 'required|exists:gtk,id',
            'hari' => 'required|in:senin,selasa,rabu,kamis,jumat,sabtu',
            'jam_ke' => 'required|integer|min:1|max:12',
            'waktu_mulai' => 'required|date_format:H:i',
            'waktu_selesai' => 'required|date_format:H:i|after:waktu_mulai',
            'ruangan' => 'nullable|string|max:50',
            'semester' => 'required|integer|in:1,2',
            'is_aktif' => 'boolean',
        ]);

        // Check for conflict
        $conflict = JadwalPelajaran::where('tahun_pelajaran_id', $validated['tahun_pelajaran_id'])
            ->where('kelas_id', $validated['kelas_id'])
            ->where('hari', $validated['hari'])
            ->where('jam_ke', $validated['jam_ke'])
            ->where('semester', $validated['semester'])
            ->exists();

        if ($conflict) {
            return back()->withErrors(['jam_ke' => 'Jadwal sudah ada untuk kelas ini pada hari dan jam yang sama'])->withInput();
        }

        // Check teacher conflict
        $teacherConflict = JadwalPelajaran::where('tahun_pelajaran_id', $validated['tahun_pelajaran_id'])
            ->where('gtk_id', $validated['gtk_id'])
            ->where('hari', $validated['hari'])
            ->where('jam_ke', $validated['jam_ke'])
            ->where('semester', $validated['semester'])
            ->exists();

        if ($teacherConflict) {
            return back()->withErrors(['gtk_id' => 'Guru sudah mengajar di kelas lain pada jam yang sama'])->withInput();
        }

        $validated['is_aktif'] = $request->boolean('is_aktif', true);

        JadwalPelajaran::create($validated);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Jadwal pelajaran berhasil ditambahkan',
            ]);
        }

        return redirect()->route('admin.jadwal-pelajaran.index')
            ->with('success', 'Jadwal pelajaran berhasil ditambahkan');
    }

    public function show(JadwalPelajaran $jadwalPelajaran)
    {
        $jadwalPelajaran->load(['kelas', 'mapel', 'gtk', 'tahunPelajaran']);

        return response()->json([
            'success' => true,
            'data' => $jadwalPelajaran,
        ]);
    }

    public function update(Request $request, JadwalPelajaran $jadwalPelajaran)
    {
        $validated = $request->validate([
            'tahun_pelajaran_id' => 'required|exists:tahun_pelajaran,id',
            'kelas_id' => 'required|exists:kelas,id',
            'mapel_id' => 'required|exists:mapel,id',
            'gtk_id' => 'required|exists:gtk,id',
            'hari' => 'required|in:senin,selasa,rabu,kamis,jumat,sabtu',
            'jam_ke' => 'required|integer|min:1|max:12',
            'waktu_mulai' => 'required|date_format:H:i',
            'waktu_selesai' => 'required|date_format:H:i|after:waktu_mulai',
            'ruangan' => 'nullable|string|max:50',
            'semester' => 'required|integer|in:1,2',
            'is_aktif' => 'boolean',
        ]);

        // Check for conflict (exclude current)
        $conflict = JadwalPelajaran::where('tahun_pelajaran_id', $validated['tahun_pelajaran_id'])
            ->where('kelas_id', $validated['kelas_id'])
            ->where('hari', $validated['hari'])
            ->where('jam_ke', $validated['jam_ke'])
            ->where('semester', $validated['semester'])
            ->where('id', '!=', $jadwalPelajaran->id)
            ->exists();

        if ($conflict) {
            return response()->json([
                'success' => false,
                'message' => 'Jadwal sudah ada untuk kelas ini pada hari dan jam yang sama',
            ], 422);
        }

        $validated['is_aktif'] = $request->boolean('is_aktif');

        $jadwalPelajaran->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Jadwal pelajaran berhasil diperbarui',
        ]);
    }

    public function destroy(JadwalPelajaran $jadwalPelajaran)
    {
        $jadwalPelajaran->delete();

        return response()->json([
            'success' => true,
            'message' => 'Jadwal pelajaran berhasil dihapus',
        ]);
    }

    // View jadwal per kelas (timetable view)
    public function timetable(Request $request)
    {
        $tahunPelajaran = TahunPelajaran::orderBy('tahun_mulai', 'desc')->get();
        $kelas = Kelas::with('tingkat')->orderBy('nama')->get();
        $hariList = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];

        $selectedKelas = $request->kelas_id;
        $selectedTahunPelajaran = $request->tahun_pelajaran_id ?? TahunPelajaran::where('is_aktif', true)->first()?->id;
        $selectedSemester = $request->semester ?? 1;
        $kelasNama = null;

        $jadwal = collect();
        if ($selectedKelas && $selectedTahunPelajaran) {
            $kelasData = Kelas::find($selectedKelas);
            $kelasNama = $kelasData?->nama_lengkap ?? $kelasData?->nama;
            
            $jadwal = JadwalPelajaran::with(['mapel', 'gtk'])
                ->where('kelas_id', $selectedKelas)
                ->where('tahun_pelajaran_id', $selectedTahunPelajaran)
                ->where('semester', $selectedSemester)
                ->where('is_aktif', true)
                ->orderBy('jam_ke')
                ->get();
        }

        return view('admin.jadwal-pelajaran.timetable', compact(
            'tahunPelajaran', 'kelas', 'hariList', 'jadwal',
            'selectedKelas', 'selectedTahunPelajaran', 'selectedSemester', 'kelasNama'
        ));
    }

    /**
     * POST: copy semua jadwal dari satu tahun pelajaran ke tahun lain.
     * Mencocokkan kelas berdasarkan `nama_kelas` yang sama.
     * Jadwal yang sudah ada di tahun tujuan (kelas+hari+jam_ke+semester sama) di-skip.
     */
    public function copyJadwal(Request $request)
    {
        $request->validate([
            'tahun_asal_id'   => 'required|exists:tahun_pelajaran,id',
            'tahun_tujuan_id' => 'required|exists:tahun_pelajaran,id|different:tahun_asal_id',
        ]);

        $tahunAsal   = TahunPelajaran::findOrFail($request->tahun_asal_id);
        $tahunTujuan = TahunPelajaran::findOrFail($request->tahun_tujuan_id);

        // Ambil semua kelas di tahun tujuan, index by nama_kelas (lowercase)
        $kelasTujuanMap = Kelas::where('tahun_pelajaran_id', $tahunTujuan->id)
            ->get()
            ->keyBy(fn($k) => strtolower(trim($k->nama_kelas)));

        $jadwalAsal = JadwalPelajaran::where('tahun_pelajaran_id', $tahunAsal->id)
            ->get();

        $disalin   = 0;
        $dilewati  = 0;
        $no_target = 0;

        DB::transaction(function () use ($jadwalAsal, $kelasTujuanMap, $tahunTujuan, &$disalin, &$dilewati, &$no_target) {
            foreach ($jadwalAsal as $j) {
                // Cari kelas di tahun tujuan berdasarkan nama_kelas yang sama
                $kelasAsal = Kelas::find($j->kelas_id);
                if (!$kelasAsal) { $no_target++; continue; }

                $kelasTujuan = $kelasTujuanMap->get(strtolower(trim($kelasAsal->nama_kelas)));
                if (!$kelasTujuan) { $no_target++; continue; }

                // Skip jika jadwal sudah ada (kelas+hari+jam_ke+semester)
                $exists = JadwalPelajaran::where('tahun_pelajaran_id', $tahunTujuan->id)
                    ->where('kelas_id', $kelasTujuan->id)
                    ->where('hari', $j->hari)
                    ->where('jam_ke', $j->jam_ke)
                    ->where('semester', $j->semester)
                    ->exists();

                if ($exists) { $dilewati++; continue; }

                JadwalPelajaran::create([
                    'tahun_pelajaran_id' => $tahunTujuan->id,
                    'kelas_id'           => $kelasTujuan->id,
                    'mapel_id'           => $j->mapel_id,
                    'gtk_id'             => $j->gtk_id,
                    'hari'               => $j->hari,
                    'jam_ke'             => $j->jam_ke,
                    'waktu_mulai'        => $j->getRawOriginal('waktu_mulai'),
                    'waktu_selesai'      => $j->getRawOriginal('waktu_selesai'),
                    'ruangan'            => $j->ruangan,
                    'semester'           => $j->semester,
                    'is_aktif'           => $j->is_aktif,
                ]);
                $disalin++;
            }
        });

        return response()->json([
            'success'   => true,
            'disalin'   => $disalin,
            'dilewati'  => $dilewati,
            'no_target' => $no_target,
            'message'   => "Berhasil menyalin {$disalin} jadwal dari {$tahunAsal->nama} ke {$tahunTujuan->nama}."
                . ($dilewati  > 0 ? " {$dilewati} jadwal dilewati (sudah ada)." : '')
                . ($no_target > 0 ? " {$no_target} jadwal tidak disalin (kelas tidak ditemukan di tahun tujuan)." : ''),
        ]);
    }
}
