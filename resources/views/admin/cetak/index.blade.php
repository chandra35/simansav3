@extends('adminlte::page')

@section('title', 'Cetak Dokumen')
@section('plugins.Select2', true)

@section('content_header')
    <div class="simansa-hero simansa-print-hero">
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
            <div class="card simansa-management-card simansa-print-card">
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
                        <div class="simansa-section-note simansa-print-note mb-4">
                            <i class="fas fa-info-circle"></i> 
                            <strong>{{ $isRestrictedWaliKelas ? 'Cetak Absensi Kelas Anda' : 'Cetak Absensi Sekaligus' }}</strong><br>
                            {{ $isRestrictedWaliKelas ? 'Daftar kelas di bawah ini sudah otomatis dibatasi ke kelas yang Anda ampu.' : 'Pilih filter untuk mencetak absensi beberapa kelas sekaligus. Setiap kelas akan dicetak dalam halaman terpisah dalam satu file PDF.' }}
                        </div>

                        @unless($isRestrictedWaliKelas)
                            <div class="simansa-filter-panel simansa-filter-panel--accent simansa-print-filter mb-4">
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
                            <div class="simansa-results-panel simansa-print-results">
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
            <div class="card simansa-management-card simansa-print-card simansa-coming-card">
                <div class="card-header">
                    <h3 class="card-title mb-0"><i class="fas fa-file-alt mr-2"></i> Fitur Cetak Lainnya</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 mb-3 mb-md-0">
                            <div class="simansa-print-feature">
                                <div class="simansa-print-feature__icon">
                                    <i class="fas fa-file-alt"></i>
                                </div>
                                <div>
                                    <h4>Daftar Nilai</h4>
                                    <p>Format cetak nilai per kelas sedang disiapkan.</p>
                                </div>
                                <span class="simansa-print-feature__badge">Coming Soon</span>
                            </div>
                        </div>

                        <div class="col-md-4 mb-3 mb-md-0">
                            <div class="simansa-print-feature">
                                <div class="simansa-print-feature__icon">
                                    <i class="fas fa-book"></i>
                                </div>
                                <div>
                                    <h4>Rapor</h4>
                                    <p>Template rapor akan mengikuti data akademik aktif.</p>
                                </div>
                                <span class="simansa-print-feature__badge">Coming Soon</span>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="simansa-print-feature">
                                <div class="simansa-print-feature__icon">
                                    <i class="fas fa-file-signature"></i>
                                </div>
                                <div>
                                    <h4>Surat Keterangan</h4>
                                    <p>Cetak surat akademik dari data siswa terpilih.</p>
                                </div>
                                <span class="simansa-print-feature__badge">Coming Soon</span>
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
        .simansa-print-hero {
            border-radius: 16px !important;
            background: #3b82f6 !important;
            color: #fff !important;
            box-shadow: 0 14px 32px rgba(59, 130, 246, 0.22) !important;
            border: 0 !important;
        }
        .simansa-print-hero .simansa-hero__eyebrow {
            color: rgba(255, 255, 255, 0.92) !important;
            background: transparent !important;
            padding: 0 !important;
            border-radius: 0 !important;
        }
        .simansa-print-hero .simansa-hero__title {
            color: #fff !important;
            font-size: 1.45rem !important;
            text-shadow: none !important;
        }
        .simansa-print-hero .simansa-hero__subtitle {
            color: rgba(255, 255, 255, 0.9) !important;
            max-width: 760px !important;
        }
        .simansa-print-hero .simansa-hero-chip {
            border-radius: 12px !important;
            background: rgba(255, 255, 255, 0.14) !important;
            border: 1px solid rgba(255, 255, 255, 0.18) !important;
            color: #fff !important;
            box-shadow: none !important;
        }
        .simansa-print-hero .simansa-hero-chip__label {
            color: rgba(255, 255, 255, 0.78) !important;
        }
        .simansa-print-hero .simansa-hero-chip__value {
            color: #fff !important;
        }
        .simansa-print-card {
            border: 1px solid #dbe4f0 !important;
            border-radius: 14px !important;
            box-shadow: 0 10px 28px rgba(15, 23, 42, 0.05) !important;
            overflow: hidden !important;
        }
        .simansa-print-card > .card-header {
            background: #fff !important;
            border-bottom: 1px solid #e5edf7 !important;
            color: #0f172a !important;
            padding: 0.95rem 1.25rem !important;
        }
        .simansa-print-card > .card-header .card-title,
        .simansa-print-card > .card-header .card-title i {
            font-size: 1.02rem !important;
            font-weight: 700 !important;
            color: #0f172a !important;
        }
        .simansa-print-card .card-body {
            padding: 1.25rem !important;
        }
        .simansa-print-note {
            border: 1px solid #dbeafe !important;
            border-left: 4px solid #3b82f6 !important;
            border-radius: 12px !important;
            background: #eff6ff !important;
            color: #1e3a8a !important;
            padding: 0.95rem 1rem !important;
        }
        .simansa-print-filter {
            border: 1px solid #dbe4f0 !important;
            border-radius: 14px !important;
            background: #f8fafc !important;
            padding: 1rem !important;
        }
        .simansa-print-filter.simansa-filter-panel--accent {
            background: #f8fafc !important;
            border-color: #dbe4f0 !important;
        }
        .simansa-form-section {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 1rem;
            margin-bottom: 1rem;
        }
        .simansa-form-section__title {
            font-size: 1rem;
            font-weight: 700;
            color: #0f172a;
            margin-bottom: 0.25rem;
        }
        .simansa-form-section__desc,
        .simansa-filter-hint {
            color: #64748b;
            font-size: 0.86rem;
        }
        .simansa-filter-label {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            font-size: 0.86rem;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 0.38rem;
        }
        .simansa-btn-contrast,
        .simansa-btn-strong {
            border-color: #2563eb;
            background: #2563eb;
            color: #fff;
            font-weight: 700;
        }
        .simansa-btn-contrast:hover,
        .simansa-btn-strong:hover {
            background: #1d4ed8;
            border-color: #1d4ed8;
            color: #fff;
        }
        .simansa-btn-muted {
            border-color: #cbd5e1;
            background: #fff;
            color: #334155;
            font-weight: 700;
        }
        .simansa-print-results {
            border: 1px solid #dbe4f0 !important;
            border-radius: 12px !important;
            background: #fff !important;
            padding: 1rem !important;
        }
        .simansa-results-panel__title {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 1rem;
            margin-bottom: 0.85rem;
        }
        .simansa-results-panel__title h5 {
            margin: 0;
            font-size: 0.98rem;
            font-weight: 700;
            color: #0f172a;
        }
        .simansa-coming-card {
            margin-top: 1rem;
        }
        .simansa-print-feature {
            position: relative;
            min-height: 136px;
            padding: 1rem;
            border: 1px solid #dbe4f0;
            border-top: 4px solid #3b82f6;
            border-radius: 14px;
            background: #fff;
            box-shadow: 0 8px 22px rgba(15, 23, 42, 0.04);
        }
        .simansa-print-feature__icon {
            width: 42px;
            height: 42px;
            border-radius: 12px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: #eef4ff;
            color: #2563eb;
            margin-bottom: 0.8rem;
        }
        .simansa-print-feature h4 {
            font-size: 1rem;
            font-weight: 700;
            color: #0f172a;
            margin-bottom: 0.35rem;
        }
        .simansa-print-feature p {
            margin: 0;
            color: #64748b;
            font-size: 0.86rem;
            line-height: 1.45;
            max-width: 80%;
        }
        .simansa-print-feature__badge {
            position: absolute;
            right: 1rem;
            bottom: 1rem;
            padding: 0.28rem 0.55rem;
            border-radius: 999px;
            background: #f1f5f9;
            color: #64748b;
            font-size: 0.74rem;
            font-weight: 700;
        }
        .select2-container--default .select2-selection--single {
            height: calc(2.25rem + 2px);
            border: 1px solid #cbd5e1;
            border-radius: .5rem;
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
        @media (max-width: 767.98px) {
            .simansa-form-section,
            .simansa-results-panel__title {
                flex-direction: column;
                align-items: stretch;
            }
            .simansa-toolbar__group {
                display: flex;
                gap: 0.5rem;
                flex-wrap: wrap;
            }
        }
    </style>
@stop
