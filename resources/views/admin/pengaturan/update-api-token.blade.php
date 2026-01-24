@extends('adminlte::page')

@section('title', 'Update API Token')

@section('content_header')
    <h1 class="m-0 text-dark">Update API Token</h1>
@stop

@section('plugins.Sweetalert2', true)

@section('content')
<div class="row">
    <div class="col-md-9">
        <div class="card card-primary card-outline card-outline-tabs">
            <div class="card-header p-0 border-bottom-0">
                <ul class="nav nav-tabs" id="tokenTabs" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" id="emis-tab" data-toggle="pill" href="#emis" role="tab">
                            <i class="fas fa-user-graduate"></i> Token EMIS (NISN)
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="kemenag-tab" data-toggle="pill" href="#kemenag" role="tab">
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
<script>
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
