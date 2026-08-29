@extends('adminlte::page')

@section('title', 'REST API Integrasi')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="m-0 text-dark">REST API Integrasi</h1>
        <a href="{{ url('/api/v1/openapi.json') }}" target="_blank" class="btn btn-sm btn-outline-primary">
            <i class="fas fa-file-code"></i> OpenAPI v1
        </a>
    </div>
@stop

@section('content')
<div class="row">
    <div class="col-lg-7">
        @if (session('new_token'))
            <div class="alert alert-warning">
                <h5><i class="icon fas fa-key"></i> Salin token sekarang</h5>
                <p class="mb-2">Token hanya ditampilkan sekali dan tidak dapat dilihat kembali setelah halaman ini ditutup.</p>
                <div class="input-group">
                    <input id="new-token" type="text" class="form-control" value="{{ session('new_token') }}" readonly>
                    <div class="input-group-append">
                        <button class="btn btn-dark" type="button" id="copy-token"><i class="fas fa-copy"></i> Salin</button>
                    </div>
                </div>
            </div>
        @endif

        <div class="card card-outline card-primary">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-plus-circle mr-1"></i> Buat token integrasi LMS</h3>
            </div>
            <form method="POST" action="{{ route('admin.pengaturan.rest-api.tokens.store') }}">
                @csrf
                <div class="card-body">
                    <div class="form-group">
                        <label for="name">Nama token</label>
                        <input id="name" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" maxlength="80" placeholder="Contoh: LMS produksi" required>
                        @error('name')<span class="invalid-feedback">{{ $message }}</span>@enderror
                    </div>
                    <div class="form-group">
                        <label>Kemampuan akses</label>
                        <div class="custom-control custom-checkbox">
                            <input id="ability-lms-read" name="abilities[]" value="lms:read" type="checkbox" class="custom-control-input" checked>
                            <label class="custom-control-label" for="ability-lms-read"><strong>lms:read</strong> — membaca data siswa dan GTK aktif</label>
                        </div>
                        @error('abilities')<small class="text-danger">{{ $message }}</small>@enderror
                    </div>
                    <div class="form-group mb-0">
                        <label for="expires_at">Kadaluarsa <span class="text-muted">(opsional)</span></label>
                        <input id="expires_at" name="expires_at" type="datetime-local" class="form-control @error('expires_at') is-invalid @enderror" value="{{ old('expires_at') }}">
                        @error('expires_at')<span class="invalid-feedback">{{ $message }}</span>@enderror
                    </div>
                </div>
                <div class="card-footer">
                    <button class="btn btn-primary" type="submit"><i class="fas fa-key"></i> Buat Token</button>
                </div>
            </form>
        </div>

        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-shield-alt mr-1"></i> Token aktif saya</h3>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr><th>Nama</th><th>Kemampuan</th><th>Terakhir dipakai</th><th>Kadaluarsa</th><th></th></tr>
                        </thead>
                        <tbody>
                            @forelse ($tokens as $token)
                                <tr>
                                    <td>{{ $token->name }}</td>
                                    <td><span class="badge badge-info">{{ implode(', ', $token->abilities ?? []) }}</span></td>
                                    <td>{{ optional($token->last_used_at)->timezone('Asia/Jakarta')->format('d M Y H:i') ?? 'Belum pernah' }}</td>
                                    <td>{{ optional($token->expires_at)->timezone('Asia/Jakarta')->format('d M Y H:i') ?? 'Tidak dibatasi' }}</td>
                                    <td class="text-right">
                                        <form method="POST" action="{{ route('admin.pengaturan.rest-api.tokens.destroy', $token->id) }}" class="revoke-token-form d-inline">
                                            @csrf @method('DELETE')
                                            <button class="btn btn-sm btn-outline-danger" type="submit">Cabut</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="text-center text-muted py-4">Belum ada token integrasi.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-5">
        <div class="card card-outline card-info">
            <div class="card-header"><h3 class="card-title">Kontrak API v1</h3></div>
            <div class="card-body">
                <p class="text-muted">Semua endpoint membutuhkan header Authorization Bearer token dan kemampuan lms:read.</p>
                <dl class="mb-0">
                    <dt>GET /api/v1/lms/students</dt>
                    <dd>Data siswa aktif: NISN, nama, jenis kelamin, dan waktu pembaruan.</dd>
                    <dt>GET /api/v1/lms/teachers</dt>
                    <dd>Data GTK aktif untuk sinkronisasi pengajar.</dd>
                    <dt>Parameter bersama</dt>
                    <dd>per_page 1–250 (default 100) dan updated_since format ISO-8601.</dd>
                </dl>
                <a href="{{ url('/api/v1/openapi.json') }}" target="_blank" class="btn btn-outline-info btn-sm"><i class="fas fa-external-link-alt"></i> Lihat OpenAPI JSON</a>
            </div>
        </div>
        <div class="callout callout-warning">
            <h5><i class="fas fa-exclamation-triangle"></i> Keamanan token</h5>
            <p class="mb-0">Berikan token hanya kepada layanan yang berwenang. Cabut token segera apabila terindikasi bocor atau integrasi tidak lagi digunakan.</p>
        </div>
    </div>
</div>
@stop

@section('js')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.getElementById('copy-token')?.addEventListener('click', function () {
    const input = document.getElementById('new-token');
    input.select();
    navigator.clipboard.writeText(input.value).then(() => Swal.fire({icon: 'success', title: 'Token disalin', timer: 1500, showConfirmButton: false}));
});
document.querySelectorAll('.revoke-token-form').forEach((form) => {
    form.addEventListener('submit', function (event) {
        event.preventDefault();
        Swal.fire({title: 'Cabut token ini?', text: 'Aplikasi yang memakai token ini akan langsung kehilangan akses.', icon: 'warning', showCancelButton: true, confirmButtonText: 'Ya, cabut', cancelButtonText: 'Batal', confirmButtonColor: '#dc3545'})
            .then((result) => { if (result.isConfirmed) form.submit(); });
    });
});
</script>
@stop
