@extends('adminlte::page')

@section('title', 'Manajemen API & Token')

@section('plugins.Select2', true)

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="m-0 text-dark"><i class="fas fa-key text-primary mr-2"></i>Manajemen API & Token Integrasi</h1>
            <small class="text-muted">Kelola Personal Access Token Sanctum untuk Bot OpenClaw WhatsApp, integrasi LMS, dan sistem eksternal.</small>
        </div>
        <div>
            <a href="{{ url('/docs/api') }}" target="_blank" class="btn btn-sm btn-outline-primary shadow-sm mr-1">
                <i class="fas fa-book-open mr-1"></i> Dokumentasi API (Scramble)
            </a>
            <a href="{{ url('/docs/api.json') }}" target="_blank" class="btn btn-sm btn-outline-secondary shadow-sm">
                <i class="fas fa-file-code mr-1"></i> OpenAPI Spec
            </a>
        </div>
    </div>
@stop

@section('content')
@php
    $abilities = $abilities ?? $availableAbilities ?? \App\Http\Controllers\Admin\RestApiController::ABILITIES;
@endphp
<div class="row">
    {{-- Kolom Kiri: Form Buat Token & Daftar Token --}}
    <div class="col-lg-7">
        @if (session('new_token'))
            <div class="alert alert-warning alert-dismissible shadow-sm">
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                <h5><i class="icon fas fa-check-circle"></i> Token Berhasil Dibuat!</h5>
                <p class="mb-2"><strong>Perhatian:</strong> Salin token ini sekarang. Demi keamanan, token ini hanya ditampilkan <strong>satu kali</strong> dan tidak dapat dilihat kembali setelah Anda meninggalkan halaman ini.</p>
                <div class="input-group">
                    <input id="new-token" type="text" class="form-control font-weight-bold" value="{{ session('new_token') }}" readonly>
                    <div class="input-group-append">
                        <button class="btn btn-dark" type="button" id="copy-token">
                            <i class="fas fa-copy mr-1"></i> Salin Token
                        </button>
                    </div>
                </div>
            </div>
        @endif

        {{-- Form Buat Token --}}
        <div class="card card-outline card-primary shadow-sm mb-4">
            <div class="card-header">
                <h3 class="card-title font-weight-bold"><i class="fas fa-plus-circle mr-1 text-primary"></i> Buat Token Akses Baru</h3>
            </div>
            <form method="POST" action="{{ route('admin.pengaturan.rest-api.tokens.store') }}">
                @csrf
                <div class="card-body">
                    {{-- Target User --}}
                    <div class="form-group">
                        <label for="user_id" class="font-weight-bold">Pemilik Token (Akun Pengguna) <span class="text-danger">*</span></label>
                        <select name="user_id" id="user_id" class="form-control select2 @error('user_id') is-invalid @enderror" required style="width: 100%;">
                            @foreach ($users as $user)
                                @php
                                    $roleNames = $user->roles->pluck('name')->implode(', ') ?: 'No Role';
                                @endphp
                                <option value="{{ $user->id }}" {{ (old('user_id', auth()->id()) == $user->id) ? 'selected' : '' }}>
                                    {{ $user->name }} ({{ $user->username ?? $user->email }}) — [{{ $roleNames }}]
                                </option>
                            @endforeach
                        </select>
                        <small class="form-text text-muted">Token akan dihubungkan ke akun ini dan mewarisi hak otoritas yang bersangkutan.</small>
                        @error('user_id')<span class="invalid-feedback d-block">{{ $message }}</span>@enderror
                    </div>

                    {{-- Nama Token --}}
                    <div class="form-group">
                        <label for="name" class="font-weight-bold">Nama / Keterangan Token <span class="text-danger">*</span></label>
                        <input id="name" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" maxlength="80" placeholder="Contoh: OpenClaw Bot WhatsApp Group Madrasah" required>
                        <small class="form-text text-muted">Beri nama yang jelas untuk mengidentifikasi perangkat atau bot pengguna token ini.</small>
                        @error('name')<span class="invalid-feedback">{{ $message }}</span>@enderror
                    </div>

                    {{-- Hak Akses (Abilities) --}}
                    <div class="form-group">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <label class="font-weight-bold mb-0">Hak Akses Modul (Abilities) <span class="text-danger">*</span></label>
                            <div class="btn-group btn-group-sm">
                                <button type="button" class="btn btn-xs btn-outline-primary" id="btn-select-all">Pilih Semua</button>
                                <button type="button" class="btn btn-xs btn-outline-secondary" id="btn-deselect-all">Kosongkan</button>
                            </div>
                        </div>

                        {{-- Wildcard option --}}
                        <div class="p-2 bg-light border rounded mb-2">
                            <div class="custom-control custom-checkbox">
                                <input id="ability-all" name="abilities[]" value="*" type="checkbox" class="custom-control-input ability-item" {{ in_array('*', old('abilities', ['*']), true) ? 'checked' : '' }}>
                                <label class="custom-control-label font-weight-bold text-primary" for="ability-all">
                                    <span class="badge badge-danger mr-1">*</span> Akses Penuh (Full Access / Bot Helper)
                                </label>
                                <div class="text-muted small ml-4">
                                    Memberikan akses ke seluruh modul REST API SIMANSA (Siswa, GTK, Jadwal, Presensi, Keuangan, Surat, & LMS). Sangat direkomendasikan untuk <strong>OpenClaw Bot</strong>.
                                </div>
                            </div>
                        </div>

                        {{-- Modular options --}}
                        <div class="border rounded p-2" style="max-height: 260px; overflow-y: auto;">
                            @foreach ($abilities as $abilityKey => $ability)
                                @if ($abilityKey !== '*')
                                    <div class="custom-control custom-checkbox py-1 border-bottom">
                                        <input id="ability-{{ Str::slug($abilityKey) }}" name="abilities[]" value="{{ $abilityKey }}" type="checkbox" class="custom-control-input ability-item ability-sub" {{ in_array($abilityKey, old('abilities', []), true) ? 'checked' : '' }}>
                                        <label class="custom-control-label" for="ability-{{ Str::slug($abilityKey) }}">
                                            <span class="badge badge-{{ str_replace('badge-', '', $ability['badge'] ?? 'secondary') }} mr-1">{{ $abilityKey }}</span>
                                            <strong>{{ $ability['label'] }}</strong>
                                            <span class="text-muted small d-block">{{ $ability['description'] }}</span>
                                        </label>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                        @error('abilities')<span class="invalid-feedback d-block">{{ $message }}</span>@enderror
                    </div>

                    {{-- Kadaluarsa --}}
                    <div class="form-group mb-0">
                        <label for="expires_at" class="font-weight-bold">Masa Berlaku / Kadaluarsa <span class="text-muted">(Opsional)</span></label>
                        <input id="expires_at" name="expires_at" type="datetime-local" class="form-control @error('expires_at') is-invalid @enderror" value="{{ old('expires_at') }}">
                        <small class="form-text text-muted">Biarkan kosong jika token berlaku seterusnya tanpa batas waktu (cocok untuk bot server).</small>
                        @error('expires_at')<span class="invalid-feedback">{{ $message }}</span>@enderror
                    </div>
                </div>
                <div class="card-footer bg-white border-top">
                    <button class="btn btn-primary font-weight-bold" type="submit">
                        <i class="fas fa-key mr-1"></i> Generate Token API
                    </button>
                </div>
            </form>
        </div>

        {{-- Daftar Token Aktif --}}
        <div class="card card-outline card-secondary shadow-sm">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="card-title font-weight-bold"><i class="fas fa-list-alt mr-1 text-secondary"></i> Daftar Token API Sistem</h3>
                <span class="badge badge-info">{{ $tokens->count() }} Token Terdaftar</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-striped mb-0 align-middle">
                        <thead class="thead-light">
                            <tr>
                                <th>Nama Token</th>
                                <th>Pemilik Akun</th>
                                <th>Hak Akses</th>
                                <th>Terakhir Dipakai</th>
                                <th>Kadaluarsa</th>
                                <th class="text-center" style="width: 80px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($tokens as $token)
                                <tr>
                                    <td class="font-weight-bold">
                                        <i class="fas fa-shield-alt text-success mr-1"></i>
                                        {{ $token->name }}
                                    </td>
                                    <td>
                                        @if ($token->tokenable)
                                            <div>{{ $token->tokenable->name }}</div>
                                            <small class="text-muted">{{ $token->tokenable->username ?? $token->tokenable->email }}</small>
                                        @else
                                            <span class="badge badge-secondary">User Dihapus</span>
                                        @endif
                                    </td>
                                    <td>
                                        @php
                                            $tokenAbilities = $token->abilities ?? [];
                                        @endphp
                                        @if (in_array('*', $tokenAbilities, true))
                                            <span class="badge badge-danger" title="Akses penuh semua endpoint">* Full Access</span>
                                        @else
                                            @foreach ($tokenAbilities as $ab)
                                                @php
                                                    $abBadge = str_replace('badge-', '', $abilities[$ab]['badge'] ?? 'secondary');
                                                @endphp
                                                <span class="badge badge-{{ $abBadge }}">{{ $ab }}</span>
                                            @endforeach
                                        @endif
                                    </td>
                                    <td class="small">
                                        @if ($token->last_used_at)
                                            <span title="{{ $token->last_used_at->timezone('Asia/Jakarta')->toDateTimeString() }}">
                                                {{ $token->last_used_at->timezone('Asia/Jakarta')->diffForHumans() }}
                                            </span>
                                            <div class="text-muted">{{ $token->last_used_at->timezone('Asia/Jakarta')->format('d/m/Y H:i') }}</div>
                                        @else
                                            <span class="badge badge-light border">Belum pernah</span>
                                        @endif
                                    </td>
                                    <td class="small">
                                        @if ($token->expires_at)
                                            @if ($token->expires_at->isPast())
                                                <span class="badge badge-danger">Kadaluarsa</span>
                                            @else
                                                <span class="text-success">{{ $token->expires_at->timezone('Asia/Jakarta')->format('d/m/Y H:i') }}</span>
                                            @endif
                                        @else
                                            <span class="badge badge-light border">Selamanya</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <form method="POST" action="{{ route('admin.pengaturan.rest-api.tokens.destroy', $token->id) }}" class="revoke-token-form d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn-xs btn-outline-danger" type="submit" title="Cabut Token">
                                                <i class="fas fa-trash-alt mr-1"></i> Cabut
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-4">
                                        <i class="fas fa-key fa-2x mb-2 text-muted d-block"></i>
                                        Belum ada token API integrasi yang dibuat.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- Kolom Kanan: Panduan OpenClaw & Dokumentasi --}}
    <div class="col-lg-5">
        {{-- Card OpenClaw Bot WhatsApp --}}
        <div class="card card-outline card-success shadow-sm mb-4">
            <div class="card-header">
                <h3 class="card-title font-weight-bold text-success">
                    <i class="fab fa-whatsapp mr-1"></i> Integrasi Bot OpenClaw
                </h3>
            </div>
            <div class="card-body">
                <p class="text-sm">
                    Bot OpenClaw WhatsApp di group sekolah dapat mengambil informasi madrasah secara real-time dengan mengirim HTTP GET request menggunakan token di atas.
                </p>

                <h6 class="font-weight-bold text-sm mb-1"><i class="fas fa-globe mr-1 text-primary"></i> Base URL API v1:</h6>
                <div class="bg-dark p-2 rounded text-monospace text-sm mb-3">
                    <code>https://simansa.man1metro.sch.id/api/v1</code>
                </div>

                <h6 class="font-weight-bold text-sm mb-1"><i class="fas fa-terminal mr-1 text-secondary"></i> Contoh Request (cURL):</h6>
                <pre class="bg-dark text-light p-2 rounded text-xs mb-3" style="white-space: pre-wrap; word-break: break-all;">curl -X GET "https://simansa.man1metro.sch.id/api/v1/siswa?q=Muhammad" \
  -H "Authorization: Bearer &lt;YOUR_API_TOKEN&gt;" \
  -H "Accept: application/json"</pre>

                <h6 class="font-weight-bold text-sm mb-2"><i class="fas fa-cubes mr-1 text-info"></i> Endpoint Utama untuk Bot:</h6>
                <ul class="list-unstyled text-sm mb-0">
                    <li class="mb-2">
                        <span class="badge badge-primary mr-1">GET</span>
                        <code>/api/v1/siswa</code> — Cari siswa (nama, NISN, NIS, kelas, gender)
                    </li>
                    <li class="mb-2">
                        <span class="badge badge-info mr-1">GET</span>
                        <code>/api/v1/gtk</code> — Cari guru & staf (NIP, nama, jabatan, status)
                    </li>
                    <li class="mb-2">
                        <span class="badge badge-success mr-1">GET</span>
                        <code>/api/v1/jadwal</code> — Jadwal pelajaran harian berdasarkan kelas/guru
                    </li>
                    <li class="mb-2">
                        <span class="badge badge-warning mr-1">GET</span>
                        <code>/api/v1/presensi/summary</code> — Rekap kehadiran sholat & harian hari ini
                    </li>
                    <li class="mb-2">
                        <span class="badge badge-secondary mr-1">GET</span>
                        <code>/api/v1/keuangan/tagihan-siswa</code> — Cek tagihan & pembayaran siswa
                    </li>
                    <li class="mb-2">
                        <span class="badge badge-danger mr-1">GET</span>
                        <code>/api/v1/surat-keterangan</code> — Cek verifikasi surat keterangan aktif
                    </li>
                </ul>
            </div>
            <div class="card-footer bg-light">
                <a href="{{ url('/docs/api') }}" target="_blank" class="btn btn-sm btn-primary btn-block">
                    <i class="fas fa-book-open mr-1"></i> Buka Dokumentasi Interaktif Lengkap (Scramble)
                </a>
            </div>
        </div>

        {{-- Card Tips Keamanan --}}
        <div class="card card-outline card-warning shadow-sm">
            <div class="card-header">
                <h3 class="card-title font-weight-bold text-warning">
                    <i class="fas fa-shield-alt mr-1"></i> Panduan Keamanan Token
                </h3>
            </div>
            <div class="card-body text-sm">
                <ul class="pl-3 mb-0">
                    <li class="mb-2">
                        <strong>Prinsip Hak Akses Terkecil:</strong> Jika token hanya digunakan untuk sistem tertentu (misalnya Moodle/LMS), centang hanya kemampuan yang relevan (seperti <code>lms:read</code>) dan hindari memberikan <code>*</code> (Akses Penuh).
                    </li>
                    <li class="mb-2">
                        <strong>Simpan Aman:</strong> Letakkan token pada environment variable server (misal <code>.env</code> pada VM OpenClaw). Jangan masukkan token ke dalam source code publik / Git.
                    </li>
                    <li class="mb-2">
                        <strong>Cabut Segera:</strong> Apabila ada perangkat bot yang dinonaktifkan atau token dicurigai bocor, segera tekan tombol <strong>Cabut</strong> pada tabel token aktif.
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>
@stop

