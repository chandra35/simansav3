@extends('adminlte::page')

@section('title', 'Cetak ID Card GTK')
@section('plugins.Select2', true)

@section('content_header')
    <div class="simansa-hero">
        <div class="simansa-hero__main">
            <div class="simansa-hero__eyebrow"><i class="fas fa-id-badge"></i> Akademik</div>
            <h1 class="simansa-hero__title">Cetak ID Card GTK</h1>
            <p class="simansa-hero__subtitle">Pilih kategori GTK dan siapkan preview kartu identitas tanpa meninggalkan halaman admin.</p>
        </div>
    </div>
@stop

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="card simansa-management-card">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-id-badge"></i> Cetak Kartu Identitas GTK</h3>
                </div>
                <form action="{{ route('admin.cetak.id-card-gtk') }}" method="POST" id="formCetakIdGtk" target="printPreviewFrame" data-no-overlay>
                    @csrf
                    <div class="card-body">
                        <div class="simansa-section-note mb-4">
                            <i class="fas fa-info-circle"></i>
                            <strong>Cetak Kartu Identitas GTK</strong><br>
                            Pilih kategori dan data GTK untuk mencetak ID Card. Kartu akan dicetak dalam format standar (85.6mm x 54mm) dengan bagian depan dan belakang.
                        </div>

                        {{-- Filter Section --}}
                        <div class="simansa-filter-panel mb-4">
                        <div class="simansa-form-section">
                            <div>
                                <h4 class="simansa-form-section__title">Filter GTK</h4>
                                <p class="simansa-form-section__desc">Pilih kategori PTK atau status kepegawaian. Hasil bisa dimuat ulang kapan saja dan tetap tampil di preview modal.</p>
                            </div>
                            <div class="simansa-toolbar__group">
                                <button type="button" class="btn simansa-btn-contrast" id="btnLoadGtk">
                                    <i class="fas fa-search mr-1"></i> Muat GTK
                                </button>
                                <button type="button" class="btn simansa-btn-muted" id="btnReset">
                                    <i class="fas fa-redo mr-1"></i> Reset
                                </button>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="id_gtk_kategori" class="simansa-filter-label"><i class="fas fa-user-tag"></i> Kategori PTK</label>
                                    <select name="kategori_ptk" id="id_gtk_kategori" class="form-control print-filter-select">
                                        <option value="">-- Semua Kategori --</option>
                                        <option value="Pendidik">Pendidik (Guru)</option>
                                        <option value="Tenaga Kependidikan">Tenaga Kependidikan</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="id_gtk_status" class="simansa-filter-label"><i class="fas fa-briefcase"></i> Status Kepegawaian</label>
                                    <select name="status_kepegawaian" id="id_gtk_status" class="form-control print-filter-select">
                                        <option value="">-- Semua Status --</option>
                                        <option value="PNS">PNS</option>
                                        <option value="PPPK">PPPK</option>
                                        <option value="GTY">GTY</option>
                                        <option value="PTY">PTY</option>
                                        <option value="Honorer">Honorer</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="simansa-mini-stat mt-md-4">
                                    <span class="simansa-mini-stat__label">Output</span>
                                    <span class="simansa-mini-stat__value">PDF Preview</span>
                                </div>
                            </div>
                        </div>
                        </div>

                        <hr>

                        {{-- GTK List --}}
                        <div id="gtkListSection" style="display: none;">
                            <div class="simansa-results-panel">
                            <div class="simansa-results-panel__title">
                                <h5><i class="fas fa-list mr-1"></i> Pilih GTK</h5>
                                <div id="selectedCount" class="badge badge-success px-3 py-2" style="display: none;">
                                    <i class="fas fa-check-circle mr-1"></i> <strong><span id="countText">0</span> GTK</strong> dipilih
                                </div>
                            </div>
                            <div class="form-group mb-3">
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input" id="selectAll">
                                    <label class="custom-control-label font-weight-bold" for="selectAll">Pilih Semua</label>
                                </div>
                            </div>
                            <div class="row simansa-selection-grid" id="gtkCheckboxes"></div>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn simansa-btn-strong btn-lg" id="btnCetak" disabled>
                            <i class="fas fa-id-badge"></i> Cetak ID Card GTK
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="printPreviewModal" tabindex="-1" role="dialog" aria-labelledby="printPreviewModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="printPreviewModalLabel"><i class="fas fa-file-pdf text-danger mr-1"></i> Preview ID Card GTK</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Tutup">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body p-0 position-relative">
                    <div class="print-preview-loading" id="printPreviewLoading">
                        <div class="text-center">
                            <div class="spinner-border text-warning mb-3" role="status"></div>
                            <div class="font-weight-bold">Menyiapkan preview PDF...</div>
                            <div class="text-muted small">Gunakan toolbar PDF untuk print atau simpan.</div>
                        </div>
                    </div>
                    <iframe name="printPreviewFrame" id="printPreviewFrame" class="print-preview-frame" title="Preview ID Card GTK"></iframe>
                </div>
            </div>
        </div>
    </div>
