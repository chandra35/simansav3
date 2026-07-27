@extends('adminlte::page')

@section('title', 'Download Foto Siswa')

@section('content_header')
    <div id="photoArchivePage" class="photo-archive-page">
        <div class="photo-archive-hero">
            <div>
                <div class="photo-archive-hero__eyebrow"><i class="fas fa-file-archive"></i> CETAK DOKUMEN</div>
                <h1>Download Foto Siswa</h1>
                <p>Pilih tingkat dan kelas, periksa preview, lalu unduh foto asli dalam satu ZIP yang rapi.</p>
            </div>
            <div class="photo-archive-year">
                <span>Tahun Pelajaran Aktif</span>
                <strong>{{ $activeYear?->nama ?? 'Belum tersedia' }}</strong>
                <small><i class="fas fa-lock"></i> Otomatis mengikuti tahun aktif</small>
            </div>
        </div>
    </div>
@stop

@section('content')
<div id="photoArchiveWorkspace" class="photo-archive-page">
    <div class="photo-flow mb-3">
        <div class="photo-flow__item is-active" data-flow="1"><span>1</span><div><strong>Pilih Kelas</strong><small>Filter tingkat aktif</small></div></div>
        <div class="photo-flow__line"></div>
        <div class="photo-flow__item" data-flow="2"><span>2</span><div><strong>Preview</strong><small>Periksa foto tersedia</small></div></div>
        <div class="photo-flow__line"></div>
        <div class="photo-flow__item" data-flow="3"><span>3</span><div><strong>Download ZIP</strong><small>File asli per kelas</small></div></div>
    </div>

    <div class="card photo-card">
        <div class="card-body">
            <div class="photo-section-heading">
                <div>
                    <span class="photo-section-heading__icon"><i class="fas fa-layer-group"></i></span>
                    <div>
                        <h2>Pilih Tingkat dan Kelas</h2>
                        <p>Kelas hanya diambil dari tahun pelajaran yang sedang aktif.</p>
                    </div>
                </div>
                <div class="photo-selection-count" id="selectionCount">0 kelas dipilih</div>
            </div>

            @if(!$activeYear)
                <div class="alert alert-warning mb-0">
                    <i class="fas fa-exclamation-triangle mr-1"></i>
                    Tahun pelajaran aktif belum tersedia. Aktifkan tahun pelajaran terlebih dahulu.
                </div>
            @else
                <div class="photo-filter">
                    <div class="form-group mb-0">
                        <label for="tingkatSelect">Tingkat <span class="text-danger">*</span></label>
                        <select id="tingkatSelect" class="form-control">
                            <option value="">Pilih tingkat dahulu</option>
                            @foreach($tingkatOptions as $value => $label)
                                <option value="{{ $value }}">Kelas {{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="photo-filter__hint">
                        <i class="fas fa-info-circle"></i>
                        Pilih satu tingkat untuk menampilkan rombel aktif.
                    </div>
                </div>

                <div id="classEmpty" class="photo-empty">
                    <span><i class="fas fa-hand-pointer"></i></span>
                    <strong>Mulai dengan memilih tingkat</strong>
                    <p>Daftar kelas akan tampil di sini dan dapat dicentang lebih dari satu.</p>
                </div>

                <div id="classLoading" class="photo-loading d-none">
                    <div class="spinner-border text-primary" role="status"></div>
                    <div><strong>Memuat kelas aktif...</strong><small>Mohon tunggu sebentar</small></div>
                </div>

                <div id="classPanel" class="d-none">
                    <div class="photo-class-toolbar">
                        <label class="photo-check-all">
                            <input type="checkbox" id="selectAllClasses">
                            <span>Pilih semua kelas</span>
                        </label>
                        <button type="button" class="btn btn-sm btn-outline-secondary" id="clearClasses">
                            <i class="fas fa-undo mr-1"></i> Bersihkan
                        </button>
                    </div>
                    <div id="classGrid" class="photo-class-grid"></div>
                </div>

                <div class="photo-actions">
                    <div class="photo-actions__note">
                        <i class="fas fa-shield-alt"></i>
                        Foto hanya dibaca dari penyimpanan dan tidak akan diubah.
                    </div>
                    <button type="button" id="previewButton" class="btn btn-primary btn-lg" disabled>
                        <i class="fas fa-images mr-1"></i> Preview Foto
                    </button>
                </div>
            @endif
        </div>
    </div>

    <div id="previewCard" class="card photo-card d-none">
        <div class="card-body">
            <div class="photo-section-heading">
                <div>
                    <span class="photo-section-heading__icon photo-section-heading__icon--green"><i class="fas fa-eye"></i></span>
                    <div>
                        <h2>Preview Data Foto</h2>
                        <p>Pastikan cakupan kelas dan ketersediaan foto sudah sesuai.</p>
                    </div>
                </div>
                <span class="badge badge-success px-3 py-2">Siap diproses</span>
            </div>

            <div class="photo-summary-grid">
                <div class="photo-summary"><span>Kelas Dipilih</span><strong id="summaryClasses">0</strong><small>rombel aktif</small></div>
                <div class="photo-summary"><span>Total Siswa</span><strong id="summaryStudents">0</strong><small>pada kelas dipilih</small></div>
                <div class="photo-summary photo-summary--success"><span>Foto Tersedia</span><strong id="summaryPhotos">0</strong><small>akan masuk ZIP</small></div>
                <div class="photo-summary photo-summary--danger"><span>Belum Ada Foto</span><strong id="summaryMissing">0</strong><small>tidak dimasukkan</small></div>
            </div>

            <div class="photo-preview-layout">
                <div>
                    <h3 class="photo-subtitle"><i class="fas fa-school"></i> Ringkasan per Kelas</h3>
                    <div id="classSummary" class="photo-class-summary"></div>
                </div>
                <div>
                    <h3 class="photo-subtitle"><i class="fas fa-camera"></i> Sampel Foto Siswa</h3>
                    <div id="studentPreview" class="photo-student-grid"></div>
                    <p id="previewLimitNote" class="photo-preview-note d-none">
                        Preview dibatasi 36 siswa agar halaman tetap ringan. Semua foto tersedia tetap masuk ZIP.
                    </p>
                </div>
            </div>

            <div class="photo-actions photo-actions--preview">
                <div class="photo-actions__note">
                    <i class="fas fa-folder-open"></i>
                    Struktur ZIP: <strong>Nama Kelas / NISN - Nama Siswa.ext</strong>
                </div>
                <button type="button" id="startArchiveButton" class="btn btn-success btn-lg">
                    <i class="fas fa-file-archive mr-1"></i> Proses dan Download ZIP
                </button>
            </div>
        </div>
    </div>

    <div class="modal fade photo-progress-modal" id="archiveProgressModal" tabindex="-1" role="dialog" data-backdrop="static" data-keyboard="false" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-body">
                    <div class="photo-progress-icon"><i class="fas fa-file-archive"></i></div>
                    <div class="photo-progress-kicker">MENYIAPKAN ARSIP FOTO</div>
                    <h3 id="progressTitle">Membuat file ZIP...</h3>
                    <p id="progressDetail">Mempersiapkan daftar foto siswa.</p>
                    <div class="photo-progress-track">
                        <div id="progressBar" class="photo-progress-bar" style="width: 0%"></div>
                    </div>
                    <div class="photo-progress-meta">
                        <strong id="progressPercentage">0%</strong>
                        <span id="progressCount">0 / 0 foto</span>
                    </div>
                    <div id="progressComplete" class="d-none">
                        <a href="#" id="downloadArchiveButton" class="btn btn-success btn-block btn-lg">
                            <i class="fas fa-download mr-1"></i> Download ZIP Sekarang
                        </a>
                        <button type="button" class="btn btn-link btn-block text-muted" data-dismiss="modal">Tutup</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@stop

@section('css')
<style>
.photo-archive-page{--pa-blue:#3f67f3;--pa-navy:#16213d;--pa-muted:#6e7e9e;--pa-line:#dbe4f2;--pa-bg:#f6f9fd;color:var(--pa-navy)}
.photo-archive-hero{display:flex;align-items:center;justify-content:space-between;gap:28px;padding:26px 30px;border-radius:22px;background:linear-gradient(125deg,#416cf3 0%,#3f7ed5 52%,#2d8c83 100%);color:#fff;box-shadow:0 16px 38px rgba(48,91,175,.18)}
.photo-archive-hero__eyebrow{font-size:.78rem;font-weight:800;letter-spacing:.06em;margin-bottom:8px}.photo-archive-hero h1{font-size:1.65rem;font-weight:800;margin:0 0 6px}.photo-archive-hero p{margin:0;opacity:.9;font-size:1rem}
.photo-archive-year{min-width:245px;padding:15px 18px;border:1px solid rgba(255,255,255,.28);background:rgba(255,255,255,.14);border-radius:16px;display:flex;flex-direction:column}.photo-archive-year span{font-size:.72rem;text-transform:uppercase;opacity:.82}.photo-archive-year strong{font-size:1.25rem;margin:2px 0}.photo-archive-year small{opacity:.85}
.photo-flow{display:flex;align-items:center;justify-content:center;padding:5px 10%}.photo-flow__item{display:flex;align-items:center;gap:10px;color:#8694ad}.photo-flow__item>span{width:34px;height:34px;border-radius:50%;display:grid;place-items:center;background:#e8edf6;font-weight:800}.photo-flow__item div{display:flex;flex-direction:column}.photo-flow__item strong{font-size:.86rem}.photo-flow__item small{font-size:.72rem}.photo-flow__item.is-active{color:var(--pa-blue)}.photo-flow__item.is-active>span{background:var(--pa-blue);color:#fff;box-shadow:0 6px 15px rgba(63,103,243,.25)}.photo-flow__line{height:2px;background:#dfe6f1;min-width:80px;max-width:180px;flex:1;margin:0 18px}
.photo-card{border:1px solid var(--pa-line);border-radius:20px;box-shadow:0 12px 32px rgba(30,58,110,.07);overflow:hidden}.photo-card .card-body{padding:24px}
.photo-section-heading{display:flex;align-items:center;justify-content:space-between;gap:20px;margin-bottom:22px}.photo-section-heading>div{display:flex;align-items:center;gap:13px}.photo-section-heading__icon{width:44px;height:44px;border-radius:13px;background:#edf2ff;color:var(--pa-blue);display:grid;place-items:center;font-size:1.1rem}.photo-section-heading__icon--green{background:#e9fbf3;color:#19a769}.photo-section-heading h2{font-size:1.2rem;font-weight:800;margin:0 0 3px}.photo-section-heading p{color:var(--pa-muted);margin:0}.photo-selection-count{padding:8px 13px;background:#f1f5ff;border-radius:20px;color:var(--pa-blue);font-size:.82rem;font-weight:700}
.photo-filter{display:grid;grid-template-columns:minmax(260px,420px) 1fr;align-items:end;gap:18px;background:var(--pa-bg);border:1px solid var(--pa-line);border-radius:15px;padding:17px}.photo-filter label{font-size:.82rem;font-weight:800;text-transform:uppercase;letter-spacing:.03em}.photo-filter__hint{color:var(--pa-muted);padding-bottom:9px}.photo-filter__hint i{color:var(--pa-blue)}
.photo-empty{min-height:190px;display:flex;flex-direction:column;align-items:center;justify-content:center;text-align:center;color:var(--pa-muted)}.photo-empty>span{width:58px;height:58px;border-radius:18px;background:#eef3ff;color:var(--pa-blue);display:grid;place-items:center;font-size:1.35rem;margin-bottom:12px}.photo-empty strong{color:var(--pa-navy);font-size:1rem}.photo-empty p{margin:4px 0 0}
.photo-loading{min-height:170px;align-items:center;justify-content:center;gap:16px}.photo-loading:not(.d-none){display:flex!important}.photo-loading div:last-child{display:flex;flex-direction:column}.photo-loading small{color:var(--pa-muted)}
.photo-class-toolbar{display:flex;align-items:center;justify-content:space-between;margin:18px 0 12px}.photo-check-all{display:flex;align-items:center;gap:9px;margin:0;font-weight:700}.photo-check-all input{width:18px;height:18px}.photo-class-grid{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:12px}.photo-class-option{position:relative;display:block;margin:0;cursor:pointer}.photo-class-option input{position:absolute;opacity:0;pointer-events:none}.photo-class-option__body{height:100%;padding:15px;border:1px solid var(--pa-line);border-radius:14px;background:#fff;display:flex;align-items:center;gap:12px;transition:.18s ease}.photo-class-option__check{width:25px;height:25px;border:2px solid #cbd6e8;border-radius:8px;display:grid;place-items:center;color:transparent;flex:0 0 auto}.photo-class-option__copy{display:flex;flex-direction:column;min-width:0}.photo-class-option__copy strong{font-size:.95rem;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}.photo-class-option__copy small{color:var(--pa-muted)}.photo-class-option input:checked+.photo-class-option__body{border-color:var(--pa-blue);background:#f2f5ff;box-shadow:0 7px 16px rgba(63,103,243,.1)}.photo-class-option input:checked+.photo-class-option__body .photo-class-option__check{border-color:var(--pa-blue);background:var(--pa-blue);color:#fff}
.photo-actions{display:flex;align-items:center;justify-content:space-between;gap:18px;border-top:1px solid var(--pa-line);margin-top:20px;padding-top:20px}.photo-actions__note{color:var(--pa-muted)}.photo-actions__note i{color:var(--pa-blue);margin-right:4px}.photo-actions .btn{border-radius:11px;font-weight:700;padding:10px 20px}
.photo-summary-grid{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:13px}.photo-summary{border:1px solid var(--pa-line);border-top:4px solid var(--pa-blue);border-radius:14px;padding:15px 17px}.photo-summary span{display:block;color:var(--pa-muted);font-size:.73rem;font-weight:800;text-transform:uppercase}.photo-summary strong{display:block;font-size:1.65rem;line-height:1.1;margin:5px 0;color:var(--pa-blue)}.photo-summary small{color:var(--pa-muted)}.photo-summary--success{border-top-color:#20b878}.photo-summary--success strong{color:#16985f}.photo-summary--danger{border-top-color:#ef5d69}.photo-summary--danger strong{color:#dc4050}
.photo-preview-layout{display:grid;grid-template-columns:minmax(300px,.75fr) minmax(500px,1.6fr);gap:25px;margin-top:25px}.photo-subtitle{font-size:.92rem;font-weight:800;margin:0 0 12px}.photo-subtitle i{color:var(--pa-blue);margin-right:5px}.photo-class-summary{display:flex;flex-direction:column;gap:8px}.photo-class-summary__row{display:grid;grid-template-columns:1fr auto;gap:12px;align-items:center;padding:12px 13px;border:1px solid var(--pa-line);border-radius:11px}.photo-class-summary__row strong{font-size:.88rem}.photo-class-summary__meta{display:flex;gap:8px;font-size:.72rem}.photo-class-summary__meta .ok{color:#15965f}.photo-class-summary__meta .missing{color:#d64552}
.photo-student-grid{display:grid;grid-template-columns:repeat(6,minmax(0,1fr));gap:9px}.photo-student{position:relative;min-width:0}.photo-student__image{aspect-ratio:1/1.18;border-radius:11px;background:#eff3f9;overflow:hidden;border:1px solid var(--pa-line);display:grid;place-items:center;color:#9aa8be}.photo-student__image img{width:100%;height:100%;object-fit:cover}.photo-student__name{font-weight:700;font-size:.68rem;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;margin-top:5px}.photo-student__meta{color:var(--pa-muted);font-size:.62rem;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}.photo-student__missing{position:absolute;top:5px;right:5px;background:#fff0f1;color:#d94150;border-radius:8px;padding:3px 5px;font-size:.58rem;font-weight:700}.photo-preview-note{font-size:.75rem;color:var(--pa-muted);margin:10px 0 0}
.photo-progress-modal .modal-content{border:0;border-radius:22px;box-shadow:0 25px 70px rgba(15,31,66,.28)}.photo-progress-modal .modal-body{text-align:center;padding:34px}.photo-progress-icon{width:70px;height:70px;border-radius:22px;background:linear-gradient(135deg,#416cf3,#7359e8);color:#fff;display:grid;place-items:center;margin:0 auto 15px;font-size:1.7rem;box-shadow:0 12px 25px rgba(72,91,220,.28)}.photo-progress-kicker{color:var(--pa-blue);font-weight:800;font-size:.7rem;letter-spacing:.08em}.photo-progress-modal h3{font-size:1.25rem;font-weight:800;margin:7px 0}.photo-progress-modal p{color:var(--pa-muted);min-height:22px}.photo-progress-track{height:12px;background:#e9eef7;border-radius:8px;overflow:hidden;margin-top:20px}.photo-progress-bar{height:100%;border-radius:8px;background:linear-gradient(90deg,#416cf3,#35b98a);transition:width .25s ease}.photo-progress-meta{display:flex;justify-content:space-between;margin:8px 0 22px;color:var(--pa-muted)}.photo-progress-meta strong{color:var(--pa-blue)}
@media(max-width:1199px){.photo-class-grid{grid-template-columns:repeat(3,minmax(0,1fr))}.photo-student-grid{grid-template-columns:repeat(4,minmax(0,1fr))}.photo-preview-layout{grid-template-columns:1fr 1.4fr}}
@media(max-width:767px){.photo-archive-hero{align-items:flex-start;flex-direction:column;padding:21px}.photo-archive-year{width:100%;min-width:0}.photo-flow{padding:4px 0}.photo-flow__item div{display:none}.photo-flow__line{min-width:20px;margin:0 8px}.photo-card .card-body{padding:17px}.photo-section-heading{align-items:flex-start}.photo-section-heading p{font-size:.82rem}.photo-filter{grid-template-columns:1fr}.photo-filter__hint{padding:0}.photo-class-grid{grid-template-columns:1fr 1fr}.photo-summary-grid{grid-template-columns:1fr 1fr}.photo-preview-layout{grid-template-columns:1fr}.photo-student-grid{grid-template-columns:repeat(4,minmax(0,1fr))}.photo-actions{align-items:stretch;flex-direction:column}.photo-actions .btn{width:100%}}
@media(max-width:420px){.photo-class-grid{grid-template-columns:1fr}.photo-student-grid{grid-template-columns:repeat(3,minmax(0,1fr))}.photo-summary-grid{grid-template-columns:1fr 1fr}.photo-summary{padding:12px}.photo-summary strong{font-size:1.35rem}}
</style>
@stop

@section('js')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const routes = {
        classes: @json(route('admin.cetak.download-foto-siswa.classes')),
        preview: @json(route('admin.cetak.download-foto-siswa.preview')),
        start: @json(route('admin.cetak.download-foto-siswa.archive.start')),
        processTemplate: @json(route('admin.cetak.download-foto-siswa.archive.process', ['token' => '__TOKEN__'])),
        downloadTemplate: @json(route('admin.cetak.download-foto-siswa.archive.download', ['token' => '__TOKEN__'])),
    };
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content || @json(csrf_token());
    const levelSelect = document.getElementById('tingkatSelect');
    if (!levelSelect) return;

    const classEmpty = document.getElementById('classEmpty');
    const classLoading = document.getElementById('classLoading');
    const classPanel = document.getElementById('classPanel');
    const classGrid = document.getElementById('classGrid');
    const previewButton = document.getElementById('previewButton');
    const selectAll = document.getElementById('selectAllClasses');
    const previewCard = document.getElementById('previewCard');
    let previewSignature = null;

    const escapeHtml = value => String(value ?? '').replace(/[&<>"']/g, char => ({
        '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;'
    }[char]));

    const selectedIds = () => Array.from(document.querySelectorAll('.class-checkbox:checked')).map(input => input.value);
    const signature = () => `${levelSelect.value}:${selectedIds().sort().join(',')}`;

    function showError(title, message) {
        if (window.Swal) {
            Swal.fire({ icon: 'error', title, text: message, confirmButtonColor: '#416cf3' });
        } else {
            alert(`${title}: ${message}`);
        }
    }

    async function jsonRequest(url, options = {}) {
        const response = await fetch(url, {
            credentials: 'same-origin',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrf,
                ...(options.headers || {}),
            },
            ...options,
        });
        const data = await response.json().catch(() => ({}));
        if (!response.ok || data.success === false) {
            throw new Error(data.message || Object.values(data.errors || {})?.flat()?.[0] || 'Permintaan tidak dapat diproses.');
        }
        return data;
    }

    function updateSelection() {
        const count = selectedIds().length;
        document.getElementById('selectionCount').textContent = `${count} kelas dipilih`;
        previewButton.disabled = count === 0;
        const all = document.querySelectorAll('.class-checkbox');
        selectAll.checked = all.length > 0 && count === all.length;
        selectAll.indeterminate = count > 0 && count < all.length;
        if (previewSignature !== signature()) {
            previewCard.classList.add('d-none');
            document.querySelector('[data-flow="2"]').classList.remove('is-active');
            document.querySelector('[data-flow="3"]').classList.remove('is-active');
        }
    }

    levelSelect.addEventListener('change', async function () {
        classGrid.innerHTML = '';
        classPanel.classList.add('d-none');
        classEmpty.classList.add('d-none');
        classLoading.classList.remove('d-none');
        previewCard.classList.add('d-none');
        previewSignature = null;
        updateSelection();

        if (!this.value) {
            classLoading.classList.add('d-none');
            classEmpty.classList.remove('d-none');
            return;
        }

        try {
            const result = await jsonRequest(`${routes.classes}?tingkat=${encodeURIComponent(this.value)}`, { method: 'GET' });
            classLoading.classList.add('d-none');
            if (!result.data.length) {
                classEmpty.innerHTML = '<span><i class="fas fa-users-slash"></i></span><strong>Tidak ada kelas aktif</strong><p>Belum ada rombel aktif pada tingkat ini.</p>';
                classEmpty.classList.remove('d-none');
                return;
            }
            classGrid.innerHTML = result.data.map(item => `
                <label class="photo-class-option">
                    <input type="checkbox" class="class-checkbox" value="${escapeHtml(item.id)}">
                    <span class="photo-class-option__body">
                        <span class="photo-class-option__check"><i class="fas fa-check"></i></span>
                        <span class="photo-class-option__copy">
                            <strong>${escapeHtml(item.name)}</strong>
                            <small>${Number(item.students).toLocaleString('id-ID')} siswa aktif</small>
                        </span>
                    </span>
                </label>
            `).join('');
            classPanel.classList.remove('d-none');
            document.querySelectorAll('.class-checkbox').forEach(input => input.addEventListener('change', updateSelection));
            updateSelection();
        } catch (error) {
            classLoading.classList.add('d-none');
            classEmpty.classList.remove('d-none');
            showError('Gagal Memuat Kelas', error.message);
        }
    });

    selectAll.addEventListener('change', function () {
        document.querySelectorAll('.class-checkbox').forEach(input => { input.checked = this.checked; });
        updateSelection();
    });
    document.getElementById('clearClasses').addEventListener('click', function () {
        document.querySelectorAll('.class-checkbox').forEach(input => { input.checked = false; });
        updateSelection();
    });

    previewButton.addEventListener('click', async function () {
        const original = this.innerHTML;
        this.disabled = true;
        this.innerHTML = '<span class="spinner-border spinner-border-sm mr-1"></span> Memuat Preview...';
        try {
            const result = await jsonRequest(routes.preview, {
                method: 'POST',
                body: JSON.stringify({ tingkat: Number(levelSelect.value), kelas_ids: selectedIds() }),
            });
            renderPreview(result.data);
            previewSignature = signature();
            previewCard.classList.remove('d-none');
            document.querySelector('[data-flow="2"]').classList.add('is-active');
            previewCard.scrollIntoView({ behavior: 'smooth', block: 'start' });
        } catch (error) {
            showError('Preview Gagal', error.message);
        } finally {
            this.disabled = selectedIds().length === 0;
            this.innerHTML = original;
        }
    });

    function renderPreview(data) {
        document.getElementById('summaryClasses').textContent = Number(data.summary.classes).toLocaleString('id-ID');
        document.getElementById('summaryStudents').textContent = Number(data.summary.students).toLocaleString('id-ID');
        document.getElementById('summaryPhotos').textContent = Number(data.summary.photos).toLocaleString('id-ID');
        document.getElementById('summaryMissing').textContent = Number(data.summary.missing).toLocaleString('id-ID');
        document.getElementById('startArchiveButton').disabled = Number(data.summary.photos) === 0;
        document.getElementById('classSummary').innerHTML = data.classes.map(item => `
            <div class="photo-class-summary__row">
                <strong>${escapeHtml(item.name)}</strong>
                <span class="photo-class-summary__meta">
                    <span class="ok"><i class="fas fa-check-circle"></i> ${item.photos}</span>
                    <span class="missing"><i class="fas fa-minus-circle"></i> ${item.missing}</span>
                </span>
            </div>
        `).join('');
        document.getElementById('studentPreview').innerHTML = data.students.map(student => `
            <div class="photo-student" title="${escapeHtml(student.name)}">
                <div class="photo-student__image">
                    ${student.has_photo
                        ? `<img src="${escapeHtml(student.photo_url)}" alt="${escapeHtml(student.name)}" loading="lazy">`
                        : '<i class="fas fa-user-slash"></i>'}
                </div>
                ${student.has_photo ? '' : '<span class="photo-student__missing">Kosong</span>'}
                <div class="photo-student__name">${escapeHtml(student.name)}</div>
                <div class="photo-student__meta">${escapeHtml(student.class_name)} · ${escapeHtml(student.nisn)}</div>
            </div>
        `).join('');
        document.getElementById('previewLimitNote').classList.toggle('d-none', !data.preview_limited);
    }

    document.getElementById('startArchiveButton').addEventListener('click', async function () {
        if (previewSignature !== signature()) {
            showError('Preview Perlu Diperbarui', 'Pilihan kelas berubah. Muat preview kembali sebelum membuat ZIP.');
            return;
        }

        let confirmed = true;
        if (window.Swal) {
            const result = await Swal.fire({
                icon: 'question',
                title: 'Proses foto menjadi ZIP?',
                html: `Sistem akan memasukkan <strong>${document.getElementById('summaryPhotos').textContent} foto asli</strong> dari kelas yang dipilih.`,
                showCancelButton: true,
                confirmButtonText: '<i class="fas fa-file-archive"></i> Ya, Proses',
                cancelButtonText: 'Batal',
                confirmButtonColor: '#20a86b',
            });
            confirmed = result.isConfirmed;
        }
        if (!confirmed) return;

        resetProgress();
        $('#archiveProgressModal').modal('show');
        try {
            const result = await jsonRequest(routes.start, {
                method: 'POST',
                body: JSON.stringify({ tingkat: Number(levelSelect.value), kelas_ids: selectedIds() }),
            });
            updateProgress(result.data);
            await processArchive(result.data.token);
        } catch (error) {
            $('#archiveProgressModal').modal('hide');
            showError('ZIP Gagal Dibuat', error.message);
        }
    });

    async function processArchive(token) {
        const url = routes.processTemplate.replace('__TOKEN__', encodeURIComponent(token));
        const result = await jsonRequest(url, { method: 'POST', body: '{}' });
        updateProgress(result.data);
        if (result.data.status !== 'completed') {
            await new Promise(resolve => setTimeout(resolve, 120));
            return processArchive(token);
        }

        const downloadUrl = routes.downloadTemplate.replace('__TOKEN__', encodeURIComponent(token));
        document.getElementById('progressTitle').textContent = 'ZIP siap diunduh';
        document.getElementById('progressDetail').textContent = `${result.data.total - result.data.failed} foto berhasil disiapkan${result.data.failed ? `, ${result.data.failed} gagal dibaca` : ''}.`;
        document.getElementById('progressComplete').classList.remove('d-none');
        document.getElementById('downloadArchiveButton').href = downloadUrl;
        document.querySelector('[data-flow="3"]').classList.add('is-active');
    }

    function resetProgress() {
        document.getElementById('progressTitle').textContent = 'Membuat file ZIP...';
        document.getElementById('progressDetail').textContent = 'Mempersiapkan daftar foto siswa.';
        document.getElementById('progressBar').style.width = '0%';
        document.getElementById('progressPercentage').textContent = '0%';
        document.getElementById('progressCount').textContent = '0 / 0 foto';
        document.getElementById('progressComplete').classList.add('d-none');
    }

    function updateProgress(data) {
        const percentage = Number(data.percentage || 0);
        document.getElementById('progressBar').style.width = `${percentage}%`;
        document.getElementById('progressPercentage').textContent = `${percentage}%`;
        document.getElementById('progressCount').textContent = `${Number(data.processed).toLocaleString('id-ID')} / ${Number(data.total).toLocaleString('id-ID')} foto`;
        if (data.current_student) {
            document.getElementById('progressDetail').textContent = `${data.current_class} · ${data.current_student}`;
        }
    }
});
</script>
@stop
