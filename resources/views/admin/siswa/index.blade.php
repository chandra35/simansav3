@extends('adminlte::page')

@section('title', 'Data Siswa - SIMANSA')

@section('content_header')
    <h1>Data Siswa</h1>
@stop

@section('content')
{{-- Card Informasi Siswa --}}
<div class="row mb-3">
    <div class="col-md-3 col-sm-6">
        <div class="info-box bg-info">
            <span class="info-box-icon"><i class="fas fa-users"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Total Siswa</span>
                <span class="info-box-number">{{ $stats['total_siswa'] }} Siswa</span>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6">
        <div class="info-box bg-primary">
            <span class="info-box-icon"><i class="fas fa-male"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Laki-Laki</span>
                <span class="info-box-number">{{ $stats['laki_laki'] }} Siswa</span>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6">
        <div class="info-box bg-danger">
            <span class="info-box-icon"><i class="fas fa-female"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Perempuan</span>
                <span class="info-box-number">{{ $stats['perempuan'] }} Siswa</span>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6">
        <div class="info-box bg-success">
            <span class="info-box-icon"><i class="fas fa-check-circle"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Data Lengkap</span>
                <span class="info-box-number">{{ $stats['data_lengkap'] }} Siswa</span>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-users mr-1"></i>
                    Manajemen Data Siswa
                </h3>
                <div class="card-tools">
                    @can('create-siswa')
                        <a href="{{ route('admin.siswa.import') }}" class="btn btn-success mr-2">
                            <i class="fas fa-file-excel"></i> Import Data Siswa
                        </a>
                        <a href="{{ route('admin.siswa.import-npsn') }}" class="btn btn-info mr-2">
                            <i class="fas fa-school"></i> Import NPSN
                        </a>
                        <button type="button" class="btn btn-primary" onclick="addSiswa()">
                            <i class="fas fa-plus"></i> Tambah Siswa
                        </button>
                    @endcan
                </div>
            </div>
            <div class="card-body">
                {{-- Filter Section --}}
                <div class="row mb-3">
                    <div class="col-md-12">
                        <div class="card bg-light">
                            <div class="card-body p-3">
                                <form id="filterForm" class="form-inline">
                                    <div class="form-group mr-2 mb-2">
                                        <label for="filterJenisKelamin" class="mr-2">
                                            <i class="fas fa-venus-mars"></i> Jenis Kelamin:
                                        </label>
                                        <select id="filterJenisKelamin" class="form-control form-control-sm" style="width: 150px;">
                                            <option value="">Semua</option>
                                            <option value="L">Laki-Laki</option>
                                            <option value="P">Perempuan</option>
                                        </select>
                                    </div>
                                    
                                    {{-- Hide Tingkat & Kelas filters for Wali Kelas (they only see their own class) --}}
                                    @if(!auth()->user()->hasRole('Wali Kelas') || auth()->user()->hasRole(['Super Admin', 'Admin', 'Kepala Madrasah']))
                                    <div class="form-group mr-2 mb-2">
                                        <label for="filterTingkat" class="mr-2">
                                            <i class="fas fa-layer-group"></i> Tingkat:
                                        </label>
                                        <select id="filterTingkat" class="form-control form-control-sm" style="width: 150px;">
                                            <option value="">Semua</option>
                                            @foreach($tingkatOptions as $value => $label)
                                                <option value="{{ $value }}">{{ $label }}</option>
                                            @endforeach
                                            <option value="tanpa_rombel">Tanpa Rombel</option>
                                        </select>
                                    </div>
                                    <div class="form-group mr-2 mb-2">
                                        <label for="filterKelas" class="mr-2">
                                            <i class="fas fa-door-open"></i> Kelas:
                                        </label>
                                        <select id="filterKelas" class="form-control form-control-sm" style="width: 200px;" disabled>
                                            <option value="">Pilih Tingkat Dulu</option>
                                        </select>
                                    </div>
                                    @endif
                                    
                                    <div class="form-group mr-2 mb-2">
                                        <label for="filterStatus" class="mr-2">
                                            <i class="fas fa-check-circle"></i> Status Data:
                                        </label>
                                        <select id="filterStatus" class="form-control form-control-sm" style="width: 150px;">
                                            <option value="">Semua</option>
                                            <option value="lengkap">Data Lengkap</option>
                                            <option value="belum">Belum Lengkap</option>
                                        </select>
                                    </div>
                                    <button type="button" id="btnResetFilter" class="btn btn-sm btn-secondary mb-2">
                                        <i class="fas fa-redo"></i> Reset
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="table-responsive">
                    <table id="siswa-table" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>NISN</th>
                                <th>Nama Lengkap</th>
                                <th>Jenis Kelamin</th>
                                <th>Kelas</th>
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
                        <a class="nav-link" id="sekolah-asal-tab" data-toggle="tab" href="#sekolah-asal" role="tab">
                            <i class="fas fa-school"></i> Sekolah Asal
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
                    <div class="tab-pane fade" id="sekolah-asal" role="tabpanel">
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
        /* DataTables length selector styling */
        .dataTables_length select {
            min-width: 80px !important;
            width: auto !important;
            padding: 0.375rem 1.75rem 0.375rem 0.75rem !important;
        }
        .dataTables_length {
            margin-bottom: 1rem;
        }
        .dataTables_wrapper .dataTables_length,
        .dataTables_wrapper .dataTables_filter {
            margin-bottom: 0.75rem;
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
        .btn-group-vertical {
            display: flex;
            flex-direction: column;
            gap: 0;
        }
        .btn-group-vertical .btn {
            border-radius: 0.25rem !important;
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
            type: 'GET',
            error: function(xhr, error, code) {
                console.log('Ajax error:', xhr, error, code);
                if (xhr.status === 500) {
                    alert('Terjadi kesalahan server. Silakan coba lagi atau pilih jumlah data yang lebih sedikit.');
                }
            }
        },
        columns: [
            { data: 'nisn', name: 'nisn' },
            { data: 'nama_lengkap', name: 'nama_lengkap' },
            { data: 'jenis_kelamin', name: 'jenis_kelamin' },
            { data: 'kelas', name: 'kelas' },
            { data: 'username', name: 'username' },
            { data: 'status_ortu', name: 'status_ortu', orderable: false, searchable: false },
            { data: 'status_diri', name: 'status_diri', orderable: false, searchable: false },
            { data: 'created_at', name: 'created_at' },
            { data: 'actions', name: 'actions', orderable: false, searchable: false }
        ],
        lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "Semua"]],
        pageLength: 10,
        order: [[7, 'desc']],
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
                loadSekolahAsalTab(siswa);
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
    
    // Handle alamat siswa
    let alamatHtml = '';
    
    // Cek jenis tempat tinggal atau alamat_sama_ortu
    const tinggalBersamaOrtu = siswa.jenis_tempat_tinggal === 'Bersama Orang Tua' || siswa.alamat_sama_ortu;
    
    if (tinggalBersamaOrtu) {
        // Alamat sama dengan ortu / tinggal bersama ortu
        const ortu = siswa.ortu;
        if (ortu && ortu.alamat_ortu) {
            const jenisInfo = siswa.jenis_tempat_tinggal === 'Bersama Orang Tua' 
                ? 'Tinggal Bersama Orang Tua' 
                : 'Alamat sama dengan Orang Tua';
            
            alamatHtml = `
                <div class="alert alert-info mb-2">
                    <i class="fas fa-info-circle"></i> <strong>${jenisInfo}</strong>
                </div>
                <table class="table table-detail table-sm table-bordered">
                    <tr><td width="40%" class="bg-light"><strong>No. KK</strong></td><td>${ortu.no_kk || '-'}</td></tr>
                    <tr><td class="bg-light"><strong>Alamat</strong></td><td>${ortu.alamat_ortu}</td></tr>
                    <tr><td class="bg-light"><strong>RT / RW</strong></td><td>${ortu.rt_ortu || '-'} / ${ortu.rw_ortu || '-'}</td></tr>
                    <tr><td class="bg-light"><strong>Kelurahan/Desa</strong></td><td>${ortu.kelurahan ? ortu.kelurahan.name : '-'}</td></tr>
                    <tr><td class="bg-light"><strong>Kecamatan</strong></td><td>${ortu.kecamatan ? ortu.kecamatan.name : '-'}</td></tr>
                    <tr><td class="bg-light"><strong>Kab/Kota</strong></td><td>${ortu.kabupaten ? ortu.kabupaten.name : '-'}</td></tr>
                    <tr><td class="bg-light"><strong>Provinsi</strong></td><td>${ortu.provinsi ? ortu.provinsi.name : '-'}</td></tr>
                    <tr><td class="bg-light"><strong>Kodepos</strong></td><td>${ortu.kodepos || '-'}</td></tr>
                </table>
            `;
        } else {
            alamatHtml = '<div class="alert alert-warning"><i class="fas fa-exclamation-triangle"></i> Tinggal bersama ortu, tapi data alamat ortu belum dilengkapi</div>';
        }
    } else if (siswa.alamat_siswa) {
        // Alamat sendiri
        const jenisInfo = siswa.jenis_tempat_tinggal ? `<div class="alert alert-info mb-2"><i class="fas fa-home"></i> <strong>Jenis Tempat Tinggal: ${siswa.jenis_tempat_tinggal}</strong></div>` : '';
        
        alamatHtml = `
            ${jenisInfo}
            <table class="table table-detail table-sm table-bordered">
                <tr><td width="40%" class="bg-light"><strong>Alamat</strong></td><td>${siswa.alamat_siswa}</td></tr>
                <tr><td class="bg-light"><strong>RT / RW</strong></td><td>${siswa.rt_siswa || '-'} / ${siswa.rw_siswa || '-'}</td></tr>
                <tr><td class="bg-light"><strong>Kodepos</strong></td><td>${siswa.kodepos_siswa || '-'}</td></tr>
            </table>
        `;
    } else {
        alamatHtml = '<div class="alert alert-info"><i class="fas fa-info-circle"></i> Data alamat belum dilengkapi</div>';
    }
    
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
                ${alamatHtml}
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
                    <tr><td class="bg-light"><strong>Kelurahan/Desa</strong></td><td>${ortu.kelurahan ? ortu.kelurahan.name : '-'}</td></tr>
                    <tr><td class="bg-light"><strong>Kecamatan</strong></td><td>${ortu.kecamatan ? ortu.kecamatan.name : '-'}</td></tr>
                    <tr><td class="bg-light"><strong>Kab/Kota</strong></td><td>${ortu.kabupaten ? ortu.kabupaten.name : '-'}</td></tr>
                    <tr><td class="bg-light"><strong>Provinsi</strong></td><td>${ortu.provinsi ? ortu.provinsi.name : '-'}</td></tr>
                    <tr><td class="bg-light"><strong>Kodepos</strong></td><td>${ortu.kodepos || '-'}</td></tr>
                </table>
            </div>
        </div>
    `;
    $('#data-ortu').html(html);
}

function loadSekolahAsalTab(siswa) {
    if (!siswa.npsn_asal_sekolah) {
        $('#sekolah-asal').html(`
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i> Data sekolah asal belum diisi
            </div>
        `);
        return;
    }
    
    // Handle both camelCase and snake_case
    const sekolah = siswa.sekolah_asal || siswa.sekolahAsal;
    
    if (!sekolah) {
        $('#sekolah-asal').html(`
            <div class="alert alert-warning">
                <i class="fas fa-exclamation-triangle"></i> NPSN: ${siswa.npsn_asal_sekolah} - Data sekolah tidak ditemukan di database
            </div>
        `);
        return;
    }
    
    const html = `
        <div class="row">
            <div class="col-md-6">
                <h6 class="text-primary"><i class="fas fa-school"></i> Informasi Sekolah</h6>
                <table class="table table-detail table-sm table-bordered">
                    <tr><td width="40%" class="bg-light"><strong>NPSN</strong></td><td><span class="badge badge-primary">${sekolah.npsn || '-'}</span></td></tr>
                    <tr><td class="bg-light"><strong>Nama Sekolah</strong></td><td><strong>${sekolah.nama || '-'}</strong></td></tr>
                    <tr><td class="bg-light"><strong>Bentuk Pendidikan</strong></td><td>${sekolah.bentuk_pendidikan || '-'}</td></tr>
                    <tr><td class="bg-light"><strong>Status</strong></td><td>
                        ${sekolah.status_sekolah == 'Negeri' ? '<span class="badge badge-success">Negeri</span>' : sekolah.status_sekolah == 'Swasta' ? '<span class="badge badge-info">Swasta</span>' : '<span class="badge badge-secondary">' + (sekolah.status_sekolah || '-') + '</span>'}
                    </td></tr>
                </table>
            </div>
            <div class="col-md-6">
                <h6 class="text-primary"><i class="fas fa-map-marker-alt"></i> Lokasi Sekolah</h6>
                <table class="table table-detail table-sm table-bordered">
                    <tr><td width="40%" class="bg-light"><strong>Provinsi</strong></td><td>${sekolah.provinsi || '-'}</td></tr>
                    <tr><td class="bg-light"><strong>Kab/Kota</strong></td><td>${sekolah.kabupaten_kota || '-'}</td></tr>
                    <tr><td class="bg-light"><strong>Kecamatan</strong></td><td>${sekolah.kecamatan || '-'}</td></tr>
                    <tr><td class="bg-light"><strong>Alamat</strong></td><td>${sekolah.alamat_jalan || '-'}</td></tr>
                </table>
            </div>
        </div>
        <div class="row mt-3">
            <div class="col-md-12 text-center">
                <a href="{{ url('admin/sekolah-asal') }}/${sekolah.npsn}" class="btn btn-primary btn-sm" target="_blank">
                    <i class="fas fa-external-link-alt"></i> Lihat Detail Sekolah & Daftar Siswa
                </a>
            </div>
        </div>
    `;
    $('#sekolah-asal').html(html);
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
                        const isPdf = dok.file_url.toLowerCase().endsWith('.pdf');
                        const isImage = /\.(jpg|jpeg|png|gif|webp)$/i.test(dok.file_url);
                        
                        html += `
                            <div class="col-md-6 mb-3">
                                <div class="dokumen-item">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div class="flex-grow-1">
                                            <h6 class="mb-1">
                                                <i class="fas ${isPdf ? 'fa-file-pdf text-danger' : isImage ? 'fa-file-image text-primary' : 'fa-file text-secondary'}"></i> 
                                                ${dok.jenis_dokumen_label}
                                            </h6>
                                            <small class="text-muted">
                                                <i class="fas fa-calendar"></i> ${uploadDate} | 
                                                <i class="fas fa-hdd"></i> ${dok.file_size_formatted}
                                            </small>
                                            ${dok.keterangan ? `<p class="mb-1 mt-2"><small>${dok.keterangan}</small></p>` : ''}
                                        </div>
                                        <div class="btn-group-vertical">
                                            <a href="${dok.file_url}" 
                                               class="btn btn-sm ${isImage ? 'btn-info' : 'btn-primary'} mb-1 btn-preview-doc" 
                                               data-url="${dok.file_url}"
                                               data-type="${isImage ? 'image' : isPdf ? 'pdf' : 'other'}"
                                               data-title="${dok.jenis_dokumen_label}"
                                               title="${isImage ? 'Preview & Zoom' : 'Lihat File'}">
                                                <i class="fas ${isImage ? 'fa-search-plus' : 'fa-eye'}"></i>
                                            </a>
                                            <a href="${dok.file_url}" 
                                               download 
                                               class="btn btn-sm btn-success" 
                                               title="Download">
                                                <i class="fas fa-download"></i>
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
                
                // Initialize preview click handler
                $(document).off('click', '.btn-preview-doc');
                $(document).on('click', '.btn-preview-doc', function(e) {
                    e.preventDefault();
                    const url = $(this).data('url');
                    const type = $(this).data('type');
                    const title = $(this).data('title');
                    
                    if (type === 'image') {
                        // Open image in new window with zoom functionality
                        const win = window.open('', 'ImagePreview', 'width=1000,height=800,scrollbars=yes,resizable=yes');
                        win.document.write('<!DOCTYPE html><html><head><title>' + title + '</title>');
                        win.document.write('<style>');
                        win.document.write('* { margin: 0; padding: 0; box-sizing: border-box; }');
                        win.document.write('body { background: #1a1a1a; font-family: Arial, sans-serif; overflow: hidden; }');
                        win.document.write('.header { background: #2d2d2d; padding: 15px 20px; color: #fff; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 2px 5px rgba(0,0,0,0.3); }');
                        win.document.write('.header h3 { margin: 0; font-size: 18px; font-weight: 500; }');
                        win.document.write('.controls { display: flex; gap: 10px; }');
                        win.document.write('.btn { background: #007bff; color: white; border: none; padding: 8px 16px; border-radius: 4px; cursor: pointer; font-size: 14px; transition: background 0.3s; }');
                        win.document.write('.btn:hover { background: #0056b3; }');
                        win.document.write('.btn-success { background: #28a745; }');
                        win.document.write('.btn-success:hover { background: #1e7e34; }');
                        win.document.write('.btn-danger { background: #dc3545; }');
                        win.document.write('.btn-danger:hover { background: #c82333; }');
                        win.document.write('.image-container { width: 100%; height: calc(100vh - 70px); display: flex; align-items: center; justify-content: center; overflow: auto; cursor: grab; position: relative; }');
                        win.document.write('.image-container.dragging { cursor: grabbing; }');
                        win.document.write('.image-container img { max-width: 100%; max-height: 100%; object-fit: contain; transition: transform 0.3s; user-select: none; }');
                        win.document.write('.zoom-info { position: absolute; bottom: 20px; right: 20px; background: rgba(0,0,0,0.8); color: white; padding: 8px 15px; border-radius: 20px; font-size: 14px; }');
                        win.document.write('</style></head><body>');
                        win.document.write('<div class="header"><h3>' + title + '</h3>');
                        win.document.write('<div class="controls">');
                        win.document.write('<button class="btn" onclick="zoomOut()">🔍 Zoom Out</button>');
                        win.document.write('<button class="btn" onclick="resetZoom()">↺ Reset</button>');
                        win.document.write('<button class="btn" onclick="zoomIn()">🔍 Zoom In</button>');
                        win.document.write('<a href="' + url + '" download class="btn btn-success" style="text-decoration:none;">⬇ Download</a>');
                        win.document.write('<button class="btn btn-danger" onclick="window.close()">✕ Close</button>');
                        win.document.write('</div></div>');
                        win.document.write('<div class="image-container" id="imageContainer">');
                        win.document.write('<img src="' + url + '" id="previewImage" alt="' + title + '">');
                        win.document.write('<div class="zoom-info" id="zoomInfo">100%</div></div>');
                        win.document.write('<scr' + 'ipt>');
                        win.document.write('let scale = 1;');
                        win.document.write('const img = document.getElementById("previewImage");');
                        win.document.write('const container = document.getElementById("imageContainer");');
                        win.document.write('const zoomInfo = document.getElementById("zoomInfo");');
                        win.document.write('let isDragging = false;');
                        win.document.write('let startX, startY, scrollLeft, scrollTop;');
                        win.document.write('function updateZoom() { img.style.transform = "scale(" + scale + ")"; zoomInfo.textContent = Math.round(scale * 100) + "%"; }');
                        win.document.write('function zoomIn() { scale = Math.min(scale + 0.2, 5); updateZoom(); }');
                        win.document.write('function zoomOut() { scale = Math.max(scale - 0.2, 0.2); updateZoom(); }');
                        win.document.write('function resetZoom() { scale = 1; updateZoom(); container.scrollTop = 0; container.scrollLeft = 0; }');
                        win.document.write('container.addEventListener("wheel", function(e) { e.preventDefault(); if (e.deltaY < 0) { zoomIn(); } else { zoomOut(); } });');
                        win.document.write('container.addEventListener("mousedown", function(e) { isDragging = true; container.classList.add("dragging"); startX = e.pageX - container.offsetLeft; startY = e.pageY - container.offsetTop; scrollLeft = container.scrollLeft; scrollTop = container.scrollTop; });');
                        win.document.write('container.addEventListener("mouseleave", function() { isDragging = false; container.classList.remove("dragging"); });');
                        win.document.write('container.addEventListener("mouseup", function() { isDragging = false; container.classList.remove("dragging"); });');
                        win.document.write('container.addEventListener("mousemove", function(e) { if (!isDragging) return; e.preventDefault(); const x = e.pageX - container.offsetLeft; const y = e.pageY - container.offsetTop; const walkX = (x - startX) * 2; const walkY = (y - startY) * 2; container.scrollLeft = scrollLeft - walkX; container.scrollTop = scrollTop - walkY; });');
                        win.document.write('document.addEventListener("keydown", function(e) { if (e.key === "+" || e.key === "=") zoomIn(); if (e.key === "-") zoomOut(); if (e.key === "0") resetZoom(); if (e.key === "Escape") window.close(); });');
                        win.document.write('</scr' + 'ipt></body></html>');
                        win.document.close();
                    } else {
                        // For PDF and other files, open in new tab
                        window.open(url, '_blank', 'width=1000,height=800,scrollbars=yes,resizable=yes');
                    }
                });
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

