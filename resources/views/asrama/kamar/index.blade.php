@extends('adminlte::page')
@section('title', 'Kamar Asrama')
@section('plugins.Select2', true)
@section('content_header') @stop
@section('content')
@include('asrama._alerts')
@php
    $heroTitle='Kamar Asrama';
    $heroDescription='Kelola kamar gedung putra/putri, kapasitas, pengasuh kamar, dan penempatan santri.';
    $heroAction='<button class="btn btn-light" data-toggle="modal" data-target="#createRoom"><i class="fas fa-plus mr-1"></i> Tambah Kamar</button>';
@endphp
@include('asrama._hero')
<div class="asrama-panel"><div class="asrama-panel__body asrama-form"><form method="get" class="row align-items-end" data-asrama-loading data-loading-title="Memuat daftar kamar">
<div class="col-md-4"><label>Gedung</label><select name="gedung" class="form-control asrama-select" data-placeholder="Semua gedung" data-allow-clear="1"><option value=""></option><option value="putra" @selected($selectedBuilding==='putra')>Gedung Putra</option><option value="putri" @selected($selectedBuilding==='putri')>Gedung Putri</option></select></div><div class="col-md-2 mt-3 mt-md-0"><button class="btn btn-info btn-block"><i class="fas fa-filter mr-1"></i> Terapkan</button></div>
</form></div></div>
<div class="row">
@forelse($rooms as $room)
@php($percent=$room->kapasitas ? min(100,round($room->penghuni_aktif_count/$room->kapasitas*100)) : 0)
<div class="col-xl-4 col-md-6 mb-3"><div class="asrama-room asrama-room--{{ $room->gedung }}">
<div class="d-flex justify-content-between align-items-start"><div><span class="asrama-pill mb-2"><i class="fas fa-building"></i> {{ ucfirst($room->gedung) }}{{ $room->lantai?' · Lantai '.$room->lantai:'' }}</span><h4 class="mb-0">{{ $room->nama }}</h4><small class="text-muted">{{ $room->kode }}</small></div><span class="asrama-badge {{ $room->is_active?'asrama-badge--active':'asrama-badge--muted' }}">{{ $room->is_active?'Aktif':'Nonaktif' }}</span></div>
<hr><div class="d-flex justify-content-between"><small>Penghuni</small><strong>{{ $room->penghuni_aktif_count }}/{{ $room->kapasitas }}</strong></div><div class="asrama-room__capacity mt-2"><span style="width:{{ $percent }}%"></span></div>
<div class="mt-3"><small class="text-muted">Pengasuh Kamar</small><div class="font-weight-bold">{{ $room->pengasuh?->gtk?->nama_lengkap ?? 'Belum ditetapkan' }}</div></div>
<div class="mt-3 d-flex" style="gap:.5rem"><button class="btn btn-info flex-fill" data-toggle="modal" data-target="#occupants{{ $room->id }}"><i class="fas fa-users mr-1"></i> Penghuni</button><button class="btn btn-outline-secondary" data-toggle="modal" data-target="#editRoom{{ $room->id }}"><i class="fas fa-cog"></i></button></div>
</div></div>
@empty<div class="col-12"><div class="asrama-panel"><div class="asrama-empty"><i class="fas fa-bed"></i>Belum ada kamar Asrama.</div></div></div>@endforelse
</div>

<div class="modal fade asrama-modal" id="createRoom"><div class="modal-dialog modal-lg modal-dialog-centered"><form method="post" action="{{ route('asrama.kamar.store') }}" class="modal-content asrama-form" data-asrama-loading data-loading-title="Membuat kamar">@csrf
<div class="modal-header"><div><h5 class="modal-title"><i class="fas fa-bed mr-2"></i>Tambah Kamar</h5><small class="text-white-50">Kamar dipisahkan berdasarkan gedung putra dan putri.</small></div><button type="button" class="close" data-dismiss="modal">&times;</button></div><div class="modal-body">@include('asrama.kamar._form',['room'=>null])</div><div class="modal-footer"><button type="button" class="btn btn-light" data-dismiss="modal">Batal</button><button class="btn btn-info"><i class="fas fa-save mr-1"></i> Simpan Kamar</button></div></form></div></div>

