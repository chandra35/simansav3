@extends('adminlte::page')

@section('title', 'Profile - SIMANSA')

@section('content_header')
    <h1>Profile Management</h1>
@stop

@section('content')
<div class="row">
    <div class="col-md-8">
        <!-- Profile Information Card -->
        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-user mr-1"></i>
                    Informasi Profile
                </h3>
            </div>
            <form method="POST" action="{{ route('admin.profile.update') }}" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-8">
                            <div class="form-group">
                                <label for="name">Nama Lengkap</label>
                                <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" 
                                       value="{{ old('name', $user->name) }}" required>
                                @error('name')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label for="email">Email</label>
                                <input type="email" name="email" id="email" class="form-control @error('email') is-invalid @enderror" 
                                       value="{{ old('email', $user->email) }}" required>
                                @error('email')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label for="username">Username</label>
                                <input type="text" id="username" class="form-control" 
                                       value="{{ $user->username }}" readonly>
                                <small class="text-muted">Username tidak dapat diubah</small>
                            </div>

                            <div class="form-group">
                                <label for="phone">No. HP</label>
                                <input type="text" name="phone" id="phone" class="form-control @error('phone') is-invalid @enderror" 
                                       value="{{ old('phone', $user->phone) }}">
                                @error('phone')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label for="role">Role</label>
                                <input type="text" id="role" class="form-control" 
                                       value="{{ ucwords(str_replace('_', ' ', $user->role)) }}" readonly>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group text-center">
                                <label>Foto Profile</label>
                                <div class="mb-3">
                                    <img src="{{ $user->avatar_url }}" 
                                         alt="Avatar" 
                                         class="img-circle img-bordered-sm"
                                         style="width: 150px; height: 150px; object-fit: cover;"
                                         id="avatar-preview">
                                </div>
                                
                                <div class="custom-file mb-2">
                                    <input type="file" name="avatar" class="custom-file-input @error('avatar') is-invalid @enderror" 
                                           id="avatar-input" accept="image/*">
                                    <label class="custom-file-label" for="avatar-input">Pilih Foto</label>
                                    @error('avatar')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                                
                                @if($user->avatar)
                                <button type="button" class="btn btn-danger btn-sm" onclick="deleteAvatar()">
                                    <i class="fas fa-trash"></i> Hapus Foto
                                </button>
                                @endif
                                
                                <small class="text-muted d-block mt-2">
                                    Format: JPG, PNG. Max: 2MB<br>
                                    Foto akan otomatis dipotong square (1:1)
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="col-md-4">
        <!-- Change Password Card -->
        <div class="card card-warning">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-lock mr-1"></i>
                    Ubah Password
                </h3>
            </div>
            <form method="POST" action="{{ route('admin.profile.password') }}">
                @csrf
                @method('PUT')
                <div class="card-body">
                    <div class="form-group">
                        <label for="current_password">Password Saat Ini</label>
                        <input type="password" name="current_password" id="current_password" 
                               class="form-control @error('current_password') is-invalid @enderror" required>
                        @error('current_password')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="password">Password Baru</label>
                        <input type="password" name="password" id="password" 
                               class="form-control @error('password') is-invalid @enderror" required>
                        @error('password')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="password_confirmation">Konfirmasi Password Baru</label>
                        <input type="password" name="password_confirmation" id="password_confirmation" 
                               class="form-control" required>
                    </div>
                </div>
                <div class="card-footer">
                    <button type="submit" class="btn btn-warning">
                        <i class="fas fa-key"></i> Ubah Password
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Hidden form for delete avatar -->
<form id="delete-avatar-form" method="POST" action="{{ route('admin.profile.avatar.delete') }}" style="display: none;">
    @csrf
    @method('DELETE')
</form>
@stop

@section('css')
    <style>
        .img-bordered-sm {
            border: 3px solid #adb5bd;
        }
    </style>
@stop

@section('js')
<script>
$(document).ready(function() {
    // Preview avatar before upload
    $('#avatar-input').change(function() {
        const file = this.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                $('#avatar-preview').attr('src', e.target.result);
            }
            reader.readAsDataURL(file);
            
            // Update label text
            $('.custom-file-label').text(file.name);
        }
    });
});

function deleteAvatar() {
    if (confirm('Apakah Anda yakin ingin menghapus foto profile?')) {
        document.getElementById('delete-avatar-form').submit();
    }
}
</script>
@stop