<?php

namespace App\Http\Controllers\Admin;

use App\Exports\SiswaYatimExport;
use App\Http\Controllers\Controller;
use App\Models\Kelas;
use App\Models\PendaftaranPpdb;
use App\Models\Siswa;
use App\Models\TahunPelajaran;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class SiswaYatimController extends Controller
{
    /**
     * Query dasar untuk siswa kategori Yatim / Piatu / Yatim Piatu.
     */
    private function baseQuery(): Builder
    {
        return Siswa::query()
            ->whereHas('ortu', function (Builder $q) {
                $q->where('status_ayah', 'meninggal')
                    ->orWhere('status_ibu', 'meninggal');
            });
    }

    /**
     * Terapkan filter kategori yatim/piatu pada query.
     */
    private function applyKategoriFilter(Builder $query, ?string $kategori): void
    {
        if (! $kategori || $kategori === 'all' || $kategori === 'semua') {
            return;
        }

        $query->whereHas('ortu', function (Builder $q) use ($kategori) {
            match ($kategori) {
                'yatim' => $q->where('status_ayah', 'meninggal')
                    ->where(function (Builder $sub) {
                        $sub->where('status_ibu', '!=', 'meninggal')
                            ->orWhereNull('status_ibu');
                    }),
                'piatu' => $q->where('status_ibu', 'meninggal')
                    ->where(function (Builder $sub) {
                        $sub->where('status_ayah', '!=', 'meninggal')
                            ->orWhereNull('status_ayah');
                    }),
                'yatim_piatu' => $q->where('status_ayah', 'meninggal')
                    ->where('status_ibu', 'meninggal'),
                default => null,
            };
        });
    }

    /**
     * Terapkan filter umum (status siswa, tingkat, kelas, JK, bantuan, pencarian).
     */
    private function applyFilters(Builder $query, Request $request): void
    {
        // Filter status siswa (default aktif)
        $statusSiswa = $request->input('status_siswa', 'aktif');
        if ($statusSiswa && $statusSiswa !== 'all' && $statusSiswa !== 'semua') {
            $query->where('status_siswa', $statusSiswa);
        }

        // Filter kategori yatim/piatu
        $this->applyKategoriFilter($query, $request->input('kategori'));

        // Filter tingkat
        if ($request->filled('tingkat')) {
            $query->whereHas('kelasTahunAktif', fn(Builder $q) => $q->where('kelas.tingkat', $request->tingkat));
        }

        // Filter kelas / rombel
        if ($request->filled('kelas_id')) {
            $query->whereHas('kelasTahunAktif', fn(Builder $q) => $q->where('kelas.id', $request->kelas_id));
        }

        // Filter jenis kelamin
        if ($request->filled('jenis_kelamin') && in_array($request->jenis_kelamin, ['L', 'P'], true)) {
            $query->where('jenis_kelamin', $request->jenis_kelamin);
        }

        // Filter kepemilikan bantuan (KIP / PKH / SKTM)
        if ($request->filled('bantuan')) {
            if ($request->bantuan === 'ya') {
                $query->where(function (Builder $q) {
                    $q->whereNotNull('nomor_kip')->where('nomor_kip', '!=', '')
                        ->orWhereNotNull('nomor_pkh')->where('nomor_pkh', '!=', '')
                        ->orWhereHas('dokumen', function (Builder $d) {
                            $d->where(function (Builder $sub) {
                                $sub->where('jenis_dokumen', 'like', '%kip%')
                                    ->orWhere('jenis_dokumen', 'like', '%pkh%')
                                    ->orWhere('jenis_dokumen', 'like', '%kks%')
                                    ->orWhere('jenis_dokumen', 'like', '%sktm%');
                            });
                        });
                });
            } elseif ($request->bantuan === 'tidak') {
                $query->where(function (Builder $q) {
                    $q->where(function (Builder $sub) {
                        $sub->whereNull('nomor_kip')->orWhere('nomor_kip', '');
                    })
                    ->where(function (Builder $sub) {
                        $sub->whereNull('nomor_pkh')->orWhere('nomor_pkh', '');
                    })
                    ->whereDoesntHave('dokumen', function (Builder $d) {
                        $d->where(function (Builder $sub) {
                            $sub->where('jenis_dokumen', 'like', '%kip%')
                                ->orWhere('jenis_dokumen', 'like', '%pkh%')
                                ->orWhere('jenis_dokumen', 'like', '%kks%')
                                ->orWhere('jenis_dokumen', 'like', '%sktm%');
                        });
                    });
                });
            }
        }

        // Pencarian teks
        $searchValue = $request->input('search.value') ?? $request->input('search');
        if (! empty($searchValue) && is_string($searchValue)) {
            $search = trim($searchValue);
            $query->where(function (Builder $q) use ($search) {
                $q->where('nama_lengkap', 'like', "%{$search}%")
                    ->orWhere('nisn', 'like', "%{$search}%")
                    ->orWhere('nik', 'like', "%{$search}%")
                    ->orWhere('nomor_kip', 'like', "%{$search}%")
                    ->orWhere('nomor_pkh', 'like', "%{$search}%")
                    ->orWhereHas('ortu', function (Builder $oq) use ($search) {
                        $oq->where('nama_ayah', 'like', "%{$search}%")
                            ->orWhere('nama_ibu', 'like', "%{$search}%")
                            ->orWhere('hp_ayah', 'like', "%{$search}%")
                            ->orWhere('hp_ibu', 'like', "%{$search}%")
                            ->orWhere('no_kk', 'like', "%{$search}%")
                            ->orWhere('alamat_ortu', 'like', "%{$search}%");
                    });
            });
        }
    }

    /**
     * Tampilan utama halaman monitoring Siswa Yatim/Piatu.
     */
    public function index()
    {
        $this->authorize('view-siswa-yatim');

        // Statistik siswa aktif
        $activeBase = $this->baseQuery()->where('status_siswa', 'aktif');

        $stats = [
            'total'       => (clone $activeBase)->count(),
            'yatim'       => (clone $activeBase)->whereHas('ortu', fn(Builder $q) => $q->where('status_ayah', 'meninggal')->where(fn(Builder $s) => $s->where('status_ibu', '!=', 'meninggal')->orWhereNull('status_ibu')))->count(),
            'piatu'       => (clone $activeBase)->whereHas('ortu', fn(Builder $q) => $q->where('status_ibu', 'meninggal')->where(fn(Builder $s) => $s->where('status_ayah', '!=', 'meninggal')->orWhereNull('status_ayah')))->count(),
            'yatim_piatu' => (clone $activeBase)->whereHas('ortu', fn(Builder $q) => $q->where('status_ayah', 'meninggal')->where('status_ibu', 'meninggal'))->count(),
            'laki_laki'   => (clone $activeBase)->where('jenis_kelamin', 'L')->count(),
            'perempuan'   => (clone $activeBase)->where('jenis_kelamin', 'P')->count(),
            'bantuan'     => (clone $activeBase)->where(function (Builder $q) {
                $q->whereNotNull('nomor_kip')->where('nomor_kip', '!=', '')
                    ->orWhereNotNull('nomor_pkh')->where('nomor_pkh', '!=', '');
            })->count(),
            'total_semua' => $this->baseQuery()->count(),
        ];

        $tingkatOptions = [10 => 'Kelas X', 11 => 'Kelas XI', 12 => 'Kelas XII'];

        $kelasOptions = Kelas::where('is_active', true)
            ->whereIn('tahun_pelajaran_id', TahunPelajaran::query()->active()->select('id'))
            ->orderBy('tingkat')
            ->orderBy('nama_kelas')
            ->get(['id', 'nama_kelas', 'tingkat']);

        return view('admin.siswa-yatim.index', compact('stats', 'tingkatOptions', 'kelasOptions'));
    }

    /**
     * DataTables server-side JSON endpoint.
     */
    public function data(Request $request)
    {
        $this->authorize('view-siswa-yatim');

        $query = $this->baseQuery()
            ->with([
                'ortu.provinsi',
                'ortu.kabupaten',
                'ortu.kecamatan',
                'ortu.kelurahan',
                'kelasTahunAktif',
                'dokumen',
            ]);

        $this->applyFilters($query, $request);

        $recordsFiltered = (clone $query)->count();

        // Ordering
        $orderColumnIndex = (int) $request->input('order.0.column', -1);
        $orderDirection = strtolower((string) $request->input('order.0.dir', 'asc')) === 'desc' ? 'desc' : 'asc';

        match ($orderColumnIndex) {
            1 => $query->orderBy('nama_lengkap', $orderDirection),
            2 => $query->orderBy('jenis_kelamin', $orderDirection),
            3 => $query->orderBy('status_siswa', $orderDirection),
            default => $query->orderBy('nama_lengkap', 'asc')->orderBy('id', 'asc'),
        };

        // Pagination
        if ($request->filled('length') && (int) $request->length !== -1) {
            $query->skip((int) $request->start)->take((int) $request->length);
        }

        $records = $query->get();

        $data = $records->map(function (Siswa $siswa, int $idx) use ($request) {
            $ortu = $siswa->ortu;
            $kelas = $siswa->kelasTahunAktif->first();

            $statusAyah = $ortu?->status_ayah ?? '';
            $statusIbu = $ortu?->status_ibu ?? '';

            $kategoriKey = match (true) {
                $statusAyah === 'meninggal' && $statusIbu === 'meninggal' => 'yatim_piatu',
                $statusAyah === 'meninggal' => 'yatim',
                $statusIbu === 'meninggal' => 'piatu',
                default => 'lainnya',
            };

            $kategoriBadge = match ($kategoriKey) {
                'yatim_piatu' => '<span class="badge badge-danger px-2 py-1"><i class="fas fa-heart-broken mr-1"></i>Yatim Piatu</span>',
                'yatim'       => '<span class="badge badge-warning text-dark px-2 py-1"><i class="fas fa-male mr-1"></i>Yatim (Ayah)</span>',
                'piatu'       => '<span class="badge badge-purple text-white px-2 py-1" style="background:#8b5cf6;"><i class="fas fa-female mr-1"></i>Piatu (Ibu)</span>',
                default       => '<span class="badge badge-secondary px-2 py-1">-</span>',
            };

            $statusSiswaBadge = match ($siswa->status_siswa) {
                'aktif'         => '<span class="badge badge-success">Aktif</span>',
                'lulus'         => '<span class="badge badge-info">Lulus</span>',
                'mutasi_keluar' => '<span class="badge badge-danger">Mutasi</span>',
                default         => '<span class="badge badge-secondary">' . e(ucfirst((string) $siswa->status_siswa)) . '</span>',
            };

            $kelasLabel = $kelas ? e($kelas->nama_kelas) : '<span class="text-muted font-italic">Tanpa Rombel</span>';

            $bantuanBadges = [];
            if ($siswa->nomor_kip) {
                $bantuanBadges[] = '<span class="badge badge-success mb-1" title="No. KIP: ' . e($siswa->nomor_kip) . '"><i class="fas fa-id-card mr-1"></i>KIP</span>';
            }
            if ($siswa->nomor_pkh) {
                $bantuanBadges[] = '<span class="badge badge-info mb-1" title="No. PKH: ' . e($siswa->nomor_pkh) . '"><i class="fas fa-hand-holding-heart mr-1"></i>PKH</span>';
            }
            $dokumenCount = $siswa->dokumen->filter(function ($d) {
                $lower = strtolower($d->jenis_dokumen);
                return str_contains($lower, 'kip') || str_contains($lower, 'pkh') || str_contains($lower, 'sktm');
            })->count();
            if ($dokumenCount > 0) {
                $bantuanBadges[] = '<span class="badge badge-secondary mb-1" title="' . $dokumenCount . ' Dokumen Terkait"><i class="fas fa-file-alt mr-1"></i>' . $dokumenCount . ' Berkas</span>';
            }
            $bantuanHtml = ! empty($bantuanBadges) ? implode(' ', $bantuanBadges) : '<span class="text-muted">-</span>';

            $ayahInfo = '-';
            if ($ortu) {
                $ayahStatus = $statusAyah === 'meninggal' ? '<span class="text-danger font-weight-bold">[Alm]</span> ' : '';
                $ayahNama = e($ortu->nama_ayah ?: '-');
                $ayahPekerjaan = $ortu->pekerjaan_ayah ? '<div class="text-muted small"><i class="fas fa-briefcase mr-1"></i>' . e($ortu->pekerjaan_ayah) . '</div>' : '';
                $ayahInfo = '<div>' . $ayahStatus . $ayahNama . '</div>' . $ayahPekerjaan;
            }

            $ibuInfo = '-';
            if ($ortu) {
                $ibuStatus = $statusIbu === 'meninggal' ? '<span class="text-danger font-weight-bold">[Almh]</span> ' : '';
                $ibuNama = e($ortu->nama_ibu ?: '-');
                $ibuPekerjaan = $ortu->pekerjaan_ibu ? '<div class="text-muted small"><i class="fas fa-briefcase mr-1"></i>' . e($ortu->pekerjaan_ibu) . '</div>' : '';
                $ibuInfo = '<div>' . $ibuStatus . $ibuNama . '</div>' . $ibuPekerjaan;
            }

            $kontakOrtu = '-';
            if ($ortu) {
                $phones = [];
                if ($ortu->hp_ayah) {
                    $phones[] = '<a href="tel:' . e($ortu->hp_ayah) . '" class="text-secondary small d-block"><i class="fas fa-phone mr-1"></i>Ayah: ' . e($ortu->hp_ayah) . '</a>';
                }
                if ($ortu->hp_ibu) {
                    $phones[] = '<a href="tel:' . e($ortu->hp_ibu) . '" class="text-secondary small d-block"><i class="fas fa-phone mr-1"></i>Ibu: ' . e($ortu->hp_ibu) . '</a>';
                }
                if ($siswa->nomor_hp) {
                    $phones[] = '<a href="tel:' . e($siswa->nomor_hp) . '" class="text-secondary small d-block"><i class="fas fa-mobile-alt mr-1"></i>Siswa: ' . e($siswa->nomor_hp) . '</a>';
                }
                if (! empty($phones)) {
                    $kontakOrtu = implode('', $phones);
                }
            }

            $genderIcon = $siswa->jenis_kelamin === 'L' ? '<i class="fas fa-mars text-primary" title="Laki-laki"></i>' : '<i class="fas fa-venus text-pink" style="color:#ec4899;" title="Perempuan"></i>';
            $genderText = $siswa->jenis_kelamin === 'L' ? 'L' : 'P';

            $detailUrl = route('admin.siswa-yatim.detail', $siswa->id);
            $fullShowUrl = route('admin.siswa.show', $siswa->id);

            $namaHtml = '<div>'
                . '<button type="button" class="btn btn-link p-0 text-left font-weight-bold js-btn-detail-siswa text-primary" data-detail-url="' . e($detailUrl) . '" data-full-url="' . e($fullShowUrl) . '" title="Klik untuk lihat detail">'
                    . e($siswa->nama_lengkap)
                . '</button>'
                . '<div class="small text-muted mt-1">'
                    . '<span class="mr-2"><i class="fas fa-id-card mr-1"></i>NISN: ' . e($siswa->nisn ?: '-') . '</span>'
                    . '<span><i class="fas fa-address-card mr-1"></i>NIK: ' . e($siswa->nik ?: '-') . '</span>'
                . '</div>'
            . '</div>';

            $actions = '<div class="btn-group btn-group-xs">'
                . '<button type="button" class="btn btn-outline-info btn-xs js-btn-detail-siswa" data-detail-url="' . e($detailUrl) . '" data-full-url="' . e($fullShowUrl) . '" title="Lihat Profil Lengkap"><i class="fas fa-eye"></i></button>'
                . '<a href="' . e($fullShowUrl) . '" class="btn btn-outline-primary btn-xs" target="_blank" rel="noopener" title="Buka Halaman Siswa"><i class="fas fa-external-link-alt"></i></a>'
            . '</div>';

            return [
                'id'             => $siswa->id,
                'nama_lengkap'   => $namaHtml,
                'jk'             => '<span class="badge badge-light border">' . $genderIcon . ' ' . $genderText . '</span>',
                'status_siswa'   => $statusSiswaBadge,
                'kelas'          => $kelasLabel,
                'kategori'       => $kategoriBadge,
                'ayah'           => $ayahInfo,
                'ibu'            => $ibuInfo,
                'kontak'         => $kontakOrtu,
                'bantuan'        => $bantuanHtml,
                'actions'        => $actions,
            ];
        });

        return response()->json([
            'draw'            => (int) $request->draw,
            'recordsTotal'    => $this->baseQuery()->count(),
            'recordsFiltered' => $recordsFiltered,
            'data'            => $data,
        ]);
    }

    /**
     * Endpoint detail siswa lengkap untuk modal.
     */
    public function detail(Siswa $siswa)
    {
        $this->authorize('view-siswa-yatim');

        $siswa->load([
            'user',
            'ortu.provinsi',
            'ortu.kabupaten',
            'ortu.kecamatan',
            'ortu.kelurahan',
            'sekolahAsal',
            'kelasAktif',
            'kelasTahunAktif',
            'dokumen' => fn($q) => $q->latest(),
        ]);

        $ortu = $siswa->ortu;
        $pekerjaan = PendaftaranPpdb::getPekerjaanOptions();
        $penghasilan = PendaftaranPpdb::getPenghasilanOptions();

        $statusAyah = $ortu?->status_ayah ?? '';
        $statusIbu = $ortu?->status_ibu ?? '';

        $kategori = match (true) {
            $statusAyah === 'meninggal' && $statusIbu === 'meninggal' => 'Yatim Piatu',
            $statusAyah === 'meninggal' => 'Yatim',
            $statusIbu === 'meninggal'  => 'Piatu',
            default => 'Lainnya',
        };

        $formatPekerjaan = fn(?string $val) => $pekerjaan[$val] ?? $val ?: '-';
        $formatPenghasilan = fn(?string $val) => $penghasilan[$val] ?? $val ?: '-';

        return response()->json([
            'success' => true,
            'data' => [
                'siswa' => [
                    'id'            => $siswa->id,
                    'nama_lengkap'  => $siswa->nama_lengkap,
                    'nisn'          => $siswa->nisn ?: '-',
                    'nik'           => $siswa->nik ?: '-',
                    'nis_lokal'     => $siswa->nis_lokal ?: '-',
                    'jenis_kelamin' => $siswa->jenis_kelamin === 'L' ? 'Laki-laki' : ($siswa->jenis_kelamin === 'P' ? 'Perempuan' : '-'),
                    'tempat_lahir'  => $siswa->tempat_lahir ?: '-',
                    'tanggal_lahir' => $siswa->tanggal_lahir ? $siswa->tanggal_lahir->format('d/m/Y') : '-',
                    'agama'         => $siswa->agama ?: 'Islam',
                    'nomor_hp'      => $siswa->nomor_hp ?: '-',
                    'alamat'        => $siswa->alamat_siswa ?: '-',
                    'rt_rw'         => trim(($siswa->rt_siswa ?: '') . ' / ' . ($siswa->rw_siswa ?: ''), ' /') ?: '-',
                    'kodepos'       => $siswa->kodepos_siswa ?: '-',
                    'status_siswa'  => ucfirst((string) $siswa->status_siswa),
                    'kelas'         => $siswa->kelasTahunAktif->first()?->nama_kelas ?: 'Tanpa Rombel',
                    'nomor_kip'     => $siswa->nomor_kip ?: '-',
                    'nomor_pkh'     => $siswa->nomor_pkh ?: '-',
                ],
                'kategori' => $kategori,
                'ortu' => [
                    'nama_ayah'        => $ortu?->nama_ayah ?: '-',
                    'status_ayah'      => $ortu?->status_ayah === 'meninggal' ? 'Meninggal' : ($ortu?->status_ayah === 'masih_hidup' ? 'Masih Hidup' : ($ortu?->status_ayah ?: '-')),
                    'nik_ayah'         => $ortu?->nik_ayah ?: '-',
                    'pekerjaan_ayah'   => $formatPekerjaan($ortu?->pekerjaan_ayah),
                    'penghasilan_ayah' => $formatPenghasilan($ortu?->penghasilan_ayah),
                    'hp_ayah'          => $ortu?->hp_ayah ?: '-',
                    'nama_ibu'         => $ortu?->nama_ibu ?: '-',
                    'status_ibu'       => $ortu?->status_ibu === 'meninggal' ? 'Meninggal' : ($ortu?->status_ibu === 'masih_hidup' ? 'Masih Hidup' : ($ortu?->status_ibu ?: '-')),
                    'nik_ibu'          => $ortu?->nik_ibu ?: '-',
                    'pekerjaan_ibu'    => $formatPekerjaan($ortu?->pekerjaan_ibu),
                    'penghasilan_ibu'  => $formatPenghasilan($ortu?->penghasilan_ibu),
                    'hp_ibu'           => $ortu?->hp_ibu ?: '-',
                    'nama_wali'        => $ortu?->nama_wali ?: '-',
                    'pekerjaan_wali'   => $formatPekerjaan($ortu?->pekerjaan_wali),
                    'hp_wali'          => $ortu?->hp_wali ?: '-',
                    'no_kk'            => $ortu?->no_kk ?: '-',
                    'alamat_ortu'      => $ortu?->alamat_ortu ?: '-',
                    'rt_rw_ortu'       => trim(($ortu?->rt_ortu ?: '') . ' / ' . ($ortu?->rw_ortu ?: ''), ' /') ?: '-',
                    'wilayah'          => implode(', ', array_filter([
                        $ortu?->kelurahan?->name,
                        $ortu?->kecamatan?->name,
                        $ortu?->kabupaten?->name,
                        $ortu?->provinsi?->name,
                    ])) ?: '-',
                ],
                'sekolah_asal' => [
                    'nama'   => $siswa->sekolahAsal?->nama ?: ($siswa->nama_sekolah_asal ?: '-'),
                    'npsn'   => $siswa->sekolahAsal?->npsn ?: ($siswa->npsn_asal_sekolah ?: '-'),
                    'status' => $siswa->sekolahAsal?->status_sekolah ?: '-',
                ],
                'dokumen' => $siswa->dokumen->map(fn($doc) => [
                    'id'            => $doc->id,
                    'jenis'         => $doc->jenis_dokumen_label ?? $doc->jenis_dokumen,
                    'file_name'     => $doc->original_name ?: $doc->nama_file,
                    'url'           => route('siswa.dokumen.download', $doc->id),
                    'preview_url'   => route('siswa.dokumen.preview', $doc->id),
                    'size'          => $doc->file_size_formatted ?? '-',
                ]),
            ],
        ]);
    }

    /**
     * Export data Siswa Yatim/Piatu ke file Excel.
     */
    public function export(Request $request)
    {
        $this->authorize('view-siswa-yatim');

        $query = $this->baseQuery()
            ->with([
                'ortu.provinsi',
                'ortu.kabupaten',
                'ortu.kecamatan',
                'ortu.kelurahan',
                'kelasTahunAktif',
            ]);

        $this->applyFilters($query, $request);

        $students = $query->orderBy('nama_lengkap', 'asc')->get();

        $kategori = $request->input('kategori');
        $kategoriSlug = match ($kategori) {
            'yatim'       => 'yatim',
            'piatu'       => 'piatu',
            'yatim_piatu' => 'yatim-piatu',
            default       => 'yatim-dan-piatu',
        };

        $fileName = 'data-siswa-' . $kategoriSlug . '-' . now()->format('Y-m-d') . '.xlsx';

        return Excel::download(new SiswaYatimExport($students), $fileName);
    }
}
