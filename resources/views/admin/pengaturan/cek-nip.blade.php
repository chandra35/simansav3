@extends('adminlte::page')

@section('title', 'Cek NIP - SIMANSA')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="m-0"><i class="fas fa-id-card"></i> Cek Data NIP</h1>
    </div>
@stop

@section('content')
<div class="row">
    <!-- Form Card -->
    <div class="col-md-12">
        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-search"></i> Cari Data NIP</h3>
            </div>
            <div class="card-body">
                <form id="formCekNip">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="nip">Nomor Induk Pegawai (NIP) <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="fas fa-id-badge"></i></span>
                                    </div>
                                    <input type="text" 
                                           class="form-control form-control-lg" 
                                           id="nip" 
                                           name="nip" 
                                           placeholder="Masukkan NIP (18 digit)"
                                           required
                                           minlength="18"
                                           maxlength="18"
                                           pattern="\d{18}">
                                </div>
                                <small class="form-text text-muted">
                                    <i class="fas fa-info-circle"></i> NIP harus berupa 18 digit angka
                                </small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>&nbsp;</label>
                                <div>
                                    <button type="submit" class="btn btn-primary btn-lg" id="btnCek">
                                        <i class="fas fa-search"></i> Cek NIP
                                    </button>
                                    <button type="button" class="btn btn-secondary btn-lg" id="btnReset">
                                        <i class="fas fa-redo"></i> Reset
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Loading Section -->
    <div class="col-md-12" id="loadingSection" style="display: none;">
        <div class="card">
            <div class="card-body text-center py-5">
                <i class="fas fa-spinner fa-spin fa-3x text-primary mb-3"></i>
                <h5>Sedang mengecek data NIP...</h5>
                <p class="text-muted">Mohon tunggu sebentar</p>
            </div>
        </div>
    </div>

    <!-- Result Section -->
    <div class="col-md-12" id="resultSection" style="display: none;">
        
        <!-- Data Pribadi -->
        <div class="card card-primary card-outline">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-user"></i> Data Pribadi</h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-sm table-borderless">
                            <tr>
                                <td width="40%" class="font-weight-bold">NIP Lama</td>
                                <td>: <span id="res_nip">-</span></td>
                            </tr>
                            <tr>
                                <td class="font-weight-bold">NIP Baru</td>
                                <td>: <span id="res_nip_baru" class="badge badge-primary"></span></td>
                            </tr>
                            <tr>
                                <td class="font-weight-bold">Nama</td>
                                <td>: <span id="res_nama" class="font-weight-bold text-primary"></span></td>
                            </tr>
                            <tr>
                                <td class="font-weight-bold">Nama Lengkap</td>
                                <td>: <span id="res_nama_lengkap">-</span></td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-sm table-borderless">
                            <tr>
                                <td width="40%" class="font-weight-bold">Tempat, Tgl Lahir</td>
                                <td>: <span id="res_ttl">-</span></td>
                            </tr>
                            <tr>
                                <td class="font-weight-bold">Jenis Kelamin</td>
                                <td>: <span id="res_jk">-</span></td>
                            </tr>
                            <tr>
                                <td class="font-weight-bold">Agama</td>
                                <td>: <span id="res_agama">-</span></td>
                            </tr>
                            <tr>
                                <td class="font-weight-bold">Pendidikan</td>
                                <td>: <span id="res_pendidikan">-</span></td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Data Kepegawaian -->
        <div class="card card-success card-outline">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-briefcase"></i> Data Kepegawaian</h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-sm table-borderless">
                            <tr>
                                <td width="40%" class="font-weight-bold">Status Pegawai</td>
                                <td>: <span id="res_status_pegawai" class="badge badge-success"></span></td>
                            </tr>
                            <tr>
                                <td class="font-weight-bold">Pangkat</td>
                                <td>: <span id="res_pangkat">-</span></td>
                            </tr>
                            <tr>
                                <td class="font-weight-bold">Golongan/Ruang</td>
                                <td>: <span id="res_golongan">-</span></td>
                            </tr>
                            <tr>
                                <td class="font-weight-bold">TMT CPNS</td>
                                <td>: <span id="res_tmt_cpns">-</span></td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-sm table-borderless">
                            <tr>
                                <td width="40%" class="font-weight-bold">TMT Pangkat</td>
                                <td>: <span id="res_tmt_pangkat">-</span></td>
                            </tr>
                            <tr>
                                <td class="font-weight-bold">Masa Kerja</td>
                                <td>: <span id="res_masa_kerja">-</span></td>
                            </tr>
                            <tr>
                                <td class="font-weight-bold">Gaji Pokok</td>
                                <td>: <span id="res_gaji" class="text-success font-weight-bold">-</span></td>
                            </tr>
                            <tr>
                                <td class="font-weight-bold">Status Kawin</td>
                                <td>: <span id="res_status_kawin">-</span></td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Data Jabatan -->
        <div class="card card-info card-outline">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-user-tie"></i> Data Jabatan</h3>
            </div>
            <div class="card-body">
                <table class="table table-sm table-borderless">
                    <tr>
                        <td width="20%" class="font-weight-bold">Jabatan</td>
                        <td>: <span id="res_jabatan">-</span></td>
                    </tr>
                    <tr>
                        <td class="font-weight-bold">Level Jabatan</td>
                        <td>: <span id="res_level_jabatan">-</span></td>
                    </tr>
                    <tr>
                        <td class="font-weight-bold">TMT Jabatan</td>
                        <td>: <span id="res_tmt_jabatan">-</span></td>
                    </tr>
                </table>
            </div>
        </div>

        <!-- Data Satuan Kerja -->
        <div class="card card-warning card-outline">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-building"></i> Data Satuan Kerja</h3>
            </div>
            <div class="card-body">
                <table class="table table-sm table-borderless">
                    <tr>
                        <td width="20%" class="font-weight-bold">Satker Utama</td>
                        <td>: <span id="res_satker_1">-</span></td>
                    </tr>
                    <tr>
                        <td class="font-weight-bold">Satker 2</td>
                        <td>: <span id="res_satker_2">-</span></td>
                    </tr>
                    <tr>
                        <td class="font-weight-bold">Satker 3</td>
                        <td>: <span id="res_satker_3">-</span></td>
                    </tr>
                    <tr>
                        <td class="font-weight-bold">Satker 4</td>
                        <td>: <span id="res_satker_4">-</span></td>
                    </tr>
                    <tr>
                        <td class="font-weight-bold">Grup Satuan Kerja</td>
                        <td>: <span id="res_grup_satker">-</span></td>
                    </tr>
                    <tr>
                        <td class="font-weight-bold">Keterangan</td>
                        <td>: <span id="res_ket_satker">-</span></td>
                    </tr>
                </table>
            </div>
        </div>

        <!-- Data Kontak & Alamat -->
        <div class="card card-secondary card-outline">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-map-marker-alt"></i> Data Kontak & Alamat</h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-sm table-borderless">
                            <tr>
                                <td width="40%" class="font-weight-bold">Alamat</td>
                                <td>: <span id="res_alamat">-</span></td>
                            </tr>
                            <tr>
                                <td class="font-weight-bold">Kelurahan/Desa</td>
                                <td>: <span id="res_alamat_2">-</span></td>
                            </tr>
                            <tr>
                                <td class="font-weight-bold">Kab/Kota</td>
                                <td>: <span id="res_kab_kota">-</span></td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-sm table-borderless">
                            <tr>
                                <td width="40%" class="font-weight-bold">Provinsi</td>
                                <td>: <span id="res_provinsi">-</span></td>
                            </tr>
                            <tr>
                                <td class="font-weight-bold">Kode Pos</td>
                                <td>: <span id="res_kode_pos">-</span></td>
                            </tr>
                            <tr>
                                <td class="font-weight-bold">Telepon/HP</td>
                                <td>: <span id="res_telepon">-</span></td>
                            </tr>
                            <tr>
                                <td class="font-weight-bold">Email</td>
                                <td>: <span id="res_email">-</span></td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Info Pensiun -->
        <div class="card card-danger card-outline">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-calendar-check"></i> Informasi Pensiun</h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-sm table-borderless">
                            <tr>
                                <td width="40%" class="font-weight-bold">Usia Pensiun</td>
                                <td>: <span id="res_usia_pensiun">-</span> Tahun</td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-sm table-borderless">
                            <tr>
                                <td width="40%" class="font-weight-bold">TMT Pensiun</td>
                                <td>: <span id="res_tmt_pensiun">-</span></td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