@section('js')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
$(document).ready(function() {
    if ($.fn.select2) {
        $('.select2').select2({
            theme: 'bootstrap4',
            placeholder: 'Pilih Akun Pengguna'
        });
    }

    // Salin Token
    $('#copy-token').on('click', function () {
        const input = document.getElementById('new-token');
        if (!input) return;
        input.select();
        input.setSelectionRange(0, 99999);
        navigator.clipboard.writeText(input.value).then(function() {
            Swal.fire({
                icon: 'success',
                title: 'Token Berhasil Disalin!',
                text: 'Simpan token di tempat yang aman.',
                timer: 2000,
                showConfirmButton: false
            });
        });
    });

    // Toggle abilities
    $('#ability-all').on('change', function() {
        if ($(this).is(':checked')) {
            $('.ability-sub').prop('checked', false);
        }
    });

    $('.ability-sub').on('change', function() {
        if ($(this).is(':checked')) {
            $('#ability-all').prop('checked', false);
        }
    });

    $('#btn-select-all').on('click', function() {
        $('#ability-all').prop('checked', false);
        $('.ability-sub').prop('checked', true);
    });

    $('#btn-deselect-all').on('click', function() {
        $('.ability-item').prop('checked', false);
    });

    // Konfirmasi Cabut Token
    $('.revoke-token-form').on('submit', function (event) {
        event.preventDefault();
        const form = this;
        Swal.fire({
            title: 'Cabut Token Akses Ini?',
            text: 'Aplikasi atau Bot yang menggunakan token ini akan langsung terputus dan tidak dapat mengakses API lagi.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: '<i class="fas fa-trash-alt mr-1"></i> Ya, Cabut Sekarang',
            cancelButtonText: 'Batal',
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d'
        }).then(function(result) {
            if (result.isConfirmed) {
                form.submit();
            }
        });
    });
});
</script>
@stop