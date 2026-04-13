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
    <div class="card card-outline card-primary">
        <div class="card-header py-2">
            <h3 class="card-title"><i class="fas fa-trophy text-warning"></i> Ranking Peserta</h3>
            <div class="card-tools">
                <span class="badge badge-primary" id="totalPesertaBadge">{{ $stats['total'] }} peserta</span>
            </div>
        </div>
        <div class="card-body p-0">
            <table id="rankingTable" class="table table-bordered table-hover table-sm mb-0" style="width:100%">
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
    #rankingTable thead th {
        background: linear-gradient(135deg, #2d3748, #1a202c);
        color: #ffffff;
        border-color: #4a5568;
        font-size: 0.8rem;
        text-transform: uppercase;
        letter-spacing: 0.03em;
        padding: 0.6rem 0.5rem;
        white-space: nowrap;
        vertical-align: middle;
    }
    #rankingTable thead th.col-total {
        background: linear-gradient(135deg, #3182ce, #2b6cb0);
    }
    #rankingTable tbody td {
        vertical-align: middle;
        font-size: 0.82rem;
        padding: 0.45rem 0.5rem;
    }
    #rankingTable tbody tr:hover {
        background-color: rgba(66, 153, 225, 0.08) !important;
    }
    #rankingTable tbody tr.table-success { background-color: rgba(72, 187, 120, 0.12) !important; }
    #rankingTable tbody tr.table-warning { background-color: rgba(237, 183, 49, 0.12) !important; }
    #rankingTable tbody tr.table-danger  { background-color: rgba(245, 101, 101, 0.10) !important; }
    #rankingTable_wrapper .dataTables_length,
    #rankingTable_wrapper .dataTables_filter,
    #rankingTable_wrapper .dataTables_info,
    #rankingTable_wrapper .dataTables_paginate {
        padding: 0.65rem 1rem;
        font-size: 0.85rem;
    }
    #rankingTable_wrapper .dataTables_filter input {
        border-radius: 0.75rem;
        padding: 0.35rem 0.75rem;
        border: 1px solid #cbd5e0;
    }
    #rankingTable_wrapper .page-item.active .page-link {
        background: linear-gradient(135deg, #3182ce, #2b6cb0);
        border-color: #2b6cb0;
    }
</style>
@stop

@section('js')
@include('admin.smartq._overlay')
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap4.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
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
        // Build thead columns in correct order
        var headHtml = '<th class="text-center" width="55">Rank</th>';
        headHtml += '<th width="95">No. Peserta</th>';
        headHtml += '<th>Nama Siswa</th>';
        headHtml += '<th width="100">NISN</th>';
        headHtml += '<th width="120">Kelas Asal</th>';
        komponen.forEach(function(k) {
            headHtml += '<th class="text-center" width="85" title="' + k.nama + ' (' + k.bobot + '%)">' +
                k.kode + '<br><small>' + k.bobot + '%</small></th>';
        });
        headHtml += '<th class="text-center col-total" width="85">Total</th>';
        headHtml += '<th class="text-center" width="100">Status</th>';
        headHtml += '<th class="text-center" width="120">Bidang</th>';
        $('#rankingHead').html(headHtml);

        // Column definitions matching thead order
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
        columns.push({ data: 'total', className: 'text-center font-weight-bold text-primary' });
        columns.push({ data: 'status', className: 'text-center', orderable: false, searchable: false });
        columns.push({ data: 'bidang', className: 'text-center', orderable: false, searchable: false });

        $('#rankingTable').DataTable({
            data: data,
            columns: columns,
            order: [[0, 'asc']],
            pageLength: 25,
            lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, 'Semua']],
            language: {
                url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/id.json'
            },
            createdRow: function(row, rowData) {
                if (rowData.row_class) {
                    $(row).addClass(rowData.row_class);
                }
            },
            dom: '<"row px-3 pt-2"<"col-sm-6"l><"col-sm-6"f>>rtip',
        });

        $('#totalPesertaBadge').text(data.length + ' peserta');
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
