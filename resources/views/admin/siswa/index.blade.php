@extends('adminlte::page')

@section('title', 'Data Siswa - SIMANSA')

@section('content_header')
    <h1>Data Siswa</h1>
@stop

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-users mr-1"></i>
                    Manajemen Data Siswa
                </h3>
                <div class="card-tools">
                    <a href="{{ route('admin.siswa.import') }}" class="btn btn-success mr-2">
                        <i class="fas fa-file-excel"></i> Import dari Excel
                    </a>
                    <button type="button" class="btn btn-primary" onclick="addSiswa()">
                        <i class="fas fa-plus"></i> Tambah Siswa
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="siswa-table" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>NISN</th>
                                <th>Nama Lengkap</th>
                                <th>Jenis Kelamin</th>
                                <th>Username</th>
                                <th>Status Ortu</th>
                                <th>Status Diri</th>
                                <th>Tgl Dibuat</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Add/Edit Siswa -->
<div class="modal fade" id="siswaModal" tabindex="-1" role="dialog" aria-labelledby="siswaModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="siswaModalLabel">Tambah Siswa</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="siswaForm">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label for="nisn">NISN <span class="text-danger">*</span></label>
                        <input type="text" name="nisn" id="nisn" class="form-control" required>
                        <small class="text-muted">NISN akan digunakan sebagai username dan password default</small>
                        <div class="invalid-feedback"></div>
                    </div>

                    <div class="form-group">
                        <label for="nama_lengkap">Nama Lengkap <span class="text-danger">*</span></label>
                        <input type="text" name="nama_lengkap" id="nama_lengkap" class="form-control" required>
                        <div class="invalid-feedback"></div>
                    </div>

                    <div class="form-group">
                        <label for="jenis_kelamin">Jenis Kelamin <span class="text-danger">*</span></label>
                        <select name="jenis_kelamin" id="jenis_kelamin" class="form-control" required>
                            <option value="">Pilih Jenis Kelamin</option>
                            <option value="L">Laki-laki</option>
                            <option value="P">Perempuan</option>
                        </select>
                        <div class="invalid-feedback"></div>
                    </div>

                    <div class="alert alert-info">
                        <h6><i class="icon fas fa-info"></i> Informasi</h6>
                        <ul class="mb-0">
                            <li>Username siswa: NISN</li>
                            <li>Password default: NISN</li>
                            <li>Email: NISN@student.man1metro.sch.id</li>
                            <li>Siswa akan diminta mengganti password saat login pertama</li>
                        </ul>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal View Siswa -->
<div class="modal fade" id="viewSiswaModal" tabindex="-1" role="dialog" aria-labelledby="viewSiswaModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary">
                <h5 class="modal-title text-white" id="viewSiswaModalLabel">
                    <i class="fas fa-user-graduate"></i> Detail Siswa
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <!-- Nav tabs -->
                <ul class="nav nav-tabs" id="siswaDetailTabs" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" id="data-siswa-tab" data-toggle="tab" href="#data-siswa" role="tab">
                            <i class="fas fa-user"></i> Data Siswa
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="data-diri-tab" data-toggle="tab" href="#data-diri" role="tab">
                            <i class="fas fa-id-card"></i> Data Diri
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="data-ortu-tab" data-toggle="tab" href="#data-ortu" role="tab">
                            <i class="fas fa-users"></i> Data Orang Tua
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="dokumen-tab" data-toggle="tab" href="#dokumen" role="tab">
                            <i class="fas fa-file-alt"></i> Dokumen
                        </a>
                    </li>
                </ul>

                <!-- Tab panes -->
                <div class="tab-content mt-3" id="siswaDetailTabContent">
                    <div class="tab-pane fade show active" id="data-siswa" role="tabpanel">
                        <!-- Content will be loaded here -->
                    </div>
                    <div class="tab-pane fade" id="data-diri" role="tabpanel">
                        <!-- Content will be loaded here -->
                    </div>
                    <div class="tab-pane fade" id="data-ortu" role="tabpanel">
                        <!-- Content will be loaded here -->
                    </div>
                    <div class="tab-pane fade" id="dokumen" role="tabpanel">
                        <!-- Content will be loaded here -->
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    <i class="fas fa-times"></i> Tutup
                </button>
            </div>
        </div>
    </div>
