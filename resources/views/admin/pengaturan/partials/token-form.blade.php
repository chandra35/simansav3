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
            <textarea class="form-control" rows="2" readonly style="resize: none;">{{ $tokenData ? (!empty($tokenInfo['is_secret']) ? 'Tersimpan aman (nilai tidak dapat ditampilkan kembali)' : substr($tokenData->token, 0, 50) . '...' . substr($tokenData->token, -20)) : 'Belum dikonfigurasi' }}</textarea>
            <small class="form-text text-muted">{{ !empty($tokenInfo['is_secret']) ? 'Cookie dienkripsi dan tidak pernah dikirim kembali ke browser.' : 'Token ditampilkan sebagian untuk keamanan' }}</small>
        </div>

        <div class="form-group">
            <label>API URL</label>
            <input type="text" class="form-control" value="{{ $tokenInfo['api_url'] }}" readonly>
        </div>

        <hr>

        <div class="form-group">
            <label>{{ ($tokenInfo['credential_type'] ?? 'token') === 'cookie' ? 'Cookie Sesi Baru' : 'Token Baru' }} <span class="text-danger">*</span></label>
            <textarea class="form-control token-input" 
                      rows="5" 
                      name="token" 
                      placeholder="{{ ($tokenInfo['credential_type'] ?? 'token') === 'cookie' ? 'Paste nilai header Cookie dari request preview-simpeg...' : 'Paste token baru di sini...' }}"
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
            @if(!empty($tokenInfo['test_route']))
            <a href="{{ route($tokenInfo['test_route']) }}" class="btn btn-info" target="_blank">
                <i class="fas fa-vial"></i> Test Token
            </a>
            @endif
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
        @if($tokenType === 'emisgtk_session_cookie')
        <ol>
            <li>Login ke <strong>EMIS GTK</strong>, lalu buka halaman ubah/verval NIP.</li>
            <li>Buka Developer Tools (F12) dan pilih tab <strong>Network</strong>.</li>
            <li>Cari request <code>preview-simpeg</code>, lalu buka bagian <strong>Request Headers</strong>.</li>
            <li>Salin nilai header <code>Cookie</code> secara lengkap dan tempelkan di formulir ini.</li>
            <li>Pastikan terdapat <code>cookiesession1</code>, <code>csrftoken</code>, <code>emisSSO</code>, dan <code>sessionid</code>. Cookie <code>_ga</code> tidak diperlukan.</li>
        </ol>
        <div class="alert alert-danger mt-3 mb-0">
            <i class="icon fas fa-user-shield"></i>
            Cookie memberi akses ke sesi EMIS GTK. Jangan kirim melalui chat atau menyimpannya di file proyek.
            Cookie analitik seperti <code>_ga</code> akan dibuang otomatis.
        </div>
        @elseif($tokenType === 'emis_api_token')
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
