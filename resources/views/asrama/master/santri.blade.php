@extends('adminlte::page')
@section('title', 'Santri Asrama')
@section('plugins.Select2', true)
@section('content_header') @stop
@section('content')
@include('asrama._alerts')
@php
    $heroTitle='Santri Asrama';
    $heroDescription='Aktifkan siswa SIMANSA sebagai santri per rombel atau per siswa, tanpa menggandakan identitas.';
    $heroAction='<button class="btn btn-light" data-toggle="modal" data-target="#assignSantri"><i class="fas fa-user-plus mr-1"></i> Tambah Santri</button>';
@endphp
@include('asrama._hero')
<div class="row">
    <div class="col-md-6 col-xl-3 mb-3">
        <div class="card card-outline card-primary h-100"><div class="card-body py-3">
            <div class="text-muted small text-uppercase font-weight-bold">Santri Aktif</div>
            <h3 class="text-primary mb-0">{{ number_format($stats['total']) }}</h3>
        </div></div>
    </div>
    <div class="col-md-6 col-xl-3 mb-3">
        <div class="card card-outline card-info h-100"><div class="card-body py-3">
            <div class="text-muted small text-uppercase font-weight-bold">Laki-Laki</div>
            <h3 class="text-info mb-0">{{ number_format($stats['laki']) }}</h3>
        </div></div>
    </div>
    <div class="col-md-6 col-xl-3 mb-3">
        <div class="card card-outline card-danger h-100"><div class="card-body py-3">
            <div class="text-muted small text-uppercase font-weight-bold">Perempuan</div>
            <h3 class="text-danger mb-0">{{ number_format($stats['perempuan']) }}</h3>
        </div></div>
    </div>
    <div class="col-md-6 col-xl-3 mb-3">
        <div class="card card-outline card-success h-100"><div class="card-body py-3">
            <div class="text-muted small text-uppercase font-weight-bold">Sudah Dapat Kamar</div>
            <h3 class="text-success mb-0">{{ number_format($stats['berkamar']) }}</h3>
        </div></div>
    </div>
</div>

<div class="card card-outline card-primary">
    <div class="card-header"><h3 class="card-title"><i class="fas fa-user-graduate mr-2"></i>Manajemen Santri Asrama</h3></div>
    <div class="card-body">
        <form method="get" id="filterForm">
            <div class="row">
                <div class="col-md-6 col-xl-3 mb-3">
                    <label class="small font-weight-bold text-muted" for="filterQ"><i class="fas fa-search mr-1"></i> Pencarian</label>
                    <input type="text" id="filterQ" name="q" value="{{ request('q') }}" class="form-control form-control-sm" placeholder="Nama / NISN / No. Induk">
                </div>
                <div class="col-md-6 col-xl-2 mb-3">
                    <label class="small font-weight-bold text-muted" for="filterTingkat"><i class="fas fa-layer-group mr-1"></i> Tingkat</label>
                    <select id="filterTingkat" name="tingkat" class="form-control form-control-sm">
                        <option value="">Semua</option>
                        @foreach($tingkatOptions as $t)<option value="{{ $t }}" @selected(request('tingkat')==$t)>Tingkat {{ $t }}</option>@endforeach
                    </select>
                </div>
                <div class="col-md-6 col-xl-2 mb-3">
                    <label class="small font-weight-bold text-muted" for="filterKelas"><i class="fas fa-door-open mr-1"></i> Rombel</label>
                    <select id="filterKelas" name="kelas_id" class="form-control form-control-sm">
                        <option value="">Semua</option>
                        @foreach($assignedKelas as $k)<option value="{{ $k->id }}" data-tingkat="{{ $k->tingkat }}" @selected(request('kelas_id')==$k->id)>{{ $k->nama_kelas }}</option>@endforeach
                    </select>
                    <small class="text-muted">Hanya rombel yang punya santri.</small>
                </div>
                <div class="col-md-6 col-xl-2 mb-3">
                    <label class="small font-weight-bold text-muted" for="filterJk"><i class="fas fa-venus-mars mr-1"></i> Jenis Kelamin</label>
                    <select id="filterJk" name="jenis_kelamin" class="form-control form-control-sm">
                        <option value="">Semua</option>
                        <option value="L" @selected(request('jenis_kelamin')==='L')>Laki-Laki</option>
                        <option value="P" @selected(request('jenis_kelamin')==='P')>Perempuan</option>
                    </select>
                </div>
                <div class="col-md-6 col-xl-2 mb-3">
                    <label class="small font-weight-bold text-muted" for="filterStatus"><i class="fas fa-check-circle mr-1"></i> Status</label>
                    <select id="filterStatus" name="status" class="form-control form-control-sm">
                        <option value="">Semua</option>
                        <option value="aktif" @selected(request('status')==='aktif')>Aktif</option>
                        <option value="nonaktif" @selected(request('status')==='nonaktif')>Nonaktif</option>
                    </select>
                </div>
            </div>
            <div class="d-flex justify-content-end mb-3">
                <a href="{{ route('asrama.santri.index') }}" id="btnResetFilter" class="btn btn-sm btn-outline-secondary"><i class="fas fa-redo mr-1"></i> Reset Filter</a>
            </div>
        </form>
        <div id="santriTableWrap">
            @include('asrama.master._santri-table')
        </div>
    </div>
