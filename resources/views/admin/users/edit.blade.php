@extends('adminlte::page')

@section('title', 'Edit User - SIMANSA')

@section('content_header')
    <div class="simansa-hero">
        <div class="simansa-hero__main">
            <div class="simansa-hero__eyebrow"><i class="fas fa-users-cog"></i> Users & Role</div>
            <h1 class="simansa-hero__title">Edit User</h1>
            <p class="simansa-hero__subtitle">Perbarui identitas akun, reset password bila perlu, dan sesuaikan role user tanpa bikin halaman terasa berat.</p>
        </div>
        <div class="simansa-hero__side">
            <div class="simansa-hero-chip">
                <span class="simansa-hero-chip__label">User</span>
                <span class="simansa-hero-chip__value">{{ $user->name }}</span>
            </div>
        </div>
    </div>
@stop

@section('content')
<form action="{{ route('admin.users.update', $user->id) }}" method="POST" class="simansa-form-shell">
    @csrf
    @method('PUT')

    <div class="row">
        <div class="col-xl-7 mb-4">
            <div class="card simansa-management-card simansa-form-card h-100">
                <div class="card-header">
                    <h3 class="card-title mb-0"><i class="fas fa-user mr-2"></i> Informasi User</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name" class="simansa-filter-label"><i class="fas fa-id-card"></i> Nama Lengkap <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $user->name) }}" required>
                                @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="username" class="simansa-filter-label"><i class="fas fa-user-circle"></i> Username <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('username') is-invalid @enderror" id="username" name="username" value="{{ old('username', $user->username) }}" required>
                                @error('username')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="email" class="simansa-filter-label"><i class="fas fa-envelope"></i> Email <span class="text-danger">*</span></label>
                                <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email', $user->email) }}" required>
                                @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-0">
                                <label for="phone" class="simansa-filter-label"><i class="fas fa-phone"></i> Telepon</label>
                                <input type="text" class="form-control @error('phone') is-invalid @enderror" id="phone" name="phone" value="{{ old('phone', $user->phone) }}">
                                @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-5 mb-4">
            <div class="card simansa-surface-card simansa-form-card h-100">
                <div class="card-header">
                    <h3 class="card-title mb-0"><i class="fas fa-lock mr-2"></i> Password & Role</h3>
                </div>
                <div class="card-body">
                    <div class="simansa-section-note mb-3">
                        <i class="fas fa-info-circle mr-1"></i> Kosongkan password bila Anda tidak ingin mengubah password user ini.
                    </div>
                    <div class="form-group">
                        <label for="password" class="simansa-filter-label"><i class="fas fa-key"></i> Password Baru</label>
                        <input type="password" class="form-control @error('password') is-invalid @enderror" id="password" name="password">
                        @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="form-group">
                        <label for="password_confirmation" class="simansa-filter-label"><i class="fas fa-check-circle"></i> Konfirmasi Password Baru</label>
                        <input type="password" class="form-control" id="password_confirmation" name="password_confirmation">
                    </div>
                    <div class="form-group mb-3">
                        <label class="simansa-filter-label"><i class="fas fa-user-tag"></i> Role</label>
                        <div class="simansa-check-grid">
                            @foreach($roles as $role)
                                <div class="simansa-check-card">
                                    <div class="custom-control custom-checkbox">
                                        <input class="custom-control-input" type="checkbox" name="roles[]"
                                               value="{{ $role->id }}" id="role{{ $role->id }}"
                                               {{ in_array($role->id, old('roles', $userRoles)) ? 'checked' : '' }}>
                                        <label class="custom-control-label" for="role{{ $role->id }}">
                                            {{ $role->name }}
                                        </label>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    <div class="simansa-mini-stat">
                        <span class="simansa-mini-stat__label">Role Aktif Saat Ini</span>
                        <div class="mt-2">
                            @forelse($user->roles as $role)
                                <span class="badge badge-primary mr-1 mb-1 px-3 py-2">{{ $role->name }}</span>
                            @empty
                                <span class="text-muted">Belum memiliki role</span>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card simansa-management-card">
        <div class="card-footer">
            <div class="simansa-toolbar">
                <div class="text-muted small">Role detail dan direct permission lanjutan tetap bisa diatur dari halaman Data User.</div>
                <div class="simansa-toolbar__group">
                    <a href="{{ route('admin.users.index') }}" class="btn simansa-btn-muted">
                        <i class="fas fa-arrow-left mr-1"></i> Kembali
                    </a>
                    <button type="submit" class="btn simansa-btn-strong">
                        <i class="fas fa-save mr-1"></i> Update User
                    </button>
                </div>
            </div>
        </div>
    </div>
</form>
@stop
