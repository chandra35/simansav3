@extends('adminlte::page')

@section('title', 'Proses Akhir Tahun')

@section('content_header')
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1><i class="fas fa-graduation-cap"></i> Proses Akhir Tahun Ajaran</h1>
        </div>
        <div class="col-sm-6">
            <div class="float-sm-right">
                <a href="{{ route('admin.tahun-pelajaran.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Tahun Pelajaran
                </a>
            </div>
        </div>
    </div>
@endsection

@section('content')
<div class="row">
    {{-- INFO TAHUN AKTIF --}}
    <div class="col-12 mb-3">
        @if($tahunAktif)
        <div class="alert alert-info alert-dismissible mb-0">
            <i class="fas fa-info-circle mr-1"></i>
            Tahun pelajaran aktif: <strong>{{ $tahunAktif->nama }}</strong>
            (Semester <strong>{{ $tahunAktif->semester_aktif }}</strong>) &mdash;
            Wizard ini membantu memproses akhir tahun: kelulusan kelas XII dan kenaikan kelas X→XI dan XI→XII.
        </div>
        @else
        <div class="alert alert-warning">
            <i class="fas fa-exclamation-triangle mr-1"></i>
            Belum ada tahun pelajaran aktif. Aktifkan tahun pelajaran terlebih dahulu.
        </div>
        @endif
    </div>
</div>

{{-- RINGKASAN STATISTIK --}}
<div class="row" id="stats-row">
    <div class="col-lg-3 col-6 mb-3">
        <div class="small-box bg-info">
            <div class="inner"><h3 id="stat-10">-</h3><p>Siswa Kelas X</p></div>
            <div class="icon"><i class="fas fa-user-graduate"></i></div>
        </div>
    </div>
    <div class="col-lg-3 col-6 mb-3">
        <div class="small-box bg-warning">
            <div class="inner"><h3 id="stat-11">-</h3><p>Siswa Kelas XI</p></div>
            <div class="icon"><i class="fas fa-user-graduate"></i></div>
        </div>
    </div>
    <div class="col-lg-3 col-6 mb-3">
        <div class="small-box bg-danger">
            <div class="inner"><h3 id="stat-12">-</h3><p>Siswa Kelas XII</p></div>
            <div class="icon"><i class="fas fa-user-graduate"></i></div>
        </div>
    </div>
    <div class="col-lg-3 col-6 mb-3">
        <div class="small-box bg-success">
            <div class="inner"><h3 id="stat-lulus">-</h3><p>Sudah Finalisasi Lulus</p></div>
            <div class="icon"><i class="fas fa-check-circle"></i></div>
        </div>
    </div>
</div>

{{-- STEP 1: KELULUSAN KELAS XII --}}
<div class="card card-danger card-outline">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-graduation-cap mr-2"></i>Langkah 1 — Kelulusan Kelas XII</h3>
        <div class="card-tools">
            <button type="button" class="btn btn-tool" data-card-widget="collapse">
                <i class="fas fa-minus"></i>
            </button>
        </div>
    </div>
    <div class="card-body">
        <div class="callout callout-info mb-3">
            <h6><i class="fas fa-info-circle mr-1"></i> Cara kerja</h6>
            <p class="mb-1">Langkah ini membaca status yang sudah di-set di halaman <strong>Pengumuman Kelulusan</strong>, lalu memfinalisasi record <code>siswa_kelas</code>:</p>
            <ul class="mb-0">
                <li><strong>Lulus / Lulus Bersyarat</strong> &rarr; <code>siswa_kelas.status = lulus</code></li>
                <li><strong>Tidak Lulus</strong> &rarr; <code>siswa_kelas.status = tinggal_kelas</code></li>
                <li>Siswa yang belum ada pengumuman kelulusan-nya <strong>dilewati</strong> — set dulu via halaman Pengumuman Kelulusan.</li>
            </ul>
        </div>

        {{-- Status summary --}}
        <div id="kelulusan-status" class="mb-3">
            <i class="fas fa-spinner fa-spin"></i> Memuat status...
        </div>

        <a href="{{ route('admin.kelulusan-pengumuman.index') }}" class="btn btn-outline-danger mb-3" target="_blank">
            <i class="fas fa-external-link-alt mr-1"></i> Buka Halaman Pengumuman Kelulusan
        </a>

        <div class="alert alert-info mb-3">
            <i class="fas fa-archive mr-1"></i> Siswa yang difinalisasi lulus otomatis dipindahkan dari daftar siswa aktif ke <strong>Modul Alumni</strong>, sedangkan seluruh histori kelasnya tetap tersimpan.
        </div>

        <button id="btn-proses-kelulusan" class="btn btn-danger" disabled>
            <i class="fas fa-graduation-cap mr-1"></i> Finalisasi Kelulusan Kelas XII
        </button>
        <div id="result-kelulusan" class="mt-3"></div>
    </div>
</div>

