@extends('adminlte::page')

@section('title', 'Cek NIK Dukcapil - SIMANSA')

@section('plugins.Sweetalert2', true)

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="m-0"><i class="fas fa-fingerprint"></i> Cek NIK Dukcapil</h1>
        <small class="text-muted">via BKN SIASN API</small>
    </div>
@stop

@section('content')
<div class="row">
    {{-- Form Card --}}
    <div class="col-md-12">
        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-search"></i> Validasi Data NIK ke Dukcapil</h3>
            </div>
            <div class="card-body">
                <form id="formCekNik">
                    @csrf
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>NIK <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="fas fa-fingerprint"></i></span>
                                    </div>
                                    <input type="text" class="form-control" id="nik" name="nik"
                                           placeholder="16 digit NIK" maxlength="16" pattern="\d{16}"
                                           oninput="this.value=this.value.replace(/\D/g,'')" required>
                                </div>
                                <small class="form-text text-muted">16 digit angka sesuai KTP</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Nomor KK <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="fas fa-users"></i></span>
                                    </div>
                                    <input type="text" class="form-control" id="nokk" name="nokk"
                                           placeholder="16 digit No. Kartu Keluarga" maxlength="16" pattern="\d{16}"
                                           oninput="this.value=this.value.replace(/\D/g,'')" required>
                                </div>
                                <small class="form-text text-muted">16 digit angka di Kartu Keluarga</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Nama Lengkap <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="fas fa-user"></i></span>
                                    </div>
                                    <input type="text" class="form-control" id="nama" name="nama"
                                           placeholder="Nama sesuai KTP" required>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Tanggal Lahir <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="tgl_lahir" name="tgl_lahir" required>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Jenis Kelamin <span class="text-danger">*</span></label>
                                <select class="form-control" id="jenis_kelamin" name="jenis_kelamin" required>
                                    <option value="">-- Pilih --</option>
                                    <option value="M">Laki-laki</option>
                                    <option value="F">Perempuan</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Agama <span class="text-danger">*</span></label>
                                <select class="form-control" id="agama" name="agama" required>
                                    <option value="">-- Pilih --</option>
                                    <option value="Islam">Islam</option>
                                    <option value="Kristen">Kristen</option>
                                    <option value="Katolik">Katolik</option>
                                    <option value="Hindu">Hindu</option>
                                    <option value="Buddha">Buddha</option>
                                    <option value="Konghucu">Konghucu</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-8">
                            <div class="form-group">
                                <label>ID Usulan <small class="text-muted">(opsional — UUID untuk referensi)</small></label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="fas fa-key"></i></span>
                                    </div>
                                    <input type="text" class="form-control" id="id_usulan" name="id_usulan"
                                           placeholder="Kosongkan untuk auto-generate UUID"
                                           pattern="[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}">
                                    <div class="input-group-append">
                                        <button type="button" class="btn btn-outline-secondary" id="btnGenUuid" title="Generate UUID">
                                            <i class="fas fa-random"></i>
                                        </button>
                                    </div>
                                </div>
                                <small class="form-text text-muted">Format: xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx</small>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <button type="submit" class="btn btn-primary" id="btnCek">
                                <i class="fas fa-search"></i> Validasi NIK
                            </button>
                            <button type="button" class="btn btn-secondary ml-2" id="btnReset">
                                <i class="fas fa-redo"></i> Reset
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Loading --}}
    <div class="col-md-12" id="loadingSection" style="display:none;">
        <div class="card">
            <div class="card-body text-center py-5">
                <i class="fas fa-spinner fa-spin fa-3x text-primary mb-3"></i>
                <h5>Menghubungi server BKN Dukcapil...</h5>
                <p class="text-muted">Mohon tunggu sebentar</p>
            </div>
        </div>
    </div>

    {{-- Result --}}
    <div class="col-md-12" id="resultSection" style="display:none;">
        <div class="card" id="resultCard">
            <div class="card-header d-flex align-items-center" id="resultCardHeader">
                <h3 class="card-title" id="resultTitle"></h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3 text-center mb-3">
                        <div id="validBadgeWrap">
                            <span id="validBadge" class="badge px-4 py-3" style="font-size:1.3rem; border-radius:10px;"></span>
                        </div>
                        <div class="text-muted small mt-2">Status Validasi Dukcapil</div>
                    </div>
                    <div class="col-md-9">
                        <table class="table table-sm table-borderless mb-0">
                            <tbody>
                                <tr>
                                    <td class="text-muted" width="160"><i class="fas fa-info-circle mr-1"></i> Notifikasi</td>
                                    <td><strong id="resNotification">-</strong></td>
                                </tr>
                                <tr>
                                    <td class="text-muted"><i class="fas fa-comment mr-1"></i> Pesan</td>
                                    <td id="resMessage">-</td>
                                </tr>
                                <tr>
                                    <td class="text-muted"><i class="fas fa-server mr-1"></i> Status API</td>
                                    <td id="resStatus">-</td>
                                </tr>
                                <tr>
                                    <td class="text-muted"><i class="fas fa-fingerprint mr-1"></i> NIK Dicek</td>
                                    <td><code id="resNik">-</code></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- Raw response (collapsible) --}}
                <div class="mt-3">
                    <button class="btn btn-sm btn-outline-secondary" type="button" data-toggle="collapse" data-target="#rawResponse">
                        <i class="fas fa-code"></i> Lihat Raw Response
                    </button>
                    <div class="collapse mt-2" id="rawResponse">
                        <pre class="bg-dark text-white p-3 rounded" style="font-size:.8rem; max-height:200px; overflow-y:auto;" id="rawJson"></pre>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Info card --}}
