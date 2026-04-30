@extends('adminlte::page')

@section('title', 'Tambah User - SIMANSA')

@section('content_header')
    <div class="simansa-hero">
        <div class="simansa-hero__main">
            <div class="simansa-hero__eyebrow"><i class="fas fa-users-cog"></i> Users & Role</div>
            <h1 class="simansa-hero__title">Tambah User</h1>
            <p class="simansa-hero__subtitle">Buat akun baru dengan identitas yang jelas, lalu tetapkan role yang tepat agar akses pengguna langsung sesuai kebutuhan.</p>
        </div>
        <div class="simansa-hero__side">
            <div class="simansa-hero-chip">
                <span class="simansa-hero-chip__label">Role Tersedia</span>
                <span class="simansa-hero-chip__value">{{ $roles->count() }}</span>
            </div>
        </div>
    </div>
@stop

@section('content')
<form action="{{ route('admin.users.store') }}" method="POST" class="simansa-form-shell">
    @csrf

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
                                <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}" required>
                                @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="username" class="simansa-filter-label"><i class="fas fa-user-circle"></i> Username <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('username') is-invalid @enderror" id="username" name="username" value="{{ old('username') }}" required>
                                @error('username')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                <div class="simansa-filter-hint">Dipakai untuk login.</div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="email" class="simansa-filter-label"><i class="fas fa-envelope"></i> Email <span class="text-danger">*</span></label>
                                <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email') }}" required>
                                @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-0">
                                <label for="phone" class="simansa-filter-label"><i class="fas fa-phone"></i> Telepon</label>
                                <input type="text" class="form-control @error('phone') is-invalid @enderror" id="phone" name="phone" value="{{ old('phone') }}">
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
                    <div class="form-group position-relative">
                        <label for="password" class="simansa-filter-label"><i class="fas fa-key"></i> Password <span class="text-danger">*</span></label>
                        <input type="password" class="form-control @error('password') is-invalid @enderror" id="password" name="password" required>
                        @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        <div class="simansa-filter-hint">Minimal 8 karakter.</div>
                    </div>
                    <div class="form-group position-relative">
                        <label for="password_confirmation" class="simansa-filter-label"><i class="fas fa-check-circle"></i> Konfirmasi Password <span class="text-danger">*</span></label>
                        <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" required>
                    </div>
                    <div class="form-group mb-0">
                        <label class="simansa-filter-label"><i class="fas fa-user-tag"></i> Role</label>
                        <div class="simansa-check-grid">
                            @foreach($roles as $role)
                                <div class="simansa-check-card">
                                    <div class="custom-control custom-checkbox">
                                        <input class="custom-control-input" type="checkbox" name="roles[]"
                                               value="{{ $role->id }}" id="role{{ $role->id }}"
                                               {{ in_array($role->id, old('roles', [])) ? 'checked' : '' }}>
                                        <label class="custom-control-label" for="role{{ $role->id }}">
                                            {{ $role->name }}
                                        </label>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <div class="simansa-filter-hint">User bisa diberi lebih dari satu role bila memang diperlukan.</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card simansa-management-card">
        <div class="card-footer">
            <div class="simansa-toolbar">
                <div class="text-muted small">User baru bisa disempurnakan role dan direct permission-nya nanti dari halaman Data User.</div>
                <div class="simansa-toolbar__group">
                    <a href="{{ route('admin.users.index') }}" class="btn simansa-btn-muted">
                        <i class="fas fa-arrow-left mr-1"></i> Kembali
                    </a>
                    <button type="submit" class="btn simansa-btn-strong">
                        <i class="fas fa-save mr-1"></i> Simpan User
                    </button>
                </div>
            </div>
        </div>
    </div>
</form>
@stop

@push('scripts')
<script>
$(function() {
    $('#name').on('input', function() {
        const name = $(this).val();
        const username = name.toLowerCase().replace(/\s+/g, '');
        if ($('#username').val() === '') {
            $('#username').val(username);
        }
    });
});
</script>
@endpush
