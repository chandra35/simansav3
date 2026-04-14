@extends('adminlte::page')

@section('title', $smartq->nama)

@section('content_header')
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1><i class="fas fa-star text-warning"></i> {{ $smartq->nama }}</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.smartq.index') }}">SMART-Q</a></li>
                <li class="breadcrumb-item active">Detail</li>
            </ol>
        </div>
    </div>
@stop

@section('content')
    @foreach (['success', 'error', 'warning'] as $msg)
        @if(session($msg))
            <div class="alert alert-{{ $msg === 'error' ? 'danger' : $msg }} alert-dismissible fade show">
                <i class="fas fa-{{ $msg === 'success' ? 'check-circle' : ($msg === 'error' ? 'exclamation-circle' : 'exclamation-triangle') }}"></i>
                {{ session($msg) }}
                <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
            </div>
        @endif
    @endforeach

    {{-- Info & Actions Bar --}}
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-info-circle"></i> Informasi Periode</h3>
                    <div class="card-tools">{!! $smartq->status_badge !!}</div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-sm table-borderless">
                                <tr><td class="text-muted" width="150">Tahun Pelajaran</td><td><strong>{{ $smartq->tahunPelajaran->nama ?? '-' }}</strong></td></tr>
                                <tr><td class="text-muted">Periode</td><td>{{ $smartq->tanggal_mulai->format('d M Y') }} - {{ $smartq->tanggal_selesai->format('d M Y') }}</td></tr>
                                <tr><td class="text-muted">Kuota</td><td><strong>{{ $smartq->kuota }}</strong> siswa</td></tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-sm table-borderless">
                                <tr><td class="text-muted" width="150">Moodle URL</td><td>{{ $smartq->moodle_base_url ?? '-' }}</td></tr>
                                @if(!empty($smartq->moodle_quizzes))
                                    <tr>
                                        <td class="text-muted">Kuis Terkonfigurasi</td>
                                        <td>
                                            <span class="badge badge-success">{{ count($smartq->moodle_quizzes) }} kuis</span>
                                            dari <span class="badge badge-info">{{ count($smartq->moodle_course_ids) }} course</span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted" colspan="2">
                                            <small>
                                            @foreach($smartq->moodle_quizzes as $qz)
                                                <span class="badge badge-light">{{ $qz['course_name'] ?? '' }} &raquo; {{ $qz['quiz_name'] ?? '' }}</span>
                                            @endforeach
                                            </small>
                                        </td>
                                    </tr>
                                @else
                                    <tr><td class="text-muted">Kategori</td><td>{{ $smartq->moodle_category_name ?? '-' }}</td></tr>
                                    <tr><td class="text-muted">Course</td><td>{{ $smartq->moodle_course_name ?? '-' }}</td></tr>
                                    <tr><td class="text-muted">Quiz Moodle</td><td>{{ $smartq->moodle_quiz_name ?? 'Belum dikonfigurasi' }}</td></tr>
                                @endif
                                <tr><td class="text-muted">Deskripsi</td><td>{{ $smartq->deskripsi ?? '-' }}</td></tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-header bg-gradient-dark text-white">
                    <h3 class="card-title"><i class="fas fa-cogs"></i> Aksi</h3>
                </div>
                <div class="card-body p-2">
                    <div class="list-group list-group-flush">
                        <a href="{{ route('admin.smartq.peserta', $smartq) }}" class="list-group-item list-group-item-action">
                            <i class="fas fa-user-plus text-info"></i> Kelola Peserta
                        </a>
                        <a href="{{ route('admin.smartq.nilai', $smartq) }}" class="list-group-item list-group-item-action">
                            <i class="fas fa-pencil-alt text-success"></i> Input Nilai Manual
                        </a>
                        @if($smartq->moodle_base_url)
                            <a href="{{ route('admin.smartq.moodle.config', $smartq) }}" class="list-group-item list-group-item-action">
                                <i class="fas fa-cloud text-primary"></i> Konfigurasi Moodle
                            </a>
                            @if(!empty($smartq->moodle_quizzes) || $smartq->moodle_quiz_id)
                                <form action="{{ route('admin.smartq.moodle.sync', $smartq) }}" method="POST" class="d-inline" id="formSyncMoodle">
                                    @csrf
                                    <button type="submit" class="list-group-item list-group-item-action text-left border-0">
                                        <i class="fas fa-sync text-warning"></i> Sync Nilai CBT Moodle
                                    </button>
                                </form>
                            @endif
                            @if(!empty($smartq->moodle_quizzes) || $smartq->moodle_course_id)
                                <a href="{{ route('admin.smartq.moodle.scan', $smartq) }}" class="list-group-item list-group-item-action">
                                    <i class="fas fa-cloud-download-alt text-success"></i> Scan Peserta dari Moodle
                                </a>
                            @endif
                            @if($hasScanData ?? false)
                                <a href="{{ route('admin.smartq.nilai-cbt', $smartq) }}" class="list-group-item list-group-item-action list-group-item-info">
                                    <i class="fas fa-chart-line text-primary"></i> <strong>Nilai CBT / Smart Score</strong>
                                    <span class="badge badge-primary float-right">{{ $smartq->last_scan_at?->diffForHumans() }}</span>
                                </a>
                                <a href="{{ route('admin.smartq.moodle.scan.export', ['smartq' => $smartq, 'format' => 'excel']) }}" class="list-group-item list-group-item-action">
                                    <i class="fas fa-file-excel text-success"></i> Export Nilai CBT (Excel)
                                </a>
                                <a href="{{ route('admin.smartq.moodle.scan.export', ['smartq' => $smartq, 'format' => 'excel_hadir']) }}" class="list-group-item list-group-item-action">
                                    <i class="fas fa-file-excel text-success"></i> Export Nilai CBT Hadir Saja (Excel)
                                </a>
                                <a href="{{ route('admin.smartq.moodle.scan.export', ['smartq' => $smartq, 'format' => 'pdf']) }}" class="list-group-item list-group-item-action">
                                    <i class="fas fa-file-pdf text-danger"></i> Export Nilai CBT (PDF)
                                </a>
                            @endif
                        @endif
                        <a href="{{ route('admin.smartq.kelulusan.import', $smartq) }}" class="list-group-item list-group-item-action list-group-item-warning">
                            <i class="fas fa-file-import text-warning"></i> <strong>Import Kelulusan & Bidang</strong>
                        </a>
                        @if($stats['lulus'] + $stats['cadangan'] + $stats['tidak_lulus'] > 0)
                        <button type="button" class="list-group-item list-group-item-action list-group-item-danger text-left" id="btnResetBulk">
                            <i class="fas fa-undo text-danger"></i> Reset Semua Status Kelulusan
                        </button>
                        @endif
                        <a href="{{ route('admin.smartq.export', $smartq) }}" class="list-group-item list-group-item-action">
                            <i class="fas fa-file-excel text-success"></i> Export Excel
                        </a>
                        <a href="{{ route('admin.smartq.edit', $smartq) }}" class="list-group-item list-group-item-action">
                            <i class="fas fa-edit text-warning"></i> Edit Periode
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Statistik Status --}}
    <div class="row">
        <div class="col-lg-2 col-md-4 col-6">
            <div class="small-box bg-gradient-info">
                <div class="inner"><h3>{{ $stats['total'] }}</h3><p>Total Peserta</p></div>
                <div class="icon"><i class="fas fa-users"></i></div>
            </div>
        </div>
        <div class="col-lg-2 col-md-4 col-6">
            <div class="small-box bg-gradient-success">
                <div class="inner"><h3>{{ $stats['lulus'] }}</h3><p>Diterima</p></div>
                <div class="icon"><i class="fas fa-check-circle"></i></div>
            </div>
        </div>
        <div class="col-lg-2 col-md-4 col-6">
            <div class="small-box bg-gradient-warning">
                <div class="inner"><h3>{{ $stats['cadangan'] }}</h3><p>Cadangan</p></div>
                <div class="icon"><i class="fas fa-hourglass-half"></i></div>
            </div>
        </div>
        <div class="col-lg-2 col-md-4 col-6">
            <div class="small-box bg-gradient-danger">
                <div class="inner"><h3>{{ $stats['tidak_lulus'] }}</h3><p>Tidak Lulus</p></div>
                <div class="icon"><i class="fas fa-times-circle"></i></div>
            </div>
        </div>
        <div class="col-lg-2 col-md-4 col-6">
            <div class="small-box bg-gradient-primary">
                <div class="inner"><h3>{{ number_format($stats['rata_rata'], 1) }}</h3><p>Rata-rata Nilai</p></div>
                <div class="icon"><i class="fas fa-chart-bar"></i></div>
            </div>
        </div>
        <div class="col-lg-2 col-md-4 col-6">
            <div class="small-box bg-gradient-secondary">
                <div class="inner"><h3>{{ $stats['terdaftar'] }}</h3><p>Belum Diproses</p></div>
                <div class="icon"><i class="fas fa-clock"></i></div>
            </div>
        </div>
    </div>

    {{-- Ringkasan Status Kelulusan --}}
    @if($stats['total'] > 0)
        @php
            $total = $stats['total'];
            $pctLulus = $total > 0 ? round($stats['lulus'] / $total * 100, 1) : 0;
            $pctCadangan = $total > 0 ? round($stats['cadangan'] / $total * 100, 1) : 0;
            $pctTidak = $total > 0 ? round($stats['tidak_lulus'] / $total * 100, 1) : 0;
            $pctTerdaftar = $total > 0 ? round($stats['terdaftar'] / $total * 100, 1) : 0;
            $processed = $stats['lulus'] + $stats['cadangan'] + $stats['tidak_lulus'];
        @endphp
        <div class="card card-outline card-success">
            <div class="card-header py-2">
                <h3 class="card-title"><i class="fas fa-chart-pie text-success"></i> Ringkasan Status Kelulusan</h3>
                @if($smartq->status !== 'selesai')
                    <div class="card-tools">
                        <a href="{{ route('admin.smartq.kelulusan.import', $smartq) }}" class="btn btn-sm btn-warning">
                            <i class="fas fa-file-import"></i> Import Kelulusan & Bidang
                        </a>
                    </div>
                @endif
            </div>
            <div class="card-body py-3">
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Progres Kelulusan</span>
                    <strong>{{ $processed }}/{{ $total }} peserta diproses ({{ $total > 0 ? round($processed / $total * 100) : 0 }}%)</strong>
                </div>
                <div class="progress" style="height: 24px; border-radius: 8px;">
                    @if($pctLulus > 0)
                        <div class="progress-bar bg-success" style="width: {{ $pctLulus }}%" title="Diterima: {{ $stats['lulus'] }}">
                            @if($pctLulus >= 8) {{ $stats['lulus'] }} @endif
                        </div>
                    @endif
                    @if($pctCadangan > 0)
                        <div class="progress-bar bg-warning" style="width: {{ $pctCadangan }}%" title="Cadangan: {{ $stats['cadangan'] }}">
                            @if($pctCadangan >= 8) {{ $stats['cadangan'] }} @endif
                        </div>
                    @endif
                    @if($pctTidak > 0)
                        <div class="progress-bar bg-danger" style="width: {{ $pctTidak }}%" title="Tidak Lulus: {{ $stats['tidak_lulus'] }}">
                            @if($pctTidak >= 8) {{ $stats['tidak_lulus'] }} @endif
                        </div>
                    @endif
                    @if($pctTerdaftar > 0)
                        <div class="progress-bar bg-secondary" style="width: {{ $pctTerdaftar }}%" title="Belum Diproses: {{ $stats['terdaftar'] }}">
                            @if($pctTerdaftar >= 8) {{ $stats['terdaftar'] }} @endif
                        </div>
                    @endif
                </div>
                <div class="mt-2 text-center">
                    <span class="badge badge-success px-2 mr-1"><i class="fas fa-check-circle"></i> Diterima: {{ $stats['lulus'] }} ({{ $pctLulus }}%)</span>
                    <span class="badge badge-warning px-2 mr-1"><i class="fas fa-hourglass-half"></i> Cadangan: {{ $stats['cadangan'] }} ({{ $pctCadangan }}%)</span>
                    <span class="badge badge-danger px-2 mr-1"><i class="fas fa-times-circle"></i> Tidak Lulus: {{ $stats['tidak_lulus'] }} ({{ $pctTidak }}%)</span>
                    <span class="badge badge-secondary px-2"><i class="fas fa-clock"></i> Belum: {{ $stats['terdaftar'] }} ({{ $pctTerdaftar }}%)</span>
                </div>
                @if($stats['lulus'] > 0 || $stats['cadangan'] > 0)
                    <div class="row mt-3 pt-3 border-top">
                        <div class="col-md-4 text-center">
                            <span class="text-muted d-block">Nilai Tertinggi</span>
                            <strong class="text-success" style="font-size: 1.25rem;">{{ number_format($stats['tertinggi'], 1) }}</strong>
                        </div>
                        <div class="col-md-4 text-center">
                            <span class="text-muted d-block">Rata-rata Nilai</span>
                            <strong class="text-primary" style="font-size: 1.25rem;">{{ number_format($stats['rata_rata'], 1) }}</strong>
                        </div>
                        <div class="col-md-4 text-center">
                            <span class="text-muted d-block">Nilai Terendah</span>
                            <strong class="text-danger" style="font-size: 1.25rem;">{{ number_format($stats['terendah'], 1) }}</strong>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    @endif

    {{-- Komponen Nilai --}}
    <div class="card card-outline card-info">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-balance-scale"></i> Komponen Penilaian</h3>
        </div>
        <div class="card-body p-0">
            <table class="table table-striped table-sm mb-0">
                <thead>
                    <tr>
                        <th width="40">#</th>
                        <th>Komponen</th>
                        <th width="100">Kode</th>
                        <th width="100">Bobot</th>
                        <th width="120">Nilai Maks</th>
                        <th width="120">Sumber</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($smartq->komponenNilais as $k)
                        <tr>
                            <td>{{ $k->urutan }}</td>
                            <td><strong>{{ $k->nama }}</strong></td>
                            <td><code>{{ $k->kode }}</code></td>
                            <td><span class="badge badge-primary">{{ $k->bobot }}%</span></td>
                            <td>{{ $k->nilai_maksimal }}</td>
                            <td>{!! $k->sumber_badge !!}</td>
                        </tr>
                    @endforeach
                    <tr class="bg-light font-weight-bold">
                        <td colspan="3">Total Bobot</td>
                        <td>
                            <span class="badge badge-{{ $smartq->total_bobot == 100 ? 'success' : 'danger' }}">
                                {{ $smartq->total_bobot }}%
                            </span>
                        </td>
                        <td colspan="2">
                            @if($smartq->total_bobot != 100)
                                <span class="text-danger"><i class="fas fa-exclamation-triangle"></i> Total bobot harus 100%!</span>
                            @else
                                <span class="text-success"><i class="fas fa-check"></i> OK</span>
                            @endif
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    {{-- Tabel Ranking --}}
    <div class="card" style="border:0;border-radius:16px;box-shadow:0 4px 24px rgba(0,0,0,.10);overflow:hidden;margin-bottom:1.5rem">
        <div class="card-header" style="background:linear-gradient(135deg,#1e3a5f,#2563eb)!important;color:#fff!important;border:0;padding:14px 20px">
            <div class="d-flex align-items-center justify-content-between flex-wrap">
                <div class="d-flex align-items-center">
                    <i class="fas fa-trophy" style="color:#fbbf24;font-size:1.1rem;margin-right:10px"></i>
                    <span style="font-weight:700;font-size:1.05rem;letter-spacing:.3px;color:#fff">Ranking Peserta</span>
                    <span id="totalPesertaBadge" style="background:rgba(255,255,255,.18);color:#fff;border-radius:20px;padding:2px 10px;font-size:.78rem;margin-left:10px;font-weight:500">{{ $stats['total'] }} peserta</span>
                    <span id="openedPesertaBadge" style="background:rgba(16,185,129,.25);color:#d1fae5;border:1px solid rgba(167,243,208,.35);border-radius:20px;padding:2px 10px;font-size:.78rem;margin-left:8px;font-weight:500">
                        <i class="fas fa-envelope-open-text"></i> {{ $stats['pengumuman_dibuka'] ?? 0 }} dibuka
                    </span>
                    <span id="unopenedPesertaBadge" style="background:rgba(248,113,113,.22);color:#fecaca;border:1px solid rgba(254,202,202,.38);border-radius:20px;padding:2px 10px;font-size:.78rem;margin-left:6px;font-weight:500">
                        <i class="fas fa-envelope"></i> {{ max(($stats['total'] ?? 0) - ($stats['pengumuman_dibuka'] ?? 0), 0) }} belum
                    </span>
                </div>
                <a href="{{ route('admin.smartq.export', $smartq) }}" style="background:rgba(255,255,255,.15);color:#fff;border:1px solid rgba(255,255,255,.3);border-radius:8px;padding:4px 14px;font-size:.82rem;text-decoration:none;display:inline-flex;align-items:center;gap:6px">
                    <i class="fas fa-file-excel"></i> <span class="d-none d-md-inline">Export Excel</span>
                </a>
            </div>
        </div>
        {{-- Filter strip --}}
        <div style="background:#f8fafc;border-bottom:1px solid #e2e8f0;padding:10px 16px;display:flex;align-items:center;gap:8px;flex-wrap:wrap">
            <small class="text-muted" style="margin-right:4px"><i class="fas fa-filter"></i> Filter:</small>
            <button class="rank-filter-pill active" data-filter="all">Semua <span class="rank-pill-cnt">{{ $stats['total'] }}</span></button>
            <button class="rank-filter-pill" data-filter="lulus">Diterima <span class="rank-pill-cnt">{{ $stats['lulus'] }}</span></button>
            <button class="rank-filter-pill" data-filter="cadangan">Cadangan <span class="rank-pill-cnt">{{ $stats['cadangan'] }}</span></button>
            <button class="rank-filter-pill" data-filter="tidak_lulus">Tidak Lulus <span class="rank-pill-cnt">{{ $stats['tidak_lulus'] }}</span></button>
            <button class="rank-filter-pill" data-filter="terdaftar">Belum Diproses <span class="rank-pill-cnt">{{ $stats['terdaftar'] }}</span></button>
        </div>
        <div class="card-body p-0">
            <table id="rankingTable" class="table table-hover table-sm mb-0" style="width:100%">
                <thead>
                    <tr id="rankingHead"></tr>
                </thead>
            </table>
        </div>
    </div>
