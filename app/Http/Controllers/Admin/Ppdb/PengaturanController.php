<?php

namespace App\Http\Controllers\Admin\Ppdb;

use App\Http\Controllers\Controller;
use App\Models\PengaturanPpdb;
use App\Models\TahunPelajaran;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class PengaturanController extends Controller
{
    /**
     * Display pengaturan PPDB.
     */
    public function index()
    {
        $pengaturan = PengaturanPpdb::first() ?? new PengaturanPpdb();
        $tahunPelajaran = TahunPelajaran::orderBy('tahun_mulai', 'desc')->get();
        $defaultJalur = PengaturanPpdb::getDefaultJalur();

        return view('admin.ppdb.pengaturan.index', compact('pengaturan', 'tahunPelajaran', 'defaultJalur'));
    }

    /**
     * Update pengaturan PPDB.
     */
    public function update(Request $request)
    {
        $request->validate([
            'tahun_pelajaran_id' => 'nullable|exists:tahun_pelajaran,id',
            'tanggal_buka' => 'nullable|date',
            'tanggal_tutup' => 'nullable|date|after_or_equal:tanggal_buka',
            'tanggal_pengumuman' => 'nullable|date',
            'tanggal_daftar_ulang_mulai' => 'nullable|date',
            'tanggal_daftar_ulang_selesai' => 'nullable|date|after_or_equal:tanggal_daftar_ulang_mulai',
            'persyaratan' => 'nullable|string',
            'alur_pendaftaran' => 'nullable|string',
            'kontak_info' => 'nullable|string',
            'biaya_pendaftaran' => 'nullable|numeric|min:0',
            'rekening_pembayaran' => 'nullable|string',
        ]);

        $pengaturan = PengaturanPpdb::first();
        
        if (!$pengaturan) {
            $pengaturan = new PengaturanPpdb();
        }

        // Process jalur tersedia
        $jalurTersedia = [];
        $defaultJalur = PengaturanPpdb::getDefaultJalur();
        foreach ($defaultJalur as $key => $jalur) {
            $jalurTersedia[$key] = [
                'nama' => $jalur['nama'],
                'deskripsi' => $jalur['deskripsi'],
                'aktif' => $request->has("jalur_{$key}"),
            ];
        }

        $pengaturan->fill([
            'tahun_pelajaran_id' => $request->tahun_pelajaran_id,
            'tanggal_buka' => $request->tanggal_buka,
            'tanggal_tutup' => $request->tanggal_tutup,
            'tanggal_pengumuman' => $request->tanggal_pengumuman,
            'tanggal_daftar_ulang_mulai' => $request->tanggal_daftar_ulang_mulai,
            'tanggal_daftar_ulang_selesai' => $request->tanggal_daftar_ulang_selesai,
            'persyaratan' => $request->persyaratan,
            'alur_pendaftaran' => $request->alur_pendaftaran,
            'kontak_info' => $request->kontak_info,
            'biaya_pendaftaran' => $request->biaya_pendaftaran ?? 0,
            'rekening_pembayaran' => $request->rekening_pembayaran,
            'jalur_tersedia' => $jalurTersedia,
        ]);

        $pengaturan->save();

        // Clear cache
        Cache::forget('pengaturan_ppdb_active');

        return back()->with('success', 'Pengaturan PPDB berhasil disimpan.');
    }

    /**
     * Toggle pendaftaran status.
     */
    public function togglePendaftaran(Request $request)
    {
        $pengaturan = PengaturanPpdb::first();
        
        if (!$pengaturan) {
            return response()->json(['error' => 'Pengaturan belum dikonfigurasi'], 400);
        }

        $pengaturan->update([
            'pendaftaran_dibuka' => !$pengaturan->pendaftaran_dibuka,
        ]);

        // Clear cache
        Cache::forget('pengaturan_ppdb_active');

        return response()->json([
            'success' => true,
            'pendaftaran_dibuka' => $pengaturan->pendaftaran_dibuka,
            'message' => $pengaturan->pendaftaran_dibuka ? 'Pendaftaran dibuka' : 'Pendaftaran ditutup',
        ]);
    }
}
