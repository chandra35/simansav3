@extends('adminlte::page')

@section('title', $pageTitle)
@section('plugins.Datatables', true)
@section('plugins.DatatablesPlugins', true)

@section('content_header')
    <h1><i class="fas fa-user-shield"></i> {{ $pageTitle }}</h1>
@stop

@section('content')
<div class="card border-0 shadow-sm face-register-hero mb-3">
    <div class="card-body p-3 p-md-4">
        <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between">
            <div class="pr-lg-4 mb-3 mb-lg-0">
                <div class="text-uppercase small font-weight-bold text-primary mb-1">Face Registration</div>
                <h2 class="h4 mb-2">{{ $pageTitle }}</h2>
                <p class="text-muted mb-0">
                    Gunakan pencahayaan yang cukup, posisikan wajah di tengah kamera, dan ikuti instruksi sampai semua langkah selesai.
                </p>
            </div>
            <div class="face-register-hero__tips">
                <span class="badge badge-light">Responsif semua device</span>
                <span class="badge badge-light">Auto capture</span>
                <span class="badge badge-light">Verifikasi admin</span>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-4 col-6">
        <div class="small-box bg-info">
            <div class="inner"><h3>{{ $registrants->count() }}</h3><p>Total {{ $subjectLabel }}</p></div>
            <div class="icon"><i class="fas fa-users"></i></div>
        </div>
    </div>
    <div class="col-lg-4 col-6">
        <div class="small-box bg-success">
            <div class="inner"><h3>{{ $registeredCount }}</h3><p>Sudah Registrasi</p></div>
            <div class="icon"><i class="fas fa-check-circle"></i></div>
        </div>
    </div>
    <div class="col-lg-4 col-12">
        <div class="small-box bg-warning">
            <div class="inner"><h3>{{ $pendingCount }}</h3><p>Menunggu Verifikasi</p></div>
            <div class="icon"><i class="fas fa-clock"></i></div>
        </div>
    </div>
</div>

@if($selfOnly)
    <div class="alert alert-info">
        <i class="fas fa-info-circle mr-1"></i>
        Halaman ini hanya untuk registrasi wajah akun Anda sendiri. Persetujuan tetap dilakukan oleh admin.
    </div>
@endif

