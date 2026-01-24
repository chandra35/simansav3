<?php

namespace App\Http\Controllers\Operator;

use App\Http\Controllers\Controller;
use App\Models\DokumenPendaftaran;
use App\Models\JurusanPpdb;
use App\Models\PendaftaranPpdb;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PendaftarController extends Controller
{
    /**
     * Display a listing of pendaftaran.
     */
    public function index(Request $request)
    {
        $statusCounts = [
            'all' => PendaftaranPpdb::count(),
            'draft' => PendaftaranPpdb::draft()->count(),
            'submitted' => PendaftaranPpdb::submitted()->count(),
            'verified' => PendaftaranPpdb::verified()->count(),
            'accepted' => PendaftaranPpdb::accepted()->count(),
            'rejected' => PendaftaranPpdb::where('status', 'rejected')->count(),
        ];

        $jurusan = JurusanPpdb::active()->ordered()->get();

        return view('operator.pendaftar.index', compact('statusCounts', 'jurusan'));
    }

    /**
     * Get pendaftaran data for DataTables.
     */
    public function data(Request $request)
    {
        $query = PendaftaranPpdb::query()
            ->select([
                'id', 'nomor_pendaftaran', 'nisn', 'nama_lengkap', 
                'asal_sekolah', 'jalur_pendaftaran', 'jurusan_pilihan_1',
                'status', 'created_at'
            ]);

        // Filter by status
        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        // Filter by jalur
        if ($request->filled('jalur')) {
            $query->where('jalur_pendaftaran', $request->jalur);
        }

        // Filter by jurusan
        if ($request->filled('jurusan')) {
            $query->where(function ($q) use ($request) {
                $q->where('jurusan_pilihan_1', $request->jurusan)
                  ->orWhere('jurusan_pilihan_2', $request->jurusan);
            });
        }

        // Search
        if ($request->filled('search.value')) {
            $search = $request->input('search.value');
            $query->where(function ($q) use ($search) {
                $q->where('nomor_pendaftaran', 'like', "%{$search}%")
                  ->orWhere('nisn', 'like', "%{$search}%")
                  ->orWhere('nama_lengkap', 'like', "%{$search}%")
                  ->orWhere('asal_sekolah', 'like', "%{$search}%");
            });
        }

        // Order
        $orderColumn = $request->input('order.0.column', 0);
        $orderDir = $request->input('order.0.dir', 'desc');
        $columns = ['nomor_pendaftaran', 'nisn', 'nama_lengkap', 'asal_sekolah', 'jalur_pendaftaran', 'status', 'created_at'];
        
        if (isset($columns[$orderColumn])) {
            $query->orderBy($columns[$orderColumn], $orderDir);
        } else {
            $query->orderBy('created_at', 'desc');
        }

        $total = $query->count();
        
        $data = $query
            ->skip($request->input('start', 0))
            ->take($request->input('length', 10))
            ->get();

        // Get jurusan names
        $jurusanList = JurusanPpdb::pluck('nama', 'id');

        return response()->json([
            'draw' => $request->input('draw'),
            'recordsTotal' => $total,
            'recordsFiltered' => $total,
            'data' => $data->map(function ($item) use ($jurusanList) {
                return [
                    'id' => $item->id,
                    'nomor_pendaftaran' => $item->nomor_pendaftaran,
                    'nisn' => $item->nisn,
                    'nama_lengkap' => $item->nama_lengkap,
                    'asal_sekolah' => $item->asal_sekolah ?? '-',
                    'jalur' => ucfirst($item->jalur_pendaftaran ?? '-'),
                    'jurusan' => $jurusanList[$item->jurusan_pilihan_1] ?? '-',
                    'status' => $item->status,
                    'status_badge' => $item->status_badge,
                    'status_label' => $item->status_label,
                    'tanggal' => $item->created_at->format('d/m/Y H:i'),
                ];
            })
        ]);
    }

    /**
     * Display the specified pendaftaran.
     */
    public function show(PendaftaranPpdb $pendaftaran)
    {
        $pendaftaran->load(['dokumen', 'tahunPelajaran']);
        
        $jurusanPilihan1 = $pendaftaran->jurusan_pilihan_1 ? JurusanPpdb::find($pendaftaran->jurusan_pilihan_1) : null;
        $jurusanPilihan2 = $pendaftaran->jurusan_pilihan_2 ? JurusanPpdb::find($pendaftaran->jurusan_pilihan_2) : null;
        $jurusanList = JurusanPpdb::active()->ordered()->get();
        
        // Get jenis dokumen labels
        $jenisDokumen = DokumenPendaftaran::getJenisDokumenList();

        return view('operator.pendaftar.show', compact(
            'pendaftaran', 
            'jurusanPilihan1', 
            'jurusanPilihan2',
            'jurusanList',
            'jenisDokumen'
        ));
    }

    /**
     * Verify pendaftaran (move to verified status).
     */
    public function verify(Request $request, PendaftaranPpdb $pendaftaran)
    {
        if ($pendaftaran->status !== PendaftaranPpdb::STATUS_SUBMITTED) {
            return back()->with('error', 'Status pendaftaran tidak valid untuk verifikasi.');
        }

        DB::beginTransaction();
        try {
            $pendaftaran->update([
                'status' => PendaftaranPpdb::STATUS_VERIFIED,
                'catatan_verifikasi' => $request->catatan,
                'tanggal_verifikasi' => now(),
                'diverifikasi_oleh' => auth()->id(),
            ]);

            // Update dokumen status
            $pendaftaran->dokumen()->update([
                'status_verifikasi' => 'approved',
                'diverifikasi_oleh' => auth()->id(),
                'tanggal_verifikasi' => now(),
            ]);

            DB::commit();
            return back()->with('success', 'Pendaftaran berhasil diverifikasi.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error verifying pendaftaran: ' . $e->getMessage());
            return back()->with('error', 'Terjadi kesalahan saat memverifikasi pendaftaran.');
        }
    }

    /**
     * Accept pendaftaran.
     */
    public function accept(Request $request, PendaftaranPpdb $pendaftaran)
    {
        $request->validate([
            'jurusan' => 'required|exists:jurusan_ppdb,id',
        ]);

        if (!in_array($pendaftaran->status, [PendaftaranPpdb::STATUS_VERIFIED])) {
            return back()->with('error', 'Status pendaftaran tidak valid untuk diterima.');
        }

        // Check jurusan kuota
        $jurusan = JurusanPpdb::find($request->jurusan);
        $currentCount = PendaftaranPpdb::where('diterima_di_jurusan', $jurusan->id)
            ->where('status', PendaftaranPpdb::STATUS_ACCEPTED)
            ->count();

        if ($currentCount >= $jurusan->kuota) {
            return back()->with('error', 'Kuota jurusan ' . $jurusan->nama . ' sudah penuh.');
        }

        DB::beginTransaction();
        try {
            $pendaftaran->update([
                'status' => PendaftaranPpdb::STATUS_ACCEPTED,
                'diterima_di_jurusan' => $request->jurusan,
                'catatan_verifikasi' => $request->catatan ?? $pendaftaran->catatan_verifikasi,
                'diverifikasi_oleh' => auth()->id(),
            ]);

            DB::commit();
            return back()->with('success', 'Pendaftaran berhasil diterima di jurusan ' . $jurusan->nama);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error accepting pendaftaran: ' . $e->getMessage());
            return back()->with('error', 'Terjadi kesalahan saat menerima pendaftaran.');
        }
    }

    /**
     * Reject pendaftaran.
     */
    public function reject(Request $request, PendaftaranPpdb $pendaftaran)
    {
        $request->validate([
            'catatan' => 'required|string|min:10',
        ], [
            'catatan.required' => 'Alasan penolakan wajib diisi.',
            'catatan.min' => 'Alasan penolakan minimal 10 karakter.',
        ]);

        if (!in_array($pendaftaran->status, [PendaftaranPpdb::STATUS_SUBMITTED, PendaftaranPpdb::STATUS_VERIFIED])) {
            return back()->with('error', 'Status pendaftaran tidak valid untuk ditolak.');
        }

        DB::beginTransaction();
        try {
            $pendaftaran->update([
                'status' => PendaftaranPpdb::STATUS_REJECTED,
                'catatan_verifikasi' => $request->catatan,
                'diverifikasi_oleh' => auth()->id(),
            ]);

            DB::commit();
            return back()->with('success', 'Pendaftaran telah ditolak.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error rejecting pendaftaran: ' . $e->getMessage());
            return back()->with('error', 'Terjadi kesalahan saat menolak pendaftaran.');
        }
    }

    /**
     * Remove the specified pendaftaran.
     */
    public function destroy(PendaftaranPpdb $pendaftaran)
    {
        if (!in_array($pendaftaran->status, [PendaftaranPpdb::STATUS_DRAFT, PendaftaranPpdb::STATUS_REJECTED])) {
            return back()->with('error', 'Hanya pendaftaran dengan status draft atau ditolak yang dapat dihapus.');
        }

        DB::beginTransaction();
        try {
            // Delete dokumen files
            foreach ($pendaftaran->dokumen as $doc) {
                if ($doc->path_file) {
                    \Storage::disk('public')->delete($doc->path_file);
                }
                $doc->delete();
            }

            $pendaftaran->delete();
            
            DB::commit();
            return redirect()->route('operator.pendaftar.index')
                ->with('success', 'Pendaftaran berhasil dihapus.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error deleting pendaftaran: ' . $e->getMessage());
            return back()->with('error', 'Terjadi kesalahan saat menghapus pendaftaran.');
        }
    }

    /**
     * Export pendaftaran to Excel.
     */
    public function export(Request $request)
    {
        // TODO: Implement Excel export
        return back()->with('info', 'Fitur export sedang dalam pengembangan.');
    }
}
