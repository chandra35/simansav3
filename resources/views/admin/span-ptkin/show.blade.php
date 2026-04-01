@extends('adminlte::page')

@section('title', $spanPtkinMenu->nama_menu)

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1><i class="fas fa-mosque"></i> {{ $spanPtkinMenu->nama_menu }}</h1>
        <a href="{{ route('admin.span-ptkin-menu.index') }}" class="btn btn-secondary btn-sm">
            <i class="fas fa-arrow-left"></i> Kembali
        </a>
    </div>
@stop

@section('content')
<div class="container-fluid">
    @foreach(['success' => 'success', 'warning' => 'warning', 'error' => 'danger'] as $sessionKey => $alertType)
        @if(session($sessionKey))
            <div class="alert alert-{{ $alertType }}">{{ session($sessionKey) }}</div>
        @endif
    @endforeach

    <div id="spanPtkinProgressOverlay" class="span-ptkin-progress-overlay d-none" aria-hidden="true">
        <div class="span-ptkin-progress-dialog">
            <div class="span-ptkin-progress-icon">
                <i class="fas fa-spinner fa-spin"></i>
            </div>
            <h4 id="spanPtkinOverlayTitle" class="mb-2">Memproses Import SPAN-PTKIN</h4>
            <p id="spanPtkinOverlayText" class="text-muted mb-3">Mohon tunggu, proses sedang berjalan.</p>
            <div class="progress progress-sm mb-2">
                <div id="spanPtkinOverlayBar" class="progress-bar progress-bar-striped progress-bar-animated bg-info" role="progressbar" style="width: 0%"></div>
            </div>
            <div class="d-flex justify-content-between align-items-center">
                <small class="text-muted">Jangan tutup halaman ini sebelum proses selesai.</small>
                <span id="spanPtkinOverlayValue" class="badge badge-info">0%</span>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <strong>Tahun Pelajaran</strong><br>
                            {{ $spanPtkinMenu->tahunPelajaran->nama ?? '-' }}
                        </div>
                        <div class="col-md-4">
                            <strong>Status</strong><br>
                            <span class="badge badge-{{ $spanPtkinMenu->is_active ? 'success' : 'secondary' }}">
                                {{ $spanPtkinMenu->is_active ? 'Aktif' : 'Non-Aktif' }}
                            </span>
                        </div>
                        <div class="col-md-4">
                            <strong>Periode</strong><br>
                            <small>{{ $spanPtkinMenu->tanggal_mulai?->format('d-m-Y H:i') ?? 'Tanpa batas' }}</small>
                        </div>
                    </div>
                    @if($spanPtkinMenu->konten_informasi)
                        <hr>
                        <div>{!! $spanPtkinMenu->konten_informasi !!}</div>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <form id="spanPtkinUploadForm" action="{{ route('admin.span-ptkin-menu.import-pdf', $spanPtkinMenu) }}" method="POST" enctype="multipart/form-data" class="card card-success card-outline">
                @csrf
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-file-pdf"></i> Upload PDF untuk Preview</h3>
                </div>
                <div class="card-body">
                    <p class="text-muted small">Upload daftar siswa SPAN-PTKIN hasil unduhan resmi sekolah. Sistem akan membuat preview pencocokan berdasarkan NISN, lalu fallback ke nama siswa. Data belum disimpan sampai admin menekan tombol konfirmasi.</p>
                    <div class="form-group mb-0">
                        <input type="file" name="pdf_file" accept="application/pdf" class="form-control-file @error('pdf_file') is-invalid @enderror">
                        @error('pdf_file')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div class="card-footer">
                    <button type="submit" class="btn btn-success btn-block" data-role="upload-submit">
                        <i class="fas fa-search"></i> Preview Import
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div id="spanPtkinUploadProgressCard" class="card card-outline card-info d-none">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-upload"></i> Progress Upload PDF</h3>
        </div>
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <strong id="spanPtkinUploadProgressLabel">Menyiapkan upload...</strong>
                <span id="spanPtkinUploadProgressValue" class="badge badge-info">0%</span>
            </div>
            <div class="progress progress-sm">
                <div id="spanPtkinUploadProgressBar" class="progress-bar progress-bar-striped progress-bar-animated bg-info" role="progressbar" style="width: 0%"></div>
            </div>
            <small class="text-muted d-block mt-2">File akan dipreview terlebih dahulu. Data belum masuk database sampai admin menekan konfirmasi simpan.</small>
        </div>
    </div>

    @if($previewImport)
    <div class="card card-warning card-outline">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-clipboard-check"></i> Preview Import PDF</h3>
            <div class="card-tools">
                <span class="badge badge-light">{{ $previewImport['source_file_name'] ?? 'PDF Preview' }}</span>
            </div>
        </div>
        <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-3">
                    <div class="small-box bg-info mb-0">
                        <div class="inner"><h3>{{ $previewImport['summary']['total_rows'] ?? 0 }}</h3><p>Total baris PDF</p></div>
                        <div class="icon"><i class="fas fa-file-alt"></i></div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="small-box bg-success mb-0">
                        <div class="inner"><h3>{{ $previewImport['summary']['matched'] ?? 0 }}</h3><p>Data cocok</p></div>
                        <div class="icon"><i class="fas fa-check-circle"></i></div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="small-box bg-primary mb-0">
                        <div class="inner"><h3>{{ $previewImport['summary']['create'] ?? 0 }}</h3><p>Akan dibuat</p></div>
                        <div class="icon"><i class="fas fa-plus-circle"></i></div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="small-box bg-warning mb-0">
                        <div class="inner"><h3>{{ $previewImport['summary']['update'] ?? 0 }}</h3><p>Akan diperbarui</p></div>
                        <div class="icon"><i class="fas fa-sync-alt"></i></div>
                    </div>
                </div>
            </div>

            @if(!empty($previewImport['summary']['unmatched']))
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle"></i>
                    Ada {{ $previewImport['summary']['unmatched'] }} data yang belum cocok. Data ini tidak akan disimpan saat konfirmasi.
                </div>
            @endif

            <div class="d-flex flex-wrap mb-3">
                <form id="spanPtkinConfirmForm" action="{{ route('admin.span-ptkin-menu.confirm-import', $spanPtkinMenu) }}" method="POST" class="mr-2 mb-2">
                    @csrf
                    <button type="submit" class="btn btn-success" data-role="save-submit">
                        <i class="fas fa-save"></i> Konfirmasi Simpan ke Database
                    </button>
                </form>
                <form action="{{ route('admin.span-ptkin-menu.cancel-preview', $spanPtkinMenu) }}" method="POST" class="mb-2" onsubmit="return confirm('Batalkan preview import ini?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-outline-secondary">
                        <i class="fas fa-times"></i> Batalkan Preview
                    </button>
                </form>
            </div>

            <div id="spanPtkinSaveProgressCard" class="alert alert-info d-none">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <strong id="spanPtkinSaveProgressLabel">Menyiapkan penyimpanan...</strong>
                    <span id="spanPtkinSaveProgressValue" class="badge badge-info">0%</span>
                </div>
                <div class="progress progress-sm">
                    <div id="spanPtkinSaveProgressBar" class="progress-bar progress-bar-striped progress-bar-animated bg-info" role="progressbar" style="width: 0%"></div>
                </div>
                <small class="text-muted d-block mt-2">Sistem sedang menyimpan hasil preview ke database dan memperbarui data nomor pendaftaran.</small>
            </div>

            <div class="table-responsive">
                <table id="spanPtkinPreviewTable" class="table table-sm table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>NISN PDF</th>
                            <th>Nama PDF</th>
                            <th>No. Pendaftaran</th>
                            <th>Jurusan PDF</th>
                            <th>Status Match</th>
                            <th>Match ke Siswa</th>
                            <th>Kelas</th>
                            <th>Aksi Import</th>
                            <th>No. Lama</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($previewImport['rows'] as $index => $row)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td><code>{{ $row['nisn'] }}</code></td>
                                <td>{{ $row['nama_siswa'] }}</td>
                                <td><code>{{ $row['nomor_pendaftaran'] }}</code></td>
                                <td>{{ $row['jurusan'] }}</td>
                                <td>
                                    @if($row['matched'])
                                        <span class="badge badge-success">Cocok via {{ $row['matched_by'] === 'nisn' ? 'NISN' : 'Nama' }}</span>
                                    @else
                                        <span class="badge badge-danger">Tidak cocok</span>
                                    @endif
                                </td>
                                <td>{{ $row['matched_name'] ?? '-' }}</td>
                                <td>{{ $row['kelas'] ?? '-' }}</td>
                                <td>
                                    @if($row['will_action'] === 'create')
                                        <span class="badge badge-primary">Create</span>
                                    @elseif($row['will_action'] === 'update')
                                        <span class="badge badge-warning">Update</span>
                                    @else
                                        <span class="badge badge-secondary">Skip</span>
                                    @endif
                                </td>
                                <td>{{ $row['existing_number'] ?? '-' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif

    <div class="row">
        <div class="col-md-3">
            <div class="small-box bg-info">
                <div class="inner"><h3>{{ $summary['kelas_12_total'] }}</h3><p>Total siswa kelas 12</p></div>
                <div class="icon"><i class="fas fa-users"></i></div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="small-box bg-success">
                <div class="inner"><h3>{{ $summary['sudah_terimport'] }}</h3><p>Nomor sudah terimport</p></div>
                <div class="icon"><i class="fas fa-id-card"></i></div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="small-box bg-secondary">
                <div class="inner"><h3>{{ $summary['belum_terimport'] }}</h3><p>Belum ada nomor</p></div>
                <div class="icon"><i class="fas fa-clock"></i></div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="small-box bg-primary">
                <div class="inner"><h3>{{ $summary['terhubung_lulusan'] }}</h3><p>Terhubung ke lulusan</p></div>
                <div class="icon"><i class="fas fa-link"></i></div>
            </div>
        </div>
    </div>

    <div class="card card-outline card-success">
        <div class="card-header">
            <h3 class="card-title">Monitoring Siswa Kelas 12</h3>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="spanPtkinTable" class="table table-striped table-bordered">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>NISN</th>
                            <th>Nama</th>
                            <th>Kelas</th>
                            <th>Tanggal Lahir</th>
                            <th>No. Pendaftaran</th>
                            <th>Import Terakhir</th>
                            <th>Status Lulusan</th>
                            <th>Jalur</th>
                            <th>PTKIN / Universitas</th>
                            <th>Program Studi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($monitoring as $index => $siswa)
                            @php
                                $registration = $siswa->spanPtkinRegistration;
                                $lulusan = optional($registration)->lulusan;
                                $universitas = optional($lulusan)->nama_universitas
                                    ?? optional($lulusan)->nama_universitas_manual
                                    ?? optional(optional($lulusan)->referensiPerguruanTinggi)->nama
                                    ?? '-';
                                $programStudi = optional($lulusan)->program_studi
                                    ?? optional($lulusan)->program_studi_manual
                                    ?? optional(optional($lulusan)->referensiProgramStudi)->nama_program_studi
                                    ?? '-';
                            @endphp
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td><code>{{ $siswa->nisn }}</code></td>
                                <td>{{ $siswa->nama_lengkap }}</td>
                                <td>{{ $siswa->kelasSaatIni->nama_kelas ?? '-' }}</td>
                                <td data-order="{{ $siswa->tanggal_lahir?->format('Y-m-d') ?? '' }}">{{ $siswa->tanggal_lahir?->format('d-m-Y') ?? '-' }}</td>
                                <td>{{ $registration?->nomor_pendaftaran ? $registration->nomor_pendaftaran : 'Belum terimport' }}</td>
                                <td data-order="{{ $registration?->imported_at?->timestamp ?? 0 }}">{{ $registration?->imported_at?->format('d-m-Y H:i') ?? '-' }}</td>
                                <td>
                                    @if($lulusan)
                                        <span class="badge badge-success">Terhubung</span>
                                    @else
                                        <span class="badge badge-secondary">Belum</span>
                                    @endif
                                </td>
                                <td>{{ $lulusan?->jalur_masuk ?? '-' }}</td>
                                <td>{{ $universitas }}</td>
                                <td>{{ $programStudi }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@stop

@section('css')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap4.min.css">
<style>
    #spanPtkinPreviewTable code,
    #spanPtkinTable code {
        font-size: 0.9rem;
    }

    .progress-sm {
        height: 14px;
        border-radius: 999px;
    }

    .progress-sm .progress-bar {
        line-height: 14px;
    }

    .span-ptkin-progress-overlay {
        position: fixed;
        inset: 0;
        z-index: 1060;
        background: rgba(17, 24, 39, 0.72);
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 1.5rem;
        backdrop-filter: blur(2px);
    }

    .span-ptkin-progress-dialog {
        width: min(100%, 460px);
        background: #fff;
        border-radius: 18px;
        box-shadow: 0 18px 48px rgba(15, 23, 42, 0.24);
        padding: 1.5rem;
        text-align: center;
    }

    .span-ptkin-progress-icon {
        width: 72px;
        height: 72px;
        margin: 0 auto 1rem;
        border-radius: 999px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: rgba(23, 162, 184, 0.12);
        color: #17a2b8;
        font-size: 1.75rem;
    }

    body.span-ptkin-progress-active {
        overflow: hidden;
    }
</style>
@stop

@section('js')
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap4.min.js"></script>
<script>
    $(function () {
        const uploadForm = document.getElementById('spanPtkinUploadForm');
        const confirmForm = document.getElementById('spanPtkinConfirmForm');
        const uploadProgressCard = document.getElementById('spanPtkinUploadProgressCard');
        const uploadProgressBar = document.getElementById('spanPtkinUploadProgressBar');
        const uploadProgressLabel = document.getElementById('spanPtkinUploadProgressLabel');
        const uploadProgressValue = document.getElementById('spanPtkinUploadProgressValue');
        const saveProgressCard = document.getElementById('spanPtkinSaveProgressCard');
        const saveProgressBar = document.getElementById('spanPtkinSaveProgressBar');
        const saveProgressLabel = document.getElementById('spanPtkinSaveProgressLabel');
        const saveProgressValue = document.getElementById('spanPtkinSaveProgressValue');
        const progressOverlay = document.getElementById('spanPtkinProgressOverlay');
        const overlayTitle = document.getElementById('spanPtkinOverlayTitle');
        const overlayText = document.getElementById('spanPtkinOverlayText');
        const overlayBar = document.getElementById('spanPtkinOverlayBar');
        const overlayValue = document.getElementById('spanPtkinOverlayValue');
        let saveProgressTimer = null;

        function updateProgress(bar, label, value, percent, text) {
            if (!bar || !label || !value) {
                return;
            }

            const safePercent = Math.max(0, Math.min(100, percent));
            bar.style.width = safePercent + '%';
            bar.setAttribute('aria-valuenow', safePercent);
            label.textContent = text;
            value.textContent = safePercent + '%';
        }

        function showOverlay(title, text, percent) {
            if (!progressOverlay) {
                return;
            }

            document.body.classList.add('span-ptkin-progress-active');
            progressOverlay.classList.remove('d-none');
            progressOverlay.setAttribute('aria-hidden', 'false');

            if (overlayTitle) {
                overlayTitle.textContent = title;
            }

            if (overlayText) {
                overlayText.textContent = text;
            }

            updateProgress(overlayBar, overlayText, overlayValue, percent, text);
        }

        function updateOverlay(text, percent) {
            if (!progressOverlay || progressOverlay.classList.contains('d-none')) {
                return;
            }

            updateProgress(overlayBar, overlayText, overlayValue, percent, text);
        }

        function hideOverlay() {
            if (!progressOverlay) {
                return;
            }

            progressOverlay.classList.add('d-none');
            progressOverlay.setAttribute('aria-hidden', 'true');
            document.body.classList.remove('span-ptkin-progress-active');
        }

        function setButtonLoading(button, loadingText) {
            if (!button) {
                return;
            }

            if (!button.dataset.originalHtml) {
                button.dataset.originalHtml = button.innerHTML;
            }

            button.disabled = true;
            button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> ' + loadingText;
        }

        function resetButton(button) {
            if (!button || !button.dataset.originalHtml) {
                return;
            }

            button.disabled = false;
            button.innerHTML = button.dataset.originalHtml;
        }

        function redirectWithFlash(response) {
            if (response && response.redirect_url) {
                const url = new URL(response.redirect_url, window.location.origin);
                url.searchParams.set('flash_status', response.status || 'success');
                url.searchParams.set('flash_message', response.message || '');
                window.location.href = url.toString();
                return;
            }

            window.location.reload();
        }

        const currentUrl = new URL(window.location.href);
        const flashStatus = currentUrl.searchParams.get('flash_status');
        const flashMessage = currentUrl.searchParams.get('flash_message');

        if (flashStatus && flashMessage) {
            const alertTypeMap = {
                success: 'success',
                warning: 'warning',
                error: 'danger',
            };

            const alert = document.createElement('div');
            alert.className = 'alert alert-' + (alertTypeMap[flashStatus] || 'info');
            alert.textContent = flashMessage;
            const container = document.querySelector('.container-fluid');
            if (container) {
                container.insertBefore(alert, container.firstChild);
            }

            currentUrl.searchParams.delete('flash_status');
            currentUrl.searchParams.delete('flash_message');
            window.history.replaceState({}, document.title, currentUrl.toString());
        }

        if (uploadForm) {
            uploadForm.addEventListener('submit', function (event) {
                event.preventDefault();

                const fileInput = uploadForm.querySelector('input[name="pdf_file"]');
                const submitButton = uploadForm.querySelector('[data-role="upload-submit"]');

                if (!fileInput || !fileInput.files.length) {
                    fileInput && fileInput.focus();
                    return;
                }

                uploadProgressCard.classList.remove('d-none');
                updateProgress(uploadProgressBar, uploadProgressLabel, uploadProgressValue, 0, 'Menyiapkan upload...');
                setButtonLoading(submitButton, 'Mengunggah PDF...');
                showOverlay('Mengunggah PDF SPAN-PTKIN', 'Menyiapkan upload file PDF...', 0);

                const xhr = new XMLHttpRequest();
                xhr.open('POST', uploadForm.action, true);
                xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
                xhr.setRequestHeader('Accept', 'application/json');

                xhr.upload.addEventListener('progress', function (progressEvent) {
                    if (progressEvent.lengthComputable) {
                        const percent = Math.round((progressEvent.loaded / progressEvent.total) * 100);
                        const text = percent >= 100
                            ? 'Upload selesai, sistem sedang membuat preview...'
                            : 'Mengunggah file PDF...';
                        updateProgress(uploadProgressBar, uploadProgressLabel, uploadProgressValue, percent, text);
                        updateOverlay(text, percent);
                    }
                });

                xhr.onreadystatechange = function () {
                    if (xhr.readyState !== XMLHttpRequest.DONE) {
                        return;
                    }

                    let payload = null;
                    try {
                        payload = JSON.parse(xhr.responseText);
                    } catch (error) {
                        payload = null;
                    }

                    if (xhr.status >= 200 && xhr.status < 300) {
                        updateProgress(uploadProgressBar, uploadProgressLabel, uploadProgressValue, 100, 'Preview siap, mengarahkan ke hasil import...');
                        updateOverlay('Preview siap, mengarahkan ke hasil import...', 100);
                        redirectWithFlash(payload);
                        return;
                    }

                    const message = payload && payload.message
                        ? payload.message
                        : 'Upload PDF gagal diproses. Silakan coba lagi.';

                    updateProgress(uploadProgressBar, uploadProgressLabel, uploadProgressValue, 100, 'Upload gagal diproses.');
                    updateOverlay('Upload gagal diproses.', 100);
                    alert(message);
                    hideOverlay();
                    resetButton(submitButton);
                };

                xhr.onerror = function () {
                    updateProgress(uploadProgressBar, uploadProgressLabel, uploadProgressValue, 100, 'Terjadi gangguan jaringan saat upload.');
                    updateOverlay('Terjadi gangguan jaringan saat upload.', 100);
                    alert('Terjadi gangguan jaringan saat upload PDF.');
                    hideOverlay();
                    resetButton(submitButton);
                };

                xhr.send(new FormData(uploadForm));
            });
        }

        if (confirmForm) {
            confirmForm.addEventListener('submit', function (event) {
                event.preventDefault();

                const submitButton = confirmForm.querySelector('[data-role="save-submit"]');
                setButtonLoading(submitButton, 'Menyimpan ke database...');
                saveProgressCard.classList.remove('d-none');
                updateProgress(saveProgressBar, saveProgressLabel, saveProgressValue, 10, 'Menyiapkan penyimpanan ke database...');
                showOverlay('Menyimpan Hasil Import', 'Menyiapkan penyimpanan ke database...', 10);

                const steps = [
                    { percent: 25, text: 'Memvalidasi preview import...' },
                    { percent: 45, text: 'Menyimpan nomor pendaftaran SPAN-PTKIN...' },
                    { percent: 70, text: 'Memperbarui data siswa yang cocok...' },
                    { percent: 90, text: 'Merapikan hasil import dan finalisasi...' }
                ];

                let stepIndex = 0;
                clearInterval(saveProgressTimer);
                saveProgressTimer = window.setInterval(function () {
                    if (stepIndex >= steps.length) {
                        clearInterval(saveProgressTimer);
                        return;
                    }

                    const step = steps[stepIndex];
                    updateProgress(saveProgressBar, saveProgressLabel, saveProgressValue, step.percent, step.text);
                    updateOverlay(step.text, step.percent);
                    stepIndex += 1;
                }, 500);

                fetch(confirmForm.action, {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': confirmForm.querySelector('input[name="_token"]').value,
                    },
                    credentials: 'same-origin',
                })
                .then(async function (response) {
                    const payload = await response.json().catch(function () {
                        return null;
                    });

                    if (!response.ok) {
                        throw payload || { message: 'Konfirmasi simpan gagal diproses.' };
                    }

                    updateProgress(saveProgressBar, saveProgressLabel, saveProgressValue, 100, 'Penyimpanan selesai, mengarahkan ke hasil terbaru...');
                    updateOverlay('Penyimpanan selesai, mengarahkan ke hasil terbaru...', 100);
                    redirectWithFlash(payload);
                })
                .catch(function (error) {
                    clearInterval(saveProgressTimer);
                    updateProgress(saveProgressBar, saveProgressLabel, saveProgressValue, 100, 'Penyimpanan gagal diproses.');
                    updateOverlay('Penyimpanan gagal diproses.', 100);
                    alert(error && error.message ? error.message : 'Konfirmasi simpan gagal diproses.');
                    hideOverlay();
                    resetButton(submitButton);
                });
            });
        }

        $('#spanPtkinTable').DataTable({
            pageLength: 25,
            order: [[5, 'desc'], [2, 'asc']],
            language: {
                url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/id.json'
            }
        });

        @if($previewImport)
        $('#spanPtkinPreviewTable').DataTable({
            pageLength: 10,
            order: [[5, 'asc'], [1, 'asc']],
            language: {
                url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/id.json'
            }
        });
        @endif
    });
</script>
@stop
