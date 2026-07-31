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
    <div class="callout callout-info py-2 mb-3"><small><i class="fas fa-info-circle mr-1"></i>Gunakan rombel, pilihan individual, atau keduanya sekaligus. Semua siswa aktif pada rombel terpilih akan diaktifkan sebagai santri.</small></div>
    <div class="row">
        <div class="col-lg-6">
            <div class="card card-outline card-primary mb-3">
                <div class="card-header py-2"><h3 class="card-title"><i class="fas fa-users mr-1"></i> Ambil rombel SIMANSA</h3><div class="card-tools"><span class="badge badge-primary" id="kelasCount">0 dipilih</span></div></div>
                <div class="card-body p-2">
                    <div class="input-group input-group-sm mb-2"><div class="input-group-prepend"><span class="input-group-text"><i class="fas fa-search"></i></span></div><input type="text" id="kelasSearch" class="form-control" placeholder="Cari rombel..."></div>
                    <div style="max-height:260px;overflow-y:auto" id="kelasList">
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
});
</script>
@endsection
