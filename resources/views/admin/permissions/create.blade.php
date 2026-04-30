@extends('adminlte::page')

@section('title', 'Tambah Permission')

@section('content_header')
    <div class="simansa-hero">
        <div class="simansa-hero__main">
            <div class="simansa-hero__eyebrow"><i class="fas fa-key"></i> Users & Role</div>
            <h1 class="simansa-hero__title">Tambah Permission</h1>
            <p class="simansa-hero__subtitle">Tambahkan permission baru dengan pola penamaan yang rapi supaya modul role dan permission matrix tetap mudah dipelihara.</p>
        </div>
        <div class="simansa-hero__side">
            <div class="simansa-hero-chip">
                <span class="simansa-hero-chip__label">Kategori Tersedia</span>
                <span class="simansa-hero-chip__value">{{ count($categories) }}</span>
            </div>
        </div>
    </div>
@stop

@section('content')
    <div class="row">
        <div class="col-xl-8 mb-4">
            <form action="{{ route('admin.permissions.store') }}" method="POST">
                @csrf
                <div class="card simansa-management-card simansa-form-card">
                    <div class="card-header">
                        <h3 class="card-title mb-0"><i class="fas fa-key mr-2"></i> Informasi Permission</h3>
                    </div>
                    <div class="card-body">
                        <div class="form-group mb-0">
                            <label for="name" class="simansa-filter-label"><i class="fas fa-fingerprint"></i> Nama Permission <span class="text-danger">*</span></label>
                            <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror"
                                   value="{{ old('name') }}" placeholder="Contoh: view-siswa, create-kelas" required>
                            @error('name')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                            <div class="simansa-filter-hint">Gunakan format <code>kategori-aksi</code> dengan huruf kecil dan tanda hubung.</div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <div class="simansa-toolbar">
                            <div class="text-muted small">Permission ini nanti bisa dipakai di role dan permission matrix.</div>
                            <div class="simansa-toolbar__group">
                                <a href="{{ route('admin.permissions.index') }}" class="btn simansa-btn-muted">
                                    <i class="fas fa-arrow-left mr-1"></i> Kembali
                                </a>
                                <button type="submit" class="btn simansa-btn-strong">
                                    <i class="fas fa-save mr-1"></i> Simpan Permission
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <div class="col-xl-4 mb-4">
            <div class="card simansa-surface-card h-100">
                <div class="card-header">
                    <h3 class="card-title mb-0"><i class="fas fa-lightbulb mr-2"></i> Panduan Cepat</h3>
                </div>
                <div class="card-body">
                    <div class="simansa-section-note mb-3">
                        <i class="fas fa-info-circle mr-1"></i> Permission sebaiknya spesifik, konsisten, dan mudah dibaca saat dipilih operator.
                    </div>
                    <div class="mb-3">
                        <div class="font-weight-bold text-dark mb-2">Kategori yang sudah ada</div>
                        @foreach($categories as $category)
                            <span class="badge badge-light border mr-1 mb-2 px-3 py-2">{{ $category }}</span>
                        @endforeach
                    </div>
                    <ul class="mb-0 pl-3 text-muted">
                        <li>Gunakan format <code>kategori-aksi</code></li>
                        <li>Contoh aksi: <code>view</code>, <code>create</code>, <code>edit</code>, <code>delete</code>, <code>manage</code></li>
                        <li>Hindari nama yang terlalu panjang atau ambigu</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
@stop
