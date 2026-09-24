<?php

namespace App\Http\Controllers\Admin\TataUsaha;

use App\Http\Controllers\Controller;
use App\Models\AppSetting;
use App\Models\SuratMasuk;
use App\Models\SuratMasukSetting;
use App\Models\ReferensiPerguruanTinggi;
use App\Models\Sekolah;
use App\Models\PendaftaranPpdb;
use App\Models\SiswaLulusan;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class SuratMasukController extends Controller
{
    public function index(Request $request)
    {
        $this->ensureAccess('view-surat-masuk');

        $query = SuratMasuk::query()
            ->when($request->filled('tahun'), fn (Builder $q) => $q->where('tahun', (int) $request->input('tahun')))
            ->when($request->filled('status'), fn (Builder $q) => $q->where('status', $request->input('status')))
            ->when($request->filled('q'), function (Builder $q) use ($request) {
                $term = trim($request->string('q')->toString());
                $q->where(fn (Builder $sub) => $sub
                    ->where('nomor_berkas', 'like', "%{$term}%")
                    ->orWhere('tanggal_nomor', 'like', "%{$term}%")
                    ->orWhere('asal', 'like', "%{$term}%")
                    ->orWhere('isi_ringkasan', 'like', "%{$term}%"));
            })
            ->latest('diterima_tanggal')->latest('created_at');

        $statsQuery = clone $query;
        $stats = [
            'total' => (clone $statsQuery)->reorder()->count(),
            'dicatat' => (clone $statsQuery)->reorder()->where('status', 'dicatat')->count(),
            'diprint' => (clone $statsQuery)->reorder()->where('status', 'sudah_diprint')->count(),
            'selesai' => (clone $statsQuery)->reorder()->where('status', 'selesai')->count(),
        ];

        return view('admin.tata-usaha.surat-masuk.index', [
            'items' => $query->paginate(15)->withQueryString(),
            'stats' => $stats,
            'years' => SuratMasuk::query()->select('tahun')->distinct()->orderByDesc('tahun')->pluck('tahun'),
            'numberSetting' => SuratMasukSetting::current(),
            'canManageNumberSetting' => $this->isSuperAdmin(),
        ]);
    }

    public function asalSuggestions(Request $request)
    {
        $this->ensureAccess('view-surat-masuk');
        $term = trim($request->string('q')->toString());
        if (mb_strlen($term) < 2) return response()->json([]);

        $items = collect(SuratMasuk::query()->where('asal', 'like', "%{$term}%")->select('asal')->distinct()->limit(8)->get()->map(fn ($item) => [
            'nama' => $item->asal, 'jenis' => 'riwayat', 'sumber' => 'Riwayat Surat Masuk',
        ])->all());
        $items = $items->merge(Sekolah::query()->where('nama', 'like', "%{$term}%")->orderBy('nama')->limit(8)->get()->map(fn ($item) => [
            'nama' => $item->nama, 'jenis' => 'sekolah', 'sumber' => 'Referensi Sekolah SIMANSA',
        ]));
        $items = $items->merge(ReferensiPerguruanTinggi::query()->where('is_active', true)->where('nama', 'like', "%{$term}%")->orderBy('nama')->limit(8)->get()->map(fn ($item) => [
            'nama' => $item->nama, 'jenis' => 'perguruan_tinggi', 'sumber' => 'Referensi Perguruan Tinggi SIMANSA',
        ]));
        $items = $items->merge(PendaftaranPpdb::query()->where('asal_sekolah', 'like', "%{$term}%")->select('asal_sekolah')->distinct()->limit(8)->get()->map(fn ($item) => [
            'nama' => $item->asal_sekolah, 'jenis' => 'sekolah', 'sumber' => 'Data PPDB SIMANSA',
        ]));
        $items = $items->merge(SiswaLulusan::query()->where(function ($query) use ($term) {
            $query->where('nama_universitas', 'like', "%{$term}%")
                ->orWhere('nama_universitas_manual', 'like', "%{$term}%");
        })->selectRaw("COALESCE(NULLIF(nama_universitas, ''), nama_universitas_manual) as nama")->distinct()->limit(8)->get()->filter(fn ($item) => filled($item->nama))->map(fn ($item) => [
            'nama' => $item->nama, 'jenis' => 'perguruan_tinggi', 'sumber' => 'Data Lulusan SIMANSA',
        ]));

        return response()->json($items->filter(fn ($item) => filled($item['nama']))->unique(fn ($item) => mb_strtolower($item['nama']))->take(12)->values());
    }

    public function updateNumberSetting(Request $request)
    {
        abort_unless($this->isSuperAdmin(), 403);
        $validated = $request->validate(['nomor_terakhir' => ['required', 'integer', 'min:0', 'max:999999999']]);

        DB::transaction(function () use ($validated) {
            $setting = SuratMasukSetting::query()->lockForUpdate()->firstOrFail();
            $maxRecord = (int) SuratMasuk::query()->lockForUpdate()->max('nomor_urut');
            $newValue = (int) $validated['nomor_terakhir'];
            if ($newValue < (int) $setting->nomor_terakhir || $newValue < $maxRecord) {
                abort(422, 'Nomor terakhir tidak boleh diturunkan karena dapat merusak urutan atau menabrak record yang sudah ada.');
            }
            $setting->update(['nomor_terakhir' => $newValue, 'updated_by' => auth()->id()]);
        });

        return back()->with('success', 'Pengaturan nomor berkas berhasil disimpan. Record berikutnya akan memakai nomor setelah angka tersebut.');
    }

    public function create()
    {
        $this->ensureAccess('create-surat-masuk');

        return view('admin.tata-usaha.surat-masuk.form', ['item' => new SuratMasuk()]);
    }

    public function store(Request $request)
    {
        $this->ensureAccess('create-surat-masuk');
        $data = $this->validated($request);
        $file = $request->file('surat_masuk');

        $item = DB::transaction(function () use ($data, $file) {
            $year = now('Asia/Jakarta')->year;
            // Buku manual terakhir bernomor 360; nomor digital dilanjutkan dari sana.
            $setting = SuratMasukSetting::query()->lockForUpdate()->firstOrCreate([], ['nomor_terakhir' => 360]);
            $next = max((int) $setting->nomor_terakhir, (int) SuratMasuk::lockForUpdate()->max('nomor_urut')) + 1;
            do {
                $kodeUnik = sprintf('SM%d-%s', $year, Str::upper(Str::random(8)));
            } while (SuratMasuk::where('kode_unik', $kodeUnik)->exists());

            $item = new SuratMasuk($data + [
                'tahun' => $year,
                'nomor_urut' => $next,
                'nomor_berkas' => (string) $next,
                'kode_unik' => $kodeUnik,
                'status' => 'dicatat',
                'created_by' => auth()->id(),
                'updated_by' => auth()->id(),
            ]);
            $item->save();
            $setting->update(['nomor_terakhir' => $next, 'updated_by' => auth()->id()]);
            $this->storeOriginalFile($item, $file);

            return $item;
        });

        return redirect()->route('admin.tata-usaha.surat-masuk.show', $item)
            ->with('success', 'Surat masuk berhasil dicatat dengan nomor berkas '.$item->nomor_berkas.'.');
    }

    public function show(SuratMasuk $suratMasuk)
    {
        $this->ensureAccess('view-surat-masuk');

        return view('admin.tata-usaha.surat-masuk.show', ['item' => $suratMasuk->load('uploader')]);
    }

    public function edit(SuratMasuk $suratMasuk)
    {
        $this->ensureAccess('edit-surat-masuk');

        return view('admin.tata-usaha.surat-masuk.form', ['item' => $suratMasuk]);
    }

    public function update(Request $request, SuratMasuk $suratMasuk)
    {
        $this->ensureAccess('edit-surat-masuk');
        $validated = $this->validated($request);
        $suratMasuk->update($validated + ['updated_by' => auth()->id()]);
        if ($request->hasFile('surat_masuk')) {
            $this->storeOriginalFile($suratMasuk, $request->file('surat_masuk'));
        }

        return redirect()->route('admin.tata-usaha.surat-masuk.show', $suratMasuk)
            ->with('success', 'Data surat masuk berhasil diperbarui.');
    }

    public function print(SuratMasuk $suratMasuk)
    {
        $this->ensureAccess('print-surat-masuk');
        $setting = AppSetting::getInstance();
        $pdf = Pdf::loadView('admin.tata-usaha.surat-masuk.disposisi-pdf', [
            'item' => $suratMasuk,
            'setting' => $setting,
            'logoKemenagDataUri' => $this->imageDataUri(
                $setting->logo_kemenag_path,
                public_path('vendor/adminlte/dist/img/logo-kemenag.png')
            ),
            'logoSekolahDataUri' => $this->imageDataUri(
                $setting->logo_sekolah_path,
                public_path('vendor/adminlte/dist/img/logo-sekolah.png')
            ),
        ])->setPaper('a4', 'portrait');

        $content = $pdf->output();
        $path = sprintf('tata-usaha/surat-masuk/%d/%s/lembar-disposisi-%s.pdf',
            $suratMasuk->tahun,
            $suratMasuk->id,
            now('Asia/Jakarta')->format('YmdHis'));
        Storage::disk('private')->put($path, $content);
        $suratMasuk->update([
            'print_path' => $path,
            'print_count' => ((int) $suratMasuk->print_count) + 1,
            'printed_at' => now('Asia/Jakarta'),
            'status' => $suratMasuk->status === 'selesai' ? 'selesai' : 'sudah_diprint',
            'updated_by' => auth()->id(),
        ]);

        return response($content, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="lembar-disposisi-'.$suratMasuk->nomor_berkas.'.pdf"',
        ]);
    }

    public function uploadResult(Request $request, SuratMasuk $suratMasuk)
    {
        $this->ensureAccess('upload-hasil-disposisi');
        $request->validate([
            'hasil_disposisi' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png,doc,docx', 'max:10240'],
        ], ['hasil_disposisi.required' => 'File hasil disposisi wajib dipilih.']);

        if ($suratMasuk->hasil_disposisi_path) {
            Storage::disk('private')->delete($suratMasuk->hasil_disposisi_path);
        }
        $file = $request->file('hasil_disposisi');
        $path = $file->storeAs(
            sprintf('tata-usaha/surat-masuk/%d/%s', $suratMasuk->tahun, $suratMasuk->id),
            'hasil-disposisi-'.now('Asia/Jakarta')->format('YmdHis').'-'.Str::lower(Str::random(5)).'.'.$file->extension(),
            'private'
        );
        $suratMasuk->update([
            'hasil_disposisi_path' => $path,
            'hasil_disposisi_nama' => $file->getClientOriginalName(),
            'hasil_disposisi_uploaded_at' => now('Asia/Jakarta'),
            'hasil_disposisi_uploaded_by' => auth()->id(),
            'status' => 'selesai',
            'updated_by' => auth()->id(),
        ]);

        return back()->with('success', 'Hasil disposisi berhasil diunggah. Surat ditandai selesai.');
    }

    public function download(Request $request, SuratMasuk $suratMasuk, string $jenis)
    {
        $this->ensureAccess('view-surat-masuk');
        abort_unless(in_array($jenis, ['surat-masuk', 'print', 'hasil-disposisi'], true), 404);
        $path = match ($jenis) {
            'surat-masuk' => $suratMasuk->surat_masuk_path,
            'print' => $suratMasuk->print_path,
            default => $suratMasuk->hasil_disposisi_path,
        };
        abort_unless($path && Storage::disk('private')->exists($path), 404);

        if ($request->boolean('preview')) {
            $mime = Storage::disk('private')->mimeType($path) ?: 'application/octet-stream';
            $name = basename($path);

            return Storage::disk('private')->response($path, $name, [
                'Content-Type' => $mime,
                'X-Content-Type-Options' => 'nosniff',
            ], 'inline');
        }

        return Storage::disk('private')->download($path);
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'tanggal_nomor' => ['required', 'string', 'max:255'],
            'asal' => ['required', 'string', 'max:255'],
            'isi_ringkasan' => ['required', 'string', 'max:5000'],
            'diterima_tanggal' => ['required', 'date'],
            'surat_masuk' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png,doc,docx', 'max:10240'],
        ]) + ['updated_by' => auth()->id()];
    }

    private function storeOriginalFile(SuratMasuk $item, $file): void
    {
        if (!$file) return;
        if ($item->surat_masuk_path) Storage::disk('private')->delete($item->surat_masuk_path);
        $path = $file->storeAs(
            sprintf('tata-usaha/surat-masuk/%d/%s', $item->tahun, $item->id),
            'surat-masuk-'.Str::lower(Str::random(8)).'.'.$file->extension(),
            'private'
        );
        $item->update([
            'surat_masuk_path' => $path,
            'surat_masuk_nama' => $file->getClientOriginalName(),
        ]);
    }

    private function ensureAccess(string $permission): void
    {
        $user = request()->user();
        abort_unless($user && ($user->isStaffTu() || $user->can($permission) || $user->hasAnyRole(['Super Admin', 'Admin'])), 403);
    }

    private function isSuperAdmin(): bool
    {
        $user = request()->user();
        return (bool) $user && ($user->hasRole('Super Admin') || $user->role === 'super_admin');
    }


    private function imageDataUri(?string $configuredPath, string $fallbackPath): string
    {
        $paths = array_filter([$configuredPath ? storage_path('app/public/'.$configuredPath) : null, $fallbackPath]);
        foreach ($paths as $path) {
            if (!is_file($path)) continue;
            $image = @imagecreatefromstring((string) file_get_contents($path));
            if ($image === false) continue;

            $width = imagesx($image);
            $height = imagesy($image);
            $maxHeight = 110;
            $newHeight = min($height, $maxHeight);
            $newWidth = max(1, (int) round(($width / max(1, $height)) * $newHeight));
            $resized = imagecreatetruecolor($newWidth, $newHeight);
            imagealphablending($resized, false);
            imagesavealpha($resized, true);
            imagecopyresampled($resized, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

            ob_start();
            imagepng($resized, null, 6);
            $content = ob_get_clean();
            imagedestroy($image);
            imagedestroy($resized);

            return 'data:image/png;base64,'.base64_encode($content);
        }

        return '';
    }
}
