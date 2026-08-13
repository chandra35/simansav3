@extends('adminlte::page')

@section('title', 'Verifikasi Wajah')
@section('plugins.Datatables', true)
@section('plugins.DatatablesPlugins', true)
@section('plugins.Sweetalert2', true)

@section('content_header')
    <div class="row mb-2">
        <div class="col-sm-6"><h1><i class="fas fa-user-check text-primary"></i> Verifikasi Wajah {{ $subjectLabel }}</h1></div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item">Data Wajah</li>
                <li class="breadcrumb-item active">Verifikasi</li>
            </ol>
        </div>
    </div>
@stop

@section('content')
<div class="face-recognition-verification">
<div class="card bg-gradient-primary text-white mb-4">
    <div class="card-body">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <div class="text-uppercase small font-weight-bold text-white-50 mb-1">Face Approval</div>
                <h2 class="h4 text-white mb-2">Verifikasi Data Wajah {{ $subjectLabel }}</h2>
                <p class="text-white-50 mb-0">
                    Setujui hanya hasil registrasi yang jelas dan stabil. Data approved menjadi identitas biometrik resmi sesuai jenis responden.
                </p>
            </div>
            <div class="col-lg-4 mt-3 mt-lg-0 text-lg-right">
                <div class="small text-white-50 text-uppercase font-weight-bold mb-2">Jenis responden</div>
                <div class="btn-group" role="group" aria-label="Pilih jenis responden">
                    @foreach($typeOptions as $typeKey => $typeName)
                        <a class="btn btn-{{ $selectedType === $typeKey ? 'light' : 'outline-light' }}" href="{{ route('admin.absensi.face-verification', ['type' => $typeKey]) }}">
                            <i class="fas fa-{{ $typeKey === 'gtk' ? 'chalkboard-teacher' : 'user-graduate' }} mr-1"></i>{{ $typeName }}
                        </a>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row mb-4">
    <div class="col-md-6 col-xl-3 mb-3 mb-xl-0"><div class="card card-outline card-info h-100 mb-0"><div class="card-body py-3"><div class="text-muted small text-uppercase font-weight-bold">Total Terdaftar</div><h3 class="text-info mb-0">{{ $allFaces->count() }}</h3><small class="text-muted">Data wajah {{ strtolower($subjectLabel) }} aktif.</small></div></div></div>
    <div class="col-md-6 col-xl-3 mb-3 mb-xl-0"><div class="card card-outline card-success h-100 mb-0"><div class="card-body py-3"><div class="text-muted small text-uppercase font-weight-bold">Terverifikasi</div><h3 class="text-success mb-0">{{ $verified->total() }}</h3><small class="text-muted">Identitas biometrik sudah aktif.</small></div></div></div>
    <div class="col-md-6 col-xl-3 mb-3 mb-md-0"><div class="card card-outline card-warning h-100 mb-0"><div class="card-body py-3"><div class="text-muted small text-uppercase font-weight-bold">Menunggu</div><h3 class="text-warning mb-0">{{ $pending->count() }}</h3><small class="text-muted">Perlu ditinjau admin.</small></div></div></div>
    <div class="col-md-6 col-xl-3"><div class="card card-outline card-primary h-100 mb-0"><div class="card-body py-3"><div class="text-muted small text-uppercase font-weight-bold">Permintaan Unlock</div><h3 class="text-primary mb-0">{{ $unlockRequests->count() }}</h3><small class="text-muted">Menunggu persetujuan registrasi ulang.</small></div></div></div>
</div>

