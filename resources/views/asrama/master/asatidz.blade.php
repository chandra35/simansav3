@extends('adminlte::page')
@section('title', 'Pengasuh & Pengajar Asrama')
@section('plugins.Select2', true)
@section('content_header') @stop
@section('content')
@include('asrama._alerts')
@php
    $heroTitle='Pengasuh & Pengajar';
    $heroDescription='Ambil GTK dari SIMANSA lalu tentukan tugasnya sebagai pengasuh rombel, pengasuh kamar, dan/atau pengampu mata pelajaran.';
    $heroAction='<button class="btn btn-light" data-toggle="modal" data-target="#assignAsatidz"><i class="fas fa-user-plus mr-1"></i> Tambah GTK Asrama</button>';
@endphp
@include('asrama._hero')
<div class="row">
    <div class="col-md-6 col-xl-3 mb-3">
        <div class="card card-outline card-primary h-100"><div class="card-body py-3">
            <div class="text-muted small text-uppercase font-weight-bold">Total GTK Asrama</div>
            <h3 class="text-primary mb-0">{{ number_format($stats['total']) }}</h3>
        </div></div>
    </div>
    <div class="col-md-6 col-xl-3 mb-3">
        <div class="card card-outline card-success h-100"><div class="card-body py-3">
            <div class="text-muted small text-uppercase font-weight-bold">Penugasan Aktif</div>
            <h3 class="text-success mb-0">{{ number_format($stats['aktif']) }}</h3>
        </div></div>
    </div>
    <div class="col-md-6 col-xl-3 mb-3">
        <div class="card card-outline card-info h-100"><div class="card-body py-3">
            <div class="text-muted small text-uppercase font-weight-bold">Pengasuh Rombel/Kamar</div>
            <h3 class="text-info mb-0">{{ number_format($stats['pengasuh']) }}</h3>
        </div></div>
    </div>
    <div class="col-md-6 col-xl-3 mb-3">
        <div class="card card-outline card-warning h-100"><div class="card-body py-3">
            <div class="text-muted small text-uppercase font-weight-bold">Pengampu Mapel</div>
            <h3 class="text-warning mb-0">{{ number_format($stats['pengampu']) }}</h3>
        </div></div>
    </div>
</div>

<div class="card card-outline card-primary">
    <div class="card-header"><h3 class="card-title"><i class="fas fa-chalkboard-teacher mr-2"></i>Tim Pengasuh & Pengajar</h3></div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover table-sm">
                <thead class="thead-light"><tr>
                    <th style="width:52px">Foto</th><th>GTK</th><th>Jabatan</th><th>Kewenangan</th><th>Beban Aktif</th><th>Mulai Tugas</th><th>Status</th><th class="text-right" style="width:96px">Aksi</th>
                </tr></thead>
                <tbody>
                @forelse($records as $item)
                <tr>
                    <td><img src="{{ $item->gtk->foto_profile_url }}" alt="{{ $item->gtk->nama_lengkap }}" class="img-circle elevation-1" style="width:36px;height:36px;object-fit:cover"></td>
                    <td class="align-middle"><strong>{{ $item->gtk->nama_lengkap }}</strong><br><small class="text-muted">{{ $item->gtk->nip ? 'NIP '.$item->gtk->nip : ($item->gtk->nuptk ? 'NUPTK '.$item->gtk->nuptk : '-') }}</small></td>
                    <td class="align-middle">{{ $item->jabatan }}</td>
                    <td class="align-middle">
                        @if($item->dapat_mengasuh_rombel)<span class="badge badge-primary mr-1"><i class="fas fa-users mr-1"></i>Rombel</span>@endif
                        @if($item->dapat_mengasuh_kamar)<span class="badge badge-info mr-1"><i class="fas fa-bed mr-1"></i>Kamar</span>@endif
                        @if($item->dapat_mengampu_mapel)<span class="badge badge-success"><i class="fas fa-book-open mr-1"></i>Mapel</span>@endif
                        @if(! $item->dapat_mengasuh_rombel && ! $item->dapat_mengasuh_kamar && ! $item->dapat_mengampu_mapel)<span class="text-muted">-</span>@endif
                    </td>
                    <td class="align-middle">
                        <span class="badge badge-light border">{{ $item->rombel_diasuh_count }} rombel</span>
                        <span class="badge badge-light border">{{ $item->kamar_diasuh_count }} kamar</span>
                        <span class="badge badge-light border">{{ $item->pengampu_count }} mapel</span>
                    </td>
                    <td class="align-middle">{{ $item->tanggal_mulai?->format('d/m/Y') ?: '-' }}</td>
                    <td class="align-middle"><span class="badge badge-{{ $item->is_active ? 'success' : 'secondary' }}">{{ $item->is_active ? 'Aktif' : 'Nonaktif' }}</span></td>
                    <td class="align-middle text-right text-nowrap">
                        <button class="btn btn-sm btn-primary" title="Edit tugas" data-toggle="modal" data-target="#editAsatidz{{ $item->id }}"><i class="fas fa-pen"></i></button>
                        <form method="post" action="{{ route('asrama.asatidz.destroy', $item) }}" class="d-inline" data-asrama-loading data-confirm="Hapus {{ $item->gtk->nama_lengkap }} dari tim Asrama? Data GTK SIMANSA tidak akan berubah.">@csrf @method('DELETE')
                            <button class="btn btn-sm btn-danger" title="Hapus dari asrama"><i class="fas fa-trash"></i></button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="8" class="text-center text-muted py-4"><i class="fas fa-chalkboard-teacher mr-1"></i> Belum ada GTK yang ditugaskan di Asrama.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        <div class="d-flex flex-wrap justify-content-between align-items-center mt-2">
            <small class="text-muted">Menampilkan {{ $records->count() ? $records->firstItem().'–'.$records->lastItem() : 0 }} dari {{ $records->total() }} GTK</small>
            {{ $records->links() }}
        </div>
    </div>
