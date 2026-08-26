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
    .pip-assistance-page #pipStudentDetailModal .modal-content { border: 0; border-radius: 14px; overflow: hidden; box-shadow: 0 18px 46px rgba(15, 23, 42, .2); }
    .pip-assistance-page .pip-student-detail__photo { width: 108px; height: 108px; border: 3px solid #fff; border-radius: 50%; object-fit: cover; box-shadow: 0 6px 18px rgba(15,23,42,.16); }
    .pip-assistance-page .pip-student-detail__identity { min-width: 0; }
    .pip-assistance-page .pip-student-detail__meta { display: grid; grid-template-columns: minmax(0, 1fr) minmax(0, 1fr); gap: .65rem; }
    .pip-assistance-page .pip-student-detail__meta-item { padding: .65rem .75rem; border: 1px solid #e2e8f0; border-radius: .55rem; background: #f8fafc; }
    .pip-assistance-page .pip-student-detail__meta-item--wide { grid-column: 1 / -1; }
    .pip-assistance-page .pip-student-detail__meta-label { display: block; margin-bottom: .15rem; color: #64748b; font-size: .68rem; font-weight: 700; letter-spacing: .03em; text-transform: uppercase; }
    .pip-assistance-page .pip-student-detail__meta-value { color: #0f172a; font-size: .84rem; font-weight: 600; overflow-wrap: anywhere; }
    @media (max-width: 991.98px) { .pip-assistance-page .pip-hero__metric { border-left: 0; border-top: 1px solid rgba(255,255,255,.25); padding-top: .75rem; margin-top: .75rem; } .pip-assistance-page .pip-filter-actions { height: auto; margin-top: 1rem; } .pip-assistance-page .pip-document-entry { display: flex; margin: .28rem 0 0; } }
    @media (max-width: 575.98px) { .pip-assistance-page #pipStudentDetailModal .modal-dialog { margin: .5rem; } .pip-assistance-page .pip-student-detail__photo { width: 82px; height: 82px; } .pip-assistance-page .pip-student-detail__meta { grid-template-columns: 1fr; } }
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

<div class="modal fade" id="pipStudentDetailModal" tabindex="-1" role="dialog" aria-labelledby="pipStudentDetailModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary">
                <h5 class="modal-title text-white" id="pipStudentDetailModalLabel"><i class="fas fa-user-graduate mr-1"></i> Detail Siswa</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Tutup"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body" id="pipStudentDetailModalBody">
                <div class="text-center py-5 text-muted"><i class="fas fa-spinner fa-spin fa-2x mb-3"></i><div>Memuat detail siswa...</div></div>
            </div>
            <div class="modal-footer">
                <a href="#" id="pipStudentDetailFullLink" class="btn btn-outline-primary" target="_blank" rel="noopener"><i class="fas fa-external-link-alt mr-1"></i> Halaman Detail</a>
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
            </div>
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
            { data: 'dokumen' },
            { data: 'nomor_pkh' },
            { data: 'actions', orderable: false },
        ],
        order: [],
        language: {
            processing: '<i class="fas fa-spinner fa-spin"></i> Memuat data...',
            emptyTable:  'Belum ada data',
            zeroRecords: 'Tidak ada siswa yang cocok dengan filter.',
            lengthMenu:  'Tampilkan _MENU_ data per halaman',
            info:        'Menampilkan _START_–_END_ dari _TOTAL_ siswa',
            infoEmpty:   '',
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

    const escapeHtml = function (value) {
        return $('<div>').text(value || '-').html();
    };

    $(document).on('click', '.js-pip-student-detail', function () {
        const $button = $(this);
        const detailUrl = $button.data('detail-url');
        const fullDetailUrl = $button.data('full-detail-url');
        const $modal = $('#pipStudentDetailModal');
        const $body = $('#pipStudentDetailModalBody');

        if (!detailUrl) return;

        $body.html('<div class="text-center py-5 text-muted"><i class="fas fa-spinner fa-spin fa-2x mb-3"></i><div>Memuat detail siswa...</div></div>');
        $('#pipStudentDetailFullLink').attr('href', fullDetailUrl || '#');
        $modal.modal('show');

        $.get(detailUrl)
            .done(function (response) {
                if (!response || !response.success || !response.siswa) {
                    $body.html('<div class="alert alert-danger mb-0"><i class="fas fa-exclamation-circle mr-1"></i>Detail siswa tidak tersedia.</div>');
                    return;
                }

                const siswa = response.siswa;
                const foto = siswa.foto_profile_url || ('https://ui-avatars.com/api/?name=' + encodeURIComponent(siswa.nama_lengkap || 'Siswa') + '&size=200&background=2563eb&color=FFFFFF&bold=true');
                const gender = siswa.jenis_kelamin === 'L' ? 'Laki-laki' : (siswa.jenis_kelamin === 'P' ? 'Perempuan' : '-');
                const ketua = siswa.is_ketua_kelas ? '<span class="badge badge-warning ml-1"><i class="fas fa-crown mr-1"></i>Ketua Kelas</span>' : '';
                const item = function (label, value, icon) {
                    return '<div class="pip-student-detail__meta-item"><span class="pip-student-detail__meta-label"><i class="' + icon + ' mr-1"></i>' + label + '</span><div class="pip-student-detail__meta-value">' + escapeHtml(value) + '</div></div>';
                };

                $body.html(
                    '<div class="d-flex align-items-center mb-4">'
                    + '<img src="' + escapeHtml(foto) + '" class="pip-student-detail__photo mr-3" alt="Foto ' + escapeHtml(siswa.nama_lengkap) + '">'
                    + '<div class="pip-student-detail__identity"><h5 class="font-weight-bold mb-1">' + escapeHtml(siswa.nama_lengkap) + '</h5><div class="text-muted small mb-2">NISN: ' + escapeHtml(siswa.nisn) + '</div><span class="badge badge-primary">' + escapeHtml(gender) + '</span>' + ketua + '</div>'
                    + '</div>'
                    + '<div class="pip-student-detail__meta">'
                    + item('Kelas aktif', siswa.kelas_aktif, 'fas fa-school')
                    + item('Peran rombel', siswa.jabatan_rombel, 'fas fa-user-tag')
                    + item('NIS lokal', siswa.nis, 'fas fa-id-badge')
                    + item('Nomor tes', siswa.nomor_tes, 'fas fa-ticket-alt')
                    + item('Tempat, tanggal lahir', [siswa.tempat_lahir, siswa.tanggal_lahir_formatted].filter(Boolean).join(', '), 'fas fa-birthday-cake')
                    + item('No. HP', siswa.nomor_hp, 'fas fa-phone')
                    + item('Email', siswa.email, 'fas fa-envelope')
                    + item('Sekolah asal', siswa.nama_sekolah_asal, 'fas fa-university')
                    + '<div class="pip-student-detail__meta-item pip-student-detail__meta-item--wide"><span class="pip-student-detail__meta-label"><i class="fas fa-map-marker-alt mr-1"></i>Alamat</span><div class="pip-student-detail__meta-value">' + escapeHtml(siswa.alamat_siswa) + '</div></div>'
                    + '</div>'
                );
            })
            .fail(function (xhr) {
                const message = xhr.responseJSON?.message || 'Detail siswa tidak dapat dimuat. Silakan coba kembali.';
                $body.html('<div class="alert alert-danger mb-0"><i class="fas fa-exclamation-circle mr-1"></i>' + escapeHtml(message) + '</div>');
            });
    });
});
</script>
@stop
