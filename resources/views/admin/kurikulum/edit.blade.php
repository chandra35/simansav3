@extends('adminlte::page')

@section('title', 'Edit Kurikulum')

@section('content_header')
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1><i class="fas fa-edit"></i> Edit Kurikulum</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.kurikulum.index') }}">Kurikulum</a></li>
                <li class="breadcrumb-item active">Edit</li>
            </ol>
        </div>
    </div>
@stop

@section('content')
    <form action="{{ route('admin.kurikulum.update', $kurikulum->id) }}" method="POST">
        @csrf
        @method('PUT')
        
        <div class="callout callout-info">
            <h5><i class="fas fa-info-circle"></i> Status: {!! $kurikulum->status_badge !!}</h5>
        </div>

        <div class="card card-primary card-outline">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-info-circle"></i> Informasi Kurikulum</h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="kode">Kode Kurikulum <span class="text-danger">*</span></label>
                            <input type="text" name="kode" id="kode" class="form-control @error('kode') is-invalid @enderror" 
                                   value="{{ old('kode', $kurikulum->kode) }}" required>
                            @error('kode')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="nama_kurikulum">Nama Kurikulum <span class="text-danger">*</span></label>
                            <input type="text" name="nama_kurikulum" id="nama_kurikulum" class="form-control @error('nama_kurikulum') is-invalid @enderror" 
                                   value="{{ old('nama_kurikulum', $kurikulum->nama_kurikulum) }}" required>
                            @error('nama_kurikulum')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="tahun_berlaku">Tahun Berlaku <span class="text-danger">*</span></label>
                            <input type="number" name="tahun_berlaku" id="tahun_berlaku" class="form-control @error('tahun_berlaku') is-invalid @enderror" 
                                   value="{{ old('tahun_berlaku', $kurikulum->tahun_berlaku) }}" min="1990" max="2100" required>
                            @error('tahun_berlaku')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="has_jurusan">Memiliki Peminatan/Jurusan? <span class="text-danger">*</span></label>
                            <select name="has_jurusan" id="has_jurusan" class="form-control @error('has_jurusan') is-invalid @enderror" required>
                                <option value="1" {{ old('has_jurusan', $kurikulum->has_jurusan) == 1 ? 'selected' : '' }}>Ya</option>
                                <option value="0" {{ old('has_jurusan', $kurikulum->has_jurusan) == 0 ? 'selected' : '' }}>Tidak</option>
                            </select>
                            @error('has_jurusan')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="deskripsi">Deskripsi</label>
                    <textarea name="deskripsi" id="deskripsi" rows="3" class="form-control @error('deskripsi') is-invalid @enderror">{{ old('deskripsi', $kurikulum->deskripsi) }}</textarea>
                    @error('deskripsi')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-footer">
                <div class="row">
                    <div class="col-md-6">
                        <a href="{{ route('admin.kurikulum.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Kembali
                        </a>
                    </div>
                    <div class="col-md-6 text-right">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Update
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
@stop
