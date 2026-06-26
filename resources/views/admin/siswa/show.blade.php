@extends('adminlte::page')

@section('title', 'Detail Siswa - ' . $siswa->nama_lengkap)

@section('content_header')
    <div class="student-detail-hero">
        <div class="student-detail-hero__content">
            <div class="student-detail-hero__eyebrow">
                <i class="fas fa-user-graduate"></i>
                Profil Peserta Didik
            </div>
            <h1 class="student-detail-hero__title">Detail Siswa</h1>
            <p class="student-detail-hero__subtitle">
                Tinjau identitas, akun, asal sekolah, data orang tua, dan jejak administrasi siswa dalam satu tampilan yang lebih tenang dan mudah dioperasikan.
            </p>
        </div>
        <div class="student-detail-hero__meta">
            <div class="student-detail-hero__chip">
                <span class="student-detail-hero__chip-label">NISN</span>
                <span class="student-detail-hero__chip-value">{{ $siswa->nisn ?? '-' }}</span>
            </div>
            <div class="student-detail-hero__chip">
                <span class="student-detail-hero__chip-label">Kelas</span>
                <span class="student-detail-hero__chip-value">{{ optional($siswa->getKelasSekarang())->nama_kelas ?? 'Tanpa Rombel' }}</span>
            </div>
            <div class="student-detail-hero__chip">
                <span class="student-detail-hero__chip-label">Status Login</span>
                <span class="student-detail-hero__chip-value">{{ $siswa->user && $siswa->user->is_first_login ? 'Belum login' : 'Sudah aktif' }}</span>
            </div>
        </div>
    </div>
@stop

@section('content')
@php
    $fieldLabels = [
        'nama_lengkap' => 'Nama Lengkap',
        'tempat_lahir' => 'Tempat Lahir',
        'tanggal_lahir' => 'Tanggal Lahir',
        'nisn' => 'NISN',
        'nik' => 'NIK',
        'jenis_kelamin' => 'Jenis Kelamin',
        'username' => 'Username',
        'agama' => 'Agama',
        'npsn_asal_sekolah' => 'NPSN Asal Sekolah',
        'alamat_siswa' => 'Alamat Siswa',
        'nomor_hp' => 'Nomor HP',
        'foto_profile' => 'Foto Profil',
        'nama_ayah' => 'Nama Ayah',
        'nama_ibu' => 'Nama Ibu',
    ];

    $ijazahFields = ['nama_lengkap', 'tempat_lahir', 'tanggal_lahir', 'nisn', 'nik', 'jenis_kelamin'];
@endphp

