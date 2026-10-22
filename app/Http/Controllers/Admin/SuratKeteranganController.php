<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TemplateSurat;
use App\Models\SuratKeterangan;
use App\Models\Siswa;
use App\Models\Gtk;
use App\Models\AppSetting;
use App\Models\TahunPelajaran;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Yajra\DataTables\Facades\DataTables;
use Barryvdh\DomPDF\Facade\Pdf;

class SuratKeteranganController extends Controller
{
    // ==================== TEMPLATE SURAT ====================
    public function template(Request $request)
    {
        if ($request->ajax()) {
            $query = TemplateSurat::query();

            return DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('jenis_label', function ($row) {
                    return $row->jenis_label;
                })
                ->addColumn('status', function ($row) {
                    return $row->is_active
                        ? '<span class="badge badge-success">Aktif</span>'
                        : '<span class="badge badge-secondary">Tidak Aktif</span>';
                })
                ->addColumn('action', function ($row) {
                    $btn = '<div class="btn-group">';
                    if (auth()->user()->can('manage-layanan-surat')) {
                        $btn .= '<a href="' . route('admin.surat-keterangan.template.edit', $row->id) . '" class="btn btn-sm btn-warning" title="Edit"><i class="fas fa-edit"></i></a>';
                        $btn .= '<button type="button" class="btn btn-sm btn-danger btn-delete" data-id="' . $row->id . '" title="Hapus"><i class="fas fa-trash"></i></button>';
                    }
                    $btn .= '</div>';
                    return $btn;
                })
                ->rawColumns(['status', 'action'])
                ->make(true);
        }

        $jenis = TemplateSurat::JENIS;

        return view('admin.surat-keterangan.template', compact('jenis'));
    }

    public function createTemplate()
    {
        $jenis = TemplateSurat::JENIS;
        $variabel = TemplateSurat::DEFAULT_VARIABEL;

        return view('admin.surat-keterangan.template-create', compact('jenis', 'variabel'));
    }

