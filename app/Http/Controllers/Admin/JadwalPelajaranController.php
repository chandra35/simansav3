<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\JadwalPelajaran;
use App\Models\AbsensiSiswaSession;
use App\Models\AppSetting;
use App\Models\JadwalJamConfig;
use App\Models\JadwalHariJam;
use App\Models\JadwalGuruAlias;
use App\Models\JadwalMapelAlias;
use App\Models\Kelas;
use App\Models\MataPelajaran;
use App\Models\Gtk;
use App\Models\TahunPelajaran;
use App\Services\JadwalWakakurImportService;
use App\Services\JadwalAliasMappingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class JadwalPelajaranController extends Controller
{
    public function importForm(Request $request)
    {
        $this->authorize('manage-jadwal-pelajaran');

        $tahunList = TahunPelajaran::orderByDesc('tahun_mulai')->get();
        $tahunAktif = TahunPelajaran::where('is_active', true)->first();

        return view('admin.jadwal-pelajaran.import', [
            'tahunList' => $tahunList,
            'tahunId' => $request->old('tahun_pelajaran_id', $tahunAktif?->id),
            'semester' => (int) $request->old('semester', 1),
            'preview' => null,
        ]);
    }

    public function previewWakakurImport(
        Request $request,
        JadwalWakakurImportService $importer,
        JadwalAliasMappingService $mappingService
    )
    {
        $this->authorize('manage-jadwal-pelajaran');

        $data = $request->validate([
            'tahun_pelajaran_id' => ['required', 'exists:tahun_pelajaran,id'],
            'semester' => ['required', 'integer', 'in:1,2'],
            'file' => ['required', 'file', 'mimes:xls,xlsx', 'max:10240'],
        ]);
        $tahun = TahunPelajaran::findOrFail($data['tahun_pelajaran_id']);

        try {
            $parsed = $importer->preview($request->file('file')->getRealPath());
        } catch (\Throwable $exception) {
            return back()->withInput()->with('error', 'Template tidak dapat dibaca: '.$exception->getMessage());
        }

        $gtkByCode = Gtk::query()->whereIn('kode_gtk', array_keys($parsed['gtk_references']))
            ->get(['id', 'kode_gtk'])
            ->keyBy('kode_gtk');
        $gtkAliases = JadwalGuruAlias::query()
            ->where('tahun_pelajaran_id', $tahun->id)
            ->where('source', 'jadwal_excel')
            ->where('status', 'verified')
            ->whereNotNull('gtk_id')
            ->pluck('gtk_id', 'external_code')->all();
        $gtkByExactName = Gtk::query()
            ->get(['id', 'nama_lengkap'])
            ->groupBy(fn (Gtk $gtk) => $mappingService->normalizePersonName($gtk->nama_lengkap))
            ->map(fn ($matches) => $matches->count() === 1 ? $matches->first()->id : null)
            ->filter()
            ->all();
        $mapelByCode = MataPelajaran::query()
            ->where('kurikulum_id', $tahun->kurikulum_id)
            ->whereIn('kode_jadwal', array_keys($parsed['mapel_references']))
            ->pluck('id', 'kode_jadwal')->all();
        $mapelAliases = JadwalMapelAlias::query()
            ->where('tahun_pelajaran_id', $tahun->id)
            ->where('source', 'jadwal_excel')
            ->where('status', 'verified')
            ->whereNotNull('mata_pelajaran_id')
            ->pluck('mata_pelajaran_id', 'external_code')->all();
        $classes = Kelas::query()->where('tahun_pelajaran_id', $tahun->id)->get(['id', 'nama_kelas'])
            ->keyBy(fn (Kelas $kelas) => $importer->classKey($kelas->nama_kelas));

        $seenSlots = [];
        $hariKerja = $tahun->hariKerja();
        $rows = collect($parsed['slots'])->map(function (array $slot) use ($classes, $gtkByCode, $gtkAliases, $gtkByExactName, $mapelByCode, $mapelAliases, $mappingService, $hariKerja, &$seenSlots) {
            $errors = [];
            $kelas = $classes->get($slot['kelas_key']);
            $gtkDariKode = $gtkByCode[$slot['kode_gtk']]?->id;
            $gtkDariNama = $slot['gtk_excel']
                ? ($gtkByExactName[$mappingService->normalizePersonName($slot['gtk_excel'])] ?? null)
                : null;
            $gtkId = $gtkDariNama ?? $gtkDariKode ?? $gtkAliases[$slot['kode_gtk']] ?? null;
            $mapelId = $mapelByCode[$slot['kode_mapel']] ?? $mapelAliases[$slot['kode_mapel']] ?? null;
            $slotKey = implode('|', [$slot['kelas_key'], $slot['hari'], $slot['jam_ke']]);

            if ($gtkDariNama && $gtkDariKode && $gtkDariNama !== $gtkDariKode) {
                $errors[] = "Kode GTK {$slot['kode_gtk']} tidak sesuai dengan nama guru pada file Wakakur.";
            }

            if (! $kelas) {
                $errors[] = 'Kelas SIMANSA tidak ditemukan.';
            }
            if (! $gtkId) {
                $errors[] = 'Kode GTK belum terhubung.';
            }
            if (! $mapelId) {
                $errors[] = 'Kode mapel belum terhubung.';
            }
            if (! in_array($slot['hari'], $hariKerja, true)) {
                $errors[] = 'Hari '.ucfirst($slot['hari']).' tidak aktif pada tahun pelajaran ini.';
            }
            if (isset($seenSlots[$slotKey])) {
                $errors[] = 'Slot kelas muncul lebih dari sekali pada file.';
            }
            $seenSlots[$slotKey] = true;

            return array_merge($slot, [
                'kelas_id' => $kelas?->id,
                'gtk_id' => $gtkId,
                'mapel_id' => $mapelId,
                'errors' => $errors,
                'ready' => $errors === [],
            ]);
        })->values()->all();

        // Excel hanya membawa nomor jam. Waktu mulai/selesai wajib berasal
        // dari konfigurasi slot per hari di SIMANSA agar timetable lengkap.
        $requiredTimeSlots = collect($rows)
            ->map(fn (array $row) => $row['hari'].'|'.$row['jam_ke'])
            ->unique();
        $configuredTimeSlots = JadwalHariJam::query()
            ->where('tahun_pelajaran_id', $tahun->id)
            ->where('semester', $data['semester'])
            ->where('tipe', 'pelajaran')
            ->whereNotNull('waktu_mulai')
            ->whereNotNull('waktu_selesai')
            ->get(['hari', 'jam_ke'])
            ->mapWithKeys(fn (JadwalHariJam $slot) => [$slot->hari.'|'.$slot->jam_ke => true]);
        $missingTimeSlots = $requiredTimeSlots
            ->reject(fn (string $slot) => $configuredTimeSlots->has($slot))
            ->values()
            ->all();

        $token = (string) Str::uuid();
        $payload = [
            'tahun_pelajaran_id' => $tahun->id,
            'semester' => (int) $data['semester'],
            'file_name' => $request->file('file')->getClientOriginalName(),
            'rows' => $rows,
            'day_max_jam' => $parsed['dayMaxJam'],
            'warnings' => $parsed['warnings'],
            'ignored' => $parsed['ignored'],
        ];
        session()->put('jadwal_wakakur_preview.'.$token, $payload);

        $preview = array_merge($payload, [
            'token' => $token,
            'tahun' => $tahun,
            'existing_count' => JadwalPelajaran::query()
                ->where('tahun_pelajaran_id', $tahun->id)
                ->where('semester', $data['semester'])
                ->where('is_active', true)
                ->count(),
            'attendance_count' => AbsensiSiswaSession::query()
                ->whereIn('jadwal_pelajaran_id', JadwalPelajaran::query()
                    ->where('tahun_pelajaran_id', $tahun->id)
                    ->where('semester', $data['semester'])
                    ->where('is_active', true)
                    ->select('id'))
                ->count(),
            'ready_count' => collect($rows)->where('ready', true)->count(),
            'error_count' => collect($rows)->where('ready', false)->count(),
            'missing_time_slots' => $missingTimeSlots,
        ]);

        return view('admin.jadwal-pelajaran.import', [
            'tahunList' => TahunPelajaran::orderByDesc('tahun_mulai')->get(),
            'tahunId' => $tahun->id,
            'semester' => (int) $data['semester'],
            'preview' => $preview,
        ]);
    }

    public function importWakakur(Request $request)
    {
        $this->authorize('manage-jadwal-pelajaran');
        $data = $request->validate([
            'token' => ['required', 'uuid'],
            'confirm_replace' => ['accepted'],
        ]);
        $preview = session('jadwal_wakakur_preview.'.$data['token']);
        if (! $preview) {
            return redirect()->route('admin.jadwal-pelajaran.import')
                ->with('error', 'Preview impor sudah habis. Upload file kembali.');
        }
        if (collect($preview['rows'])->contains(fn (array $row) => ! $row['ready'])) {
            return back()->with('error', 'Perbaiki mapping yang belum cocok sebelum mengimpor jadwal.');
        }
        if (! empty($preview['missing_time_slots'])) {
            return back()->with('error', 'Lengkapi konfigurasi slot jam pelajaran sebelum mengimpor jadwal.');
        }

        $tahunId = $preview['tahun_pelajaran_id'];
        $semester = $preview['semester'];
        $imported = 0;
        $overwritten = JadwalPelajaran::query()
            ->where('tahun_pelajaran_id', $tahunId)
            ->where('semester', $semester)
            ->where('is_active', true)
            ->count();
        $attendanceLinked = AbsensiSiswaSession::query()
            ->whereIn('jadwal_pelajaran_id', JadwalPelajaran::query()
                ->where('tahun_pelajaran_id', $tahunId)
                ->where('semester', $semester)
                ->where('is_active', true)
                ->select('id'))
            ->exists();
        if ($attendanceLinked) {
            return back()->with('error', 'Jadwal tidak dapat ditimpa karena sudah dipakai sesi absensi siswa.');
        }

        DB::transaction(function () use ($preview, $tahunId, $semester, &$imported) {
            JadwalPelajaran::query()
                ->where('tahun_pelajaran_id', $tahunId)
                ->where('semester', $semester)
                ->delete();

            // Wakakur menentukan jumlah jam setiap hari. Baris Excel yang kosong
            // tidak menjadi jadwal, tetapi slotnya tetap tersedia untuk diisi manual.
            foreach ($preview['day_max_jam'] ?? [] as $hari => $jamTerakhir) {
                $slotTerakhir = JadwalHariJam::query()
                    ->where('tahun_pelajaran_id', $tahunId)
                    ->where('semester', $semester)
                    ->where('hari', $hari)
                    ->where('jam_ke', $jamTerakhir)
                    ->first();

                if ($slotTerakhir) {
                    JadwalHariJam::query()
                        ->where('tahun_pelajaran_id', $tahunId)
                        ->where('semester', $semester)
                        ->where('hari', $hari)
                        ->where('urutan', '>', $slotTerakhir->urutan)
                        ->delete();
                }
            }

            $existing = JadwalPelajaran::withTrashed()
                ->where('tahun_pelajaran_id', $tahunId)
                ->where('semester', $semester)
                ->get()
                ->keyBy(fn (JadwalPelajaran $jadwal) => implode('|', [$jadwal->kelas_id, $jadwal->hari, $jadwal->jam_ke]));
            $jam = JadwalHariJam::query()
                ->where('tahun_pelajaran_id', $tahunId)
                ->where('semester', $semester)
                ->get()
                ->keyBy(fn (JadwalHariJam $slot) => $slot->hari.'|'.$slot->jam_ke);

            foreach ($preview['rows'] as $row) {
                $key = implode('|', [$row['kelas_id'], $row['hari'], $row['jam_ke']]);
                $record = $existing->get($key) ?? new JadwalPelajaran();
                if ($record->exists && $record->trashed()) {
                    $record->restore();
                }
                $time = $jam->get($row['hari'].'|'.$row['jam_ke']);
                $record->fill([
                    'tahun_pelajaran_id' => $tahunId,
                    'kelas_id' => $row['kelas_id'],
                    'mapel_id' => $row['mapel_id'],
                    'gtk_id' => $row['gtk_id'],
                    'hari' => $row['hari'],
                    'jam_ke' => $row['jam_ke'],
                    'jam_mulai' => $time?->waktu_mulai,
                    'jam_selesai' => $time?->waktu_selesai,
                    'semester' => $semester,
                    'is_active' => true,
                    'created_by' => auth()->id(),
                ])->save();
                $imported++;
            }
        });

        activity('jadwal-pelajaran')
            ->causedBy($request->user())
            ->withProperties([
                'tahun_pelajaran_id' => $tahunId,
                'semester' => $semester,
                'file_name' => $preview['file_name'],
                'imported' => $imported,
                'overwritten' => $overwritten,
            ])
            ->log('Mengimpor jadwal Wakakur dan menimpa jadwal semester');
        session()->forget('jadwal_wakakur_preview.'.$data['token']);

        return redirect()->route('admin.jadwal-pelajaran.index', ['tahun_pelajaran_id' => $tahunId])
            ->with('success', "Impor selesai: {$imported} slot tersimpan dan {$overwritten} slot sebelumnya ditimpa.");
    }

    public function index(Request $request)
    {
        $this->authorize('view-jadwal-pelajaran');

        $tahunList   = TahunPelajaran::orderBy('tahun_mulai', 'desc')->get();
        $tahunAktif  = TahunPelajaran::where('is_active', true)->first();
        $tahunId     = $request->tahun_pelajaran_id ?? $tahunAktif?->id;

        $kelasList = $tahunId
            ? Kelas::where('tahun_pelajaran_id', $tahunId)
                ->with(['jurusan', 'waliKelas', 'ketuaKelasRecord.siswa'])
                ->orderBy('tingkat')
                ->orderBy('nama_kelas')
                ->get()
            : collect();

        // Stats for UI
        $stats = ['kelas_ids_with_jadwal' => [], 'total_slots' => 0, 'jam_count' => 0];
        if ($tahunId && $kelasList->isNotEmpty()) {
            $jadwalStats = JadwalPelajaran::where('tahun_pelajaran_id', $tahunId)
                ->where('is_active', true)
                ->select('kelas_id', DB::raw('count(*) as slot_count'))
                ->groupBy('kelas_id')
                ->get();
            $stats['kelas_ids_with_jadwal'] = $jadwalStats->pluck('kelas_id')->toArray();
            $stats['total_slots'] = $jadwalStats->sum('slot_count');
        }
        $stats['kelas_with_jadwal'] = count($stats['kelas_ids_with_jadwal']);

        return view('admin.jadwal-pelajaran.index', compact(
            'tahunList', 'tahunAktif', 'tahunId', 'kelasList', 'stats'
        ));
    }

    public function timetable(Request $request)
    {
        $this->authorize('view-jadwal-pelajaran');

        $tahunList   = TahunPelajaran::orderBy('tahun_mulai', 'desc')->get();
        $tahunAktif  = TahunPelajaran::where('is_active', true)->first();
        $tahunId     = $request->tahun_pelajaran_id ?? $tahunAktif?->id;
        $kelasId     = $request->kelas_id;
        $semester    = (int) ($request->semester ?? 1);

        $kelasList = $tahunId
            ? Kelas::where('tahun_pelajaran_id', $tahunId)
                ->with('jurusan')
                ->orderBy('tingkat')
                ->orderBy('nama_kelas')
                ->get()
            : collect();

        $kelasObj = $kelasId ? Kelas::with(['jurusan', 'tahunPelajaran'])->find($kelasId) : null;

        // Ambil slot jam per hari dari jadwal_hari_jam
        $hariJamMap = [];
        if ($tahunId) {
            $allSlots = JadwalHariJam::where('tahun_pelajaran_id', $tahunId)
                ->where('semester', $semester)
                ->orderBy('hari')
                ->orderBy('urutan')
                ->get();
            foreach ($allSlots as $slot) {
                $hariJamMap[$slot->hari][] = $slot;
            }
        }

        $jadwalRaw = ($kelasId && $tahunId)
            ? JadwalPelajaran::with(['mataPelajaran', 'gtk'])
                ->where('kelas_id', $kelasId)
                ->where('tahun_pelajaran_id', $tahunId)
                ->where('semester', $semester)
                ->where('is_active', true)
                ->get()
            : collect();

        $jadwalMap = [];
        foreach ($jadwalRaw as $j) {
            $jadwalMap[$j->hari][$j->jam_ke] = $j;
        }

        $hariList = $tahunAktif?->hariKerja() ?? array_keys(JadwalPelajaran::HARI);

        return view('admin.jadwal-pelajaran.timetable', compact(
            'tahunList', 'tahunAktif', 'tahunId', 'kelasList', 'kelasObj',
            'kelasId', 'semester', 'hariJamMap',
            'jadwalMap', 'hariList'
        ));
    }

    /** Layar monitor real-time untuk jadwal belajar mengajar hari ini. */
    public function monitor()
    {
        $this->authorize('view-jadwal-pelajaran');

        return view('admin.jadwal-pelajaran.monitor', $this->dataMonitor());
    }

    /** Layar monitor tanpa login untuk TV/layar guru piket. */
    public function publicMonitor()
    {
        return view('public.jadwal-monitor', $this->dataMonitor());
    }

    /** Data bersama untuk monitor admin dan layar publik. */
    private function dataMonitor(): array
    {
        $tahun = TahunPelajaran::where('is_active', true)->first();
        $now = now('Asia/Jakarta');
        $hari = [1 => 'senin', 2 => 'selasa', 3 => 'rabu', 4 => 'kamis', 5 => 'jumat', 6 => 'sabtu'][$now->dayOfWeekIso] ?? null;
        $semester = $tahun?->semester_aktif === 'Genap' ? 2 : 1;

        $slotRows = ($tahun && $hari && $tahun->isHariKerja($hari))
            ? JadwalHariJam::query()
                ->where('tahun_pelajaran_id', $tahun->id)
                ->where('semester', $semester)
                ->where('hari', $hari)
                ->whereNotNull('waktu_mulai')
                ->whereNotNull('waktu_selesai')
                ->orderBy('urutan')
                ->get()
            : collect();
        $jadwalByJam = ($tahun && $slotRows->isNotEmpty())
            ? JadwalPelajaran::query()
                ->with(['kelas:id,nama_kelas', 'mataPelajaran:id,nama_mapel', 'gtk:id,nama_lengkap,foto_profile'])
                ->where('tahun_pelajaran_id', $tahun->id)
                ->where('semester', $semester)
                ->where('hari', $hari)
                ->where('is_active', true)
                ->whereIn('jam_ke', $slotRows->pluck('jam_ke'))
                ->get()
                ->groupBy('jam_ke')
            : collect();
        $slots = $slotRows->map(function (JadwalHariJam $slot) use ($jadwalByJam) {
            return [
                'jam_ke' => $slot->jam_ke,
                'mulai' => substr($slot->waktu_mulai, 0, 5),
                'selesai' => substr($slot->waktu_selesai, 0, 5),
                'tipe' => $slot->tipe,
                'label' => $slot->displayLabel(),
                'kelas' => $slot->isPelajaran() ? ($jadwalByJam->get($slot->jam_ke) ?? collect())->map(fn (JadwalPelajaran $jadwal) => [
                    'kelas' => $jadwal->kelas?->nama_kelas ?? '-',
                    'mapel' => $jadwal->mataPelajaran?->nama_mapel ?? '-',
                    'guru' => $jadwal->gtk?->nama_lengkap ?? '-',
                    'foto_guru' => $jadwal->gtk?->foto_profile_url,
                ])->values() : collect(),
            ];
        })->values();

        $setting = AppSetting::query()->first();
        $schoolLogoUrl = $setting?->logo_sekolah_url ?? asset('vendor/adminlte/dist/img/logo-sekolah.png');

        return compact('tahun', 'semester', 'hari', 'slots', 'schoolLogoUrl');
    }

    public function timetableData(Request $request)
    {
        $request->validate([
            'kelas_id'            => 'required|exists:kelas,id',
            'tahun_pelajaran_id'  => 'required|exists:tahun_pelajaran,id',
            'semester'            => 'required|integer|in:1,2',
        ]);

        $jadwal = JadwalPelajaran::with(['mataPelajaran', 'gtk'])
            ->where('kelas_id', $request->kelas_id)
            ->where('tahun_pelajaran_id', $request->tahun_pelajaran_id)
            ->where('semester', $request->semester)
            ->where('is_active', true)
            ->get()
            ->map(function ($j) {
                return [
                    'id'          => $j->id,
                    'hari'        => $j->hari,
                    'jam_ke'      => $j->jam_ke,
                    'mapel_id'    => $j->mapel_id,
                    'mapel_nama'  => $j->mataPelajaran?->nama_mapel ?? '-',
                    'mapel_kode'  => $j->mataPelajaran?->kode_tampil_jadwal ?? '',
                    'gtk_id'      => $j->gtk_id,
                    'gtk_nama'    => $j->gtk?->nama_lengkap ?? '-',
                    'gtk_kode'    => $j->gtk?->kode_gtk ?? '',
                    'ruangan'     => $j->ruangan ?? '',
                    'catatan'     => $j->catatan ?? '',
                ];
            });

        return response()->json(['success' => true, 'data' => $jadwal]);
    }

    public function guruOptions(Request $request)
    {
        $tahunId  = $request->tahun_pelajaran_id;
        $hari     = $request->hari;
        $jamKe    = $request->jam_ke;
        $semester = $request->semester ?? 1;
        $excludeId = $request->exclude_id;

        $konflikQuery = JadwalPelajaran::where('tahun_pelajaran_id', $tahunId)
            ->where('hari', $hari)
            ->where('jam_ke', $jamKe)
            ->where('semester', $semester)
            ->where('is_active', true);
        if ($excludeId) {
            $konflikQuery->where('id', '!=', $excludeId);
        }
        $konflikGtkIds = $konflikQuery->pluck('gtk_id')->toArray();

        // Hitung JTM per guru untuk semester ini (lintas semua kelas)
        $jtmCounts = JadwalPelajaran::where('tahun_pelajaran_id', $tahunId)
            ->where('semester', $semester)
            ->where('is_active', true)
            ->select('gtk_id', DB::raw('count(*) as jtm'))
            ->groupBy('gtk_id')
            ->pluck('jtm', 'gtk_id')
            ->toArray();

        $gtks = Gtk::orderBy('nama_lengkap')
            ->where('jenis_ptk', 'like', '%Guru%')
            ->get(['id', 'nama_lengkap', 'nip', 'kode_gtk'])
            ->map(function ($g) use ($konflikGtkIds, $jtmCounts) {
                $jtm = $jtmCounts[$g->id] ?? 0;
                return [
                    'id'      => $g->id,
                    'nama'    => $g->nama_lengkap,
                    'nip'     => $g->nip ?? '',
                    'kode'    => $g->kode_gtk ?? '',
                    'konflik' => in_array($g->id, $konflikGtkIds),
                    'jtm'     => $jtm,
                    'jtm_status' => $jtm < 24 ? 'kurang' : ($jtm > 40 ? 'lebih' : 'normal'),
                ];
            });

        return response()->json(['success' => true, 'data' => $gtks]);
    }

    public function mapelOptions(Request $request)
    {
        $tahunId = $request->tahun_pelajaran_id;
        $kelasId = $request->kelas_id;

        $tingkat = null;
        $kelas = null;
        if ($kelasId) {
            $kelas = Kelas::find($kelasId);
            $tingkat = $kelas?->tingkat;
        }

        $query = MataPelajaran::where('is_active', true)
            ->where('is_schedulable', true)
            ->orderBy('nama_mapel');

        if ($kelas?->kurikulum_id) {
            $query->where('kurikulum_id', $kelas->kurikulum_id);
        }

        if ($tingkat) {
            $faseColumn = (int) $tingkat === 10 ? 'struktur_fase_e' : 'struktur_fase_f';
            $query->whereJsonContains('tingkat', (int) $tingkat)
                ->whereNotNull($faseColumn);
        }

        $mapels = $query->get([
            'id',
            'kode_mapel',
            'nama_mapel',
            'kelompok',
            'struktur_fase_e',
            'struktur_fase_f',
            'alokasi_jp',
            'jam_pelajaran',
        ])->map(function ($m) use ($tingkat) {
            $structure = $tingkat
                ? $m->strukturUntukTingkat((int) $tingkat)
                : ($m->kelompok ?? '');

            return [
                'id'       => $m->id,
                'nama'     => $m->nama_mapel,
                'kode'     => $m->kode_mapel ?? '',
                'kelompok' => match ($structure) {
                    'wajib_umum' => (int) $tingkat === 10 ? 'Wajib · Fase E' : 'Umum · Fase F',
                    'pilihan' => 'Pilihan',
                    'muatan_lokal' => 'Muatan Lokal',
                    'penguatan_program' => 'Penguatan Program',
                    default => $structure ?: 'Lainnya',
                },
                'jp_target' => $tingkat ? $m->jpUntukTingkat((int) $tingkat) : $m->jam_pelajaran,
            ];
        });

        return response()->json(['success' => true, 'data' => $mapels]);
    }

    /**
     * GET /admin/jadwal-pelajaran/guru-mapel-in-kelas
     * Kembalikan mapel yang diajarkan guru tertentu di kelas tertentu
     * (berdasarkan jadwal yang sudah ada). Untuk auto-fill mapel saat pilih guru.
     */
    public function guruMapelInKelas(Request $request)
    {
        $this->authorize('view-jadwal-pelajaran');

        $request->validate([
            'gtk_id'             => 'required|exists:gtks,id',
            'kelas_id'           => 'required|exists:kelas,id',
            'tahun_pelajaran_id' => 'required|exists:tahun_pelajaran,id',
            'semester'           => 'nullable|integer|in:1,2',
        ]);

        $jadwal = JadwalPelajaran::with('mataPelajaran')
            ->where('tahun_pelajaran_id', $request->tahun_pelajaran_id)
            ->where('kelas_id', $request->kelas_id)
            ->where('gtk_id', $request->gtk_id)
            ->where('is_active', true)
            ->when($request->semester, fn($q) => $q->where('semester', $request->semester))
            ->first();

        if (!$jadwal || !$jadwal->mataPelajaran) {
            return response()->json(['success' => true, 'data' => null]);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'mapel_id'   => $jadwal->mapel_id,
                'mapel_nama' => $jadwal->mataPelajaran->nama_mapel,
                'mapel_kode' => $jadwal->mataPelajaran->kode_tampil_jadwal ?? '',
            ],
        ]);
    }

    public function store(Request $request)
    {
        $this->authorize('manage-jadwal-pelajaran');

        $validated = $request->validate([
            'tahun_pelajaran_id' => 'required|exists:tahun_pelajaran,id',
            'kelas_id'           => 'required|exists:kelas,id',
            'mapel_id'           => 'required|exists:mata_pelajaran,id',
            'gtk_id'             => 'required|exists:gtks,id',
            'hari'               => 'required|in:senin,selasa,rabu,kamis,jumat,sabtu',
            'jam_ke'             => 'required|integer|min:1|max:15',
            'ruangan'            => 'nullable|string|max:50',
            'semester'           => 'required|integer|in:1,2',
            'catatan'            => 'nullable|string|max:255',
        ]);

        $conflictKelas = JadwalPelajaran::where('tahun_pelajaran_id', $validated['tahun_pelajaran_id'])
            ->where('kelas_id', $validated['kelas_id'])
            ->where('hari', $validated['hari'])
            ->where('jam_ke', $validated['jam_ke'])
            ->where('semester', $validated['semester'])
            ->where('is_active', true)
            ->exists();

        if ($conflictKelas) {
            return response()->json(['success' => false, 'message' => 'Kelas sudah memiliki jadwal di jam ini.'], 422);
        }

        $conflictGuru = JadwalPelajaran::where('tahun_pelajaran_id', $validated['tahun_pelajaran_id'])
            ->where('gtk_id', $validated['gtk_id'])
            ->where('hari', $validated['hari'])
            ->where('jam_ke', $validated['jam_ke'])
            ->where('semester', $validated['semester'])
            ->where('is_active', true)
            ->first();

        if ($conflictGuru) {
            $kelasLain = $conflictGuru->kelas?->nama_kelas ?? 'kelas lain';
            return response()->json([
                'success'       => false,
                'message'       => "Guru sudah mengajar di {$kelasLain} pada jam ini.",
                'conflict_type' => 'guru',
            ], 422);
        }

        // Ambil waktu dari jadwal_hari_jam jika ada
        $hariJam = JadwalHariJam::where('tahun_pelajaran_id', $validated['tahun_pelajaran_id'])
            ->where('semester', $validated['semester'])
            ->where('hari', $validated['hari'])
            ->where('jam_ke', $validated['jam_ke'])
            ->first();

        // Cari termasuk soft-deleted — updateOrCreate tidak cukup karena SoftDeletes
        $existing = JadwalPelajaran::withTrashed()
            ->where('tahun_pelajaran_id', $validated['tahun_pelajaran_id'])
            ->where('kelas_id', $validated['kelas_id'])
            ->where('hari', $validated['hari'])
            ->where('jam_ke', $validated['jam_ke'])
            ->where('semester', $validated['semester'])
            ->first();

        $fillData = array_merge($validated, [
            'jam_mulai'   => $hariJam?->waktu_mulai,
            'jam_selesai' => $hariJam?->waktu_selesai,
            'is_active'   => true,
            'created_by'  => auth()->id(),
        ]);

        if ($existing) {
            // Selalu reuse record lama (restore jika trashed) karena unique constraint
            // MySQL mencakup soft-deleted rows — INSERT baru akan selalu conflict
            if ($existing->trashed()) {
                $existing->restore();
            }
            $existing->fill($fillData)->save();
            $jadwal = $existing;
        } else {
            $jadwal = JadwalPelajaran::create($fillData);
        }

        $jadwal->load(['mataPelajaran', 'gtk']);

        return response()->json([
            'success' => true,
            'message' => 'Jadwal berhasil ditambahkan.',
            'data' => [
                'id'         => $jadwal->id,
                'mapel_nama' => $jadwal->mataPelajaran?->nama_mapel ?? '-',
                'mapel_kode' => $jadwal->mataPelajaran?->kode_tampil_jadwal ?? '',
                'gtk_nama'   => $jadwal->gtk?->nama_lengkap ?? '-',
                'gtk_kode'   => $jadwal->gtk?->kode_gtk ?? '',
                'ruangan'    => $jadwal->ruangan ?? '',
            ],
        ]);
    }

    public function show(JadwalPelajaran $jadwalPelajaran)
    {
        $this->authorize('view-jadwal-pelajaran');

        $jadwalPelajaran->load(['mataPelajaran', 'gtk', 'kelas', 'tahunPelajaran']);

        return response()->json([
            'success' => true,
            'data' => [
                'id'                  => $jadwalPelajaran->id,
                'tahun_pelajaran_id'  => $jadwalPelajaran->tahun_pelajaran_id,
                'kelas_id'            => $jadwalPelajaran->kelas_id,
                'mapel_id'            => $jadwalPelajaran->mapel_id,
                'mapel_nama'          => $jadwalPelajaran->mataPelajaran?->nama_mapel,
                'gtk_id'              => $jadwalPelajaran->gtk_id,
                'gtk_nama'            => $jadwalPelajaran->gtk?->nama_lengkap,
                'hari'                => $jadwalPelajaran->hari,
                'jam_ke'              => $jadwalPelajaran->jam_ke,
                'ruangan'             => $jadwalPelajaran->ruangan,
                'semester'            => $jadwalPelajaran->semester,
                'catatan'             => $jadwalPelajaran->catatan,
            ],
        ]);
    }

    public function update(Request $request, JadwalPelajaran $jadwalPelajaran)
    {
        $this->authorize('manage-jadwal-pelajaran');

        $validated = $request->validate([
            'mapel_id'  => 'required|exists:mata_pelajaran,id',
            'gtk_id'    => 'required|exists:gtks,id',
            'ruangan'   => 'nullable|string|max:50',
            'catatan'   => 'nullable|string|max:255',
        ]);

        $conflictGuru = JadwalPelajaran::where('tahun_pelajaran_id', $jadwalPelajaran->tahun_pelajaran_id)
            ->where('gtk_id', $validated['gtk_id'])
            ->where('hari', $jadwalPelajaran->hari)
            ->where('jam_ke', $jadwalPelajaran->jam_ke)
            ->where('semester', $jadwalPelajaran->semester)
            ->where('is_active', true)
            ->where('id', '!=', $jadwalPelajaran->id)
            ->first();

        if ($conflictGuru) {
            $kelasLain = $conflictGuru->kelas?->nama_kelas ?? 'kelas lain';
            return response()->json([
                'success' => false,
                'message' => "Guru sudah mengajar di {$kelasLain} pada jam ini.",
            ], 422);
        }

        $jadwalPelajaran->update($validated);
        $jadwalPelajaran->load(['mataPelajaran', 'gtk']);

        return response()->json([
            'success' => true,
            'message' => 'Jadwal berhasil diperbarui.',
            'data' => [
                'id'         => $jadwalPelajaran->id,
                'mapel_nama' => $jadwalPelajaran->mataPelajaran?->nama_mapel ?? '-',
                'mapel_kode' => $jadwalPelajaran->mataPelajaran?->kode_tampil_jadwal ?? '',
                'gtk_nama'   => $jadwalPelajaran->gtk?->nama_lengkap ?? '-',
                'gtk_kode'   => $jadwalPelajaran->gtk?->kode_gtk ?? '',
                'ruangan'    => $jadwalPelajaran->ruangan ?? '',
            ],
        ]);
    }

    public function destroy(JadwalPelajaran $jadwalPelajaran)
    {
        $this->authorize('manage-jadwal-pelajaran');
        $jadwalPelajaran->delete();
        return response()->json(['success' => true, 'message' => 'Jadwal berhasil dihapus.']);
    }

    /**
     * GET /admin/jadwal-pelajaran/guru-jtm-summary
     * Rekap JTM per guru (Jam Tatap Muka) untuk tahun pelajaran + semester.
     * JTM untuk MA/MAN: 1 slot jadwal = 1 JTM (45 menit).
     * Min sertifikasi: 24 JTM/minggu, Maks: 40 JTM/minggu.
     * Tugas tambahan dihitung ekuivalensi: Wali Kelas +6, Wakasek +12, Ka.Lab/Perpus +12.
     */
    public function guruJtmSummary(Request $request)
    {
        $this->authorize('view-jadwal-pelajaran');

        $request->validate([
            'tahun_pelajaran_id' => 'required|exists:tahun_pelajaran,id',
            'semester'           => 'required|integer|in:1,2',
        ]);

        $tahunId  = $request->tahun_pelajaran_id;
        $semester = (int) $request->semester;

        // JTM mengajar per guru (lintas semua kelas)
        $jtmRows = JadwalPelajaran::where('tahun_pelajaran_id', $tahunId)
            ->where('semester', $semester)
            ->where('is_active', true)
            ->select('gtk_id', DB::raw('count(*) as jtm_mengajar'))
            ->groupBy('gtk_id')
            ->get()
            ->keyBy('gtk_id');

        if ($jtmRows->isEmpty()) {
            return response()->json(['success' => true, 'data' => []]);
        }

        $gtkIds = $jtmRows->keys()->toArray();
        $gtks   = Gtk::whereIn('id', $gtkIds)
            ->orderBy('nama_lengkap')
            ->get(['id', 'user_id', 'nama_lengkap', 'kode_gtk', 'jabatan']);

        // Guru yang menjadi wali kelas di tahun pelajaran ini
        $waliKelasUserIds = Kelas::where('tahun_pelajaran_id', $tahunId)
            ->whereNotNull('wali_kelas_id')
            ->pluck('wali_kelas_id')
            ->toArray();

        $result = $gtks->map(function ($gtk) use ($jtmRows, $waliKelasUserIds, $tahunId) {
            $jtmMengajar    = (int) ($jtmRows[$gtk->id]->jtm_mengajar ?? 0);
            $jtmEkuivalensi = 0;
            $tugasTambahan  = [];

            // Ekuivalensi Wali Kelas: +6 JTM
            if (in_array($gtk->user_id, $waliKelasUserIds)) {
                $jtmEkuivalensi += 6;
                $tugasTambahan[] = ['label' => 'Wali Kelas', 'jtm' => 6];
            }

            // Ekuivalensi dari jabatan struktural
            $jabatan = strtolower($gtk->jabatan ?? '');
            if (str_contains($jabatan, 'wakil kepala') || str_contains($jabatan, 'waka') || str_contains($jabatan, 'wakasek')) {
                $jtmEkuivalensi += 12;
                $tugasTambahan[] = ['label' => 'Wakasek', 'jtm' => 12];
            } elseif (str_contains($jabatan, 'kepala lab')) {
                $jtmEkuivalensi += 12;
                $tugasTambahan[] = ['label' => 'Ka. Laboratorium', 'jtm' => 12];
            } elseif (str_contains($jabatan, 'kepala perpus')) {
                $jtmEkuivalensi += 12;
                $tugasTambahan[] = ['label' => 'Ka. Perpustakaan', 'jtm' => 12];
            }

            $jtmTotal = $jtmMengajar + $jtmEkuivalensi;

            return [
                'gtk_id'          => $gtk->id,
                'nama'            => $gtk->nama_lengkap,
                'kode'            => $gtk->kode_gtk ?? '',
                'jabatan'         => $gtk->jabatan ?? '',
                'jtm_mengajar'    => $jtmMengajar,
                'jtm_ekuivalensi' => $jtmEkuivalensi,
                'jtm_total'       => $jtmTotal,
                'tugas_tambahan'  => $tugasTambahan,
                // Status: kurang (<24), normal (24-40), lebih (>40)
                'status'          => $jtmTotal < 24 ? 'kurang' : ($jtmTotal > 40 ? 'lebih' : 'normal'),
            ];
        })->sortByDesc('jtm_total')->values();

        return response()->json(['success' => true, 'data' => $result]);
    }

    /**
     * POST /admin/jadwal-pelajaran/clear-all
     * Hapus semua slot jadwal kelas tertentu dalam semester tertentu.
     */
    public function clearAll(Request $request)
    {
        $this->authorize('manage-jadwal-pelajaran');

        $request->validate([
            'kelas_id'           => 'required|exists:kelas,id',
            'tahun_pelajaran_id' => 'required|exists:tahun_pelajaran,id',
            'semester'           => 'required|integer|in:1,2',
        ]);

        $deleted = JadwalPelajaran::where('kelas_id', $request->kelas_id)
            ->where('tahun_pelajaran_id', $request->tahun_pelajaran_id)
            ->where('semester', $request->semester)
            ->delete();

        return response()->json([
            'success' => true,
            'message' => "{$deleted} slot jadwal berhasil dihapus.",
        ]);
    }

    public function copyJadwal(Request $request)
    {
        $this->authorize('manage-jadwal-pelajaran');

        $request->validate([
            'tahun_asal_id'   => 'required|exists:tahun_pelajaran,id',
            'tahun_tujuan_id' => 'required|exists:tahun_pelajaran,id|different:tahun_asal_id',
        ]);

        $tahunTujuan = TahunPelajaran::findOrFail($request->tahun_tujuan_id);

        $kelasTujuanMap = Kelas::where('tahun_pelajaran_id', $tahunTujuan->id)
            ->get()
            ->keyBy(fn($k) => strtolower(trim($k->nama_kelas)));

        $jadwalAsal = JadwalPelajaran::where('tahun_pelajaran_id', $request->tahun_asal_id)->get();

        $disalin = $dilewati = $no_target = 0;

        DB::transaction(function () use ($jadwalAsal, $kelasTujuanMap, $tahunTujuan, &$disalin, &$dilewati, &$no_target) {
            foreach ($jadwalAsal as $j) {
                $kelasAsal = Kelas::find($j->kelas_id);
                if (!$kelasAsal) { $no_target++; continue; }

                $kelasTujuan = $kelasTujuanMap->get(strtolower(trim($kelasAsal->nama_kelas)));
                if (!$kelasTujuan) { $no_target++; continue; }

                $exists = JadwalPelajaran::where('tahun_pelajaran_id', $tahunTujuan->id)
                    ->where('kelas_id', $kelasTujuan->id)
                    ->where('hari', $j->hari)
                    ->where('jam_ke', $j->jam_ke)
                    ->where('semester', $j->semester)
                    ->exists();

                if ($exists) { $dilewati++; continue; }

                JadwalPelajaran::create([
                    'tahun_pelajaran_id' => $tahunTujuan->id,
                    'kelas_id'           => $kelasTujuan->id,
                    'mapel_id'           => $j->mapel_id,
                    'gtk_id'             => $j->gtk_id,
                    'hari'               => $j->hari,
                    'jam_ke'             => $j->jam_ke,
                    'jam_mulai'          => $j->jam_mulai,
                    'jam_selesai'        => $j->jam_selesai,
                    'ruangan'            => $j->ruangan,
                    'semester'           => $j->semester,
                    'catatan'            => $j->catatan,
                    'is_active'          => true,
                ]);
                $disalin++;
            }
        });

        $parts = [];
        if ($disalin)   $parts[] = "{$disalin} jadwal disalin";
        if ($dilewati)  $parts[] = "{$dilewati} dilewati (sudah ada)";
        if ($no_target) $parts[] = "{$no_target} tidak ada kelas tujuan";

        return response()->json([
            'success'   => true,
            'disalin'   => $disalin,
            'dilewati'  => $dilewati,
            'no_target' => $no_target,
            'message'   => implode(', ', $parts) . '.',
        ]);
    }
}
