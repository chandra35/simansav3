@extends('adminlte::page')

@section('title', 'Data Siswa KIP/SKTM/PKH - SIMANSA')

@section('plugins.Datatables', true)
@section('plugins.DatatablesPlugins', true)

@section('content_header')
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1><i class="fas fa-hand-holding-heart text-primary"></i> KIP, KKS/PKH & SKTM</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item active">Bantuan Siswa</li>
            </ol>
        </div>
</div>
@stop

@section('css')
<style>
    .pip-assistance-page .pip-hero__eyebrow { font-size: .72rem; font-weight: 700; letter-spacing: .08em; text-transform: uppercase; color: rgba(255,255,255,.72); }
    .pip-assistance-page .pip-hero__lead { max-width: 660px; color: rgba(255,255,255,.82); }
    .pip-assistance-page .pip-hero__metric { border-left: 1px solid rgba(255,255,255,.25); min-height: 56px; }
    .pip-assistance-page .pip-hero__metric-label { color: rgba(255,255,255,.7); font-size: .7rem; font-weight: 700; letter-spacing: .05em; text-transform: uppercase; }
    .pip-assistance-page .pip-stat-card { border-width: 1px; transition: transform .18s ease, box-shadow .18s ease; }
    .pip-assistance-page .pip-stat-card:hover { transform: translateY(-2px); box-shadow: 0 .5rem 1rem rgba(15, 23, 42, .09); }
    .pip-assistance-page .pip-stat-card .pip-stat-icon { width: 42px; height: 42px; display: inline-flex; align-items: center; justify-content: center; border-radius: 12px; font-size: 1rem; }
    .pip-assistance-page .pip-filter-summary { border: 1px solid #dbeafe; border-radius: .5rem; background: #f8fbff; }
    .pip-assistance-page .pip-filter-actions { display: flex; align-items: flex-end; height: 100%; }
    .pip-assistance-page #pip-table th { white-space: nowrap; font-size: .72rem; letter-spacing: .03em; text-transform: uppercase; }
    .pip-assistance-page #pip-table td { vertical-align: middle; }
    .pip-assistance-page .pip-document-group + .pip-document-group { margin-top: .45rem; }
    .pip-assistance-page .pip-document-entry { display: inline-flex; align-items: center; flex-wrap: wrap; gap: .3rem .45rem; margin-left: .35rem; vertical-align: middle; }
    .pip-assistance-page .pip-document-entry small { font-size: .64rem; line-height: 1.25; white-space: nowrap; }
    .pip-assistance-page .pip-document-entry .btn { padding: .16rem .42rem; line-height: 1.2; white-space: nowrap; }
    @media (max-width: 991.98px) { .pip-assistance-page .pip-hero__metric { border-left: 0; border-top: 1px solid rgba(255,255,255,.25); padding-top: .75rem; margin-top: .75rem; } .pip-assistance-page .pip-filter-actions { height: auto; margin-top: 1rem; } .pip-assistance-page .pip-document-entry { display: flex; margin: .28rem 0 0; } }
</style>
@stop

@section('content')
<div class="pip-assistance-page">
    <div class="card bg-gradient-primary text-white mb-4">
        <div class="card-body py-4">
            <div class="row align-items-center">
                <div class="col-lg-8">
                    <div class="pip-hero__eyebrow mb-2"><i class="fas fa-shield-alt mr-1"></i> Manajemen Kesiswaan</div>
                    <h3 class="mb-2"><i class="fas fa-hand-holding-heart mr-2"></i>Pemetaan Bantuan Siswa</h3>
                    <p class="mb-0 pip-hero__lead">Pantau kelengkapan dokumen KIP, KKS/PKH, dan SKTM siswa dalam satu daftar terarah. Gunakan filter untuk menemukan data yang perlu ditindaklanjuti.</p>
                </div>
                <div class="col-lg-4 mt-3 mt-lg-0">
                    <div class="row text-center">
                        <div class="col-6 pip-hero__metric">
                            <div class="pip-hero__metric-label">Total Terdata</div>
                            <div class="h3 mb-0 font-weight-bold">{{ number_format($stats['total']) }}</div>
                        </div>
                        <div class="col-6 pip-hero__metric">
                            <div class="pip-hero__metric-label">Dokumen KIP</div>
                            <div class="h3 mb-0 font-weight-bold">{{ number_format($stats['kip']) }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-2">
        <div class="col-md-6 col-xl-3 mb-3">
            <div class="card card-outline card-primary pip-stat-card h-100 mb-0"><div class="card-body d-flex align-items-center">
                <span class="pip-stat-icon bg-primary text-white mr-3"><i class="fas fa-users"></i></span><div><div class="text-muted small text-uppercase font-weight-bold">Terdata</div><div class="h4 text-primary mb-0">{{ number_format($stats['total']) }}</div></div>
            </div></div>
        </div>
        <div class="col-md-6 col-xl-3 mb-3">
            <div class="card card-outline card-success pip-stat-card h-100 mb-0"><div class="card-body d-flex align-items-center">
                <span class="pip-stat-icon bg-success text-white mr-3"><i class="fas fa-id-card"></i></span><div><div class="text-muted small text-uppercase font-weight-bold">KIP</div><div class="h4 text-success mb-0">{{ number_format($stats['kip']) }}</div></div>
            </div></div>
        </div>
        <div class="col-md-6 col-xl-3 mb-3">
            <div class="card card-outline card-warning pip-stat-card h-100 mb-0"><div class="card-body d-flex align-items-center">
                <span class="pip-stat-icon bg-warning text-white mr-3"><i class="fas fa-file-alt"></i></span><div><div class="text-muted small text-uppercase font-weight-bold">SKTM</div><div class="h4 text-warning mb-0">{{ number_format($stats['sktm']) }}</div></div>
            </div></div>
        </div>
        <div class="col-md-6 col-xl-3 mb-3">
            <div class="card card-outline card-info pip-stat-card h-100 mb-0"><div class="card-body d-flex align-items-center">
                <span class="pip-stat-icon bg-info text-white mr-3"><i class="fas fa-hand-holding-heart"></i></span><div><div class="text-muted small text-uppercase font-weight-bold">KKS / PKH</div><div class="h4 text-info mb-0">{{ number_format($stats['pkh']) }}</div></div>
            </div></div>
        </div>
    </div>

    <div class="card card-outline card-primary">
        <div class="card-header border-0">
            <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between">
                <div>
                    <h3 class="h6 font-weight-bold mb-1"><i class="fas fa-folder-open text-primary mr-2"></i>Daftar Dokumen Bantuan</h3>
                    <div class="text-muted small">Lihat dokumen tanpa meninggalkan halaman atau buka detail profil siswa.</div>
                </div>
                <div class="card-tools ml-0 mt-3 mt-lg-0"><button type="button" id="btnExportExcel" class="btn btn-success btn-sm"><i class="fas fa-file-excel mr-1"></i> Export Excel</button></div>
            </div>
        </div>
        <div class="card-body pt-0">
            <div class="pip-filter-summary p-3 mb-3">
                <div class="row align-items-end">
                    <div class="col-lg-9">
                        <div class="row">
                            <div class="col-md-4 mb-3 mb-lg-0">
                            <label class="simansa-filter-label">
                                <i class="fas fa-folder-open mr-1"></i> Jenis Bantuan
                            </label>
                            <select id="filterJenis" class="form-control form-control-sm">
                                <option value="">Semua (KIP + KKS/PKH + SKTM)</option>
                                <option value="kip">KIP saja</option>
                                <option value="sktm">SKTM saja</option>
                                <option value="pkh">KKS/PKH saja</option>
                            </select>
                            </div>
                            <div class="col-md-4 mb-3 mb-lg-0">
                            <label class="simansa-filter-label">
                                <i class="fas fa-layer-group mr-1"></i> Tingkat
                            </label>
                            <select id="filterTingkat" class="form-control form-control-sm">
                                <option value="">Semua</option>
                                @foreach($tingkatOptions as $val => $label)
                                    <option value="{{ $val }}">{{ $label }}</option>
                                @endforeach
                            </select>
                            </div>
                            <div class="col-md-4">
                            <label class="simansa-filter-label">
                                <i class="fas fa-door-open mr-1"></i> Kelas
                            </label>
                            <select id="filterKelas" class="form-control form-control-sm" disabled>
                                <option value="">Pilih Tingkat Dulu</option>
                            </select>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3">
                        <div class="pip-filter-actions"><button type="button" id="btnResetFilter" class="btn btn-outline-secondary btn-sm w-100">
                            <i class="fas fa-redo mr-1"></i> Reset Filter
                        </button></div>
                    </div>
                </div>
            </div>

            <div class="d-flex align-items-center text-muted small mb-3"><i class="fas fa-info-circle text-primary mr-2"></i>Dokumen dapat dipreview langsung; tanggal unggah dan pembaruan tersedia pada setiap berkas.</div>
            <div class="table-responsive"><table id="pip-table" class="table table-hover table-bordered table-sm mb-0"><thead><tr><th>#</th><th>NISN</th><th>Nama Lengkap</th><th>Jenis Kelamin</th><th>Kelas</th><th>Dokumen</th><th>No. KKS/PKH</th><th>Aksi</th></tr></thead></table></div>
        </div>
    </div>
</div>

@include('admin.partials.dokumen-preview-modal')

@stop

@section('js')
<script>
$(function () {
    const kelasData = @json($kelasOptions);

    // ── DataTable ──────────────────────────────────────────────────────────────
    const table = $('#pip-table').DataTable({
        processing : true,
        serverSide : true,
        ajax: {
            url: '{{ route("admin.kip-sktm.data") }}',
            data: function (d) {
                d.jenis    = $('#filterJenis').val();
                d.tingkat  = $('#filterTingkat').val();
                d.kelas_id = $('#filterKelas').val();
            }
        },
        columns: [
            { data: null, render: function(data, type, row, meta) { return meta.row + meta.settings._iDisplayStart + 1; }, orderable: false, searchable: false },
            { data: 'nisn' },
            { data: 'nama_lengkap' },
            { data: 'jenis_kelamin' },
            { data: 'kelas' },
            { data: 'dokumen', orderable: false },
            { data: 'nomor_pkh' },
            { data: 'actions', orderable: false },
        ],
        order: [[2, 'asc']],
        language: {
            processing: '<i class="fas fa-spinner fa-spin"></i> Memuat data...',
            emptyTable:  'Tidak ada siswa dengan data KIP / KKS-PKH / SKTM.',
            zeroRecords: 'Tidak ada siswa yang cocok dengan filter.',
            lengthMenu:  'Tampilkan _MENU_ data per halaman',
            info:        'Menampilkan _START_–_END_ dari _TOTAL_ siswa',
            infoEmpty:   'Tidak ada data.',
            search:      'Cari:',
            paginate:    { first: '«', last: '»', next: '›', previous: '‹' },
        },
        pageLength: 25,
        lengthMenu: [10, 25, 50, 100, -1],
    });

    // ── Filter events ──────────────────────────────────────────────────────────
    $('#filterJenis, #filterTingkat, #filterKelas').on('change', function () {
        table.draw();
    });

    // Cascading tingkat → kelas
    $('#filterTingkat').on('change', function () {
        const tingkat = $(this).val();
        const $selKelas = $('#filterKelas');
        $selKelas.html('<option value="">Semua Kelas</option>');
        if (tingkat) {
            const filtered = kelasData.filter(k => k.tingkat == tingkat);
            filtered.forEach(k => {
                $selKelas.append(`<option value="${k.id}">${k.nama_kelas}</option>`);
            });
            $selKelas.prop('disabled', filtered.length === 0);
        } else {
            $selKelas.html('<option value="">Pilih Tingkat Dulu</option>').prop('disabled', true);
        }
        table.draw();
    });

    // Reset filter
    $('#btnResetFilter').on('click', function () {
        $('#filterJenis').val('');
        $('#filterTingkat').val('');
        $('#filterKelas').html('<option value="">Pilih Tingkat Dulu</option>').prop('disabled', true);
        table.draw();
    });

    // ── Export Excel (sederhana via print) ────────────────────────────────────
    $('#btnExportExcel').on('click', function () {
        table.button('.buttons-excel')?.trigger();
        // Fallback: buka URL dengan query string
        const params = new URLSearchParams({
            jenis:    $('#filterJenis').val(),
            tingkat:  $('#filterTingkat').val(),
            kelas_id: $('#filterKelas').val(),
            export:   'excel',
        });
        window.open('{{ route("admin.kip-sktm.data") }}?' + params.toString(), '_blank');
    });
});
</script>
@stop
