@extends('adminlte::page')

@section('title', 'Detail Permission - ' . $permission->name)

@section('content_header')
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1><i class="fas fa-key text-primary mr-1"></i> Detail Permission</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.permissions.index') }}">Permission Management</a></li>
                <li class="breadcrumb-item active">Detail Permission</li>
            </ol>
        </div>
    </div>
@stop

@section('content')
    <div class="card bg-gradient-primary text-white mb-4">
        <div class="card-body">
            <div class="row align-items-center">
                <div class="col-lg-8">
                    <p class="text-uppercase small font-weight-bold mb-2"><i class="fas fa-key mr-1"></i> Users &amp; Role</p>
                    <h2 class="h4 mb-2 text-break">{{ $permission->name }}</h2>
                    <p class="mb-0">Lihat role yang memakai permission ini sebelum melakukan perubahan atau pembersihan akses.</p>
                </div>
                <div class="col-lg-4 mt-3 mt-lg-0">
                    <div class="border border-white-50 rounded p-3">
                        <div class="text-uppercase small font-weight-bold text-white-50">Role Menggunakan</div>
                        <div class="h3 mb-0">{{ $permission->roles->count() }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-xl-4 mb-4">
            <div class="card simansa-surface-card h-100">
                <div class="card-header">
                    <h3 class="card-title mb-0"><i class="fas fa-fingerprint mr-2"></i> Ringkasan Permission</h3>
                </div>
                <div class="card-body">
                    <div class="simansa-mini-stat mb-3">
                        <span class="simansa-mini-stat__label">Nama Permission</span>
                        <span class="simansa-mini-stat__value" style="font-size:1.05rem;">{{ $permission->name }}</span>
                    </div>
                    <div class="simansa-mini-stat mb-3">
                        <span class="simansa-mini-stat__label">Guard</span>
                        <span class="simansa-mini-stat__value">{{ $permission->guard_name }}</span>
                    </div>
                    <div class="simansa-filter-hint">Dibuat {{ $permission->created_at->format('d M Y H:i') }}</div>
                </div>
                <div class="card-footer">
                    <div class="simansa-toolbar">
                        <a href="{{ route('admin.permissions.index') }}" class="btn simansa-btn-muted">
                            <i class="fas fa-arrow-left mr-1"></i> Kembali
                        </a>
                        @if($permission->roles->count() == 0)
                            <form action="{{ route('admin.permissions.destroy', $permission) }}" method="POST" onsubmit="return confirm('Hapus permission {{ $permission->name }}?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger">
                                    <i class="fas fa-trash mr-1"></i> Hapus
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-8 mb-4">
            <div class="card card-outline card-primary h-100">
                <div class="card-header">
                    <h3 class="card-title mb-0"><i class="fas fa-user-tag mr-2"></i> Role yang Memiliki Permission Ini</h3>
                </div>
                <div class="card-body">
                    @if($permission->roles->count() > 0)
                        <div class="simansa-check-grid">
                            @foreach($permission->roles as $role)
                                <a href="{{ route('admin.roles.show', $role) }}" class="simansa-check-card text-decoration-none">
                                    <div class="font-weight-bold text-dark">{{ $role->name }}</div>
                                    <span class="simansa-check-card__meta">Buka detail role</span>
                                </a>
                            @endforeach
                        </div>
                    @else
                        <div class="simansa-empty-state">
                            <i class="fas fa-user-tag"></i>
                            <div class="font-weight-bold text-dark mb-1">Belum ada role yang memakai permission ini</div>
                            <div>Permission yang belum dipakai aman untuk ditinjau ulang atau dihapus.</div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@stop