</div>

{{-- Modal Preview Foto --}}
<div class="modal fade" id="fotoPreviewModal" tabindex="-1"><div class="modal-dialog modal-dialog-centered modal-lg"><div class="modal-content">
    <div class="modal-header bg-info"><h5 class="modal-title text-white"><i class="fas fa-camera-retro"></i> Preview Foto Santri</h5><button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button></div>
    <div class="modal-body text-center">
        <p class="font-weight-bold mb-3" id="fotoPreviewName">-</p>
        <img id="fotoPreviewImage" src="" alt="Preview foto santri" class="img-fluid rounded shadow-sm border" style="max-height:65vh;object-fit:contain;">
    </div>
    <div class="modal-footer"><button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button></div>
</div></div></div>

<div class="modal fade" id="assignSantri" tabindex="-1"><div class="modal-dialog modal-xl modal-dialog-centered"><form method="post" action="{{ route('asrama.santri.store') }}" class="modal-content" data-asrama-loading data-loading-title="Mengaktifkan santri" data-loading-text="Identitas dan akses portal sedang disinkronkan dari SIMANSA.">@csrf
<div class="modal-header"><h5 class="modal-title"><i class="fas fa-user-plus mr-2"></i>Tambah Santri Asrama</h5><button type="button" class="close" data-dismiss="modal">&times;</button></div>
<div class="modal-body">
    <div class="callout callout-info py-2 mb-3"><small><i class="fas fa-info-circle mr-1"></i>Daftar rombel di bawah hanya rombel SIMANSA yang bertanda <strong>Rombel Asrama</strong> (Manajemen Data → Kelas). Santri dari rombel otomatis menjadi anggota rombel asramanya. Siswa individual (titipan dari rombel reguler) dapat dipilih di panel kanan.</small></div>
    <div class="row">
        <div class="col-lg-6">
            <div class="card card-outline card-primary mb-3">
                <div class="card-header py-2"><h3 class="card-title"><i class="fas fa-users mr-1"></i> Rombel Asrama (SIMANSA)</h3><div class="card-tools"><span class="badge badge-primary" id="kelasCount">0 dipilih</span></div></div>
                <div class="card-body p-2">
                    <div class="input-group input-group-sm mb-2"><div class="input-group-prepend"><span class="input-group-text"><i class="fas fa-search"></i></span></div><input type="text" id="kelasSearch" class="form-control" placeholder="Cari rombel..."></div>
                    <div style="max-height:260px;overflow-y:auto" id="kelasList">
                        @if($classes->isEmpty())
                        <div class="text-muted small px-1 py-2"><i class="fas fa-info-circle mr-1"></i>Belum ada rombel bertanda Asrama. Centang "Rombel Asrama (Kampus 2)" pada rombel di Manajemen Data &rarr; Kelas.</div>
                        @endif
                        @foreach($classes->groupBy('tingkat') as $tingkat => $group)
                        <div class="text-muted text-uppercase font-weight-bold small px-1 pt-2 pb-1" data-kelas-group>Tingkat {{ $tingkat ?: '-' }}</div>
                        @foreach($group as $class)
                        <div class="custom-control custom-checkbox px-1 py-1 pl-4" data-kelas-item data-search="{{ strtolower($class->nama_kelas) }}">
                            <input type="checkbox" class="custom-control-input" id="kelas{{ $class->id }}" name="kelas_ids[]" value="{{ $class->id }}" data-count="{{ $class->siswa_aktif_count }}">
                            <label class="custom-control-label d-flex justify-content-between w-100" for="kelas{{ $class->id }}"><span>{{ $class->nama_kelas }}</span><span class="badge badge-light border ml-2">{{ $class->siswa_aktif_count }} siswa</span></label>
                        </div>
                        @endforeach
                        @endforeach
                    </div>
                </div>
                <div class="card-footer py-1 text-muted small">Total siswa dari rombel terpilih: <strong id="kelasTotal">0</strong></div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card card-outline card-primary mb-3">
                <div class="card-header py-2"><h3 class="card-title"><i class="fas fa-user-check mr-1"></i> Pilih siswa individual</h3><div class="card-tools"><span class="badge badge-primary" id="siswaCount">0 dipilih</span></div></div>
                <div class="card-body p-2">
                    <div class="input-group input-group-sm mb-2"><div class="input-group-prepend"><span class="input-group-text"><i class="fas fa-search"></i></span></div><input type="text" id="siswaSearch" class="form-control" placeholder="Cari nama atau NIS..."></div>
                    <div style="max-height:260px;overflow-y:auto" id="siswaList">
                        @foreach($students as $student)
                        @php $nis = $student->nis_lokal ?: $student->nisn; @endphp
                        <div class="custom-control custom-checkbox px-1 py-1 pl-4" data-siswa-item data-search="{{ strtolower($student->nama_lengkap.' '.$nis) }}">
                            <input type="checkbox" class="custom-control-input" id="siswa{{ $student->id }}" name="siswa_ids[]" value="{{ $student->id }}">
                            <label class="custom-control-label d-flex justify-content-between w-100" for="siswa{{ $student->id }}"><span>{{ $student->nama_lengkap }}</span><span class="text-muted small ml-2">{{ $nis ?: '-' }}</span></label>
                        </div>
                        @endforeach
                    </div>
                </div>
                <div class="card-footer py-1 text-muted small">Siswa individual terpilih: <strong id="siswaTotal">0</strong></div>
            </div>
        </div>
        <div class="col-md-6"><div class="form-group mb-0"><label class="font-weight-bold">Tanggal masuk</label><input type="date" name="tanggal_masuk" class="form-control" value="{{ now()->toDateString() }}"></div></div>
    </div>
