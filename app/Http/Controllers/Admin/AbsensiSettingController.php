<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AbsensiSetting;
use App\Models\AbsensiLocation;
use App\Models\HariLibur;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AbsensiSettingController extends Controller
{
    /**
     * Halaman setting absensi
     */
    public function index()
    {
        $settings = AbsensiSetting::orderBy('group')->orderBy('key')->get()->groupBy('group');
        $locations = AbsensiLocation::orderBy('nama')->get();
        $hariLibur = HariLibur::orderBy('tanggal', 'desc')->get();

        return view('admin.absensi.settings', compact('settings', 'locations', 'hariLibur'));
    }

    /**
     * Update settings
     */
    public function updateSettings(Request $request)
    {
        $request->validate([
            'settings' => 'required|array',
            'settings.*' => 'nullable|string|max:500',
        ]);

        foreach ($request->settings as $key => $value) {
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
            'kode' => 'required|string|max:50|unique:absensi_locations,kode,' . $location->id,
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
        $location->update(['is_active' => !$location->is_active]);

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
            if (!$exists) {
                HariLibur::create($h);
                $count++;
            }
        }

        return redirect()->route('admin.absensi.settings')
            ->with('success', "{$count} hari libur nasional 2026 berhasil ditambahkan.");
    }
}
