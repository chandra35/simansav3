<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Siswa;
use App\Models\DokumenSiswa;
use App\Models\TahunPelajaran;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SiswaPipController extends Controller
{
    /**
     * Kata kunci yang dianggap dokumen KIP/SKTM.
     */
    private const KEYWORDS_KIP  = ['kip', 'kartu indonesia pintar'];
    private const KEYWORDS_PKH  = ['pkh', 'kks', 'kartu kesejahteraan sosial'];
    private const KEYWORDS_SKTM = ['sktm', 'tidak mampu', 'keterangan tidak mampu', 'keterangan kurang mampu'];

    /**
     * Semua keyword gabungan untuk query
     */
    private function allKeywords(): array
    {
        return array_merge(self::KEYWORDS_KIP, self::KEYWORDS_PKH, self::KEYWORDS_SKTM);
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
            'pkh'        => (clone $base)->where(function ($q) {
                $q->whereNotNull('nomor_pkh')->where('nomor_pkh', '!=', '')
                    ->orWhereHas('dokumen', fn($dokumen) => $this->filterByType($dokumen, 'pkh'));
            })->count(),
            'laki_laki'  => (clone $base)->where('jenis_kelamin', 'L')->count(),
            'perempuan'  => (clone $base)->where('jenis_kelamin', 'P')->count(),
        ];

        $tingkatOptions = [10 => 'Kelas X', 11 => 'Kelas XI', 12 => 'Kelas XII'];

        // Kelas dari tahun lama dapat tetap aktif sebagai arsip. Pilihan filter
        // harus mengikuti rombel tahun pelajaran berjalan agar tidak dobel.
        $kelasOptions = \App\Models\Kelas::where('is_active', true)
            ->whereIn('tahun_pelajaran_id', TahunPelajaran::query()->active()->select('id'))
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

        $query = Siswa::with(['kelasTahunAktif', 'dokumen'])
            ->withCount(['dokumen as kip_count' => function ($q) {
                $this->filterByType($q, 'kip');
            }])
            ->withCount(['dokumen as sktm_count' => function ($q) {
                $this->filterByType($q, 'sktm');
            }])
            ->withCount(['dokumen as pkh_count' => function ($q) {
                $this->filterByType($q, 'pkh');
            }]);

        // Hanya siswa yang punya dokumen KIP/SKTM atau nomor PKH
        $this->applyAssistanceFilter($query);

        // Filter jenis bantuan
        if ($request->filled('jenis') && in_array($request->jenis, ['kip', 'sktm'], true)) {
            $query->whereHas('dokumen', fn($q) => $this->filterByType($q, $request->jenis));
        } elseif ($request->jenis === 'pkh') {
            $query->where(function ($q) {
                $q->whereNotNull('nomor_pkh')->where('nomor_pkh', '!=', '')
                    ->orWhereHas('dokumen', fn($dokumen) => $this->filterByType($dokumen, 'pkh'));
            });
        }

        // Filter tingkat
        if ($request->filled('tingkat')) {
            $query->whereHas('kelasTahunAktif', fn($q) => $q->where('kelas.tingkat', $request->tingkat));
        }

        // Filter kelas
        if ($request->filled('kelas_id')) {
            $query->whereHas('kelasTahunAktif', fn($q) => $q->where('kelas.id', $request->kelas_id));
        }

        // Search
        if ($request->filled('search') && $request->search['value']) {
            $search = $request->search['value'];
            $query->where(function ($q) use ($search) {
                $q->where('nama_lengkap', 'like', "%{$search}%")
                  ->orWhere('nisn', 'like', "%{$search}%")
                  ->orWhere('nik', 'like', "%{$search}%")
                  ->orWhere('nomor_kip', 'like', "%{$search}%")
                  ->orWhere('nomor_pkh', 'like', "%{$search}%");
            });
        }

        $totalFiltered = $query->count();

        // DataTables sends the column index, so only allow an explicit map of
        // real database fields. This keeps every displayed data column sortable
        // without accepting arbitrary SQL from the request.
        $orderColumn = (int) $request->input('order.0.column', -1);
        $orderDirection = strtolower((string) $request->input('order.0.dir', 'asc')) === 'desc' ? 'desc' : 'asc';

        switch ($orderColumn) {
            case 1:
                $query->orderBy('siswa.nama_lengkap', $orderDirection);
                break;
            case 2:
                $query->orderByRaw('(kip_count + pkh_count + sktm_count) ' . $orderDirection);
                break;
            case 3:
                $query->orderBy('siswa.nomor_pkh', $orderDirection);
                break;
            case 4:
                $query->orderBy('siswa.bantuan_emis_updated', $orderDirection)
                    ->orderBy('siswa.bantuan_emis_updated_at', $orderDirection);
                break;
            default:
                $query->orderBy('siswa.nama_lengkap')
                    ->orderBy('siswa.id');
                break;
        }

        // Pagination
        if ($request->filled('length') && $request->length != -1) {
            $query->skip((int) $request->start)->take((int) $request->length);
        }

        $data = $query->get()->map(function ($siswa) {
            $kelas      = $siswa->kelasTahunAktif->first();

            // Kumpulkan dokumen KIP/SKTM milik siswa ini
            $dokumenKip = $siswa->dokumen->filter(fn($d) => $this->isDokumenType($d->jenis_dokumen, 'kip'));
            $dokumenPkh = $siswa->dokumen->filter(fn($d) => $this->isDokumenType($d->jenis_dokumen, 'pkh'));
            $dokumenSktm= $siswa->dokumen->filter(fn($d) => $this->isDokumenType($d->jenis_dokumen, 'sktm'));

            $dokumenHtml = $this->renderDokumenList($dokumenKip, $dokumenPkh, $dokumenSktm);

            return [
                'id'             => $siswa->id,
                'nama_lengkap'   => $this->studentNameMetadata($siswa, $kelas),
                'dokumen'        => $dokumenHtml ?: '-',
                'nomor_pkh'      => $siswa->nomor_pkh ? e($siswa->nomor_pkh) : '<span class="text-muted">-</span>',
                'assistance_follow_up' => $this->renderAssistanceFollowUpStatus($siswa),
                'total_dokumen'  => $dokumenKip->count() + $dokumenPkh->count() + $dokumenSktm->count(),
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

    /**
     * Tandai tindak lanjut pengajuan bantuan pada modul ini.
     */
    public function toggleAssistanceFollowUp(Siswa $siswa)
    {
        $this->authorize('view-pip');
        $this->authorize('edit-siswa');

        abort_unless($this->baseQuery()->whereKey($siswa->id)->exists(), 404);

        $previousStatus = (bool) $siswa->bantuan_emis_updated;
        $siswa->bantuan_emis_updated = ! $previousStatus;
        $siswa->bantuan_emis_updated_at = $siswa->bantuan_emis_updated ? now() : null;
        $siswa->bantuan_emis_updated_by = $siswa->bantuan_emis_updated ? Auth::id() : null;
        $siswa->save();

        ActivityLog::create([
            'user_id' => Auth::id(),
            'activity_type' => 'update_bantuan_follow_up_status',
            'model_type' => Siswa::class,
            'model_id' => $siswa->id,
            'description' => sprintf(
                'Mengubah penanda tindak lanjut pengajuan bantuan untuk %s (%s) menjadi %s.',
                $siswa->nama_lengkap,
                $siswa->nisn,
                $siswa->bantuan_emis_updated ? 'Sudah' : 'Belum'
            ),
            'old_values' => ['bantuan_emis_updated' => $previousStatus],
            'new_values' => ['bantuan_emis_updated' => (bool) $siswa->bantuan_emis_updated],
            'changed_fields' => ['bantuan_emis_updated'],
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'url' => request()->fullUrl(),
            'method' => request()->method(),
        ]);

        return response()->json([
            'success' => true,
            'bantuan_followed_up' => (bool) $siswa->bantuan_emis_updated,
            'marked_at' => $siswa->bantuan_emis_updated_at?->format('d/m/Y H:i'),
            'message' => $siswa->bantuan_emis_updated
                ? 'Pengajuan bantuan ditandai sudah ditindaklanjuti.'
                : 'Penanda tindak lanjut pengajuan dibatalkan.',
        ]);
    }

    // ─── Helpers ──────────────────────────────────────────────────────────────

    private function filterByType($query, string $type)
    {
        $keywords = match ($type) {
            'kip' => self::KEYWORDS_KIP,
            'pkh' => self::KEYWORDS_PKH,
            default => self::KEYWORDS_SKTM,
        };
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
        $keywords = match ($type) {
            'kip' => self::KEYWORDS_KIP,
            'pkh' => self::KEYWORDS_PKH,
            default => self::KEYWORDS_SKTM,
        };
        $lower    = strtolower($jenisDokumen);
        foreach ($keywords as $kw) {
            if (str_contains($lower, $kw)) {
                return true;
            }
        }

        return false;
    }

    private function renderDokumenList($dokumenKip, $dokumenPkh, $dokumenSktm): string
    {
        $html = '';

        if ($dokumenKip->isNotEmpty()) {
            $html .= '<div class="pip-document-group"><span class="badge badge-success"><i class="fas fa-id-card mr-1"></i>KIP (' . $dokumenKip->count() . ')</span>';
            $html .= $this->renderDokumenLinks($dokumenKip);
            $html .= '</div>';
        }

        if ($dokumenPkh->isNotEmpty()) {
            $html .= '<div class="pip-document-group"><span class="badge badge-info"><i class="fas fa-hand-holding-heart mr-1"></i>KKS/PKH (' . $dokumenPkh->count() . ')</span>';
            $html .= $this->renderDokumenLinks($dokumenPkh);
            $html .= '</div>';
        }

        if ($dokumenSktm->isNotEmpty()) {
            $html .= '<div class="pip-document-group"><span class="badge badge-warning text-dark"><i class="fas fa-file-alt mr-1"></i>SKTM (' . $dokumenSktm->count() . ')</span>';
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

            return '<div class="pip-document-entry"><button type="button"
                        class="btn btn-outline-info btn-xs js-preview-admin-dokumen"
                        data-preview-url="' . e($previewUrl) . '"
                        data-download-url="' . e($downloadUrl) . '"
                        data-title="' . e($label) . '"
                        data-extension="' . e($extension) . '"
                        data-mime-type="' . e($item->mime_type) . '"
                        data-uploaded-at="' . e($uploadedAt) . '"
                        data-updated-at="' . e($updatedAt) . '"
                        title="' . e($label) . '">
                        <i class="fas fa-eye"></i> Lihat
                    </button><small class="text-muted"><i class="far fa-clock"></i> Diunggah: ' . e($uploadedAt) . e($updatedLabel) . '</small></div>';
        })->implode('');
    }

    private function getActionButtons(Siswa $siswa): string
    {
        $detailUrl = route('admin.siswa.show', $siswa);

        return '<a href="' . e($detailUrl) . '" class="btn btn-outline-info btn-xs" title="Buka halaman detail siswa">
                    <i class="fas fa-external-link-alt"></i>
                </a>';
    }

    private function renderAssistanceFollowUpStatus(Siswa $siswa): string
    {
        $isFollowedUp = (bool) $siswa->bantuan_emis_updated;
        $markedAt = $siswa->bantuan_emis_updated_at?->format('d/m/Y H:i');
        $markedDetail = '<small class="d-block text-muted mt-1 pip-assistance-follow-up-meta' . ($markedAt ? '' : ' d-none') . '">' . e($markedAt ?: '') . '</small>';

        if (! Auth::user()?->can('edit-siswa')) {
            return $isFollowedUp
                ? '<span class="badge badge-success" title="Pengajuan bantuan sudah ditindaklanjuti"><i class="fas fa-check-circle mr-1"></i>Sudah</span>' . $markedDetail
                : '<span class="badge badge-secondary" title="Pengajuan bantuan belum ditindaklanjuti"><i class="far fa-circle mr-1"></i>Belum</span>';
        }

        $toggleUrl = route('admin.kip-sktm.toggle-assistance-follow-up', $siswa);
        $title = $isFollowedUp
            ? 'Pengajuan bantuan sudah ditindaklanjuti' . ($markedAt ? " pada {$markedAt}" : '') . ' - klik untuk batalkan'
            : 'Klik setelah pengajuan bantuan siswa ditindaklanjuti';

        return '<button type="button" class="btn btn-xs btn-toggle-assistance-follow-up ' . ($isFollowedUp ? 'btn-success' : 'btn-outline-secondary') . '"
                    data-url="' . e($toggleUrl) . '"
                    title="' . e($title) . '">
                    <i class="fas ' . ($isFollowedUp ? 'fa-check-circle' : 'fa-check') . ' mr-1"></i>'
                    . ($isFollowedUp ? 'Sudah' : 'Tandai') .
                '</button>' . $markedDetail;
    }

    private function studentNameMetadata(Siswa $siswa, $kelas): string
    {
        $detailUrl = route('admin.siswa.show', $siswa);
        $gender = $siswa->jenis_kelamin === 'L' ? 'Laki-laki' : 'Perempuan';
        $kelasName = $kelas?->nama_kelas ?: 'Tanpa rombel';

        return '<div class="pip-student-metadata">'
            . '<button type="button" class="btn btn-link p-0 text-left font-weight-bold js-pip-student-detail"
                    data-detail-url="' . e($detailUrl) . '"
                    data-full-detail-url="' . e($detailUrl) . '">'
                    . e($siswa->nama_lengkap) .
                '</button>'
            . '<div class="pip-student-metadata__items">'
                . '<div class="pip-student-metadata__line"><i class="fas fa-id-card"></i><span class="pip-student-metadata__label">NISN</span><span>' . e($siswa->nisn ?: '-') . '</span></div>'
                . '<div class="pip-student-metadata__line"><i class="fas fa-' . ($siswa->jenis_kelamin === 'L' ? 'mars' : 'venus') . '"></i><span class="pip-student-metadata__label">JK</span><span>' . e($gender) . '</span></div>'
                . '<div class="pip-student-metadata__line"><i class="fas fa-school"></i><span class="pip-student-metadata__label">Kelas</span><span>' . e($kelasName) . '</span></div>'
            . '</div>'
        . '</div>';
    }
}
