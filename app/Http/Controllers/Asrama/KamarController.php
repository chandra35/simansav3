<?php

namespace App\Http\Controllers\Asrama;

use App\Http\Controllers\Controller;
use App\Models\AsramaAsatidz;
use App\Models\AsramaKamar;
use App\Models\AsramaKamarSantri;
use App\Models\AsramaSantri;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class KamarController extends Controller
{
    public function index(Request $request)
    {
        $gedung = in_array($request->input('gedung'), ['putra', 'putri'], true) ? $request->input('gedung') : null;

        return view('asrama.kamar.index', [
            'rooms' => AsramaKamar::with(['pengasuh.gtk', 'penghuniAktif.santri.siswa'])
                ->withCount('penghuniAktif')->when($gedung, fn ($query) => $query->where('gedung', $gedung))
                ->orderBy('gedung')->orderBy('nama')->get(),
            'caregivers' => AsramaAsatidz::with('gtk')->where('is_active', true)
                ->where('dapat_mengasuh_kamar', true)->get()->sortBy('gtk.nama_lengkap'),
            'availableSantri' => AsramaSantri::with(['siswa.kelasTahunAktif', 'kamarAktif'])
                ->where('status', 'aktif')->orderBy('nomor_induk_asrama')->get(),
            'selectedBuilding' => $gedung,
        ]);
    }

    public function store(Request $request)
    {
        AsramaKamar::create($this->validated($request) + [
            'is_active' => true, 'created_by' => $request->user()->id, 'updated_by' => $request->user()->id,
        ]);

        return back()->with('success', 'Kamar berhasil ditambahkan.');
    }

    public function update(Request $request, AsramaKamar $kamar)
    {
        $data = $this->validated($request, $kamar);
        abort_if($kamar->penghuniAktif()->count() > $data['kapasitas'], 422, 'Kapasitas lebih kecil dari jumlah penghuni aktif.');
        $kamar->update($data + [
            'is_active' => $request->boolean('is_active'), 'updated_by' => $request->user()->id,
        ]);

        return back()->with('success', 'Data kamar berhasil diperbarui.');
    }

    public function assign(Request $request, AsramaKamar $kamar)
    {
        $data = $request->validate([
            'santri_ids' => ['required', 'array', 'min:1'],
            'santri_ids.*' => ['exists:asrama_santri,id'],
            'tanggal_masuk' => ['nullable', 'date'],
        ]);
        $santri = AsramaSantri::with(['siswa', 'kamarAktif'])->where('status', 'aktif')
            ->whereIn('id', array_unique($data['santri_ids']))->get();
        $santri = $santri->reject(fn (AsramaSantri $item) => $item->kamarAktif?->asrama_kamar_id === $kamar->id)->values();
        abort_if($santri->isEmpty(), 422, 'Semua santri yang dipilih sudah berada di kamar ini.');
        abort_if($kamar->penghuniAktif()->count() + $santri->count() > $kamar->kapasitas, 422, 'Jumlah penghuni melebihi kapasitas kamar.');

        $wrongGender = $santri->first(function (AsramaSantri $item) use ($kamar): bool {
            $gender = strtoupper((string) $item->siswa->jenis_kelamin);
            return ($kamar->gedung === 'putra' && $gender === 'P') || ($kamar->gedung === 'putri' && $gender === 'L');
        });
        abort_if($wrongGender, 422, 'Jenis kelamin '.$wrongGender->siswa->nama_lengkap.' tidak sesuai gedung kamar.');

        DB::transaction(function () use ($santri, $kamar, $data, $request): void {
            foreach ($santri as $item) {
                AsramaKamarSantri::where('asrama_santri_id', $item->id)->where('status', 'aktif')->update([
                    'status' => 'keluar', 'tanggal_keluar' => now()->toDateString(),
                ]);
                AsramaKamarSantri::create([
                    'asrama_kamar_id' => $kamar->id, 'asrama_santri_id' => $item->id,
                    'tanggal_masuk' => $data['tanggal_masuk'] ?? now()->toDateString(),
                    'status' => 'aktif', 'ditetapkan_by' => $request->user()->id,
                ]);
            }
        });

        return back()->with('success', $santri->count().' santri berhasil ditempatkan di '.$kamar->nama.'.');
    }

    public function remove(AsramaKamar $kamar, AsramaKamarSantri $penghuni)
    {
        abort_unless($penghuni->asrama_kamar_id === $kamar->id && $penghuni->status === 'aktif', 404);
        $penghuni->update(['status' => 'keluar', 'tanggal_keluar' => now()->toDateString()]);

        return back()->with('success', 'Santri dikeluarkan dari kamar.');
    }

    private function validated(Request $request, ?AsramaKamar $kamar = null): array
    {
        return $request->validate([
            'kode' => ['required', 'string', 'max:30', Rule::unique('asrama_kamar', 'kode')->ignore($kamar?->id)],
            'nama' => ['required', 'string', 'max:120'],
            'gedung' => ['required', Rule::in(['putra', 'putri'])],
            'lantai' => ['nullable', 'string', 'max:30'],
            'kapasitas' => ['required', 'integer', 'min:1', 'max:100'],
            'pengasuh_asatidz_id' => [
                'nullable',
                Rule::exists('asrama_asatidz', 'id')->where(fn ($query) => $query
                    ->where('is_active', true)->where('dapat_mengasuh_kamar', true)->whereNull('deleted_at')),
            ],
            'catatan' => ['nullable', 'string'],
        ]);
    }
}