@foreach($rooms as $room)
<div class="modal fade asrama-modal" id="editRoom{{ $room->id }}"><div class="modal-dialog modal-lg modal-dialog-centered"><form method="post" action="{{ route('asrama.kamar.update',$room) }}" class="modal-content asrama-form" data-asrama-loading data-loading-title="Memperbarui kamar">@csrf @method('PUT')
<div class="modal-header"><div><h5 class="modal-title">Edit {{ $room->nama }}</h5><small class="text-white-50">{{ $room->kode }}</small></div><button type="button" class="close" data-dismiss="modal">&times;</button></div><div class="modal-body">@include('asrama.kamar._form',['room'=>$room])</div><div class="modal-footer"><button type="button" class="btn btn-light" data-dismiss="modal">Batal</button><button class="btn btn-info"><i class="fas fa-save mr-1"></i> Simpan</button></div></form></div></div>

<div class="modal fade asrama-modal" id="occupants{{ $room->id }}"><div class="modal-dialog modal-xl modal-dialog-centered"><div class="modal-content">
<div class="modal-header"><div><h5 class="modal-title"><i class="fas fa-users mr-2"></i>Penghuni {{ $room->nama }}</h5><small class="text-white-50">{{ $room->penghuni_aktif_count }} dari {{ $room->kapasitas }} tempat terisi.</small></div><button type="button" class="close" data-dismiss="modal">&times;</button></div><div class="modal-body">
<form method="post" action="{{ route('asrama.kamar.santri.store',$room) }}" class="asrama-form mb-4" data-asrama-loading data-loading-title="Menempatkan santri" data-loading-text="Riwayat kamar sebelumnya akan ditutup otomatis.">@csrf<div class="row align-items-end"><div class="col-md-8"><label>Santri</label><select required multiple name="santri_ids[]" class="form-control asrama-select" data-placeholder="Cari santri yang akan ditempatkan">@foreach($availableSantri as $santri)<option value="{{ $santri->id }}">{{ $santri->siswa->nama_lengkap }} · {{ $santri->siswa->kelasTahunAktif->first()?->nama_kelas ?? '-' }}{{ $santri->kamarAktif?' · saat ini '.$santri->kamarAktif->kamar?->nama:'' }}</option>@endforeach</select></div><div class="col-md-2 mt-3 mt-md-0"><label>Tanggal masuk</label><input type="date" name="tanggal_masuk" value="{{ now()->toDateString() }}" class="form-control"></div><div class="col-md-2 mt-3 mt-md-0"><button class="btn btn-info btn-block"><i class="fas fa-sign-in-alt mr-1"></i> Tempatkan</button></div></div></form>
<div class="table-responsive"><table class="table asrama-table"><thead><tr><th>Santri</th><th>Nomor Induk</th><th>Masuk</th><th></th></tr></thead><tbody>@forelse($room->penghuniAktif->sortBy('santri.siswa.nama_lengkap') as $occupant)<tr><td><strong>{{ $occupant->santri->siswa->nama_lengkap }}</strong></td><td><code>{{ $occupant->santri->nomor_induk_asrama }}</code></td><td>{{ $occupant->tanggal_masuk?->format('d/m/Y') ?? '-' }}</td><td class="text-right"><form method="post" action="{{ route('asrama.kamar.santri.destroy',[$room,$occupant]) }}" data-asrama-loading data-confirm="Keluarkan santri ini dari {{ $room->nama }}?" data-loading-title="Mengeluarkan santri">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger"><i class="fas fa-times mr-1"></i> Keluar</button></form></td></tr>@empty<tr><td colspan="4" class="asrama-empty"><i class="fas fa-bed"></i>Kamar belum memiliki penghuni.</td></tr>@endforelse</tbody></table></div>
</div></div></div></div>
@endforeach
@include('asrama._scripts')
@stop
@section('css') @include('asrama._styles') @stop