</div><div class="modal-footer"><button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button><button type="submit" class="btn btn-primary"><i class="fas fa-check mr-1"></i> Aktifkan Santri</button></div>
</form></div></div>
<div class="modal fade" id="viewSantriModal" tabindex="-1"><div class="modal-dialog modal-xl"><div class="modal-content">
<div class="modal-header bg-primary"><h5 class="modal-title text-white"><i class="fas fa-user-graduate"></i> Detail Santri</h5><button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button></div>
<div class="modal-body">
    <ul class="nav nav-tabs" id="santriDetailTabs" role="tablist">
        <li class="nav-item"><a class="nav-link active" data-toggle="tab" href="#santri-asrama" role="tab" style="color:#007bff;"><i class="fas fa-mosque"></i> Info Asrama</a></li>
        <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#santri-data" role="tab" style="color:#495057;"><i class="fas fa-user"></i> Data Siswa</a></li>
        <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#santri-diri" role="tab" style="color:#495057;"><i class="fas fa-id-card"></i> Data Diri</a></li>
        <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#santri-ortu" role="tab" style="color:#495057;"><i class="fas fa-users"></i> Data Orang Tua</a></li>
    </ul>
    <div class="tab-content mt-3">
        <div class="tab-pane fade show active" id="santri-asrama" role="tabpanel"></div>
        <div class="tab-pane fade" id="santri-data" role="tabpanel"></div>
        <div class="tab-pane fade" id="santri-diri" role="tabpanel"></div>
        <div class="tab-pane fade" id="santri-ortu" role="tabpanel"></div>
    </div>