<style>
    .student-detail-hero {
        display: flex;
        justify-content: space-between;
        gap: 1.5rem;
        align-items: center;
        padding: 1.25rem 1.5rem;
        border-radius: 24px;
        background: linear-gradient(135deg, #3157c7 0%, #3182b5 58%, #3f9fa6 100%);
        color: #fff;
        box-shadow: 0 24px 52px rgba(37, 99, 235, .18);
    }

    .student-detail-hero__content {
        max-width: 760px;
    }

    .student-detail-hero__eyebrow {
        display: inline-flex;
        align-items: center;
        gap: .5rem;
        font-size: .82rem;
        font-weight: 800;
        letter-spacing: .04em;
        text-transform: uppercase;
        opacity: .88;
        margin-bottom: .55rem;
    }

    .student-detail-hero__title {
        font-size: 2rem;
        line-height: 1.08;
        font-weight: 800;
        margin: 0 0 .45rem;
    }

    .student-detail-hero__subtitle {
        max-width: 700px;
        margin: 0;
        color: rgba(255, 255, 255, .88);
        font-size: 1rem;
        line-height: 1.65;
    }

    .student-detail-hero__meta {
        display: flex;
        flex-wrap: wrap;
        justify-content: flex-end;
        gap: .85rem;
        min-width: 310px;
    }

    .student-detail-hero__chip {
        min-width: 150px;
        padding: .9rem 1rem;
        border-radius: 18px;
        background: rgba(255, 255, 255, .12);
        border: 1px solid rgba(255, 255, 255, .14);
        backdrop-filter: blur(8px);
    }

    .student-detail-hero__chip-label {
        display: block;
        font-size: .72rem;
        font-weight: 700;
        letter-spacing: .04em;
        text-transform: uppercase;
        color: rgba(255,255,255,.74);
        margin-bottom: .35rem;
    }

    .student-detail-hero__chip-value {
        display: block;
        font-size: 1.02rem;
        font-weight: 800;
        color: #fff;
    }

    .student-show-card {
        border: 0;
        border-radius: 22px;
        box-shadow: 0 22px 48px rgba(15, 23, 42, .08);
        overflow: hidden;
    }

    .student-show-card .card-header {
        border-bottom: 0;
        padding: 1rem 1.25rem;
    }

    .student-show-card .card-title {
        font-weight: 700;
    }

    .student-profile-card .profile-user-img {
        width: 160px;
        height: 160px;
        object-fit: cover;
        border: 5px solid rgba(99, 102, 241, .14);
        box-shadow: 0 16px 34px rgba(15, 23, 42, .14);
    }

    .student-show-card .list-group-item {
        padding-left: 0;
        padding-right: 0;
    }

    .student-show-card .table th {
        color: #475569;
        font-weight: 700;
    }

    .student-show-section-title {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        margin-bottom: 1rem;
    }

    .student-show-section-title h4 {
        margin: 0;
        font-size: 1.05rem;
        font-weight: 800;
        color: #0f172a;
    }

    .student-show-section-title p {
        margin: .35rem 0 0;
        color: #64748b;
        font-size: .92rem;
    }

    .student-data-table td,
    .student-data-table th {
        padding-top: .55rem;
        padding-bottom: .55rem;
        vertical-align: top;
    }

    .student-data-table th {
        width: 38%;
    }

    .student-log-meta {
        display: flex;
        flex-wrap: wrap;
        gap: .5rem;
        margin-top: .35rem;
    }

    .student-log-meta .badge {
        font-weight: 700;
        padding: .45rem .65rem;
    }

    .student-log-summary {
        display: flex;
        justify-content: space-between;
        gap: 1rem;
        align-items: flex-start;
    }

    .student-log-summary__title {
        font-weight: 700;
        color: #0f172a;
        margin-bottom: .35rem;
    }

    .student-log-summary__right {
        min-width: 180px;
        text-align: right;
    }

    .student-log-detail-toggle {
        margin-top: .65rem;
    }

    .student-log-item + .student-log-item {
        border-top: 1px solid rgba(148, 163, 184, .18);
        margin-top: 1rem;
        padding-top: 1rem;
    }

    .student-log-value {
        word-break: break-word;
        white-space: normal;
    }

    @media (max-width: 1199.98px) {
        .student-detail-hero {
            flex-direction: column;
            align-items: flex-start;
        }

        .student-detail-hero__meta {
            justify-content: flex-start;
            width: 100%;
            min-width: 0;
        }
    }

    @media (max-width: 767.98px) {
        .student-detail-hero {
            padding: 1rem 1.1rem;
            border-radius: 20px;
        }

        .student-detail-hero__title {
            font-size: 1.6rem;
        }

        .student-detail-hero__chip {
            width: 100%;
            min-width: 0;
        }

        .student-profile-card .profile-user-img {
            width: 132px;
            height: 132px;
        }

        .student-log-summary {
            flex-direction: column;
        }

        .student-log-summary__right {
            min-width: auto;
            text-align: left;
        }
    }
</style>

@php
    $displayLogs = $riwayatPerubahan->take(6);
@endphp

<div class="row">
    <!-- Left Column: Profile Card -->
    <div class="col-md-4">
        <!-- Profile Card -->
        <div class="card student-show-card student-profile-card">
            <div class="card-body box-profile">
                <div class="text-center">
                    <img class="profile-user-img img-fluid img-circle elevation-2" 
                         src="{{ $siswa->foto_profile_url }}" 
                         alt="{{ $siswa->nama_lengkap }}">
                </div>

                <h3 class="profile-username text-center">{{ $siswa->nama_lengkap }}</h3>

                <p class="text-muted text-center">
                    @if($siswa->jenis_kelamin == 'L')
                        <span class="badge badge-primary"><i class="fas fa-male"></i> Laki-laki</span>
                    @else
                        <span class="badge badge-danger"><i class="fas fa-female"></i> Perempuan</span>
                    @endif
                    @if($siswa->getKelasSekarang())
                        <span class="badge badge-success">{{ optional($siswa->getKelasSekarang())->nama_kelas ?? 'Aktif' }}</span>
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
                <a href="{{ route('admin.siswa.index') }}?edit={{ $siswa->id }}" class="btn btn-primary btn-block">
                    <i class="fas fa-edit"></i> Edit Siswa
                </a>
                @endcan
            </div>
        </div>

        <!-- Account Info -->
        <div class="card student-show-card">
            <div class="card-header bg-secondary text-white">
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
        <div class="card student-show-card">
            <div class="card-header bg-info text-white">
                <h3 class="card-title"><i class="fas fa-user"></i> Data Pribadi</h3>
            </div>
            <div class="card-body">
                <div class="student-show-section-title">
                    <div>
                        <h4>Identitas Utama</h4>
                        <p>Fokus utama admin untuk verifikasi ijazah, sinkron dokumen, dan validasi biodata siswa.</p>
                    </div>
                    <span class="badge badge-warning px-3 py-2"><i class="fas fa-file-signature"></i> Data sensitif ijazah</span>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-sm table-borderless student-data-table">
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
                        <table class="table table-sm table-borderless student-data-table">
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
        <div class="card student-show-card">
            <div class="card-header bg-success text-white">
                <h3 class="card-title"><i class="fas fa-map-marker-alt"></i> Alamat</h3>
            </div>
            <div class="card-body">
                <div class="student-show-section-title">
                    <div>
                        <h4>Alamat dan Wilayah</h4>
                        <p>Gunakan bagian ini untuk memastikan alamat siswa, wilayah administrasi, dan data domisili sudah konsisten.</p>
                    </div>
                </div>
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
        <div class="card student-show-card">
            <div class="card-header bg-warning">
                <h3 class="card-title"><i class="fas fa-school"></i> Asal Sekolah</h3>
            </div>
            <div class="card-body">
                <div class="student-show-section-title">
                    <div>
                        <h4>Rekam Asal Sekolah</h4>
                        <p>Bagian ini memudahkan operator mengecek sumber sekolah sebelum proses mutasi, sinkronisasi, dan validasi administrasi.</p>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-sm table-borderless student-data-table">
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
                        <table class="table table-sm table-borderless student-data-table">
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

        <!-- Dokumen Siswa -->
        <div class="card student-show-card">
            <div class="card-header bg-primary text-white">
                <h3 class="card-title"><i class="fas fa-folder-open"></i> Dokumen Siswa</h3>
            </div>
            <div class="card-body">
                <div class="student-show-section-title">
                    <div>
                        <h4>Daftar Dokumen Terupload</h4>
                        <p>Gunakan bagian ini untuk membuka dokumen administrasi siswa tanpa meninggalkan halaman detail.</p>
                    </div>
                    <span class="badge badge-light border px-3 py-2">{{ $siswa->dokumen->count() }} dokumen</span>
                </div>

                @if($siswa->dokumen->isNotEmpty())
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered mb-0">
                            <thead>
                                <tr>
                                    <th>Jenis Dokumen</th>
                                    <th>Nama File</th>
                                    <th>Ukuran</th>
                                    <th>Upload</th>
                                    <th width="150">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($siswa->dokumen as $dokumen)
                                    <tr>
                                        <td>
                                            <strong>{{ $dokumen->getJenisDokumenLabel() }}</strong>
                                            @if($dokumen->keterangan)
                                                <div class="small text-muted">{{ $dokumen->keterangan }}</div>
                                            @endif
                                        </td>
                                        <td>{{ $dokumen->original_name ?? $dokumen->nama_file ?? '-' }}</td>
                                        <td>{{ $dokumen->getFileSizeFormatted() }}</td>
                                        <td>{{ $dokumen->created_at?->format('d M Y H:i') ?? '-' }}</td>
                                        <td>
                                            <button type="button"
                                                    class="btn btn-info btn-xs js-preview-admin-dokumen"
                                                    data-preview-url="{{ route('siswa.dokumen.preview', $dokumen->id) }}"
                                                    data-download-url="{{ route('siswa.dokumen.download', $dokumen->id) }}"
                                                    data-title="{{ $dokumen->original_name ?? $dokumen->nama_file ?? $dokumen->getJenisDokumenLabel() }}"
                                                    data-extension="{{ $dokumen->getFileExtension() }}">
                                                <i class="fas fa-eye"></i> Lihat
                                            </button>
                                            <a href="{{ route('siswa.dokumen.download', $dokumen->id) }}" class="btn btn-secondary btn-xs">
                                                <i class="fas fa-download"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="alert alert-light border mb-0">
                        <i class="fas fa-info-circle text-info"></i>
                        Belum ada dokumen yang diupload oleh siswa ini.
                    </div>
                @endif
            </div>
        </div>

        <!-- Data Orang Tua -->
        @if($siswa->ortu)
        <div class="card student-show-card">
            <div class="card-header bg-danger text-white">
                <h3 class="card-title"><i class="fas fa-users"></i> Data Orang Tua</h3>
            </div>
            <div class="card-body">
                <div class="student-show-section-title">
                    <div>
                        <h4>Kontak Orang Tua</h4>
                        <p>Bantu admin memastikan identitas orang tua, pekerjaan, dan nomor yang bisa dihubungi saat ada kebutuhan validasi.</p>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <h5 class="text-primary"><i class="fas fa-male"></i> Ayah</h5>
                        <table class="table table-sm table-borderless student-data-table">
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
                        <table class="table table-sm table-borderless student-data-table">
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
        <div class="card student-show-card">
            <div class="card-header bg-dark text-white">
                <h3 class="card-title"><i class="fas fa-info-circle"></i> Informasi Sistem</h3>
            </div>
            <div class="card-body">
                <div class="student-show-section-title">
                    <div>
                        <h4>Jejak Administrasi</h4>
                        <p>Catatan ini membantu admin mengetahui siapa yang membuat dan terakhir memperbarui data siswa.</p>
                    </div>
                </div>
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

        <div class="card student-show-card">
            <div class="card-header bg-gradient-secondary text-white">
                <h3 class="card-title"><i class="fas fa-history"></i> Riwayat Perubahan Siswa</h3>
            </div>
            <div class="card-body">
                <div class="student-show-section-title">
                    <div>
                        <h4>Audit Perubahan Data</h4>
                        <p>Admin dapat meninjau perubahan data penting siswa beserta waktu, pelaku, dan nilai sebelum-sesudah untuk kebutuhan validasi ijazah.</p>
                    </div>
                    <span class="badge badge-light border px-3 py-2">Menampilkan {{ $displayLogs->count() }} dari {{ $riwayatPerubahan->count() }} log</span>
                </div>

                @forelse($displayLogs as $log)
                    @php
                        $oldValues = $log->old_values ?? data_get($log->properties, 'old', []);
                        $newValues = $log->new_values ?? data_get($log->properties, 'new', []);
                        $changedFields = $log->changed_fields ?? array_values(array_unique(array_merge(array_keys($oldValues ?? []), array_keys($newValues ?? []))));
                        $ijazahChanges = collect($changedFields)->intersect($ijazahFields);
                        $detailCollapseId = 'student-log-detail-' . $loop->index;
                    @endphp
                    <div class="student-log-item">
                        <div class="student-log-summary">
                            <div class="flex-grow-1">
                                <div class="student-log-summary__title">{{ $log->description }}</div>
                                <div class="student-log-meta">
                                    <span class="badge badge-light border">
                                        <i class="fas fa-user-shield"></i>
                                        {{ $log->user->name ?? 'System' }}
                                    </span>
                                    @if($log->user && $log->user->roles->isNotEmpty())
                                        <span class="badge badge-light border">{{ $log->user->roles->pluck('name')->implode(', ') }}</span>
                                    @endif
                                </div>
                            </div>
                            <div class="student-log-summary__right">
                                <span class="badge badge-light border text-uppercase">{{ str_replace('_', ' ', $log->activity_type) }}</span><br>
                                <small class="text-muted">{{ $log->created_at?->format('d M Y H:i:s') }}</small>
                            </div>
                        </div>

                        <div class="student-log-detail-toggle">
                            @if($ijazahChanges->isNotEmpty())
                                <span class="badge badge-warning mr-2">
                                    <i class="fas fa-file-signature"></i> Menyentuh data penting ijazah
                                </span>
                            @endif

                            @if(!empty($changedFields))
                                <button class="btn btn-sm btn-outline-primary" type="button" data-toggle="collapse" data-target="#{{ $detailCollapseId }}" aria-expanded="false" aria-controls="{{ $detailCollapseId }}">
                                    <i class="fas fa-eye"></i> Detail perubahan
                                </button>
                            @endif
                        </div>

                        @if(!empty($changedFields))
                            <div class="collapse mt-3" id="{{ $detailCollapseId }}">
                                <div class="table-responsive">
                                    <table class="table table-sm table-borderless mb-0">
                                        <thead>
                                            <tr>
                                                <th width="24%">Field</th>
                                                <th width="38%">Nilai Lama</th>
                                                <th width="38%">Nilai Baru</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($changedFields as $field)
                                                @php
                                                    $oldValue = $oldValues[$field] ?? null;
                                                    $newValue = $newValues[$field] ?? null;
                                                    if (is_bool($oldValue)) $oldValue = $oldValue ? 'Ya' : 'Tidak';
                                                    if (is_bool($newValue)) $newValue = $newValue ? 'Ya' : 'Tidak';
                                                @endphp
                                                <tr>
                                                    <td>
                                                        <strong>{{ $fieldLabels[$field] ?? \Illuminate\Support\Str::headline($field) }}</strong>
                                                        @if(in_array($field, $ijazahFields))
                                                            <div><span class="badge badge-warning">Ijazah</span></div>
                                                        @endif
                                                    </td>
                                                    <td class="student-log-value text-muted">{{ filled($oldValue) ? $oldValue : 'Kosong' }}</td>
                                                    <td class="student-log-value text-dark">{{ filled($newValue) ? $newValue : 'Kosong' }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        @else
                            <small class="text-muted">Aktivitas ini tidak menyimpan detail perubahan field, tetapi waktu dan pelakunya tetap tercatat.</small>
                        @endif
                    </div>
                @empty
                    <div class="alert alert-light border mb-0">
                        <i class="fas fa-info-circle text-info"></i>
                        Belum ada riwayat perubahan yang tercatat untuk siswa ini.
                    </div>
                @endforelse

                @if($riwayatPerubahan->count() > $displayLogs->count())
                    <div class="alert alert-light border mt-3 mb-0">
                        <i class="fas fa-info-circle text-info"></i>
                        Log ditampilkan ringkas agar halaman tetap nyaman dibaca. Kalau perlu, saya bisa lanjut tambahkan halaman riwayat penuh khusus siswa ini.
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

@include('admin.partials.dokumen-preview-modal')

<!-- Action Buttons -->
<div class="card student-show-card">
    <div class="card-footer">
        <div class="row">
            <div class="col-md-6">
                <a href="{{ route('admin.siswa.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Kembali ke Daftar Siswa
                </a>
            </div>
            <div class="col-md-6 text-right">
                @can('edit-siswa')
                <a href="{{ route('admin.siswa.index') }}?edit={{ $siswa->id }}" class="btn btn-primary">
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
