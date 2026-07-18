@extends('adminlte::page')

@section('title', 'Cek Data EMIS')

@php
    $statusLabels = [
        'exact' => ['Sama', 'success'],
        'normalized' => ['Setara', 'info'],
        'similar' => ['Mirip', 'warning'],
        'different' => ['Berbeda', 'danger'],
        'only_simansa' => ['Hanya SIMANSA', 'secondary'],
        'only_emis' => ['Hanya EMIS', 'dark'],
    ];
    $detailLabels = \App\Services\SmartStudentComparator::LABELS;
    $syncFinishedAt = $latestSync?->finished_at ?? $latestSync?->started_at;
@endphp

@section('content_header')
    <div class="simansa-emis-hero">
        <div class="simansa-emis-hero__main">
            <div class="simansa-emis-hero__eyebrow"><i class="fas fa-exchange-alt"></i> Manajemen Data</div>
            <h1 class="simansa-emis-hero__title">Cek Data EMIS</h1>
            <p class="simansa-emis-hero__subtitle">Bandingkan siswa aktif SIMANSA dengan snapshot resmi EMIS Lembaga secara cepat dan terukur.</p>
        </div>
        <div class="simansa-emis-hero__meta">
            <div class="simansa-emis-hero-chip">
                <span>Siswa Aktif SIMANSA</span>
                <strong>{{ number_format($stats['simansa']) }}</strong>
            </div>
            <div class="simansa-emis-hero-chip">
                <span>Snapshot EMIS</span>
                <strong>{{ number_format($stats['emis']) }}</strong>
            </div>
            @can('sync-emis-comparison')
                <button type="button" id="btnSyncEmis" class="btn simansa-emis-sync-btn" @disabled(!$tokenStatus['usable'])>
                    <i class="fas fa-sync-alt mr-1"></i> Sinkronkan Data
                </button>
            @endcan
        </div>
    </div>
@stop