{{-- STEP 2: NAIK KELAS --}}
<div class="card card-warning card-outline">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-arrow-up mr-2"></i>Langkah 2 — Naik Kelas (X→XI dan XI→XII)</h3>
        <div class="card-tools">
            <button type="button" class="btn btn-tool" data-card-widget="collapse">
                <i class="fas fa-minus"></i>
            </button>
        </div>
    </div>
    <div class="card-body">
        <p class="text-muted">
            Proses ini menandai siswa kelas X dan XI sebagai <code>naik_kelas</code> pada tahun pelajaran asal.
            Sistem membuat record aktif di tahun tujuan sesuai tingkat baru, tanpa rombel. Rombel baru diisi kemudian lewat assignment kelas.
        </p>

        <div class="row mb-3">
            <div class="col-md-4">
                <div class="form-group">
                    <label><strong>Tahun Pelajaran Asal</strong></label>
                    <select id="tahun-asal" class="form-control">
                        <option value="">-- Pilih Tahun Asal --</option>
                        @foreach($semuaTahun as $tp)
                            <option value="{{ $tp->id }}" {{ $tahunAktif && $tp->id === $tahunAktif->id ? 'selected' : '' }}>
                                {{ $tp->nama }} {{ $tp->is_active ? '(Aktif)' : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label><strong>Tahun Pelajaran Tujuan</strong></label>
                    <select id="tahun-tujuan" class="form-control">
                        <option value="">-- Pilih Tahun Tujuan --</option>
                        @foreach($semuaTahun as $tp)
                            <option value="{{ $tp->id }}">{{ $tp->nama }} {{ $tp->is_active ? '(Aktif)' : '' }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label><strong>Tanggal Naik Tingkat</strong></label>
                    <input type="date" id="tanggal-masuk" class="form-control" value="{{ now()->format('Y-m-d') }}">
                </div>
            </div>
        </div>

        <button id="btn-load-mapping" class="btn btn-info mb-3" disabled>
            <i class="fas fa-sync-alt mr-1"></i> Muat Preview Kenaikan
        </button>

        {{-- Preview kenaikan tingkat --}}
        <div id="mapping-container" class="d-none">
            <h6 class="font-weight-bold mb-2">Preview Kenaikan Tingkat</h6>
            <p class="text-muted small">
                Semua siswa aktif pada kelas X dan XI akan dibuat aktif pada tingkat baru tanpa rombel. Rombel tahun baru diisi kemudian melalui assignment kelas.
            </p>
            <div class="table-responsive">
                <table class="table table-sm table-bordered">
                    <thead class="thead-light">
                        <tr>
                            <th>Kelas Asal (Tahun Asal)</th>
                            <th>Tingkat</th>
                            <th>Jml Siswa Aktif</th>
                            <th>Status Proses</th>
                        </tr>
                    </thead>
                    <tbody id="mapping-tbody"></tbody>
                </table>
            </div>
            <button id="btn-proses-naik-kelas" class="btn btn-warning mt-2">
                <i class="fas fa-arrow-up mr-1"></i> Proses Naik Kelas
            </button>
            <div id="result-naik-kelas" class="mt-3"></div>
        </div>
    </div>
</div>

{{-- STEP 3: CATATAN --}}
<div class="card card-secondary card-outline">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-info-circle mr-2"></i>Langkah 3 — Setelah Proses</h3>
        <div class="card-tools">
            <button type="button" class="btn btn-tool" data-card-widget="collapse">
                <i class="fas fa-minus"></i>
            </button>
        </div>
    </div>
    <div class="card-body">
        <p class="mb-2">Setelah proses naik kelas selesai, lakukan langkah-langkah berikut secara manual:</p>
        <ol>
            <li>
                <strong>Arsipkan tahun pelajaran lama</strong> — Buka halaman
                <a href="{{ route('admin.tahun-pelajaran.index') }}">Tahun Pelajaran</a>,
                set status tahun lama menjadi <code>selesai</code>, dan aktifkan tahun baru.
            </li>
            <li>
                <strong>Jadwal pelajaran</strong> — Salin jadwal dari tahun sebelumnya via tombol
                <strong>Copy Jadwal</strong> di halaman
                <a href="{{ route('admin.jadwal-pelajaran.index') }}">Jadwal Pelajaran</a>,
                atau buat jadwal baru secara manual.
            </li>
            <li>
                <strong>Wali kelas</strong> — Assign ulang wali kelas di halaman
                <a href="{{ route('admin.kelas.index') }}">Manajemen Kelas</a>.
            </li>
            <li>
                <strong>Siswa baru (PPDB)</strong> — Import siswa kelas X baru via
                <a href="{{ route('admin.siswa.import') }}">Import Siswa</a>.
            </li>
            <li>
                <strong>Verifikasi</strong> — Cek daftar siswa per kelas di halaman
                <a href="{{ route('admin.kelas.index') }}">Kelas</a>
                untuk memastikan semua siswa sudah terpindahkan dengan benar.
            </li>
        </ol>
    </div>
</div>

{{-- MODAL KONFIRMASI --}}
<div class="modal fade" id="actionConfirmModal" tabindex="-1" role="dialog" aria-labelledby="actionConfirmTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content action-confirm-modal">
            <div class="modal-header">
                <div class="d-flex align-items-center">
                    <span class="action-confirm-icon mr-3" id="actionConfirmIcon">
                        <i class="fas fa-question"></i>
                    </span>
                    <div>
                        <h5 class="modal-title mb-1" id="actionConfirmTitle">Konfirmasi</h5>
                        <div class="text-muted small" id="actionConfirmSubtitle">Periksa kembali sebelum melanjutkan.</div>
                    </div>
                </div>
            </div>
            <div class="modal-body">
                <div id="actionConfirmBody"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" id="actionConfirmCancel" data-dismiss="modal">
                    <i class="fas fa-times mr-1"></i> Batal
                </button>
                <button type="button" class="btn btn-primary" id="actionConfirmOk">
                    <i class="fas fa-check mr-1"></i> Lanjutkan
                </button>
            </div>
        </div>
    </div>
</div>

{{-- MODAL PESAN --}}
<div class="modal fade" id="appMessageModal" tabindex="-1" role="dialog" aria-labelledby="appMessageTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content action-confirm-modal">
            <div class="modal-header">
                <div class="d-flex align-items-center">
                    <span class="action-confirm-icon mr-3" id="appMessageIcon">
                        <i class="fas fa-info"></i>
                    </span>
                    <div>
                        <h5 class="modal-title mb-1" id="appMessageTitle">Informasi</h5>
                        <div class="text-muted small" id="appMessageSubtitle">SIMANSA</div>
                    </div>
                </div>
            </div>
            <div class="modal-body" id="appMessageBody"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" data-dismiss="modal">
                    <i class="fas fa-check mr-1"></i> Mengerti
                </button>
            </div>
        </div>
    </div>
</div>

{{-- MODAL PROGRESS PROSES --}}
<div class="modal fade" id="processProgressModal" tabindex="-1" role="dialog" aria-labelledby="processProgressTitle" aria-hidden="true" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content process-modal">
            <div class="modal-header">
                <div>
                    <h5 class="modal-title mb-1" id="processProgressTitle">Memproses</h5>
                    <div class="text-muted small" id="processProgressSubtitle">Mohon tunggu sampai proses selesai.</div>
                </div>
                <span class="badge badge-info align-self-start" id="processProgressBadge">Berjalan</span>
            </div>
            <div class="modal-body">
                <div class="d-flex align-items-center mb-3">
                    <div class="process-icon mr-3" id="processProgressIcon">
                        <i class="fas fa-spinner fa-spin"></i>
                    </div>
                    <div class="flex-fill">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <strong id="processProgressLabel">Menyiapkan proses...</strong>
                            <span class="text-muted small" id="processProgressPercent">0%</span>
                        </div>
                        <div class="progress process-progress">
                            <div class="progress-bar progress-bar-striped progress-bar-animated" id="processProgressBar" role="progressbar" style="width: 0%"></div>
                        </div>
                    </div>
                </div>

                <div class="process-steps" id="processProgressSteps"></div>

                <div class="alert mt-3 mb-0 d-none" id="processProgressResult"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary d-none" id="processProgressClose" data-dismiss="modal">
                    <i class="fas fa-times mr-1"></i> Tutup
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('css')
<style>
.kelas-checkbox-label {
    display: inline-flex;
    align-items: center;
    gap: .3rem;
    padding: .25rem .6rem;
    border: 1px solid #dee2e6;
    border-radius: .25rem;
    cursor: pointer;
    user-select: none;
    font-size: .85rem;
    background: #fff;
    transition: all .15s;
}
.kelas-checkbox-label:hover { background: #f8f9fa; }
.kelas-checkbox-label input:checked ~ span { font-weight: 600; }
.kelas-checkbox-label:has(input:checked) {
    background: #fff3cd;
    border-color: #ffc107;
}
.action-confirm-modal {
    border: 0;
    border-radius: .5rem;
    overflow: hidden;
}
.action-confirm-modal .modal-header {
    background: #f8fafc;
    border-bottom-color: #e5e7eb;
}
.action-confirm-icon {
    width: 46px;
    height: 46px;
    border-radius: .5rem;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    color: #fff;
    background: #17a2b8;
    flex: 0 0 46px;
}
.action-confirm-icon.is-warning { background: #ffc107; color: #1f2937; }
.action-confirm-icon.is-danger { background: #dc3545; }
.action-confirm-icon.is-info { background: #17a2b8; }
.action-confirm-icon.is-success { background: #28a745; }
.action-confirm-list {
    margin: .75rem 0 0;
    padding-left: 1.2rem;
}
.action-confirm-list li + li {
    margin-top: .35rem;
}
.process-modal {
    border: 0;
    border-radius: .5rem;
    overflow: hidden;
}
.process-modal .modal-header {
    background: #f8fafc;
    border-bottom-color: #e5e7eb;
}
.process-icon {
    width: 52px;
    height: 52px;
    border-radius: .5rem;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 1.4rem;
    color: #fff;
    background: #17a2b8;
    flex: 0 0 52px;
}
.process-icon.is-success { background: #28a745; }
.process-icon.is-danger { background: #dc3545; }
.process-progress {
    height: .75rem;
    border-radius: 999px;
}
.process-steps {
    border: 1px solid #e5e7eb;
    border-radius: .4rem;
    overflow: hidden;
}
.process-step {
    display: flex;
    align-items: center;
    gap: .75rem;
    padding: .75rem .9rem;
    background: #fff;
    border-bottom: 1px solid #eef2f7;
}
.process-step:last-child { border-bottom: 0; }
.process-step-icon {
    width: 30px;
    height: 30px;
    border-radius: 50%;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    color: #6c757d;
    background: #eef2f7;
    flex: 0 0 30px;
}
.process-step.is-active {
    background: #f8fbff;
}
.process-step.is-active .process-step-icon {
    color: #fff;
    background: #17a2b8;
}
.process-step.is-done .process-step-icon {
    color: #fff;
    background: #28a745;
}
.process-step.is-error .process-step-icon {
    color: #fff;
    background: #dc3545;
}
.process-step-text {
    min-width: 0;
}
.process-step-title {
    font-weight: 600;
    line-height: 1.2;
}
.process-step-note {
    font-size: .82rem;
    color: #6c757d;
}
</style>
@endsection

@section('js')
<script>
(function () {
    'use strict';

    const tahunAktifId = @json(optional($tahunAktif)->id);
    const csrfToken   = document.querySelector('meta[name="csrf-token"]').content;

    // --- UTIL ---
    function alertBox(html, type) {
        return `<div class="alert alert-${type} alert-dismissible">
            <button type="button" class="close" data-dismiss="alert">&times;</button>${html}
        </div>`;
    }

    function esc(s) {
        const d = document.createElement('div');
        d.textContent = String(s ?? '');
        return d.innerHTML;
    }

    async function parseJsonResponse(response) {
        const contentType = response.headers.get('content-type') || '';
        const payload = contentType.includes('application/json') ? await response.json() : null;
        if (!response.ok) {
            throw new Error(payload?.message || payload?.error || `HTTP ${response.status}`);
        }
        return payload || {};
    }

    function openActionConfirm({ title, subtitle, bodyHtml, confirmText = 'Lanjutkan', confirmClass = 'btn-primary', icon = 'fa-question', tone = 'info' }) {
        return new Promise(resolve => {
            const modal = $('#actionConfirmModal');
            const okBtn = document.getElementById('actionConfirmOk');
            const cancelBtn = document.getElementById('actionConfirmCancel');
            const iconEl = document.getElementById('actionConfirmIcon');

            document.getElementById('actionConfirmTitle').textContent = title;
            document.getElementById('actionConfirmSubtitle').textContent = subtitle;
            document.getElementById('actionConfirmBody').innerHTML = bodyHtml;
            iconEl.className = `action-confirm-icon mr-3 is-${tone}`;
            iconEl.innerHTML = `<i class="fas ${icon}"></i>`;
            okBtn.className = `btn ${confirmClass}`;
            okBtn.innerHTML = `<i class="fas fa-check mr-1"></i> ${esc(confirmText)}`;

            const cleanup = () => {
                okBtn.removeEventListener('click', onOk);
                cancelBtn.removeEventListener('click', onCancel);
                modal.off('hidden.bs.modal', onHidden);
            };
            const close = result => {
                cleanup();
                modal.data('confirmed', result);
                modal.modal('hide');
                resolve(result);
            };
            const onOk = () => close(true);
            const onCancel = () => close(false);
            const onHidden = () => {
                const confirmed = modal.data('confirmed') === true;
                cleanup();
                modal.removeData('confirmed');
                resolve(confirmed);
            };

            modal.removeData('confirmed');
            okBtn.addEventListener('click', onOk);
            cancelBtn.addEventListener('click', onCancel);
            modal.on('hidden.bs.modal', onHidden);
            modal.modal({ backdrop: 'static', keyboard: false, show: true });
        });
    }

    function showAppMessage({ title = 'Informasi', subtitle = 'SIMANSA', message, type = 'info' }) {
        const iconMap = {
            info: 'fa-info',
            warning: 'fa-exclamation-triangle',
            danger: 'fa-times',
            success: 'fa-check',
        };
        document.getElementById('appMessageTitle').textContent = title;
        document.getElementById('appMessageSubtitle').textContent = subtitle;
        document.getElementById('appMessageBody').innerHTML = `<p class="mb-0">${esc(message)}</p>`;
        document.getElementById('appMessageIcon').className = `action-confirm-icon mr-3 is-${type}`;
        document.getElementById('appMessageIcon').innerHTML = `<i class="fas ${iconMap[type] || iconMap.info}"></i>`;
        $('#appMessageModal').modal('show');
    }

    let processSteps = [];

    function renderProcessSteps() {
        const wrap = document.getElementById('processProgressSteps');
        wrap.innerHTML = processSteps.map((step, i) => {
            const state = step.state || 'pending';
            const icon = state === 'done'
                ? 'fa-check'
                : (state === 'error' ? 'fa-times' : (state === 'active' ? 'fa-spinner fa-spin' : 'fa-circle'));
            return `<div class="process-step is-${state}" data-step="${i}">
                <span class="process-step-icon"><i class="fas ${icon}"></i></span>
                <div class="process-step-text">
                    <div class="process-step-title">${esc(step.title)}</div>
                    ${step.note ? `<div class="process-step-note">${esc(step.note)}</div>` : ''}
                </div>
            </div>`;
        }).join('');
    }

    function openProcessModal({ title, subtitle, steps }) {
        processSteps = steps.map(title => ({ title, state: 'pending', note: '' }));
        document.getElementById('processProgressTitle').textContent = title;
        document.getElementById('processProgressSubtitle').textContent = subtitle;
        document.getElementById('processProgressBadge').className = 'badge badge-info align-self-start';
        document.getElementById('processProgressBadge').textContent = 'Berjalan';
        document.getElementById('processProgressIcon').className = 'process-icon mr-3';
        document.getElementById('processProgressIcon').innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
        document.getElementById('processProgressBar').className = 'progress-bar progress-bar-striped progress-bar-animated';
        document.getElementById('processProgressResult').className = 'alert mt-3 mb-0 d-none';
        document.getElementById('processProgressResult').innerHTML = '';
        document.getElementById('processProgressClose').classList.add('d-none');
        setProcessProgress(5, 'Menyiapkan proses...');
        renderProcessSteps();
        $('#processProgressModal').modal({ backdrop: 'static', keyboard: false, show: true });
    }

    function setProcessStep(index, state, note = '') {
        if (!processSteps[index]) return;
        processSteps[index].state = state;
        processSteps[index].note = note;
        renderProcessSteps();
    }

    function setProcessProgress(percent, label) {
        const safePercent = Math.max(0, Math.min(100, Number(percent) || 0));
        const bar = document.getElementById('processProgressBar');
        bar.style.width = safePercent + '%';
        bar.setAttribute('aria-valuenow', safePercent);
        document.getElementById('processProgressPercent').textContent = safePercent + '%';
        document.getElementById('processProgressLabel').textContent = label;
    }

    function finishProcess(success, message, detailHtml = '') {
        setProcessProgress(100, success ? 'Proses selesai' : 'Proses gagal');
        const badge = document.getElementById('processProgressBadge');
        const icon = document.getElementById('processProgressIcon');
        const bar = document.getElementById('processProgressBar');
        const result = document.getElementById('processProgressResult');

        badge.className = `badge badge-${success ? 'success' : 'danger'} align-self-start`;
        badge.textContent = success ? 'Selesai' : 'Gagal';
        icon.className = `process-icon mr-3 ${success ? 'is-success' : 'is-danger'}`;
        icon.innerHTML = `<i class="fas ${success ? 'fa-check' : 'fa-times'}"></i>`;
        bar.classList.remove('progress-bar-animated');
        bar.classList.toggle('bg-success', success);
        bar.classList.toggle('bg-danger', !success);
        result.className = `alert alert-${success ? 'success' : 'danger'} mt-3 mb-0`;
        result.innerHTML = `<strong>${success ? 'Berhasil.' : 'Gagal.'}</strong> ${esc(message)}${detailHtml}`;
        document.getElementById('processProgressClose').classList.remove('d-none');
    }

    // --- STATS ---
    function loadStats(tahunId) {
        if (!tahunId) return;
        fetch(`{{ route('admin.kenaikan-kelas.data') }}?tahun_pelajaran_id=${tahunId}`)
            .then(r => r.json())
            .then(d => {
                document.getElementById('stat-10').textContent    = d.siswa_10 ?? '-';
                document.getElementById('stat-11').textContent    = d.siswa_11 ?? '-';
                document.getElementById('stat-12').textContent    = d.siswa_12 ?? '-';
                document.getElementById('stat-lulus').textContent = d.siswa_12_lulus ?? '-';
            })
            .catch(() => {});
    }

    // --- STATUS KELULUSAN (Step 1) ---
    function loadStatusKelulusan(tahunId) {
        if (!tahunId) return;
        fetch(`{{ route('admin.kenaikan-kelas.status-kelulusan') }}?tahun_pelajaran_id=${tahunId}`, {
            headers: { 'Accept': 'application/json' }
        })
            .then(r => { if (!r.ok) throw new Error('HTTP ' + r.status); return r.json(); })
            .then(d => {
                const belum = d.belum_ada_pengumuman;
                let html = `<div class="row">`;
                html += `<div class="col-sm-3"><div class="info-box"><span class="info-box-icon bg-info"><i class="fas fa-users"></i></span><div class="info-box-content"><span class="info-box-text">Total Kelas XII</span><span class="info-box-number">${d.total}</span></div></div></div>`;
                html += `<div class="col-sm-3"><div class="info-box"><span class="info-box-icon bg-success"><i class="fas fa-check"></i></span><div class="info-box-content"><span class="info-box-text">Sudah Lulus/Lulus Bersyarat</span><span class="info-box-number">${d.sudah_lulus}</span></div></div></div>`;
                html += `<div class="col-sm-3"><div class="info-box"><span class="info-box-icon bg-warning"><i class="fas fa-times"></i></span><div class="info-box-content"><span class="info-box-text">Tidak Lulus</span><span class="info-box-number">${d.sudah_tidak_lulus}</span></div></div></div>`;
                html += `<div class="col-sm-3"><div class="info-box ${belum > 0 ? 'bg-danger' : ''}"><span class="info-box-icon ${belum > 0 ? 'bg-danger' : 'bg-secondary'}"><i class="fas fa-question"></i></span><div class="info-box-content"><span class="info-box-text">Belum Ada Pengumuman</span><span class="info-box-number ${belum > 0 ? 'text-white' : ''}">${belum}</span></div></div></div>`;
                html += `</div>`;
                if (belum > 0) {
                    html += `<div class="alert alert-warning"><i class="fas fa-exclamation-triangle mr-1"></i> <strong>${belum} siswa</strong> belum memiliki record pengumuman kelulusan. Set status mereka terlebih dahulu di halaman Pengumuman Kelulusan sebelum finalisasi.</div>`;
                }
                if (d.sudah_finalisasi > 0) {
                    html += `<div class="alert alert-success"><i class="fas fa-check-circle mr-1"></i> ${d.sudah_finalisasi} siswa sudah difinalisasi (siswa_kelas.status bukan aktif).</div>`;
                }
                document.getElementById('kelulusan-status').innerHTML = html;
                document.getElementById('btn-proses-kelulusan').disabled = (d.sudah_lulus + d.sudah_tidak_lulus) === 0;
            })
            .catch(err => {
                console.error('statusKelulusan error:', err);
                document.getElementById('kelulusan-status').innerHTML = '<span class="text-danger">Gagal memuat status kelulusan: ' + (err.message || 'Cek Console browser.') + '</span>';
            });
    }

    if (tahunAktifId) { loadStats(tahunAktifId); loadStatusKelulusan(tahunAktifId); }

    // --- STEP 1: FINALISASI KELULUSAN ---
    document.getElementById('btn-proses-kelulusan').addEventListener('click', async function () {
        const confirmed = await openActionConfirm({
            title: 'Finalisasi Kelulusan Kelas XII',
            subtitle: 'Pastikan data Pengumuman Kelulusan sudah benar.',
            icon: 'fa-graduation-cap',
            tone: 'danger',
            confirmText: 'Finalisasi Sekarang',
            confirmClass: 'btn-danger',
            bodyHtml: `<p class="mb-2">Sistem akan mengunci status kelas XII berdasarkan data Pengumuman Kelulusan.</p>
                <ul class="action-confirm-list">
                    <li><strong>Lulus / Lulus Bersyarat</strong> akan ditandai <code>siswa_kelas.status = lulus</code>.</li>
                    <li><strong>Tidak Lulus</strong> akan ditandai <code>siswa_kelas.status = tinggal_kelas</code>.</li>
                    <li>Proses ini tidak dibatalkan otomatis, jadi sebaiknya dilakukan setelah data final.</li>
                </ul>`,
        });
        if (!confirmed) return;

        const btn = this;
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i> Memproses...';
        openProcessModal({
            title: 'Finalisasi Kelulusan Kelas XII',
            subtitle: 'Sistem sedang mengunci status kelulusan dari data Pengumuman Kelulusan.',
            steps: [
                'Memvalidasi data pengumuman',
                'Mengubah status siswa_kelas',
                'Memperbarui status siswa',
                'Memuat ulang ringkasan',
            ],
        });
        setProcessStep(0, 'done', 'Data pengumuman siap diproses.');
        setProcessStep(1, 'active', 'Mengunci status lulus dan tinggal kelas.');
        setProcessProgress(35, 'Memproses finalisasi kelulusan...');

        fetch('{{ route('admin.kenaikan-kelas.proses-kelulusan') }}', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
            body: JSON.stringify({
                tahun_pelajaran_id: tahunAktifId,
            })
        })
        .then(parseJsonResponse)
        .then(d => {
            const type = d.success ? 'success' : 'warning';
            setProcessStep(1, 'done', `${d.diproses_lulus || 0} lulus, ${d.diproses_tinggal || 0} tinggal kelas.`);
            setProcessStep(2, 'done', 'Status siswa dipindahkan ke arsip alumni.');
            setProcessStep(3, 'active', 'Mengambil ulang statistik halaman.');
            setProcessProgress(85, 'Memuat ulang ringkasan...');
            document.getElementById('result-kelulusan').innerHTML = alertBox(
                `<i class="fas fa-check-circle mr-1"></i> ${esc(d.message)}`, type
            );
            loadStats(tahunAktifId);
            loadStatusKelulusan(tahunAktifId);
            setProcessStep(3, 'done', 'Ringkasan halaman sudah diperbarui.');
            finishProcess(true, d.message || 'Finalisasi kelulusan selesai.');
        })
        .catch(err => {
            setProcessStep(1, 'error', err.message || 'Proses berhenti sebelum selesai.');
            finishProcess(false, err.message || 'Terjadi kesalahan. Coba lagi.');
            document.getElementById('result-kelulusan').innerHTML = alertBox(esc(err.message || 'Terjadi kesalahan. Coba lagi.'), 'danger');
        })
        .finally(() => {
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-graduation-cap mr-1"></i> Finalisasi Kelulusan Kelas XII';
        });
    });

    // --- STEP 2: NAIK KELAS ---
    const elTahunAsal   = document.getElementById('tahun-asal');
    const elTahunTujuan = document.getElementById('tahun-tujuan');
    const btnLoadMapping = document.getElementById('btn-load-mapping');
    let naikKelasPreview = { kelasCount: 0, siswaCount: 0 };

    function updateLoadBtn() {
        btnLoadMapping.disabled = !(elTahunAsal.value && elTahunTujuan.value && elTahunAsal.value !== elTahunTujuan.value);
        naikKelasPreview = { kelasCount: 0, siswaCount: 0 };
        document.getElementById('mapping-container').classList.add('d-none');
        document.getElementById('result-naik-kelas').innerHTML = '';
    }
    elTahunAsal.addEventListener('change', updateLoadBtn);
    elTahunTujuan.addEventListener('change', updateLoadBtn);

    async function fetchKelas(tahunId, tingkat) {
        const url = `{{ route('admin.kenaikan-kelas.kelas-by-tahun') }}?tahun_pelajaran_id=${tahunId}&tingkat=${tingkat}`;
        const r = await fetch(url);
        return r.json();
    }

    async function getSiswaCount(kelasId, tahunId) {
        const url = `{{ route('admin.kenaikan-kelas.preview') }}?kelas_id=${kelasId}&tahun_pelajaran_id=${tahunId}`;
        const r = await fetch(url);
        const d = await r.json();
        return Array.isArray(d) ? d.length : 0;
    }

    btnLoadMapping.addEventListener('click', async function () {
        const asalId   = elTahunAsal.value;
        const tujuanId = elTahunTujuan.value;
        if (!asalId || !tujuanId) return;

        this.disabled = true;
        this.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i> Memuat...';

        try {
            const [kelas10, kelas11] = await Promise.all([
                fetchKelas(asalId, 10),
                fetchKelas(asalId, 11),
            ]);

            const kelasAsal = [...kelas10, ...kelas11];
            const counts = await Promise.all(kelasAsal.map(k => getSiswaCount(k.id, asalId)));
            const statsResponse = await fetch(`{{ route('admin.kenaikan-kelas.data') }}?tahun_pelajaran_id=${asalId}`);
            const stats = await statsResponse.json();
            const siswaKelas10 = kelasAsal.reduce((sum, k, i) => sum + (Number(k.tingkat) === 10 ? counts[i] : 0), 0);
            const siswaKelas11 = kelasAsal.reduce((sum, k, i) => sum + (Number(k.tingkat) === 11 ? counts[i] : 0), 0);
            const tanpaRombel10 = Math.max((Number(stats.siswa_10) || 0) - siswaKelas10, 0);
            const tanpaRombel11 = Math.max((Number(stats.siswa_11) || 0) - siswaKelas11, 0);
            const totalSiswa = (Number(stats.siswa_10) || 0) + (Number(stats.siswa_11) || 0);
            const previewRows = kelasAsal.map((k, i) => ({
                nama: k.nama_kelas,
                tingkat: Number(k.tingkat),
                count: counts[i],
                isRombel: true,
            }));
            if (tanpaRombel10 > 0) {
                previewRows.push({ nama: 'Tanpa Rombel', tingkat: 10, count: tanpaRombel10, isRombel: false });
            }
            if (tanpaRombel11 > 0) {
                previewRows.push({ nama: 'Tanpa Rombel', tingkat: 11, count: tanpaRombel11, isRombel: false });
            }
            naikKelasPreview = { kelasCount: previewRows.length, siswaCount: totalSiswa };

            const tbody = document.getElementById('mapping-tbody');
            tbody.innerHTML = previewRows.map(row => {
                const tingkatTujuan = row.tingkat + 1;
                return `<tr>
                    <td>${esc(row.nama)}${row.isRombel ? '' : ' <span class="badge badge-light ml-1">aktif</span>'}</td>
                    <td><span class="badge badge-secondary">${row.tingkat}</span></td>
                    <td>${row.count}</td>
                    <td><span class="badge badge-info">Naik ke tingkat ${tingkatTujuan}</span></td>
                </tr>`;
            }).join('') || '<tr><td colspan="4" class="text-center text-muted">Tidak ada kelas X/XI di tahun asal.</td></tr>';

            document.getElementById('mapping-container').classList.remove('d-none');
        } catch (e) {
            showAppMessage({
                title: 'Gagal Memuat Kelas',
                subtitle: 'Preview kenaikan belum bisa ditampilkan.',
                message: e.message || 'Gagal memuat data kelas. Coba lagi.',
                type: 'danger',
            });
        } finally {
            this.disabled = false;
            this.innerHTML = '<i class="fas fa-sync-alt mr-1"></i> Muat Preview Kenaikan';
        }
    });

    document.getElementById('btn-proses-naik-kelas').addEventListener('click', async function () {
        if (naikKelasPreview.siswaCount === 0) {
            showAppMessage({
                title: 'Tidak Ada Siswa',
                subtitle: 'Proses naik kelas belum dapat dijalankan.',
                message: 'Tidak ada siswa aktif kelas X/XI pada tahun pelajaran asal.',
                type: 'warning',
            });
            return;
        }

        const confirmed = await openActionConfirm({
            title: 'Proses Naik Kelas',
            subtitle: `${naikKelasPreview.siswaCount} siswa dari ${naikKelasPreview.kelasCount} baris preview akan ditandai naik tingkat.`,
            icon: 'fa-arrow-up',
            tone: 'warning',
            confirmText: 'Proses Naik Kelas',
            confirmClass: 'btn-warning',
            bodyHtml: `<p class="mb-2">Sistem akan menutup record <code>siswa_kelas</code> lama sebagai <code>naik_kelas</code>, lalu membuat record aktif di tahun tujuan sesuai tingkat baru tanpa rombel.</p>
                <ul class="action-confirm-list">
                    <li>Rombel tahun baru tidak dibuat otomatis.</li>
                    <li>Siswa ditempatkan ke rombel baru melalui assignment kelas.</li>
                    <li>Proses ini tidak dapat dibatalkan otomatis.</li>
                </ul>`,
        });
        if (!confirmed) return;

        const btn = this;
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i> Memproses...';
        openProcessModal({
            title: 'Proses Naik Kelas',
            subtitle: `${naikKelasPreview.siswaCount} siswa akan ditandai naik tingkat.`,
            steps: [
                'Memvalidasi tahun pelajaran',
                'Menandai record kelas lama',
                'Membuat record aktif tanpa rombel',
                'Memuat ulang ringkasan',
            ],
        });
        setProcessStep(0, 'done', `${naikKelasPreview.kelasCount} baris preview siap diproses.`);
        setProcessStep(1, 'active', 'Menutup histori lama siswa aktif X/XI.');
        setProcessProgress(30, 'Memproses kenaikan kelas...');

        fetch('{{ route('admin.kenaikan-kelas.proses-naik-kelas') }}', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
            body: JSON.stringify({
                tahun_asal_id:   elTahunAsal.value,
                tahun_tujuan_id: elTahunTujuan.value,
                tanggal_masuk:   document.getElementById('tanggal-masuk').value,
            })
        })
        .then(parseJsonResponse)
        .then(d => {
            let html = `<i class="fas fa-check mr-1"></i> ${esc(d.message)}`;
            const type = d.success ? 'success' : 'danger';
            const ok = Boolean(d.success);
            setProcessStep(1, ok ? 'done' : 'error', `${d.diproses || 0} siswa ditandai naik tingkat.`);
            setProcessStep(2, ok ? 'done' : 'error', `${d.sudah_ditempatkan || 0} siswa sudah punya rombel aktif di tahun tujuan.`);
            setProcessStep(3, 'active', 'Mengambil ulang statistik halaman.');
            setProcessProgress(85, 'Memuat ulang ringkasan...');
            document.getElementById('result-naik-kelas').innerHTML = alertBox(html, type);
            loadStats(elTahunAsal.value);
            setProcessStep(3, 'done', 'Ringkasan halaman sudah diperbarui.');
            finishProcess(ok, d.message || 'Proses naik kelas selesai.');
        })
        .catch(err => {
            setProcessStep(1, 'error', err.message || 'Proses berhenti sebelum selesai.');
            finishProcess(false, err.message || 'Terjadi kesalahan. Coba lagi.');
            document.getElementById('result-naik-kelas').innerHTML = alertBox(esc(err.message || 'Terjadi kesalahan. Coba lagi.'), 'danger');
        })
        .finally(() => {
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-arrow-up mr-1"></i> Proses Naik Kelas';
        });
    });
})();
</script>
@endsection