</div>
@stop

@section('css')
    <link rel="stylesheet" href="//cdn.datatables.net/1.10.25/css/dataTables.bootstrap4.min.css">
    <!-- Toastr CSS for toast notifications -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/2.1.4/toastr.min.css">
    <!-- SweetAlert2 CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <style>
        .status-badge {
            font-size: 0.8em;
        }
        .modal-xl {
            max-width: 1200px;
        }
        /* Custom toastr positioning */
        .toast-top-right {
            top: 80px;
            right: 20px;
        }
        .nav-tabs .nav-link {
            color: #495057;
        }
        .nav-tabs .nav-link.active {
            color: #007bff;
            font-weight: 600;
        }
        .table-detail td {
            padding: 0.5rem;
        }
        .dokumen-item {
            border: 1px solid #dee2e6;
            border-radius: 5px;
            padding: 15px;
            margin-bottom: 10px;
            transition: all 0.3s;
        }
        .dokumen-item:hover {
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
    </style>
@stop

@section('js')
<script src="//cdn.datatables.net/1.10.25/js/jquery.dataTables.min.js"></script>
<script src="//cdn.datatables.net/1.10.25/js/dataTables.bootstrap4.min.js"></script>
<!-- Toastr JS for toast notifications -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/2.1.4/toastr.min.js"></script>
<!-- SweetAlert2 JS -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
let siswaTable;
let editingId = null;

$(document).ready(function() {
    // Initialize DataTable
    siswaTable = $('#siswa-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route('admin.siswa.data') }}',
            type: 'GET'
        },
        columns: [
            { data: 'nisn', name: 'nisn' },
            { data: 'nama_lengkap', name: 'nama_lengkap' },
            { data: 'jenis_kelamin', name: 'jenis_kelamin' },
            { data: 'username', name: 'username' },
            { data: 'status_ortu', name: 'status_ortu', orderable: false, searchable: false },
            { data: 'status_diri', name: 'status_diri', orderable: false, searchable: false },
            { data: 'created_at', name: 'created_at' },
            { data: 'actions', name: 'actions', orderable: false, searchable: false }
        ],
        order: [[6, 'desc']],
        language: {
            processing: "Memproses...",
            search: "Cari:",
            lengthMenu: "Tampilkan _MENU_ data per halaman",
            info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
            infoEmpty: "Menampilkan 0 sampai 0 dari 0 data",
            infoFiltered: "(disaring dari _MAX_ total data)",
            loadingRecords: "Memuat...",
            zeroRecords: "Tidak ada data yang ditemukan",
            emptyTable: "Tidak ada data tersedia",
            paginate: {
                first: "Pertama",
                previous: "Sebelumnya",
                next: "Selanjutnya",
                last: "Terakhir"
            }
        }
    });

    // Configure toastr
    toastr.options = {
        "closeButton": true,
        "debug": false,
        "newestOnTop": true,
        "progressBar": true,
        "positionClass": "toast-top-right",
        "preventDuplicates": false,
        "onclick": null,
        "showDuration": "300",
        "hideDuration": "1000",
        "timeOut": "5000",
        "extendedTimeOut": "1000",
        "showEasing": "swing",
        "hideEasing": "linear",
        "showMethod": "fadeIn",
        "hideMethod": "fadeOut"
    };

    // Form submit handler
    $('#siswaForm').on('submit', function(e) {
        e.preventDefault();
        saveSiswa();
    });

    // Clear form when modal is closed
    $('#siswaModal').on('hidden.bs.modal', function() {
        clearForm();
    });
});

// Functions outside document.ready
function addSiswa() {
    editingId = null;
    $('#siswaModalLabel').text('Tambah Siswa');
    $('#siswaModal').modal('show');
}

