@extends('adminlte::page')

@section('title', 'Cek NISN - SIMANSA')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="m-0"><i class="fas fa-id-card"></i> Cek Data NISN Siswa</h1>
    </div>
@stop

@section('content')
<div class="row">
    <!-- Form Card -->
    <div class="col-md-12">
        <div class="card card-success">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-search"></i> Cari Data NISN</h3>
            </div>
            <div class="card-body">
                <form id="formCekNisn">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="nisn">Nomor Induk Siswa Nasional (NISN) <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="fas fa-id-card"></i></span>
                                    </div>
                                    <input type="text" 
                                           class="form-control form-control-lg" 
                                           id="nisn" 
                                           name="nisn" 
                                           placeholder="Masukkan NISN (10 digit)"
                                           required
                                           minlength="10"
                                           maxlength="10"
                                           pattern="\d{10}">
                                </div>
                                <small class="form-text text-muted">
                                    <i class="fas fa-info-circle"></i> NISN harus berupa 10 digit angka
                                </small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>&nbsp;</label>
                                <div>
                                    <button type="submit" class="btn btn-success btn-lg" id="btnCek">
                                        <i class="fas fa-search"></i> Cek NISN
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
                <i class="fas fa-spinner fa-spin fa-3x text-success mb-3"></i>
                <h5>Sedang mengecek data NISN...</h5>
                <p class="text-muted">Mohon tunggu sebentar</p>
            </div>
        </div>
    </div>

    <!-- Result Section -->
    <div class="col-md-12" id="resultSection" style="display: none;">
        
        <!-- Data Results (will be populated by JavaScript) -->
        <div class="card card-success card-outline">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-check-circle"></i> Data NISN Ditemukan</h3>
            </div>
            <div class="card-body" id="resultContent">
                <!-- Content will be injected here -->
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="card">
            <div class="card-body text-center">
                <button type="button" class="btn btn-primary" id="btnPrint">
                    <i class="fas fa-print"></i> Cetak Data
                </button>
                <button type="button" class="btn btn-secondary" id="btnNewSearch">
                    <i class="fas fa-search"></i> Cari NISN Lain
                </button>
            </div>
        </div>

    </div>

    <!-- Error Section -->
    <div class="col-md-12" id="errorSection" style="display: none;">
        <div class="card border-danger">
            <div class="card-header bg-danger">
                <h3 class="card-title"><i class="fas fa-exclamation-triangle"></i> Error</h3>
            </div>
            <div class="card-body">
                <div class="alert alert-danger mb-0">
                    <p class="mb-0" id="errorMessage"></p>
                </div>
            </div>
        </div>
    </div>

</div>
@stop

