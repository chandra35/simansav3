@extends('adminlte::page')

@section('title', 'Detail Permission - ' . $permission->name)

@section('content_header')
    <div class="simansa-hero">
        <div class="simansa-hero__main">
            <div class="simansa-hero__eyebrow"><i class="fas fa-key"></i> Users & Role</div>
            <h1 class="simansa-hero__title">Detail Permission</h1>
            <p class="simansa-hero__subtitle">Lihat peran mana saja yang memakai permission ini sebelum melakukan perubahan atau pembersihan akses.</p>
        </div>
        <div class="simansa-hero__side">
            <div class="simansa-hero-chip">
                <span class="simansa-hero-chip__label">Roles Menggunakan</span>
                <span class="simansa-hero-chip__value">{{ $permission->roles->count() }}</span>
            </div>
        </div>
    </div>
@stop

@section('content')
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
            <div class="card simansa-management-card h-100">
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
