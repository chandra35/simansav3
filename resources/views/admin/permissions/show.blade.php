@extends('adminlte::page')

@section('title', 'Detail Permission - ' . $permission->name)

@section('css')
<style>
    .permission-header {
        background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
        color: white;
        border-radius: 10px;
        padding: 30px;
        margin-bottom: 20px;
    }
    .role-badge {
        font-size: 0.9rem;
        margin: 3px;
        padding: 8px 15px;
    }
</style>
@stop

@section('content_header')
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1><i class="fas fa-key text-success"></i> Detail Permission</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.permissions.index') }}">Permissions</a></li>
                <li class="breadcrumb-item active">{{ $permission->name }}</li>
            </ol>
        </div>
    </div>
@stop

@section('content')
    <!-- Permission Header -->
    <div class="permission-header">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h2><i class="fas fa-key"></i> {{ $permission->name }}</h2>
                <p class="mb-0 text-white-50">
                    Guard: {{ $permission->guard_name }} | 
                    Dibuat: {{ $permission->created_at->format('d M Y H:i') }}
                </p>
            </div>
            <div class="col-md-4 text-right">
                <div class="bg-white rounded p-3 d-inline-block">
                    <h3 class="text-success mb-0">{{ $permission->roles->count() }}</h3>
                    <span class="text-muted">Roles menggunakan</span>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-user-tag"></i> Roles yang Memiliki Permission Ini</h3>
                </div>
                <div class="card-body">
                    @if($permission->roles->count() > 0)
                        @foreach($permission->roles as $role)
                            <a href="{{ route('admin.roles.show', $role) }}" class="badge badge-primary role-badge">
                                <i class="fas fa-user-tag"></i> {{ $role->name }}
                            </a>
                        @endforeach
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-user-tag fa-3x text-muted mb-3"></i>
                            <p class="text-muted">Belum ada role yang menggunakan permission ini</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Back Button -->
    <div class="mb-4">
        <a href="{{ route('admin.permissions.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Kembali ke Daftar Permission
        </a>
        @if($permission->roles->count() == 0)
            <form action="{{ route('admin.permissions.destroy', $permission) }}" method="POST" class="d-inline"
                  onsubmit="return confirm('Hapus permission {{ $permission->name }}?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger">
                    <i class="fas fa-trash"></i> Hapus Permission
                </button>
            </form>
        @endif
    </div>
@stop
