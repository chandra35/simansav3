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
    .pip-assistance-page #pip-table { border-collapse: separate; border-spacing: 0; }
    .pip-assistance-page #pip-table th { padding: .68rem .75rem; border-top: 1px solid #e2e8f0; border-bottom: 2px solid #cbd5e1; background: #f8fafc; color: #475569; white-space: nowrap; font-size: .7rem; font-weight: 700; letter-spacing: .045em; text-transform: uppercase; }
    .pip-assistance-page #pip-table td { padding: .68rem .75rem; border-top: 1px solid #e8eef5; color: #334155; vertical-align: middle; }
    .pip-assistance-page #pip-table th:nth-child(1), .pip-assistance-page #pip-table td:nth-child(1) { width: 42px; }
    .pip-assistance-page #pip-table th:nth-child(2) { width: 29%; }
    .pip-assistance-page #pip-table th:nth-child(3) { width: 34%; }
    .pip-assistance-page #pip-table tbody tr:first-child td { border-top: 0; }
    .pip-assistance-page #pip-table tbody tr { transition: background-color .15s ease; }
    .pip-assistance-page #pip-table tbody tr:hover { background: #f8fbff; }
    .pip-assistance-page .pip-document-group { display: inline-flex; align-items: center; gap: .32rem; min-width: 0; padding: .14rem 0; }
    .pip-assistance-page .pip-document-group + .pip-document-group { margin-left: .48rem; padding-left: .48rem; border-left: 1px solid #dbe4f0; }
    .pip-assistance-page .pip-document-group__type .badge { display: inline-flex; align-items: center; justify-content: space-between; min-width: auto; padding: .27rem .4rem; border-radius: .35rem; font-size: .66rem; font-weight: 700; }
    .pip-assistance-page .pip-document-group__count { margin-left: .38rem; padding-left: .38rem; border-left: 1px solid rgba(255,255,255,.42); font-variant-numeric: tabular-nums; }
    .pip-assistance-page .badge-warning .pip-document-group__count { border-left-color: rgba(51,65,85,.28); }
    .pip-assistance-page .pip-document-group__items { display: inline-flex; align-items: center; gap: .28rem; min-width: 0; }
    .pip-assistance-page .pip-document-entry { display: inline-flex; align-items: center; gap: .24rem; min-width: 0; }
    .pip-assistance-page .pip-document-entry__meta { color: #64748b; font-size: .62rem; line-height: 1.2; white-space: nowrap; }
    .pip-assistance-page .pip-document-entry__meta i { color: #94a3b8; }
    .pip-assistance-page .pip-document-entry__updated { margin-left: .18rem; color: #64748b; }
    .pip-assistance-page .pip-document-entry .btn { padding: .18rem .46rem; line-height: 1.2; white-space: nowrap; border-radius: .35rem; }
    .pip-assistance-page .pip-emis-status { min-width: 88px; }
    .pip-assistance-page .pip-emis-status .btn { min-width: 74px; }
    .pip-assistance-page .pip-student-metadata { min-width: 198px; }
    .pip-assistance-page .pip-student-metadata__items { display: grid; gap: .1rem; margin-top: .25rem; color: #64748b; font-size: .72rem; line-height: 1.35; }
    .pip-assistance-page .pip-student-metadata__line { display: grid; grid-template-columns: .9rem 2.5rem minmax(0, 1fr); align-items: center; gap: .25rem; }
    .pip-assistance-page .pip-student-metadata__line i { color: #94a3b8; text-align: center; }
    .pip-assistance-page .pip-student-metadata__label { color: #94a3b8; font-size: .67rem; font-weight: 700; letter-spacing: .02em; text-transform: uppercase; }
    .pip-assistance-page .pip-student-metadata__line--assistance { color: #2563eb; }
    .pip-assistance-page .pip-student-metadata__line--assistance i, .pip-assistance-page .pip-student-metadata__line--assistance .pip-student-metadata__label { color: #60a5fa; }
    .pip-assistance-page .js-pip-student-detail { color: #1d4ed8; text-decoration: none; }
    .pip-assistance-page .js-pip-student-detail:hover, .pip-assistance-page .js-pip-student-detail:focus { color: #1e40af; text-decoration: underline; }
    .pip-assistance-page #pipStudentDetailModal .nav-tabs { flex-wrap: nowrap; overflow-x: auto; overflow-y: hidden; }
    .pip-assistance-page #pipStudentDetailModal .nav-link { white-space: nowrap; }
    @media (max-width: 991.98px) { .pip-assistance-page .pip-hero__metric { border-left: 0; border-top: 1px solid rgba(255,255,255,.25); padding-top: .75rem; margin-top: .75rem; } .pip-assistance-page .pip-filter-actions { height: auto; margin-top: 1rem; } .pip-assistance-page .pip-document-group { display: flex; flex-wrap: wrap; } }
    @media (max-width: 575.98px) { .pip-assistance-page #pipStudentDetailModal .modal-dialog { margin: .5rem; } }
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

            <div class="d-flex align-items-center text-muted small mb-3"><i class="fas fa-info-circle text-primary mr-2"></i>Dokumen dapat dipreview langsung; tanggal unggah dan pembaruan tersedia pada setiap berkas. Gunakan penanda tindak lanjut untuk seluruh pendataan atau pengajuan bantuan pada daftar ini.</div>
            <div class="table-responsive"><table id="pip-table" class="table table-hover table-sm mb-0"><thead><tr><th>#</th><th>Nama Lengkap</th><th>Dokumen</th><th>Tindak Lanjut</th><th>Aksi</th></tr></thead></table></div>
        </div>
    </div>
</div>

<div class="modal fade" id="pipStudentDetailModal" tabindex="-1" role="dialog" aria-labelledby="pipStudentDetailModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary">
                <h5 class="modal-title text-white" id="pipStudentDetailModalLabel"><i class="fas fa-user-graduate mr-1"></i> Detail Siswa</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Tutup"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body">
                <ul class="nav nav-tabs" id="pipSiswaDetailTabs" role="tablist">
                    <li class="nav-item"><a class="nav-link active" data-toggle="tab" href="#pip-detail-siswa" role="tab"><i class="fas fa-user mr-1"></i>Data Siswa</a></li>
                    <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#pip-detail-diri" role="tab"><i class="fas fa-id-card mr-1"></i>Data Diri</a></li>
                    <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#pip-detail-ortu" role="tab"><i class="fas fa-users mr-1"></i>Data Orang Tua</a></li>
                    <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#pip-detail-sekolah" role="tab"><i class="fas fa-school mr-1"></i>Sekolah Asal</a></li>
                    <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#pip-detail-dokumen" role="tab"><i class="fas fa-file-alt mr-1"></i>Dokumen</a></li>
                </ul>
                <div class="tab-content mt-3">
                    <div class="tab-pane fade show active" id="pip-detail-siswa" role="tabpanel"></div>
                    <div class="tab-pane fade" id="pip-detail-diri" role="tabpanel"></div>
                    <div class="tab-pane fade" id="pip-detail-ortu" role="tabpanel"></div>
                    <div class="tab-pane fade" id="pip-detail-sekolah" role="tabpanel"></div>
                    <div class="tab-pane fade" id="pip-detail-dokumen" role="tabpanel"></div>
                </div>
            </div>
            <div class="modal-footer">
                <a href="#" id="pipStudentDetailFullLink" class="btn btn-primary" target="_blank" rel="noopener"><i class="fas fa-history mr-1"></i> Lihat Riwayat Perubahan</a>
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
            { data: null, render: function(data, type, row, meta) { return meta.row + meta.settings._iDisplayStart + 1; }, orderable: false, searchable: false, className: 'text-center text-muted' },
            { data: 'nama_lengkap' },
            { data: 'dokumen' },
            { data: 'assistance_follow_up', name: 'bantuan_emis_updated', searchable: false, className: 'text-center pip-emis-status' },
            { data: 'actions', orderable: false, className: 'text-center text-nowrap' },
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

    // Penanda ini berlaku untuk seluruh pengajuan bantuan pada daftar ini.
    $(document).on('click', '#pip-table .btn-toggle-assistance-follow-up', function () {
        const button = $(this);
        const url = button.data('url');

        if (!url || button.data('processing')) return;

        button.data('processing', true);
        $.ajax({
            url: url,
            method: 'POST',
            dataType: 'json',
            data: { _token: '{{ csrf_token() }}' },
        })
            .done(function (response) {
                if (!response || !response.success) {
                    toastr.error('Penanda tindak lanjut pengajuan belum berhasil diperbarui.');
                    return;
                }

                const followedUp = Boolean(response.bantuan_followed_up);
                const markedAt = response.marked_at || '';
                const meta = button.siblings('.pip-assistance-follow-up-meta');

                button
                    .toggleClass('btn-success', followedUp)
                    .toggleClass('btn-outline-secondary', !followedUp)
                    .attr('title', followedUp
                        ? `Pengajuan bantuan sudah ditindaklanjuti${markedAt ? ` pada ${markedAt}` : ''} - klik untuk batalkan`
                        : 'Klik setelah pengajuan bantuan siswa ditindaklanjuti')
                    .html(`<i class="fas ${followedUp ? 'fa-check-circle' : 'fa-check'} mr-1"></i>${followedUp ? 'Sudah' : 'Tandai'}`);

                meta.text(markedAt).toggleClass('d-none', !markedAt);
                toastr.success(response.message || 'Penanda tindak lanjut pengajuan berhasil diperbarui.');
            })
            .fail(function (xhr) {
                toastr.error(xhr.responseJSON?.message || 'Penanda tindak lanjut pengajuan belum berhasil diperbarui.');
            })
            .always(function () {
                button.removeData('processing');
            });
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
    const detailRow = (label, value) => '<tr><td width="40%" class="bg-light"><strong>' + label + '</strong></td><td>' + escapeHtml(value) + '</td></tr>';
    const detailTable = rows => '<table class="table table-detail table-sm table-bordered mb-3">' + rows.join('') + '</table>';
    const detailLoading = '<div class="text-center py-5 text-muted"><i class="fas fa-spinner fa-spin fa-2x mb-3"></i><div>Memuat detail siswa...</div></div>';

    $(document).on('click', '.js-pip-student-detail', function () {
        const $button = $(this);
        const detailUrl = $button.data('detail-url');
        const fullDetailUrl = $button.data('full-detail-url');
        const $modal = $('#pipStudentDetailModal');
        if (!detailUrl) return;

        $('#pip-detail-siswa, #pip-detail-diri, #pip-detail-ortu, #pip-detail-sekolah, #pip-detail-dokumen').html(detailLoading);
        $('#pipSiswaDetailTabs .nav-link').removeClass('active').first().addClass('active');
        $('#pipStudentDetailModal .tab-pane').removeClass('show active').first().addClass('show active');
        $('#pipStudentDetailFullLink').attr('href', fullDetailUrl || '#');
        $modal.modal('show');

        $.get(detailUrl)
            .done(function (response) {
                if (!response || !response.success || !response.data) {
                    $('#pip-detail-siswa').html('<div class="alert alert-danger mb-0"><i class="fas fa-exclamation-circle mr-1"></i>Detail siswa tidak tersedia.</div>');
                    return;
                }

                const siswa = response.data;
                const user = siswa.user || {};
                const ortu = siswa.ortu || {};
                const sekolah = siswa.sekolah_asal || siswa.sekolahAsal || {};
                const gender = siswa.jenis_kelamin === 'L' ? 'Laki-laki' : (siswa.jenis_kelamin === 'P' ? 'Perempuan' : '-');
                $('#pip-detail-siswa').html('<div class="row"><div class="col-md-6"><h6 class="text-primary"><i class="fas fa-user mr-1"></i>Informasi Akun</h6>' + detailTable([detailRow('NISN', siswa.nisn), detailRow('Nomor Tes PPDB', siswa.nomor_tes), detailRow('Nama Lengkap', siswa.nama_lengkap), detailRow('Jenis Kelamin', gender), detailRow('Username', user.username), detailRow('Email', user.email)]) + '</div><div class="col-md-6"><h6 class="text-primary"><i class="fas fa-check-circle mr-1"></i>Status Kelengkapan</h6>' + detailTable([detailRow('Data Ortu', siswa.data_ortu_completed ? 'Lengkap' : 'Belum Lengkap'), detailRow('Data Diri', siswa.data_diri_completed ? 'Lengkap' : 'Belum Lengkap'), detailRow('Status Login', user.is_first_login ? 'Belum Ganti Password' : 'Sudah Ganti Password')]) + '<h6 class="text-primary mt-3"><i class="fas fa-key mr-1"></i>Akun Login</h6>' + detailTable([detailRow('Username', user.username), detailRow('Email', user.email), detailRow('Password', user.readable_password || 'Tidak ditampilkan')]) + '</div></div>');
                $('#pip-detail-diri').html('<div class="row"><div class="col-md-6"><h6 class="text-primary"><i class="fas fa-id-card mr-1"></i>Data Pribadi</h6>' + detailTable([detailRow('NIK', siswa.nik), detailRow('Tempat Lahir', siswa.tempat_lahir), detailRow('Tanggal Lahir', siswa.tanggal_lahir), detailRow('Jumlah Saudara', siswa.jumlah_saudara), detailRow('Anak Ke', siswa.anak_ke), detailRow('Hobi', siswa.hobi), detailRow('Cita-cita', siswa.cita_cita), detailRow('No. HP', siswa.nomor_hp)]) + '</div><div class="col-md-6"><h6 class="text-primary"><i class="fas fa-map-marker-alt mr-1"></i>Alamat Siswa</h6>' + detailTable([detailRow('Jenis Tempat Tinggal', siswa.jenis_tempat_tinggal), detailRow('Alamat', siswa.alamat_siswa), detailRow('RT / RW', [siswa.rt_siswa, siswa.rw_siswa].filter(Boolean).join(' / ')), detailRow('Kodepos', siswa.kodepos_siswa)]) + '</div></div>');
                $('#pip-detail-ortu').html('<div class="row"><div class="col-md-6"><h6 class="text-primary"><i class="fas fa-male mr-1"></i>Data Ayah</h6>' + detailTable([detailRow('Nama', ortu.nama_ayah), detailRow('NIK', ortu.nik_ayah), detailRow('HP', ortu.hp_ayah), detailRow('Pekerjaan', ortu.pekerjaan_ayah), detailRow('Penghasilan', ortu.penghasilan_ayah)]) + '</div><div class="col-md-6"><h6 class="text-primary"><i class="fas fa-female mr-1"></i>Data Ibu</h6>' + detailTable([detailRow('Nama', ortu.nama_ibu), detailRow('NIK', ortu.nik_ibu), detailRow('HP', ortu.hp_ibu), detailRow('Pekerjaan', ortu.pekerjaan_ibu), detailRow('Penghasilan', ortu.penghasilan_ibu)]) + '</div></div><h6 class="text-primary mt-3"><i class="fas fa-home mr-1"></i>Alamat Orang Tua</h6>' + detailTable([detailRow('No. KK', ortu.no_kk), detailRow('Alamat', ortu.alamat_ortu), detailRow('RT / RW', [ortu.rt_ortu, ortu.rw_ortu].filter(Boolean).join(' / ')), detailRow('Kodepos', ortu.kodepos)]));
                $('#pip-detail-sekolah').html('<div class="row"><div class="col-md-6"><h6 class="text-primary"><i class="fas fa-school mr-1"></i>Informasi Sekolah</h6>' + detailTable([detailRow('NPSN', sekolah.npsn || siswa.npsn_asal_sekolah), detailRow('NSM', sekolah.nsm), detailRow('Nama Sekolah', sekolah.nama || siswa.nama_sekolah_asal), detailRow('Bentuk Pendidikan', sekolah.bentuk_pendidikan), detailRow('Status', sekolah.status_sekolah)]) + '</div><div class="col-md-6"><h6 class="text-primary"><i class="fas fa-map-marker-alt mr-1"></i>Lokasi Sekolah</h6>' + detailTable([detailRow('Provinsi', sekolah.provinsi), detailRow('Kab/Kota', sekolah.kabupaten_kota), detailRow('Kecamatan', sekolah.kecamatan), detailRow('Alamat', sekolah.alamat_jalan)]) + '</div></div>');
                $('#pip-detail-dokumen').html('<div class="text-center py-4 text-muted"><i class="fas fa-spinner fa-spin mr-2"></i>Memuat dokumen...</div>');
                $.get(`{{ url('admin/siswa') }}/${siswa.id}/dokumen`).done(function (documents) {
                    if (!documents.success || !documents.data.length) { $('#pip-detail-dokumen').html('<div class="alert alert-info mb-0"><i class="fas fa-info-circle mr-1"></i>Belum ada dokumen yang diunggah.</div>'); return; }
                    const cards = documents.data.map(doc => '<div class="col-md-6 mb-3"><div class="border rounded p-3 h-100"><div class="font-weight-bold mb-1"><i class="fas fa-file-alt text-primary mr-1"></i>' + escapeHtml(doc.jenis_dokumen_label) + '</div><div class="small text-muted mb-2">' + escapeHtml(doc.file_size_formatted) + '</div><a href="' + escapeHtml(doc.file_url) + '" target="_blank" rel="noopener" class="btn btn-sm btn-outline-primary"><i class="fas fa-eye mr-1"></i>Lihat Dokumen</a></div></div>').join('');
                    $('#pip-detail-dokumen').html('<div class="row">' + cards + '</div>');
                }).fail(() => $('#pip-detail-dokumen').html('<div class="alert alert-warning mb-0">Dokumen tidak dapat dimuat.</div>'));
            })
            .fail(function (xhr) {
                const message = xhr.responseJSON?.message || 'Detail siswa tidak dapat dimuat. Silakan coba kembali.';
                $('#pip-detail-siswa').html('<div class="alert alert-danger mb-0"><i class="fas fa-exclamation-circle mr-1"></i>' + escapeHtml(message) + '</div>');
                $('#pip-detail-diri, #pip-detail-ortu, #pip-detail-sekolah, #pip-detail-dokumen').empty();
            });
    });
});
</script>
@stop
