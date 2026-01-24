<?php

namespace App\Services;

use App\Models\Siswa;
use App\Models\Gtk;
use App\Models\User;
use App\Models\Ortu;
use App\Models\DokumenSiswa;
use App\Models\Kelas;
use App\Models\SiswaKelas;
use App\Models\MutasiSiswa;
use App\Models\TahunPelajaran;
use App\Models\Kurikulum;
use App\Models\Jurusan;
use App\Models\TugasTambahan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Spatie\Activitylog\Models\Activity;

class SystemResetService
{
    protected $backupService;

    public function __construct(DatabaseBackupService $backupService)
    {
        $this->backupService = $backupService;
    }

    /**
     * Count all affected data
     */
    public function countAffectedData()
    {
        return [
            'siswa' => Siswa::count(),
            'siswa_users' => User::role('siswa')->count(),
            'ortu' => Ortu::count(),
            'dokumen_siswa' => DokumenSiswa::count(),
            'siswa_kelas' => SiswaKelas::count(),
            'mutasi_siswa' => MutasiSiswa::count(),
            
            'gtk' => Gtk::count(),
            'gtk_users' => User::whereHas('gtk')->count(),
            'tugas_tambahan' => TugasTambahan::count(),
            
            'kelas' => Kelas::count(),
            'tahun_pelajaran' => TahunPelajaran::count(),
            'kurikulum' => Kurikulum::count(),
            'jurusan' => Jurusan::count(),
            
            'total_users_affected' => User::role(['siswa', 'gtk'])->count(),
            'activity_logs' => Activity::count(),
        ];
    }

