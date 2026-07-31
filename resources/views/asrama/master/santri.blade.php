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
<div class="asrama-panel"><div class="asrama-panel__body asrama-form"><form method="get" class="row align-items-end" data-asrama-loading data-loading-title="Memuat data santri">
    <div class="col-md-5"><label>Tahun Pelajaran Rombel</label><select name="tahun_pelajaran_id" class="form-control asrama-select">@foreach($years as $year)<option value="{{ $year->id }}" @selected($selectedYear===$year->id)>{{ $year->nama }}{{ $year->is_active?' · Aktif':'' }}</option>@endforeach</select></div>
    <div class="col-md-2 mt-3 mt-md-0"><button class="btn btn-info btn-block"><i class="fas fa-filter mr-1"></i> Terapkan</button></div>
</form></div></div>
<div class="asrama-panel"><div class="table-responsive"><table class="table asrama-table"><thead><tr><th>No. Induk</th><th>Santri</th><th>Rombel SIMANSA</th><th>Pengasuh</th><th>Kamar</th><th>Status</th><th></th></tr></thead><tbody>
@forelse($records as $item)
<tr><td><code>{{ $item->nomor_induk_asrama }}</code></td><td><strong>{{ $item->siswa->nama_lengkap }}</strong><br><small>NISN {{ $item->siswa->nisn ?: '-' }}</small></td><td>{{ $item->kelasAktif?->kelas?->kelasReguler?->nama_kelas ?? $item->siswa->kelasTahunAktif->first()?->nama_kelas ?? '-' }}</td><td>{{ $item->kelasAktif?->pengasuhAssignment?->rombelPengasuh?->pengasuh?->gtk?->nama_lengkap ?? 'Belum dibagi' }}</td><td>{{ $item->kamarAktif?->kamar?->nama ?? 'Belum ditempatkan' }}</td><td><span class="asrama-badge {{ $item->status==='aktif'?'asrama-badge--active':'asrama-badge--muted' }}">{{ ucfirst($item->status) }}</span></td><td><button class="btn btn-sm btn-outline-primary asrama-icon-button" data-toggle="modal" data-target="#editSantri{{ $item->id }}" title="Edit santri"><i class="fas fa-pen"></i></button></td></tr>
@empty<tr><td colspan="7" class="asrama-empty"><i class="fas fa-user-graduate"></i>Belum ada santri aktif.</td></tr>@endforelse
</tbody></table></div><div class="p-3">{{ $records->links() }}</div></div>

<div class="modal fade" id="assignSantri" tabindex="-1"><div class="modal-dialog modal-xl modal-dialog-centered"><form method="post" action="{{ route('asrama.santri.store') }}" class="modal-content" data-asrama-loading data-loading-title="Mengaktifkan santri" data-loading-text="Identitas dan akses portal sedang disinkronkan dari SIMANSA.">@csrf
<div class="modal-header"><h5 class="modal-title"><i class="fas fa-user-plus mr-2"></i>Tambah Santri Asrama</h5><button type="button" class="close" data-dismiss="modal">&times;</button></div>
<div class="modal-body">
    <div class="alert alert-light border d-flex align-items-center small mb-3"><i class="fas fa-info-circle text-primary mr-2"></i><div>Gunakan rombel, pilihan individual, atau keduanya sekaligus. Semua siswa aktif pada rombel terpilih akan diaktifkan sebagai santri.</div></div>
    <div class="row">
        <div class="col-lg-6">
            <div class="form-group">
                <label for="selectKelas" class="font-weight-bold"><i class="fas fa-users text-primary mr-1"></i> Ambil rombel SIMANSA</label>
                <select id="selectKelas" multiple name="kelas_ids[]" class="form-control" style="width:100%">@foreach($classes->groupBy('tingkat') as $tingkat => $group)<optgroup label="Tingkat {{ $tingkat ?: '-' }}">@foreach($group as $class)<option value="{{ $class->id }}" data-count="{{ $class->siswa_aktif_count }}" data-name="{{ $class->nama_kelas }}">{{ $class->nama_kelas }} — {{ $class->siswa_aktif_count }} siswa</option>@endforeach</optgroup>@endforeach</select>
                <small class="form-text text-muted"><i class="fas fa-search mr-1"></i>Bisa memilih beberapa rombel sekaligus.</small>
            </div>
            <div class="table-responsive" id="kelasPreviewWrap" style="display:none"><table class="table table-sm table-bordered mb-0"><thead class="thead-light"><tr><th>Rombel</th><th class="text-center" style="width:90px">Jumlah</th><th style="width:40px"></th></tr></thead><tbody id="kelasPreviewBody"></tbody><tfoot><tr class="bg-light"><th>Total siswa dari rombel</th><th class="text-center" id="kelasPreviewTotal">0</th><th></th></tr></tfoot></table></div>
        </div>
        <div class="col-lg-6 mt-3 mt-lg-0">
            <div class="form-group">
                <label for="selectSiswa" class="font-weight-bold"><i class="fas fa-user-check text-primary mr-1"></i> Pilih siswa individual</label>
                <select id="selectSiswa" multiple name="siswa_ids[]" class="form-control" style="width:100%">@foreach($students as $student)<option value="{{ $student->id }}" data-nis="{{ $student->nis_lokal ?: $student->nisn }}" data-name="{{ $student->nama_lengkap }}">{{ $student->nama_lengkap }} — {{ $student->nis_lokal ?: $student->nisn ?: '-' }}</option>@endforeach</select>
                <small class="form-text text-muted"><i class="fas fa-search mr-1"></i>Cari berdasarkan nama atau NIS. Bisa memilih banyak siswa.</small>
            </div>
            <div class="table-responsive" id="siswaPreviewWrap" style="display:none"><table class="table table-sm table-bordered mb-0"><thead class="thead-light"><tr><th>Nama</th><th style="width:120px">NIS</th><th style="width:40px"></th></tr></thead><tbody id="siswaPreviewBody"></tbody><tfoot><tr class="bg-light"><th>Total siswa dipilih</th><th id="siswaPreviewTotal" colspan="2">0</th></tr></tfoot></table></div>
        </div>
        <div class="col-md-6 mt-3"><div class="form-group mb-0"><label class="font-weight-bold">Tanggal masuk</label><input type="date" name="tanggal_masuk" class="form-control" value="{{ now()->toDateString() }}"></div></div>
    </div>
