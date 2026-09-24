@extends('adminlte::page')

@section('title', 'Edit Role - ' . $role->name)

@php
    $roleIsSystem = in_array($role->name, ['Super Admin', 'Siswa', 'GTK', 'Admin', 'Kepala Madrasah']);
    $activePermissionCount = count(old('permissions', $rolePermissions));
    $selectedPermissionNames = old('permissions', $rolePermissions);
    $permissionModuleCount = collect($permissionCatalog)->filter(fn ($module) => collect($module['items'])->whereIn('name', $selectedPermissionNames)->isNotEmpty())->count();
@endphp

@section('css')
    @include('admin.roles.partials.permission-accordion-assets')
    <style>
        .simansa-role-form-hero__stat--grid { display:grid; grid-template-columns:repeat(3,1fr); gap:.8rem; }
        .simansa-role-form-hero__stat--grid>div { display:grid; gap:.15rem; min-width:0; }
        .simansa-role-form-hero__stat--grid strong { font-size:1.25rem; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
        .simansa-permission-tools { display:flex; align-items:center; justify-content:space-between; gap:1rem; padding:.75rem; border:1px solid #dce5f2; border-radius:.65rem; background:#f8fbff; }
        .simansa-permission-tools .input-group { max-width:520px; }
        .simansa-role-permission-module.is-filtered-out { display:none; }
        @media(max-width:575.98px){.simansa-role-form-hero__stat--grid{grid-template-columns:1fr 1fr}.simansa-permission-tools{align-items:stretch; flex-direction:column}.simansa-permission-tools .input-group{max-width:none}}
    </style>
@stop

@section('content_header')
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1><i class="fas fa-user-tag text-primary mr-1"></i> Edit Role</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.roles.index') }}">Role Management</a></li>
                <li class="breadcrumb-item active">Edit Role</li>
            </ol>
        </div>
    </div>
@stop

@section('content')
    <form action="{{ route('admin.roles.update', $role) }}" method="POST" class="simansa-form-shell">
        @csrf
        @method('PUT')

        <div class="card bg-gradient-primary text-white mb-4 simansa-role-form-hero">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-lg-8">
                        <p class="simansa-role-form-hero__eyebrow"><i class="fas fa-users-cog mr-1"></i> Users &amp; Role</p>
                        <div class="d-flex flex-wrap align-items-center mb-2" style="gap:.45rem">
                            <h2 class="simansa-role-form-hero__title mb-0">Edit {{ $role->name }}</h2>
                            <span class="badge badge-light text-primary px-2 py-1">{{ $roleIsSystem ? 'Role Sistem' : 'Role Kustom' }}</span>
                        </div>
                        <p class="mb-0">Atur akses fitur role ini secara terukur. Perubahan akan langsung berlaku untuk seluruh user yang memakai role tersebut.</p>
                    </div>
                    <div class="col-lg-4 mt-3 mt-lg-0">
                        <div class="simansa-role-form-hero__stat simansa-role-form-hero__stat--grid">
                            <div><span>Permission aktif</span><strong data-active-permission-count>{{ $activePermissionCount }}</strong></div>
                            <div><span>Modul terpakai</span><strong data-active-module-count>{{ $permissionModuleCount }}</strong></div>
                            <div><span>Guard</span><strong>{{ $role->guard_name }}</strong></div>
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
                        <span class="badge badge-success px-3 py-2"><i class="fas fa-check mr-1"></i><span data-active-permission-count>{{ $activePermissionCount }}</span> permission aktif</span>
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
                            <span class="simansa-mini-stat__label">Dampak Perubahan</span>
                            <span class="simansa-mini-stat__value">{{ $role->users()->count() }} user</span>
                            <div class="simansa-filter-hint">Perubahan permission akan diterapkan ke semua user yang terhubung.</div>
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
                    <i class="fas fa-lightbulb mr-1"></i> Permission dikelompokkan per fitur supaya lebih mudah diaudit. Kotak yang aktif menandakan akses yang sedang dimiliki role ini.
                </div>
                <div class="simansa-permission-tools mb-3">
                    <div class="input-group">
                        <div class="input-group-prepend"><span class="input-group-text bg-white"><i class="fas fa-search text-primary"></i></span></div>
                        <input type="search" id="permissionSearch" class="form-control" placeholder="Cari modul atau permission..." autocomplete="off">
                        <div class="input-group-append"><button type="button" class="btn btn-outline-secondary" id="clearPermissionSearch" title="Hapus pencarian"><i class="fas fa-times"></i></button></div>
                    </div>
                    <small class="text-muted"><span data-visible-module-count>{{ count($permissionCatalog) }}</span> modul ditampilkan · <span data-active-permission-count>{{ $activePermissionCount }}</span> permission aktif</small>
                </div>
                @include('admin.roles.partials.permission-accordion', [
                    'selectedPermissions' => $selectedPermissionNames,
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
