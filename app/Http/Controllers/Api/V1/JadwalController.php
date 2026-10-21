<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Gtk;
use App\Models\JadwalPelajaran;
use App\Models\Kelas;
use App\Models\TahunPelajaran;
use App\Traits\ApiResponse;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @group Jadwal Pelajaran
 *
 * APIs for querying teaching schedules (daily, per class, per teacher) with formatted WhatsApp outputs.
 */
class JadwalController extends Controller
{
    use ApiResponse;

    /**
     * Get Schedule for Today or Specific Day
     *
     * Retrieve schedules for today (or filter by specific day, class, or teacher).
     */
    public function hariIni(Request $request): JsonResponse
    {
        $hariMap = [
            1 => 'senin',
            2 => 'selasa',
            3 => 'rabu',
            4 => 'kamis',
            5 => 'jumat',
            6 => 'sabtu',
            0 => 'minggu',
        ];

        $inputHari = strtolower((string) $request->input('hari'));
        if (!$inputHari || !in_array($inputHari, array_values($hariMap))) {
            $carbonDay = Carbon::now()->dayOfWeek;
            $inputHari = $hariMap[$carbonDay] ?? 'senin';
        }

        $kelasId = $request->input('kelas_id');
        $gtkId = $request->input('gtk_id');
        $kodeGtk = $request->input('kode_gtk');

        $activeTp = TahunPelajaran::where('is_active', true)->first();

        $query = JadwalPelajaran::with([
            'kelas:id,nama_kelas,tingkat',
            'mataPelajaran:id,nama,kode_mapel',
            'gtk:id,nama_lengkap,kode_gtk',
        ])
        ->where('hari', $inputHari)
        ->where('is_active', true);

        if ($activeTp) {
            $query->where('tahun_pelajaran_id', $activeTp->id);
            if ($activeTp->semester_aktif) {
                $query->where('semester', (int) $activeTp->semester_aktif);
            }
        }

        if ($kelasId) {
            $query->where('kelas_id', $kelasId);
        }

        if ($gtkId) {
            $query->where('gtk_id', $gtkId);
        }

        if ($kodeGtk) {
            $query->whereHas('gtk', fn ($q) => $q->where('kode_gtk', $kodeGtk));
        }

        $jadwal = $query->orderBy('jam_ke', 'asc')->get();

        // Build WhatsApp text
        $waLines = [];
        $namaHari = ucfirst($inputHari);
        $tanggal = Carbon::now()->translatedFormat('d F Y');
        $waLines[] = "📅 *JADWAL PELAJARAN - HARI {$namaHari}*";
        $waLines[] = "🗓️ Tanggal: {$tanggal}";
        if ($activeTp) {
            $waLines[] = "🏫 TP: {$activeTp->nama} (Sem. {$activeTp->semester_aktif})";
        }
        $waLines[] = "--------------------------------";

        if ($jadwal->isEmpty()) {
            $waLines[] = "🏖️ _Tidak ada jadwal pelajaran untuk hari {$namaHari}._";
        } else {
            // Group by class if not filtered by specific class
            if (!$kelasId && !$gtkId && !$kodeGtk) {
                $grouped = $jadwal->groupBy(fn ($item) => $item->kelas?->nama ?? 'Lainnya');
                foreach ($grouped as $klsNama => $items) {
                    $waLines[] = "\n🏛️ *Kelas: {$klsNama}*";
                    foreach ($items as $item) {
                        $guru = $item->gtk?->kode_gtk ? "[{$item->gtk->kode_gtk}] " . $item->gtk->nama_lengkap : ($item->gtk?->nama_lengkap ?? '-');
                        $mapel = $item->mataPelajaran?->nama ?? '-';
                        $jam = $item->jam_mulai ? "({$item->jam_mulai} - {$item->jam_selesai})" : "Jam ke-{$item->jam_ke}";
                        $waLines[] = "• {$jam}: *{$mapel}* ({$guru})";
                    }
                }
            } else {
                foreach ($jadwal as $item) {
                    $kls = $item->kelas?->nama ?? '-';
                    $guru = $item->gtk?->kode_gtk ? "[{$item->gtk->kode_gtk}] " . $item->gtk->nama_lengkap : ($item->gtk?->nama_lengkap ?? '-');
                    $mapel = $item->mataPelajaran?->nama ?? '-';
                    $jam = $item->jam_mulai ? "({$item->jam_mulai} - {$item->jam_selesai})" : "Jam ke-{$item->jam_ke}";
                    $waLines[] = "• {$jam} | *{$kls}*: {$mapel} - _{$guru}_";
                }
            }
        }
        $waLines[] = "--------------------------------";
        $waLines[] = "🤖 _SIMANSA Helper Bot_";

        $whatsappText = implode("\n", $waLines);

        return $this->successResponse([
            'hari' => $inputHari,
            'tahun_pelajaran' => $activeTp?->nama,
            'semester' => $activeTp?->semester_aktif,
            'total' => $jadwal->count(),
            'jadwal' => $jadwal,
        ], 'Jadwal hari ini berhasil dimuat.', 200, [], $whatsappText);
    }

