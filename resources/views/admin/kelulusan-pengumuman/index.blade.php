@extends('adminlte::page')

@section('title', 'Pengumuman Kelulusan')

@section('content_header')
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
        <div>
            <h1 class="mb-1">Pengumuman Kelulusan Kelas 12</h1>
            <p class="text-muted mb-0">Kelola hasil kelulusan siswa kelas 12 untuk tahun ajaran aktif.</p>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <span class="badge badge-light px-3 py-2">
                <i class="fas fa-calendar-alt mr-1"></i> {{ $tahunAktif->nama }}
            </span>
            <span class="badge badge-{{ $setting->graduation_announcement_enabled ? 'success' : 'secondary' }} px-3 py-2">
                <i class="fas fa-bullhorn mr-1"></i>
                {{ $setting->graduation_announcement_enabled ? 'Sudah Dibuka' : 'Masih Ditutup' }}
            </span>
            @if($setting->graduation_announcement_starts_at)
                <span class="badge badge-info px-3 py-2">
                    <i class="fas fa-clock mr-1"></i>
                    Tayang {{ $setting->graduation_announcement_starts_at->format('d M Y H:i') }}
                </span>
            @endif
        </div>
    </div>
@stop

@section('js')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const bulkSelect = document.getElementById('bulkGraduationStatus');
    const applyButton = document.getElementById('applyBulkGraduationStatus');
    const clearButton = document.getElementById('clearBulkGraduationStatus');
    const selectAllButton = document.getElementById('selectAllGraduationRows');
    const unselectAllButton = document.getElementById('unselectAllGraduationRows');
    const selectedCountText = document.getElementById('selectedGraduationCount');
    const selectedCountFooter = document.getElementById('selectedGraduationCountFooter');
    const saveButtonLabel = document.getElementById('saveGraduationButtonLabel');
    const saveButton = document.getElementById('saveGraduationSubmit');
    const saveModal = $('#confirmGraduationSaveModal');
    const confirmSaveButton = document.getElementById('confirmGraduationSaveButton');
    const form = document.getElementById('graduationAnnouncementForm');
    const bulkNoteWrap = document.getElementById('bulkGraduationNoteWrap');
    const bulkNote = document.getElementById('bulkGraduationNote');
    const bulkFeedback = document.getElementById('bulkGraduationFeedback');
    const statusSelects = Array.from(document.querySelectorAll('.graduation-status-select'));
    const noteFields = Array.from(document.querySelectorAll('.graduation-note-field'));
    const rowChecks = Array.from(document.querySelectorAll('.graduation-row-check'));
    let saveConfirmed = false;

    if (!bulkSelect || statusSelects.length === 0 || rowChecks.length === 0) {
        return;
    }

    const updateSelectedCount = function () {
        const count = rowChecks.filter(check => check.checked).length;
        if (selectedCountText) {
            selectedCountText.textContent = count + ' siswa dipilih';
        }
        if (selectedCountFooter) {
            selectedCountFooter.textContent = count > 0
                ? count + ' siswa dipilih untuk bulk action'
                : 'Belum ada siswa dipilih untuk bulk action';
        }
        if (saveButtonLabel) {
            saveButtonLabel.textContent = count > 0
                ? 'Simpan Pengumuman (' + count + ' dipilih)'
                : 'Simpan Pengumuman';
        }
        if (applyButton) applyButton.disabled = count === 0;
        if (clearButton) clearButton.disabled = count === 0;
    };

    const selectedSiswaIds = function () {
        return rowChecks
            .filter(check => check.checked)
            .map(check => check.value);
    };

    const selectedStatusSelects = function () {
        return selectedSiswaIds()
            .map(siswaId => document.querySelector('.graduation-status-select[data-siswa-id="' + siswaId + '"]'))
            .filter(Boolean);
    };

    const applyStatus = function (value) {
        const noteValue = bulkNote ? bulkNote.value : '';

        selectedStatusSelects().forEach(function (select) {
            select.value = value;
            select.dispatchEvent(new Event('change', { bubbles: true }));
        });

        if (value === 'lulus_bersyarat') {
            selectedSiswaIds().forEach(function (siswaId) {
                const note = document.querySelector('.graduation-note-field[data-siswa-id="' + siswaId + '"]');
                if (note) {
                    note.value = noteValue;
                    note.dispatchEvent(new Event('input', { bubbles: true }));
                }
            });
        }
    };

    const showBulkFeedback = function (message, type = 'info') {
        if (!bulkFeedback) return;

        bulkFeedback.className = 'graduation-bulk-feedback is-visible text-' + type;
        bulkFeedback.textContent = message;
    };

    const toggleBulkNote = function () {
        if (!bulkNoteWrap) return;
        bulkNoteWrap.classList.toggle('is-visible', bulkSelect.value === 'lulus_bersyarat');
        if (bulkSelect.value !== 'lulus_bersyarat' && bulkNote) {
            bulkNote.classList.remove('is-invalid');
        }
    };

    const validateConditionalNotes = function () {
        let firstInvalid = null;

        statusSelects.forEach(function (select) {
            const siswaId = select.dataset.siswaId;
            const note = document.querySelector('.graduation-note-field[data-siswa-id="' + siswaId + '"]');

            if (!note) {
                return;
            }

            const invalid = select.value === 'lulus_bersyarat' && note.value.trim() === '';
            note.classList.toggle('is-invalid', invalid);

            if (invalid && !firstInvalid) {
                firstInvalid = note;
            }
        });

        if (firstInvalid) {
            showBulkFeedback('Masih ada siswa Lulus Bersyarat yang belum memiliki catatan.', 'danger');
            firstInvalid.scrollIntoView({ behavior: 'smooth', block: 'center' });
            firstInvalid.focus();
            return false;
        }

        return true;
    };

    rowChecks.forEach(check => check.addEventListener('change', updateSelectedCount));
    bulkSelect.addEventListener('change', toggleBulkNote);

    if (selectAllButton) {
        selectAllButton.addEventListener('click', function () {
            rowChecks.forEach(check => check.checked = true);
            updateSelectedCount();
        });
    }

    if (unselectAllButton) {
        unselectAllButton.addEventListener('click', function () {
            rowChecks.forEach(check => check.checked = false);
            updateSelectedCount();
        });
    }

    if (applyButton) {
        applyButton.addEventListener('click', function () {
            const selectedCount = rowChecks.filter(check => check.checked).length;
            if (selectedCount === 0) {
                showBulkFeedback('Pilih minimal satu siswa terlebih dahulu.', 'warning');
                return;
            }

            if (bulkSelect.value === 'lulus_bersyarat' && bulkNote && bulkNote.value.trim() === '') {
                bulkNote.classList.add('is-invalid');
                showBulkFeedback('Catatan wajib diisi untuk status Lulus Bersyarat.', 'danger');
                bulkNote.focus();
                return;
            }

            const selectedLabel = bulkSelect.options[bulkSelect.selectedIndex]?.text || 'status yang dipilih';
            applyStatus(bulkSelect.value);

            if (bulkNote) {
                bulkNote.classList.remove('is-invalid');
            }

            showBulkFeedback(
                bulkSelect.value
                    ? 'Status "' + selectedLabel + '" diterapkan ke ' + selectedCount + ' siswa terpilih.'
                    : 'Status dikosongkan untuk ' + selectedCount + ' siswa terpilih.',
                'success'
            );
        });
    }

    if (clearButton) {
        clearButton.addEventListener('click', function () {
            const selectedCount = rowChecks.filter(check => check.checked).length;
            if (selectedCount === 0) {
                showBulkFeedback('Pilih minimal satu siswa terlebih dahulu.', 'warning');
                return;
            }

            bulkSelect.value = '';
            toggleBulkNote();
            applyStatus('');
            showBulkFeedback('Status dikosongkan untuk ' + selectedCount + ' siswa terpilih.', 'success');
        });
    }

    if (bulkNote) {
        bulkNote.addEventListener('input', function () {
            bulkNote.classList.remove('is-invalid');
        });
    }

    noteFields.forEach(function (note) {
        note.addEventListener('input', function () {
            note.classList.remove('is-invalid');
        });
    });

    if (form && saveButton && confirmSaveButton) {
        form.addEventListener('submit', function (event) {
            const submitter = event.submitter;
            const action = submitter?.getAttribute('formaction') || form.getAttribute('action');

            if (saveConfirmed || action !== form.getAttribute('action')) {
                return;
            }

            event.preventDefault();
            if (!validateConditionalNotes()) {
                return;
            }

            saveModal.modal('show');
        });

        confirmSaveButton.addEventListener('click', function () {
            saveConfirmed = true;
            saveModal.modal('hide');
            if (form.requestSubmit) {
                form.requestSubmit(saveButton);
            } else {
                form.submit();
            }
        });
    }

    updateSelectedCount();
    toggleBulkNote();
});
</script>
@stop

