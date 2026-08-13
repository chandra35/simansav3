<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Kiosk {{ $userType === 'siswa' ? 'Siswa' : 'GTK' }} - SIMANSA</title>
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
        .status-tepat_waktu { background: #29b6f6; color: #001b2a; }

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
        .kiosk-type-switch{display:flex;gap:5px}.kiosk-type-switch a{padding:6px 11px;border:1px solid #2a2a6e;border-radius:7px;color:#a5b4fc;text-decoration:none;font-size:.75rem;font-weight:700}.kiosk-type-switch a.active{background:#4f46e5;color:#fff;border-color:#6366f1}.automatic-mode{margin:10px;padding:12px;border:1px solid #2a2a6e;border-radius:10px;background:rgba(255,255,255,.04)}.automatic-mode__top{display:flex;align-items:center;justify-content:space-between;gap:8px}.automatic-mode__badge{padding:5px 12px;border-radius:20px;font-size:.75rem;font-weight:800;text-transform:uppercase}.automatic-mode__badge.masuk{background:#00e676;color:#032b18}.automatic-mode__badge.pulang{background:#29b6f6;color:#041d2c}.automatic-mode__badge.closed{background:#ffab00;color:#2c1c00}.automatic-mode strong,.automatic-mode small{display:block}.automatic-mode small{color:#94a3b8;margin-top:5px}.operational-overlay{position:absolute;inset:0;z-index:90;background:radial-gradient(circle at center,rgba(30,41,100,.96),rgba(3,7,30,.99));display:flex;align-items:center;justify-content:center;text-align:center;padding:30px}.operational-overlay__card{max-width:540px}.operational-overlay__icon{width:82px;height:82px;border-radius:50%;display:inline-flex;align-items:center;justify-content:center;background:rgba(255,171,0,.12);color:#ffab00;font-size:2rem;margin-bottom:18px}.operational-overlay h2{font-size:1.6rem}.operational-countdown{font-size:2.3rem;font-weight:800;color:#00e5ff;font-variant-numeric:tabular-nums;margin:14px 0}.operational-schedule-text{font-size:.82rem;color:#94a3b8}.camera-section.is-closed video,.camera-section.is-closed canvas,.camera-section.is-closed .face-guide,.camera-section.is-closed .camera-status{visibility:hidden}
        @media(max-width:900px){.kiosk-header{padding:8px 12px}.kiosk-header h1{font-size:1rem}.kiosk-header .date{display:none}.kiosk-header .clock{font-size:1.25rem}.kiosk-type-switch{display:none}.kiosk-body{grid-template-columns:1fr 310px}}
    </style>
</head>
<body>
    <!-- HEADER -->
    <div class="kiosk-header">
        <div>
            <h1><i class="fas fa-fingerprint"></i> SIMANSA - Kiosk {{ $userType === 'siswa' ? 'Siswa' : 'GTK' }}</h1>
            <div class="date" id="currentDate"></div>
        </div>
        <div style="display:flex; align-items:center; gap:15px;">
            <div class="kiosk-type-switch">
                <a href="{{ route('admin.absensi.kiosk', array_filter(['type' => 'gtk', 'location' => $location?->id])) }}" class="{{ $userType === 'gtk' ? 'active' : '' }}"><i class="fas fa-user-tie"></i> GTK</a>
                <a href="{{ route('admin.absensi.kiosk', array_filter(['type' => 'siswa', 'location' => $location?->id])) }}" class="{{ $userType === 'siswa' ? 'active' : '' }}"><i class="fas fa-user-graduate"></i> SISWA</a>
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

            <div class="operational-overlay" id="operationalOverlay" style="{{ $operationalState['is_open'] ? 'display:none' : '' }}">
                <div class="operational-overlay__card"><span class="operational-overlay__icon"><i class="fas fa-clock"></i></span><h2 id="closedTitle">Presensi sedang ditutup</h2><p id="closedReason">{{ $operationalState['reason'] }}</p><div class="operational-countdown" id="operationalCountdown">--:--:--</div><div class="operational-schedule-text" id="operationalScheduleText"></div></div>
            </div>

            <!-- Camera error fallback -->
            <div id="cameraError" style="display:none; text-align:center; padding:40px;">
                <i class="fas fa-video-slash fa-3x text-danger mb-3"></i>
                <h4>Kamera Tidak Tersedia</h4>
                <p class="text-muted">Pastikan kamera terhubung dan browser memiliki izin akses.</p>
                <button class="btn btn-outline-info mt-3" onclick="initCamera()">
                    <i class="fas fa-redo"></i> Coba Lagi
                </button>
            </div>
        </div>

        <!-- SIDEBAR -->
        <div class="sidebar">
            <div class="automatic-mode">
                <div class="automatic-mode__top"><strong><i class="fas fa-magic mr-1"></i>Mode Otomatis</strong><span class="automatic-mode__badge {{ $operationalState['mode'] }}" id="operationalMode">{{ $operationalState['mode'] === 'masuk' ? 'Masuk' : ($operationalState['mode'] === 'pulang' ? 'Pulang' : 'Ditutup') }}</span></div>
                <small id="operationalReason">{{ $operationalState['reason'] }}</small>
                @if($operationalState['schedule'])<small>{{ $operationalState['schedule']['check_in_open'] }}–{{ $operationalState['schedule']['check_in_close'] }} masuk · {{ $operationalState['schedule']['check_out_open'] }} pulang</small>@endif
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
                <button onclick="refreshData()">
                    <i class="fas fa-sync"></i> Refresh
                </button>
                <button onclick="toggleFullscreen()">
                    <i class="fas fa-expand"></i> Fullscreen
                </button>
            </div>
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
        };

        let operationalState = @json($operationalState);
        const currentUserType = @json($userType);
        let serverOffset = new Date(operationalState.server_time).getTime() - Date.now();
        let faceDatabase = [];
        let isProcessing = false;
        let detectionLoop = null;
        let lastMatchTime = 0;
        const MATCH_COOLDOWN = 5000; // 5 seconds cooldown between same-person matches
        let livenessState = createLivenessState();

        function createLivenessState() {
            return {
                challenge: null,
                samples: [],
                earHistory: [],
                eyeWasClosed: false,
                blinkCount: 0,
                blinkCloseFrames: 0,
                unlockedUntil: 0,
            };
        }

        function pickLivenessChallenge() {
            const challenges = [
                { type: 'blink', text: 'Kedipkan mata 1 kali' },
                { type: 'right', text: 'Toleh sedikit ke kanan' },
                { type: 'left', text: 'Toleh sedikit ke kiri' },
                { type: 'smile', text: 'Beri senyum tipis' },
            ];
            return challenges[Math.floor(Math.random() * challenges.length)];
        }

        function setLivenessInstruction(text, active = true) {
            const box = document.getElementById('livenessInstruction');
            const label = document.getElementById('livenessText');
            if (!box || !label) return;
            label.textContent = text;
            box.classList.toggle('active', active);
        }

        function computeEyeAspectRatio(eye) {
            const d = (a, b) => Math.sqrt((a.x - b.x) ** 2 + (a.y - b.y) ** 2);
            return (d(eye[1], eye[5]) + d(eye[2], eye[4])) / (2 * d(eye[0], eye[3]));
        }

        function collectKioskMetrics(landmarks) {
            const pts = landmarks.positions;
            const noseTip = pts[30], jawLeft = pts[0], jawRight = pts[16], mouthL = pts[48], mouthR = pts[54];
            const dL = Math.sqrt((noseTip.x - jawLeft.x) ** 2 + (noseTip.y - jawLeft.y) ** 2);
            const dR = Math.sqrt((noseTip.x - jawRight.x) ** 2 + (noseTip.y - jawRight.y) ** 2);
            const yawRatio = dL / Math.max(dR, 0.001);
            const mouthW = Math.sqrt((mouthR.x - mouthL.x) ** 2 + (mouthR.y - mouthL.y) ** 2);
            const jawW = Math.sqrt((jawRight.x - jawLeft.x) ** 2 + (jawRight.y - jawLeft.y) ** 2);
            const smileRatio = mouthW / Math.max(jawW, 0.001);
            return { yawRatio, smileRatio };
        }

        function ensureLivenessChallenge() {
            if (!livenessState.challenge) {
                livenessState.challenge = pickLivenessChallenge();
            }
            return livenessState.challenge;
        }

        function handleKioskBlink(landmarks) {
            const leftEye = landmarks.getLeftEye();
            const rightEye = landmarks.getRightEye();
            const rawEar = (computeEyeAspectRatio(leftEye) + computeEyeAspectRatio(rightEye)) / 2;
            livenessState.earHistory.push(rawEar);
            if (livenessState.earHistory.length > 3) livenessState.earHistory.shift();
            const ear = livenessState.earHistory.reduce((sum, value) => sum + value, 0) / livenessState.earHistory.length;
            const threshold = 0.26;

            if (ear < threshold) {
                livenessState.blinkCloseFrames++;
                livenessState.eyeWasClosed = true;
            } else if (ear > threshold + 0.03 && livenessState.eyeWasClosed) {
                if (livenessState.blinkCloseFrames >= 2) {
                    livenessState.blinkCount++;
                }
                livenessState.eyeWasClosed = false;
                livenessState.blinkCloseFrames = 0;
            }
        }

        function verifyLiveness(detection) {
            if (!CONFIG.livenessEnabled) {
                setLivenessInstruction('Deteksi hidup dimatikan oleh admin.', false);
                return true;
            }

            if (Date.now() < livenessState.unlockedUntil) {
                setLivenessInstruction('Liveness terverifikasi.', false);
                return true;
            }

            const challenge = ensureLivenessChallenge();
            const metrics = collectKioskMetrics(detection.landmarks);
            livenessState.samples.push(metrics);
            if (livenessState.samples.length > 18) livenessState.samples.shift();
            handleKioskBlink(detection.landmarks);

            const yawValues = livenessState.samples.map(sample => sample.yawRatio);
            const smileValues = livenessState.samples.map(sample => sample.smileRatio);
            const yawMin = Math.min(...yawValues);
            const yawMax = Math.max(...yawValues);
            const smileMax = Math.max(...smileValues);
            const yawSpan = yawMax - yawMin;

            let passed = false;
            if (challenge.type === 'blink') {
                passed = livenessState.blinkCount >= 1;
            } else if (challenge.type === 'right') {
                passed = yawMin < 0.84 && yawSpan > 0.12;
            } else if (challenge.type === 'left') {
                passed = yawMax > 1.18 && yawSpan > 0.12;
            } else if (challenge.type === 'smile') {
                passed = smileMax > 0.39;
            }

            if (passed) {
                livenessState.unlockedUntil = Date.now() + 8000;
                livenessState.challenge = null;
                livenessState.samples = [];
                livenessState.earHistory = [];
                livenessState.eyeWasClosed = false;
                livenessState.blinkCloseFrames = 0;
                livenessState.blinkCount = 0;
                setLivenessInstruction('Liveness terverifikasi. Silakan lanjut.', true);
                return true;
            }

            setLivenessInstruction(challenge.text, true);
            setCameraStatus('detecting', `Liveness: ${challenge.text}`);
            return false;
        }

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

        });

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
                if (!operationalState.is_open) {
                    detectionLoop = requestAnimationFrame(() => setTimeout(detect, 1000));
                    return;
                }
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
                    const bestDetection = detections.reduce((best, det) =>
                        det.detection.score > best.detection.score ? det : best
                    );
                    
                    // Try matching (only after liveness passes)
                    const livenessOk = verifyLiveness(bestDetection);
                    if (livenessOk && !isProcessing && Date.now() - lastMatchTime > MATCH_COOLDOWN) {
                        matchFace(bestDetection.descriptor);
                    }

                    if (!CONFIG.livenessEnabled || Date.now() < livenessState.unlockedUntil) {
                        setCameraStatus('detecting', `Wajah terdeteksi (${detections.length})`);
                    }
                } else {
                    document.getElementById('faceGuide').classList.remove('detected');
                    if (CONFIG.livenessEnabled) {
                        livenessState = createLivenessState();
                        setLivenessInstruction('Mendeteksi wajah...', false);
                    }
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
                    }),
                });

                if (!response.ok) {
                    const errData = await response.json().catch(() => null);
                    const msg = errData?.message || (errData?.errors ? Object.values(errData.errors).flat().join(', ') : `Error ${response.status}`);
                    console.error('Record error:', response.status, errData);
                    if (errData?.window) applyOperationalState(errData.window);
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
                const response = await fetch(`{{ route("admin.absensi.kiosk-today-data") }}?type=${currentUserType}`, {
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

        function formatCountdown(target) {
            if (!target) return 'Jadwal berikutnya belum ditentukan';
            const distance = new Date(target).getTime() - (Date.now() + serverOffset);
            if (distance <= 0) return '00:00:00';
            const total = Math.floor(distance / 1000), days = Math.floor(total / 86400), hours = Math.floor((total % 86400) / 3600), minutes = Math.floor((total % 3600) / 60), seconds = total % 60;
            return `${days ? days + ' hari ' : ''}${String(hours).padStart(2,'0')}:${String(minutes).padStart(2,'0')}:${String(seconds).padStart(2,'0')}`;
        }

        function applyOperationalState(state) {
            const changed = state.mode !== operationalState.mode || state.is_open !== operationalState.is_open;
            operationalState = state;
            serverOffset = new Date(state.server_time).getTime() - Date.now();
            const mode = document.getElementById('operationalMode');
            mode.className = 'automatic-mode__badge ' + state.mode;
            mode.textContent = state.mode === 'masuk' ? 'Masuk' : (state.mode === 'pulang' ? 'Pulang' : 'Ditutup');
            document.getElementById('operationalReason').textContent = state.reason;
            document.getElementById('closedReason').textContent = state.reason;
            document.getElementById('operationalOverlay').style.display = state.is_open ? 'none' : 'flex';
            document.querySelector('.camera-section').classList.toggle('is-closed', !state.is_open);
            if (changed) window.location.reload();
        }

        async function refreshOperationalState() {
            try {
                const response = await fetch(`{{ route('admin.absensi.kiosk-state') }}?type=${currentUserType}`, {headers:{'Accept':'application/json'}});
                const result = await response.json();
                if (result.success) applyOperationalState(result.state);
            } catch (error) { console.error('Gagal memperbarui jadwal kiosk:', error); }
        }

        function renderOperationalCountdown() {
            document.getElementById('operationalCountdown').textContent = formatCountdown(operationalState.next_at);
            if (operationalState.schedule) {
                const schedule = operationalState.schedule;
                document.getElementById('operationalScheduleText').textContent = `${schedule.day} · Masuk ${schedule.check_in_open}–${schedule.check_in_close} · Pulang mulai ${schedule.check_out_open}`;
            }
        }

        // ============================================
        // INIT
        // ============================================
        document.addEventListener('DOMContentLoaded', () => {
            document.querySelector('.camera-section').classList.toggle('is-closed', !operationalState.is_open);
            if (operationalState.is_open) loadModels(); else document.getElementById('loadingOverlay').style.display = 'none';
            refreshAttendanceList();
            setInterval(refreshAttendanceList, 60000);
            setInterval(refreshOperationalState, 30000);
            setInterval(renderOperationalCountdown, 1000);
            renderOperationalCountdown();
        });
    </script>
</body>
</html>