</div>
<div class="modal-footer"><button type="button" class="btn btn-secondary" data-dismiss="modal"><i class="fas fa-times"></i> Tutup</button></div>
</div></div></div>
@include('asrama._scripts')
@stop
@section('css') @include('asrama._styles') @endsection
@section('js')
<script>
$(function () {
    function filterList(term, itemSel, listId) {
        term = term.toLowerCase().trim();
        $('#' + listId + ' [' + itemSel + ']').each(function () {
            $(this).toggle($(this).data('search').indexOf(term) > -1);
        });
        if (itemSel === 'data-kelas-item') {
            $('#kelasList [data-kelas-group]').each(function () {
                var anyVisible = $(this).nextUntil('[data-kelas-group]').filter(':visible').length > 0;
                $(this).toggle(anyVisible);
            });
        }
    }

    $('#kelasSearch').on('input', function () { filterList(this.value, 'data-kelas-item', 'kelasList'); });
    $('#siswaSearch').on('input', function () { filterList(this.value, 'data-siswa-item', 'siswaList'); });

    function updateKelas() {
        var checked = $('#kelasList input:checked'), total = 0;
        checked.each(function () { total += parseInt($(this).data('count')) || 0; });
        $('#kelasCount').text(checked.length + ' dipilih');
        $('#kelasTotal').text(total);
    }
    function updateSiswa() {
        var n = $('#siswaList input:checked').length;
        $('#siswaCount').text(n + ' dipilih');
        $('#siswaTotal').text(n);
    }
    $('#kelasList').on('change', 'input', updateKelas);
    $('#siswaList').on('change', 'input', updateSiswa);

    $('#assignSantri').on('hidden.bs.modal', function () {
        $('#kelasSearch, #siswaSearch').val('');
        filterList('', 'data-kelas-item', 'kelasList');
        filterList('', 'data-siswa-item', 'siswaList');
    });

    // accent-white membuat teks nav-tabs putih; warna harus dipaksa inline saat tab berganti
    $('#santriDetailTabs .nav-link').on('shown.bs.tab', function () {
        $('#santriDetailTabs .nav-link').css('color', '#495057');
        $(this).css('color', '#007bff');
    });

    $(document).on('click', '.js-preview-foto', function () {
        $('#fotoPreviewName').text($(this).data('student-name') || '-');
        $('#fotoPreviewImage').attr('src', $(this).data('preview-url'));
        $('#fotoPreviewModal').modal('show');
    });

    var $kelasOptions = $('#filterKelas option').clone();
    function syncKelasOptions() {
        var tingkat = $('#filterTingkat').val();
        var current = $('#filterKelas').val();
        $('#filterKelas').empty().append($kelasOptions.filter(function () {
            return !tingkat || !this.value || String($(this).data('tingkat')) === tingkat;
        }).clone());
        $('#filterKelas').val(current);
        if ($('#filterKelas').val() === null) $('#filterKelas').val('');
    }
    $('#filterTingkat').on('change', syncKelasOptions);
    syncKelasOptions();

    // AJAX auto-load table saat filter berubah / pagination diklik
    var searchTimer = null;

    function loadTable(url, pushUrl) {
        $('#santriTableWrap').css('opacity', .5);
        $.get(url, function (html) {
            $('#santriTableWrap').html(html).css('opacity', 1);
            if (pushUrl) window.history.replaceState(null, '', url);
        }).fail(function () {
            $('#santriTableWrap').css('opacity', 1);
            if (window.toastr) toastr.error('Gagal memuat data santri');
        });
    }

    function applyFilters() {
        var params = $('#filterForm').serialize();
        loadTable('{{ route('asrama.santri.index') }}' + (params ? '?' + params : ''), true);
    }

    $('#filterForm').on('submit', function (e) { e.preventDefault(); applyFilters(); });
    $('#filterTingkat, #filterKelas, #filterJk, #filterStatus').on('change', applyFilters);
    $('#filterQ').on('input', function () {
        clearTimeout(searchTimer);
        searchTimer = setTimeout(applyFilters, 450);
    });
    $('#btnResetFilter').on('click', function (e) {
        e.preventDefault();
        $('#filterForm')[0].reset();
        syncKelasOptions();
        loadTable(this.href, true);
    });
    $('#santriTableWrap').on('click', '.pagination a', function (e) {
        e.preventDefault();
        loadTable(this.href, true);
    });
});

