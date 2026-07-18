@extends('adminlte::page')

@section('title', 'Detail Pembanding EMIS')

@php
    $labels = [
        'exact' => ['Sama persis', 'success', 'fa-check-circle'],
        'equivalent' => ['Setara', 'info', 'fa-equals'],
        'similar' => ['Mirip', 'warning', 'fa-adjust'],
        'different' => ['Berbeda', 'danger', 'fa-times-circle'],
        'simansa_empty' => ['Kosong di SIMANSA', 'secondary', 'fa-arrow-right'],
        'emis_empty' => ['Kosong di EMIS', 'secondary', 'fa-arrow-left'],
        'both_empty' => ['Tidak tersedia', 'light', 'fa-minus-circle'],
    ];
    $overallLabels = [
        'exact' => ['Semua data sama', 'success', 'fa-check-circle'],
        'normalized' => ['Data setara setelah normalisasi', 'info', 'fa-equals'],
        'similar' => ['Ada data mirip yang perlu ditinjau', 'warning', 'fa-search'],
        'different' => ['Ada data yang berbeda', 'danger', 'fa-exclamation-triangle'],
    ];
    [$overallText, $overallColor, $overallIcon] = $comparison
        ? ($overallLabels[$comparison['status']] ?? ['Perlu diperiksa', 'secondary', 'fa-search'])
        : ['Tidak ditemukan pada snapshot EMIS', 'secondary', 'fa-cloud'];
    $differentCount = $comparison ? collect($comparison['details'])->whereIn('status', ['different', 'similar', 'simansa_empty', 'emis_empty'])->count() : 0;
@endphp

@section('content_header')
    <div class="simansa-detail-hero">
        <div>
            <div class="simansa-detail-hero__eyebrow"><i class="fas fa-columns"></i> Pembanding Data Siswa</div>
            <h1>{{ $siswa?->nama_lengkap ?? $snapshot?->full_name ?? 'Detail Siswa' }}</h1>
            <p>NISN {{ $siswa?->nisn ?? $snapshot?->nisn ?? '-' }} · Snapshot EMIS ditampilkan berdampingan dengan data SIMANSA.</p>
        </div>
        <div class="simansa-detail-hero__actions">
            <div class="simansa-detail-chip"><span>Field perlu dicek</span><strong>{{ $differentCount }}</strong></div>
            <a href="{{ route('admin.emis-comparison.index') }}" class="btn btn-light"><i class="fas fa-arrow-left mr-1"></i> Kembali ke Daftar</a>
        </div>
    </div>
@stop

