@extends('adminlte::page')

@section('title', 'Tambah Permission')

@section('content_header')
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1><i class="fas fa-plus-circle text-success"></i> Tambah Permission</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.permissions.index') }}">Permissions</a></li>
                <li class="breadcrumb-item active">Tambah</li>
            </ol>
        </div>
    </div>
@stop

@section('content')
    <div class="row">
        <div class="col-md-8">
            <form action="{{ route('admin.permissions.store') }}" method="POST">
                @csrf
                
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-key"></i> Informasi Permission</h3>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label for="name">Nama Permission <span class="text-danger">*</span></label>
                            <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" 
                                   value="{{ old('name') }}" placeholder="Contoh: view-reports, manage-settings" required>
                            @error('name')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                            <small class="text-muted">Format: <code>kategori-aksi</code> (contoh: view-siswa, create-kelas)</small>
                        </div>
                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-save"></i> Simpan Permission
                        </button>
                        <a href="{{ route('admin.permissions.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Kembali
                        </a>
                    </div>
                </div>
            </form>
        </div>
        
        <div class="col-md-4">
            <div class="card">
                <div class="card-header bg-info text-white">
                    <h3 class="card-title"><i class="fas fa-info-circle"></i> Kategori yang Ada</h3>
                </div>
                <div class="card-body">
                    <p class="text-muted">Kategori permission yang sudah ada:</p>
                    @foreach($categories as $category)
                        <span class="badge badge-light mr-1 mb-1">{{ $category }}</span>
                    @endforeach
                </div>
            </div>

            <div class="card">
                <div class="card-header bg-secondary text-white">
                    <h3 class="card-title"><i class="fas fa-lightbulb"></i> Tips</h3>
                </div>
                <div class="card-body">
                    <ul class="mb-0">
                        <li>Gunakan format <code>kategori-aksi</code></li>
                        <li>Gunakan huruf kecil dan tanda hubung</li>
                        <li>Contoh aksi: view, create, edit, delete, manage, export</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
@stop
