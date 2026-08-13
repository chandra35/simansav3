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
                    <table class="table table-hover table-sm simansa-smart-table" id="tabelWajah">
                        <thead>
                            <tr>
                                <th width="40">No</th>
                                <th>Identitas</th>
                                <th>Foto Wajah</th>
                                <th>Data Capture</th>
                                <th>Verifikasi</th>
                                <th>Registrasi</th>
                                <th width="90">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($allFaces as $i => $face)
                                @php
                                    $profile = $face->user_type === 'gtk' ? $face->user->gtk : $face->user->siswa;
                                    $name = $profile->nama_lengkap ?? $face->user->name ?? '-';
                                    $identifier = $face->user_type === 'gtk' ? ($profile->nip ?? '-') : ($profile->nisn ?? '-');
                                    $photoUrls = $face->registration_photo_urls;
                                @endphp
                                <tr>
                                    <td>{{ $i + 1 }}</td>
                                    <td class="face-identity-cell">
                                        <div class="face-identity">
                                            <img src="{{ $profile?->foto_profile_url ?? asset('vendor/adminlte/dist/img/user2-160x160.jpg') }}" class="face-profile-thumb" alt="Foto profil {{ $name }}">
                                            <div class="face-identity__copy">
                                                <strong>{{ $name }}</strong>
                                                <span>{{ $identifierLabel }} {{ $identifier }}</span>
                                                <small><i class="fas fa-user-tag mr-1"></i>{{ strtoupper($face->user_type) }} · foto profil</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        @if($photoUrls->isNotEmpty())
                                            <button type="button" class="btn btn-link p-0 border-0 js-face-preview"
                                                    data-toggle="modal" data-target="#facePreviewModal"
                                                    data-images='@json($photoUrls)'
                                                    data-photo-angles='@json($face->capture_angles ?? [])'
                                                    data-name="{{ $name }}"
                                                    data-identifier="{{ $identifierLabel }}: {{ $identifier }}"
                                                    data-captures="{{ $face->total_captures }} frame"
                                                    data-quality="{{ number_format($face->quality_score ?? 0, 0) }}%"
                                                    data-angles="{{ implode(', ', $face->capture_angles ?? []) }}"
                                                    data-registered="{{ $face->created_at->format('d/m/Y H:i') }}"
                                                    title="Lihat foto hasil registrasi {{ $name }}">
                                                <img src="{{ $photoUrls->first() }}" class="face-capture-thumb" alt="Foto wajah terdaftar {{ $name }}">
                                                <small class="d-block mt-1"><i class="fas fa-images mr-1"></i>{{ $photoUrls->count() }} frame</small>
                                            </button>
                                        @else
                                            <span class="badge badge-light border text-muted p-2"><i class="fas fa-image mr-1"></i>Belum tersimpan</span>
                                        @endif
                                    </td>
                                    <td>
                                        @php $q = $face->quality_score ?? 0; @endphp
                                        <div class="face-capture-meta">
                                            <div class="face-capture-meta__summary">
                                                <span><i class="fas fa-camera mr-1"></i>{{ $face->total_captures }} frame</span>
                                                <span class="badge badge-{{ $q >= 80 ? 'success' : ($q >= 50 ? 'warning' : 'danger') }}">Quality {{ number_format($q, 0) }}%</span>
                                            </div>
                                            <div class="face-angle-list">@foreach($face->capture_angles ?? [] as $angle)<span>{{ $angle }}</span>@endforeach</div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="face-verification-meta">
                                        @if(! $face->is_active)
                                            <span class="badge badge-danger"><i class="fas fa-ban"></i> Ditolak</span>
                                        @elseif($face->is_verified)
                                            <span class="badge badge-success"><i class="fas fa-check"></i> Verified</span>
                                        @else
                                            <span class="badge badge-warning"><i class="fas fa-clock"></i> Pending</span>
                                        @endif
                                        @if($face->is_verified)
                                            <small><i class="fas fa-user-check mr-1"></i>{{ $face->verifier->name ?? '-' }}</small>
                                            <small><i class="far fa-clock mr-1"></i>{{ $face->verified_at?->format('d/m/Y H:i') }}</small>
                                        @else
                                            <small class="text-muted">Belum diverifikasi admin</small>
                                        @endif
                                        </div>
                                    </td>
                                    <td class="face-registration-date"><strong>{{ $face->created_at->format('d/m/Y') }}</strong><small>{{ $face->created_at->format('H:i') }} WIB</small></td>
                                    <td>
                                        <div class="dropdown face-action-dropdown">
                                            <button class="btn btn-sm btn-outline-primary dropdown-toggle" type="button" data-toggle="dropdown" data-boundary="viewport" aria-haspopup="true" aria-expanded="false"><i class="fas fa-ellipsis-v mr-1"></i>Aksi</button>
                                            <div class="dropdown-menu dropdown-menu-right">
                                            @if($photoUrls->isNotEmpty())
                                                <button type="button" class="dropdown-item js-face-preview" data-toggle="modal" data-target="#facePreviewModal"
                                                        data-images='@json($photoUrls)' data-photo-angles='@json($face->capture_angles ?? [])'
                                                        data-name="{{ $name }}" data-identifier="{{ $identifierLabel }}: {{ $identifier }}"
                                                        data-captures="{{ $face->total_captures }} frame" data-quality="{{ number_format($face->quality_score ?? 0, 0) }}%"
                                                        data-angles="{{ implode(', ', $face->capture_angles ?? []) }}" data-registered="{{ $face->created_at->format('d/m/Y H:i') }}">
                                                    <i class="fas fa-images text-info mr-2"></i>Lihat Semua Frame
                                                </button>
                                            @endif
                                            <a href="{{ route('admin.absensi.face-register', ['type' => $face->user_type, 'user_id' => $face->user_id]) }}" class="dropdown-item"><i class="fas fa-redo text-info mr-2"></i>Registrasi Ulang</a>
                                            <div class="dropdown-divider"></div>
                                            @if(! $face->self_registration_unlocked_at)
                                                <form method="POST" action="{{ route('admin.absensi.face-encoding.self-access', $face) }}" class="mb-0">
                                                    @csrf
                                                    <input type="hidden" name="action" value="unlock">
                                                    <button type="button" class="dropdown-item js-face-confirm" data-title="Buka izin registrasi ulang?" data-text="Pengguna dapat merekam ulang satu kali dari akunnya. Setelah berhasil, akses otomatis terkunci kembali." data-confirm="Ya, izinkan"><i class="fas fa-user-lock text-primary mr-2"></i>Izinkan Registrasi Mandiri</button>
                                                </form>
                                            @else
                                                <form method="POST" action="{{ route('admin.absensi.face-encoding.self-access', $face) }}" class="mb-0">
                                                    @csrf
                                                    <input type="hidden" name="action" value="lock">
                                                    <button type="button" class="dropdown-item js-face-confirm" data-title="Batalkan izin registrasi ulang?" data-text="Akun pengguna akan kembali terkunci dan tidak dapat merekam ulang." data-confirm="Ya, kunci"><i class="fas fa-lock text-success mr-2"></i>Kunci Registrasi Mandiri</button>
                                                </form>
                                            @endif
                                            @if($face->is_verified)
                                                <form method="POST" action="{{ route('admin.absensi.face-encoding.reset', $face) }}" class="mb-0">
                                                    @csrf
                                                    <button type="button" class="dropdown-item js-face-confirm" data-title="Reset status verifikasi?" data-text="Data wajah kembali ke antrean verifikasi." data-confirm="Ya, reset"><i class="fas fa-undo text-warning mr-2"></i>Reset ke Pending</button>
                                                </form>
                                            @endif
                                            <div class="dropdown-divider"></div>
                                            <form method="POST" action="{{ route('admin.absensi.face-encoding.destroy', $face) }}" class="mb-0">
                                                @csrf
                                                @method('DELETE')
                                                <button type="button" class="dropdown-item text-danger js-face-confirm" data-title="Hapus data wajah?" data-text="Data wajah {{ $name }} akan dihapus dan perlu diregistrasi ulang." data-confirm="Ya, hapus"><i class="fas fa-trash mr-2"></i>Hapus Data Wajah</button>
                                            </form>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="7" class="text-center text-muted py-3">Belum ada data wajah terdaftar</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
