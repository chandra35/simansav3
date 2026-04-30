@extends('adminlte::page')

@section('title', 'Cetak Dokumen')
@section('plugins.Select2', true)

@section('content_header')
    <div class="simansa-hero">
        <div class="simansa-hero__main">
            <div class="simansa-hero__eyebrow">
                <i class="fas fa-print"></i>
                Akademik
            </div>
            <h1 class="simansa-hero__title">Cetak Dokumen</h1>
            <p class="simansa-hero__subtitle">
                Cetak absensi dan dokumen akademik lain dari satu alur yang lebih cepat. Filter dirapikan agar admin bisa memilih kelas massal tanpa bolak-balik.
            </p>
        </div>
        <div class="simansa-hero__side">
            <div class="simansa-hero-chip">
                <span class="simansa-hero-chip__label">Mode</span>
                <span class="simansa-hero-chip__value">{{ $isRestrictedWaliKelas ? 'Kelas Saya' : 'Massal Admin' }}</span>
            </div>
            <div class="simansa-hero-chip">
                <span class="simansa-hero-chip__label">Tahun Aktif</span>
                <span class="simansa-hero-chip__value">{{ $tahunPelajarans->firstWhere('is_active', true)?->nama ?? '-' }}</span>
            </div>
        </div>
    </div>
@stop

