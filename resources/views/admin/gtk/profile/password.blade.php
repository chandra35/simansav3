@extends('adminlte::page')

@section('title', 'Ganti Password')

@section('content_header')
    <h1><i class="fas fa-key"></i> Ganti Password</h1>
@stop

@section('content')
    <div class="row justify-content-center">
        <div class="col-md-6">
            @if(session('info'))
                <div class="alert alert-info alert-dismissible fade show">
                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                    <i class="icon fas fa-info-circle"></i> {{ session('info') }}
                </div>
            @endif

            <div class="card card-warning">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-shield-alt"></i> Ubah Password Anda</h3>
                </div>
                <form action="{{ route('admin.gtk.profile.password.update') }}" method="POST">
                    @csrf
                    @method('PUT')
                    
                    <div class="card-body">
                        <div class="form-group">
                            <label for="current_password">Password Lama <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="password" 
                                       class="form-control @error('current_password') is-invalid @enderror" 
                                       id="current_password" 
                                       name="current_password" 
                                       placeholder="Masukkan password lama"
                                       required>
                                <div class="input-group-append">
                                    <span class="input-group-text">
                                        <i class="fas fa-lock"></i>
                                    </span>
                                </div>
                            </div>
                            @error('current_password')
                                <span class="invalid-feedback d-block">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="password">Password Baru <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="password" 
                                       class="form-control @error('password') is-invalid @enderror" 
                                       id="password" 
                                       name="password" 
                                       placeholder="Masukkan password baru (min. 8 karakter)"
                                       required>
                                <div class="input-group-append">
                                    <span class="input-group-text">
                                        <i class="fas fa-key"></i>
                                    </span>
                                </div>
                            </div>
                            @error('password')
                                <span class="invalid-feedback d-block">{{ $message }}</span>
                            @enderror
                            <small class="form-text text-muted">
                                <i class="fas fa-info-circle"></i> Password minimal 8 karakter
                            </small>
                        </div>

                        <div class="form-group">
                            <label for="password_confirmation">Konfirmasi Password Baru <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="password" 
                                       class="form-control" 
                                       id="password_confirmation" 
                                       name="password_confirmation" 
                                       placeholder="Ketik ulang password baru"
                                       required>
                                <div class="input-group-append">
                                    <span class="input-group-text">
                                        <i class="fas fa-check"></i>
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="alert alert-info">
                            <h5><i class="icon fas fa-info-circle"></i> Perhatian!</h5>
                            <ul class="mb-0 pl-3">
                                <li>Password minimal 8 karakter</li>
                                <li>Gunakan kombinasi huruf, angka, dan simbol untuk keamanan lebih baik</li>
                                <li>Jangan gunakan password yang mudah ditebak</li>
                                <li>Setelah ganti password, Anda akan tetap login dengan password baru untuk sesi berikutnya</li>
                            </ul>
                        </div>
                    </div>

                    <div class="card-footer">
                        <button type="submit" class="btn btn-warning">
                            <i class="fas fa-save"></i> Simpan Password Baru
                        </button>
                        <a href="{{ route('admin.gtk.dashboard') }}" class="btn btn-default">
                            <i class="fas fa-arrow-left"></i> Kembali
                        </a>
                    </div>
                </form>
            </div>

            {{-- User Info --}}
            <div class="card card-outline card-primary">
                <div class="card-body">
                    <p class="mb-1"><strong>Username:</strong> {{ Auth::user()->username }}</p>
                    <p class="mb-0"><strong>Email:</strong> {{ Auth::user()->email }}</p>
                </div>
            </div>
        </div>
    </div>
@stop