@stop

@section('css')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap4.min.css">
<style>
/* ===== Ranking Table Professional ===== */
#rankingTable thead tr th {
    background: #1e3a5f !important;
    color: #cbd5e1 !important;
    border: none !important;
    font-size: .71rem;
    text-transform: uppercase;
    letter-spacing: .5px;
    padding: 10px 7px !important;
    white-space: nowrap;
    font-weight: 600;
}
#rankingTable thead tr th.col-highlight {
    background: #162d4a !important;
    color: #93c5fd !important;
}
#rankingTable tbody tr td {
    border-top: 1px solid #f1f5f9 !important;
    border-left: none !important;
    border-right: none !important;
    vertical-align: middle !important;
    font-size: .83rem;
    padding: 9px 8px !important;
    line-height: 1.35;
}
#rankingTable tbody tr:hover > td { background: #eff6ff !important; }
#rankingTable tbody tr.table-success > td { background: #f9fdf9 !important; }
#rankingTable tbody tr.table-success > td:first-child { border-left: 3px solid #16a34a !important; }
#rankingTable tbody tr.table-warning > td { background: #fffdf7 !important; }
#rankingTable tbody tr.table-warning > td:first-child { border-left: 3px solid #d97706 !important; }
#rankingTable tbody tr.table-danger > td { background: #fff8f8 !important; }
#rankingTable tbody tr.table-danger > td:first-child { border-left: 3px solid #dc2626 !important; }
#rankingTable td.col-total { color: #2563eb !important; font-weight: 700 !important; font-size: .9rem; }
#rankingTable td.text-center { white-space: nowrap; }

