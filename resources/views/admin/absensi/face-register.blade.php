@extends('adminlte::page')

@section('title', 'Registrasi Wajah')
@section('plugins.Datatables', true)
@section('plugins.DatatablesPlugins', true)

@section('content_header')
    <h1><i class="fas fa-user-shield"></i> Registrasi Wajah GTK</h1>
@stop

@section('content')
{{-- Summary --}}
<div class="row">
    <div class="col-lg-4 col-6">
        <div class="small-box bg-info">
            <div class="inner"><h3>{{ $gtkList->count() }}</h3><p>Total GTK</p></div>
            <div class="icon"><i class="fas fa-users"></i></div>
        </div>
    </div>
    <div class="col-lg-4 col-6">
        <div class="small-box bg-success">
            <div class="inner"><h3>{{ $faceMap->count() }}</h3><p>Sudah Registrasi</p></div>
            <div class="icon"><i class="fas fa-check-circle"></i></div>
        </div>
    </div>
    <div class="col-lg-4 col-6">
        <div class="small-box bg-warning">
            <div class="inner"><h3>{{ $gtkList->count() - $faceMap->count() }}</h3><p>Belum Registrasi</p></div>
            <div class="icon"><i class="fas fa-exclamation-triangle"></i></div>
        </div>
    </div>
</div>

{{-- Table --}}
<div class="card card-primary card-outline">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-table"></i> Daftar GTK & Status Registrasi Wajah</h3>
    </div>
    <div class="card-body">
        {{-- Filter Buttons --}}
        <div class="mb-3 d-flex flex-wrap align-items-center gap-2">
            <span class="mr-2 font-weight-bold"><i class="fas fa-filter"></i> Filter:</span>
            <div class="btn-group" id="statusFilter">
                <button class="btn btn-sm btn-outline-primary active" data-filter="">Semua</button>
                <button class="btn btn-sm btn-outline-secondary" data-filter="Belum"><i class="fas fa-times"></i> Belum Daftar</button>
                <button class="btn btn-sm btn-outline-warning" data-filter="Pending"><i class="fas fa-clock"></i> Pending</button>
                <button class="btn btn-sm btn-outline-success" data-filter="Verified"><i class="fas fa-check"></i> Verified</button>
            </div>
        </div>

        <div class="table-responsive">
        <table class="table table-hover table-striped table-sm" id="tabelGtk">
            <thead>
                <tr>
                    <th width="40">No</th>
                    <th>Nama GTK</th>
                    <th>NIP</th>
                    <th>Status</th>
                    <th>Capture</th>
                    <th>Quality</th>
                    <th>Verifikasi</th>
                    <th>Tgl Registrasi</th>
                    <th width="130">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($gtkList as $i => $gtk)
                    @php $face = $faceMap[$gtk->user_id] ?? null; @endphp
                    <tr>
                        <td>{{ $i + 1 }}</td>
                        <td><strong>{{ $gtk->nama_lengkap }}</strong></td>
                        <td>{{ $gtk->nip ?? '-' }}</td>
                        <td>
                            @if($face)
                                @if($face->is_verified)
                                    <span class="badge badge-success"><i class="fas fa-check"></i> Verified</span>
                                @else
                                    <span class="badge badge-warning"><i class="fas fa-clock"></i> Pending</span>
                                @endif
                            @else
                                <span class="badge badge-secondary"><i class="fas fa-times"></i> Belum</span>
                            @endif
                        </td>
                        <td>
                            @if($face)
                                <span class="badge badge-info">{{ $face->total_captures }}</span>
                            @else
                                -
                            @endif
                        </td>
                        <td>
                            @if($face)
                                @php $q = $face->quality_score ?? 0; @endphp
                                <span class="badge badge-{{ $q >= 80 ? 'success' : ($q >= 50 ? 'warning' : 'danger') }}">{{ number_format($q, 0) }}%</span>
                            @else
                                -
                            @endif
                        </td>
                        <td>
                            @if($face && $face->is_verified)
                                <small>{{ $face->verified_at?->format('d/m/Y') }}</small>
                            @elseif($face)
                                <small class="text-muted">Menunggu</small>
                            @else
                                -
                            @endif
                        </td>
                        <td>
                            @if($face)
                                <small>{{ $face->created_at->format('d/m/Y H:i') }}</small>
                            @else
                                -
                            @endif
                        </td>
                        <td>
                            <button class="btn btn-sm btn-{{ $face ? 'warning' : 'primary' }}"
                                    onclick="openRegister('{{ $gtk->user_id }}', '{{ addslashes($gtk->nama_lengkap) }}')">
                                <i class="fas fa-{{ $face ? 'redo' : 'camera' }}"></i>
                                {{ $face ? 'Ulang' : 'Daftar' }}
                            </button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        </div>
    </div>