    /**
     * Get Weekly Schedule for a Class
     *
     * Retrieve complete weekly timetable for a given class ID or class name.
     */
    public function perKelas(string $identifier): JsonResponse
    {
        $kelas = Kelas::where('id', $identifier)
            ->orWhere('nama_kelas', $identifier)
            ->first();

        if (!$kelas) {
            return $this->errorResponse('Kelas tidak ditemukan.', 404);
        }

        $activeTp = TahunPelajaran::where('is_active', true)->first();

        $query = JadwalPelajaran::with([
            'mataPelajaran:id,nama,kode_mapel',
            'gtk:id,nama_lengkap,kode_gtk',
        ])
        ->where('kelas_id', $kelas->id)
        ->where('is_active', true);

        if ($activeTp) {
            $query->where('tahun_pelajaran_id', $activeTp->id);
            if ($activeTp->semester_aktif) {
                $query->where('semester', (int) $activeTp->semester_aktif);
            }
        }

        $jadwal = $query->orderByRaw("FIELD(hari, 'senin', 'selasa', 'rabu', 'kamis', 'jumat', 'sabtu')")
            ->orderBy('jam_ke', 'asc')
            ->get();

        $grouped = $jadwal->groupBy('hari');

        $waLines = [];
        $waLines[] = "🏛️ *JADWAL KELAS {$kelas->nama}*";
        if ($activeTp) {
            $waLines[] = "🏫 TP: {$activeTp->nama} (Semester {$activeTp->semester_aktif})";
        }
        $waLines[] = "--------------------------------";

        if ($jadwal->isEmpty()) {
            $waLines[] = "❌ _Belum ada jadwal yang diatur untuk kelas ini._";
        } else {
            foreach (['senin', 'selasa', 'rabu', 'kamis', 'jumat', 'sabtu'] as $h) {
                if ($grouped->has($h)) {
                    $waLines[] = "\n📌 *" . strtoupper($h) . "*";
                    foreach ($grouped[$h] as $item) {
                        $mapel = $item->mataPelajaran?->nama ?? '-';
                        $guru = $item->gtk?->nama_lengkap ?? '-';
                        $kode = $item->gtk?->kode_gtk ? "[{$item->gtk->kode_gtk}] " : '';
                        $jam = $item->jam_mulai ? "({$item->jam_mulai}-{$item->jam_selesai})" : "Jam {$item->jam_ke}";
                        $waLines[] = "• {$jam}: *{$mapel}* ({$kode}{$guru})";
                    }
                }
            }
        }
        $waLines[] = "--------------------------------";
        $waLines[] = "🤖 _SIMANSA Helper Bot_";

        return $this->successResponse([
            'kelas' => $kelas->only(['id', 'nama', 'tingkat']),
            'jadwal_per_hari' => $grouped,
        ], 'Jadwal kelas berhasil dimuat.', 200, [], implode("\n", $waLines));
    }

    /**
     * Get Weekly Schedule for a Teacher
     *
     * Retrieve weekly teaching schedule for a teacher by UUID, NIP, or Kode GTK.
     */
    public function perGuru(string $identifier): JsonResponse
    {
        $gtk = Gtk::where('id', $identifier)
            ->orWhere('nip', $identifier)
            ->orWhere('kode_gtk', $identifier)
            ->first();

        if (!$gtk) {
            return $this->errorResponse('Data guru/GTK tidak ditemukan.', 404);
        }

        $activeTp = TahunPelajaran::where('is_active', true)->first();

        $query = JadwalPelajaran::with([
            'kelas:id,nama,tingkat',
            'mataPelajaran:id,nama,kode_mapel',
        ])
        ->where('gtk_id', $gtk->id)
        ->where('is_active', true);

        if ($activeTp) {
            $query->where('tahun_pelajaran_id', $activeTp->id);
            if ($activeTp->semester_aktif) {
                $query->where('semester', (int) $activeTp->semester_aktif);
            }
        }

        $jadwal = $query->orderByRaw("FIELD(hari, 'senin', 'selasa', 'rabu', 'kamis', 'jumat', 'sabtu')")
            ->orderBy('jam_ke', 'asc')
            ->get();

        $grouped = $jadwal->groupBy('hari');

        $kode = $gtk->kode_gtk ? "[{$gtk->kode_gtk}] " : '';
        $waLines = [];
        $waLines[] = "👨‍🏫 *JADWAL MENGAJAR: {$kode}{$gtk->nama_lengkap}*";
        if ($activeTp) {
            $waLines[] = "🏫 TP: {$activeTp->nama} (Semester {$activeTp->semester_aktif})";
        }
        $waLines[] = "--------------------------------";

        if ($jadwal->isEmpty()) {
            $waLines[] = "❌ _Tidak ada jadwal mengajar aktif untuk guru ini._";
        } else {
            foreach (['senin', 'selasa', 'rabu', 'kamis', 'jumat', 'sabtu'] as $h) {
                if ($grouped->has($h)) {
                    $waLines[] = "\n📌 *" . strtoupper($h) . "*";
                    foreach ($grouped[$h] as $item) {
                        $mapel = $item->mataPelajaran?->nama ?? '-';
                        $kls = $item->kelas?->nama ?? '-';
                        $jam = $item->jam_mulai ? "({$item->jam_mulai}-{$item->jam_selesai})" : "Jam {$item->jam_ke}";
                        $waLines[] = "• {$jam}: *{$kls}* - {$mapel}";
                    }
                }
            }
        }
        $waLines[] = "--------------------------------";
        $waLines[] = "🤖 _SIMANSA Helper Bot_";

        return $this->successResponse([
            'gtk' => $gtk->only(['id', 'nama_lengkap', 'nip', 'kode_gtk']),
            'total_jam' => $jadwal->count(),
            'jadwal_per_hari' => $grouped,
        ], 'Jadwal guru berhasil dimuat.', 200, [], implode("\n", $waLines));
    }
}