@stop

@section('css')
<style>
    .table-borderless td {
        padding: 0.5rem 0.5rem;
    }
    
    .card-header {
        background-color: rgba(0,0,0,0.03);
    }
    
    #formCekNip .form-control-lg {
        font-size: 1.1rem;
        font-weight: 500;
    }
    
    .text-primary {
        font-size: 1.05rem;
    }
</style>
@stop

@section('js')
<script>
$(document).ready(function() {
    
    // Handle form submit
    $('#formCekNip').on('submit', function(e) {
        e.preventDefault();
        
        const nip = $('#nip').val().trim();
        
        if (nip.length < 9) {
            Swal.fire('Perhatian!', 'NIP minimal 9 digit', 'warning');
            return;
        }
        
        // Show loading, hide result
        $('#loadingSection').slideDown();
        $('#resultSection').slideUp();
        $('#btnCek').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Mengecek...');
        
        // AJAX request
        $.ajax({
            url: '{{ route('admin.pengaturan.cek-nip.check') }}',
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                nip: nip
            },
            success: function(response) {
                if (response.success && response.data) {
                    displayResult(response.data);
                    $('#loadingSection').slideUp();
                    $('#resultSection').slideDown();
                } else {
                    Swal.fire('Error!', response.message || 'Terjadi kesalahan', 'error');
                    $('#loadingSection').slideUp();
                }
            },
            error: function(xhr) {
                let errorMessage = 'Terjadi kesalahan saat mengecek NIP';
                
                if (xhr.status === 404) {
                    errorMessage = 'NIP tidak ditemukan dalam database';
                } else if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                }
                
                Swal.fire('Error!', errorMessage, 'error');
                $('#loadingSection').slideUp();
            },
            complete: function() {
                $('#btnCek').prop('disabled', false).html('<i class="fas fa-search"></i> Cek NIP');
            }
        });
    });
    
    // Handle reset button
    $('#btnReset').on('click', function() {
        $('#formCekNip')[0].reset();
        $('#resultSection').slideUp();
        $('#loadingSection').slideUp();
        $('#nip').focus();
    });
    
    // Function to display result
    function displayResult(data) {
        // Data Pribadi
        $('#res_nip').text(data.NIP || '-');
        $('#res_nip_baru').text(data.NIP_BARU || '-');
        $('#res_nama').text(data.NAMA || '-');
        $('#res_nama_lengkap').text(data.NAMA_LENGKAP || '-');
        
        // TTL
        const tglLahir = data.TANGGAL_LAHIR ? formatTanggal(data.TANGGAL_LAHIR) : '-';
        $('#res_ttl').text((data.TEMPAT_LAHIR || '-') + ', ' + tglLahir);
        
        // Jenis Kelamin
        const jk = data.JENIS_KELAMIN == 1 ? 'Laki-laki' : data.JENIS_KELAMIN == 2 ? 'Perempuan' : '-';
        $('#res_jk').html(jk == 'Laki-laki' ? '<span class="badge badge-primary">Laki-laki</span>' : '<span class="badge badge-danger">Perempuan</span>');
        
        $('#res_agama').text(data.AGAMA || '-');
        $('#res_pendidikan').text(data.PENDIDIKAN || '-');
        
        // Data Kepegawaian
        const statusBadge = getStatusBadge(data.STATUS_PEGAWAI);
        $('#res_status_pegawai').removeClass().addClass('badge').addClass(statusBadge.class).text(data.STATUS_PEGAWAI || '-');
        $('#res_pangkat').text(data.PANGKAT || '-');
        $('#res_golongan').text(data.GOL_RUANG || '-');
        $('#res_tmt_cpns').text(formatTanggalSimple(data.TMT_CPNS) || '-');
        $('#res_tmt_pangkat').text(formatTanggalSimple(data.TMT_PANGKAT) || '-');
        
        // Masa Kerja
        const masaKerja = (data.MK_TAHUN || 0) + ' Tahun ' + (data.MK_BULAN || 0) + ' Bulan';
        $('#res_masa_kerja').text(masaKerja);
        
        // Gaji
        const gaji = data.Gaji_Pokok ? formatRupiah(data.Gaji_Pokok) : '-';
        $('#res_gaji').text(gaji);
        
        $('#res_status_kawin').text(data.STATUS_KAWIN || '-');
        
        // Data Jabatan
        $('#res_jabatan').text(data.TAMPIL_JABATAN || '-');
        $('#res_level_jabatan').text(data.LEVEL_JABATAN || '-');
        $('#res_tmt_jabatan').text(formatTanggal(data.TMT_JABATAN) || '-');
        
        // Data Satuan Kerja
        $('#res_satker_1').text(data.SATKER_1 || '-');
        $('#res_satker_2').text(data.SATKER_2 || '-');
        $('#res_satker_3').text(data.SATKER_3 || '-');
        $('#res_satker_4').text(data.SATKER_4 || '-');
        $('#res_grup_satker').text(data.GRUP_SATUAN_KERJA || '-');
        $('#res_ket_satker').text(data.KETERANGAN_SATUAN_KERJA || '-');
        
        // Data Kontak & Alamat
        $('#res_alamat').text(data.ALAMAT_1 || '-');
        $('#res_alamat_2').text(data.ALAMAT_2 || '-');
        $('#res_kab_kota').text(data.KAB_KOTA || '-');
        $('#res_provinsi').text(data.PROVINSI || '-');
        $('#res_kode_pos').text(data.KODE_POS || '-');
        $('#res_telepon').text((data.TELEPON || data.NO_HP) || '-');
        $('#res_email').text(data.EMAIL || '-');
        
        // Info Pensiun
        $('#res_usia_pensiun').text(data.USIA_PENSIUN || '-');
        $('#res_tmt_pensiun').text(formatTanggal(data.TMT_PENSIUN) || '-');
    }
    
    // Helper functions
    function formatTanggal(tanggal) {
        if (!tanggal) return '-';
        const date = new Date(tanggal);
        const options = { year: 'numeric', month: 'long', day: 'numeric' };
        return date.toLocaleDateString('id-ID', options);
    }
    
    function formatTanggalSimple(tanggal) {
        if (!tanggal) return '-';
        // Format: DD-MM-YYYY
        const parts = tanggal.split('-');
        if (parts.length === 3) {
            return tanggal; // Already in correct format
        }
        return tanggal;
    }
    
    function formatRupiah(angka) {
        return 'Rp ' + new Intl.NumberFormat('id-ID').format(angka);
    }
    
    function getStatusBadge(status) {
        const badges = {
            'PNS': { class: 'badge-success' },
            'CPNS': { class: 'badge-info' },
            'PPPK': { class: 'badge-warning' }
        };
        return badges[status] || { class: 'badge-secondary' };
    }
    
});
</script>
@stop

@section('plugins.Sweetalert2', true)