</div>

{{-- Modal Camera Registration --}}
<div class="modal fade" id="modalRegister" tabindex="-1" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white py-2">
                <h5 class="modal-title"><i class="fas fa-camera"></i> Registrasi Wajah — <span id="modalUserName"></span></h5>
                <button type="button" class="close text-white" onclick="closeRegister()"><span>&times;</span></button>
            </div>
            <div class="modal-body p-0">
                <div class="row no-gutters">
                    {{-- Camera --}}
                    <div class="col-md-8 position-relative" style="background:#000; min-height:400px;">
                        <div id="loadingOverlay" style="position:absolute; inset:0; background:rgba(0,0,0,0.8); display:flex; flex-direction:column; align-items:center; justify-content:center; z-index:10;">
                            <div class="spinner-border text-info mb-3" role="status"></div>
                            <p class="text-white" id="loadingText">Memuat model face detection...</p>
                        </div>

                        <video id="videoElement" autoplay playsinline style="width:100%; height:100%; object-fit:cover; transform:scaleX(-1);"></video>
                        <canvas id="overlayCanvas" style="position:absolute; top:0; left:0; width:100%; height:100%; transform:scaleX(-1);"></canvas>

                        {{-- Step instruction --}}
                        <div id="stepInstruction" style="position:absolute; top:15px; left:50%; transform:translateX(-50%); background:rgba(0,0,0,0.7); color:#00e5ff; padding:10px 25px; border-radius:25px; font-size:1.1rem; font-weight:600; text-align:center; z-index:5; display:none;">
                            <i class="fas fa-arrow-right" id="stepIcon"></i>
                            <span id="stepText">Lihat ke kamera</span>
                        </div>

                        {{-- Countdown ring --}}
                        <div id="autoCaptureIndicator" style="position:absolute; top:50%; left:50%; transform:translate(-50%,-50%); z-index:6; display:none;">
                            <svg width="80" height="80" viewBox="0 0 80 80">
                                <circle cx="40" cy="40" r="35" stroke="#333" stroke-width="4" fill="none" opacity="0.3"/>
                                <circle id="countdownCircle" cx="40" cy="40" r="35" stroke="#00e676" stroke-width="4" fill="none"
                                    stroke-dasharray="220" stroke-dashoffset="220" stroke-linecap="round"
                                    style="transition: stroke-dashoffset 1.5s linear; transform: rotate(-90deg); transform-origin: center;"/>
                            </svg>
                        </div>

                        {{-- Face status --}}
                        <div id="faceStatus" style="position:absolute; bottom:15px; left:50%; transform:translateX(-50%); background:rgba(0,0,0,0.7); color:#aaa; padding:8px 20px; border-radius:20px; font-size:0.9rem; z-index:5;">
                            <i class="fas fa-video"></i> Menunggu...
                        </div>
                    </div>

                    {{-- Steps Panel --}}
                    <div class="col-md-4" style="background:#f8f9fa;">
                        <div class="p-3">
                            <h6 class="mb-2"><i class="fas fa-list-ol"></i> Langkah Registrasi</h6>
                            <p class="text-muted small mb-2"><i class="fas fa-magic"></i> Auto-capture saat wajah stabil (~1.5s)</p>

                            @php
                            $steps = [
                                ['Wajah Depan', 'Lihat lurus ke kamera', 'frontal'],
                                ['Toleh Kanan', 'Putar kepala ke kanan', 'kanan'],
                                ['Toleh Kiri', 'Putar kepala ke kiri', 'kiri'],
                                ['Senyum', 'Tersenyum natural', 'senyum'],
                                ['Kedipkan Mata', 'Kedip 1x (liveness)', 'kedip'],
                            ];
                            @endphp
                            @foreach($steps as $i => [$title, $desc, $angle])
                            <div class="step-item" id="step-{{ $i }}" data-angle="{{ $angle }}">
                                <div class="d-flex align-items-center p-2 mb-1 rounded" style="background:#fff; border:2px solid #ddd;">
                                    <div class="step-number mr-2" style="width:26px; height:26px; border-radius:50%; background:#6c757d; color:#fff; display:flex; align-items:center; justify-content:center; font-weight:700; font-size:0.85rem;">{{ $i+1 }}</div>
                                    <div>
                                        <strong style="font-size:0.9rem;">{{ $title }}</strong><br>
                                        <small class="text-muted">{{ $desc }}</small>
                                    </div>
                                    <div class="ml-auto step-check" style="display:none;"><i class="fas fa-check-circle text-success"></i></div>
                                </div>
                            </div>
                            @endforeach

                            <div class="mt-2">
                                <div class="progress" style="height:6px;">
                                    <div class="progress-bar bg-success" id="progressBar" style="width:0%"></div>
                                </div>
                                <small class="text-muted" id="progressText">0 / 5 selesai</small>
                            </div>

                            <button class="btn btn-secondary btn-sm btn-block mt-2 d-none" id="btnReset" onclick="resetRegistration()">
                                <i class="fas fa-redo"></i> Ulangi
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@stop

