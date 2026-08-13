<?php

namespace App\Services;

use App\Models\Gtk;
use App\Models\Kelas;
use App\Models\MutasiGtk;
use App\Models\PenugasanGtk;
use App\Models\TugasTambahan;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class GtkStatusService
{
    public function change(Gtk $gtk, array $data): MutasiGtk
    {
        return DB::transaction(function () use ($gtk, $data) {
            $gtk = Gtk::query()->with('user')->lockForUpdate()->findOrFail($gtk->id);
            $newStatus = (bool) $data['status_baru'];
            if ($gtk->status_aktif === $newStatus) {
                throw ValidationException::withMessages(['status_baru' => 'Status baru harus berbeda dari status GTK saat ini.']);
            }
            if (! $newStatus && $gtk->user_id === auth()->id()) {
                throw ValidationException::withMessages(['gtk_id' => 'Anda tidak dapat menonaktifkan akun GTK sendiri. Gunakan administrator lain untuk mencegah kehilangan akses.']);
            }

            $impact = ['akun_dinonaktifkan' => 0, 'penugasan_diakhiri' => 0, 'tugas_lama_diakhiri' => 0, 'wali_kelas_dilepas' => 0, 'role_tugas_dilepas' => 0];
            if (! $newStatus) {
                $assignmentRoleIds = PenugasanGtk::query()->where('gtk_id', $gtk->id)->where('status', 'active')
                    ->where('role_diberikan_otomatis', true)->with('jenis:id,role_id')->get()
                    ->pluck('jenis.role_id')->filter();
                $legacyRoleIds = $gtk->user_id
                    ? TugasTambahan::query()->where('user_id', $gtk->user_id)->where('is_active', true)->pluck('role_id')
                    : collect();
                $impact['akun_dinonaktifkan'] = $gtk->user?->update(['is_active' => false]) ? 1 : 0;
                $impact['penugasan_diakhiri'] = PenugasanGtk::query()->where('gtk_id', $gtk->id)->where('status', 'active')->update([
                    'status' => 'ended', 'selesai_tugas' => $data['tanggal_efektif'], 'updated_by' => auth()->id(),
                    'keterangan' => DB::raw("CONCAT(COALESCE(keterangan, ''), ' | Diakhiri otomatis karena GTK nonaktif')"),
                ]);
                if ($gtk->user_id) {
                    $impact['tugas_lama_diakhiri'] = TugasTambahan::query()->where('user_id', $gtk->user_id)->where('is_active', true)->update([
                        'is_active' => false, 'selesai_tugas' => $data['tanggal_efektif'], 'updated_by' => auth()->id(),
                    ]);
                    $impact['wali_kelas_dilepas'] = Kelas::query()->where('wali_kelas_id', $gtk->user_id)->where('is_active', true)->update(['wali_kelas_id' => null]);
                    $roleIds = $assignmentRoleIds->merge($legacyRoleIds)->filter()->unique();
                    $impact['role_tugas_dilepas'] = $gtk->user->roles()->whereIn('roles.id', $roleIds)->count();
                    $gtk->user->roles()->detach($roleIds);
                }
            } elseif ($gtk->user) {
                $gtk->user->update(['is_active' => true]);
            }

            $gtk->update([
                'status_aktif' => $newStatus,
                'alasan_nonaktif' => $newStatus ? null : $data['alasan'],
                'tanggal_status' => $data['tanggal_efektif'],
                'status_keterangan' => $data['keterangan'] ?? null,
                'updated_by' => auth()->id(),
            ]);

            return MutasiGtk::create([
                'gtk_id' => $gtk->id,
                'status_sebelumnya' => ! $newStatus,
                'status_baru' => $newStatus,
                'alasan' => $data['alasan'],
                'tanggal_efektif' => $data['tanggal_efektif'],
                'instansi_asal_tujuan' => $data['instansi_asal_tujuan'] ?? null,
                'keterangan' => $data['keterangan'] ?? null,
                'dampak_operasional' => $impact,
                'created_by' => auth()->id(),
            ]);
        });
    }
}