// Filter Functions
$(document).ready(function() {
    // Filter Tingkat - Load Kelas
    $('#filterTingkat').on('change', function() {
        let tingkat = $(this).val();
        let $kelasSelect = $('#filterKelas');
        
        // Jika pilih "Tanpa Rombel", disable kelas select
        if (tingkat === 'tanpa_rombel') {
            $kelasSelect.prop('disabled', true).html('<option value="">N/A (Tanpa Rombel)</option>');
            applyFilters();
            return;
        }
        
        $kelasSelect.prop('disabled', true).html('<option value="">Memuat...</option>');
        
        if (!tingkat) {
            $kelasSelect.html('<option value="">Pilih Tingkat Dulu</option>');
            applyFilters();
            return;
        }
        
        $.ajax({
            url: '{{ route('admin.siswa.kelas-by-tingkat') }}',
            data: { tingkat: tingkat },
            success: function(data) {
                let options = '<option value="">Semua Kelas</option>';
                data.forEach(function(kelas) {
                    options += `<option value="${kelas.id}">${kelas.text}</option>`;
                });
                $kelasSelect.html(options).prop('disabled', false);
                applyFilters();
            },
            error: function() {
                $kelasSelect.html('<option value="">Error loading</option>');
                toastr.error('Gagal memuat data kelas');
            }
        });
    });
    
    // Apply filter on change
    $('#filterJenisKelamin, #filterKelas, #filterStatus').on('change', function() {
        applyFilters();
    });
    
    // Reset Filter
    $('#btnResetFilter').on('click', function() {
        $('#filterJenisKelamin').val('');
        $('#filterTingkat').val('');
        $('#filterKelas').val('').prop('disabled', true).html('<option value="">Pilih Tingkat Dulu</option>');
        $('#filterStatus').val('');
        applyFilters();
    });
    
    function applyFilters() {
        let jk = $('#filterJenisKelamin').val();
        let tingkat = $('#filterTingkat').val();
        let kelas = $('#filterKelas').val();
        let status = $('#filterStatus').val();
        
        // Build filter parameters
        let filterParams = {};
        if (jk) filterParams.jenis_kelamin = jk;
        if (tingkat) filterParams.tingkat = tingkat;
        if (kelas) filterParams.kelas_id = kelas;
        if (status) filterParams.status = status;
        
        // Reload DataTable with filters
        siswaTable.settings()[0].ajax.data = function(d) {
            return $.extend({}, d, filterParams);
        };
        siswaTable.ajax.reload();
    }
});

</script>
@stop