function editSiswa(id) {
    editingId = id;
    $('#siswaModalLabel').text('Edit Siswa');
    
    // Load siswa data
    $.get(`{{ url('admin/siswa') }}/${id}`)
        .done(function(response) {
            if (response.success) {
                const siswa = response.data;
                $('#nisn').val(siswa.nisn);
                $('#nama_lengkap').val(siswa.nama_lengkap);
                $('#jenis_kelamin').val(siswa.jenis_kelamin);
                $('#siswaModal').modal('show');
            }
        })
        .fail(function() {
            toastr.error('Gagal memuat data siswa', 'Error!');
        });
}

function showSiswa(id) {
    $.get(`{{ url('admin/siswa') }}/${id}`)
        .done(function(response) {
            if (response.success) {
                const siswa = response.data;
                loadSiswaDataTab(siswa);
                loadDataDiriTab(siswa);
                loadDataOrtuTab(siswa);
                loadDokumenTab(siswa.id);
                $('#viewSiswaModal').modal('show');
            }
        })
        .fail(function() {
            toastr.error('Gagal memuat data siswa', 'Error!');
        });
}

function loadSiswaDataTab(siswa) {
    const createdAt = new Date(siswa.created_at).toLocaleString('id-ID');
    const updatedAt = new Date(siswa.updated_at).toLocaleString('id-ID');
    
    const html = `
        <div class="row">
            <div class="col-md-6">
                <h6 class="text-primary"><i class="fas fa-user"></i> Informasi Akun</h6>
                <table class="table table-detail table-sm table-bordered">
                    <tr><td width="40%" class="bg-light"><strong>NISN</strong></td><td>${siswa.nisn || '-'}</td></tr>
                    <tr><td class="bg-light"><strong>Nama Lengkap</strong></td><td>${siswa.nama_lengkap || '-'}</td></tr>
                    <tr><td class="bg-light"><strong>Jenis Kelamin</strong></td><td>${siswa.jenis_kelamin == 'L' ? '<span class="badge badge-primary">Laki-laki</span>' : '<span class="badge badge-danger">Perempuan</span>'}</td></tr>
                    <tr><td class="bg-light"><strong>Username</strong></td><td>${siswa.user.username || '-'}</td></tr>
                    <tr><td class="bg-light"><strong>Email</strong></td><td>${siswa.user.email || '-'}</td></tr>
                </table>
            </div>
            <div class="col-md-6">
                <h6 class="text-primary"><i class="fas fa-check-circle"></i> Status Kelengkapan</h6>
                <table class="table table-detail table-sm table-bordered">
                    <tr><td width="40%" class="bg-light"><strong>Data Ortu</strong></td><td>${siswa.data_ortu_completed ? '<span class="badge badge-success"><i class="fas fa-check"></i> Lengkap</span>' : '<span class="badge badge-warning"><i class="fas fa-exclamation-triangle"></i> Belum Lengkap</span>'}</td></tr>
                    <tr><td class="bg-light"><strong>Data Diri</strong></td><td>${siswa.data_diri_completed ? '<span class="badge badge-success"><i class="fas fa-check"></i> Lengkap</span>' : '<span class="badge badge-warning"><i class="fas fa-exclamation-triangle"></i> Belum Lengkap</span>'}</td></tr>
                    <tr><td class="bg-light"><strong>Status Login</strong></td><td>${siswa.user.is_first_login ? '<span class="badge badge-warning"><i class="fas fa-clock"></i> Belum Pernah Login</span>' : '<span class="badge badge-success"><i class="fas fa-check"></i> Sudah Login</span>'}</td></tr>
                </table>
                
                <h6 class="text-primary mt-3"><i class="fas fa-history"></i> History</h6>
                <table class="table table-detail table-sm table-bordered">
                    <tr><td width="40%" class="bg-light"><strong>Dibuat Oleh</strong></td><td>${siswa.created_by_name || 'System'}</td></tr>
                    <tr><td class="bg-light"><strong>Tanggal Dibuat</strong></td><td>${createdAt}</td></tr>
                    <tr><td class="bg-light"><strong>Diupdate Oleh</strong></td><td>${siswa.updated_by_name || '-'}</td></tr>
                    <tr><td class="bg-light"><strong>Tanggal Update</strong></td><td>${updatedAt}</td></tr>
                </table>
            </div>
        </div>
    `;
    $('#data-siswa').html(html);
}