@section('js')
<script>
$(document).ready(function() {
    
    // Check if NISN in URL parameter and auto-search
    const urlParams = new URLSearchParams(window.location.search);
    const nisnFromUrl = urlParams.get('nisn');
    
    if (nisnFromUrl) {
        $('#nisn').val(nisnFromUrl);
        // Auto submit after a short delay
        setTimeout(function() {
            $('#formCekNisn').submit();
        }, 500);
    }
    
    // Submit form
    $('#formCekNisn').submit(function(e) {
        e.preventDefault();
        
        const nisn = $('#nisn').val().trim();
        
        // Validate
        if (nisn.length !== 10 || !/^\d+$/.test(nisn)) {
            Swal.fire({
                icon: 'error',
                title: 'Format NISN Salah',
                text: 'NISN harus berupa 10 digit angka',
                confirmButtonColor: '#28a745'
            });
            return;
        }
        
        // Show loading
        $('#resultSection, #errorSection').hide();
        $('#loadingSection').show();
        $('#btnCek').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Mengecek...');
        
        // AJAX request
        $.ajax({
            url: '{{ route("admin.pengaturan.cek-nisn.check") }}',
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                nisn: nisn
            },
            success: function(response) {
                $('#loadingSection').hide();
                $('#btnCek').prop('disabled', false).html('<i class="fas fa-search"></i> Cek NISN');
                
                if (response.success && response.data) {
                    displayResult(response.data);
                    $('#resultSection').show();
                    
                    // Success notification
                    Swal.fire({
                        icon: 'success',
                        title: 'Data Ditemukan!',
                        text: 'Data NISN berhasil ditemukan di database EMIS',
                        timer: 2000,
                        showConfirmButton: false
                    });
                } else {
                    showError(response.message || 'NISN tidak ditemukan dalam database EMIS');
                }
            },
            error: function(xhr) {
                $('#loadingSection').hide();
                $('#btnCek').prop('disabled', false).html('<i class="fas fa-search"></i> Cek NISN');
                
                let errorMsg = 'Terjadi kesalahan saat mengecek NISN';
                
                if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
                    errorMsg = Object.values(xhr.responseJSON.errors)[0][0];
                } else if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMsg = xhr.responseJSON.message;
                }
                
                showError(errorMsg);
            }
        });
    });
    
    // Reset button
    $('#btnReset').click(function() {
        $('#formCekNisn')[0].reset();
        $('#resultSection, #errorSection, #loadingSection').hide();
        $('#nisn').focus();
    });
    
    // New search button
    $('#btnNewSearch').click(function() {
        $('#btnReset').click();
    });
    
    // Print button
    $('#btnPrint').click(function() {
        window.print();
    });
    
    // Function to display result
    function displayResult(data) {
        // Clear previous results
        $('#resultContent').empty();
        
        let html = '';
        
        // === DATA KEMDIKBUD (PUSDATIN) ===
        if (data.kemdikbud) {
            const dk = data.kemdikbud;
            html += `
                <div class="mb-4">
                    <h5 class="text-primary mb-3"><i class="fas fa-database"></i> Data dari Kemdikbud (Pusdatin)</h5>
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-sm table-borderless">
                                <tr>
                                    <td width="40%" class="font-weight-bold">NISN</td>
                                    <td>: <span class="badge badge-primary">${dk.nisn || '-'}</span></td>
                                </tr>
                                <tr>
                                    <td class="font-weight-bold">NIK</td>
                                    <td>: ${dk.nik || '-'}</td>
                                </tr>
                                <tr>
                                    <td class="font-weight-bold">Nama Lengkap</td>
                                    <td>: <strong class="text-primary">${dk.nama || '-'}</strong></td>
                                </tr>
                                <tr>
                                    <td class="font-weight-bold">Tempat, Tanggal Lahir</td>
                                    <td>: ${dk.tempat_lahir || '-'}, ${formatTanggal(dk.tanggal_lahir)}</td>
                                </tr>
                                <tr>
                                    <td class="font-weight-bold">Jenis Kelamin</td>
                                    <td>: ${getJenisKelaminBadge(dk.jenis_kelamin)}</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-sm table-borderless">
                                <tr>
                                    <td width="40%" class="font-weight-bold">Nama Ibu Kandung</td>
                                    <td>: ${dk.nama_ibu_kandung || '-'}</td>
                                </tr>
                                <tr>
                                    <td class="font-weight-bold">Status</td>
                                    <td>: ${getStatusAktifBadge(dk.aktif)}</td>
                                </tr>
                                <tr>
                                    <td class="font-weight-bold">Tingkat Pendidikan</td>
                                    <td>: ${dk.tingkat_pendidikan || '-'}</td>
                                </tr>
                                <tr>
                                    <td class="font-weight-bold">Sekolah</td>
                                    <td>: <strong class="text-info">${dk.sekolah || '-'}</strong></td>
                                </tr>
                                <tr>
                                    <td class="font-weight-bold">NPSN</td>
                                    <td>: <span class="badge badge-info">${dk.npsn || '-'}</span></td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            `;
        }
        
        // === DATA KEMENAG (EMIS) ===
        if (data.kemenag) {
            const km = data.kemenag;
            html += `
                <hr class="my-4">
                <div class="mb-4">
                    <h5 class="text-success mb-3"><i class="fas fa-mosque"></i> Data dari Kemenag (EMIS)</h5>
                    
                    <!-- Identitas Pribadi -->
                    <h6 class="text-muted mb-2"><i class="fas fa-user-circle"></i> Identitas Pribadi</h6>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <table class="table table-sm table-borderless">
                                <tr>
                                    <td width="40%" class="font-weight-bold">NISN</td>
                                    <td>: <span class="badge badge-success">${km.nisn || '-'}</span></td>
                                </tr>
                                <tr>
                                    <td class="font-weight-bold">NIK</td>
                                    <td>: ${km.nik || '-'}</td>
                                </tr>
                                <tr>
                                    <td class="font-weight-bold">Nama Lengkap</td>
                                    <td>: <strong class="text-success">${km.full_name || '-'}</strong></td>
                                </tr>
                                <tr>
                                    <td class="font-weight-bold">Tempat Lahir</td>
                                    <td>: ${km.birth_place || '-'}</td>
                                </tr>
                                <tr>
                                    <td class="font-weight-bold">Tanggal Lahir</td>
                                    <td>: ${formatTanggal(km.birth_date)}</td>
                                </tr>
                                <tr>
                                    <td class="font-weight-bold">Jenis Kelamin</td>
                                    <td>: ${km.gender_name ? '<span class="badge ' + (km.gender_id == 1 ? 'badge-primary' : 'badge-danger') + '">' + km.gender_name + '</span>' : '-'}</td>
                                </tr>
                                <tr>
                                    <td class="font-weight-bold">Agama</td>
                                    <td>: ${km.religion_name || '-'}</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-sm table-borderless">
                                <tr>
                                    <td width="40%" class="font-weight-bold">Anak ke-</td>
                                    <td>: ${km.birth_order || '-'} dari ${km.siblings || '-'} bersaudara</td>
                                </tr>
                                <tr>
                                    <td class="font-weight-bold">Alamat</td>
                                    <td>: ${km.address || '-'}</td>
                                </tr>
                                <tr>
                                    <td class="font-weight-bold">Kelurahan/Desa</td>
                                    <td>: ${km.subdistrict_name || '-'}</td>
                                </tr>
                                <tr>
                                    <td class="font-weight-bold">Kecamatan</td>
                                    <td>: ${km.district_name || '-'}</td>
                                </tr>
                                <tr>
                                    <td class="font-weight-bold">Kota/Kabupaten</td>
                                    <td>: ${km.city_name || '-'}</td>
                                </tr>
                                <tr>
                                    <td class="font-weight-bold">Provinsi</td>
                                    <td>: ${km.province_name || '-'}</td>
                                </tr>
                                <tr>
                                    <td class="font-weight-bold">Kode Pos</td>
                                    <td>: ${km.postal_code || '-'}</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                    
                    <!-- Pendidikan -->
                    <h6 class="text-muted mb-2"><i class="fas fa-school"></i> Informasi Pendidikan</h6>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <table class="table table-sm table-borderless">
                                <tr>
                                    <td width="40%" class="font-weight-bold">Status Siswa</td>
                                    <td>: ${getStatusSiswaBadge(km.status_name)}</td>
                                </tr>
                                <tr>
                                    <td class="font-weight-bold">Keterangan Status</td>
                                    <td>: ${km.status_description_name || '-'}</td>
                                </tr>
                                <tr>
                                    <td class="font-weight-bold">Tingkat/Kelas</td>
                                    <td>: <span class="badge badge-primary">${km.level_name || '-'}</span></td>
                                </tr>
                                <tr>
                                    <td class="font-weight-bold">Tahun Pelajaran</td>
                                    <td>: ${km.academic_year || '-'}</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-sm table-borderless">
                                <tr>
                                    <td width="40%" class="font-weight-bold">Sekolah/Madrasah</td>
                                    <td>: <strong class="text-info">${km.institution_name || '-'}</strong></td>
                                </tr>
                                <tr>
                                    <td class="font-weight-bold">NSM</td>
                                    <td>: <span class="badge badge-info">${km.institution_nsm || '-'}</span></td>
                                </tr>
                                <tr>
                                    <td class="font-weight-bold">ID Lembaga</td>
                                    <td>: <span class="text-muted small">${km.institution_id || '-'}</span></td>
                                </tr>
                                <tr>
                                    <td class="font-weight-bold">Cita-cita</td>
                                    <td>: ${km.life_goal_name || '-'}</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                    
                    <!-- Transportasi & Tempat Tinggal -->
                    <h6 class="text-muted mb-2"><i class="fas fa-home"></i> Tempat Tinggal & Transportasi</h6>
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-sm table-borderless">
                                <tr>
                                    <td width="40%" class="font-weight-bold">Jarak Tempat Tinggal</td>
                                    <td>: ${km.residence_distance_name || '-'}</td>
                                </tr>
                                <tr>
                                    <td class="font-weight-bold">Status Tempat Tinggal</td>
                                    <td>: ${km.residence_status_name || '-'}</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-sm table-borderless">
                                <tr>
                                    <td width="40%" class="font-weight-bold">Alat Transportasi</td>
                                    <td>: ${km.transportation_name || '-'}</td>
                                </tr>
                                <tr>
                                    <td class="font-weight-bold">ID Integrasi</td>
                                    <td>: <span class="text-muted small">${km.integration_id || '-'}</span></td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            `;
        }
        
        // No data found
        if (!data.kemdikbud && !data.kemenag) {
            html = `<div class="alert alert-warning"><i class="fas fa-exclamation-triangle"></i> Data tidak ditemukan dari kedua sumber (Kemdikbud & Kemenag)</div>`;
        }
        
        // Insert HTML to specific element
        $('#resultContent').html(html);
    }
    
    // Helper: Jenis Kelamin Badge
    function getJenisKelaminBadge(jk) {
        if (jk === 'L') return '<span class="badge badge-primary">Laki-laki</span>';
        if (jk === 'P') return '<span class="badge badge-danger">Perempuan</span>';
        return '-';
    }
    
    // Helper: Status Aktif Badge (Kemdikbud)
    function getStatusAktifBadge(aktif) {
        if (aktif === '1') return '<span class="badge badge-success">Aktif</span>';
        return '<span class="badge badge-secondary">Tidak Aktif</span>';
    }
    
    // Helper: Status Siswa Badge (Kemenag EMIS)
    function getStatusSiswaBadge(status) {
        const statusMap = {
            'active': '<span class="badge badge-success">Aktif</span>',
            'active_without_rombel': '<span class="badge badge-info">Aktif Tanpa Rombel</span>',
            'inactive': '<span class="badge badge-secondary">Tidak Aktif</span>',
            'graduated': '<span class="badge badge-primary">Lulus</span>',
            'moved': '<span class="badge badge-warning">Pindah</span>',
            'dropped_out': '<span class="badge badge-danger">Drop Out</span>',
        };
        return statusMap[status] || '<span class="badge badge-secondary">' + (status || '-') + '</span>';
    }
    
    // Function to show error
    function showError(message) {
        $('#errorMessage').text(message);
        $('#errorSection').show();
        
        Swal.fire({
            icon: 'error',
            title: 'Data Tidak Ditemukan',
            text: message,
            confirmButtonColor: '#28a745'
        });
    }
    
    // Helper: Format tanggal
    function formatTanggal(tanggal) {
        if (!tanggal) return '-';
        const date = new Date(tanggal);
        const options = { year: 'numeric', month: 'long', day: 'numeric' };
        return date.toLocaleDateString('id-ID', options);
    }
    
    // Helper: Get tingkat pendidikan
    function getTingkatPendidikan(tingkat) {
        const tingkatMap = {
            '0': 'PAUD',
            '1': 'TK',
            '2': 'SD Kelas 1',
            '3': 'SD Kelas 2',
            '4': 'SD Kelas 3',
            '5': 'SD Kelas 4',
            '6': 'SD Kelas 5',
            '7': 'SD Kelas 6',
            '8': 'SMP Kelas 7',
            '9': 'SMP Kelas 8',
            '10': 'SMP Kelas 9',
            '11': 'SMA Kelas 10',
            '12': 'SMA Kelas 11',
            '13': 'SMA Kelas 12'
        };
        return tingkatMap[tingkat] || tingkat || '-';
    }
    
    // Helper: Get bentuk pendidikan
    function getBentukPendidikan(id) {
        const bentukMap = {
            '5': 'SD',
            '6': 'SMP',
            '13': 'SMK',
            '15': 'SMA',
            '16': 'MA (Madrasah Aliyah)',
            '17': 'MTs (Madrasah Tsanawiyah)',
            '18': 'MI (Madrasah Ibtidaiyah)'
        };
        return bentukMap[id] || 'ID: ' + (id || '-');
    }
    
});
</script>
@stop

@section('plugins.Sweetalert2', true)

@section('css')
<style>
    @media print {
        .content-header,
        .card-header,
        #btnPrint,
        #btnNewSearch,
        .main-sidebar,
        .main-header {
            display: none !important;
        }
        
        .card {
            border: 1px solid #dee2e6 !important;
            box-shadow: none !important;
        }
    }
    
    .table-borderless td {
        padding: 0.5rem 0;
    }
</style>
@stop
