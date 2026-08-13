<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Face Detect Percobaan - SIMANSA</title>
    <link rel="stylesheet" href="{{ asset('vendor/fontawesome-free/css/all.min.css') }}">
    <style>
        :root{color-scheme:dark;--bg:#071125;--panel:#101d38;--line:#24375b;--primary:#38bdf8;--success:#34d399;--muted:#94a3b8}*{box-sizing:border-box}body{min-height:100vh;margin:0;background:radial-gradient(circle at 20% 0,#172554 0,transparent 34%),var(--bg);color:#f8fafc;font-family:Inter,Segoe UI,Arial,sans-serif}.door-shell{display:grid;min-height:100vh;grid-template-rows:auto 1fr auto}.door-header{display:flex;align-items:center;justify-content:space-between;gap:16px;padding:14px 20px;border-bottom:1px solid var(--line);background:rgba(7,17,37,.9)}.brand{display:flex;align-items:center;gap:12px}.brand-mark{display:grid;width:42px;height:42px;place-items:center;border-radius:12px;background:linear-gradient(135deg,#2563eb,#06b6d4);font-size:1.1rem}.brand strong,.brand small{display:block}.brand small{margin-top:2px;color:var(--muted);font-size:.72rem}.header-actions{display:flex;flex-wrap:wrap;gap:8px}.btn{padding:9px 12px;border:1px solid var(--line);border-radius:9px;background:#17233d;color:#fff;cursor:pointer;font-weight:700}.btn:hover{border-color:#60a5fa}.btn-primary{border-color:#0284c7;background:#0284c7}.btn-success{border-color:#059669;background:#059669}.btn:disabled{cursor:not-allowed;opacity:.55}.door-main{display:grid;grid-template-columns:minmax(0,1fr) 330px;gap:14px;padding:14px}.camera-card,.side-card{overflow:hidden;border:1px solid var(--line);border-radius:16px;background:rgba(15,29,56,.92);box-shadow:0 18px 42px rgba(0,0,0,.2)}.camera-stage{position:relative;min-height:520px;background:#020617}.camera-stage video,.camera-stage canvas{position:absolute;width:100%;height:100%;inset:0;object-fit:cover}.camera-stage video{transform:scaleX(-1)}.camera-stage canvas{z-index:2;pointer-events:none}.start-overlay{position:absolute;z-index:4;display:flex;align-items:center;justify-content:center;flex-direction:column;inset:0;padding:24px;background:radial-gradient(circle,rgba(30,64,175,.42),rgba(2,6,23,.96));text-align:center}.start-overlay i{margin-bottom:18px;color:var(--primary);font-size:3rem}.start-overlay h1{margin:0 0 8px;font-size:1.55rem}.start-overlay p{max-width:520px;margin:0 0 20px;color:#cbd5e1;line-height:1.55}.camera-toolbar{display:flex;align-items:center;justify-content:space-between;gap:12px;padding:11px 14px}.camera-status{display:flex;align-items:center;gap:8px;font-size:.82rem;font-weight:700}.status-dot{width:9px;height:9px;border-radius:50%;background:#f59e0b;box-shadow:0 0 12px currentColor}.camera-select{max-width:260px;padding:7px 9px;border:1px solid var(--line);border-radius:8px;background:#0b1730;color:#fff}.side-card{padding:17px}.side-title{margin:0 0 4px;font-size:.72rem;letter-spacing:.08em;text-transform:uppercase;color:#7dd3fc}.side-subtitle{margin:0 0 16px;color:var(--muted);font-size:.76rem}.recognition{display:flex;min-height:270px;align-items:center;justify-content:center;flex-direction:column;padding:18px;border:1px dashed #334a73;border-radius:14px;text-align:center}.recognition img{width:112px;height:132px;object-fit:cover;border:3px solid #fff;border-radius:16px;box-shadow:0 0 0 4px rgba(52,211,153,.25)}.recognition h2{margin:13px 0 4px;font-size:1.25rem}.recognition p{margin:0;color:var(--muted);font-size:.8rem}.recognition-badge{margin-top:10px;padding:5px 9px;border-radius:999px;background:rgba(52,211,153,.15);color:#6ee7b7;font-size:.68rem;font-weight:800}.waiting-icon{display:grid;width:76px;height:76px;place-items:center;border-radius:50%;background:rgba(56,189,248,.1);color:var(--primary);font-size:1.8rem}.metrics{display:grid;grid-template-columns:1fr 1fr;gap:9px;margin-top:12px}.metric{padding:11px;border-radius:10px;background:#0b1730}.metric small,.metric strong{display:block}.metric small{color:var(--muted);font-size:.66rem}.metric strong{margin-top:4px;font-size:.9rem}.notice{margin-top:12px;padding:10px;border-left:3px solid #f59e0b;border-radius:7px;background:rgba(245,158,11,.09);color:#fde68a;font-size:.7rem;line-height:1.45}.door-footer{padding:8px 16px;color:#64748b;font-size:.66rem;text-align:center}.is-hidden{display:none!important}@media(max-width:900px){.door-main{grid-template-columns:1fr}.camera-stage{min-height:430px}.side-card{display:grid;grid-template-columns:1fr 1fr;gap:14px}.side-card>.side-title,.side-card>.side-subtitle{grid-column:1/-1}.recognition{min-height:220px}}@media(max-width:600px){.door-header{align-items:flex-start;flex-direction:column}.door-main{padding:8px}.camera-stage{min-height:390px}.side-card{display:block}.camera-toolbar{align-items:stretch;flex-direction:column}.camera-select{max-width:none;width:100%}}
    </style>
</head>
<body>
<div class="door-shell">
    <header class="door-header">
        <div class="brand"><div class="brand-mark"><i class="fas fa-eye"></i></div><div><strong>Face Detect</strong><small>Mode percobaan pintu · SIMANSA</small></div></div>
        <div class="header-actions">
            <button class="btn" id="voiceButton" type="button"><i class="fas fa-volume-up"></i> Suara aktif</button>
            <button class="btn" id="fullscreenButton" type="button"><i class="fas fa-expand"></i> Layar penuh</button>
            <a class="btn" href="{{ route('admin.absensi.index') }}" style="text-decoration:none"><i class="fas fa-arrow-left"></i> Kembali</a>
        </div>
    </header>

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

<script src="{{ asset('vendor/face-api/face-api.min.js') }}"></script>
<script>
(() => {
    const CONFIG = {
        threshold: @json($faceThreshold),
        interval: 260,
        confirmations: 3,
        greetingCooldown: 20000,
    };
    const endpoints = [
        @json(route('admin.absensi.face-descriptors', ['type' => 'gtk', 'verified_only' => 1])),
        @json(route('admin.absensi.face-descriptors', ['type' => 'siswa', 'verified_only' => 1])),
    ];
    const video = document.getElementById('cameraVideo');
    const canvas = document.getElementById('cameraCanvas');
    const ctx = canvas.getContext('2d');
    const startButton = document.getElementById('startButton');
    const cameraSelect = document.getElementById('cameraSelect');
    let stream = null;
    let profiles = [];
    let running = false;
    let voiceEnabled = true;
    let greetingCount = 0;
    let candidate = { id: null, streak: 0 };
    const greetedAt = new Map();

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

    function speakWelcome(person) {
        if (!voiceEnabled || !('speechSynthesis' in window)) return;
        const utterance = new SpeechSynthesisUtterance(`Selamat datang, ${person.name}`);
        utterance.lang = 'id-ID';
        utterance.rate = .92;
        utterance.pitch = 1;
        window.speechSynthesis.speak(utterance);
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