@section('css')
<style>
    .step-item.active .d-flex { border-color: #007bff !important; background: #e8f4fd !important; }
    .step-item.done .d-flex { border-color: #28a745 !important; }
    .step-item.done .step-number { background: #28a745 !important; }
    .step-item.active .step-number { background: #007bff !important; animation: pulse 1.5s infinite; }
    .step-item.capturing .d-flex { border-color: #ffc107 !important; background: #fff8e1 !important; }
    .step-item.capturing .step-number { background: #ffc107 !important; }
    @keyframes pulse { 0%,100% { transform:scale(1); } 50% { transform:scale(1.15); } }
</style>
@stop

@section('js')
<script src="{{ asset('vendor/face-api/face-api.min.js') }}"></script>
<script>
    // ============================================
    // STATE
    // ============================================
    let selectedUserId = null;
    let selectedUserName = '';
    let currentStep = -1;
    const totalSteps = 5;
    let capturedDescriptors = [];
    let capturedAngles = [];
    let isDetecting = false;
    let modelsLoaded = false;
    let cameraStream = null;

    let faceStableStart = null;
    let autoCapturing = false;
    const STABLE_DURATION_MS = 1500;

    let blinkCount = 0;
    let lastEAR = 1;
    let earHistory = [];
    let eyeWasClosed = false;

    const STEPS = [
        { angle: 'frontal', text: 'Lihat lurus ke kamera', icon: 'fa-user' },
        { angle: 'kanan', text: 'Putar kepala ke KANAN', icon: 'fa-arrow-right' },
        { angle: 'kiri', text: 'Putar kepala ke KIRI', icon: 'fa-arrow-left' },
        { angle: 'senyum', text: 'Tersenyum natural', icon: 'fa-smile' },
        { angle: 'kedip', text: 'Kedipkan mata 1 kali', icon: 'fa-eye' },
    ];

    // ============================================
    // DATATABLE
    // ============================================
    $(function() {
        const table = $('#tabelGtk').DataTable({
            language: { url: 'https://cdn.datatables.net/plug-ins/1.13.7/i18n/id.json' },
            pageLength: 25,
            order: [[1, 'asc']],
            columnDefs: [{ orderable: false, targets: [8] }],
        });

        // Status filter buttons
        let activeFilter = '';
        $.fn.dataTable.ext.search.push(function(settings, data) {
            if (!activeFilter) return true;
            return data[3].indexOf(activeFilter) !== -1; // column 3 = Status
        });
        $('#statusFilter').on('click', 'button', function() {
            $('#statusFilter button').removeClass('active');
            $(this).addClass('active');
            activeFilter = $(this).data('filter');
            table.draw();
        });
    });

    // ============================================
    // OPEN / CLOSE REGISTER MODAL
    // ============================================
    function openRegister(userId, userName) {
        selectedUserId = userId;
        selectedUserName = userName;
        document.getElementById('modalUserName').textContent = userName;
        $('#modalRegister').modal('show');

        if (!modelsLoaded) {
            loadModels();
        } else {
            startCameraAndRegister();
        }
    }

    function closeRegister() {
        isDetecting = false;
        stopCamera();
        resetUI();
        $('#modalRegister').modal('hide');
    }

    function stopCamera() {
        if (cameraStream) {
            cameraStream.getTracks().forEach(t => t.stop());
            cameraStream = null;
        }
        const video = document.getElementById('videoElement');
        if (video) video.srcObject = null;
    }

    // ============================================
    // LOAD MODELS & CAMERA
    // ============================================
    async function loadModels() {
        const MODEL_URL = '{{ asset("vendor/face-api/models") }}';
        try {
            setLoadingText('Memuat model deteksi wajah...');
            await faceapi.nets.tinyFaceDetector.loadFromUri(MODEL_URL);
            setLoadingText('Memuat model landmark wajah...');
            await faceapi.nets.faceLandmark68TinyNet.loadFromUri(MODEL_URL);
            setLoadingText('Memuat model pengenalan wajah...');
            await faceapi.nets.faceRecognitionNet.loadFromUri(MODEL_URL);
            modelsLoaded = true;
            startCameraAndRegister();
        } catch (err) {
            setLoadingText('Error: ' + err.message);
        }
    }

    async function startCameraAndRegister() {
        setLoadingText('Menyalakan kamera...');
        document.getElementById('loadingOverlay').style.display = 'flex';
        const video = document.getElementById('videoElement');
        try {
            cameraStream = await navigator.mediaDevices.getUserMedia({
                video: { width: { ideal: 1280 }, height: { ideal: 720 }, facingMode: 'user' }
            });
            video.srcObject = cameraStream;
            await new Promise(r => { video.onloadedmetadata = r; });
            document.getElementById('loadingOverlay').style.display = 'none';
            beginAutoRegistration();
        } catch (err) {
            setLoadingText('Kamera tidak tersedia: ' + err.message);
        }
    }

    function setLoadingText(text) {
        document.getElementById('loadingText').textContent = text;
    }

    // ============================================
    // AUTO REGISTRATION FLOW
    // ============================================
    function beginAutoRegistration() {
        capturedDescriptors = [];
        capturedAngles = [];
        currentStep = 0;
        blinkCount = 0;
        earHistory = [];
        eyeWasClosed = false;
        faceStableStart = null;
        autoCapturing = false;

        resetUI();
        document.getElementById('btnReset').classList.remove('d-none');
        updateStepUI();
        startDetectionLoop();
    }

    function resetUI() {
        document.querySelectorAll('.step-item').forEach(el => {
            el.classList.remove('active', 'done', 'capturing');
            el.querySelector('.step-check').style.display = 'none';
        });
        document.getElementById('progressBar').style.width = '0%';
        document.getElementById('progressText').textContent = '0 / 5 selesai';
        document.getElementById('stepInstruction').style.display = 'none';
        document.getElementById('stepInstruction').style.background = 'rgba(0,0,0,0.7)';
        document.getElementById('stepInstruction').style.color = '#00e5ff';
        document.getElementById('btnReset').classList.add('d-none');
        hideCountdownRing();
        const canvas = document.getElementById('overlayCanvas');
        if (canvas) canvas.getContext('2d').clearRect(0, 0, canvas.width, canvas.height);
    }

    function updateStepUI() {
        if (currentStep >= totalSteps) return;
        document.querySelectorAll('.step-item').forEach((el, i) => {
            el.classList.remove('active', 'capturing');
            if (i < currentStep) el.classList.add('done');
            else if (i === currentStep) el.classList.add('active');
        });
        const step = STEPS[currentStep];
        document.getElementById('stepInstruction').style.display = 'block';
        document.getElementById('stepIcon').className = 'fas ' + step.icon + ' mr-2';
        document.getElementById('stepText').textContent = step.text;
        if (step.angle === 'kedip') { blinkCount = 0; earHistory = []; eyeWasClosed = false; }
        faceStableStart = null;
        hideCountdownRing();
    }

    // ============================================
    // DETECTION LOOP
    // ============================================
    function startDetectionLoop() {
        isDetecting = true;
        const video = document.getElementById('videoElement');
        const canvas = document.getElementById('overlayCanvas');
        const ctx = canvas.getContext('2d');
        const options = new faceapi.TinyFaceDetectorOptions({ inputSize: 320, scoreThreshold: 0.5 });

        async function detect() {
            if (!isDetecting || video.paused || currentStep < 0) return;
            canvas.width = video.videoWidth || video.clientWidth;
            canvas.height = video.videoHeight || video.clientHeight;
            const detection = await faceapi.detectSingleFace(video, options).withFaceLandmarks(true);
            ctx.clearRect(0, 0, canvas.width, canvas.height);

            if (detection) {
                const dims = faceapi.matchDimensions(canvas, video, true);
                const resized = faceapi.resizeResults(detection, dims);
                resized.landmarks.positions.forEach(pt => {
                    ctx.beginPath();
                    ctx.arc(pt.x, pt.y, 2, 0, 2 * Math.PI);
                    ctx.fillStyle = '#00e5ff';
                    ctx.globalAlpha = 0.7;
                    ctx.fill();
                });
                ctx.globalAlpha = 1;
                const box = resized.detection.box;
                ctx.strokeStyle = '#00e5ff';
                ctx.lineWidth = 2;
                ctx.strokeRect(box.x, box.y, box.width, box.height);

                setFaceStatus(`Wajah terdeteksi (${(detection.detection.score*100).toFixed(0)}%)`, true);

                if (currentStep < totalSteps && !autoCapturing) {
                    if (STEPS[currentStep].angle === 'kedip') {
                        detectBlink(detection.landmarks);
                    } else {
                        const poseOk = validatePose(detection.landmarks, detection.detection.box, STEPS[currentStep].angle);
                        if (poseOk) {
                            if (!faceStableStart) {
                                faceStableStart = Date.now();
                                showCountdownRing();
                            } else if (Date.now() - faceStableStart >= STABLE_DURATION_MS) {
                                autoCapturing = true;
                                await doCapture();
                                autoCapturing = false;
                            }
                        } else {
                            if (faceStableStart) { faceStableStart = null; hideCountdownRing(); }
                        }
                    }
                }
            } else {
                setFaceStatus('Arahkan wajah ke kamera', false);
                if (faceStableStart) { faceStableStart = null; hideCountdownRing(); }
            }
            const delay = (currentStep < totalSteps && STEPS[currentStep]?.angle === 'kedip') ? 60 : 150;
            requestAnimationFrame(() => setTimeout(detect, delay));
        }
        detect();
    }

    function setFaceStatus(text, ok) {
        const el = document.getElementById('faceStatus');
        el.style.color = ok ? '#00e676' : '#ff5252';
        el.innerHTML = `<i class="fas fa-${ok ? 'check-circle' : 'exclamation-circle'}"></i> ${text}`;
    }

    // ============================================
    // POSE VALIDATION
    // ============================================
    function validatePose(landmarks, box, angle) {
        const nose = landmarks.getNose();
        const mouth = landmarks.getMouth();
        // Nose tip = point 30 (index 4 in getNose which returns points 27-35)
        const noseTip = nose[3]; // landmark point 30
        // Yaw estimation: nose X position relative to face bounding box
        // 0.0 = left edge, 0.5 = center, 1.0 = right edge
        const noseRelX = (noseTip.x - box.x) / box.width;
        // NOTE: video is mirrored (scaleX(-1)), so visually:
        //   user turns head RIGHT -> nose moves LEFT in raw coords -> noseRelX < 0.5
        //   user turns head LEFT  -> nose moves RIGHT in raw coords -> noseRelX > 0.5

        if (angle === 'frontal') {
            const centered = noseRelX > 0.35 && noseRelX < 0.65;
            if (!centered) setFaceStatus(`Lihat lurus ke depan (${(noseRelX*100).toFixed(0)}%)`, false);
            else setFaceStatus('Bagus! Tetap menghadap depan...', true);
            return centered;
        }

        if (angle === 'kanan') {
            // User turns RIGHT visually -> in mirrored video, raw noseRelX shifts LEFT (< 0.5)
            const turned = noseRelX < 0.38;
            if (!turned) setFaceStatus(`Putar kepala ke KANAN Anda (${(noseRelX*100).toFixed(0)}%)`, false);
            else setFaceStatus('Bagus! Tahan posisi...', true);
            return turned;
        }

        if (angle === 'kiri') {
            // User turns LEFT visually -> in mirrored video, raw noseRelX shifts RIGHT (> 0.5)
            const turned = noseRelX > 0.62;
            if (!turned) setFaceStatus(`Putar kepala ke KIRI Anda (${(noseRelX*100).toFixed(0)}%)`, false);
            else setFaceStatus('Bagus! Tahan posisi...', true);
            return turned;
        }

        if (angle === 'senyum') {
            // Smile detection via Mouth Aspect Ratio
            // mouth points: 0=left corner, 6=right corner, 3=top lip center, 9=bottom lip center
            const mouthWidth = Math.sqrt((mouth[6].x - mouth[0].x)**2 + (mouth[6].y - mouth[0].y)**2);
            const mouthHeight = Math.sqrt((mouth[9].x - mouth[3].x)**2 + (mouth[9].y - mouth[3].y)**2);
            const mar = mouthWidth / (mouthHeight + 0.001);
            // When smiling, mouth gets wider -> MAR increases (typically > 4.5)
            const smiling = mar > 4.0;
            if (!smiling) setFaceStatus(`Tersenyum lebih lebar! (MAR: ${mar.toFixed(1)})`, false);
            else setFaceStatus('Senyum terdeteksi! Tahan...', true);
            return smiling;
        }

        return true; // fallback
    }

    // ============================================
    // COUNTDOWN RING
    // ============================================
    function showCountdownRing() {
        const ring = document.getElementById('autoCaptureIndicator');
        const circle = document.getElementById('countdownCircle');
        ring.style.display = 'block';
        circle.style.transition = 'none';
        circle.style.strokeDashoffset = '220';
        requestAnimationFrame(() => {
            circle.style.transition = `stroke-dashoffset ${STABLE_DURATION_MS}ms linear`;
            circle.style.strokeDashoffset = '0';
        });
        if (currentStep >= 0 && currentStep < totalSteps)
            document.getElementById('step-' + currentStep).classList.add('capturing');
    }

    function hideCountdownRing() {
        document.getElementById('autoCaptureIndicator').style.display = 'none';
        const c = document.getElementById('countdownCircle');
        c.style.transition = 'none';
        c.style.strokeDashoffset = '220';
        if (currentStep >= 0 && currentStep < totalSteps)
            document.getElementById('step-' + currentStep).classList.remove('capturing');
    }

    // ============================================
    // BLINK DETECTION
    // ============================================
    function detectBlink(landmarks) {
        const leftEye = landmarks.getLeftEye(), rightEye = landmarks.getRightEye();
        const rawEar = (eyeAspectRatio(leftEye) + eyeAspectRatio(rightEye)) / 2;

        // Smoothing: average last 3 frames to reduce noise from tiny model
        earHistory.push(rawEar);
        if (earHistory.length > 3) earHistory.shift();
        const ear = earHistory.reduce((a, b) => a + b, 0) / earHistory.length;

        // Show live EAR for feedback
        const threshold = 0.26;
        setFaceStatus(`Kedipkan mata! EAR: ${ear.toFixed(3)} ${ear < threshold ? '👁️ TERTUTUP' : '👀 Terbuka'} (${blinkCount}/1)`, true);

        // State machine: open → closed → open = 1 blink
        if (ear < threshold && !eyeWasClosed) {
            eyeWasClosed = true;
        } else if (ear > threshold + 0.03 && eyeWasClosed) {
            eyeWasClosed = false;
            blinkCount++;
            document.getElementById('stepText').textContent = `Kedip terdeteksi! (${blinkCount}/1)`;
            if (blinkCount >= 1 && !autoCapturing) {
                autoCapturing = true;
                doCapture().then(() => { autoCapturing = false; });
            }
        }
        lastEAR = ear;
    }

    function eyeAspectRatio(eye) {
        const d = (a, b) => Math.sqrt((a.x-b.x)**2 + (a.y-b.y)**2);
        return (d(eye[1],eye[5]) + d(eye[2],eye[4])) / (2 * d(eye[0],eye[3]));
    }

    // ============================================
    // CAPTURE
    // ============================================
    async function doCapture() {
        if (currentStep >= totalSteps) return;
        const video = document.getElementById('videoElement');
        const opts = new faceapi.TinyFaceDetectorOptions({ inputSize: 416, scoreThreshold: 0.5 });
        const det = await faceapi.detectSingleFace(video, opts).withFaceLandmarks(true).withFaceDescriptor();
        if (!det) { faceStableStart = null; hideCountdownRing(); return; }

        capturedDescriptors.push(Array.from(det.descriptor));
        capturedAngles.push(STEPS[currentStep].angle);

        const stepEl = document.getElementById('step-' + currentStep);
        stepEl.classList.remove('active', 'capturing');
        stepEl.classList.add('done');
        stepEl.querySelector('.step-check').style.display = 'block';
        hideCountdownRing();

        // Flash
        const video2 = document.getElementById('videoElement');
        video2.style.outline = '4px solid #00e676';
        setTimeout(() => { video2.style.outline = ''; }, 400);

        currentStep++;
        document.getElementById('progressBar').style.width = ((currentStep/totalSteps)*100)+'%';
        document.getElementById('progressText').textContent = `${currentStep} / ${totalSteps} selesai`;

        if (currentStep >= totalSteps) {
            document.getElementById('stepInstruction').style.display = 'none';
            setFaceStatus('Menyimpan...', true);
            await saveRegistration();
        } else {
            await new Promise(r => setTimeout(r, 500));
            updateStepUI();
        }
    }

    // ============================================
    // SAVE
    // ============================================
    async function saveRegistration() {
        const video = document.getElementById('videoElement');
        const c = document.createElement('canvas');
        c.width = 320; c.height = 240;
        c.getContext('2d').drawImage(video, 0, 0, 320, 240);

        try {
            const res = await fetch('{{ route("admin.absensi.face-register.store") }}', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
                body: JSON.stringify({
                    user_id: selectedUserId,
                    descriptors: capturedDescriptors,
                    angles: capturedAngles,
                    quality_score: capturedDescriptors.length * 20,
                    photo: c.toDataURL('image/jpeg', 0.8),
                }),
            });
            const result = await res.json();
            if (result.success) {
                document.getElementById('stepInstruction').innerHTML = '<i class="fas fa-check-circle mr-2"></i>Registrasi berhasil!';
                document.getElementById('stepInstruction').style.display = 'block';
                document.getElementById('stepInstruction').style.background = 'rgba(40,167,69,0.9)';
                document.getElementById('stepInstruction').style.color = '#fff';
                setFaceStatus('Tersimpan. Menunggu verifikasi admin.', true);
                setTimeout(() => { closeRegister(); window.location.reload(); }, 1500);
            } else {
                setFaceStatus('Gagal: ' + (result.message || 'Error'), false);
            }
        } catch (err) {
            setFaceStatus('Error: ' + err.message, false);
        }
    }

    // ============================================
    // RESET
    // ============================================
    function resetRegistration() {
        isDetecting = false;
        autoCapturing = false;
        capturedDescriptors = [];
        capturedAngles = [];
        currentStep = -1;
        blinkCount = 0;
        earHistory = [];
        eyeWasClosed = false;
        faceStableStart = null;
        resetUI();
        setTimeout(() => beginAutoRegistration(), 300);
    }
</script>
@stop
