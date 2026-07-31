<?php

namespace App\Http\Controllers\Asrama;

use App\Http\Controllers\Controller;
use App\Models\Asrama;
use App\Models\AsramaAsatidz;
use App\Models\AsramaMapel;
use App\Models\AsramaSantri;
use App\Models\Gtk;
use App\Models\Siswa;
use App\Services\AsramaAccessService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class MasterController extends Controller
{
    public function units()
    {
        return view('asrama.master.units', [
            'units' => Asrama::with('kepala')->withCount(['santri' => fn ($q) => $q->where('status', 'aktif')])->orderBy('nama')->get(),
            'gtks' => Gtk::orderBy('nama_lengkap')->get(['id', 'nama_lengkap', 'nip']),
        ]);
    }

    public function storeUnit(Request $request)
    {
        $data = $request->validate([
            'kode' => ['required', 'string', 'max:30', 'unique:asrama_units,kode'],
            'nama' => ['required', 'string', 'max:255'],
            'jenis' => ['required', Rule::in(['putra', 'putri', 'campuran'])],
            'kepala_gtk_id' => ['nullable', 'exists:gtks,id'],
            'alamat' => ['nullable', 'string'],
            'telepon' => ['nullable', 'string', 'max:30'],
            'deskripsi' => ['nullable', 'string'],
        ]);
        $data['is_active'] = $request->boolean('is_active', true);
        Asrama::create($data);

        return back()->with('success', 'Unit asrama berhasil ditambahkan.');
    }

    public function updateUnit(Request $request, Asrama $asrama)
    {
        $data = $request->validate([
            'kode' => ['required', 'string', 'max:30', Rule::unique('asrama_units', 'kode')->ignore($asrama->id)],
            'nama' => ['required', 'string', 'max:255'],
            'jenis' => ['required', Rule::in(['putra', 'putri', 'campuran'])],
            'kepala_gtk_id' => ['nullable', 'exists:gtks,id'],
            'alamat' => ['nullable', 'string'],
            'telepon' => ['nullable', 'string', 'max:30'],
            'deskripsi' => ['nullable', 'string'],
        ]);
        $data['is_active'] = $request->boolean('is_active');
        $asrama->update($data);

        return back()->with('success', 'Unit asrama berhasil diperbarui.');
    }

    public function destroyUnit(Asrama $asrama)
    {
        abort_if($asrama->santri()->where('status', 'aktif')->exists() || $asrama->kelas()->where('is_active', true)->exists(), 422, 'Unit masih memiliki santri atau kelas aktif.');
        $asrama->delete();

        return back()->with('success', 'Unit asrama dihapus.');
    }

    public function santri(Request $request)
    {
        $unitId = $request->input('asrama_id');

        return view('asrama.master.santri', [
            'units' => Asrama::where('is_active', true)->orderBy('nama')->get(),
            'selectedUnit' => $unitId,
            'records' => AsramaSantri::with(['asrama', 'siswa.kelasTahunAktif'])
                ->when($unitId, fn ($q) => $q->where('asrama_id', $unitId))
                ->latest()->paginate(50)->withQueryString(),
            'students' => Siswa::where('status_siswa', 'aktif')->orderBy('nama_lengkap')
                ->get(['id', 'nama_lengkap', 'nisn', 'nis_lokal']),
        ]);
    }

    public function storeSantri(Request $request, AsramaAccessService $access)
    {
        $data = $request->validate([
            'asrama_id' => ['required', 'exists:asrama_units,id'],
            'siswa_ids' => ['required', 'array', 'min:1'],
            'siswa_ids.*' => ['exists:siswa,id'],
            'nomor_induk_asrama' => ['nullable', 'string', 'max:50', 'unique:asrama_santri,nomor_induk_asrama'],
            'tanggal_masuk' => ['nullable', 'date'],
        ]);

        $unit = Asrama::findOrFail($data['asrama_id']);
        $students = Siswa::with('user')->whereIn('id', $data['siswa_ids'])->get();
        DB::transaction(function () use ($students, $unit, $data, $request, $access) {
            foreach ($students as $index => $student) {
                $otherMemberships = AsramaSantri::with('siswa.user')
                    ->where('siswa_id', $student->id)
                    ->where('asrama_id', '!=', $unit->id)
                    ->where('status', 'aktif')
                    ->get();
                foreach ($otherMemberships as $other) {
                    $other->kelasRecords()->where('status', 'aktif')->update([
                        'status' => 'keluar',
                        'tanggal_keluar' => now()->toDateString(),
                        'is_ketua_kelas' => false,
                    ]);
                    $other->update([
                        'status' => 'nonaktif',
                        'tanggal_keluar' => now()->toDateString(),
                        'updated_by' => $request->user()->id,
                    ]);
                }
                $number = count($students) === 1 && filled($data['nomor_induk_asrama'] ?? null)
                    ? $data['nomor_induk_asrama']
                    : $this->generateSantriNumber($unit, $student, $index);
                $record = AsramaSantri::withTrashed()->firstOrNew([
                    'asrama_id' => $unit->id,
                    'siswa_id' => $student->id,
                ]);
                if ($record->trashed()) {
                    $record->restore();
                }
                $record->fill([
                    'nomor_induk_asrama' => $record->exists ? $record->nomor_induk_asrama : $number,
                    'tanggal_masuk' => $data['tanggal_masuk'] ?? now()->toDateString(),
                    'tanggal_keluar' => null,
                    'status' => 'aktif',
                    'updated_by' => $request->user()->id,
                    'created_by' => $record->created_by ?: $request->user()->id,
                ])->save();
                $access->syncStudent($student->user);
            }
        });

        return back()->with('success', count($students).' siswa berhasil diaktifkan sebagai santri.');
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
        if ($data['status'] === 'nonaktif' && empty($data['tanggal_keluar'])) {
            $data['tanggal_keluar'] = now()->toDateString();
        }
        if ($data['status'] === 'nonaktif') {
            $santri->kelasRecords()->where('status', 'aktif')->update([
                'status' => 'keluar',
                'tanggal_keluar' => $data['tanggal_keluar'],
                'is_ketua_kelas' => false,
            ]);
        }
        $santri->update($data);
        $access->syncStudent($santri->siswa->user);

        return back()->with('success', 'Data santri diperbarui.');
    }

    public function asatidz(Request $request)
    {
        $unitId = $request->input('asrama_id');

        return view('asrama.master.asatidz', [
            'units' => Asrama::where('is_active', true)->orderBy('nama')->get(),
            'selectedUnit' => $unitId,
            'records' => AsramaAsatidz::with(['asrama', 'gtk.user'])
                ->when($unitId, fn ($q) => $q->where('asrama_id', $unitId))
                ->orderByDesc('is_active')->latest()->paginate(50)->withQueryString(),
            'gtks' => Gtk::orderBy('nama_lengkap')->get(['id', 'nama_lengkap', 'nip', 'user_id']),
        ]);
    }

    public function storeAsatidz(Request $request, AsramaAccessService $access)
    {
        $data = $request->validate([
            'asrama_id' => ['required', 'exists:asrama_units,id'],
            'gtk_id' => ['required', 'exists:gtks,id'],
            'nomor_identitas' => ['nullable', 'string', 'max:50'],
            'jabatan' => ['required', 'string', 'max:100'],
            'tanggal_mulai' => ['nullable', 'date'],
            'catatan' => ['nullable', 'string'],
        ]);
        $record = AsramaAsatidz::withTrashed()->firstOrNew([
            'asrama_id' => $data['asrama_id'], 'gtk_id' => $data['gtk_id'],
        ]);
        if ($record->trashed()) {
            $record->restore();
        }
        $record->fill($data + [
            'is_active' => true,
            'tanggal_selesai' => null,
            'created_by' => $request->user()->id,
            'updated_by' => $request->user()->id,
        ])->save();
        $access->syncGtk($record->gtk->user);

        return back()->with('success', 'GTK berhasil ditugaskan sebagai asatidz.');
    }

    public function updateAsatidz(Request $request, AsramaAsatidz $asatidz, AsramaAccessService $access)
    {
        $data = $request->validate([
            'nomor_identitas' => ['nullable', 'string', 'max:50'],
            'jabatan' => ['required', 'string', 'max:100'],
            'tanggal_mulai' => ['nullable', 'date'],
            'tanggal_selesai' => ['nullable', 'date', 'after_or_equal:tanggal_mulai'],
            'catatan' => ['nullable', 'string'],
        ]);
        $data['is_active'] = $request->boolean('is_active');
        $data['updated_by'] = $request->user()->id;
        if (! $data['is_active'] && empty($data['tanggal_selesai'])) {
            $data['tanggal_selesai'] = now()->toDateString();
        }
        $asatidz->update($data);
        $access->syncGtk($asatidz->gtk->user);

        return back()->with('success', 'Penugasan asatidz diperbarui.');
    }

    public function mapel(Request $request)
    {
        return view('asrama.master.mapel', [
            'units' => Asrama::where('is_active', true)->orderBy('nama')->get(),
            'records' => AsramaMapel::with('asrama')->orderBy('urutan')->orderBy('nama_latin')->get(),
        ]);
    }

    public function storeMapel(Request $request)
    {
        AsramaMapel::create($this->validateMapel($request) + [
            'is_active' => $request->boolean('is_active', true),
            'created_by' => $request->user()->id,
            'updated_by' => $request->user()->id,
        ]);

        return back()->with('success', 'Mata pelajaran asrama ditambahkan.');
    }

    public function updateMapel(Request $request, AsramaMapel $mapel)
    {
        $mapel->update($this->validateMapel($request, $mapel) + [
            'is_active' => $request->boolean('is_active'),
            'updated_by' => $request->user()->id,
        ]);

        return back()->with('success', 'Mata pelajaran asrama diperbarui.');
    }

    public function destroyMapel(AsramaMapel $mapel)
    {
        abort_if($mapel->pengampu()->exists(), 422, 'Mapel sudah digunakan dalam penugasan.');
        $mapel->delete();

        return back()->with('success', 'Mata pelajaran dihapus.');
    }

    private function validateMapel(Request $request, ?AsramaMapel $mapel = null): array
    {
        return $request->validate([
            'asrama_id' => ['nullable', 'exists:asrama_units,id'],
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

    private function generateSantriNumber(Asrama $unit, Siswa $student, int $offset): string
    {
        foreach (array_filter([$student->nis_lokal, $student->nisn]) as $candidate) {
            if (! AsramaSantri::withTrashed()->where('nomor_induk_asrama', $candidate)->exists()) {
                return $candidate;
            }
        }
        $sequence = AsramaSantri::withTrashed()->count() + $offset + 1;
        do {
            $candidate = strtoupper($unit->kode).'-'.now()->format('y').'-'.str_pad((string) $sequence++, 4, '0', STR_PAD_LEFT);
        } while (AsramaSantri::withTrashed()->where('nomor_induk_asrama', $candidate)->exists());

        return $candidate;
    }
}