<div class="row">
    <div class="col-md-12">
        <div class="card card-outline card-info">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-info-circle"></i> Keterangan</h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i></button>
                </div>
            </div>
            <div class="card-body">
                <ul class="mb-0">
                    <li>Tool ini memvalidasi data NIK ke <strong>database Dukcapil</strong> via API BKN SIASN</li>
                    <li>Semua field (NIK, No KK, Nama, Tgl Lahir, Agama, Jenis Kelamin) harus sesuai dengan data di KTP/KK</li>
                    <li>Jika validasi gagal karena <strong>401 Unauthorized</strong>, perbarui token BKN SIASN di menu <a href="{{ route('admin.pengaturan.update-api-token.index') }}">Update API Token</a> (key: <code>bkn_siasn_token</code>)</li>
                    <li>ID Usulan bersifat opsional — jika dikosongkan, UUID akan di-generate otomatis</li>
                </ul>
            </div>
        </div>
    </div>
</div>
@stop

@section('css')
<style>
    .badge { display: inline-block; }
</style>
@stop

@section('js')
<script>
$(function () {

    // Generate UUID v4
    function uuidv4() {
        return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function(c) {
            var r = Math.random() * 16 | 0, v = c === 'x' ? r : (r & 0x3 | 0x8);
            return v.toString(16);
        });
    }

    $('#btnGenUuid').on('click', function () {
        $('#id_usulan').val(uuidv4());
    });

    $('#btnReset').on('click', function () {
        $('#formCekNik')[0].reset();
        $('#resultSection, #loadingSection').hide();
    });

    $('#formCekNik').on('submit', function (e) {
        e.preventDefault();

        var nik = $('#nik').val().trim();
        if (nik.length !== 16 || !/^\d{16}$/.test(nik)) {
            Swal.fire({ icon: 'error', title: 'NIK Tidak Valid', text: 'NIK harus tepat 16 digit angka.' });
            return;
        }
        var nokk = $('#nokk').val().trim();
        if (nokk.length !== 16 || !/^\d{16}$/.test(nokk)) {
            Swal.fire({ icon: 'error', title: 'No KK Tidak Valid', text: 'Nomor KK harus tepat 16 digit angka.' });
            return;
        }

        $('#resultSection').hide();
        $('#loadingSection').show();
        $('#btnCek').prop('disabled', true);

        $.ajax({
            url: '{{ route("admin.pengaturan.cek-nik.check") }}',
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                nik: nik,
                nokk: nokk,
                nama: $('#nama').val().trim(),
                tgl_lahir: $('#tgl_lahir').val(),
                agama: $('#agama').val(),
                jenis_kelamin: $('#jenis_kelamin').val(),
                id_usulan: $('#id_usulan').val().trim() || null,
            },
            success: function (res) {
                $('#loadingSection').hide();
                $('#btnCek').prop('disabled', false);

                if (!res.success) {
                    Swal.fire({ icon: 'error', title: 'Gagal', html: res.message });
                    return;
                }

                $('#resNik').text(nik);
                $('#resNotification').text(res.notification || '-');
                $('#resMessage').text(res.message || '-');
                $('#resStatus').text(res.status || '-');
                $('#rawJson').text(JSON.stringify(res.raw || res, null, 2));

                if (res.is_valid) {
                    $('#resultCard').removeClass('card-danger card-outline').addClass('card-success card-outline');
                    $('#resultCardHeader').removeClass('bg-danger').addClass('bg-transparent');
                    $('#resultTitle').html('<i class="fas fa-check-circle text-success mr-2"></i> NIK Valid');
                    $('#validBadge').removeClass('badge-danger').addClass('badge-success').text('✔ VALID');
                } else {
                    $('#resultCard').removeClass('card-success card-outline').addClass('card-danger card-outline');
                    $('#resultCardHeader').removeClass('bg-transparent').addClass('bg-transparent');
                    $('#resultTitle').html('<i class="fas fa-times-circle text-danger mr-2"></i> NIK Tidak Valid');
                    $('#validBadge').removeClass('badge-success').addClass('badge-danger').text('✖ TIDAK VALID');
                }

                $('#resultSection').show();
            },
            error: function (xhr) {
                $('#loadingSection').hide();
                $('#btnCek').prop('disabled', false);

                var msg = 'Terjadi kesalahan server.';
                if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
                    msg = Object.values(xhr.responseJSON.errors).flat().join('<br>');
                } else if (xhr.responseJSON && xhr.responseJSON.message) {
                    msg = xhr.responseJSON.message;
                }
                Swal.fire({ icon: 'error', title: 'Gagal', html: msg });
            }
        });
    });
});
</script>
@stop
