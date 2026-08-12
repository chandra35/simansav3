@extends('adminlte::page')
@section('title','Edit Catatan Konseling')
@section('plugins.Select2',true)
@section('content_header')<div class="row mb-2"><div class="col-sm-6"><h1><i class="fas fa-edit text-warning"></i> Edit Catatan Konseling</h1></div><div class="col-sm-6"><ol class="breadcrumb float-sm-right"><li class="breadcrumb-item"><a href="{{ route('admin.catatan-konseling.index') }}">Konseling</a></li><li class="breadcrumb-item active">Edit</li></ol></div></div>@stop
@section('content')<div class="counseling-form"><div class="card card-outline card-primary"><form method="POST" action="{{ route('admin.catatan-konseling.update',$catatanKonseling) }}">@csrf @method('PUT')<div class="card-body">@include('admin.catatan-konseling._form')</div><div class="card-footer"><a href="{{ route('admin.catatan-konseling.show',$catatanKonseling) }}" class="btn btn-secondary"><i class="fas fa-times"></i> Batal</a><button class="btn btn-primary float-right"><i class="fas fa-save"></i> Simpan Perubahan</button></div></form></div></div>@stop
@include('admin.catatan-konseling._form-assets')