@stop

@section('js')
<script>
$(document).ready(function() {
    let previewPending = false;
    let autoLoadTimer = null;
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

    function queueAutoLoad() {
        clearTimeout(autoLoadTimer);
        autoLoadTimer = setTimeout(function() {
            $('#btnLoadGtk').trigger('click');
        }, 280);
    }

    $('#btnLoadGtk').on('click', function() {
        const kategori = $('#id_gtk_kategori').val();
        const status = $('#id_gtk_status').val();

        $.ajax({
            url: '{{ route("admin.cetak.gtk-by-filter") }}',
            method: 'GET',
            data: { kategori_ptk: kategori, status_kepegawaian: status },
            beforeSend: function() {
                $('#btnLoadGtk').prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Memuat...');
            },
            success: function(response) {
                if (response.success && response.data.length > 0) {
                    let html = '';
                    response.data.forEach(function(gtk) {
                        html += `
                            <div class="col-md-4 col-lg-3 mb-3">
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input gtk-checkbox"
                                           id="gtk_${gtk.id}" name="gtk_ids[]" value="${gtk.id}">
                                    <label class="custom-control-label" for="gtk_${gtk.id}">
                                        <strong>${gtk.nama_lengkap}</strong><br>
                                        <small class="text-muted">
                                            ${gtk.jabatan} &middot; ${gtk.status_kepegawaian}
                                        </small>
                                    </label>
                                </div>
                            </div>`;
                    });
                    $('#gtkCheckboxes').html(html);
                    $('#gtkListSection').slideDown();
                    updateCount();
                } else {
                    Swal.fire({ icon: 'info', title: 'Tidak Ada GTK', text: 'Tidak ada GTK yang ditemukan.' });
                    $('#gtkCheckboxes').empty();
                    $('#selectAll').prop('checked', false);
                    updateCount();
                    $('#gtkListSection').slideUp();
                }
            },
            error: function() { Swal.fire({ icon: 'error', title: 'Error', text: 'Gagal memuat data GTK.' }); },
            complete: function() {
                $('#btnLoadGtk').prop('disabled', false).html('<i class="fas fa-search mr-1"></i> Muat GTK');
            }
        });
    });

    $('#selectAll').on('change', function() {
        $('.gtk-checkbox').prop('checked', $(this).is(':checked'));
        updateCount();
    });
    $(document).on('change', '.gtk-checkbox', function() {
        updateCount();
        const total = $('.gtk-checkbox').length;
        const checked = $('.gtk-checkbox:checked').length;
        $('#selectAll').prop('checked', total === checked);
    });

    function updateCount() {
        const count = $('.gtk-checkbox:checked').length;
        $('#countText').text(count);
        count > 0 ? $('#selectedCount').slideDown() : $('#selectedCount').slideUp();
        $('#btnCetak').prop('disabled', count === 0);
    }

    $('#btnReset').on('click', function() {
        $('#formCetakIdGtk')[0].reset();
        if ($.fn.select2) {
            $('.print-filter-select').val('').trigger('change');
        }
        $('#gtkListSection').slideUp();
        $('#selectAll').prop('checked', false);
        $('#gtkCheckboxes').empty();
        updateCount();
    });

    $('#id_gtk_kategori, #id_gtk_status').on('change', function() {
        $('#gtkCheckboxes').empty();
        $('#gtkListSection').slideUp();
        $('#selectAll').prop('checked', false);
        updateCount();
        queueAutoLoad();
    });

    $('#formCetakIdGtk').on('submit', function(e) {
        const count = $('.gtk-checkbox:checked').length;
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
            .html('<i class="fas fa-id-badge"></i> Cetak ID Card GTK');
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
    .custom-control-label { cursor: pointer; }
</style>
@stop
