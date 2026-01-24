@extends('adminlte::page')

@section('title', 'Update Token EMIS')

@section('content_header')
    <h1 class="m-0 text-dark">Update Token EMIS</h1>
@stop

@section('plugins.Sweetalert2', true)

@section('content')
<div class="row">
    <div class="col-md-8">
        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title">Token Bearer API EMIS Kemenag</h3>
            </div>

            <form id="formUpdateToken">
                @csrf
                <div class="card-body">
                    <div class="alert alert-info">
                        <i class="icon fas fa-info-circle"></i>
                        Token EMIS digunakan untuk fitur <strong>Cek NISN Siswa</strong>. Token ini bersifat JWT dan memiliki masa berlaku sekitar 4-5 jam.
                    </div>

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

                    <div class="form-group">
                        <label for="current_token">Token Saat Ini</label>
                        <textarea class="form-control" rows="3" id="current_token" readonly>{{ $tokenData ? substr($tokenData->token, 0, 50) . '...' . substr($tokenData->token, -20) : 'Belum ada token' }}</textarea>
                        <small class="form-text text-muted">Token ditampilkan sebagian untuk keamanan</small>
                    </div>

                    <hr>

                    <div class="form-group">
                        <label for="new_token">Token Baru <span class="text-danger">*</span></label>
                        <textarea class="form-control @error('token') is-invalid @enderror" 
                                  rows="5" 
                                  id="new_token" 
                                  name="token" 
                                  placeholder="Paste token JWT baru di sini (format: eyJ0eXAi...)"
                                  required></textarea>
                        <small class="form-text text-muted">
                            Paste token JWT lengkap yang didapat dari API EMIS Kemenag
                        </small>
                        @error('token')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>

                    <div id="tokenInfo" class="alert alert-secondary d-none">
                        <strong>Info Token:</strong>
                        <ul class="mb-0 mt-2">
                            <li>Format: <span id="formatStatus"></span></li>
                            <li>Expires: <span id="expiryTime"></span></li>
                        </ul>
                    </div>
                </div>

                <div class="card-footer">
                    <button type="submit" class="btn btn-primary" id="btnSubmit">
                        <i class="fas fa-save"></i> Update Token
                    </button>
                    <button type="button" class="btn btn-secondary" onclick="window.location.reload()">
                        <i class="fas fa-redo"></i> Reset
                    </button>
                </div>
            </form>
        </div>

        <div class="card card-info collapsed-card">
            <div class="card-header">
                <h3 class="card-title">Cara Mendapatkan Token Baru</h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-tool" data-card-widget="collapse">
                        <i class="fas fa-plus"></i>
                    </button>
                </div>
            </div>
            <div class="card-body">
                <ol>
                    <li>Login ke sistem EMIS Kemenag</li>
                    <li>Buka Developer Tools browser (F12)</li>
                    <li>Pergi ke tab <strong>Network</strong></li>
                    <li>Lakukan pencarian NISN atau akses API</li>
                    <li>Cari request API, klik request tersebut</li>
                    <li>Pergi ke tab <strong>Headers</strong></li>
                    <li>Cari <strong>Authorization: Bearer eyJ0eXAi...</strong></li>
                    <li>Copy token setelah kata "Bearer " (tanpa kata Bearer)</li>
                    <li>Paste di form di atas</li>
                </ol>
                
                <div class="alert alert-warning mt-3">
                    <i class="icon fas fa-exclamation-triangle"></i>
                    <strong>Perhatian:</strong> Token JWT memiliki masa berlaku terbatas (±4-5 jam). 
                    Jika fitur Cek NISN error, kemungkinan token sudah kadaluarsa.
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card card-secondary">
            <div class="card-header">
                <h3 class="card-title">Informasi</h3>
            </div>
            <div class="card-body">
                <dl>
                    <dt>Fungsi Token</dt>
                    <dd>Mengakses API EMIS Kemenag untuk validasi NISN siswa</dd>

                    <dt>Fitur Terkait</dt>
                    <dd>
                        <a href="{{ route('admin.pengaturan.cek-nisn.index') }}" target="_blank">
                            <i class="fas fa-external-link-alt"></i> Cek NISN Siswa
                        </a>
                    </dd>

                    <dt>Format Token</dt>
                    <dd>JWT (JSON Web Token)</dd>

                    <dt>Masa Berlaku</dt>
                    <dd>±4-5 jam dari waktu generate</dd>

                    <dt>Terakhir Update</dt>
                    <dd>{{ $tokenData && $tokenData->updated_at ? \Carbon\Carbon::parse($tokenData->updated_at)->format('d F Y H:i:s') : '-' }}</dd>
                </dl>
            </div>
        </div>

        <div class="card card-warning">
            <div class="card-header">
                <h3 class="card-title">Testing Token</h3>
            </div>
            <div class="card-body">
                <p>Setelah update token, test dengan mencoba fitur:</p>
                <a href="{{ route('admin.pengaturan.cek-nisn.index') }}" class="btn btn-block btn-outline-primary" target="_blank">
                    <i class="fas fa-search"></i> Test Cek NISN
                </a>
            </div>
        </div>
    </div>
