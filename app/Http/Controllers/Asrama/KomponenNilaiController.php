<?php

namespace App\Http\Controllers\Asrama;

use App\Http\Controllers\Controller;
use App\Models\AsramaKomponenNilai;
use App\Models\TahunPelajaran;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class KomponenNilaiController extends Controller
{
    public function index(Request $request)
    {
        $activeYear = TahunPelajaran::active()->first();
        $defaultYearId = $activeYear?->id ?: TahunPelajaran::orderByDesc('tahun_mulai')->value('id');
        $tahunId = $request->input('tahun_pelajaran_id', $defaultYearId);

        $selectedYear = TahunPelajaran::find($tahunId);
        $years = TahunPelajaran::orderByDesc('tahun_mulai')->get();

        $components = AsramaKomponenNilai::where('tahun_pelajaran_id', $tahunId)
            ->ordered()
            ->get();

        $totalBobot = (float) $components->where('is_active', true)->sum('bobot');
        $activeCount = $components->where('is_active', true)->count();

        return view('asrama.komponen-nilai.index', compact(
            'selectedYear',
            'activeYear',
            'years',
            'components',
            'totalBobot',
            'activeCount'
        ));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'tahun_pelajaran_id' => ['required', 'exists:tahun_pelajaran,id'],
            'kode' => [
                'required', 'string', 'max:50', 'regex:/^[A-Za-z0-9_]+$/',
                Rule::unique('asrama_komponen_nilai')
                    ->where('tahun_pelajaran_id', $request->input('tahun_pelajaran_id'))
                    ->whereNull('deleted_at'),
            ],
            'nama' => ['required', 'string', 'max:100'],
            'bobot' => ['required', 'numeric', 'min:0', 'max:100'],
            'urutan' => ['nullable', 'integer', 'min:1', 'max:255'],
            'is_active' => ['nullable', 'boolean'],
            'keterangan' => ['nullable', 'string', 'max:255'],
        ], [
            'kode.regex' => 'Kode komponen hanya boleh berisi huruf, angka, dan underscore (_).',
            'kode.unique' => 'Kode komponen sudah digunakan pada tahun pelajaran ini.',
        ]);

        $data['kode'] = strtoupper(trim($data['kode']));
        $data['is_active'] = $request->boolean('is_active', true);
        $data['urutan'] = $data['urutan'] ?? ((AsramaKomponenNilai::where('tahun_pelajaran_id', $data['tahun_pelajaran_id'])->max('urutan') ?? 0) + 1);

        AsramaKomponenNilai::create($data);

        return redirect()->route('asrama.komponen-nilai.index', ['tahun_pelajaran_id' => $data['tahun_pelajaran_id']])
            ->with('success', 'Komponen nilai asrama berhasil ditambahkan.');
    }

    public function update(Request $request, AsramaKomponenNilai $komponen)
    {
        $data = $request->validate([
            'kode' => [
                'required', 'string', 'max:50', 'regex:/^[A-Za-z0-9_]+$/',
                Rule::unique('asrama_komponen_nilai')
                    ->where('tahun_pelajaran_id', $komponen->tahun_pelajaran_id)
                    ->ignore($komponen->id)
                    ->whereNull('deleted_at'),
            ],
            'nama' => ['required', 'string', 'max:100'],
            'bobot' => ['required', 'numeric', 'min:0', 'max:100'],
            'urutan' => ['required', 'integer', 'min:1', 'max:255'],
            'is_active' => ['nullable', 'boolean'],
            'keterangan' => ['nullable', 'string', 'max:255'],
        ], [
            'kode.regex' => 'Kode komponen hanya boleh berisi huruf, angka, dan underscore (_).',
            'kode.unique' => 'Kode komponen sudah digunakan pada tahun pelajaran ini.',
        ]);

        $data['kode'] = strtoupper(trim($data['kode']));
        $data['is_active'] = $request->boolean('is_active');

        $komponen->update($data);

        return redirect()->route('asrama.komponen-nilai.index', ['tahun_pelajaran_id' => $komponen->tahun_pelajaran_id])
            ->with('success', 'Komponen nilai asrama berhasil diperbarui.');
    }

    public function destroy(AsramaKomponenNilai $komponen)
    {
        $tahunId = $komponen->tahun_pelajaran_id;
        $komponen->delete();

        return redirect()->route('asrama.komponen-nilai.index', ['tahun_pelajaran_id' => $tahunId])
            ->with('success', 'Komponen nilai asrama berhasil dihapus.');
    }

    public function copyFromYear(Request $request)
    {
        $data = $request->validate([
            'from_tahun_pelajaran_id' => ['required', 'exists:tahun_pelajaran,id', 'different:to_tahun_pelajaran_id'],
            'to_tahun_pelajaran_id' => ['required', 'exists:tahun_pelajaran,id'],
        ], [
            'from_tahun_pelajaran_id.different' => 'Tahun pelajaran sumber dan tujuan tidak boleh sama.',
        ]);

        $sourceComponents = AsramaKomponenNilai::where('tahun_pelajaran_id', $data['from_tahun_pelajaran_id'])
            ->ordered()
            ->get();

        if ($sourceComponents->isEmpty()) {
            return back()->with('error', 'Tahun pelajaran sumber tidak memiliki komponen nilai untuk disalin.');
        }

        $copied = 0;
        foreach ($sourceComponents as $src) {
            $exists = AsramaKomponenNilai::where('tahun_pelajaran_id', $data['to_tahun_pelajaran_id'])
                ->where('kode', $src->kode)
                ->exists();

            if (! $exists) {
                AsramaKomponenNilai::create([
                    'tahun_pelajaran_id' => $data['to_tahun_pelajaran_id'],
                    'kode' => $src->kode,
                    'nama' => $src->nama,
                    'bobot' => $src->bobot,
                    'urutan' => $src->urutan,
                    'is_active' => $src->is_active,
                    'keterangan' => $src->keterangan,
                ]);
                $copied++;
            }
        }

        return redirect()->route('asrama.komponen-nilai.index', ['tahun_pelajaran_id' => $data['to_tahun_pelajaran_id']])
            ->with('success', "Berhasil menyalin {$copied} komponen nilai ke tahun pelajaran tujuan.");
    }
}
