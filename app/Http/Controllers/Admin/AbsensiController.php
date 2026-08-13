<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Absensi;
use App\Models\AbsensiLocation;
use App\Models\AbsensiLog;
use App\Models\AbsensiSetting;
use App\Models\FaceEncoding;
use App\Models\Gtk;
use App\Models\HariLibur;
use App\Models\TahunPelajaran;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AbsensiController extends Controller
{
    /**
     * Dashboard absensi hari ini (admin view)
     */
    public function index(Request $request)
    {
        $request->validate([
            'tanggal' => ['nullable', 'date_format:Y-m-d'],
            'status' => ['nullable', 'in:hadir,terlambat,izin,sakit,alpa,dinas_luar,cuti'],
            'metode' => ['nullable', 'in:face,manual'],
            'q' => ['nullable', 'string', 'max:100'],
        ]);

        $tanggal = $request->get('tanggal', now()->format('Y-m-d'));
        $isHoliday = HariLibur::isHoliday($tanggal);
        $tahunPelajaran = TahunPelajaran::where('is_active', true)->first();
        $isPersonalScope = $this->isPersonalGtkScope($request);

        $baseQuery = Absensi::gtk()
            ->tanggal($tanggal)
            ->when($isPersonalScope, fn ($query) => $query->where('user_id', $request->user()->id));

        $dailyAttendances = (clone $baseQuery)->get();
        $absensis = $baseQuery
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->status))
            ->when($request->filled('metode'), fn ($query) => $query->where('metode_masuk', $request->metode))
            ->when($request->filled('q'), function ($query) use ($request) {
                $keyword = trim($request->q);
                $query->whereHas('user', function ($user) use ($keyword) {
                    $user->where('name', 'like', "%{$keyword}%")
                        ->orWhereHas('gtk', fn ($gtk) => $gtk
                            ->where('nama_lengkap', 'like', "%{$keyword}%")
                            ->orWhere('nip', 'like', "%{$keyword}%"));
                });
            })
            ->with(['user:id,name', 'user.gtk:id,user_id,nama_lengkap,nip,foto_profile,jenis_kelamin', 'location:id,nama'])
            ->orderBy('waktu_masuk')
            ->get();

        $totalGtk = $isPersonalScope
            ? 1
            : Gtk::active()
                ->whereNotNull('user_id')
                ->whereHas('user', fn ($user) => $user->where('is_active', true))
                ->count();
        $recorded = $dailyAttendances->count();

        $stats = [
            'total_gtk' => $totalGtk,
            'tercatat' => $recorded,
            'belum' => max($totalGtk - $recorded, 0),
            'persentase' => $totalGtk > 0 ? round(($recorded / $totalGtk) * 100, 1) : 0,
            'hadir' => $dailyAttendances->where('status', 'hadir')->count(),
            'terlambat' => $dailyAttendances->where('status', 'terlambat')->count(),
            'izin' => $dailyAttendances->where('status', 'izin')->count(),
            'sakit' => $dailyAttendances->where('status', 'sakit')->count(),
            'alpa' => $dailyAttendances->where('status', 'alpa')->count(),
            'dinas_luar' => $dailyAttendances->where('status', 'dinas_luar')->count(),
            'cuti' => $dailyAttendances->where('status', 'cuti')->count(),
            'sudah_pulang' => $dailyAttendances->whereNotNull('waktu_pulang')->count(),
        ];

        $locations = AbsensiLocation::where('is_active', true)->get();
        $gtkOptions = collect();
        if (! $isPersonalScope && $request->user()->can('create-absensi')) {
            $gtkOptions = Gtk::active()
                ->whereNotNull('user_id')
                ->whereHas('user', fn ($user) => $user->where('is_active', true))
                ->orderBy('nama_lengkap')
                ->get(['id', 'user_id', 'nama_lengkap', 'nip', 'foto_profile', 'jenis_kelamin']);
        }

        return view('admin.absensi.index', compact(
            'absensis', 'tanggal', 'isHoliday', 'stats', 'locations', 'tahunPelajaran', 'isPersonalScope', 'gtkOptions'
        ));
    }

    /**
     * Rekap bulanan
     */
    public function rekap(Request $request)
    {
        $bulan = $request->get('bulan', now()->month);
        $tahun = $request->get('tahun', now()->year);

        $absensis = Absensi::gtk()
            ->bulan($bulan, $tahun)
            ->when($this->isPersonalGtkScope($request), fn ($query) => $query->where('user_id', $request->user()->id))
            ->with(['user:id,name', 'user.gtk:id,user_id,nama_lengkap,nip'])
            ->get()
            ->groupBy('user_id');

        $hariLibur = HariLibur::getInRange(
            Carbon::create($tahun, $bulan, 1),
            Carbon::create($tahun, $bulan, 1)->endOfMonth()
        );

        $isPersonalScope = $this->isPersonalGtkScope($request);

        return view('admin.absensi.rekap', compact('absensis', 'bulan', 'tahun', 'hariLibur', 'isPersonalScope'));
    }

    /**
     * Kiosk mode - fullscreen attendance
     */
    public function kiosk(Request $request)
    {
        $locationId = $request->get('location');
        $userType = 'gtk';
        $location = $locationId ? AbsensiLocation::find($locationId) : null;
        $locations = AbsensiLocation::where('is_active', true)->get();

        $settings = [
            'jam_masuk' => Absensi::getJamMasukForType($userType),
            'jam_pulang' => Absensi::getJamPulangForType($userType),
            'toleransi' => AbsensiSetting::getValue('toleransi_terlambat', 15),
            'face_threshold' => AbsensiSetting::getValue('face_match_threshold', 0.45),
            'liveness_enabled' => AbsensiSetting::getValue('liveness_detection', true),
            'auto_detect' => AbsensiSetting::getValue('auto_face_detect', true),
            'detection_interval' => AbsensiSetting::getValue('detection_interval_ms', 200),
        ];

        return view('admin.absensi.kiosk', compact('location', 'locations', 'settings', 'userType'));
    }

    /**
     * API: Record attendance from kiosk/face scan
     */
    public function recordFace(Request $request)
    {
        // Normalize empty strings to null
        $request->merge([
            'location_id' => $request->location_id ?: null,
        ]);

        $request->validate([
            'user_id' => 'required|uuid|exists:users,id',
            'user_type' => 'nullable|in:gtk',
            'confidence' => 'required|numeric|min:0|max:1',
            'location_id' => 'nullable|uuid',
            'photo' => 'nullable|string', // base64
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'type' => 'required|in:masuk,pulang',
        ]);

        $today = now()->format('Y-m-d');

        // Cek hari libur
        if (HariLibur::isHoliday($today)) {
            return response()->json([
                'success' => false,
                'message' => 'Hari ini adalah hari libur. Absensi tidak dicatat.',
            ], 422);
        }

        $tahunPelajaran = TahunPelajaran::where('is_active', true)->first();
        $now = now();
        $userType = 'gtk';
        abort_unless(User::query()->whereKey($request->user_id)->whereHas('gtk')->exists(), 422, 'Akun GTK tidak ditemukan.');
        $approvedFace = FaceEncoding::where('user_id', $request->user_id)
            ->where('user_type', $userType)
            ->where('is_active', true)
            ->where('is_verified', true)
            ->exists();

        if (! $approvedFace) {
            return response()->json([
                'success' => false,
                'message' => 'Data wajah belum approved atau tidak aktif untuk digunakan di kiosk.',
            ], 422);
        }

        // Simpan foto capture
        $photoPath = null;
        if ($request->photo) {
            $photoPath = $this->saveCapturePhoto($request->photo, $request->user_id, $request->type);
        }

        if ($request->type === 'masuk') {
            // Cek sudah absen masuk belum
            $existing = Absensi::where('user_id', $request->user_id)
                ->where('tanggal', $today)
                ->first();

            if ($existing && $existing->waktu_masuk) {
                return response()->json([
                    'success' => false,
                    'message' => 'Sudah melakukan absen masuk hari ini pada '.$existing->waktu_masuk->format('H:i:s'),
                    'data' => $existing,
                ], 422);
            }

            $status = Absensi::determineStatusForType($now, $userType);

            $absensi = Absensi::updateOrCreate(
                ['user_id' => $request->user_id, 'tanggal' => $today],
                [
                    'user_type' => $userType,
                    'tahun_pelajaran_id' => $tahunPelajaran?->id,
                    'waktu_masuk' => $now,
                    'status' => $status,
                    'metode_masuk' => 'face',
                    'face_confidence_masuk' => $request->confidence,
                    'foto_masuk' => $photoPath,
                    'location_id' => $request->location_id,
                    'latitude_masuk' => $request->latitude,
                    'longitude_masuk' => $request->longitude,
                    'device_masuk' => $request->header('User-Agent'),
                    'ip_masuk' => $request->ip(),
                ]
            );

            AbsensiLog::record($absensi->id, $request->user_id, 'check_in', null, [
                'status' => $status,
                'waktu' => $now->format('H:i:s'),
                'confidence' => $request->confidence,
                'metode' => 'face',
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Absen masuk berhasil!',
                'data' => [
                    'status' => $status,
                    'waktu' => $now->format('H:i:s'),
                    'nama' => $this->resolveDisplayName($absensi, $userType),
                ],
            ]);
        }

        // PULANG
        $absensi = Absensi::where('user_id', $request->user_id)
            ->where('tanggal', $today)
            ->first();

        if (! $absensi) {
            return response()->json([
                'success' => false,
                'message' => 'Belum ada data absen masuk hari ini.',
            ], 422);
        }

        if ($absensi->waktu_pulang) {
            return response()->json([
                'success' => false,
                'message' => 'Sudah melakukan absen pulang hari ini pada '.$absensi->waktu_pulang->format('H:i:s'),
            ], 422);
        }

        // Determine status pulang
        $jamPulang = Absensi::getJamPulangForType($userType);
        $batasPulang = Carbon::parse($today.' '.$jamPulang);
        $statusPulang = $now->lt($batasPulang) ? 'pulang_cepat' : 'tepat_waktu';

        $absensi->update([
            'waktu_pulang' => $now,
            'status_pulang' => $statusPulang,
            'metode_pulang' => 'face',
            'face_confidence_pulang' => $request->confidence,
            'foto_pulang' => $photoPath,
            'latitude_pulang' => $request->latitude,
            'longitude_pulang' => $request->longitude,
            'device_pulang' => $request->header('User-Agent'),
            'ip_pulang' => $request->ip(),
        ]);

        AbsensiLog::record($absensi->id, $request->user_id, 'check_out', null, [
            'status_pulang' => $statusPulang,
            'waktu' => $now->format('H:i:s'),
            'confidence' => $request->confidence,
            'metode' => 'face',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Absen pulang berhasil!',
            'data' => [
                'status_pulang' => $statusPulang,
                'waktu' => $now->format('H:i:s'),
                'durasi' => $absensi->durasi_kerja,
                'nama' => $this->resolveDisplayName($absensi, $userType),
            ],
        ]);
    }

    /**
     * Manual attendance input (fallback ketika kamera bermasalah)
     */
    public function manualInput(Request $request)
    {
        $request->validate([
            'user_id' => 'required|uuid|exists:users,id',
            'tanggal' => 'required|date',
            'status' => 'required|in:hadir,terlambat,izin,sakit,alpa,dinas_luar,cuti',
            'waktu_masuk' => 'nullable|date_format:H:i',
            'waktu_pulang' => 'nullable|date_format:H:i',
            'catatan' => 'nullable|string|max:500',
            'file_bukti' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
        ]);
        abort_unless(User::query()->whereKey($request->user_id)->whereHas('gtk')->exists(), 422, 'Akun GTK tidak ditemukan.');

        $tahunPelajaran = TahunPelajaran::where('is_active', true)->first();

        // Upload bukti jika ada
        $fileBukti = null;
        if ($request->hasFile('file_bukti')) {
            $fileBukti = $request->file('file_bukti')->store('absensi/bukti', 'public');
        }

        $tanggal = $request->tanggal;
        $waktuMasuk = $request->waktu_masuk
            ? Carbon::parse($tanggal.' '.$request->waktu_masuk)
            : null;
        $waktuPulang = $request->waktu_pulang
            ? Carbon::parse($tanggal.' '.$request->waktu_pulang)
            : null;

        $absensi = Absensi::updateOrCreate(
            ['user_id' => $request->user_id, 'tanggal' => $tanggal],
            [
                'user_type' => 'gtk',
                'tahun_pelajaran_id' => $tahunPelajaran?->id,
                'waktu_masuk' => $waktuMasuk,
                'waktu_pulang' => $waktuPulang,
                'status' => $request->status,
                'metode_masuk' => 'manual',
                'catatan' => $request->catatan,
                'file_bukti' => $fileBukti,
                'input_by' => Auth::id(),
                'device_masuk' => $request->header('User-Agent'),
                'ip_masuk' => $request->ip(),
            ]
        );

        AbsensiLog::record($absensi->id, Auth::id(), 'manual_input', null, [
            'status' => $request->status,
            'tanggal' => $tanggal,
            'input_by' => Auth::user()->name,
        ]);

        return redirect()->route('admin.absensi.index', ['tanggal' => $tanggal])
            ->with('success', 'Absensi manual berhasil dicatat.');
    }

    /**
     * Edit absensi
     */
    public function update(Request $request, Absensi $absensi)
    {
        abort_unless($absensi->user_type === 'gtk', 404);
        $request->validate([
            'status' => 'required|in:hadir,terlambat,izin,sakit,alpa,dinas_luar,cuti',
            'catatan' => 'nullable|string|max:500',
            'edit_reason' => 'required|string|max:255',
        ]);

        $oldValues = $absensi->only(['status', 'catatan']);

        $absensi->update([
            'status' => $request->status,
            'catatan' => $request->catatan,
            'edited_by' => Auth::id(),
            'edited_at' => now(),
            'edit_reason' => $request->edit_reason,
        ]);

        AbsensiLog::record($absensi->id, Auth::id(), 'updated', $oldValues, [
            'status' => $request->status,
            'catatan' => $request->catatan,
        ], $request->edit_reason);

        return redirect()->route('admin.absensi.index', ['tanggal' => $absensi->tanggal->format('Y-m-d')])
            ->with('success', 'Data absensi berhasil diperbarui.');
    }

    /** Export rekap GTK sebagai CSV yang kompatibel dengan spreadsheet. */
    public function export(Request $request)
    {
        $bulan = max(1, min(12, (int) $request->get('bulan', now()->month)));
        $tahun = max(2000, min(2100, (int) $request->get('tahun', now()->year)));
        $rows = Absensi::gtk()->bulan($bulan, $tahun)
            ->when($this->isPersonalGtkScope($request), fn ($query) => $query->where('user_id', $request->user()->id))
            ->with(['user:id,name', 'user.gtk:id,user_id,nama_lengkap,nip', 'location:id,nama'])
            ->orderBy('tanggal')->orderBy('waktu_masuk')->get();
        $filename = sprintf('rekap-presensi-gtk-%04d-%02d.csv', $tahun, $bulan);

        return response()->streamDownload(function () use ($rows) {
            $output = fopen('php://output', 'wb');
            fwrite($output, "\xEF\xBB\xBF");
            fputcsv($output, ['Tanggal', 'Nama GTK', 'NIP', 'Masuk', 'Pulang', 'Status', 'Status Pulang', 'Metode', 'Lokasi', 'Catatan'], ';');
            foreach ($rows as $attendance) {
                fputcsv($output, [
                    $attendance->tanggal?->format('d/m/Y'),
                    $attendance->user?->gtk?->nama_lengkap ?? $attendance->user?->name,
                    $attendance->user?->gtk?->nip,
                    $attendance->waktu_masuk?->format('H:i:s'),
                    $attendance->waktu_pulang?->format('H:i:s'),
                    $attendance->status,
                    $attendance->status_pulang,
                    $attendance->metode_masuk,
                    $attendance->location?->nama,
                    $attendance->catatan,
                ], ';');
            }
            fclose($output);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    /**
     * API: Get today's attendance data for kiosk sidebar
     */
    public function todayData(Request $request)
    {
        $today = now()->format('Y-m-d');
        $userType = 'gtk';

        $absensis = Absensi::query()
            ->where('user_type', $userType)
            ->tanggal($today)
            ->when($this->isPersonalGtkScope($request), fn ($query) => $query->where('user_id', $request->user()->id))
            ->with([
                'user:id,name',
                'user.gtk:id,user_id,nama_lengkap',
                'user.siswa:id,user_id,nama_lengkap',
            ])
            ->orderBy('waktu_masuk', 'desc')
            ->get();

        $totalUsers = $this->isPersonalGtkScope($request) ? 1 : \App\Models\Gtk::count();

        $data = $absensis->map(function ($a) {
            return [
                'nama' => $a->user_type === 'siswa'
                    ? ($a->user->siswa?->nama_lengkap ?? $a->user->name)
                    : ($a->user->gtk?->nama_lengkap ?? $a->user->name),
                'waktu' => $a->waktu_masuk ? $a->waktu_masuk->format('H:i') : '-',
                'status' => $a->status,
            ];
        });

        $stats = [
            'hadir' => $absensis->where('status', 'hadir')->count(),
            'terlambat' => $absensis->where('status', 'terlambat')->count(),
            'belum' => max($totalUsers - $absensis->count(), 0),
        ];

        return response()->json(['success' => true, 'data' => $data, 'stats' => $stats]);
    }

    private function normalizeUserType(?string $userType): string
    {
        return $userType === 'siswa' ? 'siswa' : 'gtk';
    }

    private function isPersonalGtkScope(Request $request): bool
    {
        $user = $request->user();
        $isManager = $user->hasAnyRole(['Super Admin', 'Admin', 'Operator', 'Kepala Madrasah', 'WAKA'])
            || $user->can('manage-absensi');

        return ! $isManager;
    }

    private function resolveDisplayName(Absensi $absensi, string $userType): string
    {
        return $userType === 'siswa'
            ? ($absensi->user->siswa?->nama_lengkap ?? $absensi->user->name)
            : ($absensi->user->gtk?->nama_lengkap ?? $absensi->user->name);
    }

    /**
     * Save capture photo from base64
     */
    private function saveCapturePhoto(string $base64, string $userId, string $type): ?string
    {
        if (! preg_match('/^data:image\/(\w+);base64,/', $base64, $matches)) {
            return null;
        }

        $extension = $matches[1];
        $data = base64_decode(preg_replace('/^data:image\/\w+;base64,/', '', $base64));
        if ($data === false) {
            return null;
        }

        $date = now()->format('Y-m-d');
        $filename = "absensi/captures/{$date}/{$userId}_{$type}.".$extension;
        \Illuminate\Support\Facades\Storage::disk('public')->put($filename, $data);

        return $filename;
    }
}
