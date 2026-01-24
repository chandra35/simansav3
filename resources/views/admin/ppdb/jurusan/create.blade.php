@extends('adminlte::page')

@section('title', 'Tambah Jurusan PPDB')

@section('content_header')
    <h1><i class="fas fa-graduation-cap mr-2"></i>Tambah Jurusan PPDB</h1>
@stop

@section('content')
<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <form action="{{ route('admin.settings.jurusan.store') }}" method="POST">
                @csrf
                <div class="card-body">
                    @if($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="kode">Kode Jurusan <span class="text-danger">*</span></label>
                                <input type="text" name="kode" id="kode" class="form-control" 
                                       value="{{ old('kode') }}" required maxlength="20"
                                       placeholder="Contoh: RPL, TKJ, MM">
                            </div>
                        </div>
                        <div class="col-md-8">
                            <div class="form-group">
                                <label for="nama">Nama Jurusan <span class="text-danger">*</span></label>
                                <input type="text" name="nama" id="nama" class="form-control" 
                                       value="{{ old('nama') }}" required
                                       placeholder="Contoh: Rekayasa Perangkat Lunak">
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="deskripsi">Deskripsi</label>
                        <textarea name="deskripsi" id="deskripsi" class="form-control" rows="3"
                                  placeholder="Deskripsi singkat tentang jurusan">{{ old('deskripsi') }}</textarea>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="kuota">Kuota <span class="text-danger">*</span></label>
                                <input type="number" name="kuota" id="kuota" class="form-control" 
                                       value="{{ old('kuota', 36) }}" required min="0">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="urutan">Urutan Tampil</label>
                                <input type="number" name="urutan" id="urutan" class="form-control" 
                                       value="{{ old('urutan', 0) }}" min="0">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <a href="{{ route('admin.settings.jurusan.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Kembali
                    </a>
                    <button type="submit" class="btn btn-primary float-right">
                        <i class="fas fa-save"></i> Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@stop
