<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @if($isPublicMode ?? false)<meta name="robots" content="noindex,nofollow,noarchive"><meta name="referrer" content="no-referrer">@endif
    <title>Face Detect Percobaan - SIMANSA</title>
    <link rel="stylesheet" href="{{ asset('vendor/fontawesome-free/css/all.min.css') }}">
    <style>
        :root{color-scheme:dark;--bg:#071125;--panel:#101d38;--line:#24375b;--primary:#38bdf8;--success:#34d399;--muted:#94a3b8}*{box-sizing:border-box}body{min-height:100vh;margin:0;background:radial-gradient(circle at 20% 0,#172554 0,transparent 34%),var(--bg);color:#f8fafc;font-family:Inter,Segoe UI,Arial,sans-serif}.door-shell{display:grid;min-height:100vh;grid-template-rows:auto 1fr auto}.door-header{display:flex;align-items:center;justify-content:space-between;gap:16px;padding:14px 20px;border-bottom:1px solid var(--line);background:rgba(7,17,37,.9)}.brand{display:flex;align-items:center;gap:12px}.brand-mark{display:grid;width:42px;height:42px;place-items:center;border-radius:12px;background:linear-gradient(135deg,#2563eb,#06b6d4);font-size:1.1rem}.brand strong,.brand small{display:block}.brand small{margin-top:2px;color:var(--muted);font-size:.72rem}.public-badge{display:inline-flex;align-items:center;gap:5px;margin-left:7px;padding:3px 7px;border-radius:999px;background:rgba(52,211,153,.15);color:#6ee7b7;font-size:.58rem;font-weight:800;text-transform:uppercase}.header-actions{display:flex;flex-wrap:wrap;gap:8px}.btn{padding:9px 12px;border:1px solid var(--line);border-radius:9px;background:#17233d;color:#fff;cursor:pointer;font-weight:700}.btn:hover{border-color:#60a5fa}.btn-primary{border-color:#0284c7;background:#0284c7}.btn-success{border-color:#059669;background:#059669}.btn-danger{border-color:#be123c;background:#be123c}.btn:disabled{cursor:not-allowed;opacity:.55}.flash-message{margin:12px 14px 0;padding:10px 13px;border:1px solid rgba(52,211,153,.35);border-radius:9px;background:rgba(52,211,153,.1);color:#a7f3d0;font-size:.75rem}.door-main{display:grid;grid-template-columns:minmax(0,1fr) 330px;gap:14px;padding:14px}.camera-card,.side-card{overflow:hidden;border:1px solid var(--line);border-radius:16px;background:rgba(15,29,56,.92);box-shadow:0 18px 42px rgba(0,0,0,.2)}.camera-stage{position:relative;min-height:520px;background:#020617}.camera-stage video,.camera-stage canvas{position:absolute;width:100%;height:100%;inset:0;object-fit:cover}.camera-stage video{transform:scaleX(-1)}.camera-stage canvas{z-index:2;pointer-events:none}.start-overlay{position:absolute;z-index:4;display:flex;align-items:center;justify-content:center;flex-direction:column;inset:0;padding:24px;background:radial-gradient(circle,rgba(30,64,175,.42),rgba(2,6,23,.96));text-align:center}.start-overlay i{margin-bottom:18px;color:var(--primary);font-size:3rem}.start-overlay h1{margin:0 0 8px;font-size:1.55rem}.start-overlay p{max-width:520px;margin:0 0 20px;color:#cbd5e1;line-height:1.55}.camera-toolbar{display:flex;align-items:center;justify-content:space-between;gap:12px;padding:11px 14px}.camera-status{display:flex;align-items:center;gap:8px;font-size:.82rem;font-weight:700}.status-dot{width:9px;height:9px;border-radius:50%;background:#f59e0b;box-shadow:0 0 12px currentColor}.camera-select{max-width:260px;padding:7px 9px;border:1px solid var(--line);border-radius:8px;background:#0b1730;color:#fff}.side-card{padding:17px}.side-title{margin:0 0 4px;font-size:.72rem;letter-spacing:.08em;text-transform:uppercase;color:#7dd3fc}.side-subtitle{margin:0 0 16px;color:var(--muted);font-size:.76rem}.recognition{display:flex;min-height:270px;align-items:center;justify-content:center;flex-direction:column;padding:18px;border:1px dashed #334a73;border-radius:14px;text-align:center}.recognition img{width:112px;height:132px;object-fit:cover;border:3px solid #fff;border-radius:16px;box-shadow:0 0 0 4px rgba(52,211,153,.25)}.recognition h2{margin:13px 0 4px;font-size:1.25rem}.recognition p{margin:0;color:var(--muted);font-size:.8rem}.recognition-badge{margin-top:10px;padding:5px 9px;border-radius:999px;background:rgba(52,211,153,.15);color:#6ee7b7;font-size:.68rem;font-weight:800}.waiting-icon{display:grid;width:76px;height:76px;place-items:center;border-radius:50%;background:rgba(56,189,248,.1);color:var(--primary);font-size:1.8rem}.metrics{display:grid;grid-template-columns:1fr 1fr;gap:9px;margin-top:12px}.metric{padding:11px;border-radius:10px;background:#0b1730}.metric small,.metric strong{display:block}.metric small{color:var(--muted);font-size:.66rem}.metric strong{margin-top:4px;font-size:.9rem}.notice{margin-top:12px;padding:10px;border-left:3px solid #f59e0b;border-radius:7px;background:rgba(245,158,11,.09);color:#fde68a;font-size:.7rem;line-height:1.45}.door-footer{padding:8px 16px;color:#64748b;font-size:.66rem;text-align:center}.is-hidden{display:none!important}.settings-dialog{width:min(720px,calc(100vw - 24px));max-height:90vh;padding:0;overflow:hidden;border:1px solid #334a73;border-radius:16px;background:#0d1930;color:#f8fafc;box-shadow:0 28px 80px rgba(0,0,0,.55)}.settings-dialog::backdrop{background:rgba(2,6,23,.78);backdrop-filter:blur(4px)}.settings-header,.settings-footer{display:flex;align-items:center;justify-content:space-between;gap:12px;padding:15px 18px;border-color:#24375b;background:#111f3b}.settings-header{border-bottom:1px solid #24375b}.settings-header h2{margin:0;font-size:1.05rem}.settings-header p{margin:4px 0 0;color:#94a3b8;font-size:.7rem}.settings-close{border:0;background:transparent;color:#94a3b8;cursor:pointer;font-size:1.2rem}.settings-body{max-height:calc(90vh - 132px);padding:17px 18px;overflow-y:auto}.settings-grid{display:grid;grid-template-columns:1fr 1fr;gap:14px}.setting-field{min-width:0}.setting-field.is-wide{grid-column:1/-1}.setting-field label{display:flex;align-items:center;justify-content:space-between;gap:8px;margin-bottom:6px;color:#cbd5e1;font-size:.72rem;font-weight:700}.setting-field label output{color:#7dd3fc}.setting-field select,.setting-field textarea,.setting-field input[type=text]{width:100%;padding:9px 10px;border:1px solid #334a73;border-radius:8px;background:#09152c;color:#f8fafc}.setting-field textarea{min-height:72px;resize:vertical}.setting-field input[type=range]{width:100%;accent-color:#38bdf8}.setting-field small{display:block;margin-top:5px;color:#7f8ea8;font-size:.65rem;line-height:1.4}.setting-check{display:flex;align-items:center;gap:9px;padding:10px;border:1px solid #273b61;border-radius:9px;background:#0a1730;color:#cbd5e1;font-size:.72rem}.setting-preview{padding:11px;border-left:3px solid #38bdf8;border-radius:8px;background:rgba(56,189,248,.08);color:#bae6fd;font-size:.72rem}.public-url{width:100%;padding:10px;border:1px solid #334a73;border-radius:8px;background:#071225;color:#bae6fd;font-family:Consolas,monospace;font-size:.68rem}.public-warning{margin-top:12px;padding:10px;border-left:3px solid #f59e0b;border-radius:8px;background:rgba(245,158,11,.09);color:#fde68a;font-size:.7rem;line-height:1.45}.settings-footer{flex-wrap:wrap;border-top:1px solid #24375b}.settings-footer__right{display:flex;flex-wrap:wrap;gap:8px}@media(max-width:900px){.door-main{grid-template-columns:1fr}.camera-stage{min-height:430px}.side-card{display:grid;grid-template-columns:1fr 1fr;gap:14px}.side-card>.side-title,.side-card>.side-subtitle{grid-column:1/-1}.recognition{min-height:220px}}@media(max-width:600px){.door-header{align-items:flex-start;flex-direction:column}.door-main{padding:8px}.camera-stage{min-height:390px}.side-card{display:block}.camera-toolbar{align-items:stretch;flex-direction:column}.camera-select{max-width:none;width:100%}.settings-grid{grid-template-columns:1fr}.setting-field.is-wide{grid-column:auto}.settings-footer{align-items:stretch;flex-direction:column}.settings-footer__right{display:grid;grid-template-columns:1fr 1fr}.settings-footer .btn{width:100%}}
    </style>
</head>
<body>
<div class="door-shell">
    <header class="door-header">
        <div class="brand"><div class="brand-mark"><i class="fas fa-eye"></i></div><div><strong>Face Detect @if($isPublicMode ?? false)<span class="public-badge"><i class="fas fa-link"></i> Perangkat publik</span>@endif</strong><small>Mode percobaan pintu · SIMANSA</small></div></div>
        <div class="header-actions">
            @unless($isPublicMode ?? false)<button class="btn" id="publicAccessButton" type="button"><i class="fas fa-link"></i> Akses publik</button>@endunless
            <button class="btn" id="settingsButton" type="button"><i class="fas fa-sliders-h"></i> Pengaturan suara</button>
            <button class="btn" id="voiceButton" type="button"><i class="fas fa-volume-up"></i> Suara aktif</button>
            <button class="btn" id="fullscreenButton" type="button"><i class="fas fa-expand"></i> Layar penuh</button>
            @unless($isPublicMode ?? false)<a class="btn" href="{{ route('admin.absensi.index') }}" style="text-decoration:none"><i class="fas fa-arrow-left"></i> Kembali</a>@endunless
        </div>
    </header>

    @if(session('success'))<div class="flash-message"><i class="fas fa-check-circle"></i> {{ session('success') }}</div>@endif

    <main class="door-main">
        <section class="camera-card">
            <div class="camera-stage" id="cameraStage">
                <video id="cameraVideo" autoplay muted playsinline></video>
                <canvas id="cameraCanvas"></canvas>
                <div class="start-overlay" id="startOverlay">
                    <i class="fas fa-camera-retro"></i>
                    <h1 id="startTitle">Deteksi Wajah Otomatis</h1>
                    <p id="startDescription">Arahkan kamera ke pintu. Sistem akan mengenali data wajah GTK dan siswa yang aktif serta sudah diverifikasi, kemudian memberikan sapaan suara.</p>
                    <button class="btn btn-success" id="startButton" type="button"><i class="fas fa-play"></i> Aktifkan Kamera &amp; Suara</button>
                    <small style="margin-top:12px;color:#94a3b8">Browser akan meminta izin kamera. Tidak ada presensi yang dicatat pada mode uji.</small>
                </div>
            </div>
            <div class="camera-toolbar">
                <div class="camera-status"><span class="status-dot" id="statusDot"></span><span id="statusText">Belum diaktifkan</span></div>
                <select class="camera-select" id="cameraSelect" aria-label="Pilih kamera"><option value="">Kamera otomatis</option></select>
            </div>
        </section>

        <aside class="side-card">
            <h2 class="side-title">Hasil pengenalan</h2>
            <p class="side-subtitle">Sapaan diberikan setelah wajah cocok secara konsisten.</p>
            <div class="recognition" id="recognitionCard">
                <div class="waiting-icon" id="waitingIcon"><i class="fas fa-user-clock"></i></div>
                <img id="personPhoto" class="is-hidden" src="" alt="Foto pengguna terdeteksi">
                <h2 id="personName">Menunggu wajah</h2>
                <p id="personMeta">Berdirilah pada area kamera</p>
                <span class="recognition-badge is-hidden" id="matchBadge">TERDETEKSI</span>
            </div>
            <div class="metrics">
                <div class="metric"><small>Database aktif</small><strong id="databaseCount">0 wajah</strong></div>
                <div class="metric"><small>Confidence terakhir</small><strong id="confidenceValue">-</strong></div>
                <div class="metric"><small>Wajah di frame</small><strong id="faceCount">0</strong></div>
                <div class="metric"><small>Sapaan sesi ini</small><strong id="greetingCount">0</strong></div>
            </div>
            <div class="notice"><i class="fas fa-flask mr-1"></i> Mode ini hanya untuk percobaan deteksi dan sapaan. Data presensi, foto tangkapan, dan histori tidak disimpan.</div>
        </aside>
    </main>
    <footer class="door-footer">Gunakan pencahayaan dari arah kamera dan posisikan kamera setinggi wajah untuk hasil terbaik.</footer>
</div>

<dialog class="settings-dialog" id="voiceSettingsModal" aria-labelledby="voiceSettingsTitle">
    <div class="settings-header">
        <div><h2 id="voiceSettingsTitle"><i class="fas fa-volume-up" style="color:#38bdf8"></i> Pengaturan Suara</h2><p>Berlaku khusus di browser dan perangkat kamera ini.</p></div>
        <button class="settings-close" id="settingsCloseButton" type="button" aria-label="Tutup"><i class="fas fa-times"></i></button>
    </div>
    <div class="settings-body">
        <div class="settings-grid">
            <div class="setting-field">
                <label for="voiceCharacter">Karakter suara</label>
                <select id="voiceCharacter"><option value="auto">Otomatis</option><option value="male">Pria</option><option value="female">Wanita</option></select>
                <small>Karakter mengatur pitch rekomendasi. Pilihan suara sebenarnya mengikuti voice yang tersedia di sistem operasi.</small>
            </div>
            <div class="setting-field">
                <label for="voiceSelect">Voice perangkat</label>
                <select id="voiceSelect"><option value="">Otomatis (Bahasa Indonesia)</option></select>
                <small>Voice Bahasa Indonesia ditampilkan paling atas bila tersedia.</small>
            </div>
            <div class="setting-field is-wide">
                <label for="voiceIntonation">Gaya intonasi</label>
                <select id="voiceIntonation">
                    <option value="natural">Natural</option>
                    <option value="friendly">Ramah</option>
                    <option value="formal">Formal</option>
                    <option value="enthusiastic">Semangat</option>
                    <option value="calm">Tenang</option>
                    <option value="custom">Kustom dari slider</option>
                </select>
                <small>Preset intonasi mengatur kombinasi pitch, tempo, dan penekanan tanda baca. Slider tetap dapat disesuaikan setelah memilih preset.</small>
            </div>
            <div class="setting-field is-wide">
                <label for="voiceTestName">Nama untuk tes suara</label>
                <input id="voiceTestName" type="text" maxlength="100" value="{{ auth()->user()?->name ?? 'Pengguna SIMANSA' }}">
            </div>
            <div class="setting-field">
                <label for="voicePitch">Tinggi/rendah suara <output id="pitchOutput">1.00</output></label>
                <input id="voicePitch" type="range" min="0.5" max="2" step="0.05" value="1">
                <small>Nilai rendah terdengar lebih berat, nilai tinggi terdengar lebih ringan.</small>
            </div>
            <div class="setting-field">
                <label for="voiceRate">Kecepatan bicara <output id="rateOutput">0.92</output></label>
                <input id="voiceRate" type="range" min="0.5" max="1.5" step="0.05" value="0.92">
            </div>
            <div class="setting-field">
                <label for="voiceVolume">Volume <output id="volumeOutput">100%</output></label>
                <input id="voiceVolume" type="range" min="0" max="1" step="0.05" value="1">
            </div>
            <div class="setting-field">
                <label for="greetingCooldown">Jeda sapaan orang yang sama <output id="cooldownOutput">20 detik</output></label>
                <input id="greetingCooldown" type="range" min="5" max="120" step="5" value="20">
            </div>
            <div class="setting-field is-wide">
                <label for="greetingMode">Pola ucapan</label>
                <select id="greetingMode">
                    <option value="time">Otomatis sesuai waktu (pagi/siang/sore/malam)</option>
                    <option value="welcome">Selamat datang, [nama]</option>
                    <option value="hello">Halo [nama], selamat datang</option>
                    <option value="custom">Teks kustom</option>
                </select>
            </div>
            <div class="setting-field is-wide is-hidden" id="customGreetingField">
                <label for="customGreeting">Teks ucapan kustom</label>
                <textarea id="customGreeting" maxlength="200" placeholder="Contoh: Assalamualaikum {nama}, selamat {waktu}"></textarea>
                <small>Gunakan <strong>{nama}</strong> untuk nama pengguna dan <strong>{waktu}</strong> untuk pagi, siang, sore, atau malam.</small>
            </div>
            <div class="setting-field is-wide">
                <label class="setting-check"><input id="includeAcademicTitle" type="checkbox" checked> Ucapkan gelar akademik dengan ejaan huruf, misalnya A.Md menjadi “a em de”.</label>
            </div>
            <div class="setting-field is-wide"><div class="setting-preview"><strong>Pratinjau:</strong> <span id="greetingPreview">-</span></div></div>
        </div>
    </div>
    <div class="settings-footer">
        <button class="btn" id="resetVoiceSettings" type="button"><i class="fas fa-undo"></i> Default</button>
        <div class="settings-footer__right">
            <button class="btn" id="testVoiceButton" type="button"><i class="fas fa-play"></i> Tes suara</button>
            <button class="btn" id="cancelVoiceSettings" type="button">Batal</button>
            <button class="btn btn-primary" id="saveVoiceSettings" type="button"><i class="fas fa-save"></i> Simpan</button>
        </div>
    </div>
</dialog>

@unless($isPublicMode ?? false)
<dialog class="settings-dialog" id="publicAccessModal" aria-labelledby="publicAccessTitle">
    <div class="settings-header">
        <div><h2 id="publicAccessTitle"><i class="fas fa-link" style="color:#34d399"></i> Akses Perangkat Tanpa Login</h2><p>Pasang tautan ini hanya pada PC kamera yang dipercaya.</p></div>
        <button class="settings-close" id="publicAccessClose" type="button" aria-label="Tutup"><i class="fas fa-times"></i></button>
    </div>
    <div class="settings-body">
        <label for="publicFaceDetectUrl" style="display:block;margin-bottom:7px;font-size:.72rem;font-weight:700">Tautan rahasia perangkat</label>
        <input class="public-url" id="publicFaceDetectUrl" type="text" value="{{ $publicFaceDetectUrl }}" readonly>
        <div class="public-warning"><i class="fas fa-shield-alt"></i> Siapa pun yang memiliki tautan ini dapat menjalankan pengenalan wajah tanpa login. Jangan bagikan melalui grup umum. Rotasi token akan langsung memutus semua perangkat yang memakai tautan lama.</div>
    </div>
    <div class="settings-footer">
        <form method="POST" action="{{ route('admin.absensi.face-detect.rotate-token') }}" id="rotatePublicTokenForm">@csrf<button class="btn btn-danger" type="button" id="rotatePublicTokenButton"><i class="fas fa-sync-alt"></i> Rotasi token</button></form>
        <div class="settings-footer__right"><button class="btn" id="openPublicFaceDetect" type="button"><i class="fas fa-external-link-alt"></i> Buka tab baru</button><button class="btn btn-success" id="copyPublicFaceDetect" type="button"><i class="fas fa-copy"></i> Salin tautan</button></div>
    </div>
</dialog>
@endunless

<script src="{{ asset('vendor/face-api/face-api.min.js') }}"></script>
<script>
(() => {
    const CONFIG = {
        threshold: @json($faceThreshold),
        interval: 260,
        confirmations: 3,
        greetingCooldown: 20000,
    };
    const VOICE_SETTINGS_KEY = 'simansa.face-detect.voice.v1';
    const DEFAULT_VOICE_SETTINGS = Object.freeze({
        character: 'auto', voiceUri: '', pitch: 1, rate: .92, volume: 1,
        intonation: 'natural', cooldown: 20, greetingMode: 'time', customGreeting: 'Selamat {waktu}, {nama}',
        includeAcademicTitle: true,
    });
    const INTONATION_PROFILES = Object.freeze({
        natural: { rate: .92, pitchOffset: 0 },
        friendly: { rate: .95, pitchOffset: .08 },
        formal: { rate: .84, pitchOffset: -.04 },
        enthusiastic: { rate: 1.05, pitchOffset: .14 },
        calm: { rate: .76, pitchOffset: -.08 },
    });
    const endpoints = @json($descriptorEndpoints);
    const video = document.getElementById('cameraVideo');
    const canvas = document.getElementById('cameraCanvas');
    const ctx = canvas.getContext('2d');
    const startButton = document.getElementById('startButton');
    const cameraSelect = document.getElementById('cameraSelect');
    const voiceSettingsModal = document.getElementById('voiceSettingsModal');
    let stream = null;
    let profiles = [];
    let running = false;
    let voiceEnabled = true;
    let greetingCount = 0;
    let availableVoices = [];
    let voiceSettings = loadVoiceSettings();
    let candidate = { id: null, streak: 0 };
    const greetedAt = new Map();
    CONFIG.greetingCooldown = voiceSettings.cooldown * 1000;

    function clamp(value, min, max, fallback) {
        const number = Number(value);
        return Number.isFinite(number) ? Math.min(max, Math.max(min, number)) : fallback;
    }

    function sanitizeVoiceSettings(value = {}) {
        const character = ['auto', 'male', 'female'].includes(value.character) ? value.character : DEFAULT_VOICE_SETTINGS.character;
        const greetingMode = ['time', 'welcome', 'hello', 'custom'].includes(value.greetingMode) ? value.greetingMode : DEFAULT_VOICE_SETTINGS.greetingMode;
        const intonation = ['natural', 'friendly', 'formal', 'enthusiastic', 'calm', 'custom'].includes(value.intonation) ? value.intonation : DEFAULT_VOICE_SETTINGS.intonation;
        return {
            character,
            greetingMode,
            intonation,
            voiceUri: String(value.voiceUri || ''),
            pitch: clamp(value.pitch, .5, 2, DEFAULT_VOICE_SETTINGS.pitch),
            rate: clamp(value.rate, .5, 1.5, DEFAULT_VOICE_SETTINGS.rate),
            volume: clamp(value.volume, 0, 1, DEFAULT_VOICE_SETTINGS.volume),
            cooldown: clamp(value.cooldown, 5, 120, DEFAULT_VOICE_SETTINGS.cooldown),
            customGreeting: String(value.customGreeting || DEFAULT_VOICE_SETTINGS.customGreeting).slice(0, 200),
            includeAcademicTitle: value.includeAcademicTitle !== false,
        };
    }

    function loadVoiceSettings() {
        try { return sanitizeVoiceSettings(JSON.parse(localStorage.getItem(VOICE_SETTINGS_KEY) || '{}')); }
        catch (error) { return sanitizeVoiceSettings(); }
    }

    function persistVoiceSettings(settings) {
        try { localStorage.setItem(VOICE_SETTINGS_KEY, JSON.stringify(settings)); } catch (error) { console.warn('Pengaturan suara tidak dapat disimpan.', error); }
    }

    function setStatus(text, color = '#f59e0b') {
        document.getElementById('statusText').textContent = text;
        document.getElementById('statusDot').style.background = color;
    }

    async function loadModelsAndProfiles() {
        const base = @json(asset('vendor/face-api/models'));
        setStatus('Memuat model deteksi…', '#38bdf8');
        await Promise.all([
            faceapi.nets.tinyFaceDetector.loadFromUri(base),
            faceapi.nets.faceLandmark68TinyNet.loadFromUri(base),
            faceapi.nets.faceRecognitionNet.loadFromUri(base),
        ]);
        setStatus('Memuat database wajah…', '#38bdf8');
        const responses = await Promise.all(endpoints.map(url => fetch(url, { headers: { Accept: 'application/json' } })));
        if (responses.some(response => !response.ok)) throw new Error('Database wajah tidak dapat dimuat.');
        const payloads = await Promise.all(responses.map(response => response.json()));
        profiles = payloads.flatMap(payload => payload.data || []).map(person => ({
            ...person,
            descriptors: (person.descriptors || []).map(value => new Float32Array(value)),
        })).filter(person => person.descriptors.length);
        document.getElementById('databaseCount').textContent = `${profiles.length} wajah`;
    }

    async function startCamera(deviceId = '') {
        if (stream) stream.getTracks().forEach(track => track.stop());
        stream = await navigator.mediaDevices.getUserMedia({
            video: deviceId ? { deviceId: { exact: deviceId }, width: { ideal: 1280 }, height: { ideal: 720 } } : { facingMode: 'environment', width: { ideal: 1280 }, height: { ideal: 720 } },
            audio: false,
        });
        video.srcObject = stream;
        await video.play();
        await populateCameras();
        resizeCanvas();
    }

    async function populateCameras() {
        const devices = (await navigator.mediaDevices.enumerateDevices()).filter(device => device.kind === 'videoinput');
        const selected = stream?.getVideoTracks()[0]?.getSettings()?.deviceId || '';
        cameraSelect.innerHTML = '';
        devices.forEach((device, index) => {
            const option = new Option(device.label || `Kamera ${index + 1}`, device.deviceId, false, device.deviceId === selected);
            cameraSelect.add(option);
        });
    }

    function resizeCanvas() {
        canvas.width = video.videoWidth || 1280;
        canvas.height = video.videoHeight || 720;
    }

    function findBestMatch(descriptor) {
        let best = null;
        let distance = Infinity;
        profiles.forEach(person => person.descriptors.forEach(reference => {
            const current = faceapi.euclideanDistance(descriptor, reference);
            if (current < distance) { distance = current; best = person; }
        }));
        return best && distance < CONFIG.threshold ? { person: best, distance } : null;
    }

    const INDONESIAN_LETTER_NAMES = Object.freeze({
        A: 'a', B: 'be', C: 'ce', D: 'de', E: 'e', F: 'ef', G: 'ge', H: 'ha', I: 'i',
        J: 'je', K: 'ka', L: 'el', M: 'em', N: 'en', O: 'o', P: 'pe', Q: 'ki', R: 'er',
        S: 'es', T: 'te', U: 'u', V: 've', W: 'we', X: 'eks', Y: 'ye', Z: 'zet',
    });

    function normalizeNameForSpeech(name) {
        return String(name || '').replace(/\b(?:[A-Za-z]{1,3}\.)+[A-Za-z]{1,3}\.?/g, title =>
            Array.from(title.replaceAll('.', ''))
                .map(letter => INDONESIAN_LETTER_NAMES[letter.toUpperCase()] || letter)
                .join(' ')
        );
    }

    function timePeriod(date = new Date()) {
        const hour = date.getHours();
        if (hour >= 4 && hour < 11) return 'pagi';
        if (hour >= 11 && hour < 15) return 'siang';
        if (hour >= 15 && hour < 18) return 'sore';
        return 'malam';
    }

    function buildGreeting(name, settings = voiceSettings, date = new Date()) {
        const displayName = settings.includeAcademicTitle ? String(name || '') : String(name || '').split(',')[0];
        const spokenName = normalizeNameForSpeech(displayName.trim());
        const period = timePeriod(date);
        let template = `Selamat ${period}, {nama}`;
        if (settings.greetingMode === 'welcome') template = 'Selamat datang, {nama}';
        if (settings.greetingMode === 'hello') template = 'Halo {nama}, selamat datang';
        if (settings.greetingMode === 'custom') template = settings.customGreeting.trim() || DEFAULT_VOICE_SETTINGS.customGreeting;
        const greeting = template.replaceAll('{nama}', spokenName).replaceAll('{waktu}', period).trim();
        if (settings.intonation === 'enthusiastic' || settings.intonation === 'friendly') return greeting.replace(/[.!?]+$/, '') + '!';
        if (settings.intonation === 'formal' || settings.intonation === 'calm') return greeting.replace(/[.!?]+$/, '') + '.';
        return greeting;
    }

    function resolveVoice(settings = voiceSettings) {
        const selected = availableVoices.find(voice => voice.voiceURI === settings.voiceUri);
        if (selected) return selected;
        const indonesian = availableVoices.filter(voice => /^id(?:-|_)/i.test(voice.lang));
        const pool = indonesian.length ? indonesian : availableVoices;
        const patterns = settings.character === 'male'
            ? /male|pria|man|david|mark|guy|andika|ardi/i
            : /female|wanita|woman|zira|susan|sari|damayanti|gadis|aria/i;
        return (settings.character === 'auto' ? null : pool.find(voice => patterns.test(voice.name))) || pool.find(voice => voice.default) || pool[0] || null;
    }

    function speakText(text, settings = voiceSettings, cancelCurrent = false) {
        if (!('speechSynthesis' in window)) return;
        if (cancelCurrent) window.speechSynthesis.cancel();
        const utterance = new SpeechSynthesisUtterance(text);
        const selectedVoice = resolveVoice(settings);
        if (selectedVoice) utterance.voice = selectedVoice;
        utterance.lang = selectedVoice?.lang || 'id-ID';
        utterance.rate = settings.rate;
        utterance.pitch = settings.pitch;
        utterance.volume = settings.volume;
        window.speechSynthesis.speak(utterance);
    }

    function speakWelcome(person) {
        if (!voiceEnabled || !('speechSynthesis' in window)) return;
        speakText(buildGreeting(person.name));
    }

    function showRecognized(person, distance) {
        const now = Date.now();
        if (now - (greetedAt.get(person.user_id) || 0) < CONFIG.greetingCooldown) return;
        greetedAt.set(person.user_id, now);
        greetingCount++;
        document.getElementById('greetingCount').textContent = greetingCount;
        document.getElementById('waitingIcon').classList.add('is-hidden');
        const photo = document.getElementById('personPhoto');
        if (person.foto) { photo.src = person.foto; photo.classList.remove('is-hidden'); } else { photo.classList.add('is-hidden'); }
        document.getElementById('personName').textContent = person.name;
        document.getElementById('personMeta').textContent = `${person.user_type.toUpperCase()}${person.identifier ? ' · ' + person.identifier : ''}`;
        document.getElementById('matchBadge').classList.remove('is-hidden');
        document.getElementById('confidenceValue').textContent = `${Math.round((1 - distance) * 100)}%`;
        speakWelcome(person);
    }

    function drawResult(result, label, matched) {
        const box = result.detection.box;
        const mirroredX = canvas.width - box.x - box.width;
        ctx.strokeStyle = matched ? '#34d399' : '#fbbf24';
        ctx.lineWidth = 3;
        ctx.strokeRect(mirroredX, box.y, box.width, box.height);
        ctx.font = 'bold 15px Segoe UI';
        const width = Math.max(ctx.measureText(label).width + 16, box.width);
        ctx.fillStyle = matched ? 'rgba(5,150,105,.9)' : 'rgba(180,83,9,.9)';
        ctx.fillRect(mirroredX, Math.max(0, box.y - 28), width, 28);
        ctx.fillStyle = '#fff';
        ctx.fillText(label, mirroredX + 8, Math.max(19, box.y - 9));
    }

    async function detectLoop() {
        if (!running) return;
        if (!video.videoWidth) { setTimeout(detectLoop, 300); return; }
        resizeCanvas();
        try {
            const detections = await faceapi.detectAllFaces(video, new faceapi.TinyFaceDetectorOptions({ inputSize: 416, scoreThreshold: .55 })).withFaceLandmarks(true).withFaceDescriptors();
            ctx.clearRect(0, 0, canvas.width, canvas.height);
            document.getElementById('faceCount').textContent = detections.length;
            let strongest = null;
            detections.forEach(detection => {
                const match = findBestMatch(detection.descriptor);
                if (match && (!strongest || match.distance < strongest.distance)) strongest = match;
                drawResult(detection, match ? match.person.name : 'Wajah belum dikenal', Boolean(match));
            });
            if (strongest) {
                if (candidate.id === strongest.person.user_id) candidate.streak++; else candidate = { id: strongest.person.user_id, streak: 1 };
                setStatus(`Mengenali ${strongest.person.name}…`, '#34d399');
                if (candidate.streak >= CONFIG.confirmations) showRecognized(strongest.person, strongest.distance);
            } else {
                candidate = { id: null, streak: 0 };
                setStatus(detections.length ? 'Wajah belum cocok' : 'Menunggu wajah…', detections.length ? '#f59e0b' : '#38bdf8');
            }
        } catch (error) {
            console.error(error);
            setStatus('Deteksi mengalami kendala', '#fb7185');
        }
        setTimeout(detectLoop, CONFIG.interval);
    }

    function populateVoiceOptions() {
        if (!('speechSynthesis' in window)) return;
        availableVoices = window.speechSynthesis.getVoices().slice().sort((left, right) => {
            const leftId = /^id(?:-|_)/i.test(left.lang) ? 0 : 1;
            const rightId = /^id(?:-|_)/i.test(right.lang) ? 0 : 1;
            return leftId - rightId || left.lang.localeCompare(right.lang) || left.name.localeCompare(right.name);
        });
        const voiceSelect = document.getElementById('voiceSelect');
        const current = voiceSelect.value || voiceSettings.voiceUri;
        voiceSelect.innerHTML = '<option value="">Otomatis (utamakan Bahasa Indonesia)</option>';
        availableVoices.forEach(voice => {
            voiceSelect.add(new Option(`${voice.name} · ${voice.lang}${voice.default ? ' · default' : ''}`, voice.voiceURI));
        });
        voiceSelect.value = availableVoices.some(voice => voice.voiceURI === current) ? current : '';
    }

    function readVoiceSettingsForm() {
        return sanitizeVoiceSettings({
            character: document.getElementById('voiceCharacter').value,
            voiceUri: document.getElementById('voiceSelect').value,
            intonation: document.getElementById('voiceIntonation').value,
            pitch: document.getElementById('voicePitch').value,
            rate: document.getElementById('voiceRate').value,
            volume: document.getElementById('voiceVolume').value,
            cooldown: document.getElementById('greetingCooldown').value,
            greetingMode: document.getElementById('greetingMode').value,
            customGreeting: document.getElementById('customGreeting').value,
            includeAcademicTitle: document.getElementById('includeAcademicTitle').checked,
        });
    }

    function updateVoiceSettingsPreview() {
        const draft = readVoiceSettingsForm();
        document.getElementById('pitchOutput').textContent = draft.pitch.toFixed(2);
        document.getElementById('rateOutput').textContent = draft.rate.toFixed(2);
        document.getElementById('volumeOutput').textContent = `${Math.round(draft.volume * 100)}%`;
        document.getElementById('cooldownOutput').textContent = `${draft.cooldown} detik`;
        document.getElementById('customGreetingField').classList.toggle('is-hidden', draft.greetingMode !== 'custom');
        const testName = document.getElementById('voiceTestName').value.trim() || 'Candra Huda Buana, A.Md';
        document.getElementById('greetingPreview').textContent = buildGreeting(testName, draft);
    }

    function fillVoiceSettingsForm(settings) {
        document.getElementById('voiceCharacter').value = settings.character;
        document.getElementById('voiceSelect').value = settings.voiceUri;
        document.getElementById('voiceIntonation').value = settings.intonation;
        document.getElementById('voicePitch').value = settings.pitch;
        document.getElementById('voiceRate').value = settings.rate;
        document.getElementById('voiceVolume').value = settings.volume;
        document.getElementById('greetingCooldown').value = settings.cooldown;
        document.getElementById('greetingMode').value = settings.greetingMode;
        document.getElementById('customGreeting').value = settings.customGreeting;
        document.getElementById('includeAcademicTitle').checked = settings.includeAcademicTitle;
        updateVoiceSettingsPreview();
    }

    function openVoiceSettings() {
        populateVoiceOptions();
        fillVoiceSettingsForm(voiceSettings);
        if (typeof voiceSettingsModal.showModal === 'function') voiceSettingsModal.showModal(); else voiceSettingsModal.setAttribute('open', '');
    }

    document.getElementById('settingsButton').addEventListener('click', openVoiceSettings);
    document.getElementById('settingsCloseButton').addEventListener('click', () => voiceSettingsModal.close());
    document.getElementById('cancelVoiceSettings').addEventListener('click', () => voiceSettingsModal.close());
    document.getElementById('resetVoiceSettings').addEventListener('click', () => fillVoiceSettingsForm(sanitizeVoiceSettings()));
    document.getElementById('saveVoiceSettings').addEventListener('click', () => {
        voiceSettings = readVoiceSettingsForm();
        CONFIG.greetingCooldown = voiceSettings.cooldown * 1000;
        persistVoiceSettings(voiceSettings);
        voiceSettingsModal.close();
    });
    document.getElementById('testVoiceButton').addEventListener('click', () => {
        const draft = readVoiceSettingsForm();
        const testName = document.getElementById('voiceTestName').value.trim() || 'Candra Huda Buana, A.Md';
        speakText(buildGreeting(testName, draft), draft, true);
    });
    function applyIntonationPreset() {
        const character = document.getElementById('voiceCharacter').value;
        const intonation = document.getElementById('voiceIntonation').value;
        const profile = INTONATION_PROFILES[intonation];
        if (!profile) { updateVoiceSettingsPreview(); return; }
        const basePitch = character === 'male' ? .82 : (character === 'female' ? 1.15 : 1);
        document.getElementById('voicePitch').value = clamp(basePitch + profile.pitchOffset, .5, 2, 1);
        document.getElementById('voiceRate').value = profile.rate;
        updateVoiceSettingsPreview();
    }
    document.getElementById('voiceCharacter').addEventListener('change', applyIntonationPreset);
    document.getElementById('voiceIntonation').addEventListener('change', applyIntonationPreset);
    ['voicePitch', 'voiceRate'].forEach(id => document.getElementById(id).addEventListener('input', () => {
        document.getElementById('voiceIntonation').value = 'custom';
        updateVoiceSettingsPreview();
    }));
    ['voiceSelect', 'voiceVolume', 'greetingCooldown', 'greetingMode', 'customGreeting', 'includeAcademicTitle', 'voiceTestName']
        .forEach(id => document.getElementById(id).addEventListener('input', updateVoiceSettingsPreview));
    if ('speechSynthesis' in window) {
        populateVoiceOptions();
        window.speechSynthesis.addEventListener?.('voiceschanged', populateVoiceOptions);
    }

    const publicAccessButton = document.getElementById('publicAccessButton');
    if (publicAccessButton) {
        const publicAccessModal = document.getElementById('publicAccessModal');
        const publicUrlInput = document.getElementById('publicFaceDetectUrl');
        publicAccessButton.addEventListener('click', () => publicAccessModal.showModal());
        document.getElementById('publicAccessClose').addEventListener('click', () => publicAccessModal.close());
        document.getElementById('openPublicFaceDetect').addEventListener('click', () => window.open(publicUrlInput.value, '_blank', 'noopener,noreferrer'));
        document.getElementById('copyPublicFaceDetect').addEventListener('click', async event => {
            await navigator.clipboard.writeText(publicUrlInput.value);
            event.currentTarget.innerHTML = '<i class="fas fa-check"></i> Tersalin';
            setTimeout(() => { event.currentTarget.innerHTML = '<i class="fas fa-copy"></i> Salin tautan'; }, 1600);
        });
        document.getElementById('rotatePublicTokenButton').addEventListener('click', () => {
            if (confirm('Rotasi token sekarang? Semua tautan lama langsung tidak berlaku.')) document.getElementById('rotatePublicTokenForm').submit();
        });
    }

    startButton.addEventListener('click', async () => {
        startButton.disabled = true;
        try {
            await loadModelsAndProfiles();
            if (!profiles.length) throw new Error('Belum ada data wajah aktif yang telah diverifikasi.');
            await startCamera();
            document.getElementById('startOverlay').classList.add('is-hidden');
            running = true;
            setStatus('Menunggu wajah…', '#38bdf8');
            detectLoop();
        } catch (error) {
            setStatus(error.message, '#fb7185');
            document.getElementById('startTitle').textContent = 'Kamera belum dapat diaktifkan';
            document.getElementById('startDescription').textContent = error.message;
            startButton.innerHTML = '<i class="fas fa-redo"></i> Coba Lagi';
            startButton.disabled = false;
        }
    });

    cameraSelect.addEventListener('change', async () => {
        try { await startCamera(cameraSelect.value); } catch (error) { setStatus('Gagal mengganti kamera', '#fb7185'); }
    });
    document.getElementById('voiceButton').addEventListener('click', event => {
        voiceEnabled = !voiceEnabled;
        event.currentTarget.innerHTML = `<i class="fas fa-volume-${voiceEnabled ? 'up' : 'mute'}"></i> Suara ${voiceEnabled ? 'aktif' : 'mati'}`;
        if (!voiceEnabled) window.speechSynthesis?.cancel();
    });
    document.getElementById('fullscreenButton').addEventListener('click', () => {
        if (!document.fullscreenElement) document.documentElement.requestFullscreen().catch(() => {}); else document.exitFullscreen();
    });
    window.addEventListener('beforeunload', () => stream?.getTracks().forEach(track => track.stop()));
})();
</script>
</body>
</html>
