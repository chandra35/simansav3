@extends('adminlte::page')

@section('title', 'Cetak ID Card Siswa')
@section('plugins.Select2', true)

@section('content_header')
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1><i class="fas fa-id-card text-primary"></i> Cetak ID Card Siswa</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ $isRestrictedWaliKelas ? route('admin.gtk.dashboard') : route('admin.cetak.index') }}">{{ $isRestrictedWaliKelas ? 'Dashboard Saya' : 'Cetak Dokumen' }}</a></li>
                <li class="breadcrumb-item active">ID Card Siswa</li>
            </ol>
        </div>
    </div>
@stop

@section('content')
<div class="id-card-siswa-page">
    <div class="card bg-gradient-primary text-white mb-4">
        <div class="card-body">
            <div class="row align-items-center">
                <div class="col-lg-8">
                    <h3 class="mb-1"><i class="fas fa-id-card mr-1"></i> Cetak Kartu Pelajar</h3>
                    <p class="mb-2 text-white-50">Pilih kelas dan siapkan kartu pelajar dalam preview PDF tanpa meninggalkan halaman kerja.</p>
                    <p class="mb-0">Kartu memakai ukuran standar vertikal dan siap dicetak pada kertas A4.</p>
                </div>
                <div class="col-lg-4 mt-3 mt-lg-0 text-center">
                    <div class="text-white-50 small text-uppercase font-weight-bold">Mode</div>
                    <h3 class="mb-0 text-white">{{ $isRestrictedWaliKelas ? 'Kelas Saya' : 'Massal Admin' }}</h3>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="card card-outline card-primary">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-id-card"></i> Cetak Kartu Pelajar</h3>
                </div>
                <form action="{{ route('admin.cetak.id-card-siswa') }}" method="POST" id="formCetakIdSiswa" target="printPreviewFrame" data-no-overlay>
                    @csrf
                    @if($isRestrictedWaliKelas)
                        <input type="hidden" name="tahun_pelajaran_id" id="id_siswa_tahun_pelajaran" value="{{ $defaultTahunPelajaranId }}">
                    @endif
                    <div class="card-body">
                        <div class="simansa-section-note mb-4">
                            <i class="fas fa-info-circle"></i>
                            <strong>Cetak Kartu Pelajar</strong><br>
                            {{ $isRestrictedWaliKelas ? 'Daftar kelas di bawah ini sudah otomatis dibatasi ke kelas yang Anda ampu.' : 'Pilih kelas untuk mencetak ID Card siswa. Kartu memakai ukuran standar vertikal 54mm x 86mm dengan layout depan-belakang untuk kertas A4 portrait.' }}
                        </div>

                        @unless($isRestrictedWaliKelas)
                            {{-- Filter Section --}}
                            <div class="simansa-filter-panel mb-4">
                            <div class="simansa-form-section">
                                <div>
                                    <h4 class="simansa-form-section__title">Filter Kelas</h4>
                                    <p class="simansa-form-section__desc">Setelah tahun dan tingkat dipilih, daftar kelas akan menyesuaikan lebih cepat dan bisa dimuat ulang kapan saja.</p>
                                </div>
                                <div class="simansa-toolbar__group">
                                    <button type="button" class="btn simansa-btn-contrast" id="btnLoadKelas">
                                        <i class="fas fa-search mr-1"></i> Muat Kelas
                                    </button>
                                    <button type="button" class="btn simansa-btn-muted" id="btnReset">
                                        <i class="fas fa-redo mr-1"></i> Reset
                                    </button>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="id_siswa_tahun_pelajaran" class="simansa-filter-label"><i class="fas fa-calendar-alt"></i> Tahun Pelajaran <span class="text-danger">*</span></label>
                                        <select name="tahun_pelajaran_id" id="id_siswa_tahun_pelajaran" class="form-control print-filter-select" required>
                                            <option value="">-- Pilih Tahun Pelajaran --</option>
                                            @foreach($tahunPelajarans as $tp)
                                                <option value="{{ $tp->id }}" {{ $tp->is_active ? 'selected' : '' }}>
                                                    {{ $tp->nama }} {{ $tp->is_active ? '(Aktif)' : '' }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="id_siswa_tingkat" class="simansa-filter-label"><i class="fas fa-layer-group"></i> Tingkat <span class="text-danger">*</span></label>
                                        <select name="tingkat" id="id_siswa_tingkat" class="form-control print-filter-select" required>
                                            <option value="">-- Pilih Tingkat --</option>
                                            @foreach($tingkatOptions as $key => $label)
                                                <option value="{{ $key }}">{{ $label }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="id_siswa_rombel" class="simansa-filter-label"><i class="fas fa-users"></i> Rombel</label>
                                        <select name="rombel" id="id_siswa_rombel" class="form-control print-filter-select">
                                            <option value="">-- Semua Rombel --</option>
                                        </select>
                                        <div class="simansa-filter-hint">Rombel mengikuti tingkat yang dipilih.</div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="simansa-mini-stat mt-md-4">
                                        <span class="simansa-mini-stat__label">Output</span>
                                        <span class="simansa-mini-stat__value">PDF Preview</span>
                                    </div>
                                </div>
                            </div>
                            </div>
                        @endunless

                        <hr>

                        {{-- Kelas List --}}
                        <div id="kelasList" style="{{ $isRestrictedWaliKelas ? '' : 'display: none;' }}">
                            <div class="simansa-results-panel">
                            <div class="simansa-results-panel__title">
                                <h5><i class="fas fa-list mr-1"></i> Pilih Kelas</h5>
                                <div id="selectedCount" class="badge badge-success px-3 py-2" style="display: none;">
                                    <i class="fas fa-check-circle mr-1"></i> <strong><span id="countText">0</span> kelas</strong> dipilih
                                </div>
                            </div>
                            <div class="form-group mb-3">
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input" id="selectAll">
                                    <label class="custom-control-label font-weight-bold" for="selectAll">Pilih Semua</label>
                                </div>
                            </div>
                            <div class="row simansa-selection-grid" id="kelasCheckboxes"></div>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn simansa-btn-strong btn-lg" id="btnCetak" disabled>
                            <i class="fas fa-id-card"></i> Cetak ID Card Siswa
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

    <div class="modal fade" id="printPreviewModal" tabindex="-1" role="dialog" aria-labelledby="printPreviewModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="printPreviewModalLabel"><i class="fas fa-file-pdf text-danger mr-1"></i> Preview ID Card Siswa</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Tutup">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body p-0 position-relative">
                    <div class="print-preview-loading" id="printPreviewLoading">
                        <div class="text-center">
                            <div class="spinner-border text-success mb-3" role="status"></div>
                            <div class="font-weight-bold">Menyiapkan preview PDF...</div>
                            <div class="text-muted small">Gunakan toolbar PDF untuk print atau simpan.</div>
                        </div>
                    </div>
                    <iframe name="printPreviewFrame" id="printPreviewFrame" class="print-preview-frame" title="Preview ID Card Siswa"></iframe>
                </div>
            </div>
        </div>
    </div>
@stop

@section('js')
<script>
$(document).ready(function() {
    const isRestrictedWaliKelas = @json($isRestrictedWaliKelas);
    let previewPending = false;
    const $printPreviewModal = $('#printPreviewModal');
    const $printPreviewLoading = $('#printPreviewLoading');
    const $printPreviewFrame = $('#printPreviewFrame');

    if ($.fn.select2) {
        $('.print-filter-select').select2({
            width: '100%',
            allowClear: false,
            minimumResultsForSearch: 8,
        });
    }

    let autoLoadTimer = null;

    function queueAutoLoad() {
        if (isRestrictedWaliKelas) {
            return;
        }

        clearTimeout(autoLoadTimer);
        autoLoadTimer = setTimeout(function() {
            const tp = $('#id_siswa_tahun_pelajaran').val();
            const tingkat = $('#id_siswa_tingkat').val();
            if (tp && tingkat) {
                loadKelasByCurrentContext();
            }
        }, 280);
    }

    function loadKelasByCurrentContext() {
        const tp = $('#id_siswa_tahun_pelajaran').val();
        const tingkat = $('#id_siswa_tingkat').val();
        const rombel = $('#id_siswa_rombel').val();

        if (!tp) {
            Swal.fire({ icon: 'warning', title: 'Tahun Pelajaran Belum Tersedia', text: 'Tahun pelajaran aktif belum ditemukan.' });
            return;
        }

        if (!isRestrictedWaliKelas && !tingkat) {
            Swal.fire({ icon: 'warning', title: 'Filter Belum Lengkap', text: 'Tahun Pelajaran dan Tingkat harus dipilih!' });
            return;
        }

        $.ajax({
            url: '{{ route("admin.cetak.kelas-by-filter") }}',
            method: 'GET',
            data: { tahun_pelajaran_id: tp, tingkat: tingkat, rombel: rombel },
            beforeSend: function() {
                $('#btnLoadKelas').prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Memuat...');
            },
            success: function(response) {
                if (response.success && response.data.length > 0) {
                    let html = '';
                    response.data.forEach(function(kelas) {
                        html += `
                            <div class="col-md-4 col-lg-3 mb-3">
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input kelas-checkbox"
                                           id="kelas_${kelas.id}" name="kelas_ids[]" value="${kelas.id}">
                                    <label class="custom-control-label" for="kelas_${kelas.id}">
                                        <strong>${kelas.nama_lengkap}</strong><br>
                                        <small class="text-muted d-block mt-1"><i class="fas fa-users mr-1"></i>${kelas.siswa_count} siswa</small>
                                    </label>
                                </div>
                            </div>`;
                    });
                    $('#kelasCheckboxes').html(html);
                    $('#kelasList').slideDown();
                    updateCount();
                } else {
                    Swal.fire({ icon: 'info', title: 'Tidak Ada Kelas', text: 'Tidak ada kelas yang ditemukan.' });
                    $('#kelasCheckboxes').empty();
                    $('#selectAll').prop('checked', false);
                    updateCount();
                    $('#kelasList').slideUp();
                }
            },
            error: function() { Swal.fire({ icon: 'error', title: 'Error', text: 'Gagal memuat data kelas.' }); },
            complete: function() {
                $('#btnLoadKelas').prop('disabled', false).html('<i class="fas fa-search mr-1"></i> Muat Kelas');
            }
        });
    }

    function resetRombelOptions() {
        const $rombel = $('#id_siswa_rombel');
        $rombel.html('<option value="">-- Semua Rombel --</option>').val('');
        $rombel.trigger('change.select2');
    }

    function populateRombelOptions(kelasList) {
        const $rombel = $('#id_siswa_rombel');
        const currentValue = $rombel.val();
        const rombels = [...new Set(kelasList.map(function(kelas) {
            return kelas.rombel;
        }).filter(Boolean))].sort(function(a, b) {
            return a.localeCompare(b, 'id');
        });

        $rombel.html('<option value="">-- Semua Rombel --</option>');
        rombels.forEach(function(rombel) {
            $rombel.append(new Option(rombel, rombel, false, rombel === currentValue));
        });
        $rombel.trigger('change.select2');
    }

    function refreshRombelOptions() {
        const tp = $('#id_siswa_tahun_pelajaran').val();
        const tingkat = $('#id_siswa_tingkat').val();

        if (!tp || !tingkat) {
            resetRombelOptions();
            return;
        }

        $.ajax({
            url: '{{ route("admin.cetak.kelas-by-filter") }}',
            method: 'GET',
            data: { tahun_pelajaran_id: tp, tingkat: tingkat },
            success: function(response) {
                populateRombelOptions(response.data || []);
            },
            error: function() {
                resetRombelOptions();
            }
        });
    }

    $('#btnLoadKelas').on('click', function() {
        loadKelasByCurrentContext();
    });

    $('#id_siswa_tahun_pelajaran, #id_siswa_tingkat').on('change', function() {
        $('#kelasCheckboxes').empty();
        $('#kelasList').slideUp();
        $('#selectAll').prop('checked', false);
        updateCount();
        refreshRombelOptions();
        queueAutoLoad();
    });

    $('#id_siswa_rombel').on('change', function() {
        $('#kelasCheckboxes').empty();
        $('#kelasList').slideUp();
        $('#selectAll').prop('checked', false);
        updateCount();
        queueAutoLoad();
    });

    $('#selectAll').on('change', function() {
        $('.kelas-checkbox').prop('checked', $(this).is(':checked'));
        updateCount();
    });
    $(document).on('change', '.kelas-checkbox', function() {
        updateCount();
        const total = $('.kelas-checkbox').length;
        const checked = $('.kelas-checkbox:checked').length;
        $('#selectAll').prop('checked', total === checked);
    });

    function updateCount() {
        const count = $('.kelas-checkbox:checked').length;
        $('#countText').text(count);
        count > 0 ? $('#selectedCount').slideDown() : $('#selectedCount').slideUp();
        $('#btnCetak').prop('disabled', count === 0);
    }

    $('#btnReset').on('click', function() {
        $('#formCetakIdSiswa')[0].reset();
        if ($.fn.select2) {
            $('.print-filter-select').val('').trigger('change');
        }
        $('#kelasList').slideUp();
        $('#selectAll').prop('checked', false);
        $('#kelasCheckboxes').empty();
        updateCount();
    });

    $('#formCetakIdSiswa').on('submit', function(e) {
        const count = $('.kelas-checkbox:checked').length;
        if (count === 0) { e.preventDefault(); return false; }
        $('#btnCetak')
            .prop('disabled', true)
            .html('<i class="fas fa-spinner fa-spin"></i> Menyiapkan PDF...');
        previewPending = true;
        if (window.hideAppGlobalOverlay) {
            window.hideAppGlobalOverlay();
        }
        $printPreviewLoading.show();
        $printPreviewModal.modal('show');
    });

    if (isRestrictedWaliKelas) {
        loadKelasByCurrentContext();
    } else {
        refreshRombelOptions();
    }

    $printPreviewFrame.on('load', function() {
        if (!previewPending) {
            return;
        }

        previewPending = false;
        if (window.hideAppGlobalOverlay) {
            window.hideAppGlobalOverlay();
        }
        $printPreviewLoading.hide();
        $('#btnCetak')
            .prop('disabled', false)
            .html('<i class="fas fa-id-card"></i> Cetak ID Card Siswa');
    });

    $printPreviewModal.on('hidden.bs.modal', function() {
        previewPending = false;
        $printPreviewFrame.attr('src', 'about:blank');
        $printPreviewLoading.show();
    });
});
</script>
@stop

@section('css')
<style>
    .id-card-siswa-page > .bg-gradient-primary { overflow:hidden; border:0; border-radius:16px; box-shadow:0 12px 28px rgba(15,23,42,.1); }
    .id-card-siswa-page > .bg-gradient-primary .card-body { padding:1.2rem 1.25rem; }
    .id-card-siswa-page > .bg-gradient-primary h3 { font-size:1.35rem; font-weight:700; overflow-wrap:anywhere; }
    .id-card-siswa-page .select2-container--default .select2-selection--single {
        height: calc(2.25rem + 2px);
        border: 1px solid #ced4da;
        border-radius: .25rem;
        padding: .375rem .75rem;
    }
    .id-card-siswa-page .select2-container--default .select2-selection--single .select2-selection__rendered {
        color: #495057;
        line-height: 1.5rem;
        padding-left: 0;
        padding-right: 1.5rem;
    }
    .id-card-siswa-page .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: calc(2.25rem + 2px);
        right: .35rem;
    }
    .id-card-siswa-page .select2-container {
        display: block;
    }
    .print-preview-frame {
        width: 100%;
        height: 78vh;
        border: 0;
        background: #f4f6f9;
    }
    .print-preview-loading {
        position: absolute;
        inset: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        background: rgba(255, 255, 255, 0.92);
        z-index: 2;
    }
    .id-card-siswa-page .custom-control-label { cursor: pointer; }
    @media (max-width:575.98px) {
        .id-card-siswa-page > .bg-gradient-primary .card-body { padding:1rem; }
        .id-card-siswa-page > .bg-gradient-primary h3 { font-size:1.1rem; }
        .id-card-siswa-page .simansa-toolbar__group { display:flex; width:100%; gap:.5rem; }
        .id-card-siswa-page .simansa-toolbar__group .btn { flex:1 1 0; white-space:normal; }
        .id-card-siswa-page .card-footer .btn { width:100%; }
    }
</style>
@stop