</div><div class="modal-footer"><button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button><button type="submit" class="btn btn-primary"><i class="fas fa-check mr-1"></i> Aktifkan Santri</button></div>
</form></div></div>
@foreach($records as $item)
<div class="modal fade asrama-modal" id="editSantri{{ $item->id }}"><div class="modal-dialog modal-lg modal-dialog-centered"><form method="post" action="{{ route('asrama.santri.update',$item) }}" class="modal-content asrama-form" data-asrama-loading data-loading-title="Menyimpan data santri">@csrf @method('PUT')
<div class="modal-header"><div><h5 class="modal-title">{{ $item->siswa->nama_lengkap }}</h5><small class="text-white-50">Perbarui nomor identitas dan status tinggal.</small></div><button type="button" class="close" data-dismiss="modal">&times;</button></div><div class="modal-body"><div class="row">
<div class="col-md-6"><label>Nomor induk</label><input required name="nomor_induk_asrama" class="form-control" value="{{ $item->nomor_induk_asrama }}"></div><div class="col-md-6"><label>Status</label><select name="status" class="form-control asrama-select">@foreach(['aktif'=>'Aktif','nonaktif'=>'Nonaktif'] as $key=>$label)<option value="{{ $key }}" @selected($item->status===$key)>{{ $label }}</option>@endforeach</select></div>
<div class="col-md-6 mt-3"><label>Tanggal masuk</label><input type="date" name="tanggal_masuk" class="form-control" value="{{ $item->tanggal_masuk?->toDateString() }}"></div><div class="col-md-6 mt-3"><label>Tanggal keluar</label><input type="date" name="tanggal_keluar" class="form-control" value="{{ $item->tanggal_keluar?->toDateString() }}"></div>
<div class="col-12 mt-3"><label>Catatan</label><textarea name="catatan" class="form-control" rows="3">{{ $item->catatan }}</textarea></div>
</div></div><div class="modal-footer"><button type="button" class="btn btn-light" data-dismiss="modal">Batal</button><button class="btn btn-info"><i class="fas fa-save mr-1"></i> Simpan</button></div></form></div></div>
@endforeach
@include('asrama._scripts')
@stop
@section('css') @include('asrama._styles') @endsection
@section('js')
<script>
$(function () {
    var $modal = $('#assignSantri');
    var initialized = false;

    $modal.on('shown.bs.modal', function () {
        if (initialized || !$.fn.select2) return;
        initialized = true;

        $('#selectKelas').select2({
            theme: 'bootstrap4',
            width: '100%',
            placeholder: 'Pilih satu atau beberapa rombel',
            dropdownParent: $modal,
            closeOnSelect: false
        }).on('change', renderKelasPreview);

        $('#selectSiswa').select2({
            theme: 'bootstrap4',
            width: '100%',
            placeholder: 'Cari nama atau NIS siswa…',
            dropdownParent: $modal,
            closeOnSelect: false
        }).on('change', renderSiswaPreview);
    });

    function renderKelasPreview() {
        var $body = $('#kelasPreviewBody').empty();
        var total = 0;
        $('#selectKelas option:selected').each(function () {
            var $o = $(this), count = parseInt($o.data('count')) || 0;
            total += count;
            $body.append('<tr><td><strong>' + $o.data('name') + '</strong></td><td class="text-center">' + count + '</td>' +
                '<td class="text-right"><button type="button" class="btn btn-sm btn-link text-danger p-0" data-remove-kelas="' + $o.val() + '"><i class="fas fa-times"></i></button></td></tr>');
        });
        $('#kelasPreviewTotal').text(total);
        $('#kelasPreviewWrap').toggle($body.children().length > 0);
    }

    function renderSiswaPreview() {
        var $body = $('#siswaPreviewBody').empty();
        var count = 0;
        $('#selectSiswa option:selected').each(function () {
            var $o = $(this);
            count++;
            $body.append('<tr><td><strong>' + $o.data('name') + '</strong></td><td>' + ($o.data('nis') || '-') + '</td>' +
                '<td class="text-right"><button type="button" class="btn btn-sm btn-link text-danger p-0" data-remove-siswa="' + $o.val() + '"><i class="fas fa-times"></i></button></td></tr>');
        });
        $('#siswaPreviewTotal').text(count);
        $('#siswaPreviewWrap').toggle(count > 0);
    }

    $(document).on('click', '[data-remove-kelas]', function () {
        var val = String($(this).data('remove-kelas'));
        $('#selectKelas').val($('#selectKelas').val().filter(function (v) { return v !== val; })).trigger('change');
    });
    $(document).on('click', '[data-remove-siswa]', function () {
        var val = String($(this).data('remove-siswa'));
        $('#selectSiswa').val($('#selectSiswa').val().filter(function (v) { return v !== val; })).trigger('change');
    });
});
</script>
@endsection