@section('css')
<style>
    .graduation-bulk-panel {
        border: 1px solid #dbe7f4;
        border-radius: 10px;
        background: #f8fbff;
    }

    .graduation-bulk-grid {
        display: grid;
        grid-template-columns: minmax(230px, 1fr) minmax(220px, 1fr) minmax(320px, 1.6fr);
        gap: 14px;
        align-items: end;
    }

    .graduation-bulk-actions {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 8px;
        margin-top: 12px;
        padding-top: 12px;
        border-top: 1px solid #e4edf7;
    }

    .graduation-bulk-note textarea {
        min-height: 62px;
    }

    .graduation-bulk-note {
        max-height: 0;
        opacity: 0;
        overflow: hidden;
        transform: translateY(-4px);
        transition: max-height .24s ease, opacity .18s ease, transform .18s ease;
    }

    .graduation-bulk-note.is-visible {
        max-height: 120px;
        opacity: 1;
        transform: translateY(0);
    }

    .graduation-bulk-feedback {
        min-height: 20px;
        opacity: 0;
        transition: opacity .18s ease;
    }

    .graduation-bulk-feedback.is-visible {
        opacity: 1;
    }

    @media (max-width: 992px) {
        .graduation-bulk-grid {
            grid-template-columns: 1fr;
        }
    }

    .stat-card-active {
        outline: 3px solid rgba(255, 255, 255, 0.6);
        outline-offset: -3px;
    }

    .stat-card-active .small-box-footer {
        background-color: rgba(0, 0, 0, 0.25);
        font-weight: 600;
    }

    tr.row-lulus-bersyarat {
        background-color: #fff8e1 !important;
        border-left: 3px solid #fd7e14;
    }

    tr.row-lulus-bersyarat td:first-child {
        padding-left: calc(1rem + 1px) !important;
    }
