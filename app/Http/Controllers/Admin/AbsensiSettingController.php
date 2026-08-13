<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AbsensiLocation;
use App\Models\AbsensiOperationalSchedule;
use App\Models\AbsensiSetting;
use App\Models\HariLibur;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AbsensiSettingController extends Controller
{
    /**
     * Halaman setting absensi
     */
    public function index()
    {
        $this->ensureDefaultSettings();
        $settings = AbsensiSetting::query()
            ->where('group', '!=', 'waktu')
            ->orderBy('group')->orderBy('key')->get()->groupBy('group');
        $locations = AbsensiLocation::orderBy('nama')->get();
        $hariLibur = HariLibur::orderBy('tanggal', 'desc')->get();
        $operationalSchedules = AbsensiOperationalSchedule::query()
            ->orderBy('user_type')->orderBy('day_of_week')->get()
            ->groupBy('user_type')->map(fn ($items) => $items->keyBy('day_of_week'));

        return view('admin.absensi.settings', compact('settings', 'locations', 'hariLibur', 'operationalSchedules'));
    }

    public function updateOperationalSchedules(Request $request)
    {
        $validated = $request->validate([
            'schedules' => ['required', 'array'],
            'schedules.*.*.active' => ['required', 'boolean'],
            'schedules.*.*.check_in_open' => ['required', 'date_format:H:i'],
            'schedules.*.*.on_time_until' => ['required', 'date_format:H:i'],
            'schedules.*.*.check_in_close' => ['required', 'date_format:H:i'],
            'schedules.*.*.check_out_open' => ['required', 'date_format:H:i'],
            'schedules.*.*.check_out_close' => ['required', 'date_format:H:i'],
        ]);

        foreach (['gtk', 'siswa'] as $type) {
            foreach (range(1, 7) as $day) {
                $row = $validated['schedules'][$type][$day] ?? null;
                if (! $row) {
                    throw ValidationException::withMessages(["schedules.{$type}.{$day}" => 'Jadwal harian tidak lengkap.']);
                }
                if (! ($row['check_in_open'] <= $row['on_time_until']
                    && $row['on_time_until'] <= $row['check_in_close']
                    && $row['check_in_close'] < $row['check_out_open']
                    && $row['check_out_open'] <= $row['check_out_close'])) {
                    throw ValidationException::withMessages(["schedules.{$type}.{$day}.check_in_open" => "Urutan waktu {$type} hari ke-{$day} tidak valid."]);
                }
            }
        }

        DB::transaction(function () use ($validated) {
            foreach (['gtk', 'siswa'] as $type) {
                foreach (range(1, 7) as $day) {
                    $row = $validated['schedules'][$type][$day];
                    AbsensiOperationalSchedule::updateOrCreate(
                        ['user_type' => $type, 'day_of_week' => $day],
                        [
                            'is_active' => (bool) $row['active'],
                            'check_in_open' => $row['check_in_open'],
                            'on_time_until' => $row['on_time_until'],
                            'check_in_close' => $row['check_in_close'],
                            'check_out_open' => $row['check_out_open'],
                            'check_out_close' => $row['check_out_close'],
                        ]
                    );
                }
            }
        });

        return redirect()->route('admin.absensi.settings')
            ->with('success', 'Jadwal operasional kiosk GTK dan siswa berhasil diperbarui.');
    }

    /**
     * Update settings
     */
    public function updateSettings(Request $request)
    {
        $this->ensureDefaultSettings();
        $request->validate([
            'settings' => 'required|array',
            'settings.*' => 'nullable|string|max:500',
        ]);

        $allowedKeys = AbsensiSetting::query()->where('group', '!=', 'waktu')->pluck('key');
        foreach ($request->settings as $key => $value) {
            if (! $allowedKeys->contains($key)) {
                continue;
            }
            AbsensiSetting::setValue($key, $value);
        }

        return redirect()->route('admin.absensi.settings')
            ->with('success', 'Pengaturan absensi berhasil disimpan.');
    }

    // ============================================
    // LOKASI MANAGEMENT
    // ============================================

    public function storeLocation(Request $request)
    {
        $request->validate([
            'nama' => 'required|string|max:255',
            'kode' => 'required|string|max:50|unique:absensi_locations,kode',
            'alamat' => 'nullable|string',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'radius_meter' => 'nullable|integer|min:10|max:1000',
        ]);

        AbsensiLocation::create($request->only([
            'nama', 'kode', 'alamat', 'latitude', 'longitude', 'radius_meter',
        ]));

        return redirect()->route('admin.absensi.settings')
            ->with('success', 'Lokasi absensi berhasil ditambahkan.');
    }

    public function updateLocation(Request $request, AbsensiLocation $location)
    {
        $request->validate([
            'nama' => 'required|string|max:255',
            'kode' => 'required|string|max:50|unique:absensi_locations,kode,'.$location->id,
            'alamat' => 'nullable|string',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'radius_meter' => 'nullable|integer|min:10|max:1000',
        ]);

        $location->update($request->only([
            'nama', 'kode', 'alamat', 'latitude', 'longitude', 'radius_meter',
        ]));

        return redirect()->route('admin.absensi.settings')
            ->with('success', 'Lokasi absensi berhasil diperbarui.');
    }

    public function toggleLocation(AbsensiLocation $location)
    {
        $location->update(['is_active' => ! $location->is_active]);

        return response()->json(['success' => true, 'is_active' => $location->is_active]);
    }

    public function destroyLocation(AbsensiLocation $location)
    {
        $location->delete();

        return redirect()->route('admin.absensi.settings')
            ->with('success', 'Lokasi absensi berhasil dihapus.');
    }

    // ============================================
    // HARI LIBUR MANAGEMENT
    // ============================================

    public function storeHariLibur(Request $request)
    {
        $request->validate([
            'tanggal' => 'required|date',
            'nama' => 'required|string|max:255',
            'jenis' => 'required|in:nasional,keagamaan,sekolah,cuti_bersama',
            'keterangan' => 'nullable|string',
            'is_recurring' => 'nullable|boolean',
        ]);

        HariLibur::create([
            'tanggal' => $request->tanggal,
            'nama' => $request->nama,
            'jenis' => $request->jenis,
            'keterangan' => $request->keterangan,
            'is_recurring' => $request->boolean('is_recurring'),
        ]);

        return redirect()->route('admin.absensi.settings')
            ->with('success', 'Hari libur berhasil ditambahkan.');
    }

    public function destroyHariLibur(HariLibur $hariLibur)
    {
        $hariLibur->delete();

        return redirect()->route('admin.absensi.settings')
            ->with('success', 'Hari libur berhasil dihapus.');
    }

    /**
     * Seed default hari libur nasional Indonesia 2026
     */
    public function seedHariLibur()
    {
        $holidays = [
            ['tanggal' => '2026-01-01', 'nama' => 'Tahun Baru Masehi', 'jenis' => 'nasional', 'is_recurring' => true],
            ['tanggal' => '2026-01-29', 'nama' => 'Tahun Baru Imlek', 'jenis' => 'keagamaan', 'is_recurring' => false],
            ['tanggal' => '2026-03-20', 'nama' => 'Hari Raya Nyepi', 'jenis' => 'keagamaan', 'is_recurring' => false],
            ['tanggal' => '2026-03-20', 'nama' => 'Isra Mi\'raj Nabi Muhammad SAW', 'jenis' => 'keagamaan', 'is_recurring' => false],
            ['tanggal' => '2026-04-03', 'nama' => 'Wafat Isa Al-Masih', 'jenis' => 'keagamaan', 'is_recurring' => false],
            ['tanggal' => '2026-05-01', 'nama' => 'Hari Buruh Internasional', 'jenis' => 'nasional', 'is_recurring' => true],
            ['tanggal' => '2026-05-14', 'nama' => 'Kenaikan Isa Al-Masih', 'jenis' => 'keagamaan', 'is_recurring' => false],
            ['tanggal' => '2026-05-16', 'nama' => 'Hari Raya Waisak', 'jenis' => 'keagamaan', 'is_recurring' => false],
            ['tanggal' => '2026-06-01', 'nama' => 'Hari Lahir Pancasila', 'jenis' => 'nasional', 'is_recurring' => true],
            ['tanggal' => '2026-06-17', 'nama' => 'Idul Adha 1447H', 'jenis' => 'keagamaan', 'is_recurring' => false],
            ['tanggal' => '2026-07-07', 'nama' => 'Tahun Baru Islam 1448H', 'jenis' => 'keagamaan', 'is_recurring' => false],
            ['tanggal' => '2026-08-17', 'nama' => 'Hari Kemerdekaan RI', 'jenis' => 'nasional', 'is_recurring' => true],
            ['tanggal' => '2026-09-15', 'nama' => 'Maulid Nabi Muhammad SAW', 'jenis' => 'keagamaan', 'is_recurring' => false],
            ['tanggal' => '2026-12-25', 'nama' => 'Hari Raya Natal', 'jenis' => 'keagamaan', 'is_recurring' => true],
        ];

        $count = 0;
        foreach ($holidays as $h) {
            $exists = HariLibur::where('tanggal', $h['tanggal'])
                ->where('nama', $h['nama'])->exists();
            if (! $exists) {
                HariLibur::create($h);
                $count++;
            }
        }

        return redirect()->route('admin.absensi.settings')
            ->with('success', "{$count} hari libur nasional 2026 berhasil ditambahkan.");
    }

    private function ensureDefaultSettings(): void
    {
        foreach ($this->defaultSettings() as $setting) {
            AbsensiSetting::firstOrCreate(
                ['key' => $setting['key']],
                $setting
            );
        }
    }

    private function defaultSettings(): array
    {
        return [
            [
                'key' => 'jam_masuk_gtk',
                'value' => '07:00',
                'type' => 'time',
                'group' => 'waktu',
                'label' => 'Jam Masuk GTK',
                'description' => 'Jam masuk GTK (format HH:mm)',
            ],
            [
                'key' => 'jam_pulang_gtk',
                'value' => '16:00',
                'type' => 'time',
                'group' => 'waktu',
                'label' => 'Jam Pulang GTK',
                'description' => 'Jam pulang GTK (format HH:mm)',
            ],
            [
                'key' => 'face_duplicate_threshold',
                'value' => '0.55',
                'type' => 'float',
                'group' => 'face',
                'label' => 'Face Duplicate Threshold',
                'description' => 'Batas kemiripan saat registrasi untuk mencegah satu wajah dipakai banyak akun',
            ],
        ];
    }
}