function loadDataDiriTab(siswa) {
    const tglLahir = siswa.tanggal_lahir ? new Date(siswa.tanggal_lahir).toLocaleDateString('id-ID') : '-';
    
    const html = `
        <div class="row">
            <div class="col-md-6">
                <h6 class="text-primary"><i class="fas fa-id-card"></i> Data Pribadi</h6>
                <table class="table table-detail table-sm table-bordered">
                    <tr><td width="40%" class="bg-light"><strong>NIK</strong></td><td>${siswa.nik || '<span class="text-muted">Belum diisi</span>'}</td></tr>
                    <tr><td class="bg-light"><strong>Tempat Lahir</strong></td><td>${siswa.tempat_lahir || '<span class="text-muted">Belum diisi</span>'}</td></tr>
                    <tr><td class="bg-light"><strong>Tanggal Lahir</strong></td><td>${tglLahir}</td></tr>
                    <tr><td class="bg-light"><strong>Jumlah Saudara</strong></td><td>${siswa.jumlah_saudara || '<span class="text-muted">Belum diisi</span>'}</td></tr>
                    <tr><td class="bg-light"><strong>Anak Ke</strong></td><td>${siswa.anak_ke || '<span class="text-muted">Belum diisi</span>'}</td></tr>
                    <tr><td class="bg-light"><strong>Hobi</strong></td><td>${siswa.hobi || '<span class="text-muted">Belum diisi</span>'}</td></tr>
                    <tr><td class="bg-light"><strong>Cita-cita</strong></td><td>${siswa.cita_cita || '<span class="text-muted">Belum diisi</span>'}</td></tr>
                </table>
            </div>
            <div class="col-md-6">
                <h6 class="text-primary"><i class="fas fa-map-marker-alt"></i> Alamat Siswa</h6>
                ${siswa.alamat_siswa ? `
                <table class="table table-detail table-sm table-bordered">
                    <tr><td width="40%" class="bg-light"><strong>Alamat</strong></td><td>${siswa.alamat_siswa}</td></tr>
                    <tr><td class="bg-light"><strong>RT / RW</strong></td><td>${siswa.rt_siswa || '-'} / ${siswa.rw_siswa || '-'}</td></tr>
                    <tr><td class="bg-light"><strong>Kodepos</strong></td><td>${siswa.kodepos_siswa || '-'}</td></tr>
                </table>
                ` : '<div class="alert alert-info"><i class="fas fa-info-circle"></i> Data alamat belum dilengkapi</div>'}
            </div>
        </div>
    `;
    $('#data-diri').html(html);
}

