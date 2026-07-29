@extends('adminlte::page')

@section('title', 'NIS Lokal Siswa')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center flex-wrap">
        <div>
            <h1 class="mb-1"><i class="fas fa-id-card mr-2"></i>NIS Lokal Siswa</h1>
            <p class="text-muted mb-0">Penerbitan tingkat 10 dan pembaruan data lama tingkat 11/12.</p>
        </div>
        <a href="{{ route('admin.settings.edit') }}" class="btn btn-outline-secondary">
            <i class="fas fa-school mr-1"></i>Pengaturan Sekolah
        </a>
    </div>
@stop

@section('content')
    <div class="row">
        <div class="col-md-4">
            <div class="info-box">
                <span class="info-box-icon bg-primary"><i class="fas fa-university"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">NSM Madrasah</span>
                    <span class="info-box-number">{{ $setting->nsm ?: 'Belum diatur' }}</span>
                    <small>NPSN: {{ $setting->npsn ?: '-' }}</small>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="info-box">
                <span class="info-box-icon bg-info"><i class="fas fa-calendar-alt"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Tahun Angkatan Aktif</span>
                    <span class="info-box-number">{{ $activeYear?->nama ?: 'Belum tersedia' }}</span>
                    <small>Kode tahun: {{ $activeYear ? substr((string) $activeYear->tahun_mulai, -2) : '-' }}</small>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="info-box">
                <span class="info-box-icon bg-success"><i class="fas fa-user-graduate"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Kandidat Tingkat 10</span>
                    <span class="info-box-number">{{ number_format($generator['total'] ?? 0) }}</span>
                    <small>{{ number_format($generator['missing'] ?? 0) }} belum memiliki NIS Lokal</small>
                </div>
            </div>
        </div>
    </div>

    @if($generatorError)
        <div class="alert alert-warning">
            <i class="fas fa-exclamation-triangle mr-1"></i>{{ $generatorError }}
        </div>
    @endif

    <div class="card card-outline card-primary">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-magic mr-2"></i>Generator NIS Lokal Tingkat 10</h3>
        </div>
        <div class="card-body">
            <div class="alert alert-light border">
                <strong>Urutan resmi:</strong> rombel X-1 sampai X-13, kemudian nama siswa A–Z pada setiap rombel.
                Saat disimpan, nomor absen setiap rombel ikut disinkronkan dengan urutan nama yang sama.
            </div>
            <div class="row align-items-center">
                <div class="col-lg-8">
                    <p class="mb-1">Format: <code>NSM + 2 digit tahun masuk + 4 digit nomor urut</code>.</p>
                    <small class="text-muted">Preview berlaku 30 menit. NIS yang sudah diterbitkan tidak akan diterbitkan ulang.</small>
                </div>
                <div class="col-lg-4 text-lg-right mt-3 mt-lg-0">
                    <button type="button" class="btn btn-primary" id="btnGeneratorPreview"
                            {{ $activeYear && $setting->nsm ? '' : 'disabled' }}>
                        <i class="fas fa-eye mr-1"></i>Preview Generator
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="card card-outline card-info">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-file-excel mr-2"></i>Update NIS Lokal Tingkat 11/12</h3>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-lg-7">
                    <p class="mb-2">Unggah Excel dengan kolom tepat seperti template berikut:</p>
                    <code>nislokal, nisn, namalengkap</code>
                    <p class="text-muted small mt-2 mb-0">
                        Sistem mengutamakan NISN, memeriksa kesesuaian nama, lalu memakai deteksi nama pintar untuk
                        singkatan atau perbedaan penulisan. Tidak ada data disimpan sebelum Anda menyetujui preview.
                    </p>
                </div>
                <div class="col-lg-5 mt-3 mt-lg-0">
                    <div class="nis-upload-panel">
                        <label class="nis-upload-zone" id="nisUploadZone" for="nisImportFile">
                            <input type="file" class="sr-only" id="nisImportFile" accept=".xlsx,.xls">
                            <span class="nis-upload-zone__icon" aria-hidden="true">
                                <i class="fas fa-file-excel"></i>
                            </span>
                            <span class="nis-upload-zone__content">
                                <span class="nis-upload-zone__eyebrow">FILE EXCEL NIS LOKAL</span>
                                <strong id="nisUploadTitle">Tarik file ke area ini</strong>
                                <small id="nisUploadHint">atau klik untuk memilih file .xlsx/.xls, maksimal 5 MB</small>
                            </span>
                            <span class="nis-upload-zone__browse">
                                <i class="fas fa-folder-open mr-1"></i>Pilih File
                            </span>
                        </label>

                        <div class="nis-upload-selected d-none" id="nisUploadSelected" aria-live="polite">
                            <span class="nis-upload-selected__icon"><i class="fas fa-check"></i></span>
                            <span class="nis-upload-selected__data">
                                <strong id="nisUploadFileName"></strong>
                                <small id="nisUploadFileMeta"></small>
                            </span>
                            <button type="button" class="nis-upload-selected__remove" id="btnClearImportFile"
                                    aria-label="Hapus file terpilih" title="Hapus file">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>

                        <div class="nis-upload-actions">
                            <a href="{{ route('admin.nis-lokal.template') }}"
                               class="btn btn-outline-success"
                               data-no-overlay
                               download="template-update-nis-lokal.xlsx">
                                <i class="fas fa-download mr-1"></i>Unduh Template
                            </a>
                            <button type="button" class="btn btn-info" id="btnImportPreview" disabled>
                                <i class="fas fa-cloud-upload-alt mr-1"></i>Upload & Preview
                            </button>
                        </div>

                        <div id="uploadProgressWrap" class="nis-upload-progress d-none">
                            <div class="d-flex justify-content-between small mb-2">
                                <span id="uploadProgressText"><i class="fas fa-spinner fa-spin mr-1"></i>Mengunggah file...</span>
                                <strong id="uploadProgressPercent">0%</strong>
                            </div>
                            <div class="progress">
                                <div class="progress-bar progress-bar-striped progress-bar-animated" id="uploadProgress"
                                     role="progressbar" style="width: 0%"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="generatorPreviewModal" tabindex="-1">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-list-ol mr-2"></i>Preview Penerbitan NIS Lokal</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div id="generatorSummary" class="mb-3"></div>
                    <div class="table-responsive preview-table-wrap">
                        <table class="table table-sm table-bordered table-hover">
                            <thead class="thead-light">
                            <tr>
                                <th>Urut</th>
                                <th>Rombel</th>
                                <th>Absen</th>
                                <th>Nama / NISN</th>
                                <th>NIS Lokal</th>
                                <th>Status</th>
                            </tr>
                            </thead>
                            <tbody id="generatorRows"></tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-primary" id="btnConfirmGenerator">
                        <i class="fas fa-check mr-1"></i>Terbitkan NIS Lokal
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="importPreviewModal" tabindex="-1">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-file-import mr-2"></i>Live Preview Update NIS Lokal</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div id="importSummary" class="mb-3"></div>
                    <div class="table-responsive preview-table-wrap">
                        <table class="table table-sm table-bordered table-hover">
                            <thead class="thead-light">
                            <tr>
                                <th>Baris</th>
                                <th>Data Excel</th>
                                <th>Hasil Pencocokan</th>
                                <th>Kelas</th>
                                <th>Aksi</th>
                                <th>Status</th>
                            </tr>
                            </thead>
                            <tbody id="importRows"></tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-info" id="btnConfirmImport">
                        <i class="fas fa-save mr-1"></i>Simpan Baris Siap
                    </button>
                </div>
            </div>
        </div>
    </div>