</div>
@stop

@section('js')
<script>
$(document).ready(function() {
    // Auto-validate token format on input
    $('#new_token').on('input', function() {
        const token = $(this).val().trim();
        
        if (token.length > 100) {
            validateTokenFormat(token);
        } else {
            $('#tokenInfo').addClass('d-none');
        }
    });

    // Handle form submission
    $('#formUpdateToken').on('submit', function(e) {
        e.preventDefault();
        
        const token = $('#new_token').val().trim();
        
        if (token.length < 100) {
            Swal.fire({
                icon: 'error',
                title: 'Token Tidak Valid',
                text: 'Token terlalu pendek. Pastikan Anda copy token JWT lengkap.'
            });
            return;
        }

        // Disable submit button
        $('#btnSubmit').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Menyimpan...');

        $.ajax({
            url: '{{ route("admin.pengaturan.update-emis-token.update") }}',
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                token: token
            },
            success: function(response) {
                if (response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        html: response.message + 
                              (response.expires_at ? '<br><small>Kadaluarsa: ' + response.expires_at + '</small>' : ''),
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
                    $('#btnSubmit').prop('disabled', false).html('<i class="fas fa-save"></i> Update Token');
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
                
                $('#btnSubmit').prop('disabled', false).html('<i class="fas fa-save"></i> Update Token');
            }
        });
    });

    function validateTokenFormat(token) {
        const parts = token.split('.');
        
        if (parts.length === 3) {
            $('#formatStatus').html('<span class="badge badge-success">Valid JWT</span>');
            
            // Try to decode payload
            try {
                const payload = JSON.parse(atob(parts[1].replace(/-/g, '+').replace(/_/g, '/')));
                
                if (payload.exp) {
                    const expiryDate = new Date(payload.exp * 1000);
                    const now = new Date();
                    
                    if (expiryDate > now) {
                        $('#expiryTime').html('<span class="badge badge-success">' + expiryDate.toLocaleString('id-ID') + '</span>');
                    } else {
                        $('#expiryTime').html('<span class="badge badge-danger">Sudah Kadaluarsa (' + expiryDate.toLocaleString('id-ID') + ')</span>');
                    }
                } else {
                    $('#expiryTime').html('<span class="badge badge-warning">Tidak ada info expiry</span>');
                }
                
                $('#tokenInfo').removeClass('d-none');
            } catch (e) {
                $('#expiryTime').text('Error decode payload');
                $('#tokenInfo').removeClass('d-none');
            }
        } else {
            $('#formatStatus').html('<span class="badge badge-danger">Format tidak valid</span>');
            $('#expiryTime').text('-');
            $('#tokenInfo').removeClass('d-none');
        }
    }
});
</script>
@stop