    public function storeTemplate(Request $request)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'kode' => 'required|string|max:50|unique:template_surat,kode',
            'jenis' => 'required|string|max:50',
            'konten' => 'required|string',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);
        $validated['created_by'] = Auth::id();

        TemplateSurat::create($validated);

        return redirect()->route('admin.surat-keterangan.template')
            ->with('success', 'Template surat berhasil ditambahkan');
    }

    public function editTemplate(TemplateSurat $template)
    {
        $jenis = TemplateSurat::JENIS;
        $variabel = TemplateSurat::DEFAULT_VARIABEL;

        return view('admin.surat-keterangan.template-edit', compact('template', 'jenis', 'variabel'));
    }

    public function updateTemplate(Request $request, TemplateSurat $template)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'kode' => 'required|string|max:50|unique:template_surat,kode,' . $template->id,
            'jenis' => 'required|string|max:50',
            'konten' => 'required|string',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active');

        $template->update($validated);

        return redirect()->route('admin.surat-keterangan.template')
            ->with('success', 'Template surat berhasil diperbarui');
    }

    public function destroyTemplate(TemplateSurat $template)
    {
        $template->delete();

        return response()->json([
            'success' => true,
            'message' => 'Template surat berhasil dihapus',
        ]);
    }

    // ==================== AJAX SISWA SEARCH & DATA ====================
    public function searchSiswa(Request $request)
    {
        $q = trim($request->input('q', ''));
        if (strlen($q) < 2) {
            return response()->json([]);
        }

        $siswa = Siswa::query()
            ->where(function ($query) use ($q) {
                $query->where('nama_lengkap', 'like', "%{$q}%")
                    ->orWhere('nisn', 'like', "%{$q}%")
                    ->orWhere('nis_lokal', 'like', "%{$q}%");
            })
            ->with(['kelasTahunAktif', 'kelasSaatIni'])
            ->limit(20)
            ->get();

        return response()->json($siswa->map(function ($s) {
            $kelasNama = $s->kelasTahunAktif->first()?->nama_kelas ?? $s->kelasSaatIni?->nama_kelas ?? '-';
            return [
                'id' => $s->id,
                'text' => "{$s->nama_lengkap} (NISN: {$s->nisn}) - Kelas {$kelasNama}",
            ];
        }));
    }

    public function getSiswaData(Siswa $siswa)
    {
        $siswa->load(['ortu', 'kelasTahunAktif', 'kelasSaatIni']);
        $kelas = $siswa->kelasTahunAktif->first()?->nama_kelas ?? $siswa->kelasSaatIni?->nama_kelas ?? '';
        $tapel = TahunPelajaran::where('is_active', true)->first()?->nama ?? '2026/2027';

        return response()->json([
            'id' => $siswa->id,
            'nama_lengkap' => $siswa->nama_lengkap,
            'nisn' => $siswa->nisn,
            'nis' => $siswa->nis ?? $siswa->nis_lokal ?? '',
            'tempat_lahir' => $siswa->tempat_lahir,
            'tanggal_lahir' => $siswa->tanggal_lahir ? Carbon::parse($siswa->tanggal_lahir)->translatedFormat('d F Y') : '',
            'nama_ayah' => $siswa->ortu?->nama_ayah ?? '',
            'nama_ibu' => $siswa->ortu?->nama_ibu ?? '',
            'kelas' => $kelas,
            'tahun_pelajaran' => $tapel,
        ]);
    }

    // ==================== SURAT KETERANGAN ====================
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = SuratKeterangan::with(['template', 'siswa.kelasTahunAktif', 'siswa.kelasSaatIni', 'penandatangan', 'createdBy']);

            return DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('nomor', function ($row) {
                    return $row->nomor_surat ?? '-';
                })
                ->addColumn('siswa_nama', function ($row) {
                    $nama = $row->siswa?->nama_lengkap ?? ($row->data_tambahan['nama_siswa'] ?? '-');
                    $nisn = $row->siswa?->nisn ?? ($row->data_tambahan['nisn'] ?? '');
                    return $nama . ($nisn ? '<br><small class="text-muted">NISN: ' . $nisn . '</small>' : '');
                })
                ->addColumn('kelas', function ($row) {
                    return $row->data_tambahan['kelas'] ?? ($row->siswa?->kelasTahunAktif->first()?->nama_kelas ?? $row->siswa?->kelasSaatIni?->nama_kelas ?? '-');
                })
                ->addColumn('keperluan', function ($row) {
                    return Str::limit($row->keperluan, 40);
                })
                ->addColumn('tanggal', function ($row) {
                    return $row->tanggal_surat?->translatedFormat('d/m/Y') ?? '-';
                })
                ->addColumn('status_badge', function ($row) {
                    return '<span class="badge badge-' . $row->status_badge . '">' . $row->status_label . '</span>';
                })
                ->addColumn('action', function ($row) {
                    $btn = '<div class="btn-group">';
                    $btn .= '<a href="' . route('admin.surat-keterangan.show', $row->id) . '" class="btn btn-sm btn-info" title="Lihat"><i class="fas fa-eye"></i></a>';
                    $btn .= '<a href="' . route('admin.surat-keterangan.print', $row->id) . '" class="btn btn-sm btn-primary" target="_blank" title="Cetak PDF"><i class="fas fa-print"></i></a>';
                    if (auth()->user()->can('manage-layanan-surat')) {
                        $btn .= '<a href="' . route('admin.surat-keterangan.edit', $row->id) . '" class="btn btn-sm btn-warning" title="Edit"><i class="fas fa-edit"></i></a>';
                        $btn .= '<button type="button" class="btn btn-sm btn-danger btn-delete" data-id="' . $row->id . '" title="Hapus"><i class="fas fa-trash"></i></button>';
                    }
                    $btn .= '</div>';
                    return $btn;
                })
                ->rawColumns(['siswa_nama', 'status_badge', 'action'])
                ->make(true);
        }

        $status = SuratKeterangan::STATUS;

        return view('admin.surat-keterangan.index', compact('status'));
    }

    public function create()
    {
        $templates = TemplateSurat::active()->get();
        $defaultTemplate = TemplateSurat::where('kode', 'SK-AKTIF')->first() ?? $templates->first();

        // Get default Kamad / Penandatangan
        $kamadGtk = Gtk::where('status_aktif', true)->where('nip', '197702222005011004')->first();
        if (!$kamadGtk) {
            $kamadGtk = Gtk::where('status_aktif', true)->orderBy('nama_lengkap')->first();
        }

        $gtks = Gtk::where('status_aktif', true)->orderBy('nama_lengkap')->get();
        
        $nomorPreview = app(\App\Services\SuratNumberService::class)->preview('PP');
        $setting = AppSetting::getInstance();
        $tapel = TahunPelajaran::where('is_active', true)->first()?->nama ?? '2026/2027';

        return view('admin.surat-keterangan.create', compact('templates', 'defaultTemplate', 'kamadGtk', 'gtks', 'nomorPreview', 'setting', 'tapel'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'template_surat_id' => 'required|exists:template_surat,id',
            'siswa_id' => 'required|exists:siswa,id',
            'penandatangan_id' => 'required|exists:gtks,id',
            'tanggal_surat' => 'required|date',
            'keperluan' => 'required|string|max:255',
            'status' => 'required|in:draft,menunggu_ttd,selesai',
            'data_tambahan' => 'nullable|array',
        ]);

        $siswa = Siswa::with(['ortu', 'kelasTahunAktif', 'kelasSaatIni'])->findOrFail($validated['siswa_id']);
        $penandatangan = Gtk::findOrFail($validated['penandatangan_id']);
        $template = TemplateSurat::findOrFail($validated['template_surat_id']);
        $setting = AppSetting::getInstance();

        // Generate sequential letter number
        $nomorSurat = SuratKeterangan::generateNomorSurat('PP');

        // Compile data_tambahan
        $kelasDefault = $siswa->kelasTahunAktif->first()?->nama_kelas ?? $siswa->kelasSaatIni?->nama_kelas ?? '-';
        $dataTambahan = array_merge([
            'nama_siswa' => $siswa->nama_lengkap,
            'nisn' => $siswa->nisn,
            'nis' => $siswa->nis ?? $siswa->nis_lokal ?? '',
            'tempat_lahir' => $siswa->tempat_lahir,
            'tanggal_lahir' => $siswa->tanggal_lahir ? Carbon::parse($siswa->tanggal_lahir)->translatedFormat('d F Y') : '',
            'nama_ayah' => $request->input('data_tambahan.nama_ayah', $siswa->ortu?->nama_ayah ?? '-'),
            'nama_ibu' => $request->input('data_tambahan.nama_ibu', $siswa->ortu?->nama_ibu ?? '-'),
            'kelas' => $request->input('data_tambahan.kelas', $kelasDefault),
            'tahun_pelajaran' => $request->input('data_tambahan.tahun_pelajaran', '2026/2027'),
            'nama_pejabat' => $penandatangan->nama_lengkap,
            'nip_pejabat' => $penandatangan->nip ?? '-',
            'pangkat_golongan_pejabat' => $request->input('data_tambahan.pangkat_golongan_pejabat', 'Pembina Tk I / IV(b)'),
            'jabatan_pejabat' => $request->input('data_tambahan.jabatan_pejabat', 'Kepala Madrasah'),
            'instansi_pejabat' => $request->input('data_tambahan.instansi_pejabat', $setting->nama_sekolah ?: 'MAN 1 Metro'),
        ], $request->input('data_tambahan', []));

        // Generate dynamic content
        $replacements = [
            '{{nama_pejabat}}' => $dataTambahan['nama_pejabat'],
            '{{nip_pejabat}}' => $dataTambahan['nip_pejabat'],
            '{{pangkat_golongan_pejabat}}' => $dataTambahan['pangkat_golongan_pejabat'],
            '{{jabatan_pejabat}}' => $dataTambahan['jabatan_pejabat'],
            '{{instansi_pejabat}}' => $dataTambahan['instansi_pejabat'],
            '{{nama_siswa}}' => $dataTambahan['nama_siswa'],
            '{{nisn}}' => $dataTambahan['nisn'],
            '{{nis}}' => $dataTambahan['nis'],
            '{{tempat_lahir}}' => $dataTambahan['tempat_lahir'],
            '{{tanggal_lahir}}' => $dataTambahan['tanggal_lahir'],
            '{{nama_ayah}}' => $dataTambahan['nama_ayah'],
            '{{nama_ibu}}' => $dataTambahan['nama_ibu'],
            '{{nama_sekolah}}' => $setting->nama_sekolah ?: 'MAN 1 Metro',
            '{{kelas}}' => $dataTambahan['kelas'],
            '{{tahun_pelajaran}}' => $dataTambahan['tahun_pelajaran'],
            '{{keperluan}}' => $validated['keperluan'],
            '{{nomor_surat}}' => $nomorSurat,
            '{{tanggal_surat}}' => Carbon::parse($validated['tanggal_surat'])->translatedFormat('d F Y'),
            '{{kota_surat}}' => 'Metro',
        ];

        $kontenGenerate = $template->generateContent($replacements);

        $surat = SuratKeterangan::create([
            'template_surat_id' => $validated['template_surat_id'],
            'siswa_id' => $validated['siswa_id'],
            'penandatangan_id' => $validated['penandatangan_id'],
            'jabatan_penandatangan' => $dataTambahan['jabatan_pejabat'],
            'nomor_surat' => $nomorSurat,
            'tanggal_surat' => $validated['tanggal_surat'],
            'perihal' => 'Surat Keterangan Aktif Siswa',
            'keperluan' => $validated['keperluan'],
            'konten_generate' => $kontenGenerate,
            'status' => $validated['status'],
            'data_tambahan' => $dataTambahan,
            'created_by' => Auth::id(),
        ]);

        return redirect()->route('admin.surat-keterangan.show', $surat->id)
            ->with('success', "Surat keterangan {$nomorSurat} berhasil dibuat!");
    }

    public function show(SuratKeterangan $suratKeterangan)
    {
        $suratKeterangan->load(['template', 'siswa.ortu', 'siswa.kelasTahunAktif', 'penandatangan', 'createdBy']);
        $setting = AppSetting::getInstance();

        return view('admin.surat-keterangan.show', compact('suratKeterangan', 'setting'));
    }

    public function edit(SuratKeterangan $suratKeterangan)
    {
        $suratKeterangan->load(['template', 'siswa.ortu', 'siswa.kelasTahunAktif', 'penandatangan']);
        $templates = TemplateSurat::active()->get();
        $gtks = Gtk::where('status_aktif', true)->orderBy('nama_lengkap')->get();
        $setting = AppSetting::getInstance();

        return view('admin.surat-keterangan.edit', compact('suratKeterangan', 'templates', 'gtks', 'setting'));
    }

    public function update(Request $request, SuratKeterangan $suratKeterangan)
    {
        $validated = $request->validate([
            'template_surat_id' => 'required|exists:template_surat,id',
            'siswa_id' => 'required|exists:siswa,id',
            'penandatangan_id' => 'required|exists:gtks,id',
            'tanggal_surat' => 'required|date',
            'keperluan' => 'required|string|max:255',
            'status' => 'required|in:draft,menunggu_ttd,selesai,dibatalkan',
            'data_tambahan' => 'nullable|array',
        ]);

        $siswa = Siswa::with(['ortu', 'kelasTahunAktif', 'kelasSaatIni'])->findOrFail($validated['siswa_id']);
        $penandatangan = Gtk::findOrFail($validated['penandatangan_id']);
        $template = TemplateSurat::findOrFail($validated['template_surat_id']);
        $setting = AppSetting::getInstance();

        // Compile data_tambahan
        $kelasDefault = $suratKeterangan->data_tambahan['kelas'] ?? ($siswa->kelasTahunAktif->first()?->nama_kelas ?? $siswa->kelasSaatIni?->nama_kelas ?? '-');
        $dataTambahan = array_merge($suratKeterangan->data_tambahan ?? [], [
            'nama_siswa' => $siswa->nama_lengkap,
            'nisn' => $siswa->nisn,
            'nis' => $siswa->nis ?? $siswa->nis_lokal ?? '',
            'tempat_lahir' => $siswa->tempat_lahir,
            'tanggal_lahir' => $siswa->tanggal_lahir ? Carbon::parse($siswa->tanggal_lahir)->translatedFormat('d F Y') : '',
            'nama_ayah' => $request->input('data_tambahan.nama_ayah', $suratKeterangan->data_tambahan['nama_ayah'] ?? ($siswa->ortu?->nama_ayah ?? '-')),
            'nama_ibu' => $request->input('data_tambahan.nama_ibu', $suratKeterangan->data_tambahan['nama_ibu'] ?? ($siswa->ortu?->nama_ibu ?? '-')),
            'kelas' => $request->input('data_tambahan.kelas', $kelasDefault),
            'tahun_pelajaran' => $request->input('data_tambahan.tahun_pelajaran', $suratKeterangan->data_tambahan['tahun_pelajaran'] ?? '2026/2027'),
            'nama_pejabat' => $penandatangan->nama_lengkap,
            'nip_pejabat' => $penandatangan->nip ?? '-',
            'pangkat_golongan_pejabat' => $request->input('data_tambahan.pangkat_golongan_pejabat', $suratKeterangan->data_tambahan['pangkat_golongan_pejabat'] ?? 'Pembina Tk I / IV(b)'),
            'jabatan_pejabat' => $request->input('data_tambahan.jabatan_pejabat', $suratKeterangan->data_tambahan['jabatan_pejabat'] ?? 'Kepala Madrasah'),
            'instansi_pejabat' => $request->input('data_tambahan.instansi_pejabat', $setting->nama_sekolah ?: 'MAN 1 Metro'),
        ], $request->input('data_tambahan', []));

        // Generate dynamic content
        $replacements = [
            '{{nama_pejabat}}' => $dataTambahan['nama_pejabat'],
            '{{nip_pejabat}}' => $dataTambahan['nip_pejabat'],
            '{{pangkat_golongan_pejabat}}' => $dataTambahan['pangkat_golongan_pejabat'],
            '{{jabatan_pejabat}}' => $dataTambahan['jabatan_pejabat'],
            '{{instansi_pejabat}}' => $dataTambahan['instansi_pejabat'],
            '{{nama_siswa}}' => $dataTambahan['nama_siswa'],
            '{{nisn}}' => $dataTambahan['nisn'],
            '{{nis}}' => $dataTambahan['nis'],
            '{{tempat_lahir}}' => $dataTambahan['tempat_lahir'],
            '{{tanggal_lahir}}' => $dataTambahan['tanggal_lahir'],
            '{{nama_ayah}}' => $dataTambahan['nama_ayah'],
            '{{nama_ibu}}' => $dataTambahan['nama_ibu'],
            '{{nama_sekolah}}' => $setting->nama_sekolah ?: 'MAN 1 Metro',
            '{{kelas}}' => $dataTambahan['kelas'],
            '{{tahun_pelajaran}}' => $dataTambahan['tahun_pelajaran'],
            '{{keperluan}}' => $validated['keperluan'],
            '{{nomor_surat}}' => $suratKeterangan->nomor_surat,
            '{{tanggal_surat}}' => Carbon::parse($validated['tanggal_surat'])->translatedFormat('d F Y'),
            '{{kota_surat}}' => 'Metro',
        ];

        $kontenGenerate = $template->generateContent($replacements);

        $suratKeterangan->update([
            'template_surat_id' => $validated['template_surat_id'],
            'siswa_id' => $validated['siswa_id'],
            'penandatangan_id' => $validated['penandatangan_id'],
            'jabatan_penandatangan' => $dataTambahan['jabatan_pejabat'],
            'tanggal_surat' => $validated['tanggal_surat'],
            'keperluan' => $validated['keperluan'],
            'konten_generate' => $kontenGenerate,
            'status' => $validated['status'],
            'data_tambahan' => $dataTambahan,
        ]);

        return redirect()->route('admin.surat-keterangan.show', $suratKeterangan->id)
            ->with('success', 'Surat keterangan berhasil diperbarui');
    }

    public function destroy(SuratKeterangan $suratKeterangan)
    {
        $suratKeterangan->delete();

        return response()->json([
            'success' => true,
            'message' => 'Surat keterangan berhasil dihapus',
        ]);
    }

    public function approve(SuratKeterangan $suratKeterangan)
    {
        $suratKeterangan->update([
            'status' => 'selesai',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Surat keterangan berhasil diselesaikan / disetujui',
        ]);
    }

    public function print(SuratKeterangan $suratKeterangan)
    {
        $suratKeterangan->load(['template', 'siswa.ortu', 'siswa.kelasTahunAktif', 'penandatangan']);
        $setting = AppSetting::getInstance();

        // Increment count
        $suratKeterangan->increment('jumlah_cetak');
        if ($suratKeterangan->status === 'draft' || $suratKeterangan->status === 'menunggu_ttd') {
            $suratKeterangan->update(['status' => 'selesai']);
        }

        $pdf = Pdf::loadView('admin.surat-keterangan.print', [
            'suratKeterangan' => $suratKeterangan,
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

        $cleanNomor = str_replace(['/', '\\', ' '], '_', $suratKeterangan->nomor_surat ?? 'surat');
        return $pdf->stream('Surat_Keterangan_Aktif_' . $cleanNomor . '.pdf');
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