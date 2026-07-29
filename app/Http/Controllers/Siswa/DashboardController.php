<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use App\Models\PengumumanKelulusan;
use App\Models\Siswa;
use App\Models\SnbpMenu;
use App\Models\SnbpRegistration;
use App\Models\TahunPelajaran;
use App\Services\StudentGraduationAccessService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index(Request $request, StudentGraduationAccessService $accessService)
    {
        $user = Auth::user();
        $siswa = $user->siswa;
        $isImpersonating = $request->attributes->has('impersonation');

        if (!$siswa) {
            // SECURITY: Don't auto-create siswa if user is not actually a siswa
            // This prevents GTK or other users from accidentally creating duplicate siswa records
            
            // Check if user is actually marked as siswa (not GTK, admin, etc.)
            if (!$user->isSiswa()) {
                abort(403, 'Unauthorized: You are not a siswa. Please contact administrator.');
            }
            
            // Additional validation: Check if username/NISN already exists in GTK
            $existingGtk = \App\Models\Gtk::where('nik', $user->username)->first();
            if ($existingGtk) {
                abort(403, 'Unauthorized: This NIK is registered as GTK. Please contact administrator.');
            }
            
            // Safe to create siswa record
            $siswa = Siswa::create([
                'user_id' => $user->id,
                'nisn' => $user->username,
                'nama_lengkap' => $user->name,
                'jenis_kelamin' => 'L', // Default, will be updated in profile
            ]);
        }

        // Check if user needs to change password (handled by ForcePasswordChange middleware)
        if ($user->is_first_login && ! $isImpersonating) {
            return redirect()->route('siswa.force-setup');
        }

        if (!$siswa->data_ortu_completed) {
            return redirect()->route('siswa.profile.ortu')->with('info', 'Silakan lengkapi data orangtua terlebih dahulu.');
        }

        if (!$siswa->data_diri_completed) {
            return redirect()->route('siswa.profile.diri')->with('info', 'Silakan lengkapi data diri Anda.');
        }

        $siswa->load([
            'kelasAktif.waliKelas.gtk',
            'kelasAktif.ketuaKelasRecord.siswa',
        ]);

        $kelasAktif = $siswa->kelasAktif->first();
        $temanSekelas = collect();
        $temanSekelasOnline = 0;

        if ($kelasAktif) {
            $temanSekelas = $kelasAktif->siswaAktif()
                ->where('siswa.id', '!=', $siswa->id)
                ->with(['user.latestSession'])
                ->orderBy('siswa.nama_lengkap')
                ->get();

            $temanSekelasOnline = $temanSekelas->filter(function ($teman) {
                return $teman->user?->latestSession?->isStillOnline();
            })->count();
        }

        // Get tahun pelajaran aktif
        $tahunPelajaranAktif = TahunPelajaran::where('is_active', true)->first();
        $graduationAnnouncementInfo = null;

        $graduationEnrollment = $accessService->resolveAnnouncementEnrollment($siswa);
        if ($graduationEnrollment) {
            $announcement = PengumumanKelulusan::query()
                ->where('tahun_pelajaran_id', $graduationEnrollment->tahun_pelajaran_id)
                ->where('siswa_id', $siswa->id)
                ->first();

            $graduationAnnouncementInfo = [
                'starts_at' => null,
                'is_available' => true,
                'has_result' => true,
                'opened_at' => $announcement?->opened_at,
                'status_label' => $announcement?->status_label,
                'route' => route('siswa.kelulusan-pengumuman.index'),
            ];
        }

        $snbpReminder = null;
        $snbpMenu = SnbpMenu::getActiveMenu();

        if ($snbpMenu) {
            $snbpStatus = $snbpMenu->getSiswaStatus($siswa->id);

            if ($snbpStatus === true) {
                $snbpRegistration = SnbpRegistration::query()
                    ->where('snbp_menu_id', $snbpMenu->id)
                    ->where('siswa_id', $siswa->id)
                    ->where('tahun_pelajaran_id', $snbpMenu->tahun_pelajaran_id)
                    ->first();

                if (!$snbpRegistration || blank($snbpRegistration->nomor_pendaftaran)) {
                    $snbpReminder = [
                        'menu_name' => $snbpMenu->nama_menu,
                        'tahun_pelajaran' => $snbpMenu->tahunPelajaran->nama ?? null,
                        'route' => route('siswa.snbp.index'),
                    ];
                }
            }
        }

        return view('siswa.dashboard', compact(
            'siswa',
            'tahunPelajaranAktif',
            'snbpReminder',
            'temanSekelas',
            'temanSekelasOnline',
            'graduationAnnouncementInfo'
        ));
    }
}
