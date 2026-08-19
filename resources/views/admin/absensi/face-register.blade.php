@extends('adminlte::page')

@section('title', $pageTitle)
@section('plugins.Datatables', true)
@section('plugins.DatatablesPlugins', true)

@php
    $approvedDescription = $selectedType === 'siswa'
        ? 'Data wajah aktif dan siap digunakan pada kiosk Presensi Gerbang siswa.'
        : 'Data wajah aktif dan siap digunakan pada kiosk Presensi Gerbang GTK.';
    $selfStatus = 'belum';
    if ($selfFace) {
        $selfStatus = $selfFace->is_verified ? 'approved' : ($selfFace->is_active ? 'pending' : 'belum');
    }

    $statusMeta = match ($selfStatus) {
        'approved' => ['badge' => 'success', 'icon' => 'check-circle', 'title' => 'Registrasi Anda sudah disetujui', 'description' => $approvedDescription, 'label' => 'Approved'],
        'pending' => ['badge' => 'warning', 'icon' => 'clock', 'title' => 'Registrasi sedang menunggu verifikasi', 'description' => 'Admin akan meninjau hasil capture sebelum data wajah diaktifkan.', 'label' => 'Pending'],
        default => ['badge' => 'secondary', 'icon' => 'camera', 'title' => 'Anda belum melakukan registrasi wajah', 'description' => 'Mulai registrasi agar akun Anda memiliki identitas biometrik resmi.', 'label' => 'Belum Registrasi'],
    };
@endphp

