@extends('adminlte::page')
@section('title', 'Nomor Induk Santri')
@section('plugins.Select2', true)
@section('content_header') @stop
@section('content')
@include('asrama._alerts')
@php
    $heroTitle='Nomor Induk Santri';
    $heroDescription='Kelola nomor induk santri: perbarui satu per satu atau unggah massal melalui berkas Excel.';
    $heroAction='<button class="btn btn-light" data-toggle="modal" data-target="#importInduk"><i class="fas fa-file-upload mr-1"></i> Unggah Excel</button>';
@endphp
@include('asrama._hero')

@if($result = session('nomor_induk_import'))
<div class="asrama-panel"><div class="asrama-panel__body">
    <div class="d-flex flex-wrap" style="gap:1rem">
        <div class="asrama-pill"><i class="fas fa-check-circle text-success"></i> {{ $result['success'] }} berhasil</div>
        <div class="asrama-pill"><i class="fas fa-times-circle text-danger"></i> {{ $result['failed'] }} gagal</div>
    </div>
    @if(!empty($result['errors']))
    <div class="table-responsive mt-3"><table class="table table-sm asrama-table mb-0"><thead><tr><th>Baris</th><th>NISN</th><th>Nama</th><th>Keterangan</th></tr></thead><tbody>
        @foreach($result['errors'] as $err)
        <tr><td>{{ $err['row'] }}</td><td><code>{{ $err['nisn'] }}</code></td><td>{{ $err['nama'] }}</td><td class="text-danger">{{ $err['error'] }}</td></tr>
        @endforeach
    </tbody></table></div>
    @endif
</div></div>
@endif

<div class="asrama-panel"><div class="asrama-panel__body asrama-form"><form method="get" class="row align-items-end">
    <div class="col-md-6"><label>Cari santri</label><input name="q" value="{{ $search }}" class="form-control" placeholder="Nama, NISN, NIS lokal, atau nomor induk"></div>
    <div class="col-md-3 mt-3 mt-md-0"><button class="btn btn-info btn-block"><i class="fas fa-search mr-1"></i> Cari</button></div>
    <div class="col-md-3 mt-3 mt-md-0"><a href="{{ route('asrama.santri.induk.template') }}" class="btn btn-outline-secondary btn-block"><i class="fas fa-download mr-1"></i> Template Excel</a></div>
</form></div></div>

<div class="asrama-panel"><div class="table-responsive"><table class="table asrama-table"><thead><tr><th>NISN</th><th>Santri</th><th>NIS Lokal</th><th>Status</th><th style="width:320px">Nomor Induk Santri</th></tr></thead><tbody>
@forelse($records as $item)
<tr>
    <td><code>{{ $item->siswa->nisn ?: '-' }}</code></td>
    <td><strong>{{ $item->siswa->nama_lengkap }}</strong></td>
    <td>{{ $item->siswa->nis_lokal ?: '-' }}</td>
    <td><span class="asrama-badge {{ $item->status==='aktif'?'asrama-badge--active':'asrama-badge--muted' }}">{{ ucfirst($item->status) }}</span></td>
    <td><form method="post" action="{{ route('asrama.santri.induk.update',$item) }}" class="form-inline">@csrf @method('PUT')
        <div class="input-group input-group-sm w-100">
            <input required name="nomor_induk_asrama" class="form-control" value="{{ $item->nomor_induk_asrama }}">
            <div class="input-group-append"><button class="btn btn-info" title="Simpan"><i class="fas fa-save"></i></button></div>
        </div>
    </form></td>
</tr>
@empty<tr><td colspan="5" class="asrama-empty"><i class="fas fa-id-card"></i>Belum ada santri. Aktifkan santri terlebih dahulu di menu Santri Asrama.</td></tr>@endforelse
</tbody></table></div><div class="p-3">{{ $records->links() }}</div></div>

<div class="modal fade asrama-modal" id="importInduk"><div class="modal-dialog modal-dialog-centered"><form method="post" action="{{ route('asrama.santri.induk.import') }}" enctype="multipart/form-data" class="modal-content asrama-form" data-asrama-loading data-loading-title="Mengunggah nomor induk" data-loading-text="Nomor induk sedang disesuaikan dengan data santri.">@csrf
<div class="modal-header"><div><h5 class="modal-title"><i class="fas fa-file-upload mr-2"></i>Unggah Nomor Induk</h5><small class="text-white-50">Cocokkan berdasarkan NISN santri yang sudah aktif.</small></div><button type="button" class="close" data-dismiss="modal">&times;</button></div>
<div class="modal-body">
    <div class="asrama-help mb-3"><i class="fas fa-info-circle mr-1"></i> Format kolom: <strong>nisn</strong>, <strong>nama</strong>, <strong>nomor_induk</strong>. Baris pertama adalah judul kolom. Unduh <a href="{{ route('asrama.santri.induk.template') }}">template Excel</a> agar sesuai.</div>
    <label>Berkas Excel (.xlsx / .xls)</label>
    <input type="file" name="file" accept=".xlsx,.xls" required class="form-control-file">
    @error('file')<small class="text-danger d-block mt-1">{{ $message }}</small>@enderror
</div>
<div class="modal-footer"><button type="button" class="btn btn-light" data-dismiss="modal">Batal</button><button class="btn btn-info"><i class="fas fa-upload mr-1"></i> Proses Unggah</button></div>
</form></div></div>

@include('asrama._scripts')
@stop
@section('css') @include('asrama._styles') @endsection
@section('js')
<script>
$(function () {
    @if(session('nomor_induk_import') || $errors->has('file'))
    $('#importInduk').modal('show');
    @endif
});
</script>
@endsection