<div class="row mb-3">
    <div class="col-lg-8">
        <div class="card card-outline card-info h-100">
            <div class="card-body py-3">
                <div class="d-flex align-items-start">
                    <div class="mr-3 text-info" style="font-size:1.5rem;">
                        <i class="fas fa-camera-retro"></i>
                    </div>
                    <div>
                        <div class="font-weight-bold mb-1">Panduan singkat registrasi</div>
                        <div class="text-muted small">
                            Pastikan kamera depan aktif, wajah terlihat penuh, dan jangan berpindah tempat saat countdown berjalan. Sistem akan mengambil beberapa sudut wajah secara otomatis.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-4 mt-3 mt-lg-0">
        <div class="card card-outline card-secondary h-100">
            <div class="card-body py-3">
                <div class="font-weight-bold mb-2">Checklist sebelum mulai</div>
                <div class="small text-muted face-register-checklist">
                    <div><i class="fas fa-check text-success mr-1"></i> Cahaya cukup terang</div>
                    <div><i class="fas fa-check text-success mr-1"></i> Kamera stabil</div>
                    <div><i class="fas fa-check text-success mr-1"></i> Wajah tanpa terpotong</div>
                    <div><i class="fas fa-check text-success mr-1"></i> Lepas penutup wajah</div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card card-primary card-outline">
    <div class="card-header d-flex flex-wrap align-items-center justify-content-between">
        <h3 class="card-title mb-0"><i class="fas fa-table"></i> Daftar {{ $subjectLabel }} & Status Registrasi Wajah</h3>
        @if($canManageAll)
            <div class="btn-group btn-group-sm mt-2 mt-md-0">
                @foreach($typeOptions as $typeKey => $typeName)
                    <a href="{{ route('admin.absensi.face-register', ['type' => $typeKey]) }}"
                       class="btn btn-{{ $selectedType === $typeKey ? 'primary' : 'outline-primary' }}">
                        {{ $typeName }}
                    </a>
                @endforeach
            </div>
        @endif
    </div>
    <div class="card-body">
        @if($canManageAll && $registrants->count() > 1)
            <div class="mb-3 d-flex flex-wrap align-items-center">
                <span class="mr-2 font-weight-bold"><i class="fas fa-filter"></i> Filter:</span>
                <div class="btn-group" id="statusFilter">
                    <button class="btn btn-sm btn-outline-primary active" data-filter="">Semua</button>
                    <button class="btn btn-sm btn-outline-secondary" data-filter="Belum">Belum Daftar</button>
                    <button class="btn btn-sm btn-outline-warning" data-filter="Pending">Pending</button>
                    <button class="btn btn-sm btn-outline-success" data-filter="Verified">Verified</button>
                </div>
            </div>
        @endif

        <div class="table-responsive">
            <table class="table table-hover table-striped table-sm" id="tabelFaceRegister">
                <thead>
                    <tr>
                        <th width="40">No</th>
                        <th>Nama {{ $subjectLabel }}</th>
                        <th>{{ $identifierLabel }}</th>
                        <th>Status</th>
                        <th>Capture</th>
                        <th>Quality</th>
                        <th>Verifikasi</th>
                        <th>Tgl Registrasi</th>
                        <th width="130">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($registrants as $i => $registrant)
                        @php $face = $faceMap[$registrant['user_id']] ?? null; @endphp
                        <tr>
                            <td data-label="No">{{ $i + 1 }}</td>
                            <td data-label="Nama">
                                <div class="d-flex align-items-center">
                                    <img src="{{ $registrant['avatar_url'] }}" class="img-circle mr-2" width="34" height="34" style="object-fit:cover;">
                                    <strong>{{ $registrant['name'] }}</strong>
                                </div>
                            </td>
                            <td data-label="{{ $identifierLabel }}">{{ $registrant['identifier'] ?? '-' }}</td>
                            <td data-label="Status">
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
                            <td data-label="Capture">@if($face)<span class="badge badge-info">{{ $face->total_captures }}</span>@else - @endif</td>
                            <td data-label="Quality">
                                @if($face)
                                    @php $q = $face->quality_score ?? 0; @endphp
                                    <span class="badge badge-{{ $q >= 80 ? 'success' : ($q >= 50 ? 'warning' : 'danger') }}">{{ number_format($q, 0) }}%</span>
                                @else
                                    -
                                @endif
                            </td>
                            <td data-label="Verifikasi">
                                @if($face && $face->is_verified)
                                    <small>{{ $face->verified_at?->format('d/m/Y H:i') }}</small>
                                @elseif($face)
                                    <small class="text-muted">Menunggu</small>
                                @else
                                    -
                                @endif
                            </td>
                            <td data-label="Tgl Registrasi">@if($face)<small>{{ $face->created_at->format('d/m/Y H:i') }}</small>@else - @endif</td>
                            <td data-label="Aksi">
                                <button class="btn btn-sm btn-{{ $face ? 'warning' : 'primary' }} btn-face-action"
                                        onclick="openRegister('{{ $registrant['user_id'] }}', '{{ addslashes($registrant['name']) }}', '{{ $registrant['user_type'] }}')">
                                    <i class="fas fa-{{ $face ? 'redo' : 'camera' }}"></i>
                                    {{ $face ? 'Ulang' : 'Daftar' }}
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="9" class="text-center text-muted py-4">Belum ada data {{ strtolower($subjectLabel) }} yang bisa diregistrasi.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="modalRegister" tabindex="-1" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable face-register-modal">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white py-2">
                <h5 class="modal-title"><i class="fas fa-camera"></i> Registrasi Wajah - <span id="modalUserName"></span></h5>
                <button type="button" class="close text-white" onclick="closeRegister()"><span>&times;</span></button>
            </div>
            <div class="modal-body p-0">
                <div class="face-register-modal__info px-3 py-2 border-bottom">
                    <div class="small text-muted d-flex flex-wrap align-items-center">
                        <span class="mr-3 mb-1"><i class="fas fa-mobile-alt mr-1"></i> Tampilan menyesuaikan perangkat</span>
                        <span class="mr-3 mb-1"><i class="fas fa-lightbulb mr-1"></i> Gunakan cahaya dari depan</span>
                        <span class="mb-1"><i class="fas fa-user-check mr-1"></i> Simpan lalu tunggu verifikasi admin</span>
                    </div>
                </div>
                <div class="row no-gutters">
                    <div class="col-md-8 position-relative face-register-camera-panel">
                        <div id="loadingOverlay" style="position:absolute; inset:0; background:rgba(0,0,0,0.8); display:flex; flex-direction:column; align-items:center; justify-content:center; z-index:10;">
                            <div class="spinner-border text-info mb-3" role="status"></div>
                            <p class="text-white" id="loadingText">Memuat model face detection...</p>
                        </div>
                        <video id="videoElement" autoplay playsinline style="width:100%; height:100%; object-fit:cover; transform:scaleX(-1);"></video>
                        <canvas id="overlayCanvas" style="position:absolute; top:0; left:0; width:100%; height:100%; transform:scaleX(-1);"></canvas>
                        <div id="stepInstruction" class="face-register-step-instruction" style="position:absolute; top:15px; left:50%; transform:translateX(-50%); background:rgba(0,0,0,0.7); color:#00e5ff; padding:10px 25px; border-radius:25px; font-size:1.1rem; font-weight:600; text-align:center; z-index:5; display:none;">
                            <i class="fas fa-arrow-right" id="stepIcon"></i>
                            <span id="stepText">Lihat ke kamera</span>
                        </div>
                        <div id="autoCaptureIndicator" style="position:absolute; top:50%; left:50%; transform:translate(-50%,-50%); z-index:6; display:none;">
                            <svg width="80" height="80" viewBox="0 0 80 80">
                                <circle cx="40" cy="40" r="35" stroke="#333" stroke-width="4" fill="none" opacity="0.3"/>
                                <circle id="countdownCircle" cx="40" cy="40" r="35" stroke="#00e676" stroke-width="4" fill="none" stroke-dasharray="220" stroke-dashoffset="220" stroke-linecap="round" style="transition: stroke-dashoffset 1.5s linear; transform: rotate(-90deg); transform-origin: center;"/>
                            </svg>
                        </div>
                        <div id="faceStatus" class="face-register-status" style="position:absolute; bottom:15px; left:50%; transform:translateX(-50%); background:rgba(0,0,0,0.7); color:#aaa; padding:8px 20px; border-radius:20px; font-size:0.9rem; z-index:5;">
                            <i class="fas fa-video"></i> Menunggu...
                        </div>
                    </div>
                    <div class="col-md-4 face-register-side-panel" style="background:#f8f9fa;">
                        <div class="p-3">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <h6 class="mb-0"><i class="fas fa-list-ol"></i> Langkah Registrasi</h6>
                                <span class="badge badge-info">5 tahap</span>
                            </div>
                            <p class="text-muted small mb-3"><i class="fas fa-magic"></i> Auto-capture saat wajah stabil (~1.5s)</p>
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
                                        <div class="face-register-step-copy"><strong style="font-size:0.9rem;">{{ $title }}</strong><br><small class="text-muted">{{ $desc }}</small></div>
                                        <div class="ml-auto step-check" style="display:none;"><i class="fas fa-check-circle text-success"></i></div>
                                    </div>
                                </div>
                            @endforeach
                            <div class="mt-2">
                                <div class="progress" style="height:6px;"><div class="progress-bar bg-success" id="progressBar" style="width:0%"></div></div>
                                <small class="text-muted" id="progressText">0 / 5 selesai</small>
                            </div>
                            <button class="btn btn-secondary btn-sm btn-block mt-2 d-none" id="btnReset" onclick="resetRegistration()"><i class="fas fa-redo"></i> Ulangi</button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer py-2">
                <button type="button" class="btn btn-secondary" onclick="closeRegister()"><i class="fas fa-times"></i> Batal</button>
            </div>
        </div>
    </div>
