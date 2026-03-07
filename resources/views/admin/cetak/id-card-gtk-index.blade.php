@extends('adminlte::page')

@section('title', 'Cetak ID Card GTK')

@section('content_header')
    <h1><i class="fas fa-id-badge"></i> Cetak ID Card GTK</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="card card-warning">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-id-badge"></i> Cetak Kartu Identitas GTK</h3>
                </div>
                <form action="{{ route('admin.cetak.id-card-gtk') }}" method="POST" id="formCetakIdGtk" target="_blank">
                    @csrf
                    <div class="card-body">
                        <div class="alert alert-warning">
                            <i class="fas fa-info-circle"></i>
                            <strong>Cetak Kartu Identitas GTK</strong><br>
                            Pilih kategori dan data GTK untuk mencetak ID Card. Kartu akan dicetak dalam format standar (85.6mm x 54mm) dengan bagian depan dan belakang.
                        </div>

                        {{-- Filter Section --}}
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="id_gtk_kategori">
                                        <i class="fas fa-user-tag"></i> Kategori PTK
                                    </label>
                                    <select name="kategori_ptk" id="id_gtk_kategori" class="form-control">
                                        <option value="">-- Semua Kategori --</option>
                                        <option value="Pendidik">Pendidik (Guru)</option>
                                        <option value="Tenaga Kependidikan">Tenaga Kependidikan</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="id_gtk_status">
                                        <i class="fas fa-briefcase"></i> Status Kepegawaian
                                    </label>
                                    <select name="status_kepegawaian" id="id_gtk_status" class="form-control">
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
                                <div class="form-group">
                                    <label>&nbsp;</label>
                                    <button type="button" class="btn btn-info btn-block" id="btnLoadGtk">
                                        <i class="fas fa-search"></i> Cari GTK
                                    </button>
                                </div>
                            </div>
                        </div>

                        <hr>

                        {{-- GTK List --}}
                        <div id="gtkListSection" style="display: none;">
                            <h5><i class="fas fa-list"></i> Pilih GTK</h5>
                            <div class="form-group">
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input" id="selectAll">
                                    <label class="custom-control-label font-weight-bold" for="selectAll">Pilih Semua</label>
                                </div>
                            </div>
                            <div class="row" id="gtkCheckboxes"></div>
                            <div id="selectedCount" class="alert alert-success mt-3" style="display: none;">
                                <i class="fas fa-check-circle"></i> <strong><span id="countText">0</span> GTK</strong> dipilih
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-warning btn-lg" id="btnCetak" disabled>
                            <i class="fas fa-id-badge"></i> Cetak ID Card GTK
                        </button>
                        <button type="button" class="btn btn-secondary btn-lg" id="btnReset">
                            <i class="fas fa-redo"></i> Reset
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@stop

@section('js')
<script>
$(document).ready(function() {
    $('#btnLoadGtk').on('click', function() {
        const kategori = $('#id_gtk_kategori').val();
        const status = $('#id_gtk_status').val();

        $.ajax({
            url: '{{ route("admin.cetak.gtk-by-filter") }}',
            method: 'GET',
            data: { kategori_ptk: kategori, status_kepegawaian: status },
            beforeSend: function() {
                $('#btnLoadGtk').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Loading...');
            },
            success: function(response) {
                if (response.success && response.data.length > 0) {
                    let html = '';
                    response.data.forEach(function(gtk) {
                        html += `
                            <div class="col-md-4 mb-2">
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
                    $('#gtkListSection').slideUp();
                }
            },
            error: function() { Swal.fire({ icon: 'error', title: 'Error', text: 'Gagal memuat data GTK.' }); },
            complete: function() {
                $('#btnLoadGtk').prop('disabled', false).html('<i class="fas fa-search"></i> Cari GTK');
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
        $('#gtkListSection').slideUp();
        $('#selectAll').prop('checked', false);
        updateCount();
    });

    $('#formCetakIdGtk').on('submit', function(e) {
        const count = $('.gtk-checkbox:checked').length;
        if (count === 0) { e.preventDefault(); return false; }
        Swal.fire({ icon: 'info', title: 'Sedang Mencetak...', text: `Mencetak ID Card untuk ${count} GTK. Mohon tunggu...`, allowOutsideClick: false, showConfirmButton: false, willOpen: () => { Swal.showLoading(); } });
        setTimeout(function() { Swal.close(); }, 5000);
    });
});
</script>
@stop

@section('css')
<style>
    .custom-control-label { cursor: pointer; }
</style>
@stop
