@extends('adminlte::page')

@section('title', 'Cetak ID Card Siswa')

@section('content_header')
    <h1><i class="fas fa-id-card"></i> Cetak ID Card Siswa</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="card card-success">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-id-card"></i> Cetak Kartu Pelajar</h3>
                </div>
                <form action="{{ route('admin.cetak.id-card-siswa') }}" method="POST" id="formCetakIdSiswa" target="printPreviewFrame">
                    @csrf
                    @if($isRestrictedWaliKelas)
                        <input type="hidden" name="tahun_pelajaran_id" id="id_siswa_tahun_pelajaran" value="{{ $defaultTahunPelajaranId }}">
                    @endif
                    <div class="card-body">
                        <div class="alert alert-success">
                            <i class="fas fa-info-circle"></i>
                            <strong>Cetak Kartu Pelajar</strong><br>
                            {{ $isRestrictedWaliKelas ? 'Daftar kelas di bawah ini sudah otomatis dibatasi ke kelas yang Anda ampu.' : 'Pilih kelas untuk mencetak ID Card siswa. Kartu akan dicetak dalam format standar (85.6mm x 54mm) dengan bagian depan dan belakang.' }}
                        </div>

                        @unless($isRestrictedWaliKelas)
                            {{-- Filter Section --}}
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="id_siswa_tahun_pelajaran">
                                            <i class="fas fa-calendar-alt"></i> Tahun Pelajaran <span class="text-danger">*</span>
                                        </label>
                                        <select name="tahun_pelajaran_id" id="id_siswa_tahun_pelajaran" class="form-control" required>
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
                                        <label for="id_siswa_tingkat">
                                            <i class="fas fa-layer-group"></i> Tingkat <span class="text-danger">*</span>
                                        </label>
                                        <select name="tingkat" id="id_siswa_tingkat" class="form-control" required>
                                            <option value="">-- Pilih Tingkat --</option>
                                            @foreach($tingkatOptions as $key => $label)
                                                <option value="{{ $key }}">{{ $label }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="id_siswa_jurusan"><i class="fas fa-graduation-cap"></i> Jurusan</label>
                                        <select name="jurusan_id" id="id_siswa_jurusan" class="form-control">
                                            <option value="">-- Semua Jurusan --</option>
                                            @foreach($jurusans as $jurusan)
                                                <option value="{{ $jurusan->id }}">{{ $jurusan->nama }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>&nbsp;</label>
                                        <button type="button" class="btn btn-info btn-block" id="btnLoadKelas">
                                            <i class="fas fa-search"></i> Cari Kelas
                                        </button>
                                    </div>
                                </div>
                            </div>
                        @endunless

                        <hr>

                        {{-- Kelas List --}}
                        <div id="kelasList" style="{{ $isRestrictedWaliKelas ? '' : 'display: none;' }}">
                            <h5><i class="fas fa-list"></i> Pilih Kelas</h5>
                            <div class="form-group">
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input" id="selectAll">
                                    <label class="custom-control-label font-weight-bold" for="selectAll">Pilih Semua</label>
                                </div>
                            </div>
                            <div class="row" id="kelasCheckboxes"></div>
                            <div id="selectedCount" class="alert alert-success mt-3" style="display: none;">
                                <i class="fas fa-check-circle"></i> <strong><span id="countText">0</span> kelas</strong> dipilih
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-success btn-lg" id="btnCetak" disabled>
                            <i class="fas fa-id-card"></i> Cetak ID Card Siswa
                        </button>
                        <button type="button" class="btn btn-secondary btn-lg" id="btnReset">
                            <i class="fas fa-redo"></i> Reset
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

    function loadKelasByCurrentContext() {
        const tp = $('#id_siswa_tahun_pelajaran').val();
        const tingkat = $('#id_siswa_tingkat').val();
        const jurusan = $('#id_siswa_jurusan').val();

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
            data: { tahun_pelajaran_id: tp, tingkat: tingkat, jurusan_id: jurusan },
            beforeSend: function() {
                $('#btnLoadKelas').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Loading...');
            },
            success: function(response) {
                if (response.success && response.data.length > 0) {
                    let html = '';
                    response.data.forEach(function(kelas) {
                        html += `
                            <div class="col-md-4 mb-2">
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input kelas-checkbox"
                                           id="kelas_${kelas.id}" name="kelas_ids[]" value="${kelas.id}">
                                    <label class="custom-control-label" for="kelas_${kelas.id}">
                                        <strong>${kelas.nama_lengkap}</strong><br>
                                        <small class="text-muted"><i class="fas fa-users"></i> ${kelas.siswa_count} siswa</small>
                                    </label>
                                </div>
                            </div>`;
                    });
                    $('#kelasCheckboxes').html(html);
                    $('#kelasList').slideDown();
                    updateCount();
                } else {
                    Swal.fire({ icon: 'info', title: 'Tidak Ada Kelas', text: 'Tidak ada kelas yang ditemukan.' });
                    $('#kelasList').slideUp();
                }
            },
            error: function() { Swal.fire({ icon: 'error', title: 'Error', text: 'Gagal memuat data kelas.' }); },
            complete: function() {
                $('#btnLoadKelas').prop('disabled', false).html('<i class="fas fa-search"></i> Cari Kelas');
            }
        });
    }

    $('#btnLoadKelas').on('click', function() {
        loadKelasByCurrentContext();
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
        $('#kelasList').slideUp();
        $('#selectAll').prop('checked', false);
        updateCount();
    });

    $('#formCetakIdSiswa').on('submit', function(e) {
        const count = $('.kelas-checkbox:checked').length;
        if (count === 0) { e.preventDefault(); return false; }
        $('#btnCetak')
            .prop('disabled', true)
            .html('<i class="fas fa-spinner fa-spin"></i> Menyiapkan PDF...');
        previewPending = true;
        $('#printPreviewLoading').show();
        $('#printPreviewModal').modal('show');
    });

    if (isRestrictedWaliKelas) {
        loadKelasByCurrentContext();
    }

    $('#printPreviewFrame').on('load', function() {
        if (!previewPending) {
            return;
        }

        previewPending = false;
        $('#printPreviewLoading').hide();
        $('#btnCetak')
            .prop('disabled', false)
            .html('<i class="fas fa-id-card"></i> Cetak ID Card Siswa');
    });

    $('#printPreviewModal').on('hidden.bs.modal', function() {
        previewPending = false;
        $('#printPreviewFrame').attr('src', 'about:blank');
        $('#printPreviewLoading').show();
    });
});
</script>
@stop

@section('css')
<style>
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