@section('content')
    <section class="simansa-detail-status simansa-detail-status--{{ $overallColor }} mb-4">
        <div class="simansa-detail-status__icon"><i class="fas {{ $overallIcon }}"></i></div>
        <div><span>Hasil pembandingan</span><h2>{{ $overallText }}</h2><p>Nilai dibandingkan dengan normalisasi huruf, spasi, tanggal, dan variasi penulisan yang wajar.</p></div>
        @if($snapshot?->synced_at)<div class="simansa-detail-status__time"><span>Waktu snapshot</span><strong>{{ $snapshot->synced_at->format('d/m/Y H:i:s') }} WIB</strong></div>@endif
    </section>

    @if(!$tokenStatus['usable'])
        <section class="simansa-snapshot-note mb-4"><i class="fas fa-lock"></i><div><strong>Mode snapshot tetap aktif</strong><span>Token EMIS sedang tidak aktif. Detail ini tetap dapat dibuka tanpa melakukan request ke API.</span></div></section>
    @endif

    @php
        $documentDefinitions = [
            'kk' => [
                'title' => 'Kartu Keluarga',
                'short' => 'KK',
                'icon' => 'fa-users',
                'tone' => 'blue',
                'usage' => 'Acuan utama untuk nama, tempat/tanggal lahir, jenis kelamin, NIK, dan data keluarga.',
            ],
            'ijazah_smp' => [
                'title' => 'Ijazah SMP/MTs',
                'short' => 'Ijazah',
                'icon' => 'fa-certificate',
                'tone' => 'amber',
                'usage' => 'Acuan pendidikan untuk nama resmi, NISN, dan identitas kelahiran siswa.',
            ],
        ];
        $availableDocumentCount = collect(array_keys($documentDefinitions))
            ->filter(fn ($type) => $referenceDocuments->has($type))
            ->count();
    @endphp

    <section class="simansa-document-section mb-4">
        <div class="simansa-section-head simansa-document-head">
            <div>
                <h3><i class="fas fa-paperclip mr-2 text-primary"></i>Dokumen Acuan Koreksi</h3>
                <p>Gunakan dokumen asli siswa sebagai dasar menentukan nilai yang benar sebelum melakukan perbaikan langsung di EMIS.</p>
            </div>
            <span class="simansa-document-readiness simansa-document-readiness--{{ $availableDocumentCount === 2 ? 'ready' : 'partial' }}">
                <i class="fas {{ $availableDocumentCount === 2 ? 'fa-check-circle' : 'fa-exclamation-circle' }} mr-1"></i>
                {{ $availableDocumentCount }}/2 tersedia
            </span>
        </div>

        @if(!$siswa)
            <div class="simansa-document-unmatched">
                <i class="fas fa-link"></i>
                <div><strong>Dokumen belum dapat ditampilkan</strong><span>Data EMIS ini belum memiliki pasangan siswa di SIMANSA.</span></div>
            </div>
        @else
            <div class="simansa-document-grid">
                @foreach($documentDefinitions as $type => $definition)
                    @php
                        $document = $referenceDocuments->get($type);
                        $extension = $document?->getFileExtension();
                        $isPreviewable = $document && ($extension === 'pdf' || in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp'], true));
                        $fileIcon = $extension === 'pdf' ? 'fa-file-pdf' : (in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp'], true) ? 'fa-file-image' : 'fa-file-alt');
                        $documentName = $document?->original_name ?: $document?->nama_file;
                    @endphp
                    <article class="simansa-document-card simansa-document-card--{{ $definition['tone'] }} {{ $document ? 'is-available' : 'is-missing' }}">
                        <div class="simansa-document-card__top">
                            <div class="simansa-document-card__icon"><i class="fas {{ $definition['icon'] }}"></i></div>
                            <div class="simansa-document-card__identity">
                                <span>{{ $definition['short'] }}</span>
                                <h4>{{ $definition['title'] }}</h4>
                            </div>
                            <span class="simansa-document-state simansa-document-state--{{ $document ? 'available' : 'missing' }}">
                                <i class="fas {{ $document ? 'fa-check' : 'fa-minus' }} mr-1"></i>{{ $document ? 'Tersedia' : 'Belum ada' }}
                            </span>
                        </div>
                        <p class="simansa-document-card__usage">{{ $definition['usage'] }}</p>

                        @if($document)
                            <div class="simansa-document-file">
                                <div class="simansa-document-file__type"><i class="fas {{ $fileIcon }}"></i></div>
                                <div class="simansa-document-file__copy">
                                    <strong title="{{ $documentName }}">{{ \Illuminate\Support\Str::limit($documentName ?: $definition['title'], 42) }}</strong>
                                    <span>
                                        {{ strtoupper($extension ?: 'FILE') }}
                                        @if($document->file_size) · {{ $document->getFileSizeFormatted() }} @endif
                                        @if($document->created_at) · Diunggah {{ $document->created_at->format('d/m/Y H:i') }} WIB @endif
                                    </span>
                                </div>
                            </div>
                            <div class="simansa-document-actions">
                                @if($isPreviewable)
                                    <button type="button" class="btn btn-sm btn-primary btn-preview-reference"
                                            data-url="{{ $document->getFileUrl() }}"
                                            data-title="{{ $definition['title'] }}"
                                            data-extension="{{ $extension }}"
                                            data-file="{{ $documentName }}">
                                        <i class="fas fa-eye mr-1"></i> Preview Dokumen
                                    </button>
                                @endif
                                <a href="{{ $document->getFileUrl() }}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-secondary">
                                    <i class="fas fa-external-link-alt mr-1"></i> Buka Tab Baru
                                </a>
                            </div>
                        @else
                            <div class="simansa-document-empty">
                                <i class="fas fa-cloud-upload-alt"></i>
                                <div><strong>Belum diunggah siswa</strong><span>Minta siswa melengkapi dokumen melalui akun mereka.</span></div>
                            </div>
                        @endif
                    </article>
                @endforeach
            </div>

            <div class="simansa-document-guidance">
                <div class="simansa-document-guidance__icon"><i class="fas fa-shield-alt"></i></div>
                <div>
                    <strong>Urutan pemeriksaan yang disarankan</strong>
                    <p>Periksa dokumen, cocokkan baris berwarna pada tabel, lalu lakukan koreksi di EMIS. SIMANSA hanya menjadi alat bantu pembanding dan tidak mengubah data EMIS secara otomatis.</p>
                </div>
            </div>
        @endif
    </section>

    <section class="simansa-detail-section mb-4">
        <div class="simansa-section-head"><div><h3>Perbandingan Field</h3><p>SIMANSA berada di kiri dan EMIS Lembaga di kanan. Baris berwarna menunjukkan data yang perlu ditinjau admin.</p></div></div>
        <div class="table-responsive simansa-comparison-shell">
            <table class="table simansa-comparison-table mb-0">
                <thead><tr><th>Field</th><th class="simansa-head-simansa"><i class="fas fa-database mr-1"></i> SIMANSA</th><th class="text-center">Hasil</th><th class="simansa-head-emis"><i class="fas fa-cloud mr-1"></i> EMIS Lembaga</th></tr></thead>
                <tbody>
                @if($comparison)
                    @foreach($comparison['details'] as $field => $detail)
                        @php
                            [$text, $color, $icon] = $labels[$detail['status']] ?? [ucfirst($detail['status']), 'secondary', 'fa-question-circle'];
                            $left = $detail['simansa']; $right = $detail['emis'];
                            if($field === 'tanggal_lahir') {
                                try { $left = $left ? \Carbon\Carbon::parse($left)->translatedFormat('d F Y') : null; } catch(\Throwable $e) {}
                                try { $right = $right ? \Carbon\Carbon::parse($right)->translatedFormat('d F Y') : null; } catch(\Throwable $e) {}
                            }
                            $rowAttention = in_array($detail['status'], ['different', 'similar', 'simansa_empty', 'emis_empty'], true);
                        @endphp
                        <tr class="{{ $rowAttention ? 'is-attention is-'.$color : '' }}">
                            <th>{{ $detail['label'] }}</th>
                            <td class="simansa-value">{{ filled($left) ? $left : '—' }}</td>
                            <td class="text-center"><span class="badge badge-{{ $color }} simansa-result-badge"><i class="fas {{ $icon }} mr-1"></i>{{ $text }}</span>@if($detail['score'] !== null)<small class="d-block text-muted mt-1">Kemiripan {{ number_format($detail['score'], 1) }}%</small>@endif</td>
                            <td class="simansa-value">{{ filled($right) ? $right : '—' }}</td>
                        </tr>
                    @endforeach
                @else
                    <tr><td colspan="4" class="text-center text-muted py-5"><i class="fas fa-cloud d-block fa-2x mb-2 text-light"></i>Siswa ini tidak ditemukan pada snapshot EMIS terakhir.</td></tr>
                @endif
                </tbody>
            </table>
        </div>
    </section>

    <div class="row">
        <div class="col-md-6 mb-4">
            <section class="simansa-info-card simansa-info-card--blue">
                <div class="simansa-info-card__head"><div class="simansa-info-card__icon"><i class="fas fa-database"></i></div><div><h3>Informasi SIMANSA</h3><p>Data operasional siswa di SIMANSA.</p></div></div>
                <dl class="simansa-info-list"><dt>Nama</dt><dd>{{ $siswa?->nama_lengkap ?? '—' }}</dd><dt>NISN</dt><dd>{{ $siswa?->nisn ?? '—' }}</dd><dt>Kelas</dt><dd>{{ $siswa?->kelasSaatIni?->nama_kelas ?? '—' }}</dd></dl>
                @if($siswa)<a href="{{ route('admin.siswa.show', $siswa) }}" class="btn btn-sm btn-outline-primary"><i class="fas fa-user mr-1"></i> Buka Data Siswa</a>@endif
            </section>
        </div>
        <div class="col-md-6 mb-4">
            <section class="simansa-info-card simansa-info-card--green">
                <div class="simansa-info-card__head"><div class="simansa-info-card__icon"><i class="fas fa-cloud"></i></div><div><h3>Informasi EMIS</h3><p>Metadata siswa pada snapshot Lembaga.</p></div></div>
                <dl class="simansa-info-list"><dt>ID Siswa EMIS</dt><dd>{{ $snapshot?->emis_student_id ?? '—' }}</dd><dt>Tingkat / Rombel</dt><dd>{{ collect([$snapshot?->level_name, $snapshot?->study_group_name])->filter()->implode(' · ') ?: '—' }}</dd><dt>Jurusan</dt><dd>{{ $snapshot?->major_name ?? '—' }}</dd><dt>Tahun Pelajaran</dt><dd>{{ $snapshot?->academic_year ?? '—' }}</dd><dt>NISN Valid</dt><dd>@if($snapshot && $snapshot->valid_nisn !== null)<span class="badge badge-{{ $snapshot->valid_nisn ? 'success' : 'danger' }}">{{ $snapshot->valid_nisn ? 'Ya' : 'Tidak' }}</span>@else — @endif</dd></dl>
            </section>
        </div>
    </div>

    <div class="modal fade" id="referenceDocumentModal" tabindex="-1" role="dialog" aria-labelledby="referenceDocumentTitle" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered" role="document">
            <div class="modal-content simansa-document-modal">
                <div class="modal-header">
                    <div>
                        <span class="simansa-document-modal__eyebrow"><i class="fas fa-paperclip mr-1"></i> Dokumen Acuan</span>
                        <h5 class="modal-title" id="referenceDocumentTitle">Preview Dokumen</h5>
                        <small id="referenceDocumentFile" class="text-muted"></small>
                    </div>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Tutup"><span aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body p-0">
                    <div class="simansa-document-viewer-loading" id="referenceDocumentLoading"><i class="fas fa-spinner fa-spin"></i><span>Memuat dokumen...</span></div>
                    <img id="referenceDocumentImage" class="simansa-document-viewer-image" alt="Preview dokumen siswa">
                    <iframe id="referenceDocumentFrame" class="simansa-document-viewer-frame" title="Preview dokumen siswa"></iframe>
                </div>
                <div class="modal-footer">
                    <span class="mr-auto text-muted small"><i class="fas fa-info-circle mr-1"></i>Gunakan dokumen ini hanya untuk kepentingan koreksi data siswa.</span>
                    <a href="#" target="_blank" rel="noopener" id="referenceDocumentOpen" class="btn btn-outline-primary"><i class="fas fa-external-link-alt mr-1"></i> Buka Tab Baru</a>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>
@stop

@section('css')
<style>
    .simansa-detail-hero{display:flex;justify-content:space-between;align-items:center;gap:1rem;padding:1.35rem 1.45rem;border-radius:16px;background:#3b82f6;color:#fff;box-shadow:0 14px 32px rgba(59,130,246,.22)}.simansa-detail-hero__eyebrow{font-size:.8rem;font-weight:700;text-transform:uppercase;letter-spacing:.04em;color:rgba(255,255,255,.9);margin-bottom:.55rem}.simansa-detail-hero h1{font-size:1.45rem;font-weight:700;color:#fff;margin:0 0 .35rem}.simansa-detail-hero p{margin:0;color:rgba(255,255,255,.88)}.simansa-detail-hero__actions{display:flex;gap:.7rem;align-items:stretch}.simansa-detail-chip{display:flex;flex-direction:column;justify-content:center;padding:.7rem 1rem;border:1px solid rgba(255,255,255,.2);background:rgba(255,255,255,.14);border-radius:12px;min-width:135px}.simansa-detail-chip span{font-size:.75rem;color:rgba(255,255,255,.78)}.simansa-detail-chip strong{font-size:1.25rem}.simansa-detail-hero .btn{display:flex;align-items:center;border-radius:10px;color:#2563eb;font-weight:600}
    .simansa-detail-status{display:flex;align-items:center;gap:1rem;padding:1rem 1.15rem;background:#fff;border:1px solid #dbe4f0;border-left:4px solid #22c55e;border-radius:14px;box-shadow:0 8px 24px rgba(15,23,42,.04)}.simansa-detail-status--info{border-left-color:#0ea5e9}.simansa-detail-status--warning{border-left-color:#f59e0b}.simansa-detail-status--danger{border-left-color:#e11d48}.simansa-detail-status--secondary{border-left-color:#64748b}.simansa-detail-status__icon{width:46px;height:46px;display:flex;align-items:center;justify-content:center;border-radius:12px;background:#ecfdf5;color:#15803d}.simansa-detail-status--info .simansa-detail-status__icon{background:#f0f9ff;color:#0284c7}.simansa-detail-status--warning .simansa-detail-status__icon{background:#fffbeb;color:#b45309}.simansa-detail-status--danger .simansa-detail-status__icon{background:#fff1f2;color:#be123c}.simansa-detail-status>div:nth-child(2){flex:1}.simansa-detail-status span{font-size:.75rem;text-transform:uppercase;letter-spacing:.04em;color:#64748b;font-weight:700}.simansa-detail-status h2{font-size:1rem;font-weight:700;color:#0f172a;margin:.15rem 0}.simansa-detail-status p{margin:0;color:#64748b}.simansa-detail-status__time{display:flex;flex-direction:column;padding-left:1rem;border-left:1px solid #e2e8f0}.simansa-detail-status__time strong{font-size:.85rem;color:#334155}
    .simansa-snapshot-note{display:flex;align-items:center;gap:.8rem;padding:.85rem 1rem;border:1px solid #fde68a;background:#fffbeb;border-radius:12px;color:#92400e}.simansa-snapshot-note>div{display:flex;flex-direction:column}.simansa-snapshot-note span{font-size:.84rem;color:#a16207}
    .simansa-document-section{padding:1.1rem 1.25rem;border-radius:14px;background:#fff;border:1px solid #dbe4f0;box-shadow:0 10px 28px rgba(15,23,42,.05)}.simansa-document-head{align-items:center}.simansa-document-readiness{display:inline-flex;align-items:center;white-space:nowrap;padding:.42rem .72rem;border-radius:999px;font-size:.78rem;font-weight:700}.simansa-document-readiness--ready{background:#ecfdf5;color:#15803d}.simansa-document-readiness--partial{background:#fffbeb;color:#b45309}.simansa-document-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:1rem}.simansa-document-card{padding:1rem;border-radius:13px;border:1px solid #dbe4f0;border-top:4px solid #3b82f6;background:#fff;min-width:0}.simansa-document-card--amber{border-top-color:#f59e0b}.simansa-document-card.is-missing{background:#fbfcfe;border-style:dashed}.simansa-document-card__top{display:flex;align-items:center;gap:.75rem}.simansa-document-card__icon{width:44px;height:44px;display:flex;align-items:center;justify-content:center;flex:0 0 auto;border-radius:12px;background:#eff6ff;color:#2563eb}.simansa-document-card--amber .simansa-document-card__icon{background:#fffbeb;color:#b45309}.simansa-document-card__identity{flex:1;min-width:0}.simansa-document-card__identity>span{font-size:.68rem;text-transform:uppercase;letter-spacing:.05em;color:#94a3b8;font-weight:700}.simansa-document-card__identity h4{margin:0;color:#1e293b;font-size:.96rem;font-weight:700}.simansa-document-state{display:inline-flex;align-items:center;padding:.3rem .55rem;border-radius:999px;font-size:.7rem;font-weight:700}.simansa-document-state--available{background:#ecfdf5;color:#15803d}.simansa-document-state--missing{background:#f1f5f9;color:#64748b}.simansa-document-card__usage{margin:.8rem 0;color:#64748b;font-size:.82rem;line-height:1.5;min-height:40px}.simansa-document-file{display:flex;align-items:center;gap:.7rem;padding:.72rem;border-radius:10px;background:#f8fafc;border:1px solid #e5edf7}.simansa-document-file__type{width:36px;height:36px;display:flex;align-items:center;justify-content:center;flex:0 0 auto;border-radius:9px;background:#fff;color:#e11d48;box-shadow:0 3px 9px rgba(15,23,42,.07)}.simansa-document-file__copy{display:flex;flex-direction:column;min-width:0}.simansa-document-file__copy strong{overflow:hidden;text-overflow:ellipsis;white-space:nowrap;color:#334155;font-size:.82rem}.simansa-document-file__copy span{color:#94a3b8;font-size:.7rem}.simansa-document-actions{display:flex;gap:.5rem;flex-wrap:wrap;margin-top:.75rem}.simansa-document-actions .btn{border-radius:8px}.simansa-document-empty{display:flex;align-items:center;gap:.7rem;padding:.75rem;border-radius:10px;background:#f8fafc;color:#64748b}.simansa-document-empty>i{font-size:1.25rem;color:#94a3b8}.simansa-document-empty>div{display:flex;flex-direction:column}.simansa-document-empty strong{font-size:.82rem;color:#475569}.simansa-document-empty span{font-size:.74rem}.simansa-document-guidance{display:flex;align-items:flex-start;gap:.75rem;margin-top:1rem;padding:.82rem 1rem;border-radius:11px;background:#eff6ff;border:1px solid #dbeafe}.simansa-document-guidance__icon{width:36px;height:36px;display:flex;align-items:center;justify-content:center;flex:0 0 auto;border-radius:9px;background:#2563eb;color:#fff}.simansa-document-guidance strong{color:#1e3a8a}.simansa-document-guidance p{margin:.15rem 0 0;color:#475569;font-size:.82rem;line-height:1.5}.simansa-document-unmatched{display:flex;align-items:center;gap:.8rem;padding:1rem;border:1px dashed #cbd5e1;border-radius:11px;background:#f8fafc;color:#64748b}.simansa-document-unmatched>i{font-size:1.3rem}.simansa-document-unmatched>div{display:flex;flex-direction:column}.simansa-document-unmatched strong{color:#334155}.simansa-document-modal{border:0;border-radius:16px;overflow:hidden}.simansa-document-modal .modal-header{align-items:flex-start;background:#f8fafc;border-bottom-color:#e5edf7}.simansa-document-modal__eyebrow{color:#2563eb;font-size:.7rem;font-weight:700;text-transform:uppercase;letter-spacing:.05em}.simansa-document-modal .modal-title{font-weight:700;color:#0f172a}.simansa-document-viewer-loading{height:68vh;display:flex;flex-direction:column;align-items:center;justify-content:center;gap:.6rem;background:#f1f5f9;color:#64748b}.simansa-document-viewer-loading i{font-size:1.7rem;color:#2563eb}.simansa-document-viewer-image{display:none;width:100%;height:68vh;object-fit:contain;background:#111827}.simansa-document-viewer-frame{display:none;width:100%;height:68vh;border:0;background:#f1f5f9}
    .simansa-detail-section,.simansa-info-card{padding:1.1rem 1.25rem;border-radius:14px;background:#fff;border:1px solid #dbe4f0;box-shadow:0 10px 28px rgba(15,23,42,.05)}.simansa-section-head{margin-bottom:1rem}.simansa-section-head h3,.simansa-info-card h3{font-size:1.05rem;font-weight:700;color:#0f172a;margin:0 0 .3rem}.simansa-section-head p,.simansa-info-card p{color:#64748b;line-height:1.5;margin:0}.simansa-comparison-shell{border:1px solid #e5edf7;border-radius:12px;overflow:hidden}.simansa-comparison-table th,.simansa-comparison-table td{vertical-align:middle;padding:1rem;border-color:#edf2f7}.simansa-comparison-table thead th{border:0;background:#f8fafc;color:#64748b;font-size:.78rem;text-transform:uppercase;letter-spacing:.03em}.simansa-head-simansa{background:#eff6ff!important;color:#1d4ed8!important}.simansa-head-emis{background:#ecfdf5!important;color:#15803d!important}.simansa-comparison-table tbody th{color:#475569;font-size:.85rem}.simansa-value{font-size:1rem;font-weight:700;color:#1e293b}.simansa-comparison-table tr.is-danger{background:#fff7f7}.simansa-comparison-table tr.is-warning{background:#fffdf4}.simansa-result-badge{padding:.45rem .65rem}
    .simansa-info-card{height:100%;border-top:4px solid #3b82f6}.simansa-info-card--green{border-top-color:#22c55e}.simansa-info-card__head{display:flex;gap:.8rem;align-items:center;padding-bottom:1rem;border-bottom:1px solid #edf2f7}.simansa-info-card__icon{width:42px;height:42px;display:flex;align-items:center;justify-content:center;border-radius:11px;background:#eef4ff;color:#2563eb}.simansa-info-card--green .simansa-info-card__icon{background:#ecfdf5;color:#15803d}.simansa-info-list{display:grid;grid-template-columns:150px minmax(0,1fr);gap:.65rem 1rem;padding:1rem 0;margin:0}.simansa-info-list dt{color:#64748b;font-size:.82rem}.simansa-info-list dd{margin:0;color:#1e293b;font-weight:600}
    @media(max-width:767.98px){.simansa-detail-hero{align-items:flex-start;flex-direction:column}.simansa-detail-hero__actions{width:100%;flex-wrap:wrap}.simansa-detail-status{align-items:flex-start;flex-wrap:wrap}.simansa-detail-status__time{width:100%;padding-left:0;border-left:0}.simansa-info-list{grid-template-columns:1fr}.simansa-info-list dd{margin-bottom:.35rem}.simansa-document-grid{grid-template-columns:1fr}.simansa-document-card__top{flex-wrap:wrap}.simansa-document-state{margin-left:auto}.simansa-document-viewer-loading,.simansa-document-viewer-image,.simansa-document-viewer-frame{height:62vh}}
</style>
@stop

@section('js')
<script>
$(function () {
    const modal = $('#referenceDocumentModal');
    const loading = $('#referenceDocumentLoading');
    const image = $('#referenceDocumentImage');
    const frame = $('#referenceDocumentFrame');

    $('.btn-preview-reference').on('click', function () {
        const button = $(this);
        const url = button.data('url');
        const extension = String(button.data('extension') || '').toLowerCase();
        const isImage = ['jpg', 'jpeg', 'png', 'gif', 'webp'].includes(extension);

        $('#referenceDocumentTitle').text(button.data('title'));
        $('#referenceDocumentFile').text(button.data('file') || 'Dokumen siswa');
        $('#referenceDocumentOpen').attr('href', url);
        loading.css('display', 'flex');
        image.hide().attr('src', '');
        frame.hide().attr('src', 'about:blank');
        modal.modal({backdrop: 'static', keyboard: true});

        if (isImage) {
            image.one('load', function () { loading.hide(); image.show(); });
            image.one('error', function () { loading.html('<i class="fas fa-exclamation-circle text-danger"></i><span>Preview gambar gagal dimuat. Gunakan tombol Buka Tab Baru.</span>'); });
            image.attr('src', url);
        } else {
            frame.one('load', function () { loading.hide(); frame.show(); });
            frame.attr('src', url);
        }
    });

    modal.on('hidden.bs.modal', function () {
        image.attr('src', '').hide();
        frame.attr('src', 'about:blank').hide();
        loading.html('<i class="fas fa-spinner fa-spin"></i><span>Memuat dokumen...</span>').css('display', 'flex');
    });
});
</script>
@stop
