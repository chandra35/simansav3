<?php

namespace App\Http\Controllers\Admin\Ppdb;

use App\Http\Controllers\Controller;
use App\Models\DokumenPendaftaran;
use App\Models\JurusanPpdb;
use App\Models\PendaftaranPpdb;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PendaftaranController extends Controller
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

        return view('admin.ppdb.pendaftaran.index', compact('statusCounts', 'jurusan'));
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
                    'asal_sekolah' => $item->asal_sekolah,
                    'jalur' => ucfirst($item->jalur_pendaftaran),
                    'jurusan' => $jurusanList[$item->jurusan_pilihan_1] ?? '-',
                    'status' => $item->status,
                    'status_label' => $item->status_label,
                    'status_badge' => $item->status_badge,
                    'tanggal' => $item->created_at->format('d/m/Y H:i'),
                ];
            }),
        ]);
    }

    /**
     * Display the specified pendaftaran.
     */
    public function show(PendaftaranPpdb $pendaftaran)
    {
        $pendaftaran->load('dokumen');
        
        $jurusanPilihan1 = JurusanPpdb::find($pendaftaran->jurusan_pilihan_1);
        $jurusanPilihan2 = JurusanPpdb::find($pendaftaran->jurusan_pilihan_2);
        $jenisDokumen = DokumenPendaftaran::getJenisDokumenList();
        $jurusanList = JurusanPpdb::active()->ordered()->get();

        return view('admin.ppdb.pendaftaran.show', compact(
            'pendaftaran',
            'jurusanPilihan1',
            'jurusanPilihan2',
            'jenisDokumen',
            'jurusanList'
        ));
    }

    /**
     * Verify pendaftaran (mark documents as valid).
     */
    public function verify(Request $request, PendaftaranPpdb $pendaftaran)
    {
        if (!in_array($pendaftaran->status, ['submitted', 'rejected'])) {
            return back()->with('error', 'Pendaftaran tidak dapat diverifikasi.');
        }

        DB::beginTransaction();
        try {
            // Update all documents to valid
            $pendaftaran->dokumen()->update([
                'status_verifikasi' => DokumenPendaftaran::STATUS_VALID,
                'diverifikasi_oleh' => auth()->id(),
                'tanggal_verifikasi' => now(),
            ]);

            $pendaftaran->update([
                'status' => PendaftaranPpdb::STATUS_VERIFIED,
                'diverifikasi_oleh' => auth()->id(),
                'tanggal_verifikasi' => now(),
                'catatan_verifikasi' => $request->catatan,
            ]);

            DB::commit();

            return back()->with('success', 'Pendaftaran berhasil diverifikasi.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error verifying pendaftaran: ' . $e->getMessage());
            return back()->with('error', 'Terjadi kesalahan sistem.');
        }
    }

    /**
     * Reject pendaftaran.
     */
    public function reject(Request $request, PendaftaranPpdb $pendaftaran)
    {
        $request->validate([
            'catatan' => 'required|string|max:1000',
        ], [
            'catatan.required' => 'Alasan penolakan wajib diisi.',
        ]);

        if (!in_array($pendaftaran->status, ['submitted', 'verified'])) {
            return back()->with('error', 'Pendaftaran tidak dapat ditolak.');
        }

        try {
            $pendaftaran->update([
                'status' => PendaftaranPpdb::STATUS_REJECTED,
                'catatan_verifikasi' => $request->catatan,
                'diverifikasi_oleh' => auth()->id(),
                'tanggal_verifikasi' => now(),
            ]);

            return back()->with('success', 'Pendaftaran ditolak.');
        } catch (\Exception $e) {
            Log::error('Error rejecting pendaftaran: ' . $e->getMessage());
            return back()->with('error', 'Terjadi kesalahan sistem.');
        }
    }

    /**
     * Accept pendaftaran (diterima).
     */
    public function accept(Request $request, PendaftaranPpdb $pendaftaran)
    {
        $request->validate([
            'jurusan' => 'required|exists:jurusan_ppdb,id',
        ], [
            'jurusan.required' => 'Jurusan penerimaan wajib dipilih.',
        ]);

        if ($pendaftaran->status !== PendaftaranPpdb::STATUS_VERIFIED) {
            return back()->with('error', 'Pendaftaran harus terverifikasi terlebih dahulu.');
        }

        $jurusan = JurusanPpdb::find($request->jurusan);
        if ($jurusan->isKuotaPenuh()) {
            return back()->with('error', 'Kuota jurusan sudah penuh.');
        }

        DB::beginTransaction();
        try {
            $pendaftaran->update([
                'status' => PendaftaranPpdb::STATUS_ACCEPTED,
                'diterima_di_jurusan' => $jurusan->nama,
                'catatan_verifikasi' => $request->catatan,
            ]);

            // Increment jurusan count
            $jurusan->incrementTerisi();

            DB::commit();

            return back()->with('success', 'Pendaftaran diterima di jurusan ' . $jurusan->nama);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error accepting pendaftaran: ' . $e->getMessage());
            return back()->with('error', 'Terjadi kesalahan sistem.');
        }
    }

    /**
     * Remove the specified pendaftaran.
     */
    public function destroy(PendaftaranPpdb $pendaftaran)
    {
        if (!in_array($pendaftaran->status, ['draft', 'rejected'])) {
            return back()->with('error', 'Hanya pendaftaran draft atau ditolak yang dapat dihapus.');
        }

        try {
            // Delete all related documents
            foreach ($pendaftaran->dokumen as $doc) {
                \Storage::disk('public')->delete($doc->path_file);
            }
            $pendaftaran->dokumen()->delete();
            $pendaftaran->delete();

            return redirect()->route('admin.ppdb.pendaftaran.index')
                ->with('success', 'Pendaftaran berhasil dihapus.');
        } catch (\Exception $e) {
            Log::error('Error deleting pendaftaran: ' . $e->getMessage());
            return back()->with('error', 'Terjadi kesalahan sistem.');
        }
    }

    /**
     * Export pendaftaran to Excel.
     */
    public function export(Request $request)
    {
        // Placeholder for export functionality
        return back()->with('info', 'Fitur export dalam pengembangan.');
    }
}