@section('content')
    <section class="simansa-token-panel simansa-token-panel--{{ $tokenStatus['usable'] ? ($tokenStatus['state'] === 'expiring' ? 'warning' : 'success') : 'danger' }} mb-4">
        <div class="simansa-token-icon"><i class="fas {{ $tokenStatus['usable'] ? 'fa-key' : 'fa-lock' }}"></i></div>
        <div class="simansa-token-copy">
            <h2>{{ $tokenStatus['usable'] ? 'Token EMIS Lembaga siap digunakan' : 'Token EMIS Lembaga perlu diperbarui' }}</h2>
            <p>
                {{ $tokenStatus['message'] }}
                @if($tokenStatus['expires_at']) Kedaluwarsa {{ $tokenStatus['expires_at']->format('d/m/Y H:i') }} WIB. @endif
                @if(!$tokenStatus['usable']) Snapshot terakhir tetap aman dan halaman ini tidak mengirim request sia-sia ke EMIS. @endif
            </p>
        </div>
        <div class="simansa-token-meta">
            @if($latestSync)
                <span>Sinkronisasi terakhir</span>
                <strong>{{ optional($syncFinishedAt)->format('d/m/Y H:i:s') }} WIB</strong>
                <small>{{ ucfirst($latestSync->status) }}@if($latestSync->status === 'completed') · {{ number_format($latestSync->total_students) }} siswa @endif</small>
            @else
                <span>Status snapshot</span><strong>Belum tersedia</strong><small>Lakukan sinkronisasi pertama.</small>
            @endif
        </div>
        @if(!$tokenStatus['usable'] || $tokenStatus['state'] === 'expiring')
            @can('manage-settings')
                <a href="{{ route('admin.pengaturan.update-api-token.index') }}#emis-institusi" class="btn btn-outline-primary btn-sm simansa-token-action">
                    <i class="fas fa-key mr-1"></i> Buka Pengaturan Token
                </a>
            @endcan
        @endif
    </section>

    <div class="row mb-2">
        @foreach([
            ['value' => $stats['exact'], 'label' => 'Sama / Setara', 'desc' => 'Data utama sudah sesuai antara SIMANSA dan snapshot EMIS.', 'tone' => 'green', 'filter' => 'exact'],
            ['value' => $stats['similar'] + $stats['different'], 'label' => 'Perlu Diperiksa', 'desc' => number_format($stats['similar']).' nama mirip dan '.number_format($stats['different']).' siswa memiliki perbedaan field.', 'tone' => 'amber', 'filter' => 'attention'],
            ['value' => $stats['only_simansa'], 'label' => 'Hanya SIMANSA', 'desc' => 'Siswa aktif SIMANSA yang belum ditemukan pada snapshot EMIS.', 'tone' => 'slate', 'filter' => 'only_simansa'],
            ['value' => $stats['only_emis'], 'label' => 'Hanya EMIS', 'desc' => 'Siswa pada snapshot EMIS yang belum memiliki pasangan di SIMANSA.', 'tone' => 'purple', 'filter' => 'only_emis'],
        ] as $card)
            <div class="col-md-6 col-xl-3 mb-4">
                <a href="{{ route('admin.emis-comparison.index', ['status' => $card['filter']]) }}" class="simansa-kpi-link">
                    <div class="simansa-emis-kpi simansa-emis-kpi--{{ $card['tone'] }} {{ $status === $card['filter'] ? 'is-active' : '' }}">
                        <div class="simansa-emis-kpi__label">{{ $card['label'] }}</div>
                        <div class="simansa-emis-kpi__value">{{ number_format($card['value']) }}</div>
                        <div class="simansa-emis-kpi__desc">{{ $card['desc'] }}</div>
                        <div class="simansa-emis-kpi__action"><i class="fas fa-arrow-right mr-1"></i> Tampilkan daftar</div>
                    </div>
                </a>
            </div>
        @endforeach
    </div>

    <section class="simansa-analytics-section mb-4">
        <div class="simansa-section-head">
            <div>
                <h3>Daftar Pembandingan</h3>
                <p>Pilih satu siswa untuk melihat perbedaan field secara berdampingan. Data berasal dari snapshot, sehingga membuka daftar tidak memanggil API EMIS.</p>
            </div>
            <span class="simansa-result-count">{{ number_format($items->total()) }} data</span>
        </div>

        <form method="GET" action="{{ route('admin.emis-comparison.index') }}" class="simansa-filter-panel">
            <div class="form-group mb-0">
                <label for="search">Nama atau NISN</label>
                <input id="search" type="text" name="search" value="{{ $search }}" class="form-control" placeholder="Cari data siswa...">
            </div>
            <div class="form-group mb-0">
                <label for="status">Status Pembandingan</label>
                <select id="status" name="status" class="form-control">
                    <option value="all" @selected($status === 'all')>Semua siswa SIMANSA</option>
                    <option value="exact" @selected($status === 'exact')>Sama / Setara</option>
                    <option value="attention" @selected($status === 'attention')>Perlu Diperiksa</option>
                    <option value="similar" @selected($status === 'similar')>Nama Mirip</option>
                    <option value="different" @selected($status === 'different')>Berbeda</option>
                    <option value="only_simansa" @selected($status === 'only_simansa')>Hanya di SIMANSA</option>
                    <option value="only_emis" @selected($status === 'only_emis')>Hanya di EMIS</option>
                </select>
            </div>
            <div class="form-group mb-0">
                <label for="kelas_id">Kelas SIMANSA</label>
                <select id="kelas_id" name="kelas_id" class="form-control" @disabled($listMode === 'emis')>
                    <option value="">Semua kelas</option>
                    @foreach($classes as $class)
                        <option value="{{ $class->id }}" @selected($kelasId === $class->id)>{{ $class->nama_kelas }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="btn btn-primary"><i class="fas fa-filter mr-1"></i> Terapkan Filter</button>
        </form>

        <div class="table-responsive simansa-table-shell">
            <table class="table table-hover simansa-table mb-0">
                <thead>
                    <tr class="simansa-group-header">
                        <th rowspan="2" class="simansa-col-number">No</th>
                        <th colspan="2" class="simansa-source-group simansa-source-group--local"><i class="fas fa-database mr-1"></i> Data SIMANSA</th>
                        <th colspan="2" class="simansa-source-group simansa-source-group--emis"><i class="fas fa-cloud mr-1"></i> Data EMIS Lembaga</th>
                        <th rowspan="2">Status</th>
                        <th rowspan="2">Field Perlu Dicek</th>
                        <th rowspan="2" class="text-right">Aksi</th>
                    </tr>
                    <tr class="simansa-sub-header">
                        <th>Identitas Siswa</th>
                        <th>Kelas</th>
                        <th class="simansa-emis-column-head">Identitas Siswa</th>
                        <th class="simansa-emis-column-head">Rombel</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($items as $index => $item)
                    @php
                        $siswa = $listMode === 'simansa' ? $item : null;
                        $snapshot = $listMode === 'simansa' ? $item->emisStudentSnapshot : $item;
                        $rowStatus = $snapshot?->comparison_status ?? 'only_simansa';
                        [$statusText, $statusColor] = $statusLabels[$rowStatus] ?? [ucfirst($rowStatus), 'secondary'];
                        $details = $snapshot?->comparison_details ?? [];
                        $differentFields = collect($details)->filter(fn($detail) => in_array($detail['status'] ?? '', ['different', 'similar', 'simansa_empty'], true))->keys();
                        $emisBirthDate = $snapshot?->birth_date?->format('d/m/Y');
                        $emisBirth = collect([$snapshot?->birth_place, $emisBirthDate])->filter()->implode(', ');
                    @endphp
                    <tr class="simansa-comparison-row">
                        <td class="simansa-col-number" data-label="Nomor">{{ $items->firstItem() + $index }}</td>
                        <td class="simansa-local-identity" data-label="Identitas SIMANSA">
                            @if($siswa)
                                <div class="simansa-table-title">{{ $siswa->nama_lengkap }}</div>
                                <div class="simansa-table-subtitle"><i class="fas fa-id-card mr-1"></i>NISN: {{ $siswa->nisn ?: '-' }}</div>
                            @else
                                <div class="simansa-empty-identity"><i class="fas fa-unlink mr-1"></i>Belum memiliki pasangan</div>
                            @endif
                        </td>
                        <td class="simansa-local-class" data-label="Kelas SIMANSA">{{ $siswa?->kelasSaatIni?->nama_kelas ?? '-' }}</td>
                        <td class="simansa-emis-identity" data-label="Identitas EMIS">
                            @if($snapshot)
                                <div class="simansa-table-title simansa-table-title--emis">{{ $snapshot->full_name ?: '-' }}</div>
                                <div class="simansa-table-subtitle"><i class="fas fa-id-card mr-1"></i>NISN: {{ $snapshot->nisn ?: '-' }}</div>
                                <div class="simansa-table-subtitle"><i class="fas fa-map-marker-alt mr-1"></i>{{ $emisBirth ?: 'Tempat/tanggal lahir belum tersedia' }}</div>
                            @else
                                <div class="simansa-empty-identity simansa-empty-identity--emis"><i class="fas fa-cloud-slash mr-1"></i>Belum ditemukan di EMIS</div>
                            @endif
                        </td>
                        <td class="simansa-emis-class" data-label="Rombel EMIS">{{ $snapshot?->study_group_name ?? '-' }}@if($snapshot?->level_name)<div class="simansa-table-subtitle">{{ $snapshot->level_name }}</div>@endif</td>
                        <td class="simansa-result-cell" data-label="Status"><span class="badge badge-{{ $statusColor }} simansa-status-badge">{{ $statusText }}</span>@if($rowStatus === 'similar' && $snapshot?->name_similarity)<div class="simansa-table-subtitle mt-1">{{ number_format($snapshot->name_similarity, 1) }}% mirip</div>@endif</td>
                        <td class="simansa-fields-cell" data-label="Field Perlu Dicek">@forelse($differentFields as $field)<span class="simansa-field-chip">{{ $detailLabels[$field] ?? $field }}</span>@empty<span class="text-muted">—</span>@endforelse</td>
                        <td class="text-right simansa-actions-cell" data-label="Aksi">
                            <div class="simansa-row-actions">
                                @if($siswa)
                                    @can('sync-emis-comparison')
                                        <button type="button" class="btn btn-sm btn-outline-success btn-sync-emis-student"
                                                data-url="{{ route('admin.emis-comparison.sync-student', $siswa) }}"
                                                data-name="{{ $siswa->nama_lengkap }}"
                                                @disabled(!$tokenStatus['usable'])
                                                title="{{ $tokenStatus['usable'] ? 'Ambil ulang data terbaru siswa ini dari EMIS' : 'Token EMIS Lembaga tidak aktif' }}">
                                            <i class="fas fa-sync-alt mr-1"></i> Sync Siswa
                                        </button>
                                    @endcan
                                @endif
                                <a href="{{ $siswa ? route('admin.emis-comparison.show', $siswa) : route('admin.emis-comparison.show-emis', $snapshot) }}" class="btn btn-sm btn-outline-primary"><i class="fas fa-columns mr-1"></i> Bandingkan</a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="text-center text-muted py-5"><i class="fas fa-search d-block mb-2 fa-2x text-light"></i>Tidak ada data untuk filter ini.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        @if($items->hasPages())<div class="simansa-pagination">{{ $items->onEachSide(1)->links() }}</div>@endif
    </section>

    <div id="emisSyncOverlay" class="simansa-progress-overlay" aria-hidden="true">
        <div class="simansa-progress-modal" role="dialog" aria-modal="true" aria-labelledby="emisProgressTitle">
            <div class="simansa-progress-modal__head">
                <div><div class="simansa-progress-eyebrow"><i class="fas fa-cloud-download-alt mr-1"></i> Sinkronisasi EMIS</div><h3 id="emisProgressTitle">Memperbarui Snapshot Siswa</h3><p id="emisProgressDescription">Proses berjalan di server dan dapat dipantau langsung.</p></div>
                <button type="button" class="btn btn-sm btn-light" id="btnCloseEmisOverlay" disabled><i class="fas fa-times mr-1"></i> Tutup</button>
            </div>
            <div class="simansa-progress-summary">
                <div><span id="emisProgressText">Menyiapkan proses...</span><strong id="emisProgressCounter">0%</strong></div>
                <div class="progress simansa-progress-bar"><div id="emisProgressBar" class="progress-bar bg-primary progress-bar-striped progress-bar-animated" role="progressbar" style="width:0%"></div></div>
            </div>
            <div class="simansa-progress-log" id="emisProgressLog"></div>
        </div>
    </div>
@stop

@section('css')
<style>
    .simansa-emis-hero{display:flex;justify-content:space-between;align-items:stretch;gap:1rem;padding:1.35rem 1.45rem;border-radius:16px;background:#3b82f6;color:#fff;box-shadow:0 14px 32px rgba(59,130,246,.22)}
    .simansa-emis-hero__main{max-width:760px}.simansa-emis-hero__eyebrow{font-size:.8rem;font-weight:700;text-transform:uppercase;letter-spacing:.04em;margin-bottom:.65rem;color:rgba(255,255,255,.92)}
    .simansa-emis-hero__title{font-size:1.45rem;font-weight:700;color:#fff;margin-bottom:.35rem}.simansa-emis-hero__subtitle{margin:0;color:rgba(255,255,255,.9);line-height:1.55}
    .simansa-emis-hero__meta{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:.7rem;min-width:340px}.simansa-emis-hero-chip{display:flex;flex-direction:column;justify-content:center;padding:.75rem 1rem;border-radius:12px;background:rgba(255,255,255,.14);border:1px solid rgba(255,255,255,.18)}
    .simansa-emis-hero-chip span{font-size:.78rem;color:rgba(255,255,255,.78)}.simansa-emis-hero-chip strong{font-size:1.35rem}.simansa-emis-sync-btn{grid-column:1/-1;background:#fff;color:#2563eb;font-weight:700;border-radius:9px}.simansa-emis-sync-btn:hover{background:#eff6ff;color:#1d4ed8}.simansa-emis-sync-btn:disabled{opacity:.62}
    .simansa-token-panel{display:flex;align-items:center;gap:1rem;padding:1rem 1.15rem;border-radius:14px;background:#fff;border:1px solid #dbe4f0;border-left:4px solid #22c55e;box-shadow:0 8px 24px rgba(15,23,42,.04)}.simansa-token-panel--warning{border-left-color:#f59e0b}.simansa-token-panel--danger{border-left-color:#e11d48}
    .simansa-token-icon{width:46px;height:46px;display:flex;align-items:center;justify-content:center;flex:0 0 auto;border-radius:12px;background:#ecfdf5;color:#15803d}.simansa-token-panel--warning .simansa-token-icon{background:#fffbeb;color:#b45309}.simansa-token-panel--danger .simansa-token-icon{background:#fff1f2;color:#be123c}
    .simansa-token-copy{flex:1}.simansa-token-copy h2{font-size:1rem;font-weight:700;color:#0f172a;margin:0 0 .25rem}.simansa-token-copy p{color:#64748b;margin:0;line-height:1.45}.simansa-token-meta{display:flex;flex-direction:column;padding-left:1rem;border-left:1px solid #e2e8f0;min-width:230px}.simansa-token-meta span,.simansa-token-meta small{font-size:.76rem;color:#64748b}.simansa-token-meta strong{color:#1e293b;font-size:.9rem}.simansa-token-action{white-space:nowrap}
    a.simansa-kpi-link{display:block;height:100%;text-decoration:none!important;color:inherit}.simansa-emis-kpi{display:flex;flex-direction:column;min-height:158px;height:100%;padding:1.05rem 1.15rem;border-radius:14px;background:#fff;border:1px solid #dbe4f0;border-top:4px solid #3b82f6;box-shadow:0 10px 28px rgba(15,23,42,.05);transition:.18s}.simansa-emis-kpi:hover,.simansa-emis-kpi.is-active{transform:translateY(-2px);box-shadow:0 16px 34px rgba(15,23,42,.1);border-color:#bfdbfe}.simansa-emis-kpi.is-active{background:#f8fbff}
    .simansa-emis-kpi__label{font-size:.76rem;color:#64748b;text-transform:uppercase;letter-spacing:.035em;font-weight:700}.simansa-emis-kpi__value{font-size:1.45rem;font-weight:700;color:#2563eb;line-height:1.25;margin:.18rem 0 .3rem}.simansa-emis-kpi__desc{font-size:.84rem;line-height:1.5;color:#64748b;flex:1}.simansa-emis-kpi__action{margin-top:.55rem;font-size:.78rem;font-weight:700;color:#2563eb;opacity:.68}.simansa-emis-kpi:hover .simansa-emis-kpi__action{opacity:1}
    .simansa-emis-kpi--purple{border-top-color:#8b5cf6}.simansa-emis-kpi--purple .simansa-emis-kpi__value,.simansa-emis-kpi--purple .simansa-emis-kpi__action{color:#7c3aed}.simansa-emis-kpi--green{border-top-color:#22c55e}.simansa-emis-kpi--green .simansa-emis-kpi__value,.simansa-emis-kpi--green .simansa-emis-kpi__action{color:#15803d}.simansa-emis-kpi--amber{border-top-color:#f59e0b}.simansa-emis-kpi--amber .simansa-emis-kpi__value,.simansa-emis-kpi--amber .simansa-emis-kpi__action{color:#b45309}.simansa-emis-kpi--slate{border-top-color:#0f766e}.simansa-emis-kpi--slate .simansa-emis-kpi__value,.simansa-emis-kpi--slate .simansa-emis-kpi__action{color:#0f766e}
    .simansa-analytics-section{padding:1.1rem 1.25rem;border-radius:14px;background:#fff;border:1px solid #dbe4f0;box-shadow:0 10px 28px rgba(15,23,42,.05)}.simansa-section-head{display:flex;justify-content:space-between;gap:1rem;align-items:flex-start;margin-bottom:1rem}.simansa-section-head h3{font-size:1.05rem;font-weight:700;color:#0f172a;margin:0 0 .3rem}.simansa-section-head p{color:#64748b;line-height:1.55;margin:0;max-width:800px}.simansa-result-count{white-space:nowrap;background:#eef4ff;color:#2563eb;border-radius:999px;padding:.4rem .75rem;font-size:.8rem;font-weight:700}
    .simansa-filter-panel{display:grid;grid-template-columns:1.2fr 1fr 1fr auto;gap:.8rem;align-items:end;padding:1rem;margin-bottom:1rem;border:1px solid #e5edf7;border-radius:12px;background:#f8fafc}.simansa-filter-panel label{font-size:.78rem;text-transform:uppercase;letter-spacing:.03em;color:#64748b}.simansa-filter-panel .form-control{border-color:#dbe4f0;border-radius:8px}.simansa-filter-panel .btn{height:38px;border-radius:8px}
    .simansa-table-shell{border:1px solid #e5edf7;border-radius:12px;overflow-x:auto!important;overflow-y:hidden;-webkit-overflow-scrolling:touch}.simansa-table-shell::-webkit-scrollbar{height:9px}.simansa-table-shell::-webkit-scrollbar-track{background:#f1f5f9}.simansa-table-shell::-webkit-scrollbar-thumb{background:#bfdbfe;border-radius:999px}.simansa-table{min-width:1240px}.simansa-table thead th{border:0;background:#f8fafc;color:#64748b;font-size:.72rem;text-transform:uppercase;letter-spacing:.035em;padding:.68rem .78rem;vertical-align:middle}.simansa-group-header th{border-bottom:1px solid #dbe4f0!important}.simansa-source-group{text-align:center!important;font-weight:800!important;font-size:.76rem!important}.simansa-source-group--local{background:#eff6ff!important;color:#1d4ed8!important;border-bottom:3px solid #3b82f6!important}.simansa-source-group--emis{background:#ecfdf5!important;color:#15803d!important;border-bottom:3px solid #22c55e!important}.simansa-sub-header th:nth-child(-n+2){background:#f8fbff;color:#2563eb}.simansa-table td{vertical-align:middle;border-color:#edf2f7;padding:.78rem}.simansa-local-identity,.simansa-local-class{background:rgba(239,246,255,.3)}.simansa-local-class{border-right:2px solid #dbeafe}.simansa-table-title{font-weight:700;color:#1e293b}.simansa-table-title--emis{color:#166534}.simansa-table-subtitle{font-size:.76rem;color:#94a3b8;line-height:1.55}.simansa-table-subtitle i{width:13px;text-align:center;color:#94a3b8}.simansa-emis-column-head{background:#f0fdf4!important;color:#15803d!important}.simansa-emis-identity,.simansa-emis-class{background:rgba(240,253,244,.42)}.simansa-emis-class{border-right:2px solid #dcfce7}.simansa-empty-identity{font-size:.78rem;color:#94a3b8;font-style:italic}.simansa-empty-identity--emis{color:#b45309}.simansa-status-badge{padding:.42rem .58rem;min-width:74px}.simansa-field-chip{display:inline-flex;padding:.25rem .5rem;margin:0 .2rem .2rem 0;border-radius:999px;background:#f1f5f9;color:#475569;font-size:.72rem;font-weight:600}.simansa-row-actions{display:flex;justify-content:flex-end;gap:.45rem;white-space:nowrap}.simansa-pagination{padding-top:1rem}.simansa-pagination nav,.simansa-pagination>div{width:100%}.simansa-pagination .pagination{margin-bottom:0}.simansa-pagination .page-link{min-width:36px;text-align:center;border-color:#dbe4f0;color:#2563eb}.simansa-pagination .page-item.active .page-link{background:#2563eb;border-color:#2563eb;color:#fff}
    .simansa-progress-overlay{position:fixed;inset:0;z-index:1080;display:none;align-items:center;justify-content:center;padding:1.25rem;background:rgba(15,23,42,.58);backdrop-filter:blur(4px)}.simansa-progress-overlay.is-active{display:flex}.simansa-progress-modal{width:min(760px,100%);max-height:min(720px,92vh);display:flex;flex-direction:column;border-radius:18px;background:#fff;border:1px solid #d9e3f0;box-shadow:0 26px 70px rgba(15,23,42,.28);overflow:hidden}.simansa-progress-modal__head{display:flex;justify-content:space-between;align-items:flex-start;gap:1rem;padding:1.1rem 1.2rem;color:#fff;background:linear-gradient(135deg,#2563eb 0%,#0f766e 100%)}.simansa-progress-modal__head h3{font-size:1.25rem;font-weight:700;margin:.2rem 0 .25rem}.simansa-progress-modal__head p{color:rgba(255,255,255,.84);margin:0}.simansa-progress-eyebrow{font-size:.78rem;font-weight:700;text-transform:uppercase;letter-spacing:.04em}.simansa-progress-summary{padding:1rem 1.2rem;border-bottom:1px solid #e5edf7;background:#f8fafc}.simansa-progress-summary>div:first-child{display:flex;justify-content:space-between;gap:1rem;margin-bottom:.75rem;color:#334155}.simansa-progress-bar{height:.72rem;border-radius:999px;background:#e2e8f0}.simansa-progress-log{padding:.25rem 1.2rem 1rem;overflow:auto;min-height:220px;max-height:380px}.simansa-progress-log-row{display:grid;grid-template-columns:92px minmax(0,1fr);gap:.75rem;padding:.82rem 0;border-bottom:1px solid #edf2f7;color:#334155}.simansa-progress-log-row strong{color:#0f172a}.simansa-progress-log-meta{color:#64748b;font-size:.82rem;margin-top:.15rem}
    @media(max-width:991.98px){.simansa-emis-hero{flex-direction:column}.simansa-emis-hero__meta{min-width:0}.simansa-token-panel{align-items:flex-start;flex-wrap:wrap}.simansa-token-meta{border-left:0;padding-left:0;min-width:0;width:100%}.simansa-filter-panel{grid-template-columns:1fr 1fr}.simansa-filter-panel .btn{grid-column:1/-1}.simansa-table-shell{border:0;overflow:visible!important;background:transparent}.simansa-table{display:block;min-width:0}.simansa-table thead{display:none}.simansa-table tbody{display:grid;gap:1rem}.simansa-table .simansa-comparison-row{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:0;border:1px solid #dbe4f0;border-radius:14px;background:#fff;box-shadow:0 8px 22px rgba(15,23,42,.06);overflow:hidden}.simansa-table .simansa-comparison-row td{display:block;border:0;padding:.82rem}.simansa-table .simansa-comparison-row td::before{content:attr(data-label);display:block;margin-bottom:.4rem;color:#64748b;font-size:.66rem;font-weight:800;text-transform:uppercase;letter-spacing:.045em}.simansa-table .simansa-col-number{display:none}.simansa-local-identity,.simansa-local-class{background:#f8fbff}.simansa-local-identity{border-top:4px solid #3b82f6!important}.simansa-local-class{border:0;border-top:4px solid #3b82f6!important}.simansa-emis-identity,.simansa-emis-class{background:#f7fef9}.simansa-emis-class{border:0}.simansa-result-cell,.simansa-fields-cell{border-top:1px solid #edf2f7!important}.simansa-actions-cell{grid-column:1/-1;border-top:1px solid #edf2f7!important;background:#f8fafc}.simansa-row-actions{justify-content:stretch}.simansa-row-actions .btn{flex:1}.simansa-table tbody>tr:not(.simansa-comparison-row){display:block;grid-column:1/-1}.simansa-table tbody>tr:not(.simansa-comparison-row) td{display:block}.simansa-pagination{padding-top:1.2rem}}
    @media(max-width:575.98px){.simansa-emis-hero__meta,.simansa-filter-panel{grid-template-columns:1fr}.simansa-emis-sync-btn,.simansa-filter-panel .btn{grid-column:auto}.simansa-section-head{flex-direction:column}.simansa-progress-log-row{grid-template-columns:1fr}}
</style>
@stop

@section('js')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
$(function () {
    const startUrl = @json(route('admin.emis-comparison.sync'));
    const csrfToken = @json(csrf_token());
    const overlay = $('#emisSyncOverlay');
    const closeButton = $('#btnCloseEmisOverlay');
    let pollTimer = null;
    let lastLogKey = '';
    let reloadOnClose = false;

    function notify(type, message) {
        if (window.toastr && typeof window.toastr[type] === 'function') {
            window.toastr[type](message);
            return;
        }
        Swal.fire({icon: type === 'success' ? 'success' : 'error', title: type === 'success' ? 'Berhasil' : 'Proses gagal', text: message});
    }

    function addLog(sync) {
        const key = [sync.stage, sync.progress_message, sync.progress_percent].join('|');
        if (key === lastLogKey) return;
        lastLogKey = key;
        const time = new Date().toLocaleTimeString('id-ID', {hour:'2-digit', minute:'2-digit', second:'2-digit'});
        const label = String(sync.stage || 'proses').replaceAll('_', ' ').replace(/\b\w/g, c => c.toUpperCase());
        $('#emisProgressLog').prepend('<div class="simansa-progress-log-row"><strong>' + $('<div>').text(time).html() + '</strong><div><strong>' + $('<div>').text(label).html() + '</strong><div class="simansa-progress-log-meta">' + $('<div>').text(sync.progress_message || 'Proses diperbarui.').html() + '</div></div></div>');
    }

    function renderProgress(sync) {
        const percent = Math.max(0, Math.min(100, Number(sync.progress_percent || 0)));
        $('#emisProgressText').text(sync.progress_message || 'Memproses data...');
        $('#emisProgressCounter').text(percent + '%');
        $('#emisProgressBar').css('width', percent + '%').attr('aria-valuenow', percent);
        addLog(sync);

        if (sync.status === 'completed') finishProgress(true, sync.progress_message || 'Sinkronisasi selesai.');
        if (sync.status === 'failed') finishProgress(false, sync.error_message || sync.progress_message || 'Sinkronisasi gagal.');
    }

    function finishProgress(success, message) {
        clearInterval(pollTimer);
        pollTimer = null;
        const bar = $('#emisProgressBar').removeClass('progress-bar-animated bg-primary');
        bar.addClass(success ? 'bg-success' : 'bg-danger');
        $('#emisProgressTitle').text(success ? 'Sinkronisasi Selesai' : 'Sinkronisasi Gagal');
        closeButton.prop('disabled', false).html(success ? '<i class="fas fa-table mr-1"></i> Lihat Hasil' : '<i class="fas fa-times mr-1"></i> Tutup');
        reloadOnClose = success;
        notify(success ? 'success' : 'error', message);
    }

    function poll(statusUrl) {
        const check = function () {
            $.getJSON(statusUrl).done(function (response) { renderProgress(response.sync); }).fail(function () {
                $('#emisProgressText').text('Koneksi pemantauan terputus, mencoba kembali...');
            });
        };
        check();
        pollTimer = setInterval(check, 700);
    }

    function beginSync() {
        lastLogKey = '';
        reloadOnClose = false;
        $('#emisProgressLog').empty();
        $('#emisProgressTitle').text('Memperbarui Snapshot Siswa');
        $('#emisProgressDescription').text('Proses berjalan di server dan dapat dipantau langsung.');
        $('#emisProgressBar').removeClass('bg-success bg-danger').addClass('bg-primary progress-bar-animated').css('width', '0%');
        closeButton.prop('disabled', true).html('<i class="fas fa-times mr-1"></i> Tutup');
        overlay.addClass('is-active').attr('aria-hidden', 'false');

        $.ajax({url:startUrl, method:'POST', headers:{'X-CSRF-TOKEN':csrfToken}, dataType:'json'})
            .done(function (response) {
                renderProgress(response.sync);
                poll(response.status_url);
                $.ajax({url:response.process_url, method:'POST', headers:{'X-CSRF-TOKEN':csrfToken}, dataType:'json'})
                    .fail(function (xhr) {
                        if (!pollTimer) return;
                        const message = xhr.responseJSON?.message || 'Server gagal menjalankan sinkronisasi.';
                        $('#emisProgressText').text(message);
                    });
            })
            .fail(function (xhr) {
                const message = xhr.responseJSON?.message || 'Permintaan sinkronisasi tidak dapat dimulai.';
                finishProgress(false, message);
            });
    }

    $('#btnSyncEmis').on('click', function () {
        Swal.fire({
            title:'Sinkronkan data EMIS?',
            text:'Snapshot lama baru diganti setelah seluruh data EMIS berhasil diterima dan dibandingkan.',
            icon:'question', showCancelButton:true, confirmButtonText:'Ya, sinkronkan', cancelButtonText:'Batal',
            confirmButtonColor:'#2563eb', reverseButtons:true
        }).then(result => { if (result.isConfirmed) beginSync(); });
    });

    function syncOneStudent(button) {
        const name = button.data('name');
        const url = button.data('url');
        let stageTimer = null;

        Swal.fire({
            title: 'Sinkronkan siswa ini?',
            text: 'SIMANSA hanya akan mengambil ulang data EMIS untuk ' + name + ' dan menghitung ulang perbandingannya.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Ya, sync siswa ini',
            cancelButtonText: 'Batal',
            confirmButtonColor: '#16a34a',
            reverseButtons: true
        }).then(function (result) {
            if (!result.isConfirmed) return;

            button.prop('disabled', true).find('i').addClass('fa-spin');
            Swal.fire({
                title: 'Mengambil data terbaru',
                html: '<div id="singleSyncName" class="font-weight-bold text-dark mb-2"></div><div id="singleSyncStage" class="text-muted mb-3">Menghubungkan ke API EMIS Lembaga...</div><div class="progress" style="height:10px;border-radius:999px"><div class="progress-bar progress-bar-striped progress-bar-animated bg-success" style="width:100%"></div></div><small class="d-block text-muted mt-3">Snapshot lama tetap aman sampai data baru berhasil diterima.</small>',
                showConfirmButton: false,
                allowOutsideClick: false,
                allowEscapeKey: false,
                didOpen: function () {
                    $('#singleSyncName').text(name);
                    stageTimer = setTimeout(function () {
                        $('#singleSyncStage').text('Mencari NISN yang sama persis dan membandingkan setiap field...');
                    }, 1800);
                }
            });

            $.ajax({url:url, method:'POST', headers:{'X-CSRF-TOKEN':csrfToken}, dataType:'json'})
                .done(function (response) {
                    clearTimeout(stageTimer);
                    Swal.fire({
                        icon:'success', title:'Data siswa diperbarui', text:response.message,
                        confirmButtonText:'Lihat hasil terbaru', confirmButtonColor:'#2563eb'
                    }).then(function () { window.location.reload(); });
                })
                .fail(function (xhr) {
                    clearTimeout(stageTimer);
                    button.prop('disabled', false).find('i').removeClass('fa-spin');
                    Swal.fire({
                        icon:'error', title:'Sync siswa gagal',
                        text:xhr.responseJSON?.message || 'Data EMIS siswa belum dapat diperbarui.',
                        confirmButtonText:'Tutup'
                    });
                });
        });
    }

    $('.btn-sync-emis-student').on('click', function () {
        syncOneStudent($(this));
    });

    closeButton.on('click', function () {
        if ($(this).prop('disabled')) return;
        overlay.removeClass('is-active').attr('aria-hidden', 'true');
        if (reloadOnClose) window.location.reload();
    });

    @if(session('success')) notify('success', @json(session('success'))); @endif
    @if(session('error')) notify('error', @json(session('error'))); @endif
});
</script>
@stop
