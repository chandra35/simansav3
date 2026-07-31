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
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class MasterController extends Controller
{
    public function santri(Request $request)
    {
        $tahunId = $request->input('tahun_pelajaran_id') ?: TahunPelajaran::active()->value('id');

        return view('asrama.master.santri', [
            'records' => AsramaSantri::with([
                'siswa.kelasTahunAktif', 'kamarAktif.kamar',
                'kelasAktif.kelas.kelasReguler', 'kelasAktif.pengasuhAssignment.rombelPengasuh.pengasuh.gtk',
            ])->orderByDesc('status')->latest()->paginate(50)->withQueryString(),
            'students' => Siswa::where('status_siswa', 'aktif')->orderBy('nama_lengkap')
                ->get(['id', 'nama_lengkap', 'nisn', 'nis_lokal']),
            'classes' => Kelas::withCount(['siswaAktif'])->where('tahun_pelajaran_id', $tahunId)
                ->where('is_active', true)->orderBy('nama_kelas')->get(),
            'years' => TahunPelajaran::orderByDesc('tahun_mulai')->get(),
            'selectedYear' => $tahunId,
        ]);
    }

    public function storeSantri(Request $request, AsramaAccessService $access)
    {
        $data = $request->validate([
            'kelas_id' => ['nullable', 'exists:kelas,id'],
            'siswa_ids' => ['nullable', 'array'],
            'siswa_ids.*' => ['exists:siswa,id'],
            'nomor_induk_asrama' => ['nullable', 'string', 'max:50', 'unique:asrama_santri,nomor_induk_asrama'],
            'tanggal_masuk' => ['nullable', 'date'],
        ]);
        $ids = collect($data['siswa_ids'] ?? []);
        if (! empty($data['kelas_id'])) {
            $ids = $ids->merge(Kelas::findOrFail($data['kelas_id'])->siswaAktif()->pluck('siswa.id'));
        }
        $ids = $ids->unique()->values();
        abort_if($ids->isEmpty(), 422, 'Pilih rombel atau minimal satu siswa.');

        $unit = $this->singleAsrama();
        $students = Siswa::with('user')->whereIn('id', $ids)->get();
        DB::transaction(function () use ($students, $unit, $data, $request, $access): void {
            foreach ($students as $student) {
                $record = AsramaSantri::withTrashed()->where('siswa_id', $student->id)->first()
                    ?: new AsramaSantri(['asrama_id' => $unit->id, 'siswa_id' => $student->id]);
                if ($record->trashed()) {
                    $record->restore();
                }
                $record->fill([
                    'asrama_id' => $unit->id,
                    'nomor_induk_asrama' => $record->nomor_induk_asrama
                        ?: ($students->count() === 1 && filled($data['nomor_induk_asrama'] ?? null)
                            ? $data['nomor_induk_asrama'] : $this->generateSantriNumber($student)),
                    'tanggal_masuk' => $record->tanggal_masuk ?: ($data['tanggal_masuk'] ?? now()->toDateString()),
                    'tanggal_keluar' => null,
                    'status' => 'aktif',
                    'created_by' => $record->created_by ?: $request->user()->id,
                    'updated_by' => $request->user()->id,
                ])->save();
                $access->syncStudent($student->user);
            }
        });

        return back()->with('success', $students->count().' siswa berhasil diaktifkan sebagai santri.');
    }

    public function updateSantri(Request $request, AsramaSantri $santri, AsramaAccessService $access)
    {
        $data = $request->validate([
            'nomor_induk_asrama' => ['required', 'string', 'max:50', Rule::unique('asrama_santri')->ignore($santri->id)],
            'status' => ['required', Rule::in(['aktif', 'nonaktif'])],
            'tanggal_masuk' => ['nullable', 'date'],
            'tanggal_keluar' => ['nullable', 'date', 'after_or_equal:tanggal_masuk'],
            'catatan' => ['nullable', 'string'],
        ]);
        $data['updated_by'] = $request->user()->id;
        if ($data['status'] === 'nonaktif') {
            $data['tanggal_keluar'] ??= now()->toDateString();
            $santri->kelasRecords()->where('status', 'aktif')->update([
                'status' => 'keluar', 'tanggal_keluar' => $data['tanggal_keluar'], 'is_ketua_kelas' => false,
            ]);
            $santri->kamarRecords()->where('status', 'aktif')->update([
                'status' => 'keluar', 'tanggal_keluar' => $data['tanggal_keluar'],
            ]);
        }
        $santri->update($data);
        $access->syncStudent($santri->siswa->user);

        return back()->with('success', 'Data santri berhasil diperbarui.');
    }

    public function asatidz()
    {
        return view('asrama.master.asatidz', [
            'records' => AsramaAsatidz::with('gtk.user')->withCount(['rombelDiasuh', 'kamarDiasuh', 'pengampu'])
                ->orderByDesc('is_active')->latest()->paginate(50),
            'gtks' => Gtk::whereNotNull('user_id')->orderBy('nama_lengkap')
                ->get(['id', 'nama_lengkap', 'nip', 'user_id']),
        ]);
    }

    public function storeAsatidz(Request $request, AsramaAccessService $access)
    {
        $data = $this->validateAsatidz($request);
        $unit = $this->singleAsrama();
        $record = AsramaAsatidz::withTrashed()->firstOrNew(['asrama_id' => $unit->id, 'gtk_id' => $data['gtk_id']]);
        if ($record->trashed()) {
            $record->restore();
        }
        $record->fill($data + [
            'asrama_id' => $unit->id, 'is_active' => true, 'tanggal_selesai' => null,
            'created_by' => $record->created_by ?: $request->user()->id, 'updated_by' => $request->user()->id,
        ])->save();
        $access->syncGtk($record->gtk->user);

        return back()->with('success', 'GTK berhasil ditambahkan ke tim Asrama.');
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
