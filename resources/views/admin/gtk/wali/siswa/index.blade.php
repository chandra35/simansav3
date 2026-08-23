@extends('adminlte::page')

@section('title', 'Daftar Siswa — Kelas Saya')
@section('plugins.Datatables', true)
@section('plugins.Sweetalert2', true)

@section('content_header')
    <div class="row mb-2">
        <div class="col-sm-6"><h1><i class="fas fa-user-graduate text-primary"></i> Data Siswa</h1></div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('admin.gtk.dashboard') }}">Dashboard Saya</a></li>
                <li class="breadcrumb-item active">Data Siswa</li>
            </ol>
        </div>
    </div>
@stop

@section('content')
<div class="gtk-wali-siswa-page">
    <div class="card bg-gradient-primary text-white mb-4">
        <div class="card-body">
            <div class="row align-items-center">
                <div class="col-lg-8">
                    <h3 class="mb-1"><i class="fas fa-database mr-1"></i> Data Peserta Didik</h3>
                    <p class="mb-2 text-white-50">Pantau biodata, rombel, status akun, dan kelengkapan siswa dalam tampilan hanya-baca.</p>
                    <p class="mb-0">Gunakan filter dan detail lengkap untuk mendukung administrasi kelas secara cepat.</p>
                </div>
                <div class="col-lg-4 mt-3 mt-lg-0">
                    <div class="row text-center">
                        <div class="col-7"><div class="text-white-50 small text-uppercase font-weight-bold">Rombel</div><h3 class="mb-0 text-white">{{ $kelas->nama_lengkap ?? $kelas->nama_kelas }}</h3></div>
                        <div class="col-5"><div class="text-white-50 small text-uppercase font-weight-bold">Data Lengkap</div><h3 class="mb-0 text-white">{{ $stats['data_lengkap'] }}/{{ $stats['total'] }}</h3></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @includeWhen($kelasList->count() > 1, 'admin.gtk.wali.partials.kelas-switcher', ['route' => 'admin.gtk.wali.siswa.index'])

    <div class="row mb-4">
        @foreach([
            ['label' => 'Total Siswa', 'value' => $stats['total'], 'color' => 'primary', 'description' => 'Siswa aktif pada rombel ini.'],
            ['label' => 'Laki-Laki', 'value' => $stats['laki_laki'], 'color' => 'info', 'description' => 'Siswa laki-laki aktif.'],
            ['label' => 'Perempuan', 'value' => $stats['perempuan'], 'color' => 'danger', 'description' => 'Siswa perempuan aktif.'],
            ['label' => 'Data Lengkap', 'value' => $stats['data_lengkap'], 'color' => 'success', 'description' => 'Data diri dan orang tua lengkap.'],
        ] as $stat)
            <div class="col-6 col-xl-3 mb-3 mb-xl-0">
                <div class="card card-outline card-{{ $stat['color'] }} h-100">
                    <div class="card-body">
                        <div class="text-muted small text-uppercase font-weight-bold">{{ $stat['label'] }}</div>
                        <h3 class="text-{{ $stat['color'] }} mb-1">{{ number_format($stat['value']) }}</h3>
                        <div class="text-muted small">{{ $stat['description'] }}</div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="card card-outline card-primary">
        <div class="card-header"><h3 class="card-title"><i class="fas fa-user-graduate mr-1"></i> Data Siswa {{ $kelas->nama_kelas }}</h3></div>
        <div class="card-body">
            <div class="simansa-filter-panel mb-3">
                <div class="row">
                    <div class="col-md-6 col-xl-3 mb-3">
                        <label for="filterJenisKelamin" class="font-weight-bold small"><i class="fas fa-venus-mars mr-1"></i> Jenis Kelamin</label>
                        <select id="filterJenisKelamin" class="form-control form-control-sm">
                            <option value="">Semua</option><option value="L">Laki-Laki</option><option value="P">Perempuan</option>
                        </select>
                    </div>
                    <div class="col-md-6 col-xl-3 mb-3">
                        <label for="filterStatusData" class="font-weight-bold small"><i class="fas fa-check-circle mr-1"></i> Status Data</label>
                        <select id="filterStatusData" class="form-control form-control-sm">
                            <option value="">Semua</option><option value="lengkap">Data Lengkap</option><option value="belum">Belum Lengkap</option>
                        </select>
                    </div>
                </div>
                <button type="button" id="btnResetFilter" class="btn btn-sm btn-outline-secondary"><i class="fas fa-redo mr-1"></i> Reset Filter</button>
            </div>

            <div class="gtk-wali-table-note"><i class="fas fa-info-circle mr-1"></i> Data bersifat hanya-baca. Klik Detail untuk melihat biodata lengkap, orang tua, sekolah asal, dokumen, dan catatan siswa.</div>
            <div class="gtk-wali-table-scroll">
                <table id="tblSiswaWali" class="table table-hover table-sm gtk-wali-siswa-table mb-0" style="width:100%">
                    <thead><tr>
                        <th class="text-center">Foto</th><th>Nama / NISN</th><th class="text-center">JK</th><th>Kelas</th>
                        <th class="text-center">Ortu</th><th class="text-center">Diri</th>
                        <th class="text-center">EMIS</th><th class="text-center">Keberadaan</th><th class="text-center">Tgl Masuk</th><th class="text-center">Aksi</th>
                    </tr></thead>
                    <tbody>
                    @foreach($siswa as $i => $s)
                        @php $absen = $s->pivot->nomor_urut_absen ?? ($i + 1); @endphp
                        <tr data-gender="{{ $s->jenis_kelamin }}" data-complete="{{ $s->isDataComplete() ? 'lengkap' : 'belum' }}">
                            <td class="text-center"><img src="{{ $s->foto_profile_url }}" alt="Foto {{ $s->nama_lengkap }}" class="img-circle elevation-1 gtk-wali-avatar"></td>
                            <td>
                                <div class="font-weight-bold text-dark">{{ $s->nama_lengkap }}</div>
                                <small class="text-muted">NISN {{ $s->nisn ?: '—' }} · Absen {{ $absen }}</small>
                                @if($s->pivot->is_ketua_kelas && $s->pivot->ketua_kelas_selesai_at === null)<span class="badge badge-warning ml-1"><i class="fas fa-crown"></i> Ketua</span>@endif
                            </td>
                            <td class="text-center"><span class="badge {{ $s->jenis_kelamin === 'L' ? 'badge-primary' : 'badge-danger' }}">{{ $s->jenis_kelamin }}</span></td>
                            <td><span class="badge badge-primary">{{ $kelas->nama_kelas }}</span></td>
                            <td class="text-center"><span class="badge {{ $s->data_ortu_completed ? 'badge-success' : 'badge-warning' }}">{{ $s->data_ortu_completed ? 'Lengkap' : 'Belum' }}</span></td>
                            <td class="text-center"><span class="badge {{ $s->data_diri_completed ? 'badge-success' : 'badge-warning' }}">{{ $s->data_diri_completed ? 'Lengkap' : 'Belum' }}</span></td>
                            <td class="text-center"><span class="badge {{ $s->emis_registered ? 'badge-success' : 'badge-warning' }}">{{ $s->emis_registered ? 'Sudah' : 'Belum' }}</span></td>
                            <td class="text-center">@if($s->pivot->keberadaan_diverifikasi_at)<span class="badge badge-success">Ada</span>@else<span class="badge badge-secondary">Belum Dicek</span>@endif</td>
                            <td class="text-center"><small>{{ $s->pivot->tanggal_masuk ? \Carbon\Carbon::parse($s->pivot->tanggal_masuk)->format('d/m/Y') : '—' }}</small></td>
                            <td class="text-center">
                                <div class="btn-group btn-group-sm">
                                    <button type="button" class="btn btn-primary btn-detail-siswa" data-url="{{ route('admin.gtk.wali.siswa.show', $s->id) }}" title="Detail lengkap"><i class="fas fa-eye"></i></button>
                                    <a href="{{ route('admin.gtk.wali.catatan.index', ['kelas_id' => $kelas->id, 'siswa_id' => $s->id]) }}" class="btn btn-secondary" title="Catatan siswa"><i class="fas fa-sticky-note"></i></a>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalDetailSiswa" tabindex="-1" role="dialog" aria-labelledby="modalDetailSiswaLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable" role="document">
            <div class="modal-content">
                <div class="modal-header bg-primary">
                    <h5 class="modal-title text-white" id="modalDetailSiswaLabel"><i class="fas fa-user-graduate mr-1"></i> Detail Siswa</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Tutup"><span aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body" id="modalDetailSiswaBody"><div class="text-center py-5 text-muted"><i class="fas fa-spinner fa-spin fa-2x mb-3"></i><div>Memuat detail siswa...</div></div></div>
            </div>
        </div>
    </div>
