<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Absensi;
use App\Models\FaceEncoding;
use App\Models\AbsensiLog;
use App\Models\AbsensiSetting;
use App\Models\AbsensiLocation;
use App\Models\HariLibur;
use App\Models\TahunPelajaran;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class AbsensiController extends Controller
{
    /**
     * Dashboard absensi hari ini (admin view)
     */
    public function index(Request $request)
    {
        $tanggal = $request->get('tanggal', now()->format('Y-m-d'));
        $isHoliday = HariLibur::isHoliday($tanggal);
        $tahunPelajaran = TahunPelajaran::where('is_active', true)->first();

        $absensis = Absensi::gtk()
            ->tanggal($tanggal)
            ->with(['user:id,name', 'user.gtk:id,user_id,nama_lengkap,nip,foto_profile', 'location:id,nama'])
            ->orderBy('waktu_masuk')
            ->get();

        // Stats
        $stats = [
            'hadir' => $absensis->where('status', 'hadir')->count(),
            'terlambat' => $absensis->where('status', 'terlambat')->count(),
            'izin' => $absensis->where('status', 'izin')->count(),
            'sakit' => $absensis->where('status', 'sakit')->count(),
            'alpa' => $absensis->where('status', 'alpa')->count(),
            'dinas_luar' => $absensis->where('status', 'dinas_luar')->count(),
            'cuti' => $absensis->where('status', 'cuti')->count(),
        ];

        $locations = AbsensiLocation::where('is_active', true)->get();

        return view('admin.absensi.index', compact(
            'absensis', 'tanggal', 'isHoliday', 'stats', 'locations', 'tahunPelajaran'
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
            ->with(['user:id,name', 'user.gtk:id,user_id,nama_lengkap,nip'])
            ->get()
            ->groupBy('user_id');

        $hariLibur = HariLibur::getInRange(
            Carbon::create($tahun, $bulan, 1),
            Carbon::create($tahun, $bulan, 1)->endOfMonth()
        );

        return view('admin.absensi.rekap', compact('absensis', 'bulan', 'tahun', 'hariLibur'));
    }

    /**
     * Kiosk mode - fullscreen attendance
     */
    public function kiosk(Request $request)
    {
        $locationId = $request->get('location');
        $userType = $this->normalizeUserType($request->get('type', 'gtk'));
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
            'user_type' => 'nullable|in:gtk,siswa',
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
        $userType = $this->normalizeUserType($request->input('user_type', 'gtk'));
        $approvedFace = FaceEncoding::where('user_id', $request->user_id)
            ->where('user_type', $userType)
            ->where('is_active', true)
            ->where('is_verified', true)
            ->exists();

        if (!$approvedFace) {
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
                    'message' => 'Sudah melakukan absen masuk hari ini pada ' . $existing->waktu_masuk->format('H:i:s'),
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

        if (!$absensi) {
            return response()->json([
                'success' => false,
                'message' => 'Belum ada data absen masuk hari ini.',
            ], 422);
        }

        if ($absensi->waktu_pulang) {
            return response()->json([
                'success' => false,
                'message' => 'Sudah melakukan absen pulang hari ini pada ' . $absensi->waktu_pulang->format('H:i:s'),
            ], 422);
        }

        // Determine status pulang
        $jamPulang = Absensi::getJamPulangForType($userType);
        $batasPulang = Carbon::parse($today . ' ' . $jamPulang);
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

        $tahunPelajaran = TahunPelajaran::where('is_active', true)->first();

        // Upload bukti jika ada
        $fileBukti = null;
        if ($request->hasFile('file_bukti')) {
            $fileBukti = $request->file('file_bukti')->store('absensi/bukti', 'public');
        }

        $tanggal = $request->tanggal;
        $waktuMasuk = $request->waktu_masuk
            ? Carbon::parse($tanggal . ' ' . $request->waktu_masuk)
            : null;
        $waktuPulang = $request->waktu_pulang
            ? Carbon::parse($tanggal . ' ' . $request->waktu_pulang)
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

    /**
     * Export rekap to Excel (placeholder)
     */
    public function export(Request $request)
    {
        // TODO: implement Excel export with maatwebsite/excel
        return back()->with('info', 'Fitur export sedang dalam pengembangan.');
    }

    /**
     * API: Get today's attendance data for kiosk sidebar
     */
    public function todayData(Request $request)
    {
        $today = now()->format('Y-m-d');
        $userType = $this->normalizeUserType($request->get('type', 'gtk'));

        $absensis = Absensi::query()
            ->where('user_type', $userType)
            ->tanggal($today)
            ->with([
                'user:id,name',
                'user.gtk:id,user_id,nama_lengkap',
                'user.siswa:id,user_id,nama_lengkap',
            ])
            ->orderBy('waktu_masuk', 'desc')
            ->get();

        $totalUsers = $userType === 'siswa' ? \App\Models\Siswa::count() : \App\Models\Gtk::count();

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
        if (!preg_match('/^data:image\/(\w+);base64,/', $base64, $matches)) {
            return null;
        }

        $extension = $matches[1];
        $data = base64_decode(preg_replace('/^data:image\/\w+;base64,/', '', $base64));
        if ($data === false) {
            return null;
        }

        $date = now()->format('Y-m-d');
        $filename = "absensi/captures/{$date}/{$userId}_{$type}." . $extension;
        \Illuminate\Support\Facades\Storage::disk('public')->put($filename, $data);

        return $filename;
    }
}