@section('content')
    <div class="row">
        <div class="col-md-12">
            {{-- Card: Cetak Absensi Kelas --}}
            <div class="card simansa-management-card">
                <div class="card-header">
                    <div class="simansa-toolbar">
                        <h3 class="card-title mb-0"><i class="fas fa-clipboard-check mr-2"></i> Cetak Absensi Kelas</h3>
                        <div class="simansa-toolbar__group">
                            <span class="badge badge-light px-3 py-2">{{ $isRestrictedWaliKelas ? 'Mode Wali Kelas' : 'Preview PDF Massal' }}</span>
                        </div>
                    </div>
                </div>
                <form action="{{ route('admin.cetak.absensi-batch') }}" method="POST" id="formCetakAbsensi" target="printPreviewFrame" data-no-overlay>
                    @csrf
                    @if($isRestrictedWaliKelas)
                        <input type="hidden" name="tahun_pelajaran_id" id="filter_tahun_pelajaran" value="{{ $defaultTahunPelajaranId }}">
                    @endif
                    <div class="card-body">
                        <div class="simansa-section-note mb-4">
                            <i class="fas fa-info-circle"></i> 
                            <strong>{{ $isRestrictedWaliKelas ? 'Cetak Absensi Kelas Anda' : 'Cetak Absensi Sekaligus' }}</strong><br>
                            {{ $isRestrictedWaliKelas ? 'Daftar kelas di bawah ini sudah otomatis dibatasi ke kelas yang Anda ampu.' : 'Pilih filter untuk mencetak absensi beberapa kelas sekaligus. Setiap kelas akan dicetak dalam halaman terpisah dalam satu file PDF.' }}
                        </div>

                        @unless($isRestrictedWaliKelas)
                            <div class="simansa-filter-panel simansa-filter-panel--accent mb-4">
                                <div class="simansa-form-section">
                                    <div>
                                        <h4 class="simansa-form-section__title">Filter Kelas</h4>
                                        <p class="simansa-form-section__desc">Pilih konteks kelas terlebih dulu. Saat tahun dan tingkat sudah lengkap, daftar kelas akan dimuat lebih cepat secara otomatis.</p>
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
                                        <label for="filter_tahun_pelajaran" class="simansa-filter-label"><i class="fas fa-calendar-alt"></i> Tahun Pelajaran <span class="text-danger">*</span></label>
                                        <select name="tahun_pelajaran_id" id="filter_tahun_pelajaran" class="form-control print-filter-select" required>
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
                                        <label for="filter_tingkat" class="simansa-filter-label"><i class="fas fa-layer-group"></i> Tingkat <span class="text-danger">*</span></label>
                                        <select name="tingkat" id="filter_tingkat" class="form-control print-filter-select" required>
                                            <option value="">-- Pilih Tingkat --</option>
                                            @foreach($tingkatOptions as $key => $label)
                                                <option value="{{ $key }}">{{ $label }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="filter_rombel" class="simansa-filter-label"><i class="fas fa-users"></i> Rombel</label>
                                        <select name="rombel" id="filter_rombel" class="form-control print-filter-select">
                                            <option value="">-- Semua Rombel --</option>
                                        </select>
                                        <div class="simansa-filter-hint">Rombel mengikuti tingkat yang dipilih.</div>
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="filter_kurikulum" class="simansa-filter-label"><i class="fas fa-book"></i> Kurikulum</label>
                                        <select name="kurikulum_id" id="filter_kurikulum" class="form-control print-filter-select">
                                            <option value="">-- Semua Kurikulum --</option>
                                            @foreach($kurikulums as $kurikulum)
                                                <option value="{{ $kurikulum->id }}">{{ $kurikulum->nama }}</option>
                                            @endforeach
                                        </select>
                                        <div class="simansa-filter-hint">Opsional untuk mempersempit hasil.</div>
                                    </div>
                                </div>
                            </div>
                            </div>
                        @endunless

                        <hr>

                        {{-- Kelas List Section --}}
                        <div id="kelasList" style="{{ $isRestrictedWaliKelas ? '' : 'display: none;' }}">
                            <div class="simansa-results-panel">
                            <div class="simansa-results-panel__title">
                                <h5><i class="fas fa-list mr-1"></i> Pilih Kelas yang Akan Dicetak</h5>
                                <div id="selectedCount" class="badge badge-success px-3 py-2" style="display: none;">
                                    <i class="fas fa-check-circle mr-1"></i> <strong><span id="countText">0</span> kelas</strong> dipilih
                                </div>
                            </div>
                            <div class="form-group mb-3">
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input" id="selectAll">
                                    <label class="custom-control-label font-weight-bold" for="selectAll">
                                        Pilih Semua
                                    </label>
                                </div>
                            </div>
                            
                            <div class="row simansa-selection-grid" id="kelasCheckboxes">
                                <!-- Checkboxes will be loaded here -->
                            </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn simansa-btn-strong btn-lg" id="btnCetak" disabled>
                            <i class="fas fa-print"></i> Cetak Absensi
                        </button>
                    </div>
                </form>
            </div>

            {{-- Card: Fitur Cetak Lainnya (Placeholder) --}}
            <div class="card card-secondary">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-file-alt"></i> Fitur Cetak Lainnya</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="small-box bg-info">
                                <div class="inner">
                                    <h4>Daftar Nilai</h4>
                                    <p>Coming Soon</p>
                                </div>
                                <div class="icon">
                                    <i class="fas fa-file-alt"></i>
                                </div>
                                <a href="#" class="small-box-footer disabled">
                                    Coming Soon <i class="fas fa-arrow-circle-right"></i>
                                </a>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="small-box bg-success">
                                <div class="inner">
                                    <h4>Rapor</h4>
                                    <p>Coming Soon</p>
                                </div>
                                <div class="icon">
                                    <i class="fas fa-book"></i>
                                </div>
                                <a href="#" class="small-box-footer disabled">
                                    Coming Soon <i class="fas fa-arrow-circle-right"></i>
                                </a>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="small-box bg-warning">
                                <div class="inner">
                                    <h4>Surat Keterangan</h4>
                                    <p>Coming Soon</p>
                                </div>
                                <div class="icon">
                                    <i class="fas fa-file-signature"></i>
                                </div>
                                <a href="#" class="small-box-footer disabled">
                                    Coming Soon <i class="fas fa-arrow-circle-right"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="printPreviewModal" tabindex="-1" role="dialog" aria-labelledby="printPreviewModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="printPreviewModalLabel"><i class="fas fa-file-pdf text-danger mr-1"></i> Preview Cetak</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Tutup">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body p-0 position-relative">
                    <div class="print-preview-loading" id="printPreviewLoading">
                        <div class="text-center">
                            <div class="spinner-border text-primary mb-3" role="status"></div>
                            <div class="font-weight-bold">Menyiapkan preview PDF...</div>
                            <div class="text-muted small">Gunakan toolbar PDF untuk print atau simpan.</div>
                        </div>
                    </div>
                    <iframe name="printPreviewFrame" id="printPreviewFrame" class="print-preview-frame" title="Preview Cetak Absensi"></iframe>
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
                    const tahunPelajaran = $('#filter_tahun_pelajaran').val();
                    const tingkat = $('#filter_tingkat').val();

                    if (tahunPelajaran && tingkat) {
                        loadKelasByCurrentContext();
                    }
                }, 280);
            }

            function loadKelasByCurrentContext() {
                const tahunPelajaran = $('#filter_tahun_pelajaran').val();
                const tingkat = $('#filter_tingkat').val();
                const rombel = $('#filter_rombel').val();
                const kurikulum = $('#filter_kurikulum').val();

                if (!tahunPelajaran) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Tahun Pelajaran Belum Tersedia',
                        text: 'Tahun pelajaran aktif belum ditemukan.'
                    });
                    return;
                }

                if (!isRestrictedWaliKelas && !tingkat) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Filter Belum Lengkap',
                        text: 'Tahun Pelajaran dan Tingkat harus dipilih!'
                    });
                    return;
                }

                $.ajax({
                    url: '{{ route('admin.cetak.kelas-by-filter') }}',
                    method: 'GET',
                    data: {
                        tahun_pelajaran_id: tahunPelajaran,
                        tingkat: tingkat,
                        rombel: rombel,
                        kurikulum_id: kurikulum
                    },
                    beforeSend: function() {
                        $('#btnLoadKelas').prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Memuat...');
                    },
                    success: function(response) {
                        if (response.success && response.data.length > 0) {
                            renderKelasList(response.data);
                            $('#kelasList').slideDown();
                        } else {
                            Swal.fire({
                                icon: 'info',
                                title: 'Tidak Ada Kelas',
                                text: 'Tidak ada kelas yang ditemukan dengan filter tersebut.'
                            });
                            $('#kelasCheckboxes').empty();
                            $('#selectAll').prop('checked', false);
                            updateSelectedCount();
                            $('#kelasList').slideUp();
                        }
                    },
                    error: function() {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Gagal memuat data kelas.'
                        });
                    },
                    complete: function() {
                        $('#btnLoadKelas').prop('disabled', false).html('<i class="fas fa-search mr-1"></i> Muat Kelas');
                    }
                });
            }

            function resetRombelOptions() {
                const $rombel = $('#filter_rombel');
                $rombel.html('<option value="">-- Semua Rombel --</option>').val('');
                $rombel.trigger('change.select2');
            }

            function populateRombelOptions(kelasList) {
                const $rombel = $('#filter_rombel');
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
                const tahunPelajaran = $('#filter_tahun_pelajaran').val();
                const tingkat = $('#filter_tingkat').val();
                const kurikulum = $('#filter_kurikulum').val();

                if (!tahunPelajaran || !tingkat) {
                    resetRombelOptions();
                    return;
                }

                $.ajax({
                    url: '{{ route('admin.cetak.kelas-by-filter') }}',
                    method: 'GET',
                    data: {
                        tahun_pelajaran_id: tahunPelajaran,
                        tingkat: tingkat,
                        kurikulum_id: kurikulum
                    },
                    success: function(response) {
                        populateRombelOptions(response.data || []);
                    },
                    error: function() {
                        resetRombelOptions();
                    }
                });
            }

            // Load Kelas by Filter
            $('#btnLoadKelas').on('click', function() {
                loadKelasByCurrentContext();
            });

            $('#filter_tahun_pelajaran, #filter_tingkat, #filter_kurikulum').on('change', function() {
                $('#kelasCheckboxes').empty();
                $('#kelasList').slideUp();
                $('#selectAll').prop('checked', false);
                updateSelectedCount();
                refreshRombelOptions();
                queueAutoLoad();
            });

            $('#filter_rombel').on('change', function() {
                $('#kelasCheckboxes').empty();
                $('#kelasList').slideUp();
                $('#selectAll').prop('checked', false);
                updateSelectedCount();
                queueAutoLoad();
            });

            // Render Kelas List
            function renderKelasList(kelasList) {
                let html = '';
                kelasList.forEach(function(kelas) {
                    html += `
                        <div class="col-md-4 col-lg-3 mb-3">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input kelas-checkbox" 
                                       id="kelas_${kelas.id}" name="kelas_ids[]" value="${kelas.id}">
                                <label class="custom-control-label" for="kelas_${kelas.id}">
                                    <strong>${kelas.nama_lengkap}</strong><br>
                                    <small class="text-muted d-block mt-1"><i class="fas fa-users mr-1"></i>${kelas.siswa_count} siswa</small>
                                    <small class="text-muted d-block">${kelas.kurikulum || 'Kurikulum belum diatur'}</small>
                                </label>
                            </div>
                        </div>
                    `;
                });
                $('#kelasCheckboxes').html(html);
                updateSelectedCount();
            }

            // Select All Checkbox
            $('#selectAll').on('change', function() {
                $('.kelas-checkbox').prop('checked', $(this).is(':checked'));
                updateSelectedCount();
            });

            // Individual Checkbox Change
            $(document).on('change', '.kelas-checkbox', function() {
                updateSelectedCount();
                
                // Update select all checkbox
                const total = $('.kelas-checkbox').length;
                const checked = $('.kelas-checkbox:checked').length;
                $('#selectAll').prop('checked', total === checked);
            });

            // Update Selected Count
            function updateSelectedCount() {
                const count = $('.kelas-checkbox:checked').length;
                $('#countText').text(count);
                
                if (count > 0) {
                    $('#selectedCount').slideDown();
                    $('#btnCetak').prop('disabled', false);
                } else {
                    $('#selectedCount').slideUp();
                    $('#btnCetak').prop('disabled', true);
                }
            }

            // Reset Button
            $('#btnReset').on('click', function() {
                $('#formCetakAbsensi')[0].reset();
                if ($.fn.select2) {
                    $('.print-filter-select').val('').trigger('change');
                }
                $('#kelasList').slideUp();
                $('#selectAll').prop('checked', false);
                $('#kelasCheckboxes').empty();
                refreshRombelOptions();
                updateSelectedCount();
            });

            // Form Submit
            $('#formCetakAbsensi').on('submit', function(e) {
                const count = $('.kelas-checkbox:checked').length;
                if (count === 0) {
                    e.preventDefault();
                    Swal.fire({
                        icon: 'warning',
                        title: 'Belum Ada Kelas Dipilih',
                        text: 'Pilih minimal 1 kelas untuk dicetak!'
                    });
                    return false;
                }

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
                    .html('<i class="fas fa-print"></i> Cetak Absensi');
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
        .select2-container--default .select2-selection--single {
            height: calc(2.25rem + 2px);
            border: 1px solid #ced4da;
            border-radius: .25rem;
            padding: .375rem .75rem;
        }
        .select2-container--default .select2-selection--single .select2-selection__rendered {
            color: #495057;
            line-height: 1.5rem;
            padding-left: 0;
            padding-right: 1.5rem;
        }
        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: calc(2.25rem + 2px);
            right: .35rem;
        }
        .select2-container {
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
        .small-box.disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }
        .small-box.disabled .small-box-footer {
            pointer-events: none;
        }
        .custom-control-label {
            cursor: pointer;
        }
    </style>
@stop
