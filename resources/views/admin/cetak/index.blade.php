@extends('adminlte::page')

@section('title', 'Cetak Dokumen')
@section('plugins.Select2', true)

@section('content_header')
    <h1><i class="fas fa-print"></i> Cetak Dokumen</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-md-12">
            {{-- Card: Cetak Absensi Kelas --}}
            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-clipboard-check"></i> Cetak Absensi Kelas (Batch)</h3>
                </div>
                <form action="{{ route('admin.cetak.absensi-batch') }}" method="POST" id="formCetakAbsensi" target="printPreviewFrame" data-no-overlay>
                    @csrf
                    @if($isRestrictedWaliKelas)
                        <input type="hidden" name="tahun_pelajaran_id" id="filter_tahun_pelajaran" value="{{ $defaultTahunPelajaranId }}">
                    @endif
                    <div class="card-body">
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> 
                            <strong>{{ $isRestrictedWaliKelas ? 'Cetak Absensi Kelas Anda' : 'Cetak Absensi Sekaligus' }}</strong><br>
                            {{ $isRestrictedWaliKelas ? 'Daftar kelas di bawah ini sudah otomatis dibatasi ke kelas yang Anda ampu.' : 'Pilih filter untuk mencetak absensi beberapa kelas sekaligus. Setiap kelas akan dicetak dalam halaman terpisah dalam satu file PDF.' }}
                        </div>

                        @unless($isRestrictedWaliKelas)
                            {{-- Filter Section --}}
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="filter_tahun_pelajaran">
                                            <i class="fas fa-calendar-alt"></i> Tahun Pelajaran <span class="text-danger">*</span>
                                        </label>
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
                                        <label for="filter_tingkat">
                                            <i class="fas fa-layer-group"></i> Tingkat <span class="text-danger">*</span>
                                        </label>
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
                                        <label for="filter_jurusan"><i class="fas fa-graduation-cap"></i> Jurusan</label>
                                        <select name="jurusan_id" id="filter_jurusan" class="form-control print-filter-select">
                                            <option value="">-- Semua Jurusan --</option>
                                            @foreach($jurusans as $jurusan)
                                                <option value="{{ $jurusan->id }}">{{ $jurusan->nama }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="filter_kurikulum"><i class="fas fa-book"></i> Kurikulum</label>
                                        <select name="kurikulum_id" id="filter_kurikulum" class="form-control print-filter-select">
                                            <option value="">-- Semua Kurikulum --</option>
                                            @foreach($kurikulums as $kurikulum)
                                                <option value="{{ $kurikulum->id }}">{{ $kurikulum->nama }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-12">
                                    <button type="button" class="btn btn-info" id="btnLoadKelas">
                                        <i class="fas fa-search"></i> Cari Kelas
                                    </button>
                                </div>
                            </div>
                        @endunless

                        <hr>

                        {{-- Kelas List Section --}}
                        <div id="kelasList" style="{{ $isRestrictedWaliKelas ? '' : 'display: none;' }}">
                            <h5><i class="fas fa-list"></i> Pilih Kelas yang Akan Dicetak</h5>
                            <div class="form-group">
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input" id="selectAll">
                                    <label class="custom-control-label font-weight-bold" for="selectAll">
                                        Pilih Semua
                                    </label>
                                </div>
                            </div>
                            
                            <div class="row" id="kelasCheckboxes">
                                <!-- Checkboxes will be loaded here -->
                            </div>

                            <div id="selectedCount" class="alert alert-success mt-3" style="display: none;">
                                <i class="fas fa-check-circle"></i> <strong><span id="countText">0</span> kelas</strong> dipilih
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary btn-lg" id="btnCetak" disabled>
                            <i class="fas fa-print"></i> Cetak Absensi
                        </button>
                        <button type="button" class="btn btn-secondary btn-lg" id="btnReset">
                            <i class="fas fa-redo"></i> Reset
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

            function loadKelasByCurrentContext() {
                const tahunPelajaran = $('#filter_tahun_pelajaran').val();
                const tingkat = $('#filter_tingkat').val();
                const jurusan = $('#filter_jurusan').val();
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
                        jurusan_id: jurusan,
                        kurikulum_id: kurikulum
                    },
                    beforeSend: function() {
                        $('#btnLoadKelas').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Loading...');
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
                        $('#btnLoadKelas').prop('disabled', false).html('<i class="fas fa-search"></i> Cari Kelas');
                    }
                });
            }

            // Load Kelas by Filter
            $('#btnLoadKelas').on('click', function() {
                loadKelasByCurrentContext();
            });

            // Render Kelas List
            function renderKelasList(kelasList) {
                let html = '';
                kelasList.forEach(function(kelas) {
                    html += `
                        <div class="col-md-4 mb-2">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input kelas-checkbox" 
                                       id="kelas_${kelas.id}" name="kelas_ids[]" value="${kelas.id}">
                                <label class="custom-control-label" for="kelas_${kelas.id}">
                                    <strong>${kelas.nama_lengkap}</strong><br>
                                    <small class="text-muted">
                                        <i class="fas fa-users"></i> ${kelas.siswa_count} siswa
                                    </small>
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
                $('#kelasList').slideUp();
                $('#selectAll').prop('checked', false);
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
