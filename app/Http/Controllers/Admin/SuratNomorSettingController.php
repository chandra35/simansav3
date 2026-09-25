<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SuratNomorConfig;
use App\Models\SuratNomorCounter;
use App\Services\SuratNumberService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SuratNomorSettingController extends Controller
{
    public function edit(SuratNumberService $service)
    {
        $this->authorize('manage-settings');

        $counter = SuratNomorCounter::where('tahun', $service->year())->first();

        return view('admin.settings.surat-nomor', [
            'configs' => SuratNomorConfig::orderBy('kode')->get(),
            'tahun' => $service->year(),
            'preview' => $service->preview('PP'),
            'nomorBerikutnya' => ($counter?->nomor_terakhir ?? 0) + 1,
        ]);
    }

    public function update(Request $request)
    {
        $this->authorize('manage-settings');

        $validated = $request->validate([
            'configs' => ['required', 'array'],
            'configs.*.nama' => ['required', 'string', 'max:100'],
            'configs.*.prefix' => ['required', 'string', 'max:20'],
            'configs.*.kode_satuan_kerja' => ['required', 'string', 'max:50'],
            'configs.*.kode_klasifikasi' => ['required', 'string', 'max:50'],
            'configs.*.format_nomor' => ['required', 'string', 'max:255'],
            'configs.*.panjang_nomor' => ['required', 'integer', 'min:1', 'max:6'],
            'configs.*.is_active' => ['nullable', 'boolean'],
            'nomor_berikutnya' => ['required', 'integer', 'min:1', 'max:999999'],
        ]);

        DB::transaction(function () use ($validated) {
            $year = app(SuratNumberService::class)->year();
            $counter = SuratNomorCounter::where('tahun', $year)->lockForUpdate()->first();
            $currentNext = ($counter?->nomor_terakhir ?? 0) + 1;

            if ($validated['nomor_berikutnya'] < $currentNext) {
                abort(422, 'Nomor berikutnya tidak boleh lebih kecil dari nomor yang sudah berjalan.');
            }

            if ($validated['nomor_berikutnya'] > $currentNext || !$counter) {
                $counter ??= new SuratNomorCounter(['tahun' => $year]);
                $counter->nomor_terakhir = $validated['nomor_berikutnya'] - 1;
                $counter->save();
            }

            foreach ($validated['configs'] as $id => $data) {
                $config = SuratNomorConfig::findOrFail($id);
                $config->update([
                    'nama' => $data['nama'], 'prefix' => $data['prefix'],
                    'kode_satuan_kerja' => $data['kode_satuan_kerja'],
                    'kode_klasifikasi' => $data['kode_klasifikasi'],
                    'format_nomor' => $data['format_nomor'],
                    'panjang_nomor' => $data['panjang_nomor'],
                    'is_active' => (bool) ($data['is_active'] ?? false),
                ]);
            }
        });

        return back()->with('success', 'Pengaturan penomoran surat berhasil disimpan. Nomor yang telah diterbitkan tidak berubah.');
    }

    public function preview(Request $request, SuratNumberService $service)
    {
        $this->authorize('manage-settings');

        $request->validate(['kode' => ['required', 'exists:surat_nomor_configs,kode']]);

        return response()->json([
            'success' => true,
            'nomor' => $service->preview($request->string('kode')->toString()),
            'tahun' => $service->year(),
        ]);
    }
}
