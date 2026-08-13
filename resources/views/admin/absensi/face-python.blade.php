@extends('adminlte::page')

@section('title', 'Face Python (Uji Coba)')
@section('plugins.Sweetalert2', true)

@section('content_header')
<div class="row mb-2">
    <div class="col-sm-6"><h1><i class="fab fa-python text-primary mr-2"></i>Face Python</h1></div>
    <div class="col-sm-6"><ol class="breadcrumb float-sm-right"><li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li><li class="breadcrumb-item">Presensi Gerbang</li><li class="breadcrumb-item active">Face Python</li></ol></div>
</div>
@stop

@section('content')
<style>
    .face-python-page .face-python-hero{border:0;border-radius:12px;box-shadow:0 8px 22px rgba(37,99,235,.16)}.face-python-page .face-python-hero .btn{white-space:nowrap}.face-python-page .metric-card{height:100%;border:1px solid #e6ebf4;border-radius:10px;box-shadow:0 4px 15px rgba(15,23,42,.06)}.face-python-page .metric-icon{display:grid;width:44px;height:44px;place-items:center;border-radius:9px;font-size:1.05rem}.face-python-page .token-field{font-family:Consolas,monospace;font-size:.78rem;letter-spacing:.02em}.face-python-page .step-number{display:grid;flex:0 0 34px;width:34px;height:34px;place-items:center;border-radius:50%;background:#e0e7ff;color:#4338ca;font-weight:800}.face-python-page .setup-step{display:flex;align-items:flex-start;gap:12px;padding:12px 0;border-bottom:1px solid #eef1f6}.face-python-page .setup-step:last-child{border-bottom:0}.face-python-page .comparison-table td,.face-python-page .comparison-table th{vertical-align:middle}.face-python-page .status-pulse{display:inline-block;width:9px;height:9px;margin-right:6px;border-radius:50%;background:#94a3b8}.face-python-page .status-pulse.online{background:#22c55e;box-shadow:0 0 0 5px rgba(34,197,94,.13)}@media(max-width:767.98px){.face-python-page .face-python-hero .hero-actions{width:100%;margin-top:12px}.face-python-page .face-python-hero .hero-actions .btn{display:block;width:100%;margin:5px 0!important}.face-python-page .token-actions{margin-top:8px}.face-python-page .token-actions .btn{flex:1}.face-python-page .metric-card{margin-bottom:12px}}
</style>

<div class="face-python-page">

@if(session('success'))<div class="alert alert-success alert-dismissible"><button type="button" class="close" data-dismiss="alert">&times;</button><i class="fas fa-check-circle mr-1"></i>{{ session('success') }}</div>@endif

<div class="card bg-gradient-primary text-white mb-4 face-python-hero">
    <div class="card-body"><div class="row align-items-center">
        <div class="col-lg-8">
            <small class="font-weight-bold text-uppercase"><i class="fas fa-flask mr-1"></i>Edge AI · Mode simulasi</small>
            <h3 class="font-weight-bold mb-1 mt-1">Deteksi multi-wajah tanpa mengubah Face Detect lama</h3>
            <p class="mb-0 text-white-50">Python berjalan pada PC kamera. SIMANSA hanya menyinkronkan referensi terverifikasi dan menerima status perangkat—belum mencatat presensi.</p>
        </div>
        <div class="col-lg-4 hero-actions text-lg-right">
            <a href="{{ route('admin.absensi.face-python.download') }}" class="btn btn-light text-primary font-weight-bold"><i class="fas fa-download mr-1"></i>Unduh Agent</a>
            <a href="{{ route('admin.absensi.face-detect') }}" class="btn btn-info ml-1"><i class="fas fa-eye mr-1"></i>Versi Browser</a>
        </div>
    </div></div>
</div>

<div class="row mb-3">
    <div class="col-6 col-lg-3"><div class="card metric-card"><div class="card-body d-flex align-items-center"><span class="metric-icon bg-light text-secondary mr-3"><i class="fas fa-satellite-dish"></i></span><div><small class="text-muted text-uppercase font-weight-bold">Status Agent</small><div class="font-weight-bold" id="agentStatus"><span class="status-pulse"></span>Belum terhubung</div></div></div></div></div>
    <div class="col-6 col-lg-3"><div class="card metric-card"><div class="card-body d-flex align-items-center"><span class="metric-icon bg-light text-info mr-3"><i class="fas fa-tachometer-alt"></i></span><div><small class="text-muted text-uppercase font-weight-bold">Performa</small><div class="font-weight-bold" id="agentFps">- FPS</div></div></div></div></div>
    <div class="col-6 col-lg-3"><div class="card metric-card"><div class="card-body d-flex align-items-center"><span class="metric-icon bg-light text-primary mr-3"><i class="fas fa-users"></i></span><div><small class="text-muted text-uppercase font-weight-bold">Wajah / Profil</small><div class="font-weight-bold" id="agentFaces">0 / 0</div></div></div></div></div>
    <div class="col-6 col-lg-3"><div class="card metric-card"><div class="card-body d-flex align-items-center"><span class="metric-icon bg-light text-success mr-3"><i class="fas fa-user-check"></i></span><div class="min-w-0"><small class="text-muted text-uppercase font-weight-bold">Terakhir Dikenali</small><div class="font-weight-bold text-truncate" id="lastRecognition">-</div></div></div></div></div>
</div>

<div class="row">
    <div class="col-lg-7">
        <div class="card card-outline card-primary pairing-card">
            <div class="card-header bg-white"><h3 class="card-title font-weight-bold"><i class="fas fa-link text-primary mr-1"></i>Pairing PC Kamera</h3></div>
            <div class="card-body">
                <div class="alert alert-info py-2"><i class="fas fa-shield-alt mr-1"></i>Token hanya dipasang pada PC kamera tepercaya. Agent tidak memakai akun atau sesi admin.</div>
                <label>Alamat API SIMANSA</label>
                <div class="input-group mb-3"><input id="apiBaseUrl" class="form-control token-field" value="{{ $apiBaseUrl }}" readonly><div class="input-group-append"><button class="btn btn-outline-primary copy-button" data-target="apiBaseUrl" type="button"><i class="far fa-copy"></i></button></div></div>
                <label>Bearer Token Perangkat</label>
                <div class="input-group"><input id="deviceToken" type="password" class="form-control token-field" value="{{ $deviceToken }}" readonly><div class="input-group-append token-actions"><button id="toggleToken" class="btn btn-outline-secondary" type="button" title="Tampilkan token"><i class="far fa-eye"></i></button><button class="btn btn-outline-primary copy-button" data-target="deviceToken" type="button" title="Salin token"><i class="far fa-copy"></i></button></div></div>
                <small class="form-text text-muted">Rotasi token segera jika token pernah dibagikan ke perangkat yang tidak dipercaya.</small>
            </div>
            <div class="card-footer bg-white d-flex align-items-center justify-content-between flex-wrap">
                <small class="text-muted"><i class="fas fa-lock mr-1"></i>Endpoint memakai HTTPS, Bearer token, rate limit, dan respons no-store.</small>
                <form method="POST" action="{{ route('admin.absensi.face-python.rotate-token') }}" id="rotateTokenForm">@csrf<button type="button" id="rotateTokenButton" class="btn btn-outline-danger btn-sm"><i class="fas fa-sync-alt mr-1"></i>Rotasi Token</button></form>
            </div>
        </div>

        <div class="card card-outline card-primary">
            <div class="card-header"><h3 class="card-title font-weight-bold"><i class="fas fa-balance-scale mr-1"></i>Perbandingan Percobaan</h3></div>
            <div class="table-responsive"><table class="table table-sm comparison-table mb-0"><thead><tr><th>Aspek</th><th>Face Detect Browser</th><th>Face Python</th></tr></thead><tbody><tr><td>Engine</td><td>face-api.js</td><td>InsightFace + ONNX</td></tr><tr><td>Multi-wajah</td><td>Terbatas</td><td><span class="badge badge-success">Paralel + tracking</span></td></tr><tr><td>Menjalankan kamera</td><td>Tab browser</td><td>PC Edge Agent</td></tr><tr><td>Data presensi</td><td>Tidak dicatat</td><td>Tidak dicatat (simulasi)</td></tr><tr><td>Data lama</td><td>Tetap digunakan</td><td>Foto registrasi dibentuk menjadi embedding lokal</td></tr></tbody></table></div>
        </div>
    </div>
    <div class="col-lg-5">
        <div class="card card-outline card-info">
            <div class="card-header"><h3 class="card-title font-weight-bold"><i class="fab fa-windows mr-1"></i>Instalasi PC Gerbang</h3></div>
            <div class="card-body py-2">
                <div class="setup-step"><span class="step-number">1</span><div><strong>Unduh dan ekstrak Agent</strong><p class="text-muted small mb-0">Gunakan Windows 10/11 64-bit. Python 3.10 atau 3.11 direkomendasikan.</p></div></div>
                <div class="setup-step"><span class="step-number">2</span><div><strong>Pasang dependensi</strong><code class="d-block mt-1">pip install -r requirements.txt</code></div></div>
                <div class="setup-step"><span class="step-number">3</span><div><strong>Buat config.json</strong><p class="text-muted small mb-0">Salin config.example.json, lalu isi API URL dan token pairing di atas.</p></div></div>
                <div class="setup-step"><span class="step-number">4</span><div><strong>Jalankan simulasi</strong><code class="d-block mt-1">python agent.py</code><p class="text-muted small mb-0 mt-1">Tekan Q untuk keluar dan F untuk layar penuh.</p></div></div>
            </div>
        </div>
        <div class="card card-outline card-warning">
            <div class="card-header"><h3 class="card-title font-weight-bold"><i class="fas fa-info-circle mr-1"></i>Batas Percobaan</h3></div>
            <div class="card-body small"><ul class="pl-3 mb-0"><li>Model awal diunduh oleh InsightFace pada PC saat pertama dijalankan.</li><li>Foto referensi tersimpan sebagai embedding lokal di PC; jangan memakai PC publik.</li><li>Ambang cocok tetap konservatif. Hasil meragukan ditampilkan sebagai unknown.</li><li>Belum ada penulisan absensi, foto tangkapan, atau histori ke database.</li></ul></div>
        </div>
    </div>
</div>
</div>
@stop

@section('js')
<script>
(() => {
    const initialStatus = @json($deviceStatus);
    const statusUrl = @json(route('admin.absensi.face-python.status'));
    const applyStatus = status => {
        const online = Boolean(status && status.online);
        document.getElementById('agentStatus').innerHTML = `<span class="status-pulse ${online ? 'online' : ''}"></span>${online ? 'Online' : 'Offline'}${status.device_name ? ` · ${status.device_name}` : ''}`;
        document.getElementById('agentFps').textContent = `${Number(status.fps || 0).toFixed(1)} FPS`;
        document.getElementById('agentFaces').textContent = `${status.faces_in_frame || 0} / ${status.profiles || 0}`;
        document.getElementById('lastRecognition').textContent = status.recognized_name ? `${status.recognized_name} (${Math.round((status.confidence || 0) * 100)}%)` : '-';
    };
    applyStatus(initialStatus);
    setInterval(async () => { try { const response = await fetch(statusUrl, {headers:{Accept:'application/json'}, cache:'no-store'}); if (response.ok) applyStatus((await response.json()).status); } catch (_) {} }, 10000);

    document.querySelectorAll('.copy-button').forEach(button => button.addEventListener('click', async () => {
        const field = document.getElementById(button.dataset.target);
        try { await navigator.clipboard.writeText(field.value); Swal.fire({toast:true,position:'top-end',icon:'success',title:'Berhasil disalin',showConfirmButton:false,timer:1600}); }
        catch (_) { field.type = 'text'; field.select(); document.execCommand('copy'); }
    }));
    document.getElementById('toggleToken').addEventListener('click', event => { const field = document.getElementById('deviceToken'); field.type = field.type === 'password' ? 'text' : 'password'; event.currentTarget.querySelector('i').className = field.type === 'password' ? 'far fa-eye' : 'far fa-eye-slash'; });
    document.getElementById('rotateTokenButton').addEventListener('click', async () => { const result = await Swal.fire({icon:'warning',title:'Rotasi token perangkat?',text:'Agent yang sedang terhubung akan langsung offline sampai config.json diperbarui.',showCancelButton:true,confirmButtonText:'Ya, rotasi',cancelButtonText:'Batal',confirmButtonColor:'#dc3545'}); if (result.isConfirmed) document.getElementById('rotateTokenForm').submit(); });
})();
</script>
@stop
