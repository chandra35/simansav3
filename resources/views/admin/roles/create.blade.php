@extends('adminlte::page')

@section('title', 'Tambah Role')

@section('css')
    @include('admin.roles.partials.permission-accordion-assets')
@stop

@section('content_header')
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1><i class="fas fa-user-tag text-primary mr-1"></i> Tambah Role</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.roles.index') }}">Role Management</a></li>
                <li class="breadcrumb-item active">Tambah Role</li>
            </ol>
        </div>
    </div>
@stop

@section('content')
    <form action="{{ route('admin.roles.store') }}" method="POST" class="simansa-form-shell">
        @csrf

        <div class="card bg-gradient-primary text-white mb-4 simansa-role-form-hero">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-lg-8">
                        <p class="simansa-role-form-hero__eyebrow"><i class="fas fa-users-cog mr-1"></i> Users &amp; Role</p>
                        <h2 class="simansa-role-form-hero__title">Tambah Role</h2>
                        <p class="mb-0">Buat role baru, lalu pilih permission minimum yang benar-benar dibutuhkan agar pengelolaan akses tetap rapi dan mudah diaudit.</p>
                    </div>
                    <div class="col-lg-4 mt-3 mt-lg-0">
                        <div class="simansa-role-form-hero__stat">
                            <span>Grup Permission</span>
                            <strong>{{ count($permissionCatalog) }}</strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card card-outline card-primary simansa-form-card">
            <div class="card-header">
                <div class="simansa-toolbar">
                    <h3 class="card-title mb-0"><i class="fas fa-info-circle mr-2"></i> Informasi Role</h3>
                    <div class="simansa-toolbar__group">
                        <a href="{{ route('admin.roles.index') }}" class="btn simansa-btn-muted">
                            <i class="fas fa-arrow-left mr-1"></i> Kembali
                        </a>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-lg-8">
                        <div class="form-group mb-0">
                            <label for="name" class="simansa-filter-label"><i class="fas fa-fingerprint"></i> Nama Role <span class="text-danger">*</span></label>
                            <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror"
                                   value="{{ old('name') }}" placeholder="Contoh: Koordinator BK, Staff Keuangan" required>
                            @error('name')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                            <div class="simansa-filter-hint">Gunakan nama yang jelas dan mudah dikenali operator saat assign role ke user.</div>
                        </div>
                    </div>
                    <div class="col-lg-4 mt-3 mt-lg-0">
                        <div class="simansa-mini-stat h-100">
                            <span class="simansa-mini-stat__label">Prinsip</span>
                            <span class="simansa-mini-stat__value">Least Privilege</span>
                            <div class="simansa-filter-hint">Mulai dari akses minimum, lalu tambah bila benar-benar diperlukan.</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card card-outline card-primary simansa-form-card">
            <div class="card-header">
                <div class="simansa-toolbar">
                    <div>
                        <h3 class="card-title mb-0"><i class="fas fa-key mr-2"></i> Permission Role</h3>
                    </div>
                    <div class="simansa-toolbar__group">
                        <button type="button" class="btn simansa-btn-contrast" onclick="checkAll()">
                            <i class="fas fa-check-double mr-1"></i> Pilih Semua
                        </button>
                        <button type="button" class="btn simansa-btn-muted" onclick="uncheckAll()">
                            <i class="fas fa-eraser mr-1"></i> Kosongkan
                        </button>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="simansa-section-note mb-4">
                    <i class="fas fa-lightbulb mr-1"></i> Permission sekarang dikelompokkan per fitur sekolah. Jadi Anda bisa langsung menyusun akses untuk modul seperti Data Siswa, GTK, Kelas, atau Tahun Pelajaran.
                </div>
                @include('admin.roles.partials.permission-accordion', [
                    'selectedPermissions' => old('permissions', []),
                    'accordionId' => 'rolePermissionAccordion',
                ])
            </div>
            <div class="card-footer">
                <div class="simansa-toolbar">
                    <div class="text-muted small">Pastikan role baru ini hanya membawa akses yang memang diperlukan.</div>
                    <div class="simansa-toolbar__group">
                        <a href="{{ route('admin.roles.index') }}" class="btn simansa-btn-muted">
                            <i class="fas fa-arrow-left mr-1"></i> Kembali
                        </a>
                        <button type="submit" class="btn simansa-btn-strong">
                            <i class="fas fa-save mr-1"></i> Simpan Role
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
@stop

@section('js')
    @include('admin.roles.partials.permission-accordion-scripts')
@stop
