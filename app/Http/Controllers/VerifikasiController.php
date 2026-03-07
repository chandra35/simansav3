<?php

namespace App\Http\Controllers;

use App\Models\Gtk;
use App\Models\Siswa;
use App\Models\AppSetting;
use Illuminate\Http\Request;

class VerifikasiController extends Controller
{
    /**
     * Verify GTK by UUID
     */
    public function verifikasiGtk($id)
    {
        $gtk = Gtk::find($id);
        $setting = AppSetting::first();

        if (!$gtk) {
            return view('verifikasi.hasil', [
                'valid' => false,
                'tipe' => 'GTK',
                'setting' => $setting,
            ]);
        }

        return view('verifikasi.hasil', [
            'valid' => true,
            'tipe' => 'GTK',
            'data' => [
                'nama' => $gtk->nama_lengkap,
                'nip' => $gtk->nip ?? '-',
                'nuptk' => $gtk->nuptk ?? '-',
                'jabatan' => $gtk->jabatan ?? '-',
                'status' => $gtk->status_kepegawaian ?? '-',
            ],
            'setting' => $setting,
        ]);
    }

    /**
     * Verify Siswa by UUID
     */
    public function verifikasiSiswa($id)
    {
        $siswa = Siswa::with('kelasSaatIni')->find($id);
        $setting = AppSetting::first();

        if (!$siswa) {
            return view('verifikasi.hasil', [
                'valid' => false,
                'tipe' => 'Siswa',
                'setting' => $setting,
            ]);
        }

        return view('verifikasi.hasil', [
            'valid' => true,
            'tipe' => 'Siswa',
            'data' => [
                'nama' => $siswa->nama_lengkap,
                'nisn' => $siswa->nisn ?? '-',
                'kelas' => $siswa->kelasSaatIni->nama_lengkap ?? '-',
                'status' => $siswa->status_siswa ?? 'Aktif',
            ],
            'setting' => $setting,
        ]);
    }
}
