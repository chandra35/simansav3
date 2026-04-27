@extends('adminlte::page')

@section('title', 'Update API Token')

@section('content_header')
    <h1 class="m-0 text-dark">Update API Token</h1>
@stop

@section('css')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
@stop

@section('plugins.Sweetalert2', true)

@section('content')
{{-- Nuclear fix: accent-white body class (dari navbar-white) memaksa semua .nav-link jadi #fff.
     CSS di @section('css') dimuat sebelum adminlte CSS, sehingga kalah. Style block di body
     dimuat SETELAH semua CSS head + !important = menang absolut. --}}
<style>
    #tokenTabs .nav-link          { color: #495057 !important; }
    #tokenTabs .nav-link.active   { color: #007bff !important; }
    #tokenTabs .nav-link:hover    { color: #007bff !important; }
</style>
<div class="row">
    <div class="col-md-9">
        <div class="card">
            <div class="card-header" style="border-top: 3px solid #007bff; padding-bottom: 0;">
                <ul class="nav nav-tabs card-header-tabs" id="tokenTabs" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" id="emis-tab" data-toggle="tab" href="#emis" role="tab"
                           style="color: #007bff;">
                            <i class="fas fa-user-graduate"></i> Token EMIS (NISN)
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="emis-institusi-tab" data-toggle="tab" href="#emis-institusi" role="tab"
                           style="color: #495057;">
                            <i class="fas fa-school"></i> Token EMIS Lembaga
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="kemenag-tab" data-toggle="tab" href="#kemenag" role="tab"
                           style="color: #495057;">
                            <i class="fas fa-id-card"></i> Token Kemenag (NIP)
                        </a>
                    </li>
                </ul>
            </div>
            <div class="card-body">
                <div class="tab-content" id="tokenTabContent">
                    {{-- EMIS Token Tab --}}
                    <div class="tab-pane fade show active" id="emis" role="tabpanel">
                        @include('admin.pengaturan.partials.token-form', [
                            'tokenType' => 'emis_api_token',
                            'tokenName' => 'Token EMIS (Cek NISN)',
                            'tokenData' => $tokens->get('emis_api_token'),
                            'tokenInfo' => $tokenTypes['emis_api_token'],
                        ])
                    </div>

                    {{-- EMIS Lembaga Token Tab --}}
                    <div class="tab-pane fade" id="emis-institusi" role="tabpanel">
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i>
                            <strong>Token EMIS Lembaga</strong> digunakan untuk mengambil data siswa dari EMIS berdasarkan lembaga (MAN 1 Metro).
                            Token ini harus dari <strong>akun operator lembaga</strong> (bukan akun pusat).
                            Cara mendapat token: Login ke <a href="https://emis.kemenag.go.id" target="_blank">emis.kemenag.go.id</a>
                            dengan akun operator lembaga → buka DevTools (F12) → tab Network → cari request ke <code>api-emis.kemenag.go.id</code>
                            → salin isi header <code>Authorization: Bearer ...</code>.
                        </div>
                        @include('admin.pengaturan.partials.token-form', [
                            'tokenType' => 'emis_institusi_token',
                            'tokenName' => 'Token EMIS Lembaga (Data Siswa)',
                            'tokenData' => $tokens->get('emis_institusi_token'),
                            'tokenInfo' => $tokenTypes['emis_institusi_token'],
                        ])
                    </div>

                    {{-- Kemenag Token Tab --}}
                    <div class="tab-pane fade" id="kemenag" role="tabpanel">
                        @include('admin.pengaturan.partials.token-form', [
                            'tokenType' => 'kemenag_nip_token',
                            'tokenName' => 'Token Kemenag (Cek NIP)',
                            'tokenData' => $tokens->get('kemenag_nip_token'),
                            'tokenInfo' => $tokenTypes['kemenag_nip_token'],
                        ])
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card card-secondary">
            <div class="card-header">
                <h3 class="card-title">Informasi</h3>
            </div>
            <div class="card-body">
                <dl>
                    <dt>Total Token</dt>
                    <dd>{{ $tokens->count() }} API Token</dd>

                    <dt>Fungsi</dt>
                    <dd>
                        <ul class="pl-3">
                            <li>Validasi NISN Siswa</li>
                            <li>Validasi NIP GTK</li>
                        </ul>
                    </dd>

                    <dt>Format</dt>
                    <dd>JWT atau Bearer Token</dd>

                    <dt>Keamanan</dt>
                    <dd>Token disimpan terenkripsi di database</dd>
                </dl>
            </div>
        </div>

        <div class="card card-warning">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-info-circle"></i> Tips</h3>
            </div>
            <div class="card-body">
                <ul class="pl-3 mb-0">
                    <li>Token JWT memiliki masa berlaku terbatas</li>
                    <li>Update token sebelum kadaluarsa</li>
                    <li>Test token setelah update</li>
                    <li>Simpan token lama sebagai backup</li>
                </ul>
            </div>
        </div>
    </div>