function esc(v) {
    return (v === null || v === undefined || v === '') ? null : $('<div>').text(v).html();
}
function cell(v, fallback) {
    return esc(v) || '<span class="text-muted">' + (fallback || '-') + '</span>';
}
function fmtDate(v) {
    return v ? new Date(v).toLocaleDateString('id-ID') : '-';
}

function showSantri(id) {
    $.get(`{{ url('asrama/santri') }}/${id}/detail`)
        .done(function (response) {
            if (!response.success) return;
            const santri = response.data;
            loadSantriAsramaTab(santri);
            loadSantriDataTab(santri.siswa);
            loadSantriDiriTab(santri.siswa);
            loadSantriOrtuTab(santri.siswa);
            $('#santriDetailTabs .nav-link').css('color', '#495057').removeClass('active').first().addClass('active').css('color', '#007bff');
            $('#viewSantriModal .tab-pane').removeClass('show active').first().addClass('show active');
            $('#viewSantriModal').modal('show');
        })
        .fail(function () {
            if (window.toastr) toastr.error('Gagal memuat data santri', 'Error!');
            else alert('Gagal memuat data santri');
        });
}

function loadSantriAsramaTab(santri) {
    const statusBadge = santri.status === 'aktif'
        ? '<span class="badge badge-success">Aktif</span>'
        : '<span class="badge badge-secondary">' + (esc(santri.status) || '-') + '</span>';
    const html = `
        <div class="row">
            <div class="col-md-6">
                <h6 class="text-primary"><i class="fas fa-id-badge"></i> Identitas Asrama</h6>
                <table class="table table-sm table-bordered">
                    <tr><td width="40%" class="bg-light"><strong>Nomor Induk Asrama</strong></td><td><code>${cell(santri.nomor_induk_asrama)}</code></td></tr>
                    <tr><td class="bg-light"><strong>Status</strong></td><td>${statusBadge}</td></tr>
                    <tr><td class="bg-light"><strong>Tanggal Masuk</strong></td><td>${fmtDate(santri.tanggal_masuk)}</td></tr>
                    <tr><td class="bg-light"><strong>Tanggal Keluar</strong></td><td>${fmtDate(santri.tanggal_keluar)}</td></tr>
                    <tr><td class="bg-light"><strong>Catatan</strong></td><td>${cell(santri.catatan, 'Tidak ada')}</td></tr>
                </table>
            </div>
            <div class="col-md-6">
                <h6 class="text-primary"><i class="fas fa-bed"></i> Penempatan</h6>
                <table class="table table-sm table-bordered">
                    <tr><td width="40%" class="bg-light"><strong>Rombel Asrama</strong></td><td>${cell(santri.kelas_aktif?.kelas?.nama_kelas, 'Belum dibagi')}</td></tr>
                    <tr><td class="bg-light"><strong>Kamar</strong></td><td>${cell(santri.kamar_aktif?.kamar?.nama, 'Belum ditempatkan')}</td></tr>
                    <tr><td class="bg-light"><strong>Rombel SIMANSA</strong></td><td>${cell(santri.kelas_aktif?.kelas?.kelas_reguler?.nama_kelas || santri.siswa?.kelas_tahun_aktif?.[0]?.nama_kelas)}</td></tr>
                </table>
            </div>
        </div>`;
    $('#santri-asrama').html(html);
}

function loadSantriDataTab(siswa) {
    const jk = siswa.jenis_kelamin === 'L'
        ? '<span class="badge badge-primary">Laki-laki</span>'
        : '<span class="badge badge-danger">Perempuan</span>';
    const html = `
        <div class="row">
            <div class="col-md-6">
                <h6 class="text-primary"><i class="fas fa-user"></i> Informasi Siswa</h6>
                <table class="table table-sm table-bordered">
                    <tr><td width="40%" class="bg-light"><strong>NISN</strong></td><td>${cell(siswa.nisn)}</td></tr>
                    <tr><td class="bg-light"><strong>NIS Lokal</strong></td><td>${cell(siswa.nis_lokal)}</td></tr>
                    <tr><td class="bg-light"><strong>Nama Lengkap</strong></td><td>${cell(siswa.nama_lengkap)}</td></tr>
                    <tr><td class="bg-light"><strong>Jenis Kelamin</strong></td><td>${jk}</td></tr>
                </table>
            </div>
            <div class="col-md-6">
                <h6 class="text-primary"><i class="fas fa-key"></i> Akun Login</h6>
                <table class="table table-sm table-bordered">
                    <tr><td width="40%" class="bg-light"><strong>Username</strong></td><td><code>${cell(siswa.user?.username)}</code></td></tr>
                    <tr><td class="bg-light"><strong>Email</strong></td><td>${cell(siswa.user?.email, 'Belum diisi')}</td></tr>
                </table>
            </div>
        </div>`;
    $('#santri-data').html(html);
}

function loadSantriDiriTab(siswa) {
    const html = `
        <div class="row">
            <div class="col-md-6">
                <h6 class="text-primary"><i class="fas fa-id-card"></i> Data Pribadi</h6>
                <table class="table table-sm table-bordered">
                    <tr><td width="40%" class="bg-light"><strong>NIK</strong></td><td>${cell(siswa.nik, 'Belum diisi')}</td></tr>
                    <tr><td class="bg-light"><strong>Tempat Lahir</strong></td><td>${cell(siswa.tempat_lahir, 'Belum diisi')}</td></tr>
                    <tr><td class="bg-light"><strong>Tanggal Lahir</strong></td><td>${fmtDate(siswa.tanggal_lahir)}</td></tr>
                    <tr><td class="bg-light"><strong>Anak Ke</strong></td><td>${cell(siswa.anak_ke, 'Belum diisi')}</td></tr>
                    <tr><td class="bg-light"><strong>Jumlah Saudara</strong></td><td>${cell(siswa.jumlah_saudara, 'Belum diisi')}</td></tr>
                    <tr><td class="bg-light"><strong>Hobi</strong></td><td>${cell(siswa.hobi, 'Belum diisi')}</td></tr>
                    <tr><td class="bg-light"><strong>Cita-cita</strong></td><td>${cell(siswa.cita_cita, 'Belum diisi')}</td></tr>
                </table>
            </div>
            <div class="col-md-6">
                <h6 class="text-primary"><i class="fas fa-map-marker-alt"></i> Alamat Siswa</h6>
                ${buildAlamatSiswa(siswa)}
            </div>
        </div>`;
    $('#santri-diri').html(html);
}

function buildAlamatSiswa(siswa) {
    const ortu = siswa.ortu;
    const bersamaOrtu = siswa.jenis_tempat_tinggal === 'Bersama Orang Tua' || siswa.alamat_sama_ortu;
    if (bersamaOrtu && ortu && ortu.alamat_ortu) {
        return `
            <div class="alert alert-info mb-2 py-2"><i class="fas fa-info-circle"></i> <strong>Tinggal bersama / alamat sama dengan orang tua</strong></div>
            <table class="table table-sm table-bordered">
                <tr><td width="40%" class="bg-light"><strong>Alamat</strong></td><td>${cell(ortu.alamat_ortu)}</td></tr>
                <tr><td class="bg-light"><strong>RT / RW</strong></td><td>${cell(ortu.rt_ortu)} / ${cell(ortu.rw_ortu)}</td></tr>
                <tr><td class="bg-light"><strong>Kelurahan/Desa</strong></td><td>${cell(ortu.kelurahan?.name)}</td></tr>
                <tr><td class="bg-light"><strong>Kecamatan</strong></td><td>${cell(ortu.kecamatan?.name)}</td></tr>
                <tr><td class="bg-light"><strong>Kab/Kota</strong></td><td>${cell(ortu.kabupaten?.name)}</td></tr>
                <tr><td class="bg-light"><strong>Provinsi</strong></td><td>${cell(ortu.provinsi?.name)}</td></tr>
            </table>`;
    }
    if (siswa.alamat_siswa) {
        return `
            <table class="table table-sm table-bordered">
                <tr><td width="40%" class="bg-light"><strong>Alamat</strong></td><td>${cell(siswa.alamat_siswa)}</td></tr>
                <tr><td class="bg-light"><strong>RT / RW</strong></td><td>${cell(siswa.rt_siswa)} / ${cell(siswa.rw_siswa)}</td></tr>
                <tr><td class="bg-light"><strong>Kodepos</strong></td><td>${cell(siswa.kodepos_siswa)}</td></tr>
            </table>`;
    }
    return '<div class="alert alert-info py-2"><i class="fas fa-info-circle"></i> Data alamat belum dilengkapi</div>';
}

