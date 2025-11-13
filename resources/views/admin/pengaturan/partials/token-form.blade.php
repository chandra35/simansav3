{{-- Token Form Partial --}}
<div class="token-form-container">
    @if($tokenData && $tokenData->expires_at)
    <div class="alert {{ strtotime($tokenData->expires_at) > time() ? 'alert-success' : 'alert-danger' }}">
        <i class="icon fas {{ strtotime($tokenData->expires_at) > time() ? 'fa-check-circle' : 'fa-exclamation-triangle' }}"></i>
        <strong>Status Token:</strong>
        @if(strtotime($tokenData->expires_at) > time())
            Aktif (Kadaluarsa: {{ \Carbon\Carbon::parse($tokenData->expires_at)->format('d F Y H:i:s') }})
        @else
            Kadaluarsa ({{ \Carbon\Carbon::parse($tokenData->expires_at)->format('d F Y H:i:s') }})
        @endif
    </div>
    @endif

    <form class="form-update-token" data-token-type="{{ $tokenType }}">
        @csrf

        <div class="form-group">
            <label>Token Saat Ini</label>
            <textarea class="form-control" rows="2" readonly style="resize: none;">{{ $tokenData ? substr($tokenData->token, 0, 50) . '...' . substr($tokenData->token, -20) : 'Belum ada token' }}</textarea>
            <small class="form-text text-muted">Token ditampilkan sebagian untuk keamanan</small>
        </div>

        <div class="form-group">
            <label>API URL</label>
            <input type="text" class="form-control" value="{{ $tokenInfo['api_url'] }}" readonly>
        </div>

        <hr>

        <div class="form-group">
            <label>Token Baru <span class="text-danger">*</span></label>
            <textarea class="form-control token-input" 
                      rows="5" 
                      name="token" 
                      placeholder="Paste token baru di sini..."
                      style="resize: vertical; min-height: 120px;"
                      required></textarea>
            <small class="form-text text-muted">Paste token lengkap yang didapat dari {{ $tokenName }}</small>
        </div>

        <div class="token-info alert alert-secondary d-none">
            <strong>Info Token:</strong>
            <ul class="mb-0 mt-2">
                <li>Format: <span class="format-status"></span></li>
                <li>Expires: <span class="expiry-time"></span></li>
            </ul>
        </div>

        <div class="form-group">
            <button type="submit" class="btn btn-primary btn-submit">
                <i class="fas fa-save"></i> Update Token
            </button>
            <button type="button" class="btn btn-secondary" onclick="window.location.reload()">
                <i class="fas fa-redo"></i> Reset
            </button>
            <a href="{{ route($tokenInfo['test_route']) }}" class="btn btn-info" target="_blank">
                <i class="fas fa-vial"></i> Test Token
            </a>
        </div>
    </form>
</div>

<div class="card card-info collapsed-card mt-3">
    <div class="card-header">
        <h3 class="card-title">Cara Mendapatkan Token</h3>
        <div class="card-tools">
            <button type="button" class="btn btn-tool" data-card-widget="collapse">
                <i class="fas fa-plus"></i>
            </button>
        </div>
    </div>
    <div class="card-body">
        @if($tokenType === 'emis_api_token')
        <ol>
            <li>Login ke sistem EMIS Kemenag</li>
            <li>Buka Developer Tools browser (F12)</li>
            <li>Pergi ke tab <strong>Network</strong></li>
            <li>Lakukan pencarian NISN atau akses API</li>
            <li>Cari request API, klik request tersebut</li>
            <li>Pergi ke tab <strong>Headers</strong></li>
            <li>Cari <strong>Authorization: Bearer eyJ0eXAi...</strong></li>
            <li>Copy token setelah kata "Bearer " (tanpa kata Bearer)</li>
        </ol>
        @else
        <ol>
            <li>Login ke sistem BE-PINTAR Kemenag</li>
            <li>Buka Developer Tools browser (F12)</li>
            <li>Pergi ke tab <strong>Network</strong></li>
            <li>Lakukan pencarian NIP atau akses fitur GTK</li>
            <li>Cari request API ke be-pintar.kemenag.go.id</li>
            <li>Klik request tersebut → Tab <strong>Headers</strong></li>
            <li>Cari <strong>Authorization: Bearer ...</strong></li>
            <li>Copy token setelah kata "Bearer"</li>
        </ol>
        @endif
        
        <div class="alert alert-warning mt-3">
            <i class="icon fas fa-exclamation-triangle"></i>
            <strong>Perhatian:</strong> Token memiliki masa berlaku terbatas. 
            Jika fitur tidak berfungsi, kemungkinan token sudah kadaluarsa.
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    const form = $('.form-update-token[data-token-type="{{ $tokenType }}"]');
    const tokenInput = form.find('.token-input');
    const tokenInfo = form.find('.token-info');
    const formatStatus = form.find('.format-status');
    const expiryTime = form.find('.expiry-time');
    const btnSubmit = form.find('.btn-submit');

    // Auto-validate token format on input
    tokenInput.on('input', function() {
        const token = $(this).val().trim();
        
        if (token.length > 100) {
            validateToken(token);
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
                token_type: '{{ $tokenType }}',
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

    function validateToken(token) {
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
