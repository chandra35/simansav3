<?php

namespace App\Http\Controllers\Admin\TataUsaha;

use App\Http\Controllers\Controller;
use App\Models\AppSetting;
use App\Models\SuratMasuk;
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
        ]);
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
            $next = max(360, (int) SuratMasuk::lockForUpdate()->max('nomor_urut')) + 1;
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
        $suratMasuk->update($this->validated($request) + ['updated_by' => auth()->id()]);
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
            'logoDataUri' => $this->logoDataUri($setting),
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

    public function download(SuratMasuk $suratMasuk, string $jenis)
    {
        $this->ensureAccess('view-surat-masuk');
        abort_unless(in_array($jenis, ['surat-masuk', 'print', 'hasil-disposisi'], true), 404);
        $path = match ($jenis) {
            'surat-masuk' => $suratMasuk->surat_masuk_path,
            'print' => $suratMasuk->print_path,
            default => $suratMasuk->hasil_disposisi_path,
        };
        abort_unless($path && Storage::disk('private')->exists($path), 404);

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

    private function logoDataUri(AppSetting $setting): string
    {
        $paths = array_filter([
            $setting->logo_sekolah_path ? storage_path('app/public/'.$setting->logo_sekolah_path) : null,
            public_path('vendor/adminlte/dist/img/logo-sekolah.png'),
        ]);
        foreach ($paths as $path) {
            if (is_file($path)) return 'data:'.(mime_content_type($path) ?: 'image/png').';base64,'.base64_encode(file_get_contents($path));
        }

        return '';
    }
}
