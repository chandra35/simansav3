<?php

namespace App\Services;

use App\Models\Gtk;
use App\Models\MutasiGtk;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class GtkOnboardingService
{
    public function create(array $data, string $origin): Gtk
    {
        return DB::transaction(function () use ($data, $origin) {
            $user = User::create([
                'name' => $data['nama_lengkap'],
                'username' => $data['nik'],
                'email' => $data['email'] ?? $data['nik'].'@gtk.simansa.sch.id',
                'password' => Hash::make($data['nik']),
                'is_first_login' => true,
                'is_active' => true,
            ]);
            $user->assignRole('GTK');

            $gtk = Gtk::create([
                'user_id' => $user->id,
                'nama_lengkap' => $data['nama_lengkap'],
                'nik' => $data['nik'],
                'nip' => $data['nip'] ?? null,
                'jenis_kelamin' => $data['jenis_kelamin'],
                'kategori_ptk' => $data['kategori_ptk'],
                'jenis_ptk' => $data['jenis_ptk'],
                'status_kepegawaian' => $data['status_kepegawaian'] ?? null,
                'tmt_kerja' => $data['tanggal_efektif'],
                'status_aktif' => true,
                'tanggal_status' => $data['tanggal_efektif'],
                'status_keterangan' => $data['keterangan'] ?? null,
                'created_by' => auth()->id(),
            ]);

            MutasiGtk::create([
                'gtk_id' => $gtk->id,
                'status_sebelumnya' => null,
                'status_baru' => true,
                'alasan' => $origin,
                'tanggal_efektif' => $data['tanggal_efektif'],
                'instansi_asal_tujuan' => $origin === 'mutasi_masuk' ? $data['instansi_asal'] : null,
                'keterangan' => $data['keterangan'] ?? null,
                'dampak_operasional' => ['akun_dibuat' => 1, 'profil_gtk_dibuat' => 1],
                'created_by' => auth()->id(),
            ]);

            return $gtk->load('user');
        });
    }
}
