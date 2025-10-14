@extends('adminlte::page')

@section('title', 'Tambah Kurikulum')

@section('content_header')
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1><i class="fas fa-plus-circle"></i> Tambah Kurikulum</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.kurikulum.index') }}">Kurikulum</a></li>
                <li class="breadcrumb-item active">Tambah</li>
            </ol>
        </div>
    </div>
@stop

@section('content')
    <form action="{{ route('admin.kurikulum.store') }}" method="POST">
        @csrf
        
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
                                   value="{{ old('kode') }}" placeholder="Contoh: K13, MERDEKA" required>
                            @error('kode')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">
                                <i class="fas fa-info-circle"></i> Kode singkat kurikulum (otomatis uppercase)
                            </small>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="nama_kurikulum">Nama Kurikulum <span class="text-danger">*</span></label>
                            <input type="text" name="nama_kurikulum" id="nama_kurikulum" class="form-control @error('nama_kurikulum') is-invalid @enderror" 
                                   value="{{ old('nama_kurikulum') }}" placeholder="Contoh: Kurikulum 2013" required>
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
                                   value="{{ old('tahun_berlaku', 2013) }}" min="1990" max="2100" required>
                            @error('tahun_berlaku')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">
                                <i class="fas fa-info-circle"></i> Tahun mulai berlakunya kurikulum
                            </small>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="has_jurusan">Memiliki Peminatan/Jurusan? <span class="text-danger">*</span></label>
                            <select name="has_jurusan" id="has_jurusan" class="form-control @error('has_jurusan') is-invalid @enderror" required>
                                <option value="">-- Pilih --</option>
                                <option value="1" {{ old('has_jurusan') == '1' ? 'selected' : '' }}>Ya (Ada peminatan seperti IPA, IPS)</option>
                                <option value="0" {{ old('has_jurusan') == '0' ? 'selected' : '' }}>Tidak (Seperti Kurikulum Merdeka)</option>
                            </select>
                            @error('has_jurusan')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">
                                <i class="fas fa-info-circle"></i> Apakah kurikulum ini memiliki pembagian jurusan?
                            </small>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="deskripsi">Deskripsi</label>
                    <textarea name="deskripsi" id="deskripsi" rows="3" class="form-control @error('deskripsi') is-invalid @enderror" 
                              placeholder="Deskripsi singkat tentang kurikulum...">{{ old('deskripsi') }}</textarea>
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
                        <button type="reset" class="btn btn-warning">
                            <i class="fas fa-undo"></i> Reset
                        </button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Simpan
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
@stop