<div class="card card-primary card-outline">
    <div class="card-body">
        <div class="mb-3">
            <ul class="nav nav-pills">
                <li class="nav-item">
                    <a class="nav-link {{ $unlockRequests->count() > 0 ? 'active' : '' }}" data-toggle="tab" href="#tabUnlock">
                        <i class="fas fa-user-lock text-primary"></i> Permintaan Unlock
                        @if($unlockRequests->count() > 0)<span class="badge badge-primary">{{ $unlockRequests->count() }}</span>@endif
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ $unlockRequests->isEmpty() && $pending->count() > 0 ? 'active' : '' }}" data-toggle="tab" href="#tabPending">
                        <i class="fas fa-clock text-warning"></i> Menunggu Verifikasi
                        @if($pending->count() > 0)
                            <span class="badge badge-warning">{{ $pending->count() }}</span>
                        @endif
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ $unlockRequests->isEmpty() && $pending->count() === 0 ? 'active' : '' }}" data-toggle="tab" href="#tabAll">
                        <i class="fas fa-list text-primary"></i> Semua Data Wajah
                    </a>
                </li>
            </ul>
        </div>

        <div class="tab-content">
            <div class="tab-pane {{ $unlockRequests->count() > 0 ? 'active' : '' }}" id="tabUnlock">
                @forelse($unlockRequests as $face)
                    @php
                        $profile = $face->user_type === 'gtk' ? $face->user->gtk : $face->user->siswa;
                        $name = $profile->nama_lengkap ?? $face->user->name ?? '-';
                    @endphp
                    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between border rounded p-3 mb-2">
                        <div class="d-flex align-items-center">
                            <img src="{{ $face->registration_photo_url ?? $profile?->foto_profile_url ?? asset('vendor/adminlte/dist/img/user2-160x160.jpg') }}" class="img-circle mr-3" width="48" height="48" style="object-fit:cover" alt="{{ $name }}">
                            <div><strong>{{ $name }}</strong><small class="d-block text-muted">{{ strtoupper($face->user_type) }} · Diajukan {{ $face->self_registration_requested_at?->diffForHumans() }}</small>@if($face->self_registration_request_note)<div class="mt-1"><i class="fas fa-comment-alt text-muted mr-1"></i>{{ $face->self_registration_request_note }}</div>@endif</div>
                        </div>
                        <div class="d-flex mt-3 mt-md-0">
                            <form method="POST" action="{{ route('admin.absensi.face-encoding.self-access', $face) }}">
                                @csrf<input type="hidden" name="action" value="unlock">
                                <button type="button" class="btn btn-primary js-face-confirm" data-title="Setujui registrasi ulang?" data-text="{{ $name }} memperoleh satu kali kesempatan registrasi ulang. Akses terkunci otomatis setelah berhasil." data-confirm="Ya, setujui"><i class="fas fa-unlock mr-1"></i>Setujui</button>
                            </form>
                            <form method="POST" action="{{ route('admin.absensi.face-encoding.self-access', $face) }}" class="ml-2">
                                @csrf<input type="hidden" name="action" value="lock">
                                <button type="button" class="btn btn-outline-danger js-face-confirm" data-title="Tolak permintaan unlock?" data-text="Permintaan {{ $name }} akan ditutup dan registrasi tetap terkunci." data-confirm="Ya, tolak"><i class="fas fa-times mr-1"></i>Tolak</button>
                            </form>
                        </div>
                    </div>
                @empty
                    <div class="text-center text-muted py-4"><i class="fas fa-check-circle fa-2x text-success mb-2"></i><br>Tidak ada permintaan unlock yang menunggu persetujuan.</div>
                @endforelse
            </div>
            <div class="tab-pane {{ $unlockRequests->isEmpty() && $pending->count() > 0 ? 'active' : '' }}" id="tabPending">
                @if($pending->count() > 0)
                    <div class="row">
                        @foreach($pending as $face)
                            @php
                                $profile = $face->user_type === 'gtk' ? $face->user->gtk : $face->user->siswa;
                                $name = $profile->nama_lengkap ?? $face->user->name ?? 'Unknown';
                                $identifier = $face->user_type === 'gtk' ? ($profile->nip ?? '-') : ($profile->nisn ?? '-');
                                $identifierLabelFace = $face->user_type === 'gtk' ? 'NIP' : 'NISN';
                            @endphp
                            <div class="col-lg-3 col-md-4 col-sm-6">
                                <div class="card card-outline card-warning">
                                    <div class="card-body text-center">
                                        <div class="mb-2"><img src="{{ $face->registration_photo_url ?? $profile?->foto_profile_url ?? asset('vendor/adminlte/dist/img/user2-160x160.jpg') }}" class="img-circle" width="64" height="64" style="object-fit:cover;" alt="Hasil registrasi {{ $name }}"></div>
                                        <h6 class="mb-0">{{ $name }}</h6>
                                        <small class="text-muted d-block">{{ $identifierLabelFace }}: {{ $identifier }}</small>
                                        <small class="text-muted d-block">{{ strtoupper($face->user_type) }}</small>
                                    <div class="mt-2">
                                        <span class="badge badge-info">{{ $face->total_captures }} capture</span>
                                        <span class="badge badge-secondary">Score: {{ number_format($face->quality_score, 0) }}%</span>
                                    </div>
                                    @if(($face->quality_score ?? 0) < 60)
                                        <div class="alert alert-light border py-2 px-2 mt-2 mb-0 small">
                                            Quality masih rendah. Pertimbangkan minta user registrasi ulang bila hasil wajah kurang jelas.
                                        </div>
                                    @endif
                                    <div class="mt-1">
                                        @foreach($face->capture_angles ?? [] as $angle)
                                            <span class="badge badge-light">{{ $angle }}</span>
                                        @endforeach
                                    </div>
                                        <small class="text-muted d-block mt-1">{{ $face->created_at->format('d/m/Y H:i') }}</small>
                                        <div class="mt-3 btn-group">
                                            <form method="POST" action="{{ route('admin.absensi.face-verify', $face) }}">
                                                @csrf
                                                <input type="hidden" name="action" value="approve">
                                                <button class="btn btn-sm btn-success"><i class="fas fa-check"></i> Setujui</button>
                                            </form>
                                            <form method="POST" action="{{ route('admin.absensi.face-verify', $face) }}" class="ml-1">
                                                @csrf
                                                <input type="hidden" name="action" value="reject">
                                                <button type="button" class="btn btn-sm btn-danger js-face-confirm" data-title="Tolak data wajah?" data-text="Data dinonaktifkan dan tetap terkunci. Admin dapat merekam ulang atau membuka izin registrasi ulang secara terpisah." data-confirm="Ya, tolak"><i class="fas fa-times"></i> Tolak</button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center text-muted py-4">
                        <i class="fas fa-check-circle fa-2x mb-2 text-success"></i><br>
                        Semua data wajah {{ strtolower($subjectLabel) }} sudah diverifikasi.
                    </div>
                @endif
            </div>

            <div class="tab-pane {{ $unlockRequests->isEmpty() && $pending->count() === 0 ? 'active' : '' }}" id="tabAll">
                <div class="table-responsive">
                    <table class="table table-hover table-striped table-sm" id="tabelWajah">
                        <thead>
                            <tr>
                                <th width="40">No</th>
                                <th>Nama</th>
                                <th>{{ $identifierLabel }}</th>
                                <th>Capture</th>
                                <th>Angle</th>
                                <th>Quality</th>
                                <th>Status</th>
                                <th>Diverifikasi</th>
                                <th>Tgl Registrasi</th>
                                <th width="140">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($allFaces as $i => $face)
                                @php
                                    $profile = $face->user_type === 'gtk' ? $face->user->gtk : $face->user->siswa;
                                    $name = $profile->nama_lengkap ?? $face->user->name ?? '-';
                                    $identifier = $face->user_type === 'gtk' ? ($profile->nip ?? '-') : ($profile->nisn ?? '-');
                                @endphp
                                <tr>
                                    <td>{{ $i + 1 }}</td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <img src="{{ $face->registration_photo_url ?? $profile?->foto_profile_url ?? asset('vendor/adminlte/dist/img/user2-160x160.jpg') }}" class="img-circle mr-2" width="32" height="32" style="object-fit:cover;" alt="Hasil registrasi {{ $name }}">
                                            <div>
                                                <div>{{ $name }}</div>
                                                <small class="text-muted">{{ strtoupper($face->user_type) }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>{{ $identifier }}</td>
                                    <td><span class="badge badge-info">{{ $face->total_captures }}</span></td>
                                    <td>@foreach($face->capture_angles ?? [] as $angle)<span class="badge badge-light">{{ $angle }}</span>@endforeach</td>
                                    <td>@php $q = $face->quality_score ?? 0; @endphp <span class="badge badge-{{ $q >= 80 ? 'success' : ($q >= 50 ? 'warning' : 'danger') }}">{{ number_format($q, 0) }}%</span></td>
                                    <td>
                                        @if(! $face->is_active)
                                            <span class="badge badge-danger"><i class="fas fa-ban"></i> Ditolak</span>
                                        @elseif($face->is_verified)
                                            <span class="badge badge-success"><i class="fas fa-check"></i> Verified</span>
                                        @else
                                            <span class="badge badge-warning"><i class="fas fa-clock"></i> Pending</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($face->is_verified)
                                            {{ $face->verifier->name ?? '-' }}<br><small class="text-muted">{{ $face->verified_at?->format('d/m/Y H:i') }}</small>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>{{ $face->created_at->format('d/m/Y H:i') }}</td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="{{ route('admin.absensi.face-register', ['type' => $face->user_type, 'user_id' => $face->user_id]) }}" class="btn btn-info" title="Registrasi Ulang"><i class="fas fa-redo"></i></a>
                                            @if(! $face->self_registration_unlocked_at)
                                                <form method="POST" action="{{ route('admin.absensi.face-encoding.self-access', $face) }}" class="d-inline">
                                                    @csrf
                                                    <input type="hidden" name="action" value="unlock">
                                                    <button type="button" class="btn btn-primary js-face-confirm" title="Izinkan Registrasi Ulang dari Akun Pengguna" data-title="Buka izin registrasi ulang?" data-text="Pengguna dapat merekam ulang satu kali dari akunnya. Setelah berhasil, akses otomatis terkunci kembali." data-confirm="Ya, izinkan"><i class="fas fa-user-lock"></i></button>
                                                </form>
                                            @else
                                                <form method="POST" action="{{ route('admin.absensi.face-encoding.self-access', $face) }}" class="d-inline">
                                                    @csrf
                                                    <input type="hidden" name="action" value="lock">
                                                    <button type="button" class="btn btn-success js-face-confirm" title="Batalkan Izin Registrasi Ulang" data-title="Batalkan izin registrasi ulang?" data-text="Akun pengguna akan kembali terkunci dan tidak dapat merekam ulang." data-confirm="Ya, kunci"><i class="fas fa-lock-open"></i></button>
                                                </form>
                                            @endif
                                            @if($face->is_verified)
                                                <form method="POST" action="{{ route('admin.absensi.face-encoding.reset', $face) }}" class="d-inline">
                                                    @csrf
                                                    <button type="button" class="btn btn-warning js-face-confirm" title="Reset ke Pending" data-title="Reset status verifikasi?" data-text="Data wajah kembali ke antrean verifikasi." data-confirm="Ya, reset"><i class="fas fa-undo"></i></button>
                                                </form>
                                            @endif
                                            <form method="POST" action="{{ route('admin.absensi.face-encoding.destroy', $face) }}" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="button" class="btn btn-danger js-face-confirm" title="Hapus Data Wajah" data-title="Hapus data wajah?" data-text="Data wajah {{ $name }} akan dihapus dan perlu diregistrasi ulang." data-confirm="Ya, hapus"><i class="fas fa-trash"></i></button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="10" class="text-center text-muted py-3">Belum ada data wajah terdaftar</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
</div>
@stop

@section('js')
<script>
$(function() {
    $('#tabelWajah').DataTable({
        language: { url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/id.json' },
        pageLength: 25,
        order: [[8, 'desc']],
        scrollX: true,
        autoWidth: false,
    });

    $(document).on('click', '.js-face-confirm', function() {
        const button = this;
        Swal.fire({
            icon: 'warning',
            title: button.dataset.title,
            text: button.dataset.text,
            showCancelButton: true,
            confirmButtonText: button.dataset.confirm,
            cancelButtonText: 'Batal',
            confirmButtonColor: '#dc3545',
        }).then(result => {
            if (result.isConfirmed) button.closest('form').submit();
        });
    });
});
</script>
@stop
