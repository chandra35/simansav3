@extends('adminlte::page')

@section('title', 'Password E-Learning - SIMANSA')

@section('content_header')
    <div class="d-flex align-items-center justify-content-between flex-wrap" style="gap:.75rem">
        <div>
            <h1 class="mb-1"><i class="fas fa-graduation-cap mr-2 text-primary"></i>Password E-Learning</h1>
            <p class="text-muted mb-0">Kelola password akun E-Learning langsung dari SIMANSA.</p>
        </div>
        <a href="{{ route('siswa.dashboard') }}" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-left mr-1"></i>Kembali ke dashboard
        </a>
    </div>
@stop

@section('content')
    @if(session('error'))
        <div class="alert alert-danger"><i class="fas fa-exclamation-circle mr-1"></i>{{ session('error') }}</div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger">
            <strong>Password belum diperbarui.</strong>
            <ul class="mb-0 mt-1">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
    @endif

    @php($ready = $integration->enabled && filled($integration->base_url) && filled($integration->webservice_token))
    <div class="row">
        <div class="col-lg-8">
            <div class="card card-primary card-outline shadow-sm">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-key mr-2"></i>Reset password akun E-Learning</h3>
                </div>
                <div class="card-body">
                    <div class="account-meta mb-4">
                        <div><span>Nama siswa</span><strong>{{ $siswa->nama_lengkap }}</strong></div>
                        <div><span>Username E-Learning</span><strong class="text-primary">{{ $siswa->nisn ?: 'NISN belum tersedia' }}</strong></div>
                        <div><span>Platform</span><strong>E-Learning MAN 1 Metro</strong></div>
                    </div>

                    @if(!$ready)
                        <div class="alert alert-warning mb-0">
                            <i class="fas fa-tools mr-1"></i>
                            Fitur reset E-Learning belum aktif. Hubungi admin SIMANSA untuk mengaktifkan integrasi.
                        </div>
                    @else
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle mr-1"></i>
                            NISN digunakan untuk menemukan akun E-Learning Anda. Buat password baru minimal 8 karakter.
                            Password SIMANSA tidak akan diubah.
                        </div>
                        <form method="POST" action="{{ route('siswa.profile.moodle-password.update') }}" id="moodlePasswordForm">
                            @csrf
                            @method('PUT')
                            <div class="form-group">
                                <label for="password">Password E-Learning baru</label>
                                <div class="input-group">
                                    <input type="password" class="form-control" id="password" name="password"
                                           minlength="8" required autocomplete="new-password">
                                    <div class="input-group-append">
                                        <button class="btn btn-outline-secondary toggle-password" type="button" data-target="password" aria-label="Tampilkan password">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="password_confirmation">Konfirmasi password E-Learning</label>
                                <div class="input-group">
                                    <input type="password" class="form-control" id="password_confirmation" name="password_confirmation"
                                           minlength="8" required autocomplete="new-password">
                                    <div class="input-group-append">
                                        <button class="btn btn-outline-secondary toggle-password" type="button" data-target="password_confirmation" aria-label="Tampilkan konfirmasi password">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary" id="submitMoodlePassword">
                                <i class="fas fa-sync-alt mr-1"></i>Reset password E-Learning
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card card-light shadow-sm">
                <div class="card-body">
                    <h5 class="font-weight-bold"><i class="fas fa-shield-alt text-success mr-2"></i>Catatan keamanan</h5>
                    <ul class="small text-muted pl-3 mb-0">
                        <li>Password hanya dikirim melalui koneksi aman ke E-Learning.</li>
                        <li>Password tidak ditampilkan kembali setelah disimpan.</li>
                        <li>Gunakan password berbeda dari akun lain.</li>
                        <li>Setelah berhasil, login E-Learning menggunakan NISN dan password baru.</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
@stop

@section('css')
<style>
    .account-meta{display:grid;grid-template-columns:repeat(3,1fr);gap:.75rem;padding:1rem;border:1px solid #e5e7eb;border-radius:12px;background:#f8fafc}
    .account-meta span{display:block;color:#64748b;font-size:.72rem;margin-bottom:.2rem}
    .account-meta strong{display:block;color:#1e293b;font-size:.9rem}
    .password-success-modal{border-radius:1rem;padding:1.75rem}
    @media(max-width:767px){.account-meta{grid-template-columns:1fr}}
</style>
@stop

@section('js')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
$(function(){
    @if(session('success'))
        Swal.fire({
            icon: 'success',
            title: 'Password berhasil diperbarui',
            text: @json(session('success')),
            confirmButtonText: 'Mengerti',
            confirmButtonColor: '#2563eb',
            allowOutsideClick: false,
            customClass: { popup: 'password-success-modal' }
        });
    @endif

    $('.toggle-password').on('click', function(){
        const input = $('#' + $(this).data('target'));
        const icon = $(this).find('i');
        const show = input.attr('type') === 'password';
        input.attr('type', show ? 'text' : 'password');
        icon.toggleClass('fa-eye fa-eye-slash');
    });
    $('#moodlePasswordForm').on('submit', function(){
        $('#submitMoodlePassword').prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i>Menyimpan...');
    });
});
</script>
@stop