</div>

<div class="modal fade" id="assignAsatidz" tabindex="-1"><div class="modal-dialog modal-lg modal-dialog-centered"><form method="post" action="{{ route('asrama.asatidz.store') }}" class="modal-content" data-asrama-loading data-loading-title="Menambahkan GTK Asrama" data-loading-text="Akses menu dan kewenangan sedang disinkronkan.">@csrf
<div class="modal-header"><h5 class="modal-title"><i class="fas fa-user-plus mr-2"></i>Tambah GTK Asrama</h5><button type="button" class="close" data-dismiss="modal">&times;</button></div>
<div class="modal-body">
    <div class="callout callout-info py-2 mb-3"><small><i class="fas fa-info-circle mr-1"></i>Satu GTK dapat memegang beberapa jenis tugas sekaligus. Tanggal mulai tugas diisi otomatis hari ini.</small></div>
    <div class="row">
        <div class="col-md-7 mb-3"><label class="small font-weight-bold text-muted">GTK SIMANSA</label><select required name="gtk_id" class="form-control asrama-select" data-placeholder="Cari nama atau NIP"><option value=""></option>@foreach($gtks as $gtk)<option value="{{ $gtk->id }}">{{ $gtk->nama_lengkap }}{{ $gtk->nip?' · '.$gtk->nip:'' }}</option>@endforeach</select></div>
        <div class="col-md-5 mb-3"><label class="small font-weight-bold text-muted">Jabatan</label><input required name="jabatan" value="Pengasuh/Pengajar Asrama" class="form-control"></div>
    </div>
    <label class="small font-weight-bold text-muted">Pilih kewenangan</label>
    <div class="row">
        @foreach([['dapat_mengasuh_rombel','fa-users','Pengasuh Rombel','Dapat ditugaskan pada satu atau beberapa rombel.',false],['dapat_mengasuh_kamar','fa-bed','Pengasuh Kamar','Dapat menangani satu atau beberapa kamar.',false],['dapat_mengampu_mapel','fa-book-open','Pengampu Mapel','Dapat diberi akses input nilai mapel.',true]] as [$name,$icon,$title,$desc,$checked])
        <div class="col-md-4 mb-2">
            <div class="card card-outline card-primary h-100 mb-0"><div class="card-body p-2">
                <div class="custom-control custom-checkbox">
                    <input type="checkbox" class="custom-control-input" id="assign_{{ $name }}" name="{{ $name }}" value="1" @checked($checked)>
                    <label class="custom-control-label font-weight-bold" for="assign_{{ $name }}"><i class="fas {{ $icon }} text-primary mr-1"></i>{{ $title }}</label>
                </div>
                <small class="text-muted d-block mt-1">{{ $desc }}</small>
            </div></div>
        </div>
        @endforeach
    </div>
</div>
<div class="modal-footer"><button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button><button class="btn btn-primary"><i class="fas fa-check mr-1"></i> Simpan Penugasan</button></div></form></div></div>

@foreach($records as $item)
<div class="modal fade" id="editAsatidz{{ $item->id }}" tabindex="-1"><div class="modal-dialog modal-dialog-centered"><form method="post" action="{{ route('asrama.asatidz.update',$item) }}" class="modal-content" data-asrama-loading data-loading-title="Memperbarui tugas GTK">@csrf @method('PUT')
<div class="modal-header"><h5 class="modal-title"><i class="fas fa-user-edit mr-2"></i>{{ $item->gtk->nama_lengkap }}</h5><button type="button" class="close" data-dismiss="modal">&times;</button></div>
<div class="modal-body">
    <div class="form-group"><label class="small font-weight-bold text-muted">Jabatan</label><input required name="jabatan" class="form-control" value="{{ $item->jabatan }}"></div>
    <label class="small font-weight-bold text-muted">Kewenangan</label>
    @foreach([['dapat_mengasuh_rombel','fa-users','Pengasuh Rombel',$item->dapat_mengasuh_rombel],['dapat_mengasuh_kamar','fa-bed','Pengasuh Kamar',$item->dapat_mengasuh_kamar],['dapat_mengampu_mapel','fa-book-open','Pengampu Mapel',$item->dapat_mengampu_mapel]] as [$name,$icon,$title,$checked])
    <div class="custom-control custom-checkbox mb-1">
        <input type="checkbox" class="custom-control-input" id="edit_{{ $name }}_{{ $item->id }}" name="{{ $name }}" value="1" @checked($checked)>
        <label class="custom-control-label" for="edit_{{ $name }}_{{ $item->id }}"><i class="fas {{ $icon }} text-primary mr-1"></i>{{ $title }}</label>
    </div>
    @endforeach
    <hr class="my-3">
    <div class="custom-control custom-switch"><input id="asatidzActive{{ $item->id }}" type="checkbox" name="is_active" value="1" class="custom-control-input" @checked($item->is_active)><label for="asatidzActive{{ $item->id }}" class="custom-control-label">Penugasan aktif</label></div>
    <small class="text-muted d-block mt-1">Jika dinonaktifkan, tanggal selesai diisi otomatis hari ini.</small>
</div>
<div class="modal-footer"><button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button><button class="btn btn-primary"><i class="fas fa-save mr-1"></i> Simpan</button></div></form></div></div>
@endforeach
@include('asrama._scripts')
@stop
@section('css') @include('asrama._styles') @stop