</div>
@stop

@section('css')
<style>
    .gtk-wali-siswa-page > .bg-gradient-primary { overflow:hidden; border:0; border-radius:16px; box-shadow:0 12px 28px rgba(15,23,42,.1); }
    .gtk-wali-siswa-page > .bg-gradient-primary .card-body { padding:1.2rem 1.25rem; }
    .gtk-wali-siswa-page > .bg-gradient-primary h3 { font-size:1.35rem; font-weight:700; overflow-wrap:anywhere; }
    .gtk-wali-siswa-page .simansa-filter-panel { padding:1rem; border:1px solid #e2e8f0; border-radius:12px; background:#f8fafc; }
    .gtk-wali-table-note { margin-bottom:.85rem; padding:.72rem .85rem; border:1px solid #dbeafe; border-radius:.75rem; background:#eff6ff; color:#1e3a8a; font-size:.86rem; font-weight:600; }
    .gtk-wali-table-scroll { width:100%; overflow-x:auto; overscroll-behavior-inline:contain; scrollbar-width:thin; -webkit-overflow-scrolling:touch; }
    .gtk-wali-siswa-table { min-width:1080px; table-layout:fixed; }
    .gtk-wali-siswa-table thead th { background:#f1f5f9; color:#1e293b; font-size:.72rem; font-weight:800; letter-spacing:.035em; text-transform:uppercase; vertical-align:middle; }
    .gtk-wali-siswa-table tbody td { color:#0f172a; font-size:.82rem; padding:.55rem; vertical-align:middle; border-top:0; border-bottom:1px solid #f1f5f9; }
    .gtk-wali-siswa-table th:nth-child(1) { width:5%; } .gtk-wali-siswa-table th:nth-child(2) { width:20%; }
    .gtk-wali-siswa-table th:nth-child(3) { width:4%; } .gtk-wali-siswa-table th:nth-child(4) { width:8%; }
    .gtk-wali-siswa-table th:nth-child(5), .gtk-wali-siswa-table th:nth-child(6), .gtk-wali-siswa-table th:nth-child(7), .gtk-wali-siswa-table th:nth-child(8) { width:7%; }
    .gtk-wali-siswa-table th:nth-child(9) { width:10%; } .gtk-wali-siswa-table th:nth-child(10) { width:9%; } .gtk-wali-siswa-table th:nth-child(11) { width:16%; }
    .gtk-wali-avatar { width:40px; height:40px; object-fit:cover; }
    #modalDetailSiswa .modal-content { border:0; border-radius:16px; overflow:hidden; box-shadow:0 24px 64px rgba(15,23,42,.22); }
    #modalDetailSiswa .nav-tabs { flex-wrap:nowrap; overflow-x:auto; overflow-y:hidden; }
    #modalDetailSiswa .nav-tabs .nav-link { white-space:nowrap; }
    #modalDetailSiswa .table-detail td { padding:.5rem; overflow-wrap:anywhere; }
    @media (max-width:575.98px) {
        .gtk-wali-siswa-page > .bg-gradient-primary .card-body { padding:1rem; }
        .gtk-wali-siswa-page > .bg-gradient-primary h3 { font-size:1.1rem; }
        #modalDetailSiswa .modal-dialog { margin:.5rem; }
        #modalDetailSiswa .modal-body { padding:.75rem; }
    }
</style>
@stop

@section('js')
<script>
    $(function () {
        var table = $('#tblSiswaWali').DataTable({
            paging:true, pageLength:25, ordering:true, order:[[1, 'asc']],
            columnDefs:[{ orderable:false, targets:[0, 10] }],
            language:{ url:'//cdn.datatables.net/plug-ins/1.13.6/i18n/id.json' }
        });

        $.fn.dataTable.ext.search.push(function (settings, data, dataIndex) {
            if (settings.nTable.id !== 'tblSiswaWali') return true;
            var row = table.row(dataIndex).node();
            var gender = $('#filterJenisKelamin').val();
            var complete = $('#filterStatusData').val();
            return (!gender || row.dataset.gender === gender) && (!complete || row.dataset.complete === complete);
        });
        $('#filterJenisKelamin, #filterStatusData').on('change', function () { table.draw(); });
        $('#btnResetFilter').on('click', function () { $('#filterJenisKelamin, #filterStatusData').val(''); table.search('').draw(); });

        $(document).on('click', '.btn-detail-siswa', function () {
            var modal = $('#modalDetailSiswa');
            var body = $('#modalDetailSiswaBody');
            var title = $('#modalDetailSiswaLabel');
            if (typeof window.hideAppGlobalOverlay === 'function') window.hideAppGlobalOverlay();
            title.html('<i class="fas fa-user-graduate mr-1"></i> Detail Siswa');
            body.html('<div class="text-center py-5 text-muted"><i class="fas fa-spinner fa-spin fa-2x mb-3"></i><div>Memuat detail siswa...</div></div>');
            modal.modal('show');
            $.ajax({ url:$(this).data('url'), method:'GET', dataType: 'json', headers:{ 'X-Requested-With':'XMLHttpRequest' } })
                .done(function (response) {
                    title.html('<i class="fas fa-user-graduate mr-1"></i> ' + $('<div>').text(response.title || 'Detail Siswa').html());
                    body.html(response.html);
                }).fail(function () {
                    modal.modal('hide');
                    Swal.fire({ icon:'error', title:'Detail Tidak Dapat Dimuat', text:'Silakan coba kembali beberapa saat lagi.', confirmButtonText:'Tutup' });
                });
        });
    });
</script>
@stop
