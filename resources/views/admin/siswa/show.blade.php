@extends('adminlte::page')

@section('title', 'Detail Siswa - ' . $siswa->nama_lengkap)

@section('content_header')
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1><i class="fas fa-user-graduate"></i> Detail Siswa</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.siswa.index') }}">Siswa</a></li>
                <li class="breadcrumb-item active">Detail</li>
            </ol>
        </div>
    </div>
@stop

@section('content')
<div class="row">
    <!-- Left Column: Profile Card -->
    <div class="col-md-4">
        <!-- Profile Card -->
        <div class="card card-primary card-outline">
            <div class="card-body box-profile">
                <div class="text-center">
                    <img class="profile-user-img img-fluid img-circle elevation-2" 
                         src="{{ $siswa->foto_profile_url }}" 
                         alt="{{ $siswa->nama_lengkap }}"
                         style="width: 150px; height: 150px; object-fit: cover;">
                </div>

                <h3 class="profile-username text-center">{{ $siswa->nama_lengkap }}</h3>

                <p class="text-muted text-center">
                    @if($siswa->jenis_kelamin == 'L')
                        <span class="badge badge-primary"><i class="fas fa-male"></i> Laki-laki</span>
                    @else
                        <span class="badge badge-danger"><i class="fas fa-female"></i> Perempuan</span>
                    @endif
                    @if($siswa->kelasAktif)
                        <span class="badge badge-success">{{ $siswa->kelasAktif->nama_kelas ?? 'Aktif' }}</span>
                    @endif
                </p>

                <ul class="list-group list-group-unbordered mb-3">
                    <li class="list-group-item">
                        <b><i class="fas fa-id-card text-primary"></i> NISN</b>
                        <a class="float-right">{{ $siswa->nisn ?? '-' }}</a>
                    </li>
                    <li class="list-group-item">
                        <b><i class="fas fa-id-badge text-info"></i> NIS</b>
                        <a class="float-right">{{ $siswa->nis ?? '-' }}</a>
                    </li>
                    <li class="list-group-item">
                        <b><i class="fas fa-envelope text-secondary"></i> Email</b>
                        <a class="float-right">{{ $siswa->user->email ?? '-' }}</a>
                    </li>
                    <li class="list-group-item">
                        <b><i class="fas fa-phone text-success"></i> No. HP</b>
                        <a class="float-right">{{ $siswa->nomor_hp ?? '-' }}</a>
                    </li>
                </ul>

                @can('edit-siswa')
                <a href="{{ route('admin.siswa.edit', $siswa->id) }}" class="btn btn-primary btn-block">
                    <i class="fas fa-edit"></i> Edit Siswa
                </a>
                @endcan
            </div>
        </div>

        <!-- Account Info -->
        <div class="card card-secondary">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-user-lock"></i> Informasi Akun</h3>
            </div>
            <div class="card-body">
                <strong><i class="fas fa-user"></i> Username</strong>
                <p class="text-muted">{{ $siswa->user->username ?? '-' }}</p>

                <hr>

                <strong><i class="fas fa-key"></i> Password</strong>
                <p class="text-muted">
                    @if($siswa->user && $siswa->user->readable_password)
                        <code class="js-password-text" data-password="{{ $siswa->user->readable_password }}">••••••••</code>
                        <button type="button" class="btn btn-xs btn-outline-secondary ml-2 js-toggle-password" aria-label="Tampilkan password">
                            <i class="fas fa-eye"></i>
                        </button>
                        <button class="btn btn-xs btn-outline-secondary ml-1" onclick="copyPassword('{{ $siswa->user->readable_password }}')">
                            <i class="fas fa-copy"></i>
                        </button>
                    @else
                        <em class="text-muted">Tidak tersedia</em>
                    @endif
                </p>

                <hr>

                <strong><i class="fas fa-sign-in-alt"></i> Status Login</strong>
                <p class="text-muted">
                    @if($siswa->user && $siswa->user->is_first_login)
                        <span class="badge badge-warning">Belum pernah login</span>
                    @else
                        <span class="badge badge-success">Sudah pernah login</span>
                    @endif
                </p>
            </div>
        </div>
    </div>

    <!-- Right Column: Details -->
    <div class="col-md-8">
        <!-- Data Pribadi -->
        <div class="card card-info">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-user"></i> Data Pribadi</h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-sm table-borderless">
                            <tr>
                                <th width="40%">Nama Lengkap</th>
                                <td>{{ $siswa->nama_lengkap ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th>Tempat Lahir</th>
                                <td>{{ $siswa->tempat_lahir ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th>Tanggal Lahir</th>
                                <td>{{ $siswa->tanggal_lahir ? \Carbon\Carbon::parse($siswa->tanggal_lahir)->format('d F Y') : '-' }}</td>
                            </tr>
                            <tr>
                                <th>Jenis Kelamin</th>
                                <td>{{ $siswa->jenis_kelamin == 'L' ? 'Laki-laki' : 'Perempuan' }}</td>
                            </tr>
                            <tr>
                                <th>Agama</th>
                                <td>{{ $siswa->agama ?? '-' }}</td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-sm table-borderless">
                            <tr>
                                <th width="40%">NIK</th>
                                <td>{{ $siswa->nik ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th>Anak Ke</th>
                                <td>{{ $siswa->anak_ke ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th>Jumlah Saudara</th>
                                <td>{{ $siswa->jumlah_saudara ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th>Cita-cita</th>
                                <td>{{ $siswa->cita_cita ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th>Hobi</th>
                                <td>{{ $siswa->hobi ?? '-' }}</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Alamat -->
        <div class="card card-success">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-map-marker-alt"></i> Alamat</h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-12">
                        <p><strong>Alamat Lengkap:</strong></p>
                        <p>{{ $siswa->alamat_siswa ?? '-' }}</p>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-3">
                        <strong>RT/RW</strong>
                        <p>{{ $siswa->rt_siswa ?? '-' }} / {{ $siswa->rw_siswa ?? '-' }}</p>
                    </div>
                    <div class="col-md-3">
                        <strong>Kelurahan/Desa</strong>
                        <p>{{ $siswa->kelurahanSiswa->nama ?? '-' }}</p>
                    </div>
                    <div class="col-md-3">
                        <strong>Kecamatan</strong>
                        <p>{{ $siswa->kecamatanSiswa->nama ?? '-' }}</p>
                    </div>
                    <div class="col-md-3">
                        <strong>Kab/Kota</strong>
                        <p>{{ $siswa->kabupatenSiswa->nama ?? '-' }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Asal Sekolah -->
        <div class="card card-warning">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-school"></i> Asal Sekolah</h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-sm table-borderless">
                            <tr>
                                <th width="40%">NPSN</th>
                                <td>{{ $siswa->npsn_asal_sekolah ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th>Nama Sekolah</th>
                                <td>{{ $siswa->sekolahAsal->nama ?? $siswa->nama_sekolah_asal ?? '-' }}</td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-sm table-borderless">
                            <tr>
                                <th width="40%">Bentuk</th>
                                <td>{{ $siswa->sekolahAsal->bentuk_pendidikan ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th>Status</th>
                                <td>{{ $siswa->sekolahAsal->status ?? '-' }}</td>
                            </tr>
                        </table>
                    </div>
                </div>
                @if($siswa->sekolahAsal && $siswa->sekolahAsal->alamat_lengkap)
                <div class="row">
                    <div class="col-md-12">
                        <strong>Alamat Sekolah:</strong>
                        <p class="text-muted">{{ $siswa->sekolahAsal->alamat_lengkap }}</p>
                    </div>
                </div>
                @endif
            </div>
        </div>

        <!-- Data Orang Tua -->
        @if($siswa->ortu)
        <div class="card card-danger">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-users"></i> Data Orang Tua</h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h5 class="text-primary"><i class="fas fa-male"></i> Ayah</h5>
                        <table class="table table-sm table-borderless">
                            <tr>
                                <th width="40%">Nama</th>
                                <td>{{ $siswa->ortu->nama_ayah ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th>NIK</th>
                                <td>{{ $siswa->ortu->nik_ayah ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th>Pekerjaan</th>
                                <td>{{ $siswa->ortu->pekerjaan_ayah ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th>No. HP</th>
                                <td>{{ $siswa->ortu->hp_ayah ?? '-' }}</td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <h5 class="text-danger"><i class="fas fa-female"></i> Ibu</h5>
                        <table class="table table-sm table-borderless">
                            <tr>
                                <th width="40%">Nama</th>
                                <td>{{ $siswa->ortu->nama_ibu ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th>NIK</th>
                                <td>{{ $siswa->ortu->nik_ibu ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th>Pekerjaan</th>
                                <td>{{ $siswa->ortu->pekerjaan_ibu ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th>No. HP</th>
                                <td>{{ $siswa->ortu->hp_ibu ?? '-' }}</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        @endif

        <!-- Metadata -->
        <div class="card card-secondary">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-info-circle"></i> Informasi Sistem</h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <small class="text-muted">
                            <i class="fas fa-user-plus"></i> Dibuat oleh: {{ $siswa->creator->name ?? 'System' }}<br>
                            <i class="fas fa-calendar"></i> {{ $siswa->created_at ? $siswa->created_at->format('d M Y H:i') : '-' }}
                        </small>
                    </div>
                    <div class="col-md-6">
                        <small class="text-muted">
                            <i class="fas fa-user-edit"></i> Diupdate oleh: {{ $siswa->updater->name ?? '-' }}<br>
                            <i class="fas fa-calendar"></i> {{ $siswa->updated_at ? $siswa->updated_at->format('d M Y H:i') : '-' }}
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Action Buttons -->
<div class="card">
    <div class="card-footer">
        <div class="row">
            <div class="col-md-6">
                <a href="{{ route('admin.siswa.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Kembali ke Daftar Siswa
                </a>
            </div>
            <div class="col-md-6 text-right">
                @can('edit-siswa')
                <a href="{{ route('admin.siswa.edit', $siswa->id) }}" class="btn btn-primary">
                    <i class="fas fa-edit"></i> Edit Siswa
                </a>
                @endcan
                @can('reset-password-siswa')
                <button type="button" class="btn btn-warning" onclick="resetPassword('{{ $siswa->id }}')">
                    <i class="fas fa-key"></i> Reset Password
                </button>
                @endcan
            </div>
        </div>
    </div>
</div>
@stop

@section('js')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.querySelectorAll('.js-toggle-password').forEach(function (button) {
    button.addEventListener('click', function () {
        const passwordElement = button.parentElement.querySelector('.js-password-text');
        if (!passwordElement) {
            return;
        }

        const isHidden = passwordElement.textContent === '••••••••';
        passwordElement.textContent = isHidden
            ? passwordElement.dataset.password
            : '••••••••';
        button.innerHTML = '<i class="fas ' + (isHidden ? 'fa-eye-slash' : 'fa-eye') + '"></i>';
    });
});

function copyPassword(password) {
    navigator.clipboard.writeText(password).then(function() {
        toastr.success('Password berhasil disalin!');
    }, function() {
        toastr.error('Gagal menyalin password');
    });
}

function resetPassword(siswaId) {
    Swal.fire({
        title: 'Reset Password?',
        text: 'Password siswa akan direset ke default (NISN)',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#f39c12',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Ya, Reset',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: '{{ url("admin/siswa") }}/' + siswaId + '/reset-password',
                type: 'PUT',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: response.default_password
                            ? `${response.message || 'Password berhasil direset'} (Password: ${response.default_password})`
                            : (response.message || 'Password berhasil direset')
                    }).then(() => {
                        location.reload();
                    });
                },
                error: function(xhr) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal!',
                        text: xhr.responseJSON?.message || 'Terjadi kesalahan'
                    });
                }
            });
        }
    });
}
</script>
@stop