</div>
@stop

@section('js')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>
<script>
// Fix: accent-white memaksa nav-link jadi putih — set warna saat load + saat tab switch
$(function () {
    // Set warna awal saat halaman dimuat
    $('#tokenTabs .nav-link').css('color', '#495057');
    $('#tokenTabs .nav-link.active').css('color', '#007bff');

    // Update warna saat klik tab lain
    $('#tokenTabs .nav-link').on('shown.bs.tab', function () {
        $('#tokenTabs .nav-link').css('color', '#495057');
        $(this).css('color', '#007bff');
    });
});

// Shared token validation functions
function validateTokenFormat(token) {
    const parts = token.split('.');
    return parts.length === 3;
}

function decodeJwtPayload(token) {
    try {
        const parts = token.split('.');
        if (parts.length !== 3) {
            return null;
        }
        const payload = JSON.parse(atob(parts[1].replace(/-/g, '+').replace(/_/g, '/')));
        return payload;
    } catch (e) {
        return null;
    }
}

// Initialize token forms after DOM ready
$(document).ready(function() {
    console.log('Initializing token forms...');
    
    // Initialize each token form
    $('.form-update-token').each(function() {
        const form = $(this);
        const tokenType = form.data('token-type');
        const tokenInput = form.find('.token-input');
        const tokenInfo = form.find('.token-info');
        const formatStatus = form.find('.format-status');
        const expiryTime = form.find('.expiry-time');
        const btnSubmit = form.find('.btn-submit');

        console.log('Initializing form for token type:', tokenType);

        // Auto-validate token format on input
        tokenInput.on('input', function() {
            const token = $(this).val().trim();
            
            if (token.length > 100) {
                validateToken(token, tokenInfo, formatStatus, expiryTime);
            } else {
                tokenInfo.addClass('d-none');
            }
        });

        // Handle form submission
        form.on('submit', function(e) {
            e.preventDefault();
            
            const token = tokenInput.val().trim();
            
            if (token.length < 100) {
                Swal.fire({
                    icon: 'error',
                    title: 'Token Tidak Valid',
                    text: 'Token terlalu pendek. Pastikan Anda copy token lengkap.'
                });
                return;
            }

            // Disable submit button
            btnSubmit.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Menyimpan...');

            $.ajax({
                url: '{{ route("admin.pengaturan.update-api-token.update") }}',
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    token_type: tokenType,
                    token: token
                },
                success: function(response) {
                    if (response.success) {
                        let message = response.message;
                        if (response.expires_at) {
                            message += '<br><small>Kadaluarsa: ' + response.expires_at + '</small>';
                        }
                        if (!response.is_jwt) {
                            message += '<br><small class="text-warning">Token bukan format JWT</small>';
                        }

                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil!',
                            html: message,
                            timer: 3000,
                            showConfirmButton: false
                        }).then(() => {
                            window.location.reload();
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal',
                            text: response.message
                        });
                        btnSubmit.prop('disabled', false).html('<i class="fas fa-save"></i> Update Token');
                    }
                },
                error: function(xhr) {
                    let message = 'Terjadi kesalahan saat update token';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        message = xhr.responseJSON.message;
                    }
                    
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: message
                    });
                    
                    btnSubmit.prop('disabled', false).html('<i class="fas fa-save"></i> Update Token');
                }
            });
        });
    });
    
    // Validate token function
    function validateToken(token, tokenInfo, formatStatus, expiryTime) {
        const isJwt = validateTokenFormat(token);
        
        if (isJwt) {
            formatStatus.html('<span class="badge badge-success">Valid JWT</span>');
            
            const payload = decodeJwtPayload(token);
            if (payload && payload.exp) {
                const expiryDate = new Date(payload.exp * 1000);
                const now = new Date();
                
                if (expiryDate > now) {
                    expiryTime.html('<span class="badge badge-success">' + expiryDate.toLocaleString('id-ID') + '</span>');
                } else {
                    expiryTime.html('<span class="badge badge-danger">Sudah Kadaluarsa (' + expiryDate.toLocaleString('id-ID') + ')</span>');
                }
            } else {
                expiryTime.html('<span class="badge badge-warning">Tidak ada info expiry</span>');
            }
            
            tokenInfo.removeClass('d-none');
        } else {
            formatStatus.html('<span class="badge badge-warning">Bukan JWT (Token biasa)</span>');
            expiryTime.html('<span class="badge badge-secondary">N/A</span>');
            tokenInfo.removeClass('d-none');
        }
    }
});
</script>
@stop
