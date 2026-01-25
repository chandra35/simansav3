@extends('adminlte::page')

@section('title', 'Tambah Pengumuman')

@section('content_header')
    <h1><i class="fas fa-plus mr-2"></i>Tambah Pengumuman</h1>
@stop

@section('content')
    <div class="card">
        <form action="{{ route('admin.pengumuman.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="card-body">
                <div class="row">
                    <div class="col-md-8">
                        <div class="form-group">
                            <label for="judul">Judul <span class="text-danger">*</span></label>
                            <input type="text" name="judul" id="judul" class="form-control @error('judul') is-invalid @enderror" 
                                value="{{ old('judul') }}" required>
                            @error('judul')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                        
                        <div class="form-group">
                            <label for="isi">Isi Pengumuman <span class="text-danger">*</span></label>
                            <textarea name="isi" id="isi" class="form-control @error('isi') is-invalid @enderror" 
                                rows="10" required>{{ old('isi') }}</textarea>
                            @error('isi')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="kategori">Kategori <span class="text-danger">*</span></label>
                            <select name="kategori" id="kategori" class="form-control @error('kategori') is-invalid @enderror" required>
                                @foreach($kategori as $key => $label)
                                    <option value="{{ $key }}" {{ old('kategori') == $key ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('kategori')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                        
                        <div class="form-group">
                            <label for="prioritas">Prioritas <span class="text-danger">*</span></label>
                            <select name="prioritas" id="prioritas" class="form-control @error('prioritas') is-invalid @enderror" required>
                                @foreach($prioritas as $key => $label)
                                    <option value="{{ $key }}" {{ old('prioritas') == $key ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('prioritas')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                        
                        <div class="form-group">
                            <label for="target">Target <span class="text-danger">*</span></label>
                            <select name="target" id="target" class="form-control @error('target') is-invalid @enderror" required>
                                @foreach($target as $key => $label)
                                    <option value="{{ $key }}" {{ old('target') == $key ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('target')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                        
                        <div class="form-group">
                            <label for="tanggal_mulai">Tanggal Mulai <span class="text-danger">*</span></label>
                            <input type="date" name="tanggal_mulai" id="tanggal_mulai" 
                                class="form-control @error('tanggal_mulai') is-invalid @enderror" 
                                value="{{ old('tanggal_mulai', date('Y-m-d')) }}" required>
                            @error('tanggal_mulai')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                        
                        <div class="form-group">
                            <label for="tanggal_selesai">Tanggal Selesai</label>
                            <input type="date" name="tanggal_selesai" id="tanggal_selesai" 
                                class="form-control @error('tanggal_selesai') is-invalid @enderror" 
                                value="{{ old('tanggal_selesai') }}">
                            @error('tanggal_selesai')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                        
                        <div class="form-group">
                            <label for="lampiran">Lampiran</label>
                            <input type="file" name="lampiran" id="lampiran" 
                                class="form-control-file @error('lampiran') is-invalid @enderror">
                            <small class="text-muted">Maks 10MB</small>
                            @error('lampiran')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                        
                        <div class="form-group">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" name="is_pinned" id="is_pinned" class="custom-control-input" value="1" 
                                    {{ old('is_pinned') ? 'checked' : '' }}>
                                <label class="custom-control-label" for="is_pinned">
                                    <i class="fas fa-thumbtack"></i> Pin Pengumuman
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-footer">
                <a href="{{ route('admin.pengumuman.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left mr-1"></i> Kembali
                </a>
                <button type="submit" class="btn btn-primary float-right">
                    <i class="fas fa-save mr-1"></i> Simpan
                </button>
            </div>
        </form>
    </div>
@stop
