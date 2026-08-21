<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Siswa;
use App\Models\DokumenSiswa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SiswaPipController extends Controller
{
    /**
     * Kata kunci yang dianggap dokumen KIP/SKTM.
     */
    private const KEYWORDS_KIP  = ['kip', 'kartu indonesia pintar'];
    private const KEYWORDS_SKTM = ['sktm', 'tidak mampu', 'keterangan tidak mampu', 'keterangan kurang mampu'];

    /**
     * Semua keyword gabungan untuk query
     */
    private function allKeywords(): array
    {
        return array_merge(self::KEYWORDS_KIP, self::KEYWORDS_SKTM);
    }

    /**
     * Query dasar: siswa yang punya dokumen KIP/SKTM atau nomor PKH.
     */
    private function baseQuery()
    {
        $query = Siswa::query();
        $this->applyAssistanceFilter($query);

        return $query;
    }

    /**
     * Halaman utama daftar siswa KIP/SKTM
     */
    public function index()
    {
        $this->authorize('view-pip');

        $base = $this->baseQuery();

        $stats = [
            'total'      => (clone $base)->count(),
            'kip'        => (clone $base)->whereHas('dokumen', fn($q) => $this->filterByType($q, 'kip'))->count(),
            'sktm'       => (clone $base)->whereHas('dokumen', fn($q) => $this->filterByType($q, 'sktm'))->count(),
            'pkh'        => (clone $base)->whereNotNull('nomor_pkh')->where('nomor_pkh', '!=', '')->count(),
            'laki_laki'  => (clone $base)->where('jenis_kelamin', 'L')->count(),
            'perempuan'  => (clone $base)->where('jenis_kelamin', 'P')->count(),
        ];

        $tingkatOptions = [10 => 'Kelas X', 11 => 'Kelas XI', 12 => 'Kelas XII'];

        $kelasOptions = \App\Models\Kelas::where('is_active', true)
            ->orderBy('tingkat')->orderBy('nama_kelas')
            ->get(['id', 'nama_kelas', 'tingkat']);

        return view('admin.pip.index', compact('stats', 'tingkatOptions', 'kelasOptions'));
    }

    /**
     * DataTable AJAX endpoint
     */
    public function data(Request $request)
    {
        $this->authorize('view-pip');

        $query = Siswa::with(['kelasAktif', 'dokumen'])
            ->withCount(['dokumen as kip_count' => function ($q) {
                $this->filterByType($q, 'kip');
            }])
            ->withCount(['dokumen as sktm_count' => function ($q) {
                $this->filterByType($q, 'sktm');
            }]);

        // Hanya siswa yang punya dokumen KIP/SKTM atau nomor PKH
        $this->applyAssistanceFilter($query);

        // Filter jenis bantuan
        if ($request->filled('jenis') && in_array($request->jenis, ['kip', 'sktm'], true)) {
            $query->whereHas('dokumen', fn($q) => $this->filterByType($q, $request->jenis));
        } elseif ($request->jenis === 'pkh') {
            $query->whereNotNull('nomor_pkh')->where('nomor_pkh', '!=', '');
        }

        // Filter tingkat
        if ($request->filled('tingkat')) {
            $query->whereHas('kelasAktif', fn($q) => $q->where('kelas.tingkat', $request->tingkat));
        }

        // Filter kelas
        if ($request->filled('kelas_id')) {
            $query->whereHas('kelasAktif', fn($q) => $q->where('kelas.id', $request->kelas_id));
        }

        // Search
        if ($request->filled('search') && $request->search['value']) {
            $search = $request->search['value'];
            $query->where(function ($q) use ($search) {
                $q->where('nama_lengkap', 'like', "%{$search}%")
                  ->orWhere('nisn', 'like', "%{$search}%")
                  ->orWhere('nik', 'like', "%{$search}%")
                  ->orWhere('nomor_pkh', 'like', "%{$search}%");
            });
        }

        $totalFiltered = $query->count();

        // Order
        $query->orderBy('nama_lengkap');

        // Pagination
        if ($request->filled('length') && $request->length != -1) {
            $query->skip((int) $request->start)->take((int) $request->length);
        }

        $data = $query->get()->map(function ($siswa) {
            $kelas      = $siswa->kelasAktif()->first();
            $kelasNama  = $kelas ? $kelas->nama_kelas : '<em class="text-muted">Tanpa Rombel</em>';

            // Kumpulkan dokumen KIP/SKTM milik siswa ini
            $dokumenKip = $siswa->dokumen->filter(fn($d) => $this->isDokumenType($d->jenis_dokumen, 'kip'));
            $dokumenSktm= $siswa->dokumen->filter(fn($d) => $this->isDokumenType($d->jenis_dokumen, 'sktm'));

            $dokumenHtml = $this->renderDokumenList($dokumenKip, $dokumenSktm);

            return [
                'id'             => $siswa->id,
                'nisn'           => $siswa->nisn ?? '-',
                'nama_lengkap'   => e($siswa->nama_lengkap),
                'jenis_kelamin'  => $siswa->jenis_kelamin === 'L' ? 'Laki-laki' : 'Perempuan',
                'kelas'          => $kelasNama,
                'dokumen'        => $dokumenHtml ?: '-',
                'nomor_pkh'      => $siswa->nomor_pkh ? e($siswa->nomor_pkh) : '<span class="text-muted">-</span>',
                'total_dokumen'  => $dokumenKip->count() + $dokumenSktm->count(),
                'actions'        => $this->getActionButtons($siswa),
            ];
        });

        return response()->json([
            'draw'            => intval($request->draw),
            'recordsTotal'    => $this->baseQuery()->count(),
            'recordsFiltered' => $totalFiltered,
            'data'            => $data,
        ]);
    }

    // ─── Helpers ──────────────────────────────────────────────────────────────

    private function filterByType($query, string $type)
    {
        $keywords = $type === 'kip' ? self::KEYWORDS_KIP : self::KEYWORDS_SKTM;
        $query->where(function ($inner) use ($keywords) {
            foreach ($keywords as $kw) {
                $inner->orWhere('jenis_dokumen', 'like', "%{$kw}%");
            }
        });

        return $query;
    }

    private function applyAssistanceFilter($query)
    {
        $keywords = $this->allKeywords();

        return $query->where(function ($outer) use ($keywords) {
            $outer->whereHas('dokumen', function ($q) use ($keywords) {
                $q->where(function ($inner) use ($keywords) {
                    foreach ($keywords as $kw) {
                        $inner->orWhere('jenis_dokumen', 'like', "%{$kw}%");
                    }
                });
            })->orWhere(function ($q) {
                $q->whereNotNull('nomor_pkh')->where('nomor_pkh', '!=', '');
            });
        });
    }

    private function isDokumenType(string $jenisDokumen, string $type): bool
    {
        $keywords = $type === 'kip' ? self::KEYWORDS_KIP : self::KEYWORDS_SKTM;
        $lower    = strtolower($jenisDokumen);
        foreach ($keywords as $kw) {
            if (str_contains($lower, $kw)) {
                return true;
            }
        }

        return false;
    }

    private function renderDokumenList($dokumenKip, $dokumenSktm): string
    {
        $html = '';

        if ($dokumenKip->isNotEmpty()) {
            $html .= '<div class="mb-1"><span class="badge badge-success mr-1"><i class="fas fa-id-card mr-1"></i>KIP (' . $dokumenKip->count() . ')</span>';
            $html .= $this->renderDokumenLinks($dokumenKip);
            $html .= '</div>';
        }

        if ($dokumenSktm->isNotEmpty()) {
            $html .= '<div><span class="badge badge-warning text-dark mr-1"><i class="fas fa-file-alt mr-1"></i>SKTM (' . $dokumenSktm->count() . ')</span>';
            $html .= $this->renderDokumenLinks($dokumenSktm);
            $html .= '</div>';
        }

        return $html;
    }

    private function renderDokumenLinks($dokumen): string
    {
        return $dokumen->values()->map(function (DokumenSiswa $item, int $index) {
            $label = $item->original_name ?: $item->nama_file ?: ('Dokumen ' . ($index + 1));
            $previewUrl = route('siswa.dokumen.preview', $item->id);
            $downloadUrl = route('siswa.dokumen.download', $item->id);
            $extension = $item->getFileExtension();
            $uploadedAt = $item->created_at?->format('d/m/Y H:i') ?? '-';
            $updatedAt = $item->updated_at?->format('d/m/Y H:i') ?? '-';
            $updatedLabel = $item->updated_at && ! $item->updated_at->equalTo($item->created_at)
                ? ' • Diperbarui: '.$updatedAt
                : '';

            return '<span class="d-inline-block mr-1 mb-1"><button type="button"
                        class="btn btn-outline-info btn-xs mr-1 js-preview-admin-dokumen"
                        data-preview-url="' . e($previewUrl) . '"
                        data-download-url="' . e($downloadUrl) . '"
                        data-title="' . e($label) . '"
                        data-extension="' . e($extension) . '"
                        data-mime-type="' . e($item->mime_type) . '"
                        data-uploaded-at="' . e($uploadedAt) . '"
                        data-updated-at="' . e($updatedAt) . '"
                        title="' . e($label) . '">
                        <i class="fas fa-eye"></i> Lihat
                    </button><small class="text-muted d-block"><i class="far fa-clock"></i> Diunggah: ' . e($uploadedAt) . e($updatedLabel) . '</small></span>';
        })->implode('');
    }

    private function getActionButtons(Siswa $siswa): string
    {
        $detailUrl = route('admin.siswa.show', $siswa);

        return '<a href="' . $detailUrl . '" class="btn btn-info btn-xs" title="Detail Siswa">
                    <i class="fas fa-user"></i> Detail
                </a>';
    }
}
