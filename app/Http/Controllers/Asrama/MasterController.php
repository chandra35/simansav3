<?php

namespace App\Http\Controllers\Asrama;

use App\Http\Controllers\Controller;
use App\Models\Asrama;
use App\Models\AsramaAsatidz;
use App\Models\AsramaMapel;
use App\Models\AsramaSantri;
use App\Models\Gtk;
use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\TahunPelajaran;
use App\Services\AsramaAccessService;
use App\Services\AsramaRombelSyncService;
use App\Imports\AsramaNomorIndukImport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class MasterController extends Controller
{
    public function santri(Request $request)
    {
        $tahunId = $request->input('tahun_pelajaran_id') ?: TahunPelajaran::active()->value('id');
        $search = trim((string) $request->input('q', ''));
        $tingkat = $request->input('tingkat');
        $kelasId = $request->input('kelas_id');
        $jk = $request->input('jenis_kelamin');
        $status = $request->input('status');

        $records = AsramaSantri::with([
            'siswa.kelasTahunAktif', 'kamarAktif.kamar',
            'kelasAktif.kelas.kelasReguler', 'kelasAktif.pengasuhAssignment.rombelPengasuh.pengasuh.gtk',
        ])
            ->when($search !== '', fn ($query) => $query->where(fn ($q) => $q
                ->where('nomor_induk_asrama', 'like', "%{$search}%")
                ->orWhereHas('siswa', fn ($s) => $s
                    ->where('nama_lengkap', 'like', "%{$search}%")
                    ->orWhere('nisn', 'like', "%{$search}%"))))
            ->when($tingkat, fn ($query) => $query->whereHas('siswa.kelasTahunAktif',
                fn ($s) => $s->where('siswa_kelas.tingkat', $tingkat)))
            ->when($kelasId, fn ($query) => $query->whereHas('siswa.kelasTahunAktif',
                fn ($s) => $s->where('kelas.id', $kelasId)))
            ->when($jk, fn ($query) => $query->whereHas('siswa', fn ($s) => $s->where('jenis_kelamin', $jk)))
            ->when($status, fn ($query) => $query->where('status', $status))
            ->orderByDesc('status')->latest()->paginate(50)->withQueryString();

        if ($request->ajax()) {
            return view('asrama.master._santri-table', ['records' => $records]);
        }

        // Rombel SIMANSA yang siswanya sudah terdaftar sebagai santri
        $assignedKelas = Kelas::where('is_active', true)
            ->where('tahun_pelajaran_id', $tahunId)
            ->whereHas('siswaAktif', fn ($q) => $q->whereIn('siswa.id', AsramaSantri::query()->select('siswa_id')))
            ->orderBy('tingkat')->orderBy('nama_kelas')
            ->get(['id', 'nama_kelas', 'tingkat']);

        $aktif = AsramaSantri::where('status', 'aktif');

        return view('asrama.master.santri', [
            'records' => $records,
            'students' => Siswa::where('status_siswa', 'aktif')->orderBy('nama_lengkap')
                ->get(['id', 'nama_lengkap', 'nisn', 'nis_lokal']),
            'classes' => Kelas::withCount(['siswaAktif'])->where('tahun_pelajaran_id', $tahunId)
                ->where('is_active', true)->where('is_asrama', true)->orderBy('nama_kelas')->get(),
            'years' => TahunPelajaran::orderByDesc('tahun_mulai')->get(),
            'selectedYear' => $tahunId,
            'assignedKelas' => $assignedKelas,
            'tingkatOptions' => $assignedKelas->pluck('tingkat')->filter()->unique()->sort()->values(),
            'stats' => [
                'total' => (clone $aktif)->count(),
                'laki' => (clone $aktif)->whereHas('siswa', fn ($q) => $q->where('jenis_kelamin', 'L'))->count(),
                'perempuan' => (clone $aktif)->whereHas('siswa', fn ($q) => $q->where('jenis_kelamin', 'P'))->count(),
                'berkamar' => (clone $aktif)->whereHas('kamarAktif')->count(),
            ],
        ]);
    }

    public function storeSantri(Request $request, AsramaAccessService $access, AsramaRombelSyncService $sync)
    {
        $data = $request->validate([
            'kelas_ids' => ['nullable', 'array'],
            'kelas_ids.*' => ['exists:kelas,id'],
            'siswa_ids' => ['nullable', 'array'],
            'siswa_ids.*' => ['exists:siswa,id'],
            'tanggal_masuk' => ['nullable', 'date'],
        ]);
        $ids = collect($data['siswa_ids'] ?? []);
        if (! empty($data['kelas_ids'])) {
            foreach (Kelas::whereIn('id', $data['kelas_ids'])->get() as $kelas) {
                $ids = $ids->merge($kelas->siswaAktif()->pluck('siswa.id'));
            }
        }
        $ids = $ids->unique()->values();
        abort_if($ids->isEmpty(), 422, 'Pilih rombel atau minimal satu siswa.');

        $unit = $this->singleAsrama();
        $students = Siswa::with(['user', 'kelasTahunAktif'])->whereIn('id', $ids)->get();
        DB::transaction(function () use ($students, $unit, $data, $request, $access, $sync): void {
            $mirrors = [];
            foreach ($students as $student) {
                $record = AsramaSantri::withTrashed()->where('siswa_id', $student->id)->first()
                    ?: new AsramaSantri(['asrama_id' => $unit->id, 'siswa_id' => $student->id]);
                if ($record->trashed()) {
                    $record->restore();
                }
                $record->fill([
                    'asrama_id' => $unit->id,
                    'nomor_induk_asrama' => $record->nomor_induk_asrama ?: $this->generateSantriNumber($student),
                    'tanggal_masuk' => $record->tanggal_masuk ?: ($data['tanggal_masuk'] ?? now()->toDateString()),
                    'tanggal_keluar' => null,
                    'status' => 'aktif',
                    'created_by' => $record->created_by ?: $request->user()->id,
                    'updated_by' => $request->user()->id,
                ])->save();

                // Otomatis jadi anggota mirror rombel asrama jika rombel SIMANSA-nya bertanda asrama.
                $kelasAktif = $student->kelasTahunAktif->first();
                if ($kelasAktif && $kelasAktif->is_asrama) {
                    $mirrors[$kelasAktif->id] ??= $sync->mirrorFor($kelasAktif, $unit, $request->user()->id);
                    $sync->placeSantri($mirrors[$kelasAktif->id], $record, $request->user()->id);
                }

                $access->syncStudent($student->user);
            }
        });

        return back()->with('success', $students->count().' siswa berhasil diaktifkan sebagai santri.');
    }

    public function showSantri(AsramaSantri $santri)
    {
        $santri->load([
            'siswa.user',
            'siswa.ortu.provinsi', 'siswa.ortu.kabupaten', 'siswa.ortu.kecamatan', 'siswa.ortu.kelurahan',
            'siswa.kelasTahunAktif',
            'kamarAktif.kamar', 'kelasAktif.kelas.kelasReguler',
        ]);

        return response()->json(['success' => true, 'data' => $santri]);
    }

    public function destroySantri(Request $request, AsramaSantri $santri, AsramaAccessService $access)
    {
        $nama = $santri->siswa->nama_lengkap;
        DB::transaction(function () use ($santri, $request): void {
            $tanggalKeluar = now()->toDateString();
            $santri->kelasRecords()->where('status', 'aktif')->update([
                'status' => 'keluar', 'tanggal_keluar' => $tanggalKeluar, 'is_ketua_kelas' => false,
            ]);
            $santri->kamarRecords()->where('status', 'aktif')->update([
                'status' => 'keluar', 'tanggal_keluar' => $tanggalKeluar,
            ]);
            $santri->update([
                'status' => 'nonaktif',
                'tanggal_keluar' => $santri->tanggal_keluar ?: $tanggalKeluar,
                'updated_by' => $request->user()->id,
            ]);
            $santri->delete();
        });
        $access->syncStudent($santri->siswa->user);

        return back()->with('success', "Santri {$nama} dihapus dari asrama. Data siswa SIMANSA tidak berubah.");
    }

    public function nomorInduk(Request $request)
    {
        $search = trim((string) $request->input('q', ''));

        return view('asrama.master.nomor-induk', [
            'records' => AsramaSantri::with('siswa')
                ->when($search !== '', fn ($query) => $query->whereHas('siswa', fn ($q) => $q
                    ->where('nama_lengkap', 'like', "%{$search}%")
                    ->orWhere('nisn', 'like', "%{$search}%")
                    ->orWhere('nis_lokal', 'like', "%{$search}%"))
                    ->orWhere('nomor_induk_asrama', 'like', "%{$search}%"))
                ->orderByDesc('status')->latest()->paginate(50)->withQueryString(),
            'search' => $search,
        ]);
    }

    public function updateNomorInduk(Request $request, AsramaSantri $santri)
    {
        $data = $request->validate([
            'nomor_induk_asrama' => ['required', 'string', 'max:50', Rule::unique('asrama_santri', 'nomor_induk_asrama')->ignore($santri->id)],
        ]);
        $data['updated_by'] = $request->user()->id;
        $santri->update($data);

        return back()->with('success', 'Nomor induk santri berhasil diperbarui.');
    }

    public function importNomorInduk(Request $request)
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:xlsx,xls', 'max:2048'],
        ], [], ['file' => 'file Excel']);

        $import = new AsramaNomorIndukImport;
        Excel::import($import, $request->file('file'));
        $results = $import->getResults();

        return back()
            ->with('nomor_induk_import', $results)
            ->with($results['failed'] === 0 ? 'success' : 'warning',
                $results['success'].' nomor induk diperbarui, '.$results['failed'].' gagal.');
    }

    public function templateNomorInduk()
    {
        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Nomor Induk Santri');

        $headers = ['nisn', 'nama', 'nomor_induk'];
        $sheet->fromArray($headers, null, 'A1');
        foreach (range('A', 'C') as $col) {
            $sheet->getColumnDimension($col)->setWidth(28);
            $sheet->getStyle($col.'1')->getFont()->setBold(true);
            $sheet->getStyle($col.'1')->getFill()->setFillType(Fill::FILL_SOLID)
                ->getStartColor()->setRGB('17324D');
            $sheet->getStyle($col.'1')->getFont()->getColor()->setRGB('FFFFFF');
        }
        $sheet->fromArray([
            ['0012345678', 'Contoh Nama Santri', 'AST-26-0001'],
        ], null, 'A2');

        $fileName = 'Template_Nomor_Induk_Santri.xlsx';
        $tempPath = storage_path('app/'.$fileName);
        (new Xlsx($spreadsheet))->save($tempPath);

        return response()->download($tempPath, $fileName)->deleteFileAfterSend(true);
    }

    public function asatidz()
    {
        return view('asrama.master.asatidz', [
            'records' => AsramaAsatidz::with('gtk.user')->withCount([
                'rombelDiasuh' => fn ($q) => $q->where('is_active', true),
                'kamarDiasuh' => fn ($q) => $q->where('is_active', true),
                'pengampu' => fn ($q) => $q->where('is_active', true),
            ])
                ->orderByDesc('is_active')->latest()->paginate(50),
            'gtks' => Gtk::whereNotNull('user_id')
                ->whereNotIn('id', AsramaAsatidz::pluck('gtk_id'))
                ->orderBy('nama_lengkap')
                ->get(['id', 'nama_lengkap', 'nip', 'nuptk', 'user_id', 'foto_profile']),
            'stats' => [
                'total' => AsramaAsatidz::count(),
                'aktif' => AsramaAsatidz::where('is_active', true)->count(),
                'pengasuh' => AsramaAsatidz::where('is_active', true)
                    ->where(fn ($q) => $q->where('dapat_mengasuh_rombel', true)->orWhere('dapat_mengasuh_kamar', true))->count(),
                'pengampu' => AsramaAsatidz::where('is_active', true)->where('dapat_mengampu_mapel', true)->count(),
            ],
        ]);
    }

    public function storeAsatidz(Request $request, AsramaAccessService $access)
    {
        $data = $this->validateAsatidz($request, false);
        unset($data['gtk_id']);
        $ids = collect($request->validate([
            'gtk_ids' => ['required', 'array', 'min:1'],
            'gtk_ids.*' => ['exists:gtks,id'],
        ])['gtk_ids'])->unique()->values();
        $unit = $this->singleAsrama();
        $gtks = Gtk::with('user')->whereNotNull('user_id')->whereIn('id', $ids)->get();
        abort_if($gtks->isEmpty(), 422, 'Pilih minimal satu GTK.');
        DB::transaction(function () use ($gtks, $unit, $data, $request, $access): void {
            foreach ($gtks as $gtk) {
                $record = AsramaAsatidz::withTrashed()->firstOrNew(['asrama_id' => $unit->id, 'gtk_id' => $gtk->id]);
                if ($record->trashed()) {
                    $record->restore();
                }
                $record->fill($data + [
                    'asrama_id' => $unit->id, 'is_active' => true, 'tanggal_selesai' => null,
                    'tanggal_mulai' => $record->tanggal_mulai?->toDateString() ?: now()->toDateString(),
                    'created_by' => $record->created_by ?: $request->user()->id, 'updated_by' => $request->user()->id,
                ])->save();
                $access->syncGtk($gtk->user);
            }
        });

        return back()->with('success', $gtks->count().' GTK berhasil ditambahkan ke tim Asrama.');
    }

    public function updateAsatidz(Request $request, AsramaAsatidz $asatidz, AsramaAccessService $access)
    {
        $data = $this->validateAsatidz($request, false);
        unset($data['gtk_id']);
        $data['is_active'] = $request->boolean('is_active');
        $data['updated_by'] = $request->user()->id;
        if (! $data['is_active']) {
            $data['tanggal_selesai'] ??= now()->toDateString();
        }
        $asatidz->update($data);
        $access->syncGtk($asatidz->gtk->user);

        return back()->with('success', 'Tugas GTK Asrama berhasil diperbarui.');
    }

    public function destroyAsatidz(Request $request, AsramaAsatidz $asatidz, AsramaAccessService $access)
    {
        $bebanAktif = $asatidz->rombelDiasuh()->where('is_active', true)->exists()
            || $asatidz->kamarDiasuh()->where('is_active', true)->exists()
            || $asatidz->pengampu()->where('is_active', true)->exists();
        if ($bebanAktif) {
            return back()->with('error', 'GTK masih memiliki tugas aktif (rombel/kamar/mapel). Lepas semua tugas terlebih dahulu sebelum menghapus.');
        }

        $nama = $asatidz->gtk->nama_lengkap;
        $asatidz->update([
            'is_active' => false,
            'tanggal_selesai' => $asatidz->tanggal_selesai?->toDateString() ?: now()->toDateString(),
            'updated_by' => $request->user()->id,
        ]);
        $asatidz->delete();
        $access->syncGtk($asatidz->gtk->user);

        return back()->with('success', "GTK {$nama} dihapus dari tim Asrama. Data GTK SIMANSA tidak berubah.");
    }

    public function mapel()
    {
        return view('asrama.master.mapel', [
            'records' => AsramaMapel::orderBy('urutan')->orderBy('nama_latin')->get(),
        ]);
    }

    public function storeMapel(Request $request)
    {
        AsramaMapel::create($this->validateMapel($request) + [
            'asrama_id' => null, 'is_active' => $request->boolean('is_active', true),
            'created_by' => $request->user()->id, 'updated_by' => $request->user()->id,
        ]);

        return back()->with('success', 'Mata pelajaran Asrama ditambahkan.');
    }

    public function updateMapel(Request $request, AsramaMapel $mapel)
    {
        $mapel->update($this->validateMapel($request, $mapel) + [
            'asrama_id' => null, 'is_active' => $request->boolean('is_active'),
            'updated_by' => $request->user()->id,
        ]);

        return back()->with('success', 'Mata pelajaran Asrama diperbarui.');
    }

    public function destroyMapel(AsramaMapel $mapel)
    {
        abort_if($mapel->pengampu()->exists(), 422, 'Mapel sudah digunakan dalam penugasan.');
        $mapel->delete();

        return back()->with('success', 'Mata pelajaran dihapus.');
    }

    private function validateAsatidz(Request $request, bool $requireGtk = true): array
    {
        return $request->validate([
            'gtk_id' => [$requireGtk ? 'required' : 'nullable', 'exists:gtks,id'],
            'nomor_identitas' => ['nullable', 'string', 'max:50'],
            'jabatan' => ['required', 'string', 'max:100'],
            'dapat_mengasuh_rombel' => ['nullable', 'boolean'],
            'dapat_mengasuh_kamar' => ['nullable', 'boolean'],
            'dapat_mengampu_mapel' => ['nullable', 'boolean'],
            'tanggal_mulai' => ['nullable', 'date'],
            'tanggal_selesai' => ['nullable', 'date', 'after_or_equal:tanggal_mulai'],
            'catatan' => ['nullable', 'string'],
        ]) + [
            'dapat_mengasuh_rombel' => $request->boolean('dapat_mengasuh_rombel'),
            'dapat_mengasuh_kamar' => $request->boolean('dapat_mengasuh_kamar'),
            'dapat_mengampu_mapel' => $request->boolean('dapat_mengampu_mapel'),
        ];
    }

    private function validateMapel(Request $request, ?AsramaMapel $mapel = null): array
    {
        return $request->validate([
            'kode' => ['required', 'string', 'max:30', Rule::unique('asrama_mapel', 'kode')->ignore($mapel?->id)],
            'nama_latin' => ['required', 'string', 'max:255'],
            'nama_arab' => ['required', 'string', 'max:255'],
            'kategori' => ['nullable', 'string', 'max:80'],
            'skala_maksimum' => ['required', 'numeric', 'min:1', 'max:100'],
            'nilai_minimum' => ['nullable', 'numeric', 'min:0', 'lte:skala_maksimum'],
            'urutan' => ['required', 'integer', 'min:0'],
            'deskripsi' => ['nullable', 'string'],
        ]);
    }

    private function singleAsrama(): Asrama
    {
        return Asrama::where('is_active', true)->first() ?: Asrama::firstOrCreate(
            ['kode' => 'ASRAMA'],
            ['nama' => 'Asrama MAN 1 Metro', 'jenis' => 'campuran', 'is_active' => true]
        );
    }

    private function generateSantriNumber(Siswa $student): string
    {
        foreach (array_filter([$student->nis_lokal, $student->nisn]) as $candidate) {
            if (! AsramaSantri::withTrashed()->where('nomor_induk_asrama', $candidate)->exists()) {
                return $candidate;
            }
        }
        $sequence = AsramaSantri::withTrashed()->count() + 1;
        do {
            $candidate = 'AST-'.now()->format('y').'-'.str_pad((string) $sequence++, 4, '0', STR_PAD_LEFT);
        } while (AsramaSantri::withTrashed()->where('nomor_induk_asrama', $candidate)->exists());

        return $candidate;
    }
}