.btn-reset-kelulusan {
    border-radius: 999px !important;
    border-width: 1px !important;
    width: 30px;
    height: 30px;
    display: inline-flex !important;
    align-items: center;
    justify-content: center;
    padding: 0 !important;
    transition: all .15s ease;
}
.btn-reset-kelulusan:hover {
    background: #dc3545 !important;
    color: #fff !important;
    border-color: #dc3545 !important;
    transform: translateY(-1px);
}
/* DataTables toolbar */
.ranking-dt-top { background: #fff; border-bottom: 1px solid #f1f5f9; }
.ranking-dt-foot { background: #fff; border-top: 1px solid #f1f5f9; }
.ranking-dt-top .dataTables_length label,
.ranking-dt-top .dataTables_filter label { font-weight: 500; font-size: .83rem; margin-bottom: 0; color: #475569; }
.ranking-dt-top .dataTables_filter input { border-radius: 8px; border: 1px solid #e2e8f0; padding: 4px 10px; font-size: .83rem; outline: none; }
.ranking-dt-top .dataTables_filter input:focus { border-color: #93c5fd; box-shadow: 0 0 0 3px rgba(37,99,235,.1); }
.ranking-dt-foot .dataTables_info { font-size: .8rem; color: #64748b; }
.ranking-dt-foot .dataTables_paginate .paginate_button { font-size: .8rem; border-radius: 6px !important; }
/* Filter pills */
.rank-filter-pill {
    background: #fff;
    border: 1px solid #e2e8f0;
    color: #64748b;
    border-radius: 20px;
    padding: 3px 12px;
    font-size: .77rem;
    font-weight: 500;
    cursor: pointer;
    transition: all .15s;
    display: inline-flex;
    align-items: center;
    gap: 5px;
    white-space: nowrap;
}
.rank-filter-pill:hover { border-color: #94a3b8; color: #334155; }
.rank-filter-pill.active { background: #2563eb; color: #fff; border-color: #2563eb; box-shadow: 0 2px 8px rgba(37,99,235,.3); }
.rank-pill-cnt {
    background: rgba(0,0,0,.08);
    border-radius: 10px;
    padding: 0 6px;
    font-size: .7rem;
    font-weight: 700;
}
.rank-filter-pill.active .rank-pill-cnt { background: rgba(255,255,255,.25); }
</style>
@stop

@section('js')
@include('admin.smartq._overlay')
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap4.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var dtRanking = null;
    var _rankFilter = 'all';

    // Custom filter for ranking by status
    $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
        if (settings.nTable.id !== 'rankingTable') return true;
        if (_rankFilter === 'all') return true;
        return data[data.length - 1] === _rankFilter;
    });

    // Filter pill click handler
    $(document).on('click', '.rank-filter-pill', function() {
        _rankFilter = $(this).data('filter');
        $('.rank-filter-pill').removeClass('active');
        $(this).addClass('active');
        if (dtRanking) dtRanking.draw();
    });

    // ========== RANKING DATATABLE ==========
    $.ajax({
        url: '{{ route("admin.smartq.ranking.data", $smartq) }}',
        dataType: 'json',
        beforeSend: function() {
            showSmartqOverlay('Memuat data ranking...', 'Mengambil data peserta dari server', 'table');
        },
        success: function(res) {
            hideSmartqOverlay();
            initRankingTable(res.data, res.komponen);
        },
        error: function(xhr) {
            hideSmartqOverlay();
            console.error('Ranking AJAX error:', xhr.responseText);
            Swal.fire({ icon: 'error', title: 'Gagal', text: 'Tidak dapat memuat data ranking.' });
        }
    });

    function initRankingTable(data, komponen) {
        // Build thead columns
        var headHtml = '<th class="text-center" width="50">Rank</th>';
        headHtml += '<th width="90">No. Peserta</th>';
        headHtml += '<th>Nama Siswa</th>';
        headHtml += '<th width="95">NISN</th>';
        headHtml += '<th width="110">Kelas Asal</th>';
        komponen.forEach(function(k) {
            headHtml += '<th class="text-center" width="80" title="' + k.nama + ' (' + k.bobot + ')">' +
                k.kode + '<br><small class="text-info">' + k.bobot + '</small></th>';
        });
        headHtml += '<th class="text-center col-highlight" width="80">Total</th>';
        headHtml += '<th class="text-center" width="95">Status</th>';
        headHtml += '<th class="text-center" width="110">Bidang</th>';
        headHtml += '<th class="text-center" width="110">Amplop</th>';
        headHtml += '<th class="text-center" width="65">P.Mapel</th>';
        headHtml += '<th class="text-center" width="60">Aksi</th>';
        headHtml += '<th style="display:none"></th>'; // status_raw for filter
        $('#rankingHead').html(headHtml);

        // Column definitions
        var columns = [
            { data: 'ranking_display', className: 'text-center' },
            { data: 'nomor_peserta' },
            { data: 'nama' },
            { data: 'nisn' },
            { data: 'kelas' },
        ];
        komponen.forEach(function(k) {
            columns.push({ data: 'komponen_' + k.id, className: 'text-center' });
        });
        columns.push({ data: 'total', className: 'text-center col-total' });
        columns.push({ data: 'status', className: 'text-center', orderable: false, searchable: false });
        columns.push({ data: 'bidang', className: 'text-center', orderable: false, searchable: false });
        columns.push({ data: 'pengumuman_dibuka', className: 'text-center', orderable: false, searchable: false });
        columns.push({ data: 'peringkat_mapel', className: 'text-center' });
        columns.push({
            data: null,
            className: 'text-center',
            orderable: false,
            searchable: false,
            render: function(data, type, row) {
                if (!row.peserta_id || row.status_raw === 'terdaftar') return '<span class="text-muted">-</span>';
                return '<button class="btn btn-xs btn-outline-danger btn-reset-kelulusan" data-id="' + row.peserta_id + '" data-nama="' + (row.nama_sort || '-') + '" title="Reset ke Terdaftar"><i class="fas fa-undo"></i></button>';
            }
        });
        columns.push({ data: 'status_raw', visible: false, searchable: false }); // for filter

        dtRanking = $('#rankingTable').DataTable({
            data: data,
            columns: columns,
            order: [[0, 'asc']],
            pageLength: 25,
            lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, 'Semua']],
            language: {
                url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/id.json'
            },
            createdRow: function(row, rowData) {
                if (rowData.row_class) $(row).addClass(rowData.row_class);
            },
            dom: '<"ranking-dt-top d-flex align-items-center justify-content-between flex-wrap px-3 py-2"<"d-flex align-items-center"l><"d-flex align-items-center"f>>rt<"ranking-dt-foot d-flex align-items-center justify-content-between flex-wrap px-3 py-2"ip>',
            autoWidth: false,
        });

        $('#totalPesertaBadge').text(data.length + ' peserta');
        var openedCount = data.filter(function(row) { return Number(row.pengumuman_dibuka_raw || 0) === 1; }).length;
        var unopenedCount = Math.max(data.length - openedCount, 0);
        $('#openedPesertaBadge').html('<i class="fas fa-envelope-open-text"></i> ' + openedCount + ' dibuka');
        $('#unopenedPesertaBadge').html('<i class="fas fa-envelope"></i> ' + unopenedCount + ' belum');
    }

    // ========== RESET KELULUSAN PER PESERTA ==========
    $(document).on('click', '.btn-reset-kelulusan', function() {
        var id   = $(this).data('id');
        var nama = $(this).data('nama');
        Swal.fire({
            icon: 'warning',
            title: 'Reset Kelulusan?',
            html: 'Status kelulusan <strong>' + nama + '</strong> akan dikembalikan ke <em>Terdaftar</em>.<br>Bidang mapel & peringkat akan dihapus.',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            confirmButtonText: '<i class="fas fa-undo"></i> Ya, Reset',
            cancelButtonText: 'Batal',
            reverseButtons: true,
        }).then(function(result) {
            if (!result.isConfirmed) return;
            showSmartqOverlay('Mereset data kelulusan...', 'Mohon tunggu', 'undo');
            $.ajax({
                url: '{{ route("admin.smartq.kelulusan.reset.peserta", ["smartq" => $smartq, "peserta" => "__ID__"]) }}'.replace('__ID__', id),
                type: 'POST',
                data: { _method: 'DELETE', _token: '{{ csrf_token() }}' },
                success: function(res) {
                    hideSmartqOverlay();
                    if (res.success) {
                        Swal.fire({ icon: 'success', title: 'Reset Berhasil', text: res.message, timer: 1800, showConfirmButton: false });
                        setTimeout(function() { location.reload(); }, 1900);
                    } else {
                        Swal.fire({ icon: 'error', title: 'Gagal', text: res.message });
                    }
                },
                error: function() {
                    hideSmartqOverlay();
                    Swal.fire({ icon: 'error', title: 'Gagal', text: 'Terjadi kesalahan server.' });
                }
            });
        });
    });

    // ========== RESET KELULUSAN BULK ==========
    var btnResetBulk = document.getElementById('btnResetBulk');
    if (btnResetBulk) {
        btnResetBulk.addEventListener('click', function() {
            Swal.fire({
                icon: 'warning',
                title: 'Reset SEMUA Kelulusan?',
                html: '<p>Seluruh status kelulusan peserta akan dikembalikan ke <em>Terdaftar</em>.</p><p class="text-danger mb-0"><strong><i class="fas fa-exclamation-triangle"></i> Tindakan ini tidak dapat dibatalkan!</strong></p>',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                confirmButtonText: '<i class="fas fa-undo"></i> Ya, Reset Semua',
                cancelButtonText: 'Batal',
                reverseButtons: true,
                input: 'text',
                inputPlaceholder: 'Ketik RESET untuk konfirmasi',
                preConfirm: function(val) {
                    if (val !== 'RESET') {
                        Swal.showValidationMessage('Ketik RESET untuk konfirmasi');
                        return false;
                    }
                },
            }).then(function(result) {
                if (!result.isConfirmed) return;
                showSmartqOverlay('Mereset semua data kelulusan...', 'Mohon tunggu', 'undo');
                $.ajax({
                    url: '{{ route("admin.smartq.kelulusan.reset.bulk", $smartq) }}',
                    type: 'POST',
                    data: { _method: 'DELETE', _token: '{{ csrf_token() }}' },
                    success: function(res) {
                        hideSmartqOverlay();
                        if (res.success) {
                            Swal.fire({ icon: 'success', title: 'Reset Berhasil', text: res.message, timer: 2000, showConfirmButton: false });
                            setTimeout(function() { location.reload(); }, 2100);
                        } else {
                            Swal.fire({ icon: 'error', title: 'Gagal', text: res.message });
                        }
                    },
                    error: function() {
                        hideSmartqOverlay();
                        Swal.fire({ icon: 'error', title: 'Gagal', text: 'Terjadi kesalahan server.' });
                    }
                });
            });
        });
    }

    // Sync Nilai CBT form — SweetAlert confirm + overlay
    var formSync = document.getElementById('formSyncMoodle');
    if (formSync) {
        formSync.addEventListener('submit', function(e) {
            e.preventDefault();
            smartqConfirm(null, {
                title: 'Sync Nilai CBT?',
                text: '<p>Nilai CBT akan ditarik dari Moodle.</p><p class="text-danger mb-0"><small><i class="fas fa-exclamation-triangle"></i> Nilai yang sudah ada akan di-<b>overwrite</b>!</small></p>',
                icon: 'warning',
                confirmText: '<i class="fas fa-sync"></i> Ya, Sync Sekarang',
                confirmColor: '#e6a817',
            }).then(function(result) {
                if (result.isConfirmed) {
                    showSmartqOverlay('Menarik nilai CBT dari Moodle...', 'Memproses data dari semua quiz yang dikonfigurasi', 'cloud-download-alt');
                    smartqOverlayMessages([
                        'Menarik nilai CBT dari Moodle...',
                        'Mengambil skor dari setiap quiz...',
                        'Menghitung rata-rata per siswa...',
                        'Menyimpan nilai ke database...',
                        'Hampir selesai...',
                    ], 2500);
                    formSync.submit();
                }
            });
        });
    }

    // Scan Peserta link
    document.querySelectorAll('a[href*="moodle/scan"]').forEach(function(link) {
        link.addEventListener('click', function() {
            showSmartqOverlay('Scanning peserta dari Moodle...', 'Mengambil data enrolled users dari semua course', 'search');
            smartqOverlayMessages([
                'Scanning peserta dari Moodle...',
                'Mencocokkan NISN dengan database siswa...',
                'Mengumpulkan data skor quiz...',
                'Menyusun hasil scan...',
            ], 2500);
        });
    });

    // Export Excel link
    document.querySelectorAll('a[href*="export"]').forEach(function(link) {
        link.addEventListener('click', function() {
            showSmartqOverlay('Membuat file Excel...', 'Menyiapkan data ranking dan nilai peserta', 'file-excel');
            setTimeout(hideSmartqOverlay, 8000);
        });
    });
});
</script>
@stop