    /**
     * Reset ALL data with auto-backup
     */
    public function resetAllData($mode = 'permanent', $autoBackup = true)
    {
        try {
            // Auto backup before delete
            $backup = null;
            if ($autoBackup) {
                $backup = $this->backupService->createBackup('before_reset_all');
                if (!$backup['success']) {
                    throw new \Exception('Backup gagal: ' . $backup['error']);
                }
            }

            DB::beginTransaction();

            $deleted = [];

            // Delete in correct order (dependencies first)
            $deleted['siswa_kelas'] = $this->deleteSiswaKelas($mode);
            $deleted['mutasi_siswa'] = $this->deleteMutasiSiswa($mode);
            $deleted['dokumen_siswa'] = $this->deleteDokumenSiswa($mode);
            $deleted['ortu'] = $this->deleteOrtu($mode);
            $deleted['siswa'] = $this->deleteSiswa($mode);
            
            $deleted['tugas_tambahan'] = $this->deleteTugasTambahan($mode);
            $deleted['gtk'] = $this->deleteGtk($mode);
            
            $deleted['kelas'] = $this->deleteKelas($mode);
            $deleted['tahun_pelajaran'] = $this->deleteTahunPelajaran($mode);
            $deleted['jurusan'] = $this->deleteJurusan($mode);
            $deleted['kurikulum'] = $this->deleteKurikulum($mode);
            
            // Clean activity logs
            $deleted['activity_logs'] = $this->cleanActivityLogs();

            DB::commit();

            // Log critical action
            Log::critical('SYSTEM RESET: ALL DATA', [
                'mode' => $mode,
                'user_id' => Auth::id(),
                'user_name' => Auth::user()->name,
                'deleted_counts' => $deleted,
                'backup_file' => $backup['filename'] ?? null,
                'ip_address' => request()->ip(),
                'timestamp' => now(),
            ]);

            return [
                'success' => true,
                'message' => 'Semua data berhasil di' . ($mode === 'archive' ? 'arsipkan' : 'hapus'),
                'deleted' => $deleted,
                'backup' => $backup,
            ];

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('System reset failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Delete SISWA data only
     */
    public function deleteSiswaOnly($mode = 'permanent', $autoBackup = true)
    {
        try {
            if ($autoBackup) {
                $backup = $this->backupService->createBackup('before_delete_siswa');
                if (!$backup['success']) {
                    throw new \Exception('Backup gagal: ' . $backup['error']);
                }
            }

            DB::beginTransaction();

            $deleted = [];
            $deleted['siswa_kelas'] = $this->deleteSiswaKelas($mode);
            $deleted['mutasi_siswa'] = $this->deleteMutasiSiswa($mode);
            $deleted['dokumen_siswa'] = $this->deleteDokumenSiswa($mode);
            $deleted['ortu'] = $this->deleteOrtu($mode);
            $deleted['siswa'] = $this->deleteSiswa($mode);

            DB::commit();

            Log::warning('DELETED: Siswa data', [
                'mode' => $mode,
                'user_id' => Auth::id(),
                'deleted_counts' => $deleted,
            ]);

            return [
                'success' => true,
                'message' => 'Data Siswa berhasil di' . ($mode === 'archive' ? 'arsipkan' : 'hapus'),
                'deleted' => $deleted,
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Delete GTK data only
     */
    public function deleteGtkOnly($mode = 'permanent', $autoBackup = true)
    {
        try {
            if ($autoBackup) {
                $backup = $this->backupService->createBackup('before_delete_gtk');
                if (!$backup['success']) {
                    throw new \Exception('Backup gagal: ' . $backup['error']);
                }
            }

            DB::beginTransaction();

            $deleted = [];
            $deleted['tugas_tambahan'] = $this->deleteTugasTambahan($mode);
            $deleted['gtk'] = $this->deleteGtk($mode);

            DB::commit();

            Log::warning('DELETED: GTK data', [
                'mode' => $mode,
                'user_id' => Auth::id(),
                'deleted_counts' => $deleted,
            ]);

            return [
                'success' => true,
                'message' => 'Data GTK berhasil di' . ($mode === 'archive' ? 'arsipkan' : 'hapus'),
                'deleted' => $deleted,
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Delete KELAS data only
     */
    public function deleteKelasOnly($mode = 'permanent', $autoBackup = true)
    {
        try {
            if ($autoBackup) {
                $backup = $this->backupService->createBackup('before_delete_kelas');
                if (!$backup['success']) {
                    throw new \Exception('Backup gagal: ' . $backup['error']);
                }
            }

            DB::beginTransaction();

            $deleted = [];
            $deleted['kelas'] = $this->deleteKelas($mode);

            DB::commit();

            Log::warning('DELETED: Kelas data', [
                'mode' => $mode,
                'user_id' => Auth::id(),
                'deleted_counts' => $deleted,
            ]);

            return [
                'success' => true,
                'message' => 'Data Kelas berhasil di' . ($mode === 'archive' ? 'arsipkan' : 'hapus'),
                'deleted' => $deleted,
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    // ========== PRIVATE DELETION METHODS ==========

    private function deleteSiswa($mode)
    {
        if ($mode === 'archive') {
            // Soft delete with archived_at
            return Siswa::update(['deleted_at' => now()]);
        } else {
            // Get siswa user IDs before delete
            $userIds = Siswa::pluck('user_id')->filter();
            
            // Delete siswa
            $count = Siswa::count();
            Siswa::query()->delete();
            
            // Delete associated users
            User::whereIn('id', $userIds)->delete();
            
            return $count;
        }
    }

    private function deleteOrtu($mode)
    {
        if ($mode === 'archive') {
            return Ortu::update(['deleted_at' => now()]);
        } else {
            $count = Ortu::count();
            Ortu::query()->delete();
            return $count;
        }
    }

    private function deleteDokumenSiswa($mode)
    {
        if ($mode === 'archive') {
            return DokumenSiswa::update(['deleted_at' => now()]);
        } else {
            $count = DokumenSiswa::count();
            // TODO: Delete physical files from storage
            DokumenSiswa::query()->delete();
            return $count;
        }
    }

    private function deleteSiswaKelas($mode)
    {
        $count = SiswaKelas::count();
        SiswaKelas::query()->delete();
        return $count;
    }

    private function deleteMutasiSiswa($mode)
    {
        $count = MutasiSiswa::count();
        MutasiSiswa::query()->delete();
        return $count;
    }

    private function deleteGtk($mode)
    {
        if ($mode === 'archive') {
            return Gtk::update(['deleted_at' => now()]);
        } else {
            // Set wali_kelas_id to NULL in kelas
            Kelas::whereNotNull('wali_kelas_id')->update(['wali_kelas_id' => null]);
            
            // Get gtk user IDs
            $userIds = Gtk::pluck('user_id')->filter();
            
            $count = Gtk::count();
            Gtk::query()->delete();
            
            // Delete associated users
            User::whereIn('id', $userIds)->delete();
            
            return $count;
        }
    }

    private function deleteTugasTambahan($mode)
    {
        $count = TugasTambahan::count();
        TugasTambahan::query()->delete();
        return $count;
    }

    private function deleteKelas($mode)
    {
        // Set siswa.kelas_saat_ini_id to NULL
        Siswa::whereNotNull('kelas_saat_ini_id')->update(['kelas_saat_ini_id' => null]);
        
        $count = Kelas::count();
        Kelas::query()->delete();
        return $count;
    }

    private function deleteTahunPelajaran($mode)
    {
        $count = TahunPelajaran::count();
        TahunPelajaran::query()->delete();
        return $count;
    }

    private function deleteJurusan($mode)
    {
        $count = Jurusan::count();
        Jurusan::query()->delete();
        return $count;
    }

    private function deleteKurikulum($mode)
    {
        $count = Kurikulum::count();
        Kurikulum::query()->delete();
        return $count;
    }

    private function cleanActivityLogs()
    {
        $count = Activity::whereIn('subject_type', [
            'App\Models\Siswa',
            'App\Models\Gtk',
            'App\Models\Kelas',
            'App\Models\TahunPelajaran',
            'App\Models\Kurikulum',
            'App\Models\Jurusan',
        ])->delete();

        return $count;
    }
}