</style>
@stop

@section('content')
    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <div class="font-weight-bold mb-1">
                <i class="fas fa-exclamation-triangle mr-1"></i> Data belum tersimpan
            </div>
            <div>{{ $errors->first() }}</div>
            <button type="button" class="close" data-dismiss="alert" aria-label="Tutup">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    <div class="row">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm simansa-surface-card">
                <div class="card-body p-4">
                    <div class="d-flex flex-wrap align-items-start justify-content-between gap-3">
                        <div>
                            <div class="text-uppercase text-muted small font-weight-bold mb-2">Publikasi Kelulusan</div>
                            <h3 class="mb-2">Kontrol Akses Siswa Kelas 12</h3>
                            <p class="text-muted mb-0">
                                Saat fitur dibuka, menu pengumuman hanya muncul untuk siswa kelas 12 pada tahun ajaran aktif.
                            </p>
                        </div>
                        <form action="{{ route('admin.kelulusan-pengumuman.publish') }}" method="POST" class="d-flex flex-wrap align-items-end gap-2">
                            @csrf
                            <div>
                                <label for="graduation_announcement_starts_at" class="small text-muted font-weight-bold mb-1">
                                    Jadwal amplop tampil
                                </label>
                                <input
                                    type="datetime-local"
                                    id="graduation_announcement_starts_at"
                                    name="graduation_announcement_starts_at"
                                    class="form-control"
                                    value="{{ optional($setting->graduation_announcement_starts_at)->format('Y-m-d\TH:i') }}"
                                >
                            </div>
                            <button type="submit" name="graduation_announcement_enabled" value="{{ $setting->graduation_announcement_enabled ? 1 : 0 }}" class="btn btn-outline-primary">
                                <i class="fas fa-save mr-1"></i>
                                Simpan Jadwal
                            </button>
                            <button type="submit" name="graduation_announcement_enabled" value="{{ $setting->graduation_announcement_enabled ? 0 : 1 }}" class="btn {{ $setting->graduation_announcement_enabled ? 'btn-outline-secondary' : 'btn-success' }}">
                                <i class="fas {{ $setting->graduation_announcement_enabled ? 'fa-eye-slash' : 'fa-eye' }} mr-1"></i>
                                {{ $setting->graduation_announcement_enabled ? 'Tutup Pengumuman' : 'Buka Pengumuman' }}
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm simansa-surface-card h-100">
                <div class="card-body p-4">
                    <div class="text-uppercase text-muted small font-weight-bold mb-2">Catatan Custom Menu</div>
                    <h4 class="mb-2">Bisa dipakai, tapi bukan inti fitur ini</h4>
                    <p class="text-muted mb-2">
                        Custom Menu cocok untuk surat pengantar, video sambutan, atau pesan tambahan yang ditugaskan ke siswa tertentu.
                    </p>
                    <p class="text-muted mb-0">
                        Untuk hasil kelulusan, sistem ini sengaja dibuat khusus agar aksesnya otomatis mengikuti kelas 12, tahun ajaran aktif, dan toggle publish dari admin.
                    </p>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-2 col-md-4">
            <div class="small-box bg-gradient-success shadow-sm {{ $selectedStatusFilter === 'lulus' ? 'stat-card-active' : '' }}">
                <div class="inner">
                    <h3>{{ $stats['lulus'] }}</h3>
                    <p>Lulus</p>
                </div>
                <div class="icon"><i class="fas fa-check-circle"></i></div>
                <a href="{{ route('admin.kelulusan-pengumuman.index', array_filter(['kelas_id' => $selectedKelasId, 'status_filter' => 'lulus'])) }}" class="small-box-footer">
                    @if($selectedStatusFilter === 'lulus') <i class="fas fa-check mr-1"></i>Aktif @else Filter <i class="fas fa-arrow-circle-right"></i> @endif
                </a>
            </div>
        </div>
        <div class="col-lg-2 col-md-4">
            <div class="small-box bg-gradient-warning shadow-sm {{ $selectedStatusFilter === 'lulus_bersyarat' ? 'stat-card-active' : '' }}">
                <div class="inner">
                    <h3>{{ $stats['lulus_bersyarat'] }}</h3>
                    <p>Lulus Bersyarat</p>
                </div>
                <div class="icon"><i class="fas fa-exclamation-circle"></i></div>
                <a href="{{ route('admin.kelulusan-pengumuman.index', array_filter(['kelas_id' => $selectedKelasId, 'status_filter' => 'lulus_bersyarat'])) }}" class="small-box-footer">
                    @if($selectedStatusFilter === 'lulus_bersyarat') <i class="fas fa-check mr-1"></i>Aktif @else Filter <i class="fas fa-arrow-circle-right"></i> @endif
                </a>
            </div>
        </div>
        <div class="col-lg-2 col-md-4">
            <div class="small-box bg-gradient-danger shadow-sm {{ $selectedStatusFilter === 'tidak_lulus' ? 'stat-card-active' : '' }}">
                <div class="inner">
                    <h3>{{ $stats['tidak_lulus'] }}</h3>
                    <p>Tidak Lulus</p>
                </div>
                <div class="icon"><i class="fas fa-times-circle"></i></div>
                <a href="{{ route('admin.kelulusan-pengumuman.index', array_filter(['kelas_id' => $selectedKelasId, 'status_filter' => 'tidak_lulus'])) }}" class="small-box-footer">
                    @if($selectedStatusFilter === 'tidak_lulus') <i class="fas fa-check mr-1"></i>Aktif @else Filter <i class="fas fa-arrow-circle-right"></i> @endif
                </a>
            </div>
        </div>
        <div class="col-lg-2 col-md-4">
            <div class="small-box bg-gradient-info shadow-sm {{ (!$selectedStatusFilter && !$selectedOpenedFilter) ? 'stat-card-active' : '' }}">
                <div class="inner">
                    <h3>{{ $stats['total'] }}</h3>
                    <p>Total Siswa Kelas 12</p>
                </div>
                <div class="icon"><i class="fas fa-user-graduate"></i></div>
                <a href="{{ route('admin.kelulusan-pengumuman.index', array_filter(['kelas_id' => $selectedKelasId])) }}" class="small-box-footer">
                    @if(!$selectedStatusFilter && !$selectedOpenedFilter) Semua Data @else Reset Filter <i class="fas fa-times-circle"></i> @endif
                </a>
            </div>
        </div>
        <div class="col-lg-2 col-md-4">
            <div class="small-box bg-gradient-primary shadow-sm {{ $selectedOpenedFilter === 'sudah' ? 'stat-card-active' : '' }}">
                <div class="inner">
                    <h3>{{ $stats['sudah_buka'] }}</h3>
                    <p>Sudah Buka</p>
                </div>
                <div class="icon"><i class="fas fa-envelope-open-text"></i></div>
                <a href="{{ route('admin.kelulusan-pengumuman.index', array_filter(['kelas_id' => $selectedKelasId, 'opened_filter' => 'sudah'])) }}" class="small-box-footer">
                    @if($selectedOpenedFilter === 'sudah') <i class="fas fa-check mr-1"></i>Aktif @else Filter <i class="fas fa-arrow-circle-right"></i> @endif
                </a>
            </div>
        </div>
        <div class="col-lg-2 col-md-4">
            <div class="small-box bg-gradient-secondary shadow-sm {{ $selectedOpenedFilter === 'belum' ? 'stat-card-active' : '' }}">
                <div class="inner">
                    <h3>{{ $stats['belum_buka'] }}</h3>
                    <p>Belum Buka</p>
                </div>
                <div class="icon"><i class="fas fa-envelope"></i></div>
                <a href="{{ route('admin.kelulusan-pengumuman.index', array_filter(['kelas_id' => $selectedKelasId, 'opened_filter' => 'belum'])) }}" class="small-box-footer">
                    @if($selectedOpenedFilter === 'belum') <i class="fas fa-check mr-1"></i>Aktif @else Filter <i class="fas fa-arrow-circle-right"></i> @endif
                </a>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-header border-0">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-2">
                <div>
                    <h3 class="h5 font-weight-bold mb-1">Data Pengumuman Kelulusan</h3>
                    <div class="text-muted small">Isi status per siswa lalu simpan. Catatan hanya wajib untuk status Lulus Bersyarat.</div>
                </div>
                @if($stats['sudah_buka'] > 0)
                    <button
                        type="submit"
                        form="graduationAnnouncementForm"
                        formaction="{{ route('admin.kelulusan-pengumuman.reset-opened') }}"
                        formmethod="POST"
                        class="btn btn-outline-warning btn-sm"
                        onclick="return confirm('Reset riwayat buka amplop untuk {{ $selectedKelasId ? 'siswa pada filter rombel ini' : 'semua siswa kelas 12' }}? Status kelulusan tidak akan berubah.')"
                    >
                        <i class="fas fa-undo mr-1"></i>
                        Reset Buka {{ $selectedKelasId ? 'Filter Ini' : 'Semua' }}
                    </button>
                @endif
            </div>
            <form action="{{ route('admin.kelulusan-pengumuman.index') }}" method="GET">
                <div class="row align-items-end no-gutters" style="gap: 0;">
                    <div class="col-12 col-sm-6 col-lg-3 pr-2 mb-2">
                        <label class="small text-muted font-weight-bold mb-1">Rombel</label>
                        <select name="kelas_id" class="form-control form-control-sm">
                            <option value="">Semua Rombel Kelas 12</option>
                            @foreach($kelasList as $kelas)
                                <option value="{{ $kelas->id }}" @selected($selectedKelasId === $kelas->id)>{{ $kelas->nama_kelas }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12 col-sm-6 col-lg-3 pr-2 mb-2">
                        <label class="small text-muted font-weight-bold mb-1">Status Kelulusan</label>
                        <select name="status_filter" class="form-control form-control-sm">
                            <option value="">Semua Status</option>
                            @foreach($statusOptions as $value => $label)
                                <option value="{{ $value }}" @selected($selectedStatusFilter === $value)>{{ $label }}</option>
                            @endforeach
                            <option value="belum_ditentukan" @selected($selectedStatusFilter === 'belum_ditentukan')>Belum Ditentukan</option>
                        </select>
                    </div>
                    <div class="col-12 col-sm-6 col-lg-3 pr-2 mb-2">
                        <label class="small text-muted font-weight-bold mb-1">Status Amplop</label>
                        <select name="opened_filter" class="form-control form-control-sm">
                            <option value="">Semua Amplop</option>
                            <option value="sudah" @selected($selectedOpenedFilter === 'sudah')>Sudah Dibuka</option>
                            <option value="belum" @selected($selectedOpenedFilter === 'belum')>Belum Dibuka</option>
                        </select>
                    </div>
                    <div class="col-12 col-sm-6 col-lg-3 mb-2 d-flex align-items-end gap-2">
                        <button type="submit" class="btn btn-primary btn-sm">
                            <i class="fas fa-filter mr-1"></i> Terapkan
                        </button>
                        @if($selectedKelasId || $selectedStatusFilter || $selectedOpenedFilter)
                            <a href="{{ route('admin.kelulusan-pengumuman.index') }}" class="btn btn-outline-secondary btn-sm">
                                <i class="fas fa-times mr-1"></i> Reset
                            </a>
                        @endif
                    </div>
                </div>
            </form>
        </div>
        <div class="card-body p-0">
            @if($selectedStatusFilter || $selectedOpenedFilter)
                <div class="alert alert-info alert-dismissible mb-0" style="border-radius: 0; border-left: none; border-right: none; border-top: none;">
                    <i class="fas fa-filter mr-1"></i>
                    Filter aktif:
                    @if($selectedStatusFilter)
                        <strong>Status: {{ $statusOptions[$selectedStatusFilter] ?? ucfirst(str_replace('_', ' ', $selectedStatusFilter)) }}</strong>
                    @endif
                    @if($selectedStatusFilter && $selectedOpenedFilter)  &amp;&amp;  @endif
                    @if($selectedOpenedFilter)
                        <strong>Amplop: {{ $selectedOpenedFilter === 'sudah' ? 'Sudah Dibuka' : 'Belum Dibuka' }}</strong>
                    @endif
                    — Menampilkan {{ $students->count() }} dari {{ $stats['total'] }} siswa.
                    <a href="{{ route('admin.kelulusan-pengumuman.index', array_filter(['kelas_id' => $selectedKelasId])) }}" class="ml-2 font-weight-bold">
                        <i class="fas fa-times mr-1"></i>Reset Filter
                    </a>
                    <button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">&times;</span></button>
                </div>
            @endif
            <form id="graduationAnnouncementForm" action="{{ route('admin.kelulusan-pengumuman.save') }}" method="POST">
                @csrf
                <input type="hidden" name="kelas_filter" value="{{ $selectedKelasId }}">
                <input type="hidden" name="status_filter_preserve" value="{{ $selectedStatusFilter }}">
                <input type="hidden" name="opened_filter_preserve" value="{{ $selectedOpenedFilter }}">
                @if($students->isNotEmpty())
                    <div class="px-4 py-3 border-bottom">
                        <div class="graduation-bulk-panel p-3">
                            <div class="graduation-bulk-grid">
                                <div>
                                    <label class="small text-muted font-weight-bold mb-1 d-block">Pilih siswa</label>
                                    <div class="btn-group w-100" role="group" aria-label="Pilih siswa">
                                        <button type="button" class="btn btn-outline-primary flex-fill" id="selectAllGraduationRows">
                                            <i class="fas fa-check-square mr-1"></i> Select Semua
                                        </button>
                                        <button type="button" class="btn btn-outline-secondary flex-fill" id="unselectAllGraduationRows">
                                            <i class="far fa-square mr-1"></i> Unselect Semua
                                        </button>
                                    </div>
                                </div>
                                <div>
                                    <label for="bulkGraduationStatus" class="small text-muted font-weight-bold mb-1">
                                        Status untuk siswa terpilih
                                    </label>
                                    <select id="bulkGraduationStatus" class="form-control">
                                        <option value="">Belum Ditentukan</option>
                                        @foreach($statusOptions as $value => $label)
                                            <option value="{{ $value }}">{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div id="bulkGraduationNoteWrap" class="graduation-bulk-note">
                                    <label for="bulkGraduationNote" class="small text-muted font-weight-bold mb-1">
                                        Catatan Lulus Bersyarat
                                    </label>
                                    <textarea
                                        id="bulkGraduationNote"
                                        class="form-control"
                                        rows="2"
                                        placeholder="Catatan ini akan diisi ke siswa terpilih"
                                    ></textarea>
                                </div>
                            </div>
                            <div class="graduation-bulk-actions">
                                <button type="button" class="btn btn-success" id="applyBulkGraduationStatus">
                                    <i class="fas fa-check-double mr-1"></i> Terapkan Status
                                </button>
                                <button type="button" class="btn btn-outline-secondary" id="clearBulkGraduationStatus">
                                    <i class="fas fa-times mr-1"></i> Kosongkan Status
                                </button>
                            </div>
                            <div class="text-muted small">
                                <span class="badge badge-info px-2 py-1" id="selectedGraduationCount">0 siswa dipilih</span>
                                <span class="ml-2">Klik <strong>Simpan Pengumuman</strong> setelah menerapkan.</span>
                            </div>
                            <div class="graduation-bulk-feedback small font-weight-bold" id="bulkGraduationFeedback"></div>
                        </div>
                    </div>
                @endif
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="thead-light">
                            <tr>
                                <th class="pl-4" style="width: 54px;">Pilih</th>
                                <th class="pl-4">Siswa</th>
                                <th>Rombel</th>
                                <th style="width: 220px;">Status</th>
                                <th>Catatan</th>
                                <th class="pr-4">Dibuka</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($students as $row)
                                @php($item = $announcementMap->get($row->siswa->id))
                                <tr class="{{ optional($item)->status === 'lulus_bersyarat' ? 'row-lulus-bersyarat' : '' }}">
                                    <td class="pl-4">
                                        <div class="custom-control custom-checkbox">
                                            <input
                                                type="checkbox"
                                                class="custom-control-input graduation-row-check"
                                                id="graduationCheck{{ $row->siswa->id }}"
                                                value="{{ $row->siswa->id }}"
                                            >
                                            <label class="custom-control-label" for="graduationCheck{{ $row->siswa->id }}"></label>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="font-weight-bold">{{ $row->siswa->nama_lengkap }}</div>
                                        <div class="text-muted small">{{ $row->siswa->nisn }} @if($row->siswa->user?->username) | {{ $row->siswa->user->username }} @endif</div>
                                    </td>
                                    <td>
                                        <span class="badge badge-light">{{ $row->kelas->nama_kelas }}</span>
                                    </td>
                                    <td>
                                        <select name="statuses[{{ $row->siswa->id }}]" class="form-control graduation-status-select" data-siswa-id="{{ $row->siswa->id }}">
                                            <option value="">Belum Ditentukan</option>
                                            @foreach($statusOptions as $value => $label)
                                                <option value="{{ $value }}" @selected(optional($item)->status === $value)>{{ $label }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td>
                                        <textarea name="notes[{{ $row->siswa->id }}]" rows="2" class="form-control graduation-note-field" data-siswa-id="{{ $row->siswa->id }}" placeholder="Catatan tambahan, khususnya untuk Lulus Bersyarat">{{ old("notes.{$row->siswa->id}", optional($item)->catatan) }}</textarea>
                                    </td>
                                    <td class="pr-4">
                                        @if(optional($item)->opened_at)
                                            <span class="badge badge-success">Sudah</span>
                                            <div class="text-muted small mt-1">{{ $item->opened_at->format('d M Y H:i') }}</div>
                                            @if($item->opened_ip)
                                                <div class="text-muted small">IP: {{ $item->opened_ip }}</div>
                                            @endif
                                            <button
                                                type="submit"
                                                formaction="{{ route('admin.kelulusan-pengumuman.reset-opened-student', $row->siswa->id) }}"
                                                formmethod="POST"
                                                class="btn btn-xs btn-outline-warning mt-2"
                                                onclick="return confirm('Reset riwayat buka amplop untuk {{ addslashes($row->siswa->nama_lengkap) }}?')"
                                            >
                                                <i class="fas fa-undo mr-1"></i> Reset
                                            </button>
                                        @else
                                            <span class="badge badge-secondary">Belum</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-5">
                                        Belum ada siswa kelas 12 pada tahun ajaran aktif.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($students->isNotEmpty())
                    <div class="card-footer bg-white border-0 d-flex flex-wrap align-items-center justify-content-between gap-2">
                        <div class="text-muted small">
                            <span class="badge badge-light border px-2 py-1" id="selectedGraduationCountFooter">Belum ada siswa dipilih untuk bulk action</span>
                            <span class="ml-2">Simpan akan menyimpan seluruh perubahan status dan catatan pada tabel ini.</span>
                        </div>
                        <button type="submit" class="btn btn-primary btn-lg" id="saveGraduationSubmit">
                            <i class="fas fa-save mr-1"></i> <span id="saveGraduationButtonLabel">Simpan Pengumuman</span>
                        </button>
                    </div>
                @endif
            </form>
        </div>
    </div>

    <div class="modal fade" id="confirmGraduationSaveModal" tabindex="-1" role="dialog" aria-labelledby="confirmGraduationSaveModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content border-0 shadow">
                <div class="modal-header">
                    <h5 class="modal-title" id="confirmGraduationSaveModalLabel">
                        <i class="fas fa-save text-primary mr-2"></i> Simpan Pengumuman Kelulusan
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Tutup">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    Pastikan status dan catatan siswa sudah benar. Perubahan ini akan disimpan ke data pengumuman kelulusan tahun ajaran aktif.
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-primary" id="confirmGraduationSaveButton">
                        <i class="fas fa-check mr-1"></i> Ya, Simpan
                    </button>
                </div>
            </div>
        </div>
    </div>
@stop
