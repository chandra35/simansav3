@extends('adminlte::page')

@section('title', 'Kiosk QR Siswa')

@section('content_header')
<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center">
    <div><h1 class="mb-1"><i class="fas fa-qrcode text-primary mr-2"></i>Kiosk QR Siswa</h1><small class="text-muted">Buat QR dinamis untuk absensi harian dan presensi sholat.</small></div>
    <a href="{{ route('admin.absensi.settings') }}" class="btn btn-outline-primary mt-2 mt-md-0"><i class="fas fa-sliders-h mr-1"></i>Pengaturan metode</a>
</div>
@stop

@section('content')
<div class="row qr-kiosk-page">
    <div class="col-lg-5 mb-3">
        <div class="card card-primary card-outline shadow-sm h-100">
            <div class="card-body">
                <span class="badge badge-primary mb-2">LANGKAH 1</span>
                <h5 class="font-weight-bold">Pilih sesi yang dibuka</h5>
                <p class="text-muted small">QR aman hanya berlaku untuk satu sesi dan otomatis berganti saat dibuat ulang.</p>
                <div class="form-group"><label>Tanggal sesi</label><input id="attendance-date" type="date" class="form-control" value="{{ $date }}" onchange="window.location='{{ route('admin.absensi.qr-kiosk') }}?tanggal='+this.value"></div>
                <div class="qr-session-list">
                    @if($dailyEnabled)
                        <div class="qr-section-label"><i class="fas fa-calendar-check mr-1"></i>Absensi harian</div>
                        @forelse($dailySessions as $session)
                            <button class="qr-session-button" data-kind="daily" data-url="{{ route('admin.absensi-siswa.qr-token.issue', $session) }}" data-title="Absensi harian · {{ $session->kelas_snapshot ?: optional($session->kelas)->nama_kelas }}"><i class="fas fa-user-graduate"></i><span><strong>{{ $session->kelas_snapshot ?: optional($session->kelas)->nama_kelas }}</strong><small>{{ $session->records()->count() }} presensi tercatat</small></span><i class="fas fa-chevron-right ml-auto"></i></button>
                        @empty <div class="empty-state">Belum ada sesi harian aktif. Buat sesi dari Absensi Siswa terlebih dahulu.</div> @endforelse
                    @endif
                    @if($sholatEnabled)
                        <div class="qr-section-label mt-3"><i class="fas fa-mosque mr-1"></i>Presensi sholat</div>
                        @forelse($sholatSessions as $session)
                            <button class="qr-session-button" data-kind="sholat" data-url="{{ route('admin.sholat.qr-token.issue', $session) }}" data-title="{{ optional($session->sholatSetting)->nama }} · {{ optional($session->kelas)->nama_kelas }}"><i class="fas fa-mosque"></i><span><strong>{{ optional($session->sholatSetting)->nama }}</strong><small>{{ optional($session->kelas)->nama_kelas }}</small></span><i class="fas fa-chevron-right ml-auto"></i></button>
                        @empty <div class="empty-state">Belum ada sesi sholat aktif untuk tanggal ini.</div> @endforelse
                    @endif
                    @if(!$dailyEnabled && !$sholatEnabled)<div class="alert alert-warning mb-0">QR sedang dinonaktifkan dari pengaturan absensi.</div>@endif
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-7 mb-3">
        <div class="card qr-display-card shadow-sm h-100"><div class="card-body d-flex flex-column align-items-center justify-content-center text-center">
            <div id="qr-placeholder"><div class="qr-placeholder-icon"><i class="fas fa-qrcode"></i></div><h4>Pilih sesi untuk membuat QR</h4><p>QR berlaku selama <strong>{{ $defaultTtl }} menit</strong> dan hanya dapat dipakai oleh siswa pada rombel terkait.</p></div>
            <div id="qr-result" class="d-none"><span class="badge badge-success mb-2">QR AKTIF</span><h3 id="qr-title" class="font-weight-bold"></h3><div id="qr-image" class="qr-image my-3"></div><p id="qr-expiry" class="text-muted mb-3"></p><button id="refresh-qr" class="btn btn-outline-light"><i class="fas fa-sync-alt mr-1"></i>Buat QR baru</button></div>
        </div></div>
    </div>
</div>
@stop

@section('css')
<style>
.qr-kiosk-page .card{border-radius:16px}.qr-session-list{max-height:58vh;overflow:auto}.qr-section-label{font-size:.75rem;font-weight:800;letter-spacing:.08em;color:#64748b;text-transform:uppercase}.qr-session-button{width:100%;border:1px solid #e2e8f0;background:#fff;border-radius:12px;padding:13px;display:flex;gap:12px;align-items:center;text-align:left;margin-top:8px;transition:.2s}.qr-session-button:hover{border-color:#3b82f6;background:#eff6ff;transform:translateY(-1px)}.qr-session-button>i:first-child{width:38px;height:38px;display:grid;place-items:center;border-radius:10px;background:#dbeafe;color:#2563eb}.qr-session-button span{display:grid;gap:2px}.qr-session-button small{color:#64748b}.qr-display-card{background:linear-gradient(145deg,#0f172a,#1d4ed8);color:#fff;min-height:520px}.qr-display-card p{color:#cbd5e1}.qr-placeholder-icon{font-size:5rem;color:#93c5fd}.qr-image{background:#fff;padding:14px;border-radius:16px;line-height:0;display:inline-block}.qr-image canvas,.qr-image img{width:min(280px,68vw)!important;height:auto!important}.empty-state{border:1px dashed #cbd5e1;border-radius:10px;padding:13px;color:#64748b;font-size:.875rem;margin-top:8px}
</style>
@stop

@section('js')
<script src="https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js"></script>
<script>
(() => {const csrf=document.querySelector('meta[name="csrf-token"]').content;let current=null;
const result=document.getElementById('qr-result'), placeholder=document.getElementById('qr-placeholder'), target=document.getElementById('qr-image');
async function issue(url,title){current={url,title};const response=await fetch(url,{method:'POST',headers:{'X-CSRF-TOKEN':csrf,'Accept':'application/json','Content-Type':'application/json'},body:JSON.stringify({ttl_minutes:{{ (int) $defaultTtl }})});const payload=await response.json();if(!response.ok){alert(payload.message || 'QR tidak dapat dibuat.');return} target.innerHTML='';new QRCode(target,{text:payload.data.token,width:280,height:280,correctLevel:QRCode.CorrectLevel.M});document.getElementById('qr-title').textContent=title;document.getElementById('qr-expiry').textContent='Berlaku hingga '+new Date(payload.data.expires_at).toLocaleTimeString('id-ID',{hour:'2-digit',minute:'2-digit'})+' WIB';placeholder.classList.add('d-none');result.classList.remove('d-none')}
document.querySelectorAll('.qr-session-button').forEach(el=>el.addEventListener('click',()=>issue(el.dataset.url,el.dataset.title)));document.getElementById('refresh-qr').addEventListener('click',()=>current&&issue(current.url,current.title));})();
</script>
@stop