</div>

<div class="modal fade" id="facePreviewModal" tabindex="-1" role="dialog" aria-labelledby="facePreviewTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <div><h5 class="modal-title mb-0" id="facePreviewTitle"><i class="fas fa-user-check text-primary mr-1"></i>Data Wajah Terdaftar</h5><small class="text-muted">Foto capture yang menjadi referensi verifikasi pengguna.</small></div>
                <button type="button" class="close" data-dismiss="modal" aria-label="Tutup"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body">
                <div class="face-preview-stage"><img id="facePreviewImage" src="" alt="Preview data wajah"></div>
                <div class="face-preview-gallery mt-3" id="facePreviewGallery"></div>
                <div class="mt-3"><h5 class="font-weight-bold mb-0" id="facePreviewName">-</h5><div class="text-muted" id="facePreviewIdentifier">-</div></div>
                <div class="row mt-3 face-preview-metrics">
                    <div class="col-6"><small>Jumlah Capture</small><strong id="facePreviewCaptures">-</strong></div>
                    <div class="col-6"><small>Quality</small><strong id="facePreviewQuality">-</strong></div>
                    <div class="col-12 mt-2"><small>Sudut Terekam</small><strong id="facePreviewAngles">-</strong></div>
                    <div class="col-12 mt-2"><small>Tanggal Registrasi</small><strong id="facePreviewRegistered">-</strong></div>
                </div>
            </div>
            <div class="modal-footer"><button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button></div>
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
        order: [[5, 'desc']],
        scrollX: true,
        autoWidth: true,
        columnDefs: [
            { targets: [0, 2, 6], orderable: false, searchable: false },
        ],
    });

    $(document).on('click', '.js-face-preview', function() {
        const button = this;
        let images = [];
        let photoAngles = [];
        try { images = JSON.parse(button.dataset.images || '[]'); } catch (error) { images = []; }
        try { photoAngles = JSON.parse(button.dataset.photoAngles || '[]'); } catch (error) { photoAngles = []; }
        const gallery = $('#facePreviewGallery').empty();
        const showFrame = (index) => {
            $('#facePreviewImage').attr('src', images[index] || '').attr('alt', `Frame ${index + 1} data wajah ${button.dataset.name}`);
            gallery.find('button').removeClass('is-active').eq(index).addClass('is-active');
        };
        images.forEach((image, index) => {
            const angle = photoAngles[index] || `Frame ${index + 1}`;
            $('<button>', { type: 'button', class: 'face-preview-gallery__item', title: `Lihat ${angle}` })
                .append($('<img>', { src: image, alt: `${angle} ${button.dataset.name}` }))
                .append($('<span>').text(angle))
                .on('click', () => showFrame(index))
                .appendTo(gallery);
        });
        showFrame(0);
        $('#facePreviewName').text(button.dataset.name);
        $('#facePreviewIdentifier').text(button.dataset.identifier);
        $('#facePreviewCaptures').text(button.dataset.captures);
        $('#facePreviewQuality').text(button.dataset.quality);
        $('#facePreviewAngles').text(button.dataset.angles || '-');
        $('#facePreviewRegistered').text(button.dataset.registered);
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

@section('css')
<style>
.face-recognition-verification #tabelWajah{width:100%!important}.face-recognition-verification #tabelWajah th,.face-recognition-verification #tabelWajah td{vertical-align:middle}.face-identity-cell{min-width:235px}.face-identity{display:flex;align-items:center;gap:10px}.face-profile-thumb{width:40px;height:48px;flex:0 0 40px;object-fit:cover;border:2px solid #fff;border-radius:9px;box-shadow:0 0 0 1px #dbe4ef}.face-identity__copy{min-width:0}.face-identity__copy strong,.face-identity__copy span,.face-identity__copy small{display:block}.face-identity__copy strong{color:#172033;font-size:.82rem;line-height:1.25}.face-identity__copy span{margin-top:2px;color:#475569;font-size:.7rem;white-space:nowrap}.face-identity__copy small{margin-top:3px;color:#8492a6;font-size:.64rem}.face-capture-meta{min-width:170px}.face-capture-meta__summary{display:flex;align-items:center;gap:6px;white-space:nowrap}.face-capture-meta__summary>span:first-child{color:#475569;font-size:.7rem;font-weight:700}.face-angle-list{display:flex;max-width:190px;flex-wrap:wrap;gap:3px;margin-top:6px}.face-angle-list span{padding:2px 6px;border-radius:10px;background:#f1f5f9;color:#475569;font-size:.62rem;font-weight:700}.face-verification-meta{display:flex;min-width:145px;flex-direction:column;align-items:flex-start;gap:4px}.face-verification-meta small{color:#64748b;font-size:.64rem;line-height:1.25}.face-registration-date{min-width:92px}.face-registration-date strong,.face-registration-date small{display:block}.face-registration-date strong{font-size:.72rem}.face-registration-date small{color:#64748b;font-size:.64rem}.face-action-dropdown{white-space:nowrap}
.face-capture-thumb{width:58px;height:72px;object-fit:cover;object-position:center;border-radius:10px;border:2px solid #dce5f3;box-shadow:0 4px 12px rgba(33,55,91,.12);transition:transform .2s ease,border-color .2s ease}.js-face-preview:hover .face-capture-thumb{transform:scale(1.05);border-color:#3b82f6}.face-preview-stage{display:flex;align-items:center;justify-content:center;min-height:360px;padding:16px;border-radius:14px;background:linear-gradient(145deg,#eef3fa,#dde7f4)}.face-preview-stage img{display:block;width:auto;max-width:100%;height:auto;max-height:460px;object-fit:contain;border-radius:12px;box-shadow:0 12px 30px rgba(20,38,69,.2)}.face-preview-gallery{display:grid;grid-template-columns:repeat(5,minmax(0,1fr));gap:8px}.face-preview-gallery__item{padding:5px;border:2px solid transparent;border-radius:10px;background:#f1f5f9;color:#64748b;font-size:.72rem;transition:.2s}.face-preview-gallery__item img{display:block;width:100%;height:66px;object-fit:cover;border-radius:6px;margin-bottom:4px}.face-preview-gallery__item.is-active{border-color:#3b82f6;background:#eff6ff;color:#1d4ed8}.face-preview-metrics>div{padding:10px 12px;border-radius:8px;background:#f7f9fc}.face-preview-metrics small,.face-preview-metrics strong{display:block}.face-preview-metrics small{color:#6c757d}.face-preview-metrics strong{color:#253858}.face-action-dropdown .dropdown-menu{min-width:245px}.face-action-dropdown .dropdown-item{font-size:.875rem;padding:.55rem .9rem}.face-action-dropdown form{display:block;width:100%}@media(max-width:575.98px){.face-preview-stage{min-height:280px}.face-preview-stage img{max-height:360px}.face-preview-gallery{grid-template-columns:repeat(3,minmax(0,1fr))}}
</style>
@stop
