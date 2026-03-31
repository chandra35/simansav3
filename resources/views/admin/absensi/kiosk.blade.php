<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Absensi Wajah - SIMANSA</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }
        body {
            background: #0a0a2e;
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            color: #fff;
            overflow: hidden;
            height: 100vh;
        }
        /* HEADER */
        .kiosk-header {
            background: linear-gradient(135deg, #1a1a4e 0%, #0d0d3a 100%);
            padding: 10px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 2px solid #2a2a6e;
            height: 60px;
        }
        .kiosk-header h1 { font-size: 1.3rem; font-weight: 700; }
        .kiosk-header .clock {
            font-size: 2rem;
            font-weight: 700;
            font-variant-numeric: tabular-nums;
            color: #00e5ff;
        }
        .kiosk-header .date { font-size: 0.9rem; color: #aaa; }

        /* MAIN LAYOUT */
        .kiosk-body {
            display: grid;
            grid-template-columns: 1fr 380px;
            height: calc(100vh - 60px);
            gap: 0;
        }

        /* CAMERA SECTION */
        .camera-section {
            position: relative;
            background: #000;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }
        #videoElement {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transform: scaleX(-1); /* mirror */
        }
        #overlayCanvas {
            position: absolute;
            top: 0; left: 0;
            width: 100%;
            height: 100%;
            transform: scaleX(-1);
        }

        /* Face tracking overlay */
        .face-guide {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 280px;
            height: 350px;
            border: 3px dashed rgba(0, 229, 255, 0.4);
            border-radius: 50%;
            pointer-events: none;
            transition: border-color 0.3s;
        }
        .face-guide.detected {
            border-color: #00e676;
            box-shadow: 0 0 30px rgba(0, 230, 118, 0.3);
        }
        .face-guide.matching {
            border-color: #ffab00;
            animation: pulse-border 1s infinite;
        }

        /* Status indicator on camera */
        .camera-status {
            position: absolute;
            bottom: 20px;
            left: 50%;
            transform: translateX(-50%);
            padding: 10px 30px;
            border-radius: 30px;
            font-size: 1rem;
            font-weight: 600;
            backdrop-filter: blur(10px);
            z-index: 10;
        }
        .camera-status.idle { background: rgba(255,255,255,0.15); }
        .camera-status.detecting { background: rgba(0,229,255,0.3); color: #00e5ff; }
        .camera-status.matched { background: rgba(0,230,118,0.3); color: #00e676; }
        .camera-status.error { background: rgba(255,82,82,0.3); color: #ff5252; }

        /* Liveness instruction overlay */
        .liveness-instruction {
            position: absolute;
            top: 15%;
            left: 50%;
            transform: translateX(-50%);
            font-size: 1.8rem;
            font-weight: 700;
            color: #00e5ff;
            text-shadow: 0 2px 10px rgba(0,0,0,0.8);
            text-align: center;
            z-index: 10;
            display: none;
        }
        .liveness-instruction.active { display: block; }

        /* SIDEBAR */
        .sidebar {
            background: linear-gradient(180deg, #111140 0%, #0a0a2e 100%);
            border-left: 2px solid #2a2a6e;
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }

        /* Tab switching: masuk/pulang */
        .tab-switch {
            display: flex;
            padding: 10px;
            gap: 5px;
        }
        .tab-switch button {
            flex: 1;
            padding: 10px;
            border: 2px solid #2a2a6e;
            background: transparent;
            color: #888;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
        }
        .tab-switch button.active {
            background: #00e5ff;
            color: #000;
            border-color: #00e5ff;
        }

        /* Result card - shown after successful match */
        .result-card {
            margin: 10px;
            padding: 20px;
            background: rgba(255,255,255,0.05);
            border-radius: 12px;
            border: 1px solid #2a2a6e;
            text-align: center;
            display: none;
        }
        .result-card.show { display: block; animation: slideIn 0.3s ease; }
        .result-card .avatar {
            width: 80px; height: 80px;
            border-radius: 50%;
            margin: 0 auto 10px;
            border: 3px solid #00e676;
        }
        .result-card .name { font-size: 1.2rem; font-weight: 700; }
        .result-card .nip { color: #aaa; font-size: 0.9rem; }
        .result-card .status-badge {
            display: inline-block;
            padding: 5px 20px;
            border-radius: 20px;
            font-weight: 600;
            margin-top: 10px;
        }
        .status-hadir { background: #00e676; color: #000; }
        .status-terlambat { background: #ffab00; color: #000; }

        /* Attendance list */
        .attendance-list {
            flex: 1;
            overflow-y: auto;
            padding: 0 10px 10px;
        }
        .attendance-list h6 {
            color: #aaa;
            font-size: 0.8rem;
            text-transform: uppercase;
            padding: 10px 0 5px;
            border-bottom: 1px solid #2a2a6e;
        }
        .att-item {
            display: flex;
            align-items: center;
            padding: 8px;
            border-radius: 8px;
            margin: 4px 0;
            background: rgba(255,255,255,0.03);
            gap: 10px;
            font-size: 0.85rem;
        }
        .att-item .time {
            color: #00e5ff;
            font-weight: 600;
            font-variant-numeric: tabular-nums;
            min-width: 55px;
        }
        .att-item .name { flex: 1; }
        .att-item .badge-s {
            padding: 2px 10px;
            border-radius: 12px;
            font-size: 0.7rem;
            font-weight: 600;
        }

        /* Stats bar */
        .stats-bar {
            padding: 10px;
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 5px;
        }
        .stat-box {
            text-align: center;
            padding: 8px;
            background: rgba(255,255,255,0.05);
            border-radius: 8px;
        }
        .stat-box .number { font-size: 1.5rem; font-weight: 700; }
        .stat-box .label { font-size: 0.7rem; color: #aaa; }

        /* Settings button */
        .settings-bar {
            padding: 10px;
            border-top: 1px solid #2a2a6e;
            display: flex;
            gap: 5px;
        }
        .settings-bar button {
            flex: 1;
            padding: 8px;
            border: 1px solid #2a2a6e;
            background: transparent;
            color: #aaa;
            border-radius: 6px;
            cursor: pointer;
            font-size: 0.8rem;
        }
        .settings-bar button:hover { background: rgba(255,255,255,0.1); }

        /* Loading & error states */
        .loading-overlay {
            position: absolute;
            inset: 0;
            background: rgba(0,0,0,0.8);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            z-index: 100;
        }
        .loading-overlay .spinner {
            width: 50px; height: 50px;
            border: 4px solid #2a2a6e;
            border-top-color: #00e5ff;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        /* Manual fallback modal */
        .manual-modal {
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.9);
            z-index: 1000;
            display: none;
            align-items: center;
            justify-content: center;
        }
        .manual-modal.show { display: flex; }
        .manual-modal .modal-content {
            background: #1a1a4e;
            border-radius: 12px;
            padding: 30px;
            width: 500px;
            max-width: 90%;
        }

        /* Animations */
        @keyframes spin { to { transform: rotate(360deg); } }
        @keyframes pulse-border {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }
        @keyframes slideIn {
            from { transform: translateY(-20px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }

        /* Scrollbar */
        ::-webkit-scrollbar { width: 4px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #2a2a6e; border-radius: 2px; }

        /* Fullscreen button */
        .btn-fullscreen {
            background: none;
            border: 1px solid #2a2a6e;
            color: #aaa;
            padding: 5px 12px;
            border-radius: 6px;
            cursor: pointer;
        }
        .btn-fullscreen:hover { background: rgba(255,255,255,0.1); }

        /* Face landmark points */
        .landmark-dot {
            fill: #00e5ff;
            opacity: 0.7;
        }
    </style>
</head>
<body>
    <!-- HEADER -->
    <div class="kiosk-header">
        <div>
            <h1><i class="fas fa-fingerprint"></i> SIMANSA - Absensi Wajah {{ $userType === 'siswa' ? 'Siswa' : 'GTK' }}</h1>
            <div class="date" id="currentDate"></div>
        </div>
        <div style="display:flex; align-items:center; gap:15px;">
            <div>
                <select id="userTypeSelect" class="form-select form-select-sm" style="background:#1a1a4e; color:#fff; border-color:#2a2a6e; width:160px;">
                    <option value="gtk" {{ $userType === 'gtk' ? 'selected' : '' }}>Mode GTK</option>
                    <option value="siswa" {{ $userType === 'siswa' ? 'selected' : '' }}>Mode Siswa</option>
                </select>
            </div>
            <div>
                <select id="locationSelect" class="form-select form-select-sm" style="background:#1a1a4e; color:#fff; border-color:#2a2a6e; width:200px;">
                    <option value="">Pilih Lokasi</option>
                    @foreach($locations as $loc)
                        <option value="{{ $loc->id }}" {{ ($location && $location->id === $loc->id) ? 'selected' : '' }}>
                            {{ $loc->nama }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="clock" id="currentTime">00:00:00</div>
            <button class="btn-fullscreen" onclick="toggleFullscreen()" title="Fullscreen">
                <i class="fas fa-expand"></i>
            </button>
            <a href="{{ route('admin.absensi.index') }}" class="btn-fullscreen" title="Kembali">
                <i class="fas fa-sign-out-alt"></i>
            </a>
        </div>
    </div>

    <!-- MAIN -->
    <div class="kiosk-body">
        <!-- CAMERA -->
        <div class="camera-section">
            <div class="loading-overlay" id="loadingOverlay">
                <div class="spinner"></div>
                <p class="mt-3" id="loadingText">Memuat model face detection...</p>
            </div>

            <video id="videoElement" autoplay playsinline></video>
            <canvas id="overlayCanvas"></canvas>

            <div class="face-guide" id="faceGuide"></div>

            <div class="liveness-instruction" id="livenessInstruction">
                <i class="fas fa-arrows-alt-h"></i><br>
                <span id="livenessText">Mendeteksi wajah...</span>
            </div>

            <div class="camera-status idle" id="cameraStatus">
                <i class="fas fa-video"></i> Menunggu wajah...
            </div>

            <!-- Camera error fallback -->
            <div id="cameraError" style="display:none; text-align:center; padding:40px;">
                <i class="fas fa-video-slash fa-3x text-danger mb-3"></i>
                <h4>Kamera Tidak Tersedia</h4>
                <p class="text-muted">Pastikan kamera terhubung dan browser memiliki izin akses.</p>
                <button class="btn btn-outline-info mt-3" onclick="initCamera()">
                    <i class="fas fa-redo"></i> Coba Lagi
                </button>
                <button class="btn btn-outline-warning mt-3" onclick="openManualModal()">
                    <i class="fas fa-keyboard"></i> Input Manual
                </button>
            </div>
        </div>

        <!-- SIDEBAR -->
        <div class="sidebar">
            <!-- Tab Switch -->
            <div class="tab-switch">
                <button id="tabMasuk" class="active" onclick="switchTab('masuk')">
                    <i class="fas fa-sign-in-alt"></i> MASUK
                </button>
                <button id="tabPulang" onclick="switchTab('pulang')">
                    <i class="fas fa-sign-out-alt"></i> PULANG
                </button>
            </div>

            <!-- Stats -->
            <div class="stats-bar">
                <div class="stat-box">
                    <div class="number" style="color:#00e676" id="statHadir">0</div>
                    <div class="label">Hadir</div>
                </div>
                <div class="stat-box">
                    <div class="number" style="color:#ffab00" id="statTerlambat">0</div>
                    <div class="label">Terlambat</div>
                </div>
                <div class="stat-box">
                    <div class="number" style="color:#ff5252" id="statAlpa">0</div>
                    <div class="label">Belum</div>
                </div>
            </div>

            <!-- Result Card -->
            <div class="result-card" id="resultCard">
                <img class="avatar" id="resultAvatar" src="" alt="">
                <div class="name" id="resultName"></div>
                <div class="nip" id="resultIdentifier"></div>
                <div class="status-badge" id="resultStatus"></div>
                <div class="mt-2" style="font-size:0.85rem; color:#aaa;" id="resultTime"></div>
            </div>

            <!-- Attendance List -->
            <div class="attendance-list" id="attendanceList">
                <h6><i class="fas fa-list"></i> Riwayat Hari Ini</h6>
                <div id="attendanceItems">
                    <div class="text-center text-muted py-3" style="font-size:0.85rem;">
                        Belum ada data absensi
                    </div>
                </div>
            </div>

            <!-- Settings Bar -->
            <div class="settings-bar">
                <button onclick="openManualModal()">
                    <i class="fas fa-keyboard"></i> Manual
                </button>
                <button onclick="refreshData()">
                    <i class="fas fa-sync"></i> Refresh
                </button>
                <button onclick="toggleFullscreen()">
                    <i class="fas fa-expand"></i> Fullscreen
                </button>
            </div>
        </div>
    </div>

    <!-- MANUAL INPUT MODAL -->
    <div class="manual-modal" id="manualModal">
        <div class="modal-content">
            <h4 class="mb-3"><i class="fas fa-keyboard"></i> Input Absensi Manual</h4>
            <p class="text-muted mb-3">Gunakan jika kamera bermasalah atau perlu input khusus.</p>
            <form id="manualForm" method="POST" action="{{ route('admin.absensi.manual') }}" enctype="multipart/form-data">
                @csrf
                <div class="mb-3">
                    <label class="form-label">Cari GTK</label>
                    <input type="text" id="manualSearchGtk" class="form-control bg-dark text-white border-secondary"
                           placeholder="Ketik nama atau NIP..." autocomplete="off">
                    <div id="manualSearchResults" class="mt-1"></div>
                    <input type="hidden" name="user_id" id="manualUserId">
                </div>
                <div class="mb-3">
                    <label class="form-label">Tanggal</label>
                    <input type="date" name="tanggal" class="form-control bg-dark text-white border-secondary"
                           value="{{ now()->format('Y-m-d') }}">
                </div>
                <div class="row mb-3">
                    <div class="col-6">
                        <label class="form-label">Jam Masuk</label>
                        <input type="time" name="waktu_masuk" class="form-control bg-dark text-white border-secondary">
                    </div>
                    <div class="col-6">
                        <label class="form-label">Jam Pulang</label>
                        <input type="time" name="waktu_pulang" class="form-control bg-dark text-white border-secondary">
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select bg-dark text-white border-secondary">
                        <option value="hadir">Hadir</option>
                        <option value="terlambat">Terlambat</option>
                        <option value="izin">Izin</option>
                        <option value="sakit">Sakit</option>
                        <option value="dinas_luar">Dinas Luar</option>
                        <option value="cuti">Cuti</option>
                        <option value="alpa">Alpa</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Catatan</label>
                    <textarea name="catatan" class="form-control bg-dark text-white border-secondary" rows="2"></textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label">Bukti (opsional)</label>
                    <input type="file" name="file_bukti" class="form-control bg-dark text-white border-secondary" accept=".jpg,.jpeg,.png,.pdf">
                </div>
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-success flex-fill">
                        <i class="fas fa-save"></i> Simpan
                    </button>
                    <button type="button" class="btn btn-secondary" onclick="closeManualModal()">Batal</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Audio feedback -->
    <audio id="audioSuccess" preload="auto">
        <source src="data:audio/wav;base64,UklGRnoGAABXQVZFZm10IBAAAAABAAEAQB8AAEAfAAABAAgAZGF0YQ==" type="audio/wav">
    </audio>

    <!-- face-api.js -->
    <script src="{{ asset('vendor/face-api/face-api.min.js') }}"></script>
    <script>
        // ============================================
        // CONFIGURATION
        // ============================================
        const CONFIG = {
            faceThreshold: {{ $settings['face_threshold'] ?? 0.45 }},
            livenessEnabled: {{ $settings['liveness_enabled'] ? 'true' : 'false' }},
            autoDetect: {{ $settings['auto_detect'] ? 'true' : 'false' }},
            detectionInterval: {{ $settings['detection_interval'] ?? 200 }},
            jamMasuk: '{{ $settings['jam_masuk'] ?? '07:00' }}',
            jamPulang: '{{ $settings['jam_pulang'] ?? '16:00' }}',
        };

        let currentTab = 'masuk';
        let currentUserType = '{{ $userType }}';
        let faceDatabase = [];
        let isProcessing = false;
        let detectionLoop = null;
        let lastMatchTime = 0;
        const MATCH_COOLDOWN = 5000; // 5 seconds cooldown between same-person matches

        // ============================================
        // CLOCK
        // ============================================
        function updateClock() {
            const now = new Date();
            document.getElementById('currentTime').textContent = now.toLocaleTimeString('id-ID', { hour12: false });
            document.getElementById('currentDate').textContent = now.toLocaleDateString('id-ID', {
                weekday: 'long', year: 'numeric', month: 'long', day: 'numeric'
            });
        }
        setInterval(updateClock, 1000);
        updateClock();

        // ============================================
        // FULLSCREEN
        // ============================================
        function toggleFullscreen() {
            if (!document.fullscreenElement) {
                document.documentElement.requestFullscreen().catch(()=>{});
            } else {
                document.exitFullscreen();
            }
        }

        // Auto-enter fullscreen on first click (requires user gesture)
        document.addEventListener('DOMContentLoaded', () => {
            document.addEventListener('click', function enterFS() {
                if (!document.fullscreenElement) {
                    document.documentElement.requestFullscreen().catch(()=>{});
                }
                document.removeEventListener('click', enterFS);
            }, { once: true });

            document.getElementById('userTypeSelect')?.addEventListener('change', function() {
                const locationId = document.getElementById('locationSelect')?.value || '';
                const url = new URL(window.location.href);
                url.searchParams.set('type', this.value);
                if (locationId) {
                    url.searchParams.set('location', locationId);
                } else {
                    url.searchParams.delete('location');
                }
                window.location.href = url.toString();
            });
        });

        // ============================================
        // TAB SWITCHING
        // ============================================
        function switchTab(tab) {
            currentTab = tab;
            document.getElementById('tabMasuk').classList.toggle('active', tab === 'masuk');
            document.getElementById('tabPulang').classList.toggle('active', tab === 'pulang');
        }

        // ============================================
        // CAMERA INITIALIZATION
        // ============================================
        async function initCamera() {
            const video = document.getElementById('videoElement');
            const errorDiv = document.getElementById('cameraError');
            
            try {
                const stream = await navigator.mediaDevices.getUserMedia({
                    video: {
                        width: { ideal: 1280 },
                        height: { ideal: 720 },
                        facingMode: 'user',
                        frameRate: { ideal: 30 },
                    }
                });
                video.srcObject = stream;
                await new Promise(resolve => { video.onloadedmetadata = resolve; });
                await video.play();
                video.style.display = 'block';
                errorDiv.style.display = 'none';
                console.log(`Camera ready: ${video.videoWidth}x${video.videoHeight}`);
                return true;
            } catch (err) {
                console.error('Camera error:', err);
                video.style.display = 'none';
                errorDiv.style.display = 'block';
                return false;
            }
        }

        // ============================================
        // FACE-API INITIALIZATION
        // ============================================
        async function loadModels() {
            const MODEL_URL = '{{ asset("vendor/face-api/models") }}';
            
            setLoadingText('Memuat model deteksi wajah...');
            await faceapi.nets.tinyFaceDetector.loadFromUri(MODEL_URL);
            
            setLoadingText('Memuat model landmark wajah...');
            await faceapi.nets.faceLandmark68TinyNet.loadFromUri(MODEL_URL);
            
            setLoadingText('Memuat model pengenalan wajah...');
            await faceapi.nets.faceRecognitionNet.loadFromUri(MODEL_URL);

            setLoadingText('Memuat database wajah...');
            await loadFaceDatabase();
            
            setLoadingText('Menyalakan kamera...');
            const cameraOk = await initCamera();
            
            if (cameraOk) {
                document.getElementById('loadingOverlay').style.display = 'none';
                startDetection();
            } else {
                document.getElementById('loadingOverlay').style.display = 'none';
            }
        }

        function setLoadingText(text) {
            document.getElementById('loadingText').textContent = text;
        }

        // ============================================
        // FACE DATABASE
        // ============================================
        async function loadFaceDatabase() {
            try {
                const response = await fetch(`{{ route("admin.absensi.face-descriptors") }}?type=${currentUserType}&verified_only=1`, {
                    headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
                });
                const result = await response.json();
                if (result.success) {
                    faceDatabase = result.data.map(person => ({
                        userId: person.user_id,
                        userType: person.user_type,
                        name: person.name,
                        identifier: person.identifier,
                        foto: person.foto,
                        descriptors: person.descriptors.map(d => new Float32Array(d)),
                    }));
                    console.log(`Loaded ${faceDatabase.length} face profiles`);
                    if (faceDatabase.length === 0) {
                        setCameraStatus('error', `Database wajah ${currentUserType === 'siswa' ? 'siswa' : 'GTK'} kosong atau belum approved.`);
                    }
                } else {
                    console.error('Face database response not success:', result);
                    setCameraStatus('error', 'Gagal memuat database wajah');
                }
            } catch (err) {
                console.error('Error loading face database:', err);
                setCameraStatus('error', 'Error: ' + err.message);
            }
        }

        // ============================================
        // REAL-TIME FACE DETECTION & TRACKING
        // ============================================
        function startDetection() {
            const video = document.getElementById('videoElement');
            const canvas = document.getElementById('overlayCanvas');
            const ctx = canvas.getContext('2d');

            // Match canvas size to video
            function resizeCanvas() {
                canvas.width = video.videoWidth || video.clientWidth;
                canvas.height = video.videoHeight || video.clientHeight;
            }
            video.addEventListener('loadedmetadata', resizeCanvas);
            resizeCanvas();

            const options = new faceapi.TinyFaceDetectorOptions({
                inputSize: 320,
                scoreThreshold: 0.5,
            });

            async function detect() {
                if (video.paused || video.ended) {
                    console.warn('Video paused or ended, retrying...');
                    detectionLoop = requestAnimationFrame(() => setTimeout(detect, 1000));
                    return;
                }

                // Ensure video has dimensions
                if (video.videoWidth === 0 || video.videoHeight === 0) {
                    console.warn('Video not ready yet, retrying...');
                    detectionLoop = requestAnimationFrame(() => setTimeout(detect, 500));
                    return;
                }

                resizeCanvas();

                try {
                    const detections = await faceapi
                        .detectAllFaces(video, options)
                        .withFaceLandmarks(true)
                        .withFaceDescriptors();

                // Clear canvas
                ctx.clearRect(0, 0, canvas.width, canvas.height);

                if (detections.length > 0) {
                    // Scale detections to canvas size
                    const dims = faceapi.matchDimensions(canvas, video, true);
                    const resizedDetections = faceapi.resizeResults(detections, dims);

                    // Draw face tracking points (landmark dots)
                    resizedDetections.forEach(det => {
                        const landmarks = det.landmarks;
                        const positions = landmarks.positions;

                        // Draw all 68 landmark points
                        positions.forEach(pt => {
                            ctx.beginPath();
                            ctx.arc(pt.x, pt.y, 2, 0, 2 * Math.PI);
                            ctx.fillStyle = '#00e5ff';
                            ctx.globalAlpha = 0.7;
                            ctx.fill();
                        });

                        // Draw bounding box with rounded corners
                        const box = det.detection.box;
                        ctx.globalAlpha = 1;
                        ctx.strokeStyle = '#00e5ff';
                        ctx.lineWidth = 2;
                        drawRoundedRect(ctx, box.x, box.y, box.width, box.height, 10);

                        // Draw confidence score
                        const score = (det.detection.score * 100).toFixed(0);
                        ctx.fillStyle = '#00e5ff';
                        ctx.font = '14px Arial';
                        ctx.fillText(`${score}%`, box.x + 5, box.y - 5);
                    });

                    // Update face guide
                    document.getElementById('faceGuide').classList.add('detected');
                    
                    // Try matching (only the best/largest face)
                    if (!isProcessing && Date.now() - lastMatchTime > MATCH_COOLDOWN) {
                        const bestDetection = detections.reduce((best, det) =>
                            det.detection.score > best.detection.score ? det : best
                        );
                        matchFace(bestDetection.descriptor);
                    }

                    setCameraStatus('detecting', `Wajah terdeteksi (${detections.length})`);
                } else {
                    document.getElementById('faceGuide').classList.remove('detected');
                    setCameraStatus('idle', 'Menunggu wajah...');
                }

                } catch (err) {
                    console.error('Detection error:', err);
                    setCameraStatus('error', 'Error deteksi: ' + err.message);
                }

                detectionLoop = requestAnimationFrame(() => {
                    setTimeout(detect, CONFIG.detectionInterval);
                });
            }

            detect();
        }

        function drawRoundedRect(ctx, x, y, w, h, r) {
            ctx.beginPath();
            ctx.moveTo(x + r, y);
            ctx.lineTo(x + w - r, y);
            ctx.quadraticCurveTo(x + w, y, x + w, y + r);
            ctx.lineTo(x + w, y + h - r);
            ctx.quadraticCurveTo(x + w, y + h, x + w - r, y + h);
            ctx.lineTo(x + r, y + h);
            ctx.quadraticCurveTo(x, y + h, x, y + h - r);
            ctx.lineTo(x, y + r);
            ctx.quadraticCurveTo(x, y, x + r, y);
            ctx.closePath();
            ctx.stroke();
        }

        // ============================================
        // FACE MATCHING
        // ============================================
        async function matchFace(descriptor) {
            if (faceDatabase.length === 0) {
                setCameraStatus('error', 'Database wajah kosong!');
                return;
            }

            let bestMatch = null;
            let bestDistance = Infinity;

            for (const person of faceDatabase) {
                for (const refDesc of person.descriptors) {
                    const distance = faceapi.euclideanDistance(descriptor, refDesc);
                    if (distance < bestDistance) {
                        bestDistance = distance;
                        bestMatch = person;
                    }
                }
            }

            if (bestMatch && bestDistance < CONFIG.faceThreshold) {
                console.log(`Match: ${bestMatch.name} (distance: ${bestDistance.toFixed(4)}, threshold: ${CONFIG.faceThreshold})`);
                isProcessing = true;
                lastMatchTime = Date.now();

                const confidence = 1 - bestDistance;
                setCameraStatus('matched', `✓ ${bestMatch.name}`);
                document.getElementById('faceGuide').classList.add('matching');

                // Capture photo from video
                const photoData = captureVideoFrame();

                // Record attendance
                await recordAttendance(bestMatch, confidence, photoData);

                setTimeout(() => {
                    isProcessing = false;
                    document.getElementById('faceGuide').classList.remove('matching');
                }, MATCH_COOLDOWN);
            }
        }

        function captureVideoFrame() {
            const video = document.getElementById('videoElement');
            const tempCanvas = document.createElement('canvas');
            tempCanvas.width = 320;
            tempCanvas.height = 240;
            const tempCtx = tempCanvas.getContext('2d');
            tempCtx.drawImage(video, 0, 0, 320, 240);
            return tempCanvas.toDataURL('image/jpeg', 0.7);
        }

        // ============================================
        // RECORD ATTENDANCE (API CALL)
        // ============================================
        async function recordAttendance(person, confidence, photoData) {
            const locationId = document.getElementById('locationSelect')?.value || null;

            try {
                const response = await fetch('{{ route("admin.absensi.record-face") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        user_id: person.userId,
                        user_type: person.userType || currentUserType,
                        confidence: confidence,
                        location_id: locationId || null,
                        photo: photoData,
                        type: currentTab,
                    }),
                });

                if (!response.ok) {
                    const errData = await response.json().catch(() => null);
                    const msg = errData?.message || errData?.errors ? Object.values(errData.errors).flat().join(', ') : `Error ${response.status}`;
                    console.error('Record error:', response.status, errData);
                    showNotification(msg, 'error');
                    return;
                }

                const result = await response.json();

                if (result.success) {
                    showResult(person, result.data);
                    playSuccessSound();
                    refreshAttendanceList();
                } else {
                    showNotification(result.message || 'Gagal mencatat', 'warning');
                }
            } catch (err) {
                console.error('Record error:', err);
                showNotification('Gagal mencatat absensi: ' + err.message, 'error');
            }
        }

        // ============================================
        // UI HELPERS
        // ============================================
        function setCameraStatus(type, text) {
            const el = document.getElementById('cameraStatus');
            el.className = 'camera-status ' + type;
            el.innerHTML = `<i class="fas fa-${type === 'idle' ? 'video' : type === 'detecting' ? 'face-grin' : type === 'matched' ? 'check-circle' : 'times-circle'}"></i> ${text}`;
        }

        function showResult(person, data) {
            const card = document.getElementById('resultCard');
            document.getElementById('resultAvatar').src = person.foto || 'https://ui-avatars.com/api/?name=' + encodeURIComponent(person.name);
            document.getElementById('resultName').textContent = data.nama || person.name;
            document.getElementById('resultIdentifier').textContent = person.identifier
                ? `${currentUserType === 'siswa' ? 'NISN' : 'NIP'}: ${person.identifier}`
                : '';
            
            const status = data.status || data.status_pulang || 'hadir';
            const statusEl = document.getElementById('resultStatus');
            statusEl.textContent = status.charAt(0).toUpperCase() + status.slice(1).replace('_', ' ');
            statusEl.className = 'status-badge status-' + status;
            
            document.getElementById('resultTime').textContent = data.waktu;
            if (data.durasi) {
                document.getElementById('resultTime').textContent += ' | Durasi: ' + data.durasi;
            }

            card.classList.add('show');
            setTimeout(() => card.classList.remove('show'), 4000);
        }

        function showNotification(message, type = 'info') {
            const card = document.getElementById('resultCard');
            document.getElementById('resultAvatar').src = 'https://ui-avatars.com/api/?name=!&background=' + (type === 'error' ? 'ff5252' : 'ffab00') + '&color=fff';
            document.getElementById('resultName').textContent = message;
            document.getElementById('resultIdentifier').textContent = '';
            document.getElementById('resultStatus').textContent = '';
            document.getElementById('resultTime').textContent = '';
            card.classList.add('show');
            setTimeout(() => card.classList.remove('show'), 3000);
        }

        function playSuccessSound() {
            // Simple beep using Web Audio API
            try {
                const ctx = new (window.AudioContext || window.webkitAudioContext)();
                const osc = ctx.createOscillator();
                const gain = ctx.createGain();
                osc.connect(gain);
                gain.connect(ctx.destination);
                osc.frequency.value = 800;
                gain.gain.value = 0.3;
                osc.start();
                osc.stop(ctx.currentTime + 0.15);
                setTimeout(() => {
                    const osc2 = ctx.createOscillator();
                    const gain2 = ctx.createGain();
                    osc2.connect(gain2);
                    gain2.connect(ctx.destination);
                    osc2.frequency.value = 1200;
                    gain2.gain.value = 0.3;
                    osc2.start();
                    osc2.stop(ctx.currentTime + 0.2);
                }, 150);
            } catch(e) {}
        }

        // ============================================
        // ATTENDANCE LIST REFRESH
        // ============================================
        async function refreshAttendanceList() {
            try {
                const response = await fetch(`{{ route("admin.absensi.today-data") }}?type=${currentUserType}`, {
                    headers: { 'Accept': 'application/json' }
                });
                const result = await response.json();
                if (result.success) {
                    renderAttendanceList(result.data);
                    updateStats(result.stats);
                }
            } catch(e) {
                console.error('Refresh error:', e);
            }
        }

        function renderAttendanceList(items) {
            const container = document.getElementById('attendanceItems');
            if (items.length === 0) {
                container.innerHTML = '<div class="text-center text-muted py-3" style="font-size:0.85rem;">Belum ada data absensi</div>';
                return;
            }
            container.innerHTML = items.map(item => `
                <div class="att-item">
                    <span class="time">${item.waktu}</span>
                    <span class="name">${item.nama}</span>
                    <span class="badge-s" style="background:${getStatusColor(item.status)}">${item.status}</span>
                </div>
            `).join('');
        }

        function updateStats(stats) {
            document.getElementById('statHadir').textContent = (stats.hadir || 0) + (stats.terlambat || 0);
            document.getElementById('statTerlambat').textContent = stats.terlambat || 0;
            document.getElementById('statAlpa').textContent = stats.belum || 0;
        }

        function getStatusColor(status) {
            const colors = { hadir: '#00e676', terlambat: '#ffab00', izin: '#29b6f6', sakit: '#7c4dff', alpa: '#ff5252', dinas_luar: '#78909c', cuti: '#546e7a' };
            return colors[status] || '#666';
        }

        function refreshData() {
            loadFaceDatabase();
            refreshAttendanceList();
            showNotification('Data berhasil diperbarui', 'info');
        }

        // ============================================
        // MANUAL MODAL
        // ============================================
        function openManualModal() {
            document.getElementById('manualModal').classList.add('show');
        }
        function closeManualModal() {
            document.getElementById('manualModal').classList.remove('show');
        }

        // ============================================
        // INIT
        // ============================================
        document.addEventListener('DOMContentLoaded', () => {
            loadModels();
            refreshAttendanceList();
            // Auto refresh every 60 seconds
            setInterval(refreshAttendanceList, 60000);
        });

        // ESC to close manual modal
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') closeManualModal();
        });
    </script>
</body>
</html>
