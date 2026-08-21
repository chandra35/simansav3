@php
    $ortu = $siswa->ortu;
    $alamatSiswa = collect([
        $siswa->alamat_siswa,
        $siswa->rt_siswa ? 'RT '.$siswa->rt_siswa : null,
        $siswa->rw_siswa ? 'RW '.$siswa->rw_siswa : null,
        $siswa->kelurahanSiswa?->name,
        $siswa->kecamatanSiswa?->name,
        $siswa->kabupatenSiswa?->name,
        $siswa->provinsiSiswa?->name,
        $siswa->kodepos_siswa,
    ])->filter()->implode(', ');
    $riwayatRombel = $siswa->siswaKelasRecords->sortByDesc(fn ($item) => ($item->tanggal_masuk?->format('Ymd') ?? '').($item->created_at?->format('YmdHis') ?? ''));
    $riwayatMutasi = $siswa->mutasiHistory->sortByDesc(fn ($item) => $item->tanggal_mutasi?->format('Ymd') ?? '');
    $value = fn ($content) => filled($content) ? $content : '-';
@endphp

<style>
#modalArsipSiswa .modal-dialog{max-width:1050px}#modalArsipSiswa .modal-content{border:0;border-radius:18px;overflow:hidden;box-shadow:0 24px 70px rgba(15,23,42,.24)}
#modalArsipSiswa .archive-hero{position:relative;padding:1.25rem 1.4rem;background:linear-gradient(125deg,#273d76,#216c86 65%,#188c83);color:#fff;border:0}#modalArsipSiswa .archive-person{display:flex;align-items:center;gap:1rem;min-width:0}#modalArsipSiswa .archive-photo{width:72px;height:72px;flex:0 0 72px;border-radius:16px;object-fit:cover;border:3px solid rgba(255,255,255,.35);background:#fff}#modalArsipSiswa .archive-eyebrow{font-size:.7rem;font-weight:700;letter-spacing:.09em;color:rgba(255,255,255,.7)}#modalArsipSiswa .archive-name{font-size:1.25rem;font-weight:800;line-height:1.2;margin:.2rem 0;color:#fff}#modalArsipSiswa .archive-meta{display:flex;gap:.45rem;flex-wrap:wrap}#modalArsipSiswa .archive-chip{padding:.28rem .58rem;border-radius:20px;font-size:.72rem;background:rgba(255,255,255,.13);border:1px solid rgba(255,255,255,.16)}
#modalArsipSiswa .archive-close{position:absolute;right:1rem;top:.75rem;color:#fff;opacity:.85;text-shadow:none}#modalArsipSiswa .archive-tabs{padding:.7rem 1.2rem 0;background:#f8fafc;border-bottom:1px solid #e6ebf2}#modalArsipSiswa .archive-tabs .nav-link{color:#596579!important;border:0;border-radius:9px 9px 0 0;font-weight:600;font-size:.82rem;padding:.65rem .9rem}#modalArsipSiswa .archive-tabs .nav-link.active{color:#334ac0!important;background:#fff;box-shadow:0 -2px 0 #4e62dc inset}
#modalArsipSiswa .modal-body{padding:1.25rem;background:#fff;max-height:62vh;overflow-y:auto}#modalArsipSiswa .archive-section{border:1px solid #e5eaf1;border-radius:12px;overflow:hidden;margin-bottom:1rem}#modalArsipSiswa .archive-section-title{display:flex;align-items:center;gap:.55rem;padding:.7rem .85rem;background:#f7f9fc;border-bottom:1px solid #e5eaf1;font-size:.82rem;font-weight:800;color:#273449}#modalArsipSiswa .archive-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:0}#modalArsipSiswa .archive-field{padding:.72rem .85rem;border-bottom:1px solid #edf0f5}#modalArsipSiswa .archive-field:nth-child(odd){border-right:1px solid #edf0f5}#modalArsipSiswa .archive-field.wide{grid-column:1/-1;border-right:0}#modalArsipSiswa .archive-label{display:block;font-size:.66rem;text-transform:uppercase;letter-spacing:.055em;color:#8490a3;margin-bottom:.18rem}#modalArsipSiswa .archive-value{display:block;font-size:.84rem;font-weight:600;color:#263246;overflow-wrap:anywhere}
#modalArsipSiswa .history-list{position:relative;padding-left:1.15rem}#modalArsipSiswa .history-item{position:relative;padding:0 0 1rem 1.15rem;border-left:2px solid #dce3ec}#modalArsipSiswa .history-item:last-child{padding-bottom:0}#modalArsipSiswa .history-dot{position:absolute;width:11px;height:11px;border-radius:50%;left:-6.5px;top:.25rem;background:#5265d9;border:2px solid #fff;box-shadow:0 0 0 2px #cad3e3}#modalArsipSiswa .history-title{font-size:.85rem;font-weight:800;color:#243047}#modalArsipSiswa .history-meta{font-size:.72rem;color:#7a879a;margin:.15rem 0}#modalArsipSiswa .history-note{font-size:.76rem;color:#505d70;margin:0}
#modalArsipSiswa .document-list{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:.8rem}#modalArsipSiswa .document-item{display:flex;align-items:flex-start;gap:.8rem;padding:.9rem;border:1px solid #e4e9f1;border-radius:12px;background:#fbfcfe}#modalArsipSiswa .document-icon{width:42px;height:42px;flex:0 0 42px;display:grid;place-items:center;border-radius:10px;background:#eef2ff;color:#4056d6;font-size:1.05rem}#modalArsipSiswa .document-main{min-width:0;flex:1}#modalArsipSiswa .document-title{font-size:.83rem;font-weight:800;color:#263246;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}#modalArsipSiswa .document-file{font-size:.71rem;color:#7b8798;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;margin:.12rem 0 .4rem}#modalArsipSiswa .document-info{display:flex;gap:.35rem;flex-wrap:wrap}#modalArsipSiswa .document-info .badge{font-size:.64rem;font-weight:600;padding:.3rem .42rem}#modalArsipSiswa .document-actions{display:flex;gap:.3rem;margin-top:.55rem}
@media(max-width:767.98px){#modalArsipSiswa .modal-dialog{margin:.5rem}#modalArsipSiswa .archive-photo{width:58px;height:58px;flex-basis:58px}#modalArsipSiswa .archive-name{font-size:1rem;padding-right:1.5rem}#modalArsipSiswa .archive-tabs{padding-left:.5rem;padding-right:.5rem;overflow-x:auto;flex-wrap:nowrap}#modalArsipSiswa .archive-tabs .nav-link{white-space:nowrap}#modalArsipSiswa .archive-grid,#modalArsipSiswa .document-list{grid-template-columns:1fr}#modalArsipSiswa .archive-field:nth-child(odd){border-right:0}#modalArsipSiswa .archive-field.wide{grid-column:auto}#modalArsipSiswa .modal-body{max-height:68vh;padding:.8rem}}
</style>

<div class="modal fade" id="modalArsipSiswa" tabindex="-1" role="dialog" aria-labelledby="modalArsipSiswaLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable" role="document">
        <div class="modal-content">
            <div class="modal-header archive-hero">
                <div class="archive-person">
                    <img class="archive-photo" src="{{ $siswa->foto_profile_url }}" alt="Foto {{ $siswa->nama_lengkap }}">
                    <div>
                        <div class="archive-eyebrow"><i class="fas fa-archive mr-1"></i> ARSIP SISWA MUTASI / NONAKTIF</div>
                        <h4 class="archive-name" id="modalArsipSiswaLabel">{{ $siswa->nama_lengkap }}</h4>
                        <div class="archive-meta">
                            <span class="archive-chip">NISN {{ $value($siswa->nisn) }}</span>
                            <span class="archive-chip">{{ strtoupper(str_replace('_', ' ', $siswa->status_siswa ?? '-')) }}</span>
                            <span class="archive-chip">Akun {{ $siswa->user?->is_active ? 'Aktif' : 'Nonaktif' }}</span>
                        </div>
                    </div>
                </div>
                <button type="button" class="close archive-close" data-dismiss="modal" aria-label="Tutup"><span aria-hidden="true">&times;</span></button>
            </div>

            <ul class="nav nav-tabs archive-tabs" role="tablist">
                <li class="nav-item"><a class="nav-link active" data-toggle="tab" href="#arsip-identitas" role="tab"><i class="fas fa-id-card mr-1"></i> Data Diri</a></li>
                <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#arsip-keluarga" role="tab"><i class="fas fa-users mr-1"></i> Keluarga & Alamat</a></li>
                <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#arsip-riwayat" role="tab"><i class="fas fa-history mr-1"></i> Riwayat</a></li>
                @if($siswa->dokumen->isNotEmpty())
                <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#arsip-dokumen" role="tab"><i class="fas fa-folder-open mr-1"></i> Dokumen <span class="badge badge-primary ml-1">{{ $siswa->dokumen->count() }}</span></a></li>
                @endif
            </ul>

            <div class="modal-body">
                <div class="tab-content">
                    <div class="tab-pane fade show active" id="arsip-identitas" role="tabpanel">
                        <div class="archive-section">
                            <div class="archive-section-title"><i class="fas fa-user text-primary"></i> Identitas Utama</div>
                            <div class="archive-grid">
                                @foreach([
                                    ['Nama lengkap', $siswa->nama_lengkap], ['NIK', $siswa->nik], ['NISN', $siswa->nisn], ['NIS lokal', $siswa->nis],
                                    ['Nomor tes', $siswa->nomor_tes], ['Jenis kelamin', $siswa->jenis_kelamin === 'L' ? 'Laki-laki' : ($siswa->jenis_kelamin === 'P' ? 'Perempuan' : '-')],
                                    ['Tempat lahir', $siswa->tempat_lahir], ['Tanggal lahir', $siswa->tanggal_lahir?->translatedFormat('d F Y')],
                                    ['Agama', $siswa->agama], ['Nomor HP', $siswa->nomor_hp], ['Anak ke', $siswa->anak_ke], ['Jumlah saudara', $siswa->jumlah_saudara],
                                    ['Hobi', $siswa->hobi], ['Cita-cita', $siswa->cita_cita],
                                ] as [$label, $content])
                                <div class="archive-field"><span class="archive-label">{{ $label }}</span><span class="archive-value">{{ $value($content) }}</span></div>
                                @endforeach
                            </div>
                        </div>
                        <div class="archive-section">
                            <div class="archive-section-title"><i class="fas fa-school text-info"></i> Administrasi & Akademik</div>
                            <div class="archive-grid">
                                @foreach([
                                    ['Status siswa', strtoupper(str_replace('_', ' ', $siswa->status_siswa ?? '-'))], ['Asal penerimaan', strtoupper(str_replace('_', ' ', $siswa->asal_siswa ?? '-'))],
                                    ['Tahun masuk', $siswa->tahun_masuk], ['Sekolah asal', $siswa->sekolahAsal?->nama ?? $siswa->nama_sekolah_asal],
                                    ['NPSN sekolah asal', $siswa->npsn_asal_sekolah], ['Status akun', $siswa->user?->is_active ? 'Aktif' : 'Nonaktif'],
                                    ['Username', $siswa->user?->username], ['Email', $siswa->user?->email],
                                ] as [$label, $content])
                                <div class="archive-field"><span class="archive-label">{{ $label }}</span><span class="archive-value">{{ $value($content) }}</span></div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <div class="tab-pane fade" id="arsip-keluarga" role="tabpanel">
                        <div class="archive-section">
                            <div class="archive-section-title"><i class="fas fa-map-marker-alt text-danger"></i> Alamat Siswa</div>
                            <div class="archive-grid"><div class="archive-field wide"><span class="archive-label">Alamat lengkap</span><span class="archive-value">{{ $value($alamatSiswa) }}</span></div><div class="archive-field"><span class="archive-label">Jenis tempat tinggal</span><span class="archive-value">{{ $value($siswa->jenis_tempat_tinggal) }}</span></div><div class="archive-field"><span class="archive-label">Alamat sama dengan orang tua</span><span class="archive-value">{{ $siswa->alamat_sama_ortu ? 'Ya' : 'Tidak' }}</span></div></div>
                        </div>
                        <div class="archive-section">
                            <div class="archive-section-title"><i class="fas fa-users text-success"></i> Data Orang Tua</div>
                            <div class="archive-grid">
                                @foreach([
                                    ['Nomor KK', $ortu?->no_kk], ['Alamat orang tua', $ortu?->getAlamatLengkap()], ['Nama ayah', $ortu?->nama_ayah], ['NIK ayah', $ortu?->nik_ayah],
                                    ['Status ayah', $ortu?->status_ayah ? str_replace('_', ' ', $ortu->status_ayah) : null], ['Pekerjaan ayah', $ortu?->pekerjaan_ayah], ['Penghasilan ayah', $ortu?->penghasilan_ayah], ['HP ayah', $ortu?->hp_ayah],
                                    ['Nama ibu', $ortu?->nama_ibu], ['NIK ibu', $ortu?->nik_ibu], ['Status ibu', $ortu?->status_ibu ? str_replace('_', ' ', $ortu->status_ibu) : null], ['Pekerjaan ibu', $ortu?->pekerjaan_ibu],
                                    ['Penghasilan ibu', $ortu?->penghasilan_ibu], ['HP ibu', $ortu?->hp_ibu],
                                ] as [$label, $content])
                                <div class="archive-field {{ in_array($label, ['Alamat orang tua']) ? 'wide' : '' }}"><span class="archive-label">{{ $label }}</span><span class="archive-value">{{ $value($content) }}</span></div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <div class="tab-pane fade" id="arsip-riwayat" role="tabpanel">
                        <div class="row">
                            <div class="col-lg-6">
                                <div class="archive-section">
                                    <div class="archive-section-title"><i class="fas fa-chalkboard text-primary"></i> Riwayat Rombel <span class="badge badge-light ml-auto">{{ $riwayatRombel->count() }}</span></div>
                                    <div class="p-3 history-list">
                                        @forelse($riwayatRombel as $record)
                                        <div class="history-item"><span class="history-dot"></span><div class="history-title">{{ $record->kelas?->nama_lengkap ?? $record->kelas?->nama_kelas ?? 'Rombel tidak tersedia' }}</div><div class="history-meta">{{ $record->tahunPelajaran?->nama ?? '-' }} · Tingkat {{ $record->tingkat ?? '-' }} · {{ strtoupper($record->status ?? '-') }}</div><p class="history-note">{{ $record->tanggal_masuk?->format('Y-m-d') ?? '-' }} → {{ $record->tanggal_keluar?->format('Y-m-d') ?? 'sekarang' }}@if($record->catatan_perpindahan) · {{ $record->catatan_perpindahan }}@endif</p></div>
                                        @empty <div class="text-muted text-center py-3">Belum ada riwayat rombel.</div> @endforelse
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="archive-section">
                                    <div class="archive-section-title"><i class="fas fa-exchange-alt text-warning"></i> Riwayat Mutasi <span class="badge badge-light ml-auto">{{ $riwayatMutasi->count() }}</span></div>
                                    <div class="p-3 history-list">
                                        @forelse($riwayatMutasi as $history)
                                        <div class="history-item"><span class="history-dot"></span><div class="history-title">{{ $history->jenis_mutasi === 'masuk' ? 'Mutasi Masuk' : 'Mutasi Keluar' }} · {{ $history->status_text }}</div><div class="history-meta">{{ $history->tanggal_mutasi?->format('Y-m-d') ?? '-' }} · {{ $history->tahunPelajaran?->nama ?? '-' }}</div><p class="history-note">{{ $history->nama_sekolah }}@if($history->alasan_mutasi_keluar || $history->alasan_mutasi_masuk) · {{ $history->alasan_mutasi_keluar ?? $history->alasan_mutasi_masuk }}@endif</p></div>
                                        @empty <div class="text-muted text-center py-3">Belum ada riwayat mutasi.</div> @endforelse
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    @if($siswa->dokumen->isNotEmpty())
                    <div class="tab-pane fade" id="arsip-dokumen" role="tabpanel">
                        <div class="archive-section mb-0">
                            <div class="archive-section-title"><i class="fas fa-folder-open text-primary"></i> Dokumen Tersimpan <span class="badge badge-light ml-auto">{{ $siswa->dokumen->count() }} berkas</span></div>
                            <div class="p-3 document-list">
                                @foreach($siswa->dokumen->sortByDesc('created_at') as $dokumen)
                                @php
                                    $extension = strtolower($dokumen->getFileExtension());
                                    $icon = $extension === 'pdf' ? 'fa-file-pdf' : (in_array($extension, ['jpg', 'jpeg', 'png', 'webp', 'gif']) ? 'fa-file-image' : 'fa-file-alt');
                                    $statusClass = match($dokumen->status) { 'approved' => 'success', 'rejected' => 'danger', 'pending' => 'warning', default => 'secondary' };
                                @endphp
                                <div class="document-item">
                                    <div class="document-icon"><i class="fas {{ $icon }}"></i></div>
                                    <div class="document-main">
                                        <div class="document-title" title="{{ $dokumen->getJenisDokumenLabel() }}">{{ $dokumen->getJenisDokumenLabel() }}</div>
                                        <div class="document-file" title="{{ $dokumen->original_name ?? $dokumen->nama_file }}">{{ $dokumen->original_name ?? $dokumen->nama_file ?? 'Nama file tidak tersedia' }}</div>
                                        <div class="document-info">
                                            <span class="badge badge-light border">{{ strtoupper($extension) }}</span>
                                            <span class="badge badge-light border">{{ $dokumen->getFileSizeFormatted() }}</span>
                                            <span class="badge badge-{{ $statusClass }}">{{ ucfirst($dokumen->status ?? 'tersimpan') }}</span>
                                            <span class="badge badge-light border">{{ $dokumen->created_at?->format('Y-m-d H:i') ?? '-' }}</span>
                                        </div>
                                        @if($dokumen->keterangan)<div class="small text-muted mt-2">{{ $dokumen->keterangan }}</div>@endif
                                        @if($dokumen->file_path)
                                        <div class="document-actions">
                                            <a href="{{ route('siswa.dokumen.preview', $dokumen->id) }}" target="_blank" rel="noopener" class="btn btn-info btn-xs"><i class="fas fa-eye mr-1"></i>Lihat</a>
                                            <a href="{{ route('siswa.dokumen.download', $dokumen->id) }}" class="btn btn-outline-secondary btn-xs"><i class="fas fa-download mr-1"></i>Unduh</a>
                                        </div>
                                        @else
                                        <div class="small text-warning mt-2"><i class="fas fa-exclamation-triangle mr-1"></i>File fisik belum tersedia.</div>
                                        @endif
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
            <div class="modal-footer">
                @can('view-siswa')<a href="{{ route('admin.siswa.show', $siswa) }}" class="btn btn-outline-primary"><i class="fas fa-external-link-alt mr-1"></i> Buka Halaman Detail</a>@endcan
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>
