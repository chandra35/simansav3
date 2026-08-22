@extends('adminlte::page')

@section('title', 'Edit Role - ' . $role->name)

@section('css')
    @include('admin.roles.partials.permission-accordion-assets')
@stop

@section('content_header')
    <div class="simansa-hero">
        <div class="simansa-hero__main">
            <div class="simansa-hero__eyebrow"><i class="fas fa-user-tag"></i> Users & Role</div>
            <h1 class="simansa-hero__title">Edit Role</h1>
            <p class="simansa-hero__subtitle">Perbarui nama role dan paket permission-nya tanpa mengubah ritme kerja user yang sudah terikat pada role ini.</p>
        </div>
        <div class="simansa-hero__side">
            <div class="simansa-hero-chip">
                <span class="simansa-hero-chip__label">Role Aktif</span>
                <span class="simansa-hero-chip__value">{{ $role->name }}</span>
            </div>
            <div class="simansa-hero-chip">
                <span class="simansa-hero-chip__label">Permission Aktif</span>
                <span class="simansa-hero-chip__value">{{ count($rolePermissions) }}</span>
            </div>
        </div>
    </div>
@stop

@section('content')
    <form action="{{ route('admin.roles.update', $role) }}" method="POST" class="simansa-form-shell">
        @csrf
        @method('PUT')

        <div class="card simansa-management-card simansa-form-card">
            <div class="card-header">
                <div class="simansa-toolbar">
                    <h3 class="card-title mb-0"><i class="fas fa-info-circle mr-2"></i> Informasi Role</h3>
                    <div class="simansa-toolbar__group">
                        <span class="badge badge-success px-3 py-2"><i class="fas fa-check mr-1"></i>{{ count($rolePermissions) }} permission aktif</span>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-lg-8">
                        <div class="form-group mb-0">
                            <label for="name" class="simansa-filter-label"><i class="fas fa-fingerprint"></i> Nama Role <span class="text-danger">*</span></label>
                            <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror"
                                   value="{{ old('name', $role->name) }}" placeholder="Contoh: Koordinator BK, Staff Keuangan" required>
                            @error('name')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                            <div class="simansa-filter-hint">Perubahan nama akan langsung terlihat saat admin meng-assign role ke user.</div>
                        </div>
                    </div>
                    <div class="col-lg-4 mt-3 mt-lg-0">
                        <div class="simansa-mini-stat h-100">
                            <span class="simansa-mini-stat__label">Status</span>
                            <span class="simansa-mini-stat__value">Sedang Diubah</span>
                            <div class="simansa-filter-hint">Cek kembali permission penting sebelum menyimpan perubahan.</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card simansa-management-card simansa-form-card">
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
                    <i class="fas fa-lightbulb mr-1"></i> Permission dikelompokkan per fitur supaya lebih mudah diaudit. Kotak yang aktif menandakan akses yang sedang dimiliki role ini.
                </div>
                @include('admin.roles.partials.permission-accordion', [
                    'selectedPermissions' => old('permissions', $rolePermissions),
                    'accordionId' => 'rolePermissionAccordion',
                ])
            </div>
            <div class="card-footer">
                <div class="simansa-toolbar">
                    <div class="text-muted small">Perubahan permission akan memengaruhi seluruh user yang memakai role ini.</div>
                    <div class="simansa-toolbar__group">
                        <a href="{{ route('admin.roles.index') }}" class="btn simansa-btn-muted">
                            <i class="fas fa-arrow-left mr-1"></i> Kembali
                        </a>
                        <button type="submit" class="btn simansa-btn-strong">
                            <i class="fas fa-save mr-1"></i> Update Role
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