@stop

@section('css')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <style>
        .preview-table-wrap { max-height: 60vh; }
        .preview-table-wrap thead th { position: sticky; top: 0; z-index: 2; }
        .student-meta { line-height: 1.2; }
        .student-meta small { display: block; color: #6c757d; }
        code { color: #0056b3; }
        .nis-upload-panel {
            padding: .85rem;
            border: 1px solid #dbe5f1;
            border-radius: 14px;
            background: linear-gradient(145deg, #fbfdff 0%, #f5f9ff 100%);
            box-shadow: 0 10px 28px rgba(30, 64, 175, .06);
        }
        .nis-upload-zone {
            display: flex;
            align-items: center;
            gap: .8rem;
            min-height: 108px;
            margin: 0;
            padding: .9rem;
            border: 2px dashed #9bb9de;
            border-radius: 11px;
            background: rgba(255, 255, 255, .86);
            cursor: pointer;
            transition: border-color .18s ease, background .18s ease, box-shadow .18s ease, transform .18s ease;
        }
        .nis-upload-zone:hover,
        .nis-upload-zone:focus-within,
        .nis-upload-zone.is-dragging {
            border-color: #17a2b8;
            background: #f0fbfd;
            box-shadow: 0 0 0 4px rgba(23, 162, 184, .1);
            transform: translateY(-1px);
        }
        .nis-upload-zone.is-busy {
            pointer-events: none;
            opacity: .68;
        }
        .nis-upload-zone__icon {
            display: inline-flex;
            flex: 0 0 46px;
            width: 46px;
            height: 46px;
            align-items: center;
            justify-content: center;
            border-radius: 13px;
            color: #178447;
            background: #e8f8ef;
            font-size: 1.45rem;
        }
        .nis-upload-zone__content {
            display: flex;
            min-width: 0;
            flex: 1;
            flex-direction: column;
        }
        .nis-upload-zone__eyebrow {
            margin-bottom: .15rem;
            color: #64748b;
            font-size: .65rem;
            font-weight: 800;
            letter-spacing: .08em;
        }
        .nis-upload-zone__content strong {
            color: #17345f;
            font-size: .93rem;
        }
        .nis-upload-zone__content small {
            margin-top: .18rem;
            color: #718096;
            line-height: 1.35;
        }
        .nis-upload-zone__browse {
            flex: 0 0 auto;
            padding: .48rem .68rem;
            border-radius: 8px;
            color: #fff;
            background: #17a2b8;
            font-size: .78rem;
            font-weight: 700;
            box-shadow: 0 5px 12px rgba(23, 162, 184, .2);
        }
        .nis-upload-selected {
            display: flex;
            align-items: center;
            gap: .65rem;
            margin-top: .65rem;
            padding: .65rem .75rem;
            border: 1px solid #bde6cc;
            border-radius: 10px;
            background: #f0fbf4;
        }
        .nis-upload-selected__icon {
            display: inline-flex;
            flex: 0 0 30px;
            width: 30px;
            height: 30px;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            color: #fff;
            background: #28a745;
        }
        .nis-upload-selected__data {
            display: flex;
            min-width: 0;
            flex: 1;
            flex-direction: column;
        }
        .nis-upload-selected__data strong {
            overflow: hidden;
            color: #195b32;
            font-size: .84rem;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        .nis-upload-selected__data small { color: #5f7868; }
        .nis-upload-selected__remove {
            width: 30px;
            height: 30px;
            border: 0;
            border-radius: 8px;
            color: #8c3a45;
            background: #fff;
            transition: background .15s ease, color .15s ease;
        }
        .nis-upload-selected__remove:hover { color: #fff; background: #dc3545; }
        .nis-upload-actions {
            display: flex;
            justify-content: space-between;
            gap: .65rem;
            margin-top: .75rem;
        }
        .nis-upload-actions .btn {
            flex: 1;
            border-radius: 9px;
            font-size: .82rem;
            font-weight: 700;
        }
        .nis-upload-progress {
            margin-top: .75rem;
            padding: .7rem .8rem;
            border-radius: 10px;
            color: #28527d;
            background: #eaf4ff;
        }
        .nis-upload-progress .progress {
            height: 7px;
            border-radius: 999px;
            background: #d5e5f7;
        }
        .nis-upload-progress .progress-bar { border-radius: 999px; }
        @media (max-width: 575.98px) {
            .nis-upload-zone { align-items: flex-start; flex-wrap: wrap; }
            .nis-upload-zone__content { flex-basis: calc(100% - 60px); }
            .nis-upload-zone__browse { width: 100%; text-align: center; }
            .nis-upload-actions { flex-direction: column-reverse; }
        }
    </style>
@stop

@section('js')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        (() => {
            const csrf = @json(csrf_token());
            let generatorToken = null;
            let importToken = null;
            let importReady = 0;
            const escapeHtml = value => $('<div>').text(value ?? '').html();

            function errorMessage(xhr) {
                return xhr.responseJSON?.message
                    || Object.values(xhr.responseJSON?.errors || {})?.[0]?.[0]
                    || 'Proses belum berhasil. Silakan periksa data dan coba lagi.';
            }

            $('#btnGeneratorPreview').on('click', function () {
                const button = $(this).prop('disabled', true)
                    .html('<i class="fas fa-spinner fa-spin mr-1"></i>Menyusun preview...');
                $.post(@json(route('admin.nis-lokal.generator.preview')), {_token: csrf})
                    .done(response => {
                        const data = response.data;
                        generatorToken = data.token;
                        $('#generatorSummary').html(`
                            <div class="alert alert-primary mb-0">
                                <strong>${data.missing} NIS akan diterbitkan</strong> dari ${data.total} siswa,
                                ${data.classes.length} rombel. Nomor dimulai setelah ${String(data.last_number).padStart(4, '0')}.
                            </div>`);
                        $('#generatorRows').html(data.rows.map((row, index) => `
                            <tr>
                                <td>${index + 1}</td>
                                <td><span class="badge badge-light border">${escapeHtml(row.class_name)}</span></td>
                                <td class="text-center">${row.attendance_number}</td>
                                <td class="student-meta"><strong>${escapeHtml(row.name)}</strong><small>NISN: ${escapeHtml(row.nisn || '-')}</small></td>
                                <td><code>${escapeHtml(row.proposed_nis)}</code></td>
                                <td>${row.will_generate
                                    ? '<span class="badge badge-primary">Akan diterbitkan</span>'
                                    : '<span class="badge badge-success">Sudah ada</span>'}</td>
                            </tr>`).join(''));
                        $('#btnConfirmGenerator').prop('disabled', data.missing === 0);
                        $('#generatorPreviewModal').modal('show');
                    })
                    .fail(xhr => Swal.fire('Preview gagal', errorMessage(xhr), 'error'))
                    .always(() => button.prop('disabled', false)
                        .html('<i class="fas fa-eye mr-1"></i>Preview Generator'));
            });

            $('#btnConfirmGenerator').on('click', async function () {
                const confirmation = await Swal.fire({
                    title: 'Terbitkan NIS Lokal?',
                    html: 'Nomor akan menjadi identitas permanen. Nomor absen rombel tingkat 10 juga akan disusun ulang berdasarkan nama.',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, terbitkan',
                    cancelButtonText: 'Periksa lagi',
                    reverseButtons: true
                });
                if (!confirmation.isConfirmed) return;

                Swal.fire({title: 'Menerbitkan NIS Lokal', allowOutsideClick: false, didOpen: () => Swal.showLoading()});
                $.post(@json(route('admin.nis-lokal.generator.confirm')), {_token: csrf, token: generatorToken})
                    .done(response => Swal.fire('Berhasil', response.message, 'success').then(() => location.reload()))
                    .fail(xhr => Swal.fire('Gagal', errorMessage(xhr), 'error'));
            });

            const importInput = document.getElementById('nisImportFile');
            const uploadZone = document.getElementById('nisUploadZone');
            const maxImportSize = 5 * 1024 * 1024;

            function formatFileSize(bytes) {
                if (bytes < 1024 * 1024) return `${Math.max(1, Math.round(bytes / 1024))} KB`;
                return `${(bytes / 1024 / 1024).toFixed(2)} MB`;
            }

            function resetImportFile() {
                importInput.value = '';
                importToken = null;
                importReady = 0;
                $('#nisUploadSelected').addClass('d-none');
                $('#nisUploadTitle').text('Tarik file ke area ini');
                $('#nisUploadHint').text('atau klik untuk memilih file .xlsx/.xls, maksimal 5 MB');
                $('#btnImportPreview').prop('disabled', true);
                $('#uploadProgressWrap').addClass('d-none');
            }

            function showImportFile(file) {
                const extensionValid = /\.(xlsx|xls)$/i.test(file.name);
                if (!extensionValid || file.size > maxImportSize) {
                    resetImportFile();
                    Swal.fire(
                        'File tidak sesuai',
                        !extensionValid
                            ? 'Gunakan file Excel dengan ekstensi .xlsx atau .xls.'
                            : 'Ukuran file maksimal 5 MB.',
                        'warning'
                    );
                    return false;
                }

                $('#nisUploadFileName').text(file.name);
                $('#nisUploadFileMeta').text(`${formatFileSize(file.size)} · siap dipreview`);
                $('#nisUploadSelected').removeClass('d-none');
                $('#nisUploadTitle').text('File Excel siap');
                $('#nisUploadHint').text('Klik kembali untuk mengganti file');
                $('#btnImportPreview').prop('disabled', false);
                return true;
            }

            $('#nisImportFile').on('change', function () {
                const file = this.files[0];
                if (file) showImportFile(file);
                else resetImportFile();
            });

            $('#btnClearImportFile').on('click', function () {
                resetImportFile();
            });

            ['dragenter', 'dragover'].forEach(eventName => {
                uploadZone.addEventListener(eventName, event => {
                    event.preventDefault();
                    event.stopPropagation();
                    uploadZone.classList.add('is-dragging');
                });
            });
            ['dragleave', 'drop'].forEach(eventName => {
                uploadZone.addEventListener(eventName, event => {
                    event.preventDefault();
                    event.stopPropagation();
                    uploadZone.classList.remove('is-dragging');
                });
            });
            uploadZone.addEventListener('drop', event => {
                const file = event.dataTransfer.files[0];
                if (!file) return;

                const transfer = new DataTransfer();
                transfer.items.add(file);
                importInput.files = transfer.files;
                if (!showImportFile(file)) importInput.value = '';
            });

            $('#btnImportPreview').on('click', function () {
                const file = $('#nisImportFile')[0].files[0];
                if (!file) return;

                const button = $(this).prop('disabled', true)
                    .html('<i class="fas fa-spinner fa-spin mr-1"></i>Memproses...');
                $('#nisUploadZone').addClass('is-busy');
                const formData = new FormData();
                formData.append('_token', csrf);
                formData.append('file', file);
                $('#uploadProgressWrap').removeClass('d-none');
                $('#uploadProgress').css('width', '0%');
                $('#uploadProgressPercent').text('0%');
                $('#uploadProgressText').html('<i class="fas fa-spinner fa-spin mr-1"></i>Mengunggah file...');

                const xhr = new XMLHttpRequest();
                xhr.open('POST', @json(route('admin.nis-lokal.import.preview')));
                xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
                xhr.upload.onprogress = event => {
                    if (!event.lengthComputable) return;
                    const percent = Math.min(70, Math.round(event.loaded / event.total * 70));
                    $('#uploadProgress').css('width', percent + '%');
                    $('#uploadProgressPercent').text(percent + '%');
                };
                xhr.upload.onload = () => {
                    $('#uploadProgress').css('width', '85%');
                    $('#uploadProgressPercent').text('85%');
                    $('#uploadProgressText').text('Menganalisis NISN dan kemiripan nama...');
                };
                xhr.onload = () => {
                    let response = {};
                    try { response = JSON.parse(xhr.responseText); } catch (e) {}
                    if (xhr.status < 200 || xhr.status >= 300) {
                        $('#uploadProgressWrap').addClass('d-none');
                        Swal.fire('Preview gagal', response.message || 'File tidak dapat diproses.', 'error');
                        button.prop('disabled', false)
                            .html('<i class="fas fa-cloud-upload-alt mr-1"></i>Upload & Preview');
                        $('#nisUploadZone').removeClass('is-busy');
                        return;
                    }

                    $('#uploadProgress').css('width', '100%');
                    $('#uploadProgressPercent').text('100%');
                    $('#uploadProgressText').text('Preview siap.');
                    renderImportPreview(response.data);
                    setTimeout(() => $('#uploadProgressWrap').addClass('d-none'), 800);
                    button.prop('disabled', false)
                        .html('<i class="fas fa-cloud-upload-alt mr-1"></i>Upload & Preview');
                    $('#nisUploadZone').removeClass('is-busy');
                };
                xhr.onerror = () => {
                    $('#uploadProgressWrap').addClass('d-none');
                    Swal.fire('Koneksi gagal', 'Upload terputus. Silakan coba lagi.', 'error');
                    button.prop('disabled', false)
                        .html('<i class="fas fa-cloud-upload-alt mr-1"></i>Upload & Preview');
                    $('#nisUploadZone').removeClass('is-busy');
                };
                xhr.send(formData);
            });

            function renderImportPreview(data) {
                importToken = data.token;
                importReady = data.ready;
                $('#importSummary').html(`
                    <div class="row">
                        <div class="col-md-6"><div class="alert alert-success mb-2"><strong>${data.ready}</strong> baris siap disimpan</div></div>
                        <div class="col-md-6"><div class="alert alert-danger mb-2"><strong>${data.errors}</strong> baris perlu diperbaiki</div></div>
                    </div>`);
                $('#importRows').html(data.rows.map(row => {
                    const ready = row.status === 'ready';
                    return `<tr class="${ready ? '' : 'table-danger'}">
                        <td>${row.row}</td>
                        <td class="student-meta"><code>${escapeHtml(row.input_nis)}</code><small>${escapeHtml(row.input_nisn || '-')} · ${escapeHtml(row.input_name)}</small></td>
                        <td class="student-meta"><strong>${escapeHtml(row.matched_name || '-')}</strong><small>${escapeHtml(row.match_method || '-')} ${row.score !== null ? '· skor ' + row.score + '%' : ''}</small></td>
                        <td>${escapeHtml(row.class_name || '-')}</td>
                        <td>${escapeHtml(row.action || '-')}</td>
                        <td><span class="badge badge-${ready ? 'success' : 'danger'}">${ready ? 'Siap' : 'Periksa'}</span><small class="d-block mt-1">${escapeHtml(row.message)}</small></td>
                    </tr>`;
                }).join(''));
                $('#btnConfirmImport').prop('disabled', data.ready === 0);
                $('#importPreviewModal').modal('show');
            }

            $('#btnConfirmImport').on('click', async function () {
                const confirmation = await Swal.fire({
                    title: `Simpan ${importReady} baris siap?`,
                    text: 'Baris bermasalah tidak akan disimpan. Perubahan NIS lama akan tercatat pada log aktivitas.',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, simpan',
                    cancelButtonText: 'Batal',
                    reverseButtons: true
                });
                if (!confirmation.isConfirmed) return;

                Swal.fire({title: 'Menyimpan NIS Lokal', allowOutsideClick: false, didOpen: () => Swal.showLoading()});
                $.post(@json(route('admin.nis-lokal.import.confirm')), {_token: csrf, token: importToken})
                    .done(response => Swal.fire('Berhasil', response.message, 'success').then(() => location.reload()))
                    .fail(xhr => Swal.fire('Gagal', errorMessage(xhr), 'error'));
            });
        })();
    </script>
@stop
