<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\JenisPembayaran;
use App\Models\Tagihan;
use App\Models\Pembayaran;
use App\Models\Siswa;
use App\Models\TahunPelajaran;
use App\Models\Kelas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class PembayaranController extends Controller
{
    // ==================== JENIS PEMBAYARAN ====================
    public function jenisPembayaran(Request $request)
    {
        if ($request->ajax()) {
            $query = JenisPembayaran::with('tahunPelajaran');

            return DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('kategori_label', function ($row) {
                    return $row->kategori_label;
                })
                ->addColumn('nominal_format', function ($row) {
                    return $row->nominal_format;
                })
                ->addColumn('status', function ($row) {
                    $badges = [];
                    if ($row->is_aktif) $badges[] = '<span class="badge badge-success">Aktif</span>';
                    if ($row->is_wajib) $badges[] = '<span class="badge badge-danger">Wajib</span>';
                    if ($row->is_bulanan) $badges[] = '<span class="badge badge-info">Bulanan</span>';
                    return implode(' ', $badges);
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

        $tahunPelajaran = TahunPelajaran::orderBy('tahun_mulai', 'desc')->get();
        $kategori = JenisPembayaran::KATEGORI;

        return view('admin.pembayaran.jenis-pembayaran', compact('tahunPelajaran', 'kategori'));
    }

    public function storeJenisPembayaran(Request $request)
    {
        $validated = $request->validate([
            'tahun_pelajaran_id' => 'required|exists:tahun_pelajaran,id',
            'nama' => 'required|string|max:255',
            'kode' => 'required|string|max:20|unique:jenis_pembayaran,kode',
            'kategori' => 'required|in:spp,daftar_ulang,seragam,kegiatan,lainnya',
            'nominal' => 'required|numeric|min:0',
            'is_wajib' => 'boolean',
            'is_bulanan' => 'boolean',
            'bulan_berlaku' => 'nullable|array',
            'keterangan' => 'nullable|string',
            'is_aktif' => 'boolean',
        ]);

        $validated['is_wajib'] = $request->boolean('is_wajib');
        $validated['is_bulanan'] = $request->boolean('is_bulanan');
        $validated['is_aktif'] = $request->boolean('is_aktif', true);

        JenisPembayaran::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Jenis pembayaran berhasil ditambahkan',
        ]);
    }

    public function showJenisPembayaran(JenisPembayaran $jenisPembayaran)
    {
        return response()->json([
            'success' => true,
            'data' => $jenisPembayaran,
        ]);
    }

    public function updateJenisPembayaran(Request $request, JenisPembayaran $jenisPembayaran)
    {
        $validated = $request->validate([
            'tahun_pelajaran_id' => 'required|exists:tahun_pelajaran,id',
            'nama' => 'required|string|max:255',
            'kode' => 'required|string|max:20|unique:jenis_pembayaran,kode,' . $jenisPembayaran->id,
            'kategori' => 'required|in:spp,daftar_ulang,seragam,kegiatan,lainnya',
            'nominal' => 'required|numeric|min:0',
            'is_wajib' => 'boolean',
            'is_bulanan' => 'boolean',
            'bulan_berlaku' => 'nullable|array',
            'keterangan' => 'nullable|string',
            'is_aktif' => 'boolean',
        ]);

        $validated['is_wajib'] = $request->boolean('is_wajib');
        $validated['is_bulanan'] = $request->boolean('is_bulanan');
        $validated['is_aktif'] = $request->boolean('is_aktif');

        $jenisPembayaran->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Jenis pembayaran berhasil diperbarui',
        ]);
    }

    public function destroyJenisPembayaran(JenisPembayaran $jenisPembayaran)
    {
        $jenisPembayaran->delete();

        return response()->json([
            'success' => true,
            'message' => 'Jenis pembayaran berhasil dihapus',
        ]);
    }

    // ==================== TAGIHAN ====================
    public function tagihan(Request $request)
    {
        if ($request->ajax()) {
            $query = Tagihan::with(['jenisPembayaran', 'siswa', 'tahunPelajaran'])
                ->when($request->status, function ($q) use ($request) {
                    return $q->where('status', $request->status);
                })
                ->when($request->jenis_pembayaran_id, function ($q) use ($request) {
                    return $q->where('jenis_pembayaran_id', $request->jenis_pembayaran_id);
                });

            return DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('siswa_nama', function ($row) {
                    return $row->siswa?->nama ?? '-';
                })
                ->addColumn('siswa_nis', function ($row) {
                    return $row->siswa?->nis ?? '-';
                })
                ->addColumn('jenis', function ($row) {
                    return $row->jenisPembayaran?->nama ?? '-';
                })
                ->addColumn('periode', function ($row) {
                    return $row->bulan_label . ' ' . $row->tahun;
                })
                ->addColumn('nominal', function ($row) {
                    return $row->nominal_tagihan_format;
                })
                ->addColumn('terbayar', function ($row) {
                    return $row->nominal_terbayar_format;
                })
                ->addColumn('sisa', function ($row) {
                    return $row->sisa_tagihan_format;
                })
                ->addColumn('status_badge', function ($row) {
                    return '<span class="badge badge-' . $row->status_badge . '">' . $row->status_label . '</span>';
                })
                ->addColumn('action', function ($row) {
                    $btn = '<div class="btn-group">';
                    $btn .= '<a href="' . route('admin.pembayaran.tagihan.show', $row->id) . '" class="btn btn-sm btn-info" title="Lihat"><i class="fas fa-eye"></i></a>';
                    if ($row->status !== 'lunas') {
                        $btn .= '<button type="button" class="btn btn-sm btn-success btn-bayar" data-id="' . $row->id . '" title="Bayar"><i class="fas fa-money-bill"></i></button>';
                    }
                    $btn .= '<button type="button" class="btn btn-sm btn-danger btn-delete" data-id="' . $row->id . '" title="Hapus"><i class="fas fa-trash"></i></button>';
                    $btn .= '</div>';
                    return $btn;
                })
                ->rawColumns(['status_badge', 'action'])
                ->make(true);
        }

        $jenisPembayaran = JenisPembayaran::aktif()->get();
        $status = Tagihan::STATUS;

        return view('admin.pembayaran.tagihan', compact('jenisPembayaran', 'status'));
    }

    public function generateTagihan(Request $request)
    {
        $validated = $request->validate([
            'jenis_pembayaran_id' => 'required|exists:jenis_pembayaran,id',
            'tahun_pelajaran_id' => 'required|exists:tahun_pelajaran,id',
            'kelas_id' => 'nullable|exists:kelas,id',
            'bulan' => 'required|integer|min:1|max:12',
            'tahun' => 'required|integer|min:2020|max:2100',
            'tanggal_jatuh_tempo' => 'required|date',
        ]);

        $jenisPembayaran = JenisPembayaran::findOrFail($validated['jenis_pembayaran_id']);

        // Get siswa
        $siswaQuery = Siswa::aktif();
        if ($validated['kelas_id']) {
            $siswaQuery->whereHas('kelasSaatIni', function ($q) use ($validated) {
                $q->where('kelas_id', $validated['kelas_id']);
            });
        }

        $siswaList = $siswaQuery->get();
        $created = 0;
        $skipped = 0;

        foreach ($siswaList as $siswa) {
            // Check if already exists
            $exists = Tagihan::where('jenis_pembayaran_id', $validated['jenis_pembayaran_id'])
                ->where('siswa_id', $siswa->id)
                ->where('bulan', $validated['bulan'])
                ->where('tahun', $validated['tahun'])
                ->exists();

            if (!$exists) {
                Tagihan::create([
                    'jenis_pembayaran_id' => $validated['jenis_pembayaran_id'],
                    'siswa_id' => $siswa->id,
                    'tahun_pelajaran_id' => $validated['tahun_pelajaran_id'],
                    'bulan' => $validated['bulan'],
                    'tahun' => $validated['tahun'],
                    'nominal_tagihan' => $jenisPembayaran->nominal,
                    'nominal_terbayar' => 0,
                    'tanggal_jatuh_tempo' => $validated['tanggal_jatuh_tempo'],
                    'status' => 'belum_bayar',
                ]);
                $created++;
            } else {
                $skipped++;
            }
        }

        return response()->json([
            'success' => true,
            'message' => "Tagihan berhasil digenerate. Dibuat: {$created}, Dilewati: {$skipped}",
        ]);
    }

    public function showTagihan(Tagihan $tagihan)
    {
        $tagihan->load(['jenisPembayaran', 'siswa', 'tahunPelajaran', 'pembayaran.verifiedBy']);

        return view('admin.pembayaran.tagihan-show', compact('tagihan'));
    }

    public function destroyTagihan(Tagihan $tagihan)
    {
        $tagihan->delete();

        return response()->json([
            'success' => true,
            'message' => 'Tagihan berhasil dihapus',
        ]);
    }

    // ==================== PEMBAYARAN ====================
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = Pembayaran::with(['tagihan.siswa', 'tagihan.jenisPembayaran', 'verifiedBy'])
                ->when($request->status, function ($q) use ($request) {
                    return $q->where('status', $request->status);
                });

            return DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('nomor', function ($row) {
                    return $row->nomor_transaksi;
                })
                ->addColumn('siswa_nama', function ($row) {
                    return $row->tagihan?->siswa?->nama ?? '-';
                })
                ->addColumn('jenis', function ($row) {
                    return $row->tagihan?->jenisPembayaran?->nama ?? '-';
                })
                ->addColumn('jumlah', function ($row) {
                    return 'Rp ' . number_format($row->jumlah_bayar, 0, ',', '.');
                })
                ->addColumn('metode', function ($row) {
                    return $row->metode_label;
                })
                ->addColumn('tanggal', function ($row) {
                    return $row->tanggal_bayar?->format('d/m/Y') ?? '-';
                })
                ->addColumn('status_badge', function ($row) {
                    return '<span class="badge badge-' . $row->status_badge . '">' . $row->status_label . '</span>';
                })
                ->addColumn('action', function ($row) {
                    $btn = '<div class="btn-group">';
                    $btn .= '<a href="' . route('admin.pembayaran.show', $row->id) . '" class="btn btn-sm btn-info" title="Lihat"><i class="fas fa-eye"></i></a>';
                    if ($row->status === 'pending') {
                        $btn .= '<button type="button" class="btn btn-sm btn-success btn-verify" data-id="' . $row->id . '" title="Verifikasi"><i class="fas fa-check"></i></button>';
                        $btn .= '<button type="button" class="btn btn-sm btn-danger btn-reject" data-id="' . $row->id . '" title="Tolak"><i class="fas fa-times"></i></button>';
                    }
                    $btn .= '</div>';
                    return $btn;
                })
                ->rawColumns(['status_badge', 'action'])
                ->make(true);
        }

        $status = Pembayaran::STATUS;

        return view('admin.pembayaran.index', compact('status'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'tagihan_id' => 'required|exists:tagihan,id',
            'jumlah_bayar' => 'required|numeric|min:1',
            'metode_pembayaran' => 'required|in:tunai,transfer,qris,virtual_account',
            'tanggal_bayar' => 'required|date',
            'bukti_pembayaran' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
            'catatan' => 'nullable|string',
        ]);

        $tagihan = Tagihan::findOrFail($validated['tagihan_id']);

        // Check sisa tagihan
        if ($validated['jumlah_bayar'] > $tagihan->sisa_tagihan) {
            return response()->json([
                'success' => false,
                'message' => 'Jumlah bayar melebihi sisa tagihan',
            ], 422);
        }

        $validated['nomor_transaksi'] = Pembayaran::generateNomorTransaksi();
        $validated['status'] = 'pending';

        if ($request->hasFile('bukti_pembayaran')) {
            $validated['bukti_pembayaran'] = $request->file('bukti_pembayaran')
                ->store('pembayaran/bukti', 'public');
        }

        $pembayaran = Pembayaran::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Pembayaran berhasil dicatat, menunggu verifikasi',
            'data' => $pembayaran,
        ]);
    }

    public function show(Pembayaran $pembayaran)
    {
        $pembayaran->load(['tagihan.siswa', 'tagihan.jenisPembayaran', 'verifiedBy']);

        return view('admin.pembayaran.show', compact('pembayaran'));
    }

    public function verify(Pembayaran $pembayaran)
    {
        DB::transaction(function () use ($pembayaran) {
            $pembayaran->update([
                'status' => 'verified',
                'verified_by' => Auth::id(),
                'verified_at' => now(),
            ]);

            // Update tagihan
            $pembayaran->tagihan->updateStatus();
        });

        return response()->json([
            'success' => true,
            'message' => 'Pembayaran berhasil diverifikasi',
        ]);
    }

    public function reject(Request $request, Pembayaran $pembayaran)
    {
        $validated = $request->validate([
            'alasan' => 'required|string',
        ]);

        $pembayaran->update([
            'status' => 'rejected',
            'catatan' => $validated['alasan'],
            'verified_by' => Auth::id(),
            'verified_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Pembayaran ditolak',
        ]);
    }

    // ==================== LAPORAN ====================
    public function laporan(Request $request)
    {
        $tahunPelajaran = TahunPelajaran::orderBy('tahun_mulai', 'desc')->get();
        $jenisPembayaran = JenisPembayaran::aktif()->get();

        $data = [];
        if ($request->filled('tahun_pelajaran_id')) {
            $query = Tagihan::with(['siswa', 'jenisPembayaran'])
                ->where('tahun_pelajaran_id', $request->tahun_pelajaran_id);

            if ($request->filled('jenis_pembayaran_id')) {
                $query->where('jenis_pembayaran_id', $request->jenis_pembayaran_id);
            }

            $data = [
                'total_tagihan' => $query->sum('nominal_tagihan'),
                'total_terbayar' => $query->sum('nominal_terbayar'),
                'total_sisa' => $query->sum('nominal_tagihan') - $query->sum('nominal_terbayar'),
                'count_lunas' => (clone $query)->where('status', 'lunas')->count(),
                'count_belum' => (clone $query)->where('status', 'belum_bayar')->count(),
                'count_cicilan' => (clone $query)->where('status', 'cicilan')->count(),
            ];
        }

        return view('admin.pembayaran.laporan', compact('tahunPelajaran', 'jenisPembayaran', 'data'));
    }
}
