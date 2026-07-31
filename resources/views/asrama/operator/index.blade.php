@extends('adminlte::page')
@section('title', 'Operator Asrama')
@section('plugins.Select2', true)
@section('content_header') @stop
@section('content')
@include('asrama._alerts')
@php
    $heroTitle='Operator Asrama';
    $heroDescription='Tentukan GTK yang menjadi administrator khusus modul Asrama. Operator tidak memperoleh akses admin SIMANSA di luar kewenangan Asrama.';
    $heroAction='<button class="btn btn-light" data-toggle="modal" data-target="#addOperator"><i class="fas fa-user-shield mr-1"></i> Tetapkan Operator</button>';
@endphp
@include('asrama._hero')
<div class="asrama-panel">
    <div class="asrama-panel__header"><div><h3>Operator Aktif</h3><p>Role dapat diberikan kepada lebih dari satu GTK untuk kebutuhan pergantian tugas.</p></div><span class="asrama-pill"><i class="fas fa-users-cog"></i> {{ $operators->count() }} operator</span></div>
    <div class="table-responsive"><table class="table asrama-table"><thead><tr><th>GTK</th><th>Akun</th><th>Email</th><th>Status</th><th></th></tr></thead><tbody>
    @forelse($operators as $operator)
        <tr><td><strong>{{ $operator->gtk?->nama_lengkap ?? $operator->name }}</strong><br><small>{{ $operator->gtk?->nip ?: $operator->gtk?->nuptk }}</small></td><td>{{ $operator->username }}</td><td>{{ $operator->email ?: '-' }}</td><td><span class="asrama-badge asrama-badge--active"><i class="fas fa-check-circle mr-1"></i> Operator Asrama</span></td><td class="text-right"><form method="post" action="{{ route('asrama.operator.destroy',$operator) }}" data-asrama-loading data-confirm="Cabut tugas Operator Asrama dari {{ $operator->name }}?" data-loading-title="Mencabut akses operator">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger asrama-icon-button" title="Cabut tugas"><i class="fas fa-user-minus"></i></button></form></td></tr>
    @empty
        <tr><td colspan="5" class="asrama-empty"><i class="fas fa-user-shield"></i>Belum ada Operator Asrama. Tetapkan operator sebelum pengelolaan dimulai.</td></tr>
    @endforelse
    </tbody></table></div>
</div>
<div class="modal fade asrama-modal" id="addOperator" tabindex="-1"><div class="modal-dialog modal-lg modal-dialog-centered"><form method="post" action="{{ route('asrama.operator.store') }}" class="modal-content asrama-form" data-asrama-loading data-loading-title="Menetapkan Operator Asrama" data-loading-text="Role dan izin akses sedang disiapkan.">@csrf
    <div class="modal-header"><div><h5 class="modal-title"><i class="fas fa-user-shield mr-2"></i>Tetapkan Operator Asrama</h5><small class="text-white-50">Pilih GTK yang sudah memiliki akun SIMANSA aktif.</small></div><button type="button" class="close" data-dismiss="modal">&times;</button></div>
    <div class="modal-body"><div class="asrama-help mb-3"><i class="fas fa-info-circle mr-1"></i> Operator dapat mengelola santri, pengasuh, rombel, kamar, mapel, nilai, dan rapor Asrama.</div><div class="form-group mb-0"><label>GTK</label><select required name="gtk_id" class="form-control asrama-select" data-placeholder="Cari nama, NIP, atau akun"><option value=""></option>@foreach($gtks as $gtk)<option value="{{ $gtk->id }}">{{ $gtk->nama_lengkap }} · {{ $gtk->nip ?: ($gtk->user?->username ?? 'tanpa akun') }}</option>@endforeach</select></div></div>
    <div class="modal-footer"><button type="button" class="btn btn-light" data-dismiss="modal">Batal</button><button class="btn btn-info" type="submit"><i class="fas fa-check mr-1"></i> Tetapkan Operator</button></div>
</form></div></div>
@include('asrama._scripts')
@stop
@section('css') @include('asrama._styles') @stop