function loadSantriOrtuTab(siswa) {
    const ortu = siswa.ortu;
    if (!ortu) {
        $('#santri-ortu').html('<div class="alert alert-warning py-2"><i class="fas fa-exclamation-triangle"></i> Data orang tua belum dilengkapi</div>');
        return;
    }
    const statusOrtu = (s) => s === 'masih_hidup'
        ? '<span class="badge badge-success">Masih Hidup</span>'
        : s === 'meninggal' ? '<span class="badge badge-secondary">Meninggal</span>' : '-';
    const html = `
        <div class="row">
            <div class="col-md-6">
                <h6 class="text-primary"><i class="fas fa-male"></i> Data Ayah</h6>
                <table class="table table-sm table-bordered">
                    <tr><td width="40%" class="bg-light"><strong>Status</strong></td><td>${statusOrtu(ortu.status_ayah)}</td></tr>
                    <tr><td class="bg-light"><strong>Nama</strong></td><td>${cell(ortu.nama_ayah)}</td></tr>
                    <tr><td class="bg-light"><strong>HP</strong></td><td>${cell(ortu.hp_ayah)}</td></tr>
                    <tr><td class="bg-light"><strong>Pekerjaan</strong></td><td>${cell(ortu.pekerjaan_ayah)}</td></tr>
                </table>
            </div>
            <div class="col-md-6">
                <h6 class="text-primary"><i class="fas fa-female"></i> Data Ibu</h6>
                <table class="table table-sm table-bordered">
                    <tr><td width="40%" class="bg-light"><strong>Status</strong></td><td>${statusOrtu(ortu.status_ibu)}</td></tr>
                    <tr><td class="bg-light"><strong>Nama</strong></td><td>${cell(ortu.nama_ibu)}</td></tr>
                    <tr><td class="bg-light"><strong>HP</strong></td><td>${cell(ortu.hp_ibu)}</td></tr>
                    <tr><td class="bg-light"><strong>Pekerjaan</strong></td><td>${cell(ortu.pekerjaan_ibu)}</td></tr>
                </table>
            </div>
        </div>
        <div class="row mt-2">
            <div class="col-md-12">
                <h6 class="text-primary"><i class="fas fa-home"></i> Alamat Orang Tua</h6>
                <table class="table table-sm table-bordered">
                    <tr><td width="20%" class="bg-light"><strong>Alamat</strong></td><td>${cell(ortu.alamat_ortu)}</td></tr>
                    <tr><td class="bg-light"><strong>RT / RW</strong></td><td>${cell(ortu.rt_ortu)} / ${cell(ortu.rw_ortu)}</td></tr>
                    <tr><td class="bg-light"><strong>Kelurahan/Desa</strong></td><td>${cell(ortu.kelurahan?.name)}</td></tr>
                    <tr><td class="bg-light"><strong>Kecamatan</strong></td><td>${cell(ortu.kecamatan?.name)}</td></tr>
                    <tr><td class="bg-light"><strong>Kab/Kota</strong></td><td>${cell(ortu.kabupaten?.name)}</td></tr>
                    <tr><td class="bg-light"><strong>Provinsi</strong></td><td>${cell(ortu.provinsi?.name)}</td></tr>
                </table>
            </div>
        </div>`;
    $('#santri-ortu').html(html);
}
</script>
@endsection