function loadDataOrtuTab(siswa) {
    const ortu = siswa.ortu;
    
    if (!ortu || !siswa.data_ortu_completed) {
        $('#data-ortu').html(`
            <div class="alert alert-warning">
                <i class="fas fa-exclamation-triangle"></i> Data orang tua belum dilengkapi
            </div>
        `);
        return;
    }
    
    const html = `
        <div class="row">
            <div class="col-md-6">
                <h6 class="text-primary"><i class="fas fa-male"></i> Data Ayah</h6>
                <table class="table table-detail table-sm table-bordered">
                    <tr><td width="40%" class="bg-light"><strong>Status</strong></td><td>${ortu.status_ayah == 'masih_hidup' ? '<span class="badge badge-success">Masih Hidup</span>' : '<span class="badge badge-secondary">Meninggal</span>'}</td></tr>
                    <tr><td class="bg-light"><strong>Nama</strong></td><td>${ortu.nama_ayah || '-'}</td></tr>
                    <tr><td class="bg-light"><strong>NIK</strong></td><td>${ortu.nik_ayah || '-'}</td></tr>
                    <tr><td class="bg-light"><strong>HP</strong></td><td>${ortu.hp_ayah || '-'}</td></tr>
                    <tr><td class="bg-light"><strong>Pekerjaan</strong></td><td>${ortu.pekerjaan_ayah || '-'}</td></tr>
                    <tr><td class="bg-light"><strong>Penghasilan</strong></td><td>${ortu.penghasilan_ayah || '-'}</td></tr>
                </table>
            </div>
            <div class="col-md-6">
                <h6 class="text-primary"><i class="fas fa-female"></i> Data Ibu</h6>
                <table class="table table-detail table-sm table-bordered">
                    <tr><td width="40%" class="bg-light"><strong>Status</strong></td><td>${ortu.status_ibu == 'masih_hidup' ? '<span class="badge badge-success">Masih Hidup</span>' : '<span class="badge badge-secondary">Meninggal</span>'}</td></tr>
                    <tr><td class="bg-light"><strong>Nama</strong></td><td>${ortu.nama_ibu || '-'}</td></tr>
                    <tr><td class="bg-light"><strong>NIK</strong></td><td>${ortu.nik_ibu || '-'}</td></tr>
                    <tr><td class="bg-light"><strong>HP</strong></td><td>${ortu.hp_ibu || '-'}</td></tr>
                    <tr><td class="bg-light"><strong>Pekerjaan</strong></td><td>${ortu.pekerjaan_ibu || '-'}</td></tr>
                    <tr><td class="bg-light"><strong>Penghasilan</strong></td><td>${ortu.penghasilan_ibu || '-'}</td></tr>
                </table>
            </div>
        </div>
        <div class="row mt-3">
            <div class="col-md-12">
                <h6 class="text-primary"><i class="fas fa-home"></i> Alamat Orang Tua</h6>
                <table class="table table-detail table-sm table-bordered">
                    <tr><td width="20%" class="bg-light"><strong>No. KK</strong></td><td>${ortu.no_kk || '-'}</td></tr>
                    <tr><td class="bg-light"><strong>Alamat</strong></td><td>${ortu.alamat_ortu || '-'}</td></tr>
                    <tr><td class="bg-light"><strong>RT / RW</strong></td><td>${ortu.rt_ortu || '-'} / ${ortu.rw_ortu || '-'}</td></tr>
                    <tr><td class="bg-light"><strong>Kodepos</strong></td><td>${ortu.kodepos || '-'}</td></tr>
                </table>
            </div>
        </div>
    `;
    $('#data-ortu').html(html);
}

function loadDokumenTab(siswaId) {
    $('#dokumen').html('<div class="text-center"><i class="fas fa-spinner fa-spin"></i> Memuat dokumen...</div>');
    
    $.get(`{{ url('admin/siswa') }}/${siswaId}/dokumen`)
        .done(function(response) {
            if (response.success) {
                const dokumen = response.data;
                let html = '';
                
                if (dokumen.length === 0) {
                    html = `
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> Belum ada dokumen yang diupload
                        </div>
                    `;
                } else {
                    html = '<div class="row">';
                    dokumen.forEach(dok => {
                        const uploadDate = new Date(dok.created_at).toLocaleDateString('id-ID');
                        html += `
                            <div class="col-md-6 mb-3">
                                <div class="dokumen-item">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <h6 class="mb-1">
                                                <i class="fas fa-file-pdf text-danger"></i> 
                                                ${dok.jenis_dokumen_label}
                                            </h6>
                                            <small class="text-muted">
                                                <i class="fas fa-calendar"></i> ${uploadDate} | 
                                                <i class="fas fa-hdd"></i> ${dok.file_size_formatted}
                                            </small>
                                            ${dok.keterangan ? `<p class="mb-1 mt-2"><small>${dok.keterangan}</small></p>` : ''}
                                        </div>
                                        <div>
                                            <a href="${dok.file_url}" target="_blank" class="btn btn-sm btn-info" title="Lihat">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        `;
                    });
                    html += '</div>';
                }
                
                $('#dokumen').html(html);
            }
        })
        .fail(function() {
            $('#dokumen').html('<div class="alert alert-danger"><i class="fas fa-exclamation-triangle"></i> Gagal memuat dokumen</div>');
        });
}