@section('content_header')
    <div class="row mb-2">
        <div class="col-sm-6"><h1><i class="fas fa-id-card-alt text-primary"></i> {{ $pageTitle }}</h1></div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ request()->routeIs('siswa.*') ? route('siswa.dashboard') : route('admin.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item active">Data Wajah</li>
            </ol>
        </div>
    </div>
@stop

@section('content')
<div class="face-recognition-module">
<div class="card bg-gradient-primary text-white face-register-hero mb-4">
    <div class="card-body">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <div class="text-uppercase small font-weight-bold text-white-50 mb-1">Pusat Biometrik SIMANSA</div>
                <h2 class="h4 text-white mb-2">{{ $pageTitle }}</h2>
                <p class="text-white-50 mb-0">
                    @if($selfOnly)
                        Halaman ini hanya untuk pendaftaran identitas wajah. Presensi dilakukan melalui kiosk resmi di area madrasah, bukan dari akun pribadi.
                    @else
                        Kelola pendaftaran wajah GTK dan siswa. Admin dapat merekam langsung atau membuka izin registrasi ulang pada akun pengguna.
                    @endif
                </p>
            </div>
            <div class="col-lg-4 mt-3 mt-lg-0 text-lg-right">
                @if($canManageAll)
                    <div class="small text-white-50 text-uppercase font-weight-bold mb-2">Jenis responden</div>
                    <div class="btn-group" role="group" aria-label="Pilih jenis responden">
                        @foreach($typeOptions as $typeKey => $typeName)
                            <a href="{{ route('admin.absensi.face-register', ['type' => $typeKey]) }}" class="btn btn-{{ $selectedType === $typeKey ? 'light' : 'outline-light' }}">
                                <i class="fas fa-{{ $typeKey === 'gtk' ? 'chalkboard-teacher' : 'user-graduate' }} mr-1"></i>{{ $typeName }}
                            </a>
                        @endforeach
                    </div>
                @else
                    <span class="badge badge-light px-3 py-2"><i class="fas fa-user-shield mr-1"></i>Registrasi akun sendiri</span>
                @endif
            </div>
        </div>
    </div>
</div>

@if($selfOnly)
    <div class="row">
        <div class="col-xl-8">
            <div class="card card-primary card-outline face-self-card">
                <div class="card-body p-3 p-md-4">
                    <div class="d-flex flex-column flex-md-row align-items-md-center">
                        <div class="mr-md-3 mb-3 mb-md-0 text-center">
                            <img src="{{ $selfFace?->registration_photo_url ?: $selfRegistrant['avatar_url'] }}" alt="Preview registrasi {{ $selfRegistrant['name'] }}" class="img-circle face-self-card__avatar">
                            <small class="d-block text-muted mt-2">{{ $selfFace?->registration_photo_url ? 'Preview hasil registrasi' : 'Foto profil' }}</small>
                        </div>
                        <div class="flex-grow-1">
                            <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between">
                                <div>
                                    <div class="text-muted small mb-1">Identitas Pemilik Akun</div>
                                    <h3 class="h4 mb-1">{{ $selfRegistrant['name'] }}</h3>
                                    <div class="text-muted">{{ $identifierLabel }}: {{ $selfRegistrant['identifier'] ?: '-' }}</div>
                                </div>
                                <div class="mt-3 mt-lg-0">
                                    <span class="badge badge-{{ $statusMeta['badge'] }} px-3 py-2">
                                        <i class="fas fa-{{ $statusMeta['icon'] }} mr-1"></i>{{ $statusMeta['label'] }}
                                    </span>
                                </div>
                            </div>

                            <div class="face-self-status mt-3">
                                <div class="font-weight-bold mb-1">{{ $statusMeta['title'] }}</div>
                                <div class="text-muted">{{ $statusMeta['description'] }}</div>
                            </div>

                            <div class="row mt-3">
                                <div class="col-sm-4 mb-2 mb-sm-0">
                                    <div class="face-self-metric">
                                        <small class="text-muted d-block">Capture Tersimpan</small>
                                        <strong>{{ $selfFace?->total_captures ?? 0 }} frame</strong>
                                    </div>
                                </div>
                                <div class="col-sm-4 mb-2 mb-sm-0">
                                    <div class="face-self-metric">
                                        <small class="text-muted d-block">Update Terakhir</small>
                                        <strong>{{ $selfFace?->updated_at?->format('d/m/Y H:i') ?? '-' }}</strong>
                                    </div>
                                </div>
                                <div class="col-sm-4">
                                    <div class="face-self-metric">
                                        <small class="text-muted d-block">Akses Registrasi</small>
                                        <strong class="text-{{ $selfCanRegister ? 'warning' : 'success' }}">
                                            <i class="fas fa-{{ $selfCanRegister ? 'lock-open' : 'lock' }} mr-1"></i>{{ $selfCanRegister ? 'Diizinkan' : 'Terkunci' }}
                                        </strong>
                                    </div>
                                </div>
                            </div>

                            @if($selfCanRegister)
                                <div class="d-flex flex-column flex-md-row mt-4">
                                    <button class="btn btn-{{ $selfFace ? 'warning' : 'primary' }} btn-face-action btn-lg"
                                            onclick="openRegister('{{ $selfRegistrant['user_id'] }}', '{{ addslashes($selfRegistrant['name']) }}', '{{ $selfRegistrant['user_type'] }}')">
                                        <i class="fas fa-{{ $selfFace ? 'redo' : 'camera' }} mr-1"></i>
                                        {{ $selfFace ? 'Registrasi Ulang Diizinkan' : 'Mulai Registrasi' }}
                                    </button>
                                </div>
                            @else
                                <div class="alert alert-success mt-4 mb-3">
                                    <i class="fas fa-lock mr-1"></i>
                                    <strong>Registrasi terkunci.</strong> Data wajah tidak dapat diubah sampai admin menyetujui permintaan registrasi ulang.
                                </div>
                                @if($selfFace && $selfFace->self_registration_requested_at)
                                    <div class="alert alert-warning mb-0">
                                        <i class="fas fa-hourglass-half mr-1"></i>
                                        <strong>Permintaan sedang ditinjau admin.</strong>
                                        Dikirim {{ $selfFace->self_registration_requested_at->diffForHumans() }}.
                                    </div>
                                @elseif($selfFace)
                                    <form method="POST" action="{{ request()->routeIs('siswa.*') ? route('siswa.face-register.request-unlock') : route('admin.absensi.face-register.request-unlock') }}" class="border rounded p-3">
                                        @csrf
                                        <label for="unlockNote" class="font-weight-bold mb-1">Perlu registrasi ulang?</label>
                                        <div class="text-muted small mb-2">Jelaskan singkat kendalanya. Admin akan meninjau dan membuka akses satu kali.</div>
                                        <div class="input-group">
                                            <input id="unlockNote" name="note" class="form-control" maxlength="500" placeholder="Contoh: wajah sulit terdeteksi setelah mengganti kacamata">
                                            <div class="input-group-append"><button class="btn btn-outline-warning"><i class="fas fa-paper-plane mr-1"></i>Minta Unlock</button></div>
                                        </div>
                                    </form>
                                @endif
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-4 mt-3 mt-xl-0">
            <div class="card card-outline card-info h-100">
                <div class="card-body">
                    <h5 class="font-weight-bold mb-3"><i class="fas fa-list-check mr-1"></i> Alur Registrasi</h5>
                    <div class="face-register-checklist text-muted small">
                        <div><i class="fas fa-check text-success mr-1"></i> Registrasi hanya satu kali dari akun sendiri</div>
                        <div><i class="fas fa-check text-success mr-1"></i> Sistem menyimpan beberapa sudut otomatis</div>
                        <div><i class="fas fa-check text-success mr-1"></i> Admin melakukan approval</div>
                        <div><i class="fas fa-lock text-success mr-1"></i> Sistem mengunci perubahan setelah tersimpan</div>
                        <div><i class="fas fa-building text-success mr-1"></i> Presensi dilakukan di kiosk resmi madrasah</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if($selfFace)
        <div class="card card-outline card-primary mt-4">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-history mr-1"></i> Riwayat Registrasi Wajah</h3>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Aktivitas</th>
                                <th>Tanggal</th>
                                <th>Bulan</th>
                                <th>Tahun</th>
                                <th>Pelaksana</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($selfHistory as $activity)
                                @php
                                    $event = $activity->properties->get('event');
                                    $eventMeta = match($event) {
                                        'registered' => ['primary', 'camera', 'Registrasi awal'],
                                        'reregistered' => ['info', 'redo', 'Registrasi ulang'],
                                        'approved' => ['success', 'check-circle', 'Disetujui admin'],
                                        'rejected' => ['danger', 'times-circle', 'Ditolak admin'],
                                        'verification_reset' => ['warning', 'undo', 'Verifikasi di-reset'],
                                        'self_registration_requested' => ['info', 'paper-plane', 'Meminta registrasi ulang'],
                                        'self_registration_unlocked' => ['warning', 'lock-open', 'Izin registrasi ulang dibuka'],
                                        'self_registration_locked' => ['secondary', 'lock', 'Izin registrasi ulang dibatalkan'],
                                        default => ['secondary', 'history', $activity->description],
                                    };
                                @endphp
                                <tr>
                                    <td><span class="badge badge-{{ $eventMeta[0] }}"><i class="fas fa-{{ $eventMeta[1] }} mr-1"></i>{{ $eventMeta[2] }}</span></td>
                                    <td>{{ $activity->created_at->format('d/m/Y H:i') }}</td>
                                    <td>{{ $activity->created_at->translatedFormat('F') }}</td>
                                    <td>{{ $activity->created_at->format('Y') }}</td>
                                    <td>{{ $activity->causer?->name ?? ($activity->properties->get('source') === 'self' ? 'Akun sendiri' : 'Sistem') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td><span class="badge badge-primary"><i class="fas fa-camera mr-1"></i>Registrasi awal</span></td>
                                    <td>{{ $selfFace->created_at->format('d/m/Y H:i') }}</td>
                                    <td>{{ $selfFace->created_at->translatedFormat('F') }}</td>
                                    <td>{{ $selfFace->created_at->format('Y') }}</td>
                                    <td>Akun sendiri</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif

@else
    <div class="row mb-4">
        <div class="col-md-4 mb-3 mb-md-0"><div class="card card-outline card-info h-100 mb-0"><div class="card-body py-3"><div class="text-muted small text-uppercase font-weight-bold">Total {{ $subjectLabel }}</div><h3 class="text-info mb-0">{{ $registrants->count() }}</h3><small class="text-muted">Akun yang dapat diregistrasi.</small></div></div></div>
        <div class="col-md-4 mb-3 mb-md-0"><div class="card card-outline card-success h-100 mb-0"><div class="card-body py-3"><div class="text-muted small text-uppercase font-weight-bold">Sudah Registrasi</div><h3 class="text-success mb-0">{{ $registeredCount }}</h3><small class="text-muted">Memiliki data wajah aktif.</small></div></div></div>
        <div class="col-md-4"><div class="card card-outline card-warning h-100 mb-0"><div class="card-body py-3"><div class="text-muted small text-uppercase font-weight-bold">Menunggu Verifikasi</div><h3 class="text-warning mb-0">{{ $pendingCount }}</h3><small class="text-muted">Perlu ditinjau oleh admin.</small></div></div></div>
    </div>

    <div class="row mb-3">
        <div class="col-lg-8">
            <div class="card card-outline card-info h-100">
                <div class="card-body py-3">
                    <div class="d-flex align-items-start">
                        <div class="mr-3 text-info" style="font-size:1.5rem;">
                            <i class="fas fa-camera-retro"></i>
                        </div>
                        <div>
                            <div class="font-weight-bold mb-1">Panduan singkat registrasi</div>
                            <div class="text-muted small">
                                Pastikan kamera depan aktif, wajah terlihat penuh, dan jangan berpindah tempat saat countdown berjalan. Sistem akan mengambil beberapa sudut wajah secara otomatis.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-4 mt-3 mt-lg-0">
            <div class="card card-outline card-secondary h-100">
                <div class="card-body py-3">
                    <div class="font-weight-bold mb-2">Checklist sebelum mulai</div>
                    <div class="small text-muted face-register-checklist">
                        <div><i class="fas fa-check text-success mr-1"></i> Cahaya cukup terang</div>
                        <div><i class="fas fa-check text-success mr-1"></i> Kamera stabil</div>
                        <div><i class="fas fa-check text-success mr-1"></i> Wajah tanpa terpotong</div>
                        <div><i class="fas fa-check text-success mr-1"></i> Lepas penutup wajah</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card card-primary card-outline">
        <div class="card-header d-flex flex-wrap align-items-center justify-content-between">
            <h3 class="card-title mb-0"><i class="fas fa-table"></i> Daftar {{ $subjectLabel }} & Status Registrasi Wajah</h3>
        </div>
        <div class="card-body">
            @if($registrants->count() > 1)
                <div class="mb-3 d-flex flex-wrap align-items-center">
                    <span class="mr-2 font-weight-bold"><i class="fas fa-filter"></i> Filter:</span>
                    <div class="btn-group" id="statusFilter">
                        <button class="btn btn-sm btn-outline-primary active" data-filter="">Semua</button>
                        <button class="btn btn-sm btn-outline-secondary" data-filter="Belum">Belum Daftar</button>
                        <button class="btn btn-sm btn-outline-warning" data-filter="Pending">Pending</button>
                        <button class="btn btn-sm btn-outline-success" data-filter="Verified">Verified</button>
                    </div>
                </div>
            @endif

            <div class="table-responsive">
                <table class="table table-hover table-striped table-sm" id="tabelFaceRegister">
                    <thead>
                        <tr>
                            <th width="40">No</th>
                            <th>Nama {{ $subjectLabel }}</th>
                            <th>{{ $identifierLabel }}</th>
                            <th>Status</th>
                            <th>Capture</th>
                            <th>Quality</th>
                            <th>Verifikasi</th>
                            <th>Tgl Registrasi</th>
                            <th width="130">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($registrants as $i => $registrant)
                            @php $face = $faceMap[$registrant['user_id']] ?? null; @endphp
                            <tr>
                                <td data-label="No">{{ $i + 1 }}</td>
                                <td data-label="Nama">
                                    <div class="d-flex align-items-center">
                                        <img src="{{ $registrant['avatar_url'] }}" class="img-circle mr-2" width="34" height="34" style="object-fit:cover;">
                                        <strong>{{ $registrant['name'] }}</strong>
                                    </div>
                                </td>
                                <td data-label="{{ $identifierLabel }}">{{ $registrant['identifier'] ?? '-' }}</td>
                                <td data-label="Status">
                                    @if($face)
                                        @if($face->is_verified)
                                            <span class="badge badge-success"><i class="fas fa-check"></i> Verified</span>
                                        @else
                                            <span class="badge badge-warning"><i class="fas fa-clock"></i> Pending</span>
                                        @endif
                                    @else
                                        <span class="badge badge-secondary"><i class="fas fa-times"></i> Belum</span>
                                    @endif
                                </td>
                                <td data-label="Capture">@if($face)<span class="badge badge-info">{{ $face->total_captures }}</span>@else - @endif</td>
                                <td data-label="Quality">
                                    @if($face)
                                        @php $q = $face->quality_score ?? 0; @endphp
                                        <span class="badge badge-{{ $q >= 80 ? 'success' : ($q >= 50 ? 'warning' : 'danger') }}">{{ number_format($q, 0) }}%</span>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td data-label="Verifikasi">
                                    @if($face && $face->is_verified)
                                        <small>{{ $face->verified_at?->format('d/m/Y H:i') }}</small>
                                    @elseif($face)
                                        <small class="text-muted">Menunggu</small>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td data-label="Tgl Registrasi">@if($face)<small>{{ $face->created_at->format('d/m/Y H:i') }}</small>@else - @endif</td>
                                <td data-label="Aksi">
                                    <button class="btn btn-sm btn-{{ $face ? 'warning' : 'primary' }} btn-face-action"
                                            onclick="openRegister('{{ $registrant['user_id'] }}', '{{ addslashes($registrant['name']) }}', '{{ $registrant['user_type'] }}')">
                                        <i class="fas fa-{{ $face ? 'redo' : 'camera' }}"></i>
                                        {{ $face ? 'Ulang' : 'Daftar' }}
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="9" class="text-center text-muted py-4">Belum ada data {{ strtolower($subjectLabel) }} yang bisa diregistrasi.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endif

<div class="modal fade" id="modalRegister" tabindex="-1" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable face-register-modal">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white py-2">
                <h5 class="modal-title"><i class="fas fa-camera"></i> Registrasi Wajah - <span id="modalUserName"></span></h5>
                <button type="button" class="close text-white" onclick="closeRegister()"><span>&times;</span></button>
            </div>
            <div class="modal-body p-0">
                <div class="face-register-duplicate-overlay d-none" id="duplicateFaceModal" role="alert" aria-live="assertive">
                    <div class="face-register-duplicate-card">
                        <div class="d-flex align-items-start justify-content-between">
                            <div>
                                <div class="text-danger font-weight-bold mb-2"><i class="fas fa-user-shield mr-2"></i>Wajah Sudah Terdaftar</div>
                                <h5 class="mb-2">Registrasi dihentikan untuk mencegah duplikasi akun.</h5>
                                <p class="mb-0 text-muted" id="duplicateFaceModalText"></p>
                            </div>
                            <button type="button" class="close ml-3" aria-label="Tutup" onclick="hideDuplicateFaceModal()">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="mt-3 d-flex flex-wrap align-items-center">
                            <span class="badge badge-danger px-3 py-2 mr-2 mb-2">Pemeriksaan wajah aktif</span>
                            <span class="text-muted small mb-2">Gunakan akun yang sesuai atau hubungi admin bila identitas tidak cocok.</span>
                        </div>
                        <div class="mt-3">
                            <button type="button" class="btn btn-danger btn-sm" onclick="hideDuplicateFaceModal()">
                                <i class="fas fa-check mr-1"></i> Saya Mengerti
                            </button>
                        </div>
                    </div>
                </div>
                <div class="face-register-result-overlay d-none" id="registrationResultModal" role="alertdialog" aria-live="assertive">
                    <div class="face-register-result-card">
                        <div class="face-register-result-icon" id="registrationResultIcon">
                            <i class="fas fa-spinner fa-spin"></i>
                        </div>
                        <h4 class="mb-2" id="registrationResultTitle">Menyimpan registrasi wajah</h4>
                        <p class="text-muted mb-0" id="registrationResultMessage">Mohon tunggu, data wajah sedang dikirim ke server.</p>
                        <div class="face-register-result-meta mt-3" id="registrationResultMeta"></div>
                        <div class="mt-4 d-flex flex-column flex-sm-row justify-content-center">
                            <button type="button" class="btn btn-secondary mr-sm-2 mb-2 mb-sm-0 d-none" id="registrationResultRetry" onclick="retryRegistrationAfterFailure()">
                                <i class="fas fa-redo mr-1"></i> Ulangi Registrasi
                            </button>
                            <button type="button" class="btn btn-primary d-none" id="registrationResultClose" onclick="finishRegistrationResult()">
                                <i class="fas fa-sync-alt mr-1"></i> Refresh Halaman
                            </button>
                        </div>
                    </div>
                </div>
                <div class="face-register-modal__info px-3 py-2 border-bottom">
                    <div class="small text-muted d-flex flex-wrap align-items-center">
                        <span class="mr-3 mb-1"><i class="fas fa-mobile-alt mr-1"></i> Tampilan menyesuaikan perangkat</span>
                        <span class="mr-3 mb-1"><i class="fas fa-lightbulb mr-1"></i> Gunakan cahaya dari depan</span>
                        <span class="mb-1"><i class="fas fa-user-check mr-1"></i> Simpan lalu tunggu verifikasi admin</span>
                    </div>
                </div>
                <div class="row no-gutters">
                    <div class="col-md-8 position-relative face-register-camera-panel">
                        <div id="loadingOverlay" style="position:absolute; inset:0; background:rgba(0,0,0,0.8); display:flex; flex-direction:column; align-items:center; justify-content:center; z-index:10;">
                            <div class="spinner-border text-info mb-3" role="status"></div>
                            <p class="text-white" id="loadingText">Memuat model face detection...</p>
                        </div>
                        <video id="videoElement" autoplay playsinline style="width:100%; height:100%; object-fit:cover; transform:scaleX(-1);"></video>
                        <canvas id="overlayCanvas" style="position:absolute; top:0; left:0; width:100%; height:100%; transform:scaleX(-1);"></canvas>
                        <div id="stepInstruction" class="face-register-step-instruction" style="position:absolute; top:15px; left:50%; transform:translateX(-50%); background:rgba(0,0,0,0.7); color:#00e5ff; padding:10px 25px; border-radius:25px; font-size:1.1rem; font-weight:600; text-align:center; z-index:5; display:none;">
                            <i class="fas fa-arrow-right" id="stepIcon"></i>
                            <span id="stepText">Lihat ke kamera</span>
                        </div>
                        <div id="autoCaptureIndicator" style="position:absolute; top:50%; left:50%; transform:translate(-50%,-50%); z-index:6; display:none;">
                            <svg width="80" height="80" viewBox="0 0 80 80">
                                <circle cx="40" cy="40" r="35" stroke="#333" stroke-width="4" fill="none" opacity="0.3"/>
                                <circle id="countdownCircle" cx="40" cy="40" r="35" stroke="#00e676" stroke-width="4" fill="none" stroke-dasharray="220" stroke-dashoffset="220" stroke-linecap="round" style="transition: stroke-dashoffset 1.5s linear; transform: rotate(-90deg); transform-origin: center;"/>
                            </svg>
                        </div>
                        <div id="faceStatus" class="face-register-status" style="position:absolute; bottom:15px; left:50%; transform:translateX(-50%); background:rgba(0,0,0,0.7); color:#aaa; padding:8px 20px; border-radius:20px; font-size:0.9rem; z-index:5;">
                            <i class="fas fa-video"></i> Menunggu...
                        </div>
                    </div>
                    <div class="col-md-4 face-register-side-panel" style="background:#f8f9fa;">
                        <div class="p-3">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <h6 class="mb-0"><i class="fas fa-list-ol"></i> Langkah Registrasi</h6>
                                <span class="badge badge-info">5 tahap</span>
                            </div>
                            <p class="text-muted small mb-3"><i class="fas fa-magic"></i> Auto-capture saat wajah stabil (~1.5s)</p>
                            @for($i = 0; $i < 5; $i++)
                                <div class="step-item" id="step-{{ $i }}">
                                    <div class="d-flex align-items-center p-2 mb-1 rounded" style="background:#fff; border:2px solid #ddd;">
                                        <div class="step-number mr-2" style="width:26px; height:26px; border-radius:50%; background:#6c757d; color:#fff; display:flex; align-items:center; justify-content:center; font-weight:700; font-size:0.85rem;">{{ $i+1 }}</div>
                                        <div class="face-register-step-copy">
                                            <strong class="step-title" style="font-size:0.9rem;">Langkah {{ $i + 1 }}</strong><br>
                                            <small class="text-muted step-desc">Menyiapkan tantangan liveness...</small>
                                        </div>
                                        <div class="ml-auto step-check" style="display:none;"><i class="fas fa-check-circle text-success"></i></div>
                                    </div>
                                </div>
                            @endfor
                            <div class="mt-2">
                                <div class="progress" style="height:6px;"><div class="progress-bar bg-success" id="progressBar" style="width:0%"></div></div>
                                <small class="text-muted" id="progressText">0 / 5 selesai</small>
                            </div>
                            <div class="alert alert-danger mt-3 mb-0 d-none" id="duplicateFaceAlert">
                                <div class="font-weight-bold mb-1"><i class="fas fa-user-shield mr-1"></i> Wajah Sudah Terdaftar</div>
                                <div class="small mb-0" id="duplicateFaceAlertText"></div>
                            </div>
                            <button class="btn btn-secondary btn-sm btn-block mt-2 d-none" id="btnReset" onclick="resetRegistration()"><i class="fas fa-redo"></i> Ulangi</button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer py-2">
                <button type="button" class="btn btn-secondary" onclick="closeRegister()"><i class="fas fa-times"></i> Batal</button>
            </div>
        </div>
    </div>
</div>
 </div>
@stop

@section('css')
<style>
    .face-recognition-module .face-register-hero {
        border-radius: 1rem;
        overflow: hidden;
    }
    .face-register-hero__tips {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
    }
    .face-register-checklist > div + div {
        margin-top: 0.35rem;
    }
    .face-self-card,
    .face-register-modal .modal-content {
        border-radius: 1rem;
        overflow: hidden;
    }
    .face-register-modal .modal-body {
        position: relative;
    }
    .face-self-card__avatar {
        width: 92px;
        height: 92px;
        object-fit: cover;
        border: 4px solid rgba(0, 123, 255, 0.08);
    }
    .face-self-status,
    .face-self-metric {
        background: #f8fbff;
        border: 1px solid rgba(0, 0, 0, 0.05);
        border-radius: 0.85rem;
        padding: 0.85rem 1rem;
    }
    .btn-face-action {
        min-width: 160px;
    }
    .face-register-modal__info {
        background: #f8fbff;
    }
    .face-register-duplicate-overlay {
        position: absolute;
        inset: 0;
        z-index: 25;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 1rem;
        background: rgba(17, 24, 39, 0.72);
    }
    .face-register-duplicate-card {
        width: min(100%, 30rem);
        background: #fff;
        border-radius: 1rem;
        box-shadow: 0 1rem 3rem rgba(15, 23, 42, 0.22);
        padding: 1.25rem;
    }
    .face-register-duplicate-card .close {
        color: #6c757d;
        opacity: 1;
    }
    .face-register-result-overlay {
        position: absolute;
        inset: 0;
        z-index: 30;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 1rem;
        background: rgba(15, 23, 42, 0.72);
        backdrop-filter: blur(4px);
    }
    .face-register-result-card {
        width: min(100%, 31rem);
        background: #fff;
        border-radius: 1.1rem;
        box-shadow: 0 1.4rem 4rem rgba(15, 23, 42, 0.26);
        padding: 1.6rem;
        text-align: center;
    }
    .face-register-result-icon {
        width: 5.4rem;
        height: 5.4rem;
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 1rem;
        font-size: 2.1rem;
        color: #2563eb;
        background: #eff6ff;
        border: 0.35rem solid #dbeafe;
    }
    .face-register-result-icon.is-success {
        color: #16a34a;
        background: #f0fdf4;
        border-color: #dcfce7;
    }
    .face-register-result-icon.is-error {
        color: #dc2626;
        background: #fef2f2;
        border-color: #fee2e2;
    }
    .face-register-result-meta {
        display: inline-flex;
        flex-wrap: wrap;
        justify-content: center;
        gap: 0.45rem;
    }
    .face-register-result-meta .badge {
        padding: 0.45rem 0.7rem;
        border-radius: 999px;
        font-size: 0.8rem;
    }
    .face-register-camera-panel {
        background: #000;
        min-height: 400px;
    }
    .face-register-side-panel {
        border-left: 1px solid rgba(0, 0, 0, 0.06);
    }
    .face-register-step-instruction,
    .face-register-status {
        max-width: calc(100% - 1.5rem);
        width: max-content;
    }
    .face-register-step-copy {
        min-width: 0;
    }
    .step-item.active .d-flex { border-color: #007bff !important; background: #e8f4fd !important; }
    .step-item.done .d-flex { border-color: #28a745 !important; }
    .step-item.done .step-number { background: #28a745 !important; }
    .step-item.active .step-number { background: #007bff !important; animation: pulse 1.5s infinite; }
    .step-item.capturing .d-flex { border-color: #ffc107 !important; background: #fff8e1 !important; }
    .step-item.capturing .step-number { background: #ffc107 !important; }
    @keyframes pulse { 0%,100% { transform:scale(1); } 50% { transform:scale(1.15); } }

    @media (max-width: 991.98px) {
        .face-register-camera-panel {
            min-height: 320px;
        }
        .face-register-side-panel {
            border-left: 0;
            border-top: 1px solid rgba(0, 0, 0, 0.06);
        }
    }

    @media (max-width: 767.98px) {
        .face-register-modal {
            margin: 0;
            max-width: 100%;
            height: 100%;
        }
        .face-register-modal .modal-content {
            min-height: 100vh;
            border-radius: 0;
        }
        .face-register-camera-panel {
            min-height: 45vh;
        }
        .face-register-duplicate-card {
            padding: 1rem;
        }
        .face-register-step-instruction {
            font-size: 0.9rem !important;
            padding: 0.6rem 0.9rem !important;
            top: 0.75rem !important;
        }
        .face-register-status {
            font-size: 0.8rem !important;
            left: 0.75rem !important;
            right: 0.75rem;
            bottom: 0.75rem !important;
            transform: none !important;
            width: auto;
            text-align: center;
        }
        .btn-face-action {
            width: 100%;
        }
        #tabelFaceRegister thead {
            display: none;
        }
        #tabelFaceRegister,
        #tabelFaceRegister tbody,
        #tabelFaceRegister tr,
        #tabelFaceRegister td {
            display: block;
            width: 100%;
        }
        #tabelFaceRegister tr {
            border: 1px solid #e9ecef;
            border-radius: 0.85rem;
            background: #fff;
            padding: 0.85rem;
            margin-bottom: 0.85rem;
            box-shadow: 0 0.35rem 1rem rgba(15, 23, 42, 0.05);
        }
        #tabelFaceRegister td {
            border: 0;
            padding: 0.35rem 0 0.35rem 7.2rem !important;
            position: relative;
            min-height: 2rem;
        }
        #tabelFaceRegister td::before {
            content: attr(data-label);
            position: absolute;
            left: 0;
            top: 0.35rem;
            width: 6.4rem;
            color: #6c757d;
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
        }
        #tabelFaceRegister td[data-label="Nama"],
        #tabelFaceRegister td[data-label="Aksi"] {
            padding-left: 0 !important;
        }
        #tabelFaceRegister td[data-label="Nama"]::before,
        #tabelFaceRegister td[data-label="Aksi"]::before {
            display: none;
        }
    }
</style>
@stop

@section('js')
<script src="{{ asset('vendor/face-api/face-api.min.js') }}"></script>
<script>
let selectedUserId = null, selectedUserName = '', selectedUserType = '{{ $selectedType }}', currentStep = -1;
const totalSteps = 5, STABLE_DURATION_MS = 1500, storeUrl = @json($storeUrl), initialSelection = @json($initialSelection), canManageAll = @json($canManageAll);
let capturedDescriptors = [], capturedAngles = [], capturedPhotos = [], isDetecting = false, modelsLoaded = false, cameraStream = null, faceStableStart = null, autoCapturing = false, blinkCount = 0, earHistory = [], eyeWasClosed = false;
let STEPS = [];
let livenessSummary = {};
let passiveSamples = [];
let gestureSamples = [];
let stepStartedAt = null;
let registrationStartedAt = null;
let baselineMetrics = null;
let blinkCloseFrames = 0;
let registrationFinished = false;
let guidanceVoiceEnabled = true;
let guidanceSpeechSupported = 'speechSynthesis' in window;
let guidanceAudioContext = null;
const REQUIRED_STEP_TYPES = ['frontal', 'kedip', 'senyum'];
const STEP_LIBRARY = {
    frontal: { angle: 'frontal', title: 'Wajah Depan', text: 'Lihat lurus ke kamera', icon: 'fa-user', description: 'Tatap kamera dengan wajah penuh dan stabil.' },
    kanan: { angle: 'kanan', title: 'Toleh Kanan', text: 'Putar kepala ke KANAN', icon: 'fa-arrow-right', description: 'Putar kepala ke kanan Anda, jangan hanya menggeser foto.' },
    kiri: { angle: 'kiri', title: 'Toleh Kiri', text: 'Putar kepala ke KIRI', icon: 'fa-arrow-left', description: 'Putar kepala ke kiri Anda, jangan hanya menggeser foto.' },
    senyum: { angle: 'senyum', title: 'Senyum', text: 'Tersenyum natural', icon: 'fa-smile', description: 'Beri senyum natural agar perubahan ekspresi terbaca.' },
    kedip: { angle: 'kedip', title: 'Kedipkan Mata', text: 'Kedipkan mata 1 kali', icon: 'fa-eye', description: 'Kedip sungguhan diperlukan untuk liveness.' },
};

function shuffleArray(items) {
    const arr = [...items];
    for (let i = arr.length - 1; i > 0; i--) {
        const j = Math.floor(Math.random() * (i + 1));
        [arr[i], arr[j]] = [arr[j], arr[i]];
    }
    return arr;
}

function buildStepSequence() {
    const challengePool = shuffleArray(['kanan', 'kiri', 'senyum', 'kedip']);
    return ['frontal', ...challengePool].map(key => ({ ...STEP_LIBRARY[key] }));
}

function renderStepList() {
    document.querySelectorAll('.step-item').forEach((el, index) => {
        const step = STEPS[index];
        const titleEl = el.querySelector('.step-title');
        const descEl = el.querySelector('.step-desc');
        if (!step || !titleEl || !descEl) return;
        el.dataset.angle = step.angle;
        titleEl.textContent = step.title;
        descEl.textContent = step.description;
    });
}

$(function() {
    if ($('#tabelFaceRegister').length) {
        const table = $('#tabelFaceRegister').DataTable({
            language: { url: 'https://cdn.datatables.net/plug-ins/1.13.7/i18n/id.json' },
            pageLength: 25,
            order: [[1, 'asc']],
            columnDefs: [{ orderable: false, targets: [8] }],
            scrollX: true,
            autoWidth: false,
        });
        let activeFilter = '';
        $.fn.dataTable.ext.search.push(function(settings, data) {
            if (settings.nTable.id !== 'tabelFaceRegister' || !activeFilter) return true;
            return data[3].indexOf(activeFilter) !== -1;
        });
        $('#statusFilter').on('click', 'button', function() {
            $('#statusFilter button').removeClass('active');
            $(this).addClass('active');
            activeFilter = $(this).data('filter');
            table.draw();
        });
    }

    if (initialSelection && canManageAll) {
        openRegister(initialSelection.user_id, initialSelection.name, initialSelection.user_type);
    }
});

function openRegister(userId, userName, userType) {
    selectedUserId = userId;
    selectedUserName = userName;
    selectedUserType = userType;
    document.getElementById('modalUserName').textContent = userName;
    $('#modalRegister').modal('show');
    if (!modelsLoaded) loadModels(); else startCameraAndRegister();
}
function closeRegister() {
    if (registrationFinished) {
        window.location.reload();
        return;
    }
    isDetecting = false; stopCamera(); resetUI(); $('#modalRegister').modal('hide');
}
function stopCamera() { if (cameraStream) { cameraStream.getTracks().forEach(t => t.stop()); cameraStream = null; } const video = document.getElementById('videoElement'); if (video) video.srcObject = null; }
async function loadModels() {
    const MODEL_URL = '{{ asset("vendor/face-api/models") }}';
    try {
        setLoadingText('Memuat model deteksi wajah...'); await faceapi.nets.tinyFaceDetector.loadFromUri(MODEL_URL);
        setLoadingText('Memuat model landmark wajah...'); await faceapi.nets.faceLandmark68TinyNet.loadFromUri(MODEL_URL);
        setLoadingText('Memuat model pengenalan wajah...'); await faceapi.nets.faceRecognitionNet.loadFromUri(MODEL_URL);
        modelsLoaded = true; startCameraAndRegister();
    } catch (err) { setLoadingText('Error: ' + err.message); }
}
async function startCameraAndRegister() {
    setLoadingText('Menyalakan kamera...'); document.getElementById('loadingOverlay').style.display = 'flex';
    const video = document.getElementById('videoElement');
    try {
        cameraStream = await navigator.mediaDevices.getUserMedia({ video: { width: { ideal: 1280 }, height: { ideal: 720 }, facingMode: 'user' } });
        video.srcObject = cameraStream; await new Promise(r => { video.onloadedmetadata = r; }); document.getElementById('loadingOverlay').style.display = 'none'; beginAutoRegistration();
    } catch (err) { setLoadingText('Kamera tidak tersedia: ' + err.message); }
}
function setLoadingText(text) { document.getElementById('loadingText').textContent = text; }
function speakGuidance(text) {
    if (!guidanceVoiceEnabled || !guidanceSpeechSupported || !text) return;
    window.speechSynthesis.cancel();
    const utterance = new SpeechSynthesisUtterance(text);
    utterance.lang = 'id-ID';
    utterance.rate = 0.95;
    utterance.pitch = 1;
    const indonesianVoice = window.speechSynthesis.getVoices().find(voice => /^id([_-]|$)/i.test(voice.lang));
    if (indonesianVoice) utterance.voice = indonesianVoice;
    window.speechSynthesis.speak(utterance);
}
function playStepCompleteTone() {
    try {
        guidanceAudioContext ||= new (window.AudioContext || window.webkitAudioContext)();
        const oscillator = guidanceAudioContext.createOscillator();
        const gain = guidanceAudioContext.createGain();
        oscillator.type = 'sine'; oscillator.frequency.setValueAtTime(880, guidanceAudioContext.currentTime);
        gain.gain.setValueAtTime(0.001, guidanceAudioContext.currentTime);
        gain.gain.exponentialRampToValueAtTime(0.12, guidanceAudioContext.currentTime + 0.015);
        gain.gain.exponentialRampToValueAtTime(0.001, guidanceAudioContext.currentTime + 0.22);
        oscillator.connect(gain).connect(guidanceAudioContext.destination);
        oscillator.start(); oscillator.stop(guidanceAudioContext.currentTime + 0.24);
    } catch (_) { /* Audio cue is optional; browser voice remains available. */ }
}
function beginAutoRegistration() {
    STEPS = buildStepSequence();
    renderStepList();
    capturedDescriptors = [];
    capturedAngles = [];
    capturedPhotos = [];
    currentStep = 0;
    blinkCount = 0;
    blinkCloseFrames = 0;
    earHistory = [];
    eyeWasClosed = false;
    faceStableStart = null;
    autoCapturing = false;
    baselineMetrics = null;
    passiveSamples = [];
    gestureSamples = [];
    registrationStartedAt = Date.now();
    registrationFinished = false;
    stepStartedAt = Date.now();
    livenessSummary = {
        challenge_order: STEPS.map(step => step.angle),
        completed_steps: [],
        blink_count: 0,
        max_blink_close_frames: 0,
        yaw_min: null,
        yaw_max: null,
        smile_min: null,
        smile_max: null,
        passive_motion_score: 0,
        gesture_motion_score: 0,
        center_shift_score: 0,
        total_duration_ms: 0,
    };
    resetUI();
    document.getElementById('btnReset').classList.remove('d-none');
    updateStepUI();
    startDetectionLoop();
}
function resetUI() {
    document.querySelectorAll('.step-item').forEach(el => { el.classList.remove('active', 'done', 'capturing'); el.querySelector('.step-check').style.display = 'none'; });
    document.getElementById('progressBar').style.width = '0%'; document.getElementById('progressText').textContent = '0 / 5 selesai'; document.getElementById('stepInstruction').style.display = 'none'; document.getElementById('stepInstruction').style.background = 'rgba(0,0,0,0.7)'; document.getElementById('stepInstruction').style.color = '#00e5ff'; document.getElementById('btnReset').classList.add('d-none'); hideCountdownRing();
    document.getElementById('duplicateFaceAlert').classList.add('d-none');
    document.getElementById('duplicateFaceAlertText').textContent = '';
    hideDuplicateFaceModal();
    hideRegistrationResult();
    const canvas = document.getElementById('overlayCanvas'); if (canvas) canvas.getContext('2d').clearRect(0, 0, canvas.width, canvas.height);
}
function updateStepUI() {
    if (currentStep >= totalSteps) return;
    document.querySelectorAll('.step-item').forEach((el, i) => { el.classList.remove('active', 'capturing'); if (i < currentStep) el.classList.add('done'); else if (i === currentStep) el.classList.add('active'); });
    const step = STEPS[currentStep];
    document.getElementById('stepInstruction').style.display = 'block';
    document.getElementById('stepIcon').className = 'fas ' + step.icon + ' mr-2';
    document.getElementById('stepText').textContent = step.text;
    speakGuidance(`${step.title}. ${step.text}.`);
    if (step.angle === 'kedip') {
        blinkCount = 0;
        blinkCloseFrames = 0;
        earHistory = [];
        eyeWasClosed = false;
    }
    stepStartedAt = Date.now();
    faceStableStart = null;
    hideCountdownRing();
}
function startDetectionLoop() {
    isDetecting = true;
    const video = document.getElementById('videoElement'), canvas = document.getElementById('overlayCanvas'), ctx = canvas.getContext('2d'), options = new faceapi.TinyFaceDetectorOptions({ inputSize: 320, scoreThreshold: 0.5 });
    async function detect() {
        if (!isDetecting || video.paused || currentStep < 0) return;
        canvas.width = video.videoWidth || video.clientWidth; canvas.height = video.videoHeight || video.clientHeight;
        const detection = await faceapi.detectSingleFace(video, options).withFaceLandmarks(true); ctx.clearRect(0, 0, canvas.width, canvas.height);
        if (detection) {
            const dims = faceapi.matchDimensions(canvas, video, true), resized = faceapi.resizeResults(detection, dims);
            resized.landmarks.positions.forEach(pt => { ctx.beginPath(); ctx.arc(pt.x, pt.y, 2, 0, 2 * Math.PI); ctx.fillStyle = '#00e5ff'; ctx.globalAlpha = 0.7; ctx.fill(); });
            ctx.globalAlpha = 1; const box = resized.detection.box; ctx.strokeStyle = '#00e5ff'; ctx.lineWidth = 2; ctx.strokeRect(box.x, box.y, box.width, box.height);
            const liveMetrics = collectLivenessMetrics(detection.landmarks, box, canvas.width, canvas.height);
            setFaceStatus(`Wajah terdeteksi (${(detection.detection.score * 100).toFixed(0)}%)`, true);
            if (currentStep < totalSteps && !autoCapturing) {
                if (STEPS[currentStep].angle === 'kedip') detectBlink(detection.landmarks);
                else {
                    const poseOk = validatePose(detection.landmarks, STEPS[currentStep].angle, liveMetrics);
                    if (poseOk) {
                        if (!faceStableStart) { faceStableStart = Date.now(); showCountdownRing(); }
                        else if (Date.now() - faceStableStart >= STABLE_DURATION_MS) { autoCapturing = true; await doCapture(); autoCapturing = false; }
                    } else if (faceStableStart) { faceStableStart = null; hideCountdownRing(); }
                }
            }
        } else { setFaceStatus('Arahkan wajah ke kamera', false); if (faceStableStart) { faceStableStart = null; hideCountdownRing(); } }
        const delay = (currentStep < totalSteps && STEPS[currentStep]?.angle === 'kedip') ? 60 : 150; requestAnimationFrame(() => setTimeout(detect, delay));
    }
    detect();
}
function setFaceStatus(text, ok) { const el = document.getElementById('faceStatus'); el.style.color = ok ? '#00e676' : '#ff5252'; el.innerHTML = `<i class="fas fa-${ok ? 'check-circle' : 'exclamation-circle'}"></i> ${text}`; }
function validatePose(landmarks, angle, liveMetrics) {
    const { yawRatio, smileRatio, passiveMotion, gestureMotion } = liveMetrics;
    const yawDelta = baselineMetrics ? Math.abs(yawRatio - baselineMetrics.yawRatio) : 0;
    const smileDelta = baselineMetrics ? smileRatio - baselineMetrics.smileRatio : 0;
    const passiveOk = passiveMotion >= 0.018 || gestureMotion >= 0.014;
    if (angle === 'frontal') {
        const ok = yawRatio > 0.86 && yawRatio < 1.16 && passiveOk;
        setFaceStatus(ok ? 'Bagus! Tetap menghadap depan...' : `Pastikan wajah asli dan stabil, jangan hanya menggeser foto.`, ok);
        return ok;
    }
    if (angle === 'kanan') {
        const ok = yawRatio < 0.82 && yawDelta >= 0.14;
        setFaceStatus(ok ? 'Bagus! Tahan posisi kepala...' : `Putar kepala ke KANAN Anda sungguhan.`, ok);
        return ok;
    }
    if (angle === 'kiri') {
        const ok = yawRatio > 1.22 && yawDelta >= 0.14;
        setFaceStatus(ok ? 'Bagus! Tahan posisi kepala...' : `Putar kepala ke KIRI Anda sungguhan.`, ok);
        return ok;
    }
    if (angle === 'senyum') {
        const ok = smileRatio > 0.38 && smileDelta >= 0.028;
        setFaceStatus(ok ? 'Senyum terdeteksi! Tahan...' : `Tersenyum lebih natural agar perubahan ekspresi terbaca.`, ok);
        return ok;
    }
    return true;
}
function collectLivenessMetrics(landmarks, box, canvasWidth, canvasHeight) {
    const pts = landmarks.positions;
    const noseTip = pts[30], jawLeft = pts[0], jawRight = pts[16], mouthL = pts[48], mouthR = pts[54];
    const dL = Math.sqrt((noseTip.x - jawLeft.x) ** 2 + (noseTip.y - jawLeft.y) ** 2);
    const dR = Math.sqrt((noseTip.x - jawRight.x) ** 2 + (noseTip.y - jawRight.y) ** 2);
    const yawRatio = dL / Math.max(dR, 0.001);
    const mouthW = Math.sqrt((mouthR.x - mouthL.x) ** 2 + (mouthR.y - mouthL.y) ** 2);
    const jawW = Math.sqrt((jawRight.x - jawLeft.x) ** 2 + (jawRight.y - jawLeft.y) ** 2);
    const smileRatio = mouthW / Math.max(jawW, 0.001);
    const centerX = (box.x + (box.width / 2)) / Math.max(canvasWidth, 1);
    const centerY = (box.y + (box.height / 2)) / Math.max(canvasHeight, 1);
    const areaRatio = (box.width * box.height) / Math.max(canvasWidth * canvasHeight, 1);

    const sample = { time: Date.now(), yawRatio, smileRatio, centerX, centerY, areaRatio };
    passiveSamples.push(sample);
    if (passiveSamples.length > 24) passiveSamples.shift();

    livenessSummary.yaw_min = livenessSummary.yaw_min === null ? yawRatio : Math.min(livenessSummary.yaw_min, yawRatio);
    livenessSummary.yaw_max = livenessSummary.yaw_max === null ? yawRatio : Math.max(livenessSummary.yaw_max, yawRatio);
    livenessSummary.smile_min = livenessSummary.smile_min === null ? smileRatio : Math.min(livenessSummary.smile_min, smileRatio);
    livenessSummary.smile_max = livenessSummary.smile_max === null ? smileRatio : Math.max(livenessSummary.smile_max, smileRatio);

    const passiveMotion = computeRecentRange(passiveSamples, 'areaRatio') + computeRecentRange(passiveSamples, 'centerX');
    const gestureMotion = computeRecentRange(passiveSamples, 'yawRatio') + computeRecentRange(passiveSamples, 'smileRatio');
    const centerShift = computeRecentRange(passiveSamples, 'centerY');
    livenessSummary.passive_motion_score = Math.max(livenessSummary.passive_motion_score, passiveMotion);
    livenessSummary.gesture_motion_score = Math.max(livenessSummary.gesture_motion_score, gestureMotion);
    livenessSummary.center_shift_score = Math.max(livenessSummary.center_shift_score, centerShift);

    return { yawRatio, smileRatio, passiveMotion, gestureMotion, centerShift, areaRatio, centerX, centerY };
}
function computeRecentRange(samples, key) {
    if (samples.length < 2) return 0;
    const values = samples.slice(-12).map(sample => sample[key]);
    return Math.max(...values) - Math.min(...values);
}
function showCountdownRing() { const ring = document.getElementById('autoCaptureIndicator'), circle = document.getElementById('countdownCircle'); ring.style.display = 'block'; circle.style.transition = 'none'; circle.style.strokeDashoffset = '220'; requestAnimationFrame(() => { circle.style.transition = `stroke-dashoffset ${STABLE_DURATION_MS}ms linear`; circle.style.strokeDashoffset = '0'; }); if (currentStep >= 0 && currentStep < totalSteps) document.getElementById('step-' + currentStep).classList.add('capturing'); }
function hideCountdownRing() { document.getElementById('autoCaptureIndicator').style.display = 'none'; const c = document.getElementById('countdownCircle'); c.style.transition = 'none'; c.style.strokeDashoffset = '220'; if (currentStep >= 0 && currentStep < totalSteps) document.getElementById('step-' + currentStep).classList.remove('capturing'); }
function detectBlink(landmarks) {
    const leftEye = landmarks.getLeftEye(), rightEye = landmarks.getRightEye(), rawEar = (eyeAspectRatio(leftEye) + eyeAspectRatio(rightEye)) / 2;
    earHistory.push(rawEar); if (earHistory.length > 3) earHistory.shift(); const ear = earHistory.reduce((a, b) => a + b, 0) / earHistory.length, threshold = 0.26;
    setFaceStatus(`Kedipkan mata! EAR: ${ear.toFixed(3)} ${ear < threshold ? 'TERTUTUP' : 'Terbuka'} (${blinkCount}/1)`, true);
    if (ear < threshold) {
        blinkCloseFrames++;
        if (!eyeWasClosed) eyeWasClosed = true;
    } else if (ear > threshold + 0.03 && eyeWasClosed) {
        eyeWasClosed = false;
        if (blinkCloseFrames >= 2) {
            blinkCount++;
            livenessSummary.blink_count = Math.max(livenessSummary.blink_count, blinkCount);
            livenessSummary.max_blink_close_frames = Math.max(livenessSummary.max_blink_close_frames, blinkCloseFrames);
            document.getElementById('stepText').textContent = `Kedip terdeteksi! (${blinkCount}/1)`;
            if (blinkCount >= 1 && !autoCapturing) {
                autoCapturing = true;
                setTimeout(async () => { await doCaptureWithRetry(); autoCapturing = false; }, 400);
            }
        }
        blinkCloseFrames = 0;
    }
}
function eyeAspectRatio(eye) { const d = (a, b) => Math.sqrt((a.x - b.x) ** 2 + (a.y - b.y) ** 2); return (d(eye[1], eye[5]) + d(eye[2], eye[4])) / (2 * d(eye[0], eye[3])); }
async function doCaptureWithRetry(maxRetries = 3) { for (let i = 0; i < maxRetries; i++) { const ok = await doCapture(); if (ok) return; await new Promise(r => setTimeout(r, 300)); } setFaceStatus('Gagal capture, silakan kedipkan lagi', false); blinkCount = 0; eyeWasClosed = false; earHistory = []; }
function captureVideoFrame(video) {
    const sourceWidth = video.videoWidth || 640;
    const sourceHeight = video.videoHeight || 480;
    const width = 480;
    const height = Math.round(width * (sourceHeight / sourceWidth));
    const canvas = document.createElement('canvas');
    canvas.width = width;
    canvas.height = height;
    const context = canvas.getContext('2d');
    context.translate(width, 0);
    context.scale(-1, 1);
    context.drawImage(video, 0, 0, width, height);
    return canvas.toDataURL('image/jpeg', 0.78);
}
async function doCapture() {
    if (currentStep >= totalSteps) return;
    const video = document.getElementById('videoElement'), opts = new faceapi.TinyFaceDetectorOptions({ inputSize: 416, scoreThreshold: 0.5 }), det = await faceapi.detectSingleFace(video, opts).withFaceLandmarks(true).withFaceDescriptor();
    if (!det) { faceStableStart = null; hideCountdownRing(); return false; }
    const liveMetrics = collectLivenessMetrics(det.landmarks, det.detection.box, video.videoWidth || video.clientWidth, video.videoHeight || video.clientHeight);
    if (!verifyCurrentChallenge(det.landmarks, STEPS[currentStep].angle, liveMetrics)) {
        faceStableStart = null;
        hideCountdownRing();
        return false;
    }
    if (!baselineMetrics && STEPS[currentStep].angle === 'frontal') {
        baselineMetrics = {
            yawRatio: liveMetrics.yawRatio,
            smileRatio: liveMetrics.smileRatio,
        };
    }
    gestureSamples.push({
        angle: STEPS[currentStep].angle,
        yawRatio: liveMetrics.yawRatio,
        smileRatio: liveMetrics.smileRatio,
        duration_ms: Date.now() - stepStartedAt,
    });
    livenessSummary.completed_steps.push(STEPS[currentStep].angle);
    capturedDescriptors.push(Array.from(det.descriptor)); capturedAngles.push(STEPS[currentStep].angle); capturedPhotos.push(captureVideoFrame(video));
    const stepEl = document.getElementById('step-' + currentStep); stepEl.classList.remove('active', 'capturing'); stepEl.classList.add('done'); stepEl.querySelector('.step-check').style.display = 'block'; hideCountdownRing();
    playStepCompleteTone();
    const video2 = document.getElementById('videoElement'); video2.style.outline = '4px solid #00e676'; setTimeout(() => { video2.style.outline = ''; }, 400);
    currentStep++; document.getElementById('progressBar').style.width = ((currentStep / totalSteps) * 100) + '%'; document.getElementById('progressText').textContent = `${currentStep} / ${totalSteps} selesai`;
    if (currentStep >= totalSteps) { document.getElementById('stepInstruction').style.display = 'none'; setFaceStatus('Menyimpan...', true); await saveRegistration(); }
    else { await new Promise(r => setTimeout(r, 500)); updateStepUI(); }
    return true;
}
function verifyCurrentChallenge(landmarks, angle, liveMetrics) {
    if (angle === 'kedip') {
        if (blinkCount < 1) {
            setFaceStatus('Kedip belum terdeteksi jelas. Coba lagi.', false);
            return false;
        }
        return true;
    }
    return validatePose(landmarks, angle, liveMetrics);
}
function hideDuplicateFaceModal() {
    const duplicateModal = document.getElementById('duplicateFaceModal');
    const duplicateModalText = document.getElementById('duplicateFaceModalText');
    if (duplicateModal) duplicateModal.classList.add('d-none');
    if (duplicateModalText) duplicateModalText.textContent = '';
}
async function saveRegistration() {
    const livenessPayload = buildLivenessPayload();
    isDetecting = false;
    hideCountdownRing();
    showRegistrationResult('saving', 'Menyimpan registrasi wajah', 'Mohon tunggu, data wajah sedang dikirim ke server.', {
        captures: capturedDescriptors.length,
        score: computeQualityScore(livenessPayload),
    });
    try {
        const res = await fetch(storeUrl, { method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' }, body: JSON.stringify({ user_id: selectedUserId, user_type: selectedUserType, descriptors: capturedDescriptors, angles: capturedAngles, quality_score: computeQualityScore(livenessPayload), liveness_score: livenessPayload.liveness_score, liveness_summary: livenessPayload, photos: capturedPhotos, photo: capturedPhotos[0] || null }) });
        const result = await res.json().catch(() => ({}));
        if (!res.ok) {
            const validationErrors = result.errors ? Object.values(result.errors).flat().join(', ') : '';
            const message = result.message || validationErrors || 'Registrasi gagal diproses.';
            setFaceStatus(message, false);
            showRegistrationResult('error', 'Registrasi wajah gagal', message, {
                captures: capturedDescriptors.length,
                score: computeQualityScore(livenessPayload),
            });
            return;
        }
        if (result.success) {
            registrationFinished = true;
            document.getElementById('stepInstruction').innerHTML = '<i class="fas fa-check-circle mr-2"></i>Registrasi berhasil!';
            document.getElementById('stepInstruction').style.display = 'block';
            document.getElementById('stepInstruction').style.background = 'rgba(40,167,69,0.9)';
            document.getElementById('stepInstruction').style.color = '#fff';
            setFaceStatus('Tersimpan. Menunggu verifikasi admin.', true);
            showRegistrationResult('success', 'Registrasi wajah berhasil', result.message || 'Data wajah berhasil disimpan dan menunggu verifikasi admin.', {
                captures: result.data?.total_captures || capturedDescriptors.length,
                score: computeQualityScore(livenessPayload),
            });
        } else {
            const message = result.message || 'Server tidak mengembalikan status sukses.';
            setFaceStatus('Gagal: ' + message, false);
            showRegistrationResult('error', 'Registrasi wajah gagal', message, {
                captures: capturedDescriptors.length,
                score: computeQualityScore(livenessPayload),
            });
        }
    } catch (err) {
        setFaceStatus('Error: ' + err.message, false);
        showRegistrationResult('error', 'Koneksi ke server gagal', err.message || 'Periksa koneksi lalu ulangi registrasi.', {
            captures: capturedDescriptors.length,
            score: computeQualityScore(livenessPayload),
        });
    }
}
function showRegistrationResult(type, title, message, meta = {}) {
    const overlay = document.getElementById('registrationResultModal');
    const icon = document.getElementById('registrationResultIcon');
    const retry = document.getElementById('registrationResultRetry');
    const close = document.getElementById('registrationResultClose');
    const metaEl = document.getElementById('registrationResultMeta');

    icon.className = 'face-register-result-icon';
    retry.classList.add('d-none');
    close.classList.add('d-none');

    if (type === 'success') {
        icon.classList.add('is-success');
        icon.innerHTML = '<i class="fas fa-check"></i>';
        close.classList.remove('d-none');
        close.innerHTML = '<i class="fas fa-sync-alt mr-1"></i> Refresh Halaman';
        close.className = 'btn btn-success';
    } else if (type === 'error') {
        icon.classList.add('is-error');
        icon.innerHTML = '<i class="fas fa-times"></i>';
        retry.classList.remove('d-none');
        close.classList.remove('d-none');
        close.innerHTML = '<i class="fas fa-times mr-1"></i> Tutup';
        close.className = 'btn btn-outline-secondary';
    } else {
        icon.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
    }

    document.getElementById('registrationResultTitle').textContent = title;
    document.getElementById('registrationResultMessage').textContent = message;
    metaEl.innerHTML = [
        meta.captures ? `<span class="badge badge-light"><i class="fas fa-camera mr-1"></i>${meta.captures} capture</span>` : '',
        meta.score ? `<span class="badge badge-light"><i class="fas fa-shield-alt mr-1"></i>Quality ${meta.score}%</span>` : '',
        selectedUserName ? `<span class="badge badge-light"><i class="fas fa-user mr-1"></i>${escapeHtml(selectedUserName)}</span>` : '',
    ].join('');
    overlay.classList.remove('d-none');
}
function hideRegistrationResult() {
    const overlay = document.getElementById('registrationResultModal');
    if (overlay) overlay.classList.add('d-none');
}
function retryRegistrationAfterFailure() {
    hideRegistrationResult();
    resetRegistration();
}
function finishRegistrationResult() {
    if (registrationFinished) {
        window.location.reload();
        return;
    }
    hideRegistrationResult();
}
function escapeHtml(value) {
    return String(value ?? '').replace(/[&<>"']/g, char => ({
        '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;'
    }[char]));
}
function buildLivenessPayload() {
    livenessSummary.total_duration_ms = Date.now() - (registrationStartedAt || Date.now());
    const yawSpan = (livenessSummary.yaw_max ?? 0) - (livenessSummary.yaw_min ?? 0);
    const smileDelta = (livenessSummary.smile_max ?? 0) - (livenessSummary.smile_min ?? 0);
    const completed = Array.from(new Set(livenessSummary.completed_steps));
    const hasDirectionalTurn = completed.includes('kanan') || completed.includes('kiri');
    const durationScore = Math.min(livenessSummary.total_duration_ms / 7000, 1) * 20;
    const yawScore = Math.min(yawSpan / 0.5, 1) * 20;
    const smileScore = Math.min(Math.max(smileDelta, 0) / 0.08, 1) * 15;
    const blinkScore = livenessSummary.blink_count >= 1 ? 20 : 0;
    const passiveScore = Math.min(livenessSummary.passive_motion_score / 0.03, 1) * 10;
    const gestureScore = Math.min(livenessSummary.gesture_motion_score / 0.18, 1) * 10;
    const coverageScore = completed.length === totalSteps && REQUIRED_STEP_TYPES.every(step => completed.includes(step)) && hasDirectionalTurn ? 5 : 0;
    const livenessScore = Math.round(durationScore + yawScore + smileScore + blinkScore + passiveScore + gestureScore + coverageScore);

    return {
        ...livenessSummary,
        completed_steps: completed,
        challenge_count: STEPS.length,
        yaw_span: Number(yawSpan.toFixed(4)),
        smile_delta: Number(smileDelta.toFixed(4)),
        has_directional_turn: hasDirectionalTurn,
        liveness_score: livenessScore,
    };
}
function computeQualityScore(livenessPayload) {
    const baseScore = Math.min(capturedDescriptors.length * 16, 70);
    const bonus = Math.min(Math.round((livenessPayload.liveness_score || 0) * 0.3), 30);
    return Math.min(baseScore + bonus, 100);
}
function resetRegistration() { isDetecting = false; autoCapturing = false; capturedDescriptors = []; capturedAngles = []; capturedPhotos = []; currentStep = -1; blinkCount = 0; blinkCloseFrames = 0; earHistory = []; eyeWasClosed = false; faceStableStart = null; baselineMetrics = null; passiveSamples = []; gestureSamples = []; registrationFinished = false; resetUI(); setTimeout(() => beginAutoRegistration(), 300); }
</script>
@stop
