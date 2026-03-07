@extends('adminlte::page')

@section('title', 'Registrasi Wajah')

@section('content_header')
    <h1><i class="fas fa-user-shield"></i> Registrasi Wajah</h1>
@stop

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-10">

        {{-- Step 1: Pilih User --}}
        <div class="card card-primary card-outline" id="cardSelectUser">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-user-check"></i> Pilih GTK untuk Registrasi Wajah</h3>
            </div>
            <div class="card-body">
                <div class="form-group">
                    <label for="selectGtk">Nama GTK <span class="text-danger">*</span></label>
                    <select class="form-control select2" id="selectGtk" style="width:100%;">
                        <option value="">-- Pilih GTK --</option>
                        @foreach($gtkList as $gtk)
                            <option value="{{ $gtk->user_id }}"
                                    data-nama="{{ $gtk->nama_lengkap }}"
                                    data-nip="{{ $gtk->nip }}"
                                    data-registered="{{ in_array($gtk->user_id, $registeredFaces) ? '1' : '0' }}">
                                {{ $gtk->nama_lengkap }} {{ $gtk->nip ? '('.$gtk->nip.')' : '' }}
                                @if(in_array($gtk->user_id, $registeredFaces)) ✓ Sudah Terdaftar @endif
                            </option>
                        @endforeach
                    </select>
                </div>
                <div id="selectedUserInfo" class="alert alert-info d-none">
                    <i class="fas fa-info-circle"></i>
                    <span id="selectedUserText"></span>
                </div>
                <button class="btn btn-primary" id="btnStartCamera" disabled onclick="startCamera()">
                    <i class="fas fa-camera"></i> Mulai Registrasi Wajah
                </button>
            </div>
        </div>

        {{-- Step 2: Camera & Registration (hidden until user selected) --}}
        <div class="card card-primary card-outline d-none" id="cardCamera">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-camera"></i> Capture Wajah — <span id="headerUserName"></span></h3>
                <div class="card-tools">
                    <button class="btn btn-sm btn-default" onclick="backToSelect()"><i class="fas fa-arrow-left"></i> Ganti User</button>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="row no-gutters">
                    {{-- Camera Area --}}
                    <div class="col-md-8 position-relative" style="background:#000; min-height:480px;">
                        <div id="loadingOverlay" style="position:absolute; inset:0; background:rgba(0,0,0,0.8); display:flex; flex-direction:column; align-items:center; justify-content:center; z-index:10;">
                            <div class="spinner-border text-info mb-3" role="status"></div>
                            <p class="text-white" id="loadingText">Memuat model face detection...</p>
                        </div>

                        <video id="videoElement" autoplay playsinline style="width:100%; height:100%; object-fit:cover; transform:scaleX(-1);"></video>
                        <canvas id="overlayCanvas" style="position:absolute; top:0; left:0; width:100%; height:100%; transform:scaleX(-1);"></canvas>

                        {{-- Step instruction overlay --}}
                        <div id="stepInstruction" style="position:absolute; top:15px; left:50%; transform:translateX(-50%); background:rgba(0,0,0,0.7); color:#00e5ff; padding:10px 25px; border-radius:25px; font-size:1.1rem; font-weight:600; text-align:center; z-index:5; display:none;">
                            <i class="fas fa-arrow-right" id="stepIcon"></i>
                            <span id="stepText">Lihat ke kamera</span>
                        </div>

                        {{-- Auto-capture countdown ring --}}
                        <div id="autoCaptureIndicator" style="position:absolute; top:50%; left:50%; transform:translate(-50%,-50%); z-index:6; display:none;">
                            <svg width="80" height="80" viewBox="0 0 80 80">
                                <circle cx="40" cy="40" r="35" stroke="#333" stroke-width="4" fill="none" opacity="0.3"/>
                                <circle id="countdownCircle" cx="40" cy="40" r="35" stroke="#00e676" stroke-width="4" fill="none"
                                    stroke-dasharray="220" stroke-dashoffset="220" stroke-linecap="round"
                                    style="transition: stroke-dashoffset 1.5s linear; transform: rotate(-90deg); transform-origin: center;"/>
                            </svg>
                        </div>

                        {{-- Face detection indicator --}}
                        <div id="faceStatus" style="position:absolute; bottom:15px; left:50%; transform:translateX(-50%); background:rgba(0,0,0,0.7); color:#aaa; padding:8px 20px; border-radius:20px; font-size:0.9rem; z-index:5;">
                            <i class="fas fa-video"></i> Menunggu...
                        </div>
                    </div>

                    {{-- Steps Panel --}}
                    <div class="col-md-4" style="background:#f8f9fa;">
                        <div class="p-3">
                            <h5 class="mb-3"><i class="fas fa-list-ol"></i> Langkah Registrasi</h5>
                            <p class="text-muted small mb-2"><i class="fas fa-magic"></i> Capture otomatis saat wajah stabil terdeteksi</p>

                            <div class="step-list">
                                @php
                                $steps = [
                                    ['Wajah Depan', 'Lihat lurus ke kamera', 'frontal'],
                                    ['Toleh Kanan', 'Putar kepala ke kanan', 'kanan'],
                                    ['Toleh Kiri', 'Putar kepala ke kiri', 'kiri'],
                                    ['Senyum', 'Tersenyum natural', 'senyum'],
                                    ['Kedipkan Mata', 'Kedip 2 kali (liveness)', 'kedip'],
                                ];
                                @endphp
                                @foreach($steps as $i => [$title, $desc, $angle])
                                <div class="step-item" id="step-{{ $i }}" data-angle="{{ $angle }}">
                                    <div class="d-flex align-items-center p-2 mb-2 rounded" style="background:#fff; border:2px solid #ddd;">
                                        <div class="step-number mr-3" style="width:30px; height:30px; border-radius:50%; background:#6c757d; color:#fff; display:flex; align-items:center; justify-content:center; font-weight:700;">{{ $i+1 }}</div>
                                        <div>
                                            <strong>{{ $title }}</strong><br>
                                            <small class="text-muted">{{ $desc }}</small>
                                        </div>
                                        <div class="ml-auto step-check" style="display:none;"><i class="fas fa-check-circle text-success fa-lg"></i></div>
                                    </div>
                                </div>
                                @endforeach
                            </div>

                            {{-- Progress --}}
                            <div class="mt-3">
                                <div class="progress" style="height:8px;">
                                    <div class="progress-bar bg-success" id="progressBar" style="width:0%"></div>
                                </div>
                                <small class="text-muted" id="progressText">0 / 5 selesai</small>
                            </div>

                            {{-- Buttons --}}
                            <div class="mt-3">
                                <button class="btn btn-secondary btn-block d-none" id="btnReset" onclick="resetRegistration()">
                                    <i class="fas fa-redo"></i> Ulangi dari Awal
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Instructions --}}
        <div class="callout callout-info">
            <h5><i class="fas fa-info-circle"></i> Petunjuk</h5>
            <ul class="mb-0">
                <li>Pilih nama GTK terlebih dahulu, kemudian klik <strong>Mulai Registrasi Wajah</strong></li>
                <li>Pastikan pencahayaan cukup dan wajah terlihat jelas</li>
                <li>Lepas kacamata hitam, masker, atau penutup wajah</li>
                <li>Setiap langkah <strong>otomatis tercapture</strong> saat wajah stabil terdeteksi (~1.5 detik)</li>
                <li>Langkah terakhir memerlukan kedipan mata 2x sebagai liveness check</li>
                <li>Data wajah akan diverifikasi oleh admin sebelum bisa digunakan untuk absensi</li>
            </ul>
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
    .select2-container--default .select2-selection--single { height: 38px !important; }
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
    let cameraReady = false;

    // Auto-capture state
    let faceStableStart = null;        // timestamp when face first detected stably
    let autoCapturing = false;         // currently doing an auto-capture
    const STABLE_DURATION_MS = 1500;   // hold face 1.5s to auto-capture

    // Blink detection
    let blinkCount = 0;
    let lastEAR = 1;

    const STEPS = [
        { angle: 'frontal', text: 'Lihat lurus ke kamera', icon: 'fa-user' },
        { angle: 'kanan', text: 'Putar kepala ke KANAN', icon: 'fa-arrow-right' },
        { angle: 'kiri', text: 'Putar kepala ke KIRI', icon: 'fa-arrow-left' },
        { angle: 'senyum', text: 'Tersenyum natural', icon: 'fa-smile' },
        { angle: 'kedip', text: 'Kedipkan mata 2 kali', icon: 'fa-eye' },
    ];

    // ============================================
    // USER SELECTION
    // ============================================
    $(function() {
        $('#selectGtk').select2({ placeholder: '-- Pilih GTK --', allowClear: true });
        $('#selectGtk').on('change', function() {
            const opt = $(this).find(':selected');
            selectedUserId = $(this).val();
            if (selectedUserId) {
                selectedUserName = opt.data('nama');
                const nip = opt.data('nip');
                const registered = opt.data('registered') === '1';
                let info = `<strong>${selectedUserName}</strong>`;
                if (nip) info += ` — NIP: ${nip}`;
                if (registered) info += '<br><span class="text-warning"><i class="fas fa-exclamation-triangle"></i> Sudah pernah registrasi. Data lama akan ditimpa.</span>';
                $('#selectedUserInfo').removeClass('d-none').html('<i class="fas fa-info-circle"></i> ' + info);
                $('#btnStartCamera').prop('disabled', false);
            } else {
                selectedUserId = null;
                $('#selectedUserInfo').addClass('d-none');
                $('#btnStartCamera').prop('disabled', true);
            }
        });
    });

    function startCamera() {
        if (!selectedUserId) return;
        $('#cardSelectUser').addClass('d-none');
        $('#cardCamera').removeClass('d-none');
        $('#headerUserName').text(selectedUserName);

        if (!modelsLoaded) {
            loadModels();
        } else if (cameraReady) {
            beginAutoRegistration();
        }
    }

    function backToSelect() {
        isDetecting = false;
        resetRegistration();
        $('#cardCamera').addClass('d-none');
        $('#cardSelectUser').removeClass('d-none');
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

            setLoadingText('Menyalakan kamera...');
            await initCamera();
            cameraReady = true;
            document.getElementById('loadingOverlay').style.display = 'none';

            beginAutoRegistration();
        } catch (err) {
            setLoadingText('Error: ' + err.message);
        }
    }

    function setLoadingText(text) {
        document.getElementById('loadingText').textContent = text;
    }

    async function initCamera() {
        const video = document.getElementById('videoElement');
        const stream = await navigator.mediaDevices.getUserMedia({
            video: { width: { ideal: 1280 }, height: { ideal: 720 }, facingMode: 'user' }
        });
        video.srcObject = stream;
        await new Promise(r => { video.onloadedmetadata = r; });
    }

    // ============================================
    // AUTO REGISTRATION FLOW
    // ============================================
    function beginAutoRegistration() {
        capturedDescriptors = [];
        capturedAngles = [];
        currentStep = 0;
        blinkCount = 0;
        faceStableStart = null;
        autoCapturing = false;

        // Reset step UI
        document.querySelectorAll('.step-item').forEach(el => {
            el.classList.remove('active', 'done', 'capturing');
            el.querySelector('.step-check').style.display = 'none';
        });
        document.getElementById('progressBar').style.width = '0%';
        document.getElementById('progressText').textContent = '0 / 5 selesai';
        document.getElementById('btnReset').classList.remove('d-none');

        updateStepUI();
        startDetectionLoop();
    }

    function updateStepUI() {
        if (currentStep >= totalSteps) return;

        document.querySelectorAll('.step-item').forEach((el, i) => {
            el.classList.remove('active', 'capturing');
            if (i < currentStep) {
                el.classList.add('done');
            } else if (i === currentStep) {
                el.classList.add('active');
            }
        });

        const step = STEPS[currentStep];
        document.getElementById('stepInstruction').style.display = 'block';
        document.getElementById('stepIcon').className = 'fas ' + step.icon + ' mr-2';
        document.getElementById('stepText').textContent = step.text;

        if (step.angle === 'kedip') {
            blinkCount = 0;
        }

        // Reset stable timer for new step
        faceStableStart = null;
        hideCountdownRing();
    }

    // ============================================
    // DETECTION LOOP (CONTINUOUS + AUTO-CAPTURE)
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

                // Draw landmarks
                resized.landmarks.positions.forEach(pt => {
                    ctx.beginPath();
                    ctx.arc(pt.x, pt.y, 2, 0, 2 * Math.PI);
                    ctx.fillStyle = '#00e5ff';
                    ctx.globalAlpha = 0.7;
                    ctx.fill();
                });

                // Draw box
                ctx.globalAlpha = 1;
                const box = resized.detection.box;
                ctx.strokeStyle = '#00e5ff';
                ctx.lineWidth = 2;
                ctx.strokeRect(box.x, box.y, box.width, box.height);

                const score = detection.detection.score;
                setFaceStatus(`Wajah terdeteksi (${(score*100).toFixed(0)}%)`, true);

                if (currentStep < totalSteps && !autoCapturing) {
                    if (STEPS[currentStep].angle === 'kedip') {
                        // Blink detection for last step
                        detectBlink(detection.landmarks);
                    } else {
                        // Auto-capture: start stable timer
                        if (!faceStableStart) {
                            faceStableStart = Date.now();
                            showCountdownRing();
                        } else if (Date.now() - faceStableStart >= STABLE_DURATION_MS) {
                            // Face has been stable long enough → auto capture
                            autoCapturing = true;
                            await doCapture();
                            autoCapturing = false;
                        }
                    }
                }
            } else {
                setFaceStatus('Arahkan wajah ke kamera', false);
                // Face lost → reset stable timer
                if (faceStableStart) {
                    faceStableStart = null;
                    hideCountdownRing();
                }
            }

            requestAnimationFrame(() => setTimeout(detect, 150));
        }

        detect();
    }

    function setFaceStatus(text, detected) {
        const el = document.getElementById('faceStatus');
        el.style.color = detected ? '#00e676' : '#ff5252';
        el.innerHTML = `<i class="fas fa-${detected ? 'check-circle' : 'exclamation-circle'}"></i> ${text}`;
    }

    // ============================================
    // COUNTDOWN RING (visual feedback for auto-capture)
    // ============================================
    function showCountdownRing() {
        const ring = document.getElementById('autoCaptureIndicator');
        const circle = document.getElementById('countdownCircle');
        ring.style.display = 'block';
        // Reset then animate
        circle.style.transition = 'none';
        circle.style.strokeDashoffset = '220';
        requestAnimationFrame(() => {
            circle.style.transition = `stroke-dashoffset ${STABLE_DURATION_MS}ms linear`;
            circle.style.strokeDashoffset = '0';
        });
        // Highlight step as capturing
        if (currentStep >= 0 && currentStep < totalSteps) {
            document.getElementById('step-' + currentStep).classList.add('capturing');
        }
    }

    function hideCountdownRing() {
        document.getElementById('autoCaptureIndicator').style.display = 'none';
        const circle = document.getElementById('countdownCircle');
        circle.style.transition = 'none';
        circle.style.strokeDashoffset = '220';
        if (currentStep >= 0 && currentStep < totalSteps) {
            document.getElementById('step-' + currentStep).classList.remove('capturing');
        }
    }

    // ============================================
    // BLINK DETECTION (Liveness - step 5)
    // ============================================
    function detectBlink(landmarks) {
        const leftEye = landmarks.getLeftEye();
        const rightEye = landmarks.getRightEye();
        const earLeft = eyeAspectRatio(leftEye);
        const earRight = eyeAspectRatio(rightEye);
        const ear = (earLeft + earRight) / 2;
        const EAR_THRESHOLD = 0.21;

        if (lastEAR > EAR_THRESHOLD && ear < EAR_THRESHOLD) {
            blinkCount++;
            document.getElementById('stepText').textContent = `Kedipkan mata (${blinkCount}/2)`;
            if (blinkCount >= 2 && !autoCapturing) {
                autoCapturing = true;
                doCapture().then(() => { autoCapturing = false; });
            }
        }
        lastEAR = ear;
    }

    function eyeAspectRatio(eye) {
        const dist = (a, b) => Math.sqrt(Math.pow(a.x - b.x, 2) + Math.pow(a.y - b.y, 2));
        const A = dist(eye[1], eye[5]);
        const B = dist(eye[2], eye[4]);
        const C = dist(eye[0], eye[3]);
        return (A + B) / (2.0 * C);
    }

    // ============================================
    // CAPTURE (shared by auto + blink)
    // ============================================
    async function doCapture() {
        if (currentStep >= totalSteps) return;

        const video = document.getElementById('videoElement');
        const options = new faceapi.TinyFaceDetectorOptions({ inputSize: 416, scoreThreshold: 0.5 });

        const detection = await faceapi
            .detectSingleFace(video, options)
            .withFaceLandmarks(true)
            .withFaceDescriptor();

        if (!detection) {
            // Failed to extract descriptor → reset stable timer, try again
            faceStableStart = null;
            hideCountdownRing();
            return;
        }

        // Save descriptor
        capturedDescriptors.push(Array.from(detection.descriptor));
        capturedAngles.push(STEPS[currentStep].angle);

        // Mark step done
        const stepEl = document.getElementById('step-' + currentStep);
        stepEl.classList.remove('active', 'capturing');
        stepEl.classList.add('done');
        stepEl.querySelector('.step-check').style.display = 'block';
        hideCountdownRing();

        // Flash feedback
        flashGreen();

        // Advance
        currentStep++;
        const progress = (currentStep / totalSteps) * 100;
        document.getElementById('progressBar').style.width = progress + '%';
        document.getElementById('progressText').textContent = `${currentStep} / ${totalSteps} selesai`;

        if (currentStep >= totalSteps) {
            // All done → auto-save
            document.getElementById('stepInstruction').style.display = 'none';
            setFaceStatus('Semua langkah selesai! Menyimpan...', true);
            await saveRegistration();
        } else {
            // Brief pause so user can see the checkmark
            await new Promise(r => setTimeout(r, 600));
            updateStepUI();
        }
    }

    function flashGreen() {
        const video = document.getElementById('videoElement');
        video.style.boxShadow = 'inset 0 0 60px rgba(0,230,118,0.5)';
        video.style.outline = '4px solid #00e676';
        setTimeout(() => { video.style.boxShadow = ''; video.style.outline = ''; }, 500);
    }

    // ============================================
    // SAVE REGISTRATION
    // ============================================
    async function saveRegistration() {
        // Capture thumbnail photo
        const video = document.getElementById('videoElement');
        const tmpCanvas = document.createElement('canvas');
        tmpCanvas.width = 320;
        tmpCanvas.height = 240;
        tmpCanvas.getContext('2d').drawImage(video, 0, 0, 320, 240);
        const photoData = tmpCanvas.toDataURL('image/jpeg', 0.8);

        try {
            const response = await fetch('{{ route("admin.absensi.face-register.store") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                },
                body: JSON.stringify({
                    user_id: selectedUserId,
                    descriptors: capturedDescriptors,
                    angles: capturedAngles,
                    quality_score: capturedDescriptors.length * 20,
                    photo: photoData,
                }),
            });

            const result = await response.json();

            if (result.success) {
                setFaceStatus('Berhasil disimpan! Menunggu verifikasi admin.', true);
                document.getElementById('stepInstruction').innerHTML = '<i class="fas fa-check-circle text-success mr-2"></i><span class="text-white">Registrasi berhasil!</span>';
                document.getElementById('stepInstruction').style.display = 'block';
                document.getElementById('stepInstruction').style.background = 'rgba(40,167,69,0.85)';

                // Auto reload after 2s
                setTimeout(() => window.location.reload(), 2000);
            } else {
                setFaceStatus('Gagal menyimpan: ' + (result.message || 'Error'), false);
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
        faceStableStart = null;

        document.querySelectorAll('.step-item').forEach(el => {
            el.classList.remove('active', 'done', 'capturing');
            el.querySelector('.step-check').style.display = 'none';
        });
        document.getElementById('progressBar').style.width = '0%';
        document.getElementById('progressText').textContent = '0 / 5 selesai';
        document.getElementById('stepInstruction').style.display = 'none';
        document.getElementById('stepInstruction').style.background = 'rgba(0,0,0,0.7)';
        document.getElementById('btnReset').classList.add('d-none');
        hideCountdownRing();

        const canvas = document.getElementById('overlayCanvas');
        canvas.getContext('2d').clearRect(0, 0, canvas.width, canvas.height);

        if (cameraReady) {
            setTimeout(() => beginAutoRegistration(), 300);
        }
    }
</script>
@stop