function deleteSiswa(id) {
    Swal.fire({
        title: 'Hapus Data Siswa?',
        html: '<p>Data berikut akan dihapus <strong>permanen</strong>:</p>' +
              '<ul class="text-left">' +
              '<li>Data siswa</li>' +
              '<li>User account</li>' +
              '<li>Data orang tua</li>' +
              '<li>Dokumen yang diupload</li>' +
              '</ul>' +
              '<p class="text-danger"><strong>Tindakan ini tidak dapat dibatalkan!</strong></p>',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: '<i class="fas fa-trash"></i> Ya, Hapus!',
        cancelButtonText: '<i class="fas fa-times"></i> Batal',
        reverseButtons: true,
        focusCancel: true
    }).then((result) => {
        if (result.isConfirmed) {
            // Show loading
            Swal.fire({
                title: 'Menghapus...',
                html: 'Mohon tunggu sebentar',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
            
            $.ajax({
                url: `{{ url('admin/siswa') }}/${id}`,
                type: 'DELETE',
                data: {
                    _token: '{{ csrf_token() }}'
                }
            })
            .done(function(response) {
                if (response.success) {
                    Swal.fire({
                        title: 'Berhasil!',
                        text: response.message,
                        icon: 'success',
                        timer: 2000,
                        showConfirmButton: false
                    });
                    siswaTable.ajax.reload();
                } else {
                    Swal.fire('Gagal!', response.message, 'error');
                }
            })
            .fail(function() {
                Swal.fire('Error!', 'Terjadi kesalahan saat menghapus data siswa', 'error');
            });
        }
    });
}

function resetPassword(id) {
    if (confirm('Apakah Anda yakin ingin reset password siswa ini?\n\nPassword akan direset ke NISN dan siswa diminta login ulang.')) {
        $.ajax({
            url: `{{ url('admin/siswa') }}/${id}/reset-password`,
            type: 'PUT',
            data: {
                _token: '{{ csrf_token() }}'
            }
        })
        .done(function(response) {
            if (response.success) {
                toastr.success(response.message, 'Berhasil!');
            } else {
                toastr.error(response.message, 'Gagal!');
            }
        })
        .fail(function() {
            toastr.error('Terjadi kesalahan saat reset password', 'Error!');
        });
    }
}

function saveSiswa() {
    const formData = new FormData($('#siswaForm')[0]);
    const url = editingId ? `{{ url('admin/siswa') }}/${editingId}` : '{{ route('admin.siswa.store') }}';
    const method = editingId ? 'PUT' : 'POST';
    
    if (editingId) {
        formData.append('_method', 'PUT');
    }

    // Clear previous errors
    $('.is-invalid').removeClass('is-invalid');
    $('.invalid-feedback').text('');

    $.ajax({
        url: url,
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    })
    .done(function(response) {
        if (response.success) {
            $('#siswaModal').modal('hide');
            toastr.success(response.message, 'Berhasil!');
            siswaTable.ajax.reload();
            clearForm();
        } else {
            toastr.error(response.message, 'Gagal!');
        }
    })
    .fail(function(xhr) {
        if (xhr.status === 422) {
            const errors = xhr.responseJSON.errors;
            Object.keys(errors).forEach(function(field) {
                const input = $(`#${field}`);
                input.addClass('is-invalid');
                input.siblings('.invalid-feedback').text(errors[field][0]);
            });
            toastr.error('Silakan periksa data yang diisi', 'Validasi Gagal!');
        } else {
            toastr.error('Terjadi kesalahan pada server. Silakan coba lagi.', 'Error!');
        }
    });
}

function clearForm() {
    $('#siswaForm')[0].reset();
    $('.is-invalid').removeClass('is-invalid');
    $('.invalid-feedback').text('');
    editingId = null;
}

</script>
@stop