</div>
@stop

@section('css')
<style>
    .face-register-hero {
        background: linear-gradient(135deg, #f6fbff 0%, #eef4ff 100%);
        border-radius: 1rem;
    }
    .face-register-hero__tips {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
    }
    .face-register-checklist > div + div {
        margin-top: 0.35rem;
    }
    .btn-face-action {
        min-width: 92px;
    }
    .face-register-modal .modal-content {
        border-radius: 1rem;
        overflow: hidden;
    }
    .face-register-modal__info {
        background: #f8fbff;
    }
    .face-register-camera-panel {
        background: #000;
        min-height: 400px;
    }
    .face-register-side-panel {
        border-left: 1px solid rgba(0, 0, 0, 0.06);
    }
    .face-register-step-instruction,
    .face-register-status {
        max-width: calc(100% - 1.5rem);
        width: max-content;
    }
    .face-register-step-copy {
        min-width: 0;
    }
    .step-item.active .d-flex { border-color: #007bff !important; background: #e8f4fd !important; }
    .step-item.done .d-flex { border-color: #28a745 !important; }
    .step-item.done .step-number { background: #28a745 !important; }
    .step-item.active .step-number { background: #007bff !important; animation: pulse 1.5s infinite; }
    .step-item.capturing .d-flex { border-color: #ffc107 !important; background: #fff8e1 !important; }
    .step-item.capturing .step-number { background: #ffc107 !important; }
    @keyframes pulse { 0%,100% { transform:scale(1); } 50% { transform:scale(1.15); } }

    @media (max-width: 991.98px) {
        .face-register-camera-panel {
            min-height: 320px;
        }
        .face-register-side-panel {
            border-left: 0;
            border-top: 1px solid rgba(0, 0, 0, 0.06);
        }
    }

    @media (max-width: 767.98px) {
        .face-register-modal {
            margin: 0;
            max-width: 100%;
            height: 100%;
        }
        .face-register-modal .modal-content {
            min-height: 100vh;
            border-radius: 0;
        }
        .face-register-camera-panel {
            min-height: 45vh;
        }
        .face-register-step-instruction {
            font-size: 0.9rem !important;
            padding: 0.6rem 0.9rem !important;
            top: 0.75rem !important;
        }
        .face-register-status {
            font-size: 0.8rem !important;
            left: 0.75rem !important;
            right: 0.75rem;
            bottom: 0.75rem !important;
            transform: none !important;
            width: auto;
            text-align: center;
        }
        #tabelFaceRegister thead {
            display: none;
        }
        #tabelFaceRegister,
        #tabelFaceRegister tbody,
        #tabelFaceRegister tr,
        #tabelFaceRegister td {
            display: block;
            width: 100%;
        }
        #tabelFaceRegister tr {
            border: 1px solid #e9ecef;
            border-radius: 0.85rem;
            background: #fff;
            padding: 0.85rem;
            margin-bottom: 0.85rem;
            box-shadow: 0 0.35rem 1rem rgba(15, 23, 42, 0.05);
        }
        #tabelFaceRegister td {
            border: 0;
            padding: 0.35rem 0 0.35rem 7.2rem !important;
            position: relative;
            min-height: 2rem;
        }
        #tabelFaceRegister td::before {
            content: attr(data-label);
            position: absolute;
            left: 0;
            top: 0.35rem;
            width: 6.4rem;
            color: #6c757d;
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
        }
        #tabelFaceRegister td[data-label="Nama"] {
            padding-left: 0 !important;
            margin-bottom: 0.35rem;
        }
        #tabelFaceRegister td[data-label="Nama"]::before {
            display: none;
        }
        #tabelFaceRegister td[data-label="Aksi"] {
            padding-left: 0 !important;
            margin-top: 0.35rem;
        }
        #tabelFaceRegister td[data-label="Aksi"]::before {
            display: none;
        }
        .btn-face-action {
            width: 100%;
        }
    }
</style>
@stop

@section('js')
<script src="{{ asset('vendor/face-api/face-api.min.js') }}"></script>
<script>
let selectedUserId = null, selectedUserName = '', selectedUserType = '{{ $selectedType }}', currentStep = -1;
const totalSteps = 5, STABLE_DURATION_MS = 1500, storeUrl = @json($storeUrl), initialSelection = @json($initialSelection);
let capturedDescriptors = [], capturedAngles = [], isDetecting = false, modelsLoaded = false, cameraStream = null, faceStableStart = null, autoCapturing = false, blinkCount = 0, earHistory = [], eyeWasClosed = false;
const STEPS = [
    { angle: 'frontal', text: 'Lihat lurus ke kamera', icon: 'fa-user' },
    { angle: 'kanan', text: 'Putar kepala ke KANAN', icon: 'fa-arrow-right' },
    { angle: 'kiri', text: 'Putar kepala ke KIRI', icon: 'fa-arrow-left' },
    { angle: 'senyum', text: 'Tersenyum natural', icon: 'fa-smile' },
    { angle: 'kedip', text: 'Kedipkan mata 1 kali', icon: 'fa-eye' },
];

$(function() {
    const table = $('#tabelFaceRegister').DataTable({
        language: { url: 'https://cdn.datatables.net/plug-ins/1.13.7/i18n/id.json' },
        pageLength: 25,
        order: [[1, 'asc']],
        columnDefs: [{ orderable: false, targets: [8] }],
        scrollX: true,
        autoWidth: false,
    });
    let activeFilter = '';
    $.fn.dataTable.ext.search.push(function(settings, data) {
        if (settings.nTable.id !== 'tabelFaceRegister' || !activeFilter) return true;
        return data[3].indexOf(activeFilter) !== -1;
    });
    $('#statusFilter').on('click', 'button', function() {
        $('#statusFilter button').removeClass('active');
        $(this).addClass('active');
        activeFilter = $(this).data('filter');
        table.draw();
    });
    if (initialSelection) openRegister(initialSelection.user_id, initialSelection.name, initialSelection.user_type);
});

