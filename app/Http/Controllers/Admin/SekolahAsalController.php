<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Sekolah;
use App\Models\Siswa;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class SekolahAsalController extends Controller
{
    /**
     * Display a listing of sekolah asal with siswa count
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            // Get all sekolah with siswa count
            $sekolah = Sekolah::query()
                ->withCount('siswa')
                ->orderBy('siswa_count', 'desc')
                ->get();

            return DataTables::of($sekolah)
                ->addIndexColumn()
                ->addColumn('action', function ($row) {
                    $url = route('admin.sekolah-asal.show', $row->npsn);
                    return '
                        <a href="' . $url . '" 
                           class="btn btn-sm btn-info" title="Lihat Detail">
                            <i class="fas fa-eye"></i> Detail
                        </a>
                    ';
                })
                ->addColumn('status_badge', function ($row) {
                    $color = $row->status === 'NEGERI' ? 'primary' : 'success';
                    $status = $row->status ?? '-';
                    return '<span class="badge badge-' . $color . '">' . 
                           $status . '</span>';
                })
                ->addColumn('siswa_count_badge', function ($row) {
                    $count = $row->siswa_count;
                    $color = 'secondary';
                    if ($count > 50) {
                        $color = 'success';
                    } elseif ($count > 20) {
                        $color = 'primary';
                    } elseif ($count > 0) {
                        $color = 'info';
                    }
                    
                    $badge = '<span class="badge badge-' . $color . 
                             ' badge-pill">' . $count . ' siswa</span>';
                    return $badge;
                })
                ->rawColumns(['action', 'status_badge', 'siswa_count_badge'])
                ->make(true);
        }

        return view('admin.sekolah-asal.index');
    }

    /**
     * Display the specified sekolah with all siswa from that school
     */
    public function show($npsn)
    {
        $sekolah = Sekolah::with(['siswa' => function ($query) {
            $query->with(['kelasSaatIni.tahunPelajaran', 'user'])
                  ->orderBy('nama_lengkap', 'asc');
        }])
        ->withCount('siswa')
        ->findOrFail($npsn);

        // Get statistics
        $stats = [
            'total' => $sekolah->siswa_count,
            'aktif' => $sekolah->siswa()->where('status_siswa', 'aktif')->count(),
            'lulus' => $sekolah->siswa()->where('status_siswa', 'lulus')->count(),
            'keluar' => $sekolah->siswa()->whereIn('status_siswa', ['keluar', 'mutasi_keluar'])->count(),
            'laki' => $sekolah->siswa()->where('jenis_kelamin', 'L')->count(),
            'perempuan' => $sekolah->siswa()->where('jenis_kelamin', 'P')->count(),
        ];

        // Group siswa by current kelas
        $siswaPerKelas = $sekolah->siswa()
            ->with('kelasSaatIni')
            ->get()
            ->groupBy(function ($siswa) {
                return $siswa->kelasSaatIni ? $siswa->kelasSaatIni->nama_kelas : 'Belum Ada Kelas';
            })
            ->sortKeys();

        return view('admin.sekolah-asal.show', compact('sekolah', 'stats', 'siswaPerKelas'));
    }

    /**
     * Get siswa data for DataTables in detail view
     */
    public function getSiswaData($npsn)
    {
        $siswa = Siswa::with(['kelasSaatIni.tahunPelajaran', 'user'])
            ->where('npsn_asal_sekolah', $npsn)
            ->select('siswa.*');

        return DataTables::of($siswa)
            ->addIndexColumn()
            ->addColumn('kelas_saat_ini', function ($row) {
                if ($row->kelasSaatIni) {
                    return $row->kelasSaatIni->nama_kelas . ' (' . $row->kelasSaatIni->tahunPelajaran->nama . ')';
                }
                return '<span class="text-muted">-</span>';
            })
            ->addColumn('jenis_kelamin_badge', function ($row) {
                $color = $row->jenis_kelamin === 'L' ? 'primary' : 'danger';
                $text = $row->jenis_kelamin === 'L' ? 'Laki-laki' : 'Perempuan';
                return '<span class="badge badge-' . $color . '">' . $text . '</span>';
            })
            ->addColumn('status_badge', function ($row) {
                $badges = [
                    'aktif' => 'success',
                    'lulus' => 'primary',
                    'keluar' => 'warning',
                    'mutasi_keluar' => 'info',
                    'alumni' => 'secondary',
                ];
                $color = $badges[$row->status_siswa] ?? 'secondary';
                $status = ucfirst(str_replace('_', ' ', $row->status_siswa));
                return '<span class="badge badge-' . $color . '">' . $status . '</span>';
            })
            ->addColumn('action', function ($row) {
                return '
                    <button onclick="showSiswa(\'' . $row->id . '\')" 
                       class="btn btn-sm btn-info" title="Lihat Detail">
                        <i class="fas fa-eye"></i>
                    </button>
                ';
            })
            ->rawColumns(['kelas_saat_ini', 'jenis_kelamin_badge', 'status_badge', 'action'])
            ->make(true);
    }
}
