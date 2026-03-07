@extends('adminlte::page')

@section('title', 'Registrasi Wajah')

@section('content_header')
    <h1><i class="fas fa-user-shield"></i> Registrasi Wajah</h1>
@stop

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-10">
        {{-- Status Card --}}
        @if($faceData && $faceData->is_active)
            <div class="alert alert-{{ $faceData->is_verified ? 'success' : 'warning' }}">
                <i class="fas fa-{{ $faceData->is_verified ? 'check-circle' : 'clock' }}"></i>
                @if($faceData->is_verified)
                    <strong>Wajah Anda sudah terdaftar dan terverifikasi.</strong>
                    Terakhir diperbarui: {{ $faceData->updated_at->diffForHumans() }}
                @else
                    <strong>Wajah Anda sudah terdaftar, menunggu verifikasi admin.</strong>
                    Didaftarkan: {{ $faceData->created_at->diffForHumans() }}
                @endif
                <button class="btn btn-sm btn-outline-dark float-right" onclick="startRegistration()">
                    <i class="fas fa-redo"></i> Daftar Ulang
                </button>
            </div>
        @endif

        <div class="card card-primary card-outline">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-camera"></i> Capture Wajah</h3>
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

                        {{-- Face detection indicator --}}
                        <div id="faceStatus" style="position:absolute; bottom:15px; left:50%; transform:translateX(-50%); background:rgba(0,0,0,0.7); color:#aaa; padding:8px 20px; border-radius:20px; font-size:0.9rem; z-index:5;">
                            <i class="fas fa-video"></i> Menunggu...
                        </div>
                    </div>

                    {{-- Steps Panel --}}
                    <div class="col-md-4" style="background:#f8f9fa;">
                        <div class="p-3">
                            <h5 class="mb-3"><i class="fas fa-list-ol"></i> Langkah Registrasi</h5>

                            <div class="step-list">
                                <div class="step-item" id="step-0" data-angle="frontal">
                                    <div class="d-flex align-items-center p-2 mb-2 rounded" style="background:#fff; border:2px solid #ddd;">
                                        <div class="step-number mr-3" style="width:30px; height:30px; border-radius:50%; background:#6c757d; color:#fff; display:flex; align-items:center; justify-content:center; font-weight:700;">1</div>
                                        <div>
                                            <strong>Wajah Depan</strong><br>
                                            <small class="text-muted">Lihat lurus ke kamera</small>
                                        </div>
                                        <div class="ml-auto step-check" style="display:none;"><i class="fas fa-check-circle text-success fa-lg"></i></div>
                                    </div>
                                </div>
                                <div class="step-item" id="step-1" data-angle="kanan">
                                    <div class="d-flex align-items-center p-2 mb-2 rounded" style="background:#fff; border:2px solid #ddd;">
                                        <div class="step-number mr-3" style="width:30px; height:30px; border-radius:50%; background:#6c757d; color:#fff; display:flex; align-items:center; justify-content:center; font-weight:700;">2</div>
                                        <div>
                                            <strong>Toleh Kanan</strong><br>
                                            <small class="text-muted">Putar kepala ke kanan</small>
                                        </div>
                                        <div class="ml-auto step-check" style="display:none;"><i class="fas fa-check-circle text-success fa-lg"></i></div>
                                    </div>
                                </div>
                                <div class="step-item" id="step-2" data-angle="kiri">
                                    <div class="d-flex align-items-center p-2 mb-2 rounded" style="background:#fff; border:2px solid #ddd;">
                                        <div class="step-number mr-3" style="width:30px; height:30px; border-radius:50%; background:#6c757d; color:#fff; display:flex; align-items:center; justify-content:center; font-weight:700;">3</div>
                                        <div>
                                            <strong>Toleh Kiri</strong><br>
                                            <small class="text-muted">Putar kepala ke kiri</small>
                                        </div>
                                        <div class="ml-auto step-check" style="display:none;"><i class="fas fa-check-circle text-success fa-lg"></i></div>
                                    </div>
                                </div>
                                <div class="step-item" id="step-3" data-angle="senyum">
                                    <div class="d-flex align-items-center p-2 mb-2 rounded" style="background:#fff; border:2px solid #ddd;">
                                        <div class="step-number mr-3" style="width:30px; height:30px; border-radius:50%; background:#6c757d; color:#fff; display:flex; align-items:center; justify-content:center; font-weight:700;">4</div>
                                        <div>
                                            <strong>Senyum</strong><br>
                                            <small class="text-muted">Tersenyum natural</small>
                                        </div>
                                        <div class="ml-auto step-check" style="display:none;"><i class="fas fa-check-circle text-success fa-lg"></i></div>
                                    </div>
                                </div>
                                <div class="step-item" id="step-4" data-angle="kedip">
                                    <div class="d-flex align-items-center p-2 mb-2 rounded" style="background:#fff; border:2px solid #ddd;">
                                        <div class="step-number mr-3" style="width:30px; height:30px; border-radius:50%; background:#6c757d; color:#fff; display:flex; align-items:center; justify-content:center; font-weight:700;">5</div>
                                        <div>
                                            <strong>Kedipkan Mata</strong><br>
                                            <small class="text-muted">Kedip 2 kali (liveness)</small>
                                        </div>
                                        <div class="ml-auto step-check" style="display:none;"><i class="fas fa-check-circle text-success fa-lg"></i></div>
                                    </div>
                                </div>
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
                                <button class="btn btn-primary btn-block" id="btnStart" onclick="startRegistration()">
                                    <i class="fas fa-play"></i> Mulai Registrasi
                                </button>
                                <button class="btn btn-warning btn-block d-none" id="btnCapture" onclick="captureStep()">
                                    <i class="fas fa-camera"></i> Capture <span id="btnCaptureStep"></span>
                                </button>
                                <button class="btn btn-success btn-block d-none" id="btnSave" onclick="saveRegistration()">
                                    <i class="fas fa-save"></i> Simpan Data Wajah
                                </button>
                                <button class="btn btn-secondary btn-block mt-2 d-none" id="btnReset" onclick="resetRegistration()">
                                    <i class="fas fa-redo"></i> Ulangi
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
                <li>Pastikan pencahayaan cukup dan wajah terlihat jelas</li>
                <li>Lepas kacamata hitam, masker, atau penutup wajah</li>
                <li>Ikuti instruksi setiap langkah (frontal, kanan, kiri, senyum, kedip)</li>
                <li>Setelah semua langkah selesai, klik <strong>Simpan</strong></li>
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
    .step-item.active .step-number { background: #007bff !important; }
</style>
@stop

@section('js')
<script src="{{ asset('vendor/face-api/face-api.min.js') }}"></script>
<script>
    let currentStep = -1;
    let totalSteps = 5;
    let capturedDescriptors = [];
    let capturedAngles = [];
    let isDetecting = false;
    let blinkCount = 0;
    let lastEAR = 1; // Eye Aspect Ratio for blink detection

    const STEPS = [
        { angle: 'frontal', text: 'Lihat lurus ke kamera', icon: 'fa-user' },
        { angle: 'kanan', text: 'Putar kepala ke KANAN', icon: 'fa-arrow-right' },
        { angle: 'kiri', text: 'Putar kepala ke KIRI', icon: 'fa-arrow-left' },
        { angle: 'senyum', text: 'Tersenyum!', icon: 'fa-smile' },
        { angle: 'kedip', text: 'Kedipkan mata 2 kali', icon: 'fa-eye' },
    ];

    // ============================================
    // LOAD MODELS
    // ============================================
    async function loadModels() {
        const MODEL_URL = '{{ asset("vendor/face-api/models") }}';
        
        setLoadingText('Memuat model deteksi wajah...');
        await faceapi.nets.tinyFaceDetector.loadFromUri(MODEL_URL);
        
        setLoadingText('Memuat model landmark wajah...');
        await faceapi.nets.faceLandmark68TinyNet.loadFromUri(MODEL_URL);
        
        setLoadingText('Memuat model pengenalan wajah...');
        await faceapi.nets.faceRecognitionNet.loadFromUri(MODEL_URL);

        setLoadingText('Menyalakan kamera...');
        await initCamera();
        
        document.getElementById('loadingOverlay').style.display = 'none';
    }

    function setLoadingText(text) {
        document.getElementById('loadingText').textContent = text;
    }

    async function initCamera() {
        const video = document.getElementById('videoElement');
        try {
            const stream = await navigator.mediaDevices.getUserMedia({
                video: { width: { ideal: 1280 }, height: { ideal: 720 }, facingMode: 'user' }
            });
            video.srcObject = stream;
        } catch (err) {
            alert('Kamera tidak tersedia. Pastikan browser memiliki izin akses kamera.');
        }
    }

    // ============================================
    // REGISTRATION FLOW
    // ============================================
    function startRegistration() {
        capturedDescriptors = [];
        capturedAngles = [];
        currentStep = 0;
        blinkCount = 0;

        // Reset UI
        document.querySelectorAll('.step-item').forEach(el => {
            el.classList.remove('active', 'done');
            el.querySelector('.step-check').style.display = 'none';
        });
        document.getElementById('progressBar').style.width = '0%';
        document.getElementById('progressText').textContent = '0 / 5 selesai';

        document.getElementById('btnStart').classList.add('d-none');
        document.getElementById('btnCapture').classList.remove('d-none');
        document.getElementById('btnReset').classList.remove('d-none');
        document.getElementById('btnSave').classList.add('d-none');

        updateStepUI();
        startDetectionLoop();
    }

    function updateStepUI() {
        if (currentStep >= totalSteps) return;

        document.querySelectorAll('.step-item').forEach((el, i) => {
            if (i < currentStep) {
                el.classList.add('done');
                el.classList.remove('active');
            } else if (i === currentStep) {
                el.classList.add('active');
                el.classList.remove('done');
            } else {
                el.classList.remove('active', 'done');
            }
        });

        const step = STEPS[currentStep];
        document.getElementById('stepInstruction').style.display = 'block';
        document.getElementById('stepIcon').className = 'fas ' + step.icon + ' mr-2';
        document.getElementById('stepText').textContent = step.text;
        document.getElementById('btnCaptureStep').textContent = `(Step ${currentStep + 1})`;

        // Auto-capture for blink step
        if (step.angle === 'kedip') {
            document.getElementById('btnCapture').disabled = true;
            document.getElementById('btnCapture').innerHTML = '<i class="fas fa-eye"></i> Menunggu kedipan...';
            blinkCount = 0;
        } else {
            document.getElementById('btnCapture').disabled = false;
            document.getElementById('btnCapture').innerHTML = `<i class="fas fa-camera"></i> Capture (Step ${currentStep + 1})`;
        }
    }

    // ============================================
    // DETECTION LOOP (CONTINUOUS TRACKING)
    // ============================================
    function startDetectionLoop() {
        isDetecting = true;
        const video = document.getElementById('videoElement');
        const canvas = document.getElementById('overlayCanvas');
        const ctx = canvas.getContext('2d');

        const options = new faceapi.TinyFaceDetectorOptions({ inputSize: 320, scoreThreshold: 0.5 });

        async function detect() {
            if (!isDetecting || video.paused) return;

            canvas.width = video.videoWidth || video.clientWidth;
            canvas.height = video.videoHeight || video.clientHeight;

            const detection = await faceapi
                .detectSingleFace(video, options)
                .withFaceLandmarks(true);

            ctx.clearRect(0, 0, canvas.width, canvas.height);

            if (detection) {
                const dims = faceapi.matchDimensions(canvas, video, true);
                const resized = faceapi.resizeResults(detection, dims);

                // Draw landmarks
                const positions = resized.landmarks.positions;
                positions.forEach(pt => {
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

                setFaceStatus('Wajah terdeteksi', true);

                // Blink detection for step 4
                if (currentStep === 4) {
                    detectBlink(detection.landmarks);
                }
            } else {
                setFaceStatus('Arahkan wajah ke kamera', false);
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
    // BLINK DETECTION (Liveness)
    // ============================================
    function detectBlink(landmarks) {
        // Eye Aspect Ratio (EAR) based blink detection
        const leftEye = landmarks.getLeftEye();
        const rightEye = landmarks.getRightEye();

        const earLeft = eyeAspectRatio(leftEye);
        const earRight = eyeAspectRatio(rightEye);
        const ear = (earLeft + earRight) / 2;

        const EAR_THRESHOLD = 0.21;

        if (lastEAR > EAR_THRESHOLD && ear < EAR_THRESHOLD) {
            blinkCount++;
            document.getElementById('stepText').textContent = `Kedipkan mata (${blinkCount}/2)`;
            
            if (blinkCount >= 2) {
                // Auto capture after blinks detected
                captureStep();
            }
        }
        lastEAR = ear;
    }

    function eyeAspectRatio(eye) {
        // EAR = (|p2-p6| + |p3-p5|) / (2 * |p1-p4|)
        const dist = (a, b) => Math.sqrt(Math.pow(a.x - b.x, 2) + Math.pow(a.y - b.y, 2));
        const A = dist(eye[1], eye[5]);
        const B = dist(eye[2], eye[4]);
        const C = dist(eye[0], eye[3]);
        return (A + B) / (2.0 * C);
    }

    // ============================================
    // CAPTURE STEP
    // ============================================
    async function captureStep() {
        if (currentStep >= totalSteps) return;

        const video = document.getElementById('videoElement');
        const options = new faceapi.TinyFaceDetectorOptions({ inputSize: 416, scoreThreshold: 0.5 });

        const detection = await faceapi
            .detectSingleFace(video, options)
            .withFaceLandmarks(true)
            .withFaceDescriptor();

        if (!detection) {
            alert('Wajah tidak terdeteksi. Pastikan wajah terlihat jelas.');
            return;
        }

        // Save descriptor
        capturedDescriptors.push(Array.from(detection.descriptor));
        capturedAngles.push(STEPS[currentStep].angle);

        // Mark step done
        const stepEl = document.getElementById('step-' + currentStep);
        stepEl.classList.add('done');
        stepEl.classList.remove('active');
        stepEl.querySelector('.step-check').style.display = 'block';

        // Update progress
        currentStep++;
        const progress = (currentStep / totalSteps) * 100;
        document.getElementById('progressBar').style.width = progress + '%';
        document.getElementById('progressText').textContent = `${currentStep} / ${totalSteps} selesai`;

        if (currentStep >= totalSteps) {
            // All done!
            document.getElementById('stepInstruction').style.display = 'none';
            document.getElementById('btnCapture').classList.add('d-none');
            document.getElementById('btnSave').classList.remove('d-none');
            setFaceStatus('Semua langkah selesai! Klik Simpan.', true);
        } else {
            updateStepUI();
        }
    }

    // ============================================
    // SAVE REGISTRATION
    // ============================================
    async function saveRegistration() {
        const btn = document.getElementById('btnSave');
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Menyimpan...';

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
                    descriptors: capturedDescriptors,
                    angles: capturedAngles,
                    quality_score: capturedDescriptors.length * 20, // simple score
                    photo: photoData,
                }),
            });

            const result = await response.json();

            if (result.success) {
                alert('Data wajah berhasil disimpan! Menunggu verifikasi admin.');
                window.location.reload();
            } else {
                alert('Gagal menyimpan: ' + (result.message || 'Unknown error'));
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-save"></i> Simpan Data Wajah';
            }
        } catch (err) {
            alert('Error: ' + err.message);
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-save"></i> Simpan Data Wajah';
        }
    }

    // ============================================
    // RESET
    // ============================================
    function resetRegistration() {
        isDetecting = false;
        capturedDescriptors = [];
        capturedAngles = [];
        currentStep = -1;
        blinkCount = 0;

        document.querySelectorAll('.step-item').forEach(el => {
            el.classList.remove('active', 'done');
            el.querySelector('.step-check').style.display = 'none';
        });
        document.getElementById('progressBar').style.width = '0%';
        document.getElementById('progressText').textContent = '0 / 5 selesai';
        document.getElementById('stepInstruction').style.display = 'none';

        document.getElementById('btnStart').classList.remove('d-none');
        document.getElementById('btnCapture').classList.add('d-none');
        document.getElementById('btnSave').classList.add('d-none');
        document.getElementById('btnReset').classList.add('d-none');

        const canvas = document.getElementById('overlayCanvas');
        canvas.getContext('2d').clearRect(0, 0, canvas.width, canvas.height);
    }

    // ============================================
    // INIT
    // ============================================
    document.addEventListener('DOMContentLoaded', loadModels);
</script>
@stop