function openRegister(userId, userName, userType) {
    selectedUserId = userId;
    selectedUserName = userName;
    selectedUserType = userType;
    document.getElementById('modalUserName').textContent = userName;
    $('#modalRegister').modal('show');
    if (!modelsLoaded) loadModels(); else startCameraAndRegister();
}

function closeRegister() { isDetecting = false; stopCamera(); resetUI(); $('#modalRegister').modal('hide'); }
function stopCamera() { if (cameraStream) { cameraStream.getTracks().forEach(t => t.stop()); cameraStream = null; } const video = document.getElementById('videoElement'); if (video) video.srcObject = null; }
async function loadModels() {
    const MODEL_URL = '{{ asset("vendor/face-api/models") }}';
    try {
        setLoadingText('Memuat model deteksi wajah...'); await faceapi.nets.tinyFaceDetector.loadFromUri(MODEL_URL);
        setLoadingText('Memuat model landmark wajah...'); await faceapi.nets.faceLandmark68TinyNet.loadFromUri(MODEL_URL);
        setLoadingText('Memuat model pengenalan wajah...'); await faceapi.nets.faceRecognitionNet.loadFromUri(MODEL_URL);
        modelsLoaded = true; startCameraAndRegister();
    } catch (err) { setLoadingText('Error: ' + err.message); }
}
async function startCameraAndRegister() {
    setLoadingText('Menyalakan kamera...'); document.getElementById('loadingOverlay').style.display = 'flex';
    const video = document.getElementById('videoElement');
    try {
        cameraStream = await navigator.mediaDevices.getUserMedia({ video: { width: { ideal: 1280 }, height: { ideal: 720 }, facingMode: 'user' } });
        video.srcObject = cameraStream; await new Promise(r => { video.onloadedmetadata = r; }); document.getElementById('loadingOverlay').style.display = 'none'; beginAutoRegistration();
    } catch (err) { setLoadingText('Kamera tidak tersedia: ' + err.message); }
}
function setLoadingText(text) { document.getElementById('loadingText').textContent = text; }
function beginAutoRegistration() { capturedDescriptors = []; capturedAngles = []; currentStep = 0; blinkCount = 0; earHistory = []; eyeWasClosed = false; faceStableStart = null; autoCapturing = false; resetUI(); document.getElementById('btnReset').classList.remove('d-none'); updateStepUI(); startDetectionLoop(); }
function resetUI() {
    document.querySelectorAll('.step-item').forEach(el => { el.classList.remove('active', 'done', 'capturing'); el.querySelector('.step-check').style.display = 'none'; });
    document.getElementById('progressBar').style.width = '0%'; document.getElementById('progressText').textContent = '0 / 5 selesai'; document.getElementById('stepInstruction').style.display = 'none'; document.getElementById('stepInstruction').style.background = 'rgba(0,0,0,0.7)'; document.getElementById('stepInstruction').style.color = '#00e5ff'; document.getElementById('btnReset').classList.add('d-none'); hideCountdownRing();
    const canvas = document.getElementById('overlayCanvas'); if (canvas) canvas.getContext('2d').clearRect(0, 0, canvas.width, canvas.height);
}
function updateStepUI() {
    if (currentStep >= totalSteps) return;
    document.querySelectorAll('.step-item').forEach((el, i) => { el.classList.remove('active', 'capturing'); if (i < currentStep) el.classList.add('done'); else if (i === currentStep) el.classList.add('active'); });
    const step = STEPS[currentStep]; document.getElementById('stepInstruction').style.display = 'block'; document.getElementById('stepIcon').className = 'fas ' + step.icon + ' mr-2'; document.getElementById('stepText').textContent = step.text;
    if (step.angle === 'kedip') { blinkCount = 0; earHistory = []; eyeWasClosed = false; } faceStableStart = null; hideCountdownRing();
}
function startDetectionLoop() {
    isDetecting = true;
    const video = document.getElementById('videoElement'), canvas = document.getElementById('overlayCanvas'), ctx = canvas.getContext('2d'), options = new faceapi.TinyFaceDetectorOptions({ inputSize: 320, scoreThreshold: 0.5 });
    async function detect() {
        if (!isDetecting || video.paused || currentStep < 0) return;
        canvas.width = video.videoWidth || video.clientWidth; canvas.height = video.videoHeight || video.clientHeight;
        const detection = await faceapi.detectSingleFace(video, options).withFaceLandmarks(true); ctx.clearRect(0, 0, canvas.width, canvas.height);
        if (detection) {
            const dims = faceapi.matchDimensions(canvas, video, true), resized = faceapi.resizeResults(detection, dims);
            resized.landmarks.positions.forEach(pt => { ctx.beginPath(); ctx.arc(pt.x, pt.y, 2, 0, 2 * Math.PI); ctx.fillStyle = '#00e5ff'; ctx.globalAlpha = 0.7; ctx.fill(); });
            ctx.globalAlpha = 1; const box = resized.detection.box; ctx.strokeStyle = '#00e5ff'; ctx.lineWidth = 2; ctx.strokeRect(box.x, box.y, box.width, box.height);
            setFaceStatus(`Wajah terdeteksi (${(detection.detection.score * 100).toFixed(0)}%)`, true);
            if (currentStep < totalSteps && !autoCapturing) {
                if (STEPS[currentStep].angle === 'kedip') detectBlink(detection.landmarks);
                else {
                    const poseOk = validatePose(detection.landmarks, STEPS[currentStep].angle);
                    if (poseOk) {
                        if (!faceStableStart) { faceStableStart = Date.now(); showCountdownRing(); }
                        else if (Date.now() - faceStableStart >= STABLE_DURATION_MS) { autoCapturing = true; await doCapture(); autoCapturing = false; }
                    } else if (faceStableStart) { faceStableStart = null; hideCountdownRing(); }
                }
            }
        } else { setFaceStatus('Arahkan wajah ke kamera', false); if (faceStableStart) { faceStableStart = null; hideCountdownRing(); } }
        const delay = (currentStep < totalSteps && STEPS[currentStep]?.angle === 'kedip') ? 60 : 150; requestAnimationFrame(() => setTimeout(detect, delay));
    }
    detect();
}
function setFaceStatus(text, ok) { const el = document.getElementById('faceStatus'); el.style.color = ok ? '#00e676' : '#ff5252'; el.innerHTML = `<i class="fas fa-${ok ? 'check-circle' : 'exclamation-circle'}"></i> ${text}`; }
function validatePose(landmarks, angle) {
    const pts = landmarks.positions, noseTip = pts[30], jawLeft = pts[0], jawRight = pts[16];
    const dL = Math.sqrt((noseTip.x - jawLeft.x) ** 2 + (noseTip.y - jawLeft.y) ** 2), dR = Math.sqrt((noseTip.x - jawRight.x) ** 2 + (noseTip.y - jawRight.y) ** 2), yawRatio = dL / dR;
    if (angle === 'frontal') { const ok = yawRatio > 0.82 && yawRatio < 1.22; setFaceStatus(ok ? 'Bagus! Tetap menghadap depan...' : `Lihat lurus ke kamera (rasio: ${yawRatio.toFixed(2)})`, ok); return ok; }
    if (angle === 'kanan') { const ok = yawRatio < 0.82; setFaceStatus(ok ? 'Bagus! Tahan posisi...' : `Putar kepala ke KANAN Anda (rasio: ${yawRatio.toFixed(2)}, butuh < 0.82)`, ok); return ok; }
    if (angle === 'kiri') { const ok = yawRatio > 1.22; setFaceStatus(ok ? 'Bagus! Tahan posisi...' : `Putar kepala ke KIRI Anda (rasio: ${yawRatio.toFixed(2)}, butuh > 1.22)`, ok); return ok; }
    if (angle === 'senyum') { const mouthL = pts[48], mouthR = pts[54], mouthW = Math.sqrt((mouthR.x - mouthL.x) ** 2 + (mouthR.y - mouthL.y) ** 2), jawW = Math.sqrt((jawRight.x - jawLeft.x) ** 2 + (jawRight.y - jawLeft.y) ** 2), smileRatio = mouthW / jawW, ok = smileRatio > 0.38; setFaceStatus(ok ? 'Senyum terdeteksi! Tahan...' : `Tersenyum lebih lebar! (${(smileRatio * 100).toFixed(0)}%, butuh > 38%)`, ok); return ok; }
    return true;
}
function showCountdownRing() { const ring = document.getElementById('autoCaptureIndicator'), circle = document.getElementById('countdownCircle'); ring.style.display = 'block'; circle.style.transition = 'none'; circle.style.strokeDashoffset = '220'; requestAnimationFrame(() => { circle.style.transition = `stroke-dashoffset ${STABLE_DURATION_MS}ms linear`; circle.style.strokeDashoffset = '0'; }); if (currentStep >= 0 && currentStep < totalSteps) document.getElementById('step-' + currentStep).classList.add('capturing'); }
function hideCountdownRing() { document.getElementById('autoCaptureIndicator').style.display = 'none'; const c = document.getElementById('countdownCircle'); c.style.transition = 'none'; c.style.strokeDashoffset = '220'; if (currentStep >= 0 && currentStep < totalSteps) document.getElementById('step-' + currentStep).classList.remove('capturing'); }
function detectBlink(landmarks) {
    const leftEye = landmarks.getLeftEye(), rightEye = landmarks.getRightEye(), rawEar = (eyeAspectRatio(leftEye) + eyeAspectRatio(rightEye)) / 2;
    earHistory.push(rawEar); if (earHistory.length > 3) earHistory.shift(); const ear = earHistory.reduce((a, b) => a + b, 0) / earHistory.length, threshold = 0.26;
    setFaceStatus(`Kedipkan mata! EAR: ${ear.toFixed(3)} ${ear < threshold ? 'TERTUTUP' : 'Terbuka'} (${blinkCount}/1)`, true);
    if (ear < threshold && !eyeWasClosed) eyeWasClosed = true;
    else if (ear > threshold + 0.03 && eyeWasClosed) { eyeWasClosed = false; blinkCount++; document.getElementById('stepText').textContent = `Kedip terdeteksi! (${blinkCount}/1)`; if (blinkCount >= 1 && !autoCapturing) { autoCapturing = true; setTimeout(async () => { await doCaptureWithRetry(); autoCapturing = false; }, 400); } }
}
function eyeAspectRatio(eye) { const d = (a, b) => Math.sqrt((a.x - b.x) ** 2 + (a.y - b.y) ** 2); return (d(eye[1], eye[5]) + d(eye[2], eye[4])) / (2 * d(eye[0], eye[3])); }
async function doCaptureWithRetry(maxRetries = 3) { for (let i = 0; i < maxRetries; i++) { const ok = await doCapture(); if (ok) return; await new Promise(r => setTimeout(r, 300)); } setFaceStatus('Gagal capture, silakan kedipkan lagi', false); blinkCount = 0; eyeWasClosed = false; earHistory = []; }
async function doCapture() {
    if (currentStep >= totalSteps) return;
    const video = document.getElementById('videoElement'), opts = new faceapi.TinyFaceDetectorOptions({ inputSize: 416, scoreThreshold: 0.5 }), det = await faceapi.detectSingleFace(video, opts).withFaceLandmarks(true).withFaceDescriptor();
    if (!det) { faceStableStart = null; hideCountdownRing(); return false; }
    capturedDescriptors.push(Array.from(det.descriptor)); capturedAngles.push(STEPS[currentStep].angle);
    const stepEl = document.getElementById('step-' + currentStep); stepEl.classList.remove('active', 'capturing'); stepEl.classList.add('done'); stepEl.querySelector('.step-check').style.display = 'block'; hideCountdownRing();
    const video2 = document.getElementById('videoElement'); video2.style.outline = '4px solid #00e676'; setTimeout(() => { video2.style.outline = ''; }, 400);
    currentStep++; document.getElementById('progressBar').style.width = ((currentStep / totalSteps) * 100) + '%'; document.getElementById('progressText').textContent = `${currentStep} / ${totalSteps} selesai`;
    if (currentStep >= totalSteps) { document.getElementById('stepInstruction').style.display = 'none'; setFaceStatus('Menyimpan...', true); await saveRegistration(); }
    else { await new Promise(r => setTimeout(r, 500)); updateStepUI(); }
    return true;
}
async function saveRegistration() {
    const video = document.getElementById('videoElement'), c = document.createElement('canvas'); c.width = 320; c.height = 240; c.getContext('2d').drawImage(video, 0, 0, 320, 240);
    try {
        const res = await fetch(storeUrl, { method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' }, body: JSON.stringify({ user_id: selectedUserId, user_type: selectedUserType, descriptors: capturedDescriptors, angles: capturedAngles, quality_score: capturedDescriptors.length * 20, photo: c.toDataURL('image/jpeg', 0.8) }) });
        const result = await res.json();
        if (result.success) { document.getElementById('stepInstruction').innerHTML = '<i class="fas fa-check-circle mr-2"></i>Registrasi berhasil!'; document.getElementById('stepInstruction').style.display = 'block'; document.getElementById('stepInstruction').style.background = 'rgba(40,167,69,0.9)'; document.getElementById('stepInstruction').style.color = '#fff'; setFaceStatus('Tersimpan. Menunggu verifikasi admin.', true); setTimeout(() => { closeRegister(); window.location.reload(); }, 1500); }
        else setFaceStatus('Gagal: ' + (result.message || 'Error'), false);
    } catch (err) { setFaceStatus('Error: ' + err.message, false); }
}
function resetRegistration() { isDetecting = false; autoCapturing = false; capturedDescriptors = []; capturedAngles = []; currentStep = -1; blinkCount = 0; earHistory = []; eyeWasClosed = false; faceStableStart = null; resetUI(); setTimeout(() => beginAutoRegistration(), 300); }
</script>
@stop
