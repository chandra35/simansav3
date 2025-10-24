@extends('adminlte::page')

@section('title', 'Data GTK - SIMANSA')

@section('content_header')
    <h1>Data GTK (Guru dan Tenaga Kependidikan)</h1>
@stop

@section('content')
{{-- Card Informasi GTK --}}
<div class="row mb-3">
    <div class="col-md-3 col-sm-6">
        <div class="info-box bg-info">
            <span class="info-box-icon"><i class="fas fa-chalkboard-teacher"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Total GTK</span>
                <span class="info-box-number">{{ $stats['total_gtk'] }} Orang</span>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6">
        <div class="info-box bg-primary">
            <span class="info-box-icon"><i class="fas fa-male"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Laki-laki</span>
                <span class="info-box-number">{{ $stats['laki_laki'] }} Orang</span>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6">
        <div class="info-box bg-danger">
            <span class="info-box-icon"><i class="fas fa-female"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Perempuan</span>
                <span class="info-box-number">{{ $stats['perempuan'] }} Orang</span>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6">
        <div class="info-box bg-success">
            <span class="info-box-icon"><i class="fas fa-check-circle"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Data Lengkap</span>
                <span class="info-box-number">{{ $stats['data_lengkap'] }} Orang</span>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">
            <i class="fas fa-list"></i>
            Daftar GTK
        </h3>
        <div class="card-tools">
            @can('create-gtk')
                <a href="{{ route('admin.gtk.import') }}" class="btn btn-success btn-sm mr-1">
                    <i class="fas fa-file-excel"></i> Import GTK
                </a>
                <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#addGtkModal">
                    <i class="fas fa-plus"></i> Tambah GTK
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
                                <label for="filterKategoriPtk" class="mr-2">
                                    <i class="fas fa-users"></i> Kategori PTK:
                                </label>
                                <select id="filterKategoriPtk" class="form-control form-control-sm" style="width: 180px;">
                                    <option value="">Semua</option>
                                    <option value="Pendidik">Pendidik (Guru)</option>
                                    <option value="Tenaga Kependidikan">Tenaga Kependidikan</option>
                                </select>
                            </div>
                            <div class="form-group mr-2 mb-2">
                                <label for="filterJenisPtk" class="mr-2">
                                    <i class="fas fa-user-tag"></i> Jenis PTK:
                                </label>
                                <select id="filterJenisPtk" class="form-control form-control-sm" style="width: 180px;">
                                    <option value="">Semua</option>
                                    <option value="Guru Mapel">Guru Mapel</option>
                                    <option value="Guru BK">Guru BK</option>
                                    <option value="Kepala TU">Kepala TU</option>
                                    <option value="Staff TU">Staff TU</option>
                                    <option value="Bendahara">Bendahara</option>
                                    <option value="Laboran">Laboran</option>
                                    <option value="Pustakawan">Pustakawan</option>
                                    <option value="Cleaning Service">Cleaning Service</option>
                                    <option value="Satpam">Satpam</option>
                                    <option value="Lainnya">Lainnya</option>
                                </select>
                            </div>
                            <div class="form-group mr-2 mb-2">
                                <label for="filterJenisKelamin" class="mr-2">
                                    <i class="fas fa-venus-mars"></i> Jenis Kelamin:
                                </label>
                                <select id="filterJenisKelamin" class="form-control form-control-sm" style="width: 150px;">
                                    <option value="">Semua</option>
                                    <option value="L">Laki-laki</option>
                                    <option value="P">Perempuan</option>
                                </select>
                            </div>
                            <div class="form-group mr-2 mb-2">
                                <label for="filterStatusKepegawaian" class="mr-2">
                                    <i class="fas fa-briefcase"></i> Status Kepeg:
                                </label>
                                <select id="filterStatusKepegawaian" class="form-control form-control-sm" style="width: 150px;">
                                    <option value="">Semua</option>
                                    @foreach($statusKepegawaianOptions as $value => $label)
                                        <option value="{{ $value }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group mr-2 mb-2">
                                <label for="filterStatus" class="mr-2">
                                    <i class="fas fa-database"></i> Status Data:
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
            <table id="gtk-table" class="table table-bordered table-striped table-hover">
                <thead class="bg-light">
                    <tr>
                        <th style="width: 40px;">No</th>
                        <th>Nama Lengkap</th>
                        <th>NIK</th>
                        <th>Kategori PTK</th>
                        <th>Jenis PTK</th>
                        <th>Status Kepeg</th>
                        <th>Jabatan</th>
                        <th>Username</th>
                        <th>Status Diri</th>
                        <th>Status Kepeg</th>
                        <th style="width: 150px;" class="text-center">Aksi</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
</div>

{{-- Modal Add GTK --}}
<div class="modal fade" id="addGtkModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary">
                <h5 class="modal-title">
                    <i class="fas fa-plus"></i> Tambah GTK Baru
                </h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form id="addGtkForm">
                @csrf
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i>
                        <strong>Informasi:</strong> Username akan dibuat otomatis dari NIK. Password default adalah NIK.
                    </div>
                    
                    <div class="form-group">
                        <label for="nama_lengkap">Nama Lengkap <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="nama_lengkap" name="nama_lengkap" required>
                        <span class="invalid-feedback d-block" id="error-nama_lengkap"></span>
                    </div>

                    <div class="form-group">
                        <label for="nik">NIK (16 digit) <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="nik" name="nik" maxlength="16" required>
                        <small class="form-text text-muted">NIK akan digunakan sebagai username dan password default</small>
                        <span class="invalid-feedback d-block" id="error-nik"></span>
                    </div>

                    <div class="form-group">
                        <label for="jenis_kelamin">Jenis Kelamin <span class="text-danger">*</span></label>
                        <select class="form-control" id="jenis_kelamin" name="jenis_kelamin" required>
                            <option value="">Pilih Jenis Kelamin</option>
                            <option value="L">Laki-laki</option>
                            <option value="P">Perempuan</option>
                        </select>
                        <span class="invalid-feedback d-block" id="error-jenis_kelamin"></span>
                    </div>

                    <div class="form-group">
                        <label for="kategori_ptk">Kategori PTK <span class="text-danger">*</span></label>
                        <select class="form-control" id="kategori_ptk" name="kategori_ptk" required>
                            <option value="">Pilih Kategori PTK</option>
                            <option value="Pendidik">Pendidik (Guru)</option>
                            <option value="Tenaga Kependidikan">Tenaga Kependidikan (Staff TU, dll)</option>
                        </select>
                        <small class="form-text text-muted">Kategori PTK: Pendidik untuk Guru, Tenaga Kependidikan untuk Staff non-Guru</small>
                        <span class="invalid-feedback d-block" id="error-kategori_ptk"></span>
                    </div>

                    <div class="form-group">
                        <label for="jenis_ptk">Jenis PTK <span class="text-danger">*</span></label>
                        <select class="form-control" id="jenis_ptk" name="jenis_ptk" required disabled>
                            <option value="">Pilih Kategori PTK terlebih dahulu</option>
                        </select>
                        <small class="form-text text-muted">Jenis PTK akan muncul setelah memilih Kategori PTK</small>
                        <span class="invalid-feedback d-block" id="error-jenis_ptk"></span>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times"></i> Batal
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Modal View GTK --}}
<div class="modal fade" id="viewGtkModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-info">
                <h5 class="modal-title">
                    <i class="fas fa-eye"></i> Detail GTK
                </h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body" id="viewGtkContent">
                <div class="text-center">
                    <i class="fas fa-spinner fa-spin fa-3x"></i>
                    <p>Loading...</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

{{-- Modal Edit GTK --}}
<div class="modal fade" id="editGtkModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title">
                    <i class="fas fa-edit"></i> Edit Data GTK
                </h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form id="editGtkForm">
                @csrf
                @method('PUT')
                <input type="hidden" id="edit_gtk_id" name="gtk_id">
                <div class="modal-body" id="editGtkContent">
                    <div class="text-center">
                        <i class="fas fa-spinner fa-spin fa-3x"></i>
                        <p>Loading...</p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times"></i> Batal
                    </button>
                    <button type="submit" class="btn btn-warning">
                        <i class="fas fa-save"></i> Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@stop

@section('css')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap4.min.css">
<style>
    .info-box {
        min-height: 80px;
    }
    .info-box-number {
        font-weight: bold;
    }
    
    /* DataTables styling */
    .dataTables_length select {
        min-width: 80px !important;
        width: auto !important;
        padding: 0.375rem 1.75rem 0.375rem 0.75rem !important;
    }
    .dataTables_length {
        margin-bottom: 1rem;
    }
    
    #gtk-table {
        font-size: 0.9rem;
    }
    
    .badge {
        font-size: 85%;
    }
</style>
@stop

@section('js')
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap4.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
$(document).ready(function() {
    // Initialize DataTable
    let gtkTable = $('#gtk-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route('admin.gtk.data') }}',
            data: function(d) {
                d.kategori_ptk = $('#filterKategoriPtk').val();
                d.jenis_ptk = $('#filterJenisPtk').val();
                d.jenis_kelamin = $('#filterJenisKelamin').val();
                d.status_kepegawaian = $('#filterStatusKepegawaian').val();
                d.status = $('#filterStatus').val();
            }
        },
        lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "Semua"]],
        pageLength: 10,
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'nama_lengkap', name: 'nama_lengkap' },
            { data: 'nik', name: 'nik' },
            { data: 'kategori_ptk', name: 'kategori_ptk' },
            { data: 'jenis_ptk', name: 'jenis_ptk' },
            { data: 'status_kepegawaian', name: 'status_kepegawaian' },
            { data: 'jabatan', name: 'jabatan' },
            { data: 'username', name: 'username' },
            { data: 'status_diri', name: 'status_diri', orderable: false, searchable: false },
            { data: 'status_kepeg', name: 'status_kepeg', orderable: false, searchable: false },
            { data: 'actions', name: 'actions', orderable: false, searchable: false, className: 'text-center' }
        ],
        language: {
            processing: '<i class="fa fa-spinner fa-spin fa-3x fa-fw"></i><span class="sr-only">Loading...</span>',
            search: "Cari:",
            lengthMenu: "Tampilkan _MENU_ data",
            info: "Menampilkan _START_ - _END_ dari _TOTAL_ data",
            infoEmpty: "Menampilkan 0 - 0 dari 0 data",
            zeroRecords: "Data tidak ditemukan",
            paginate: {
                first: "Pertama",
                last: "Terakhir",
                next: "Selanjutnya",
                previous: "Sebelumnya"
            }
        }
    });

    // Filter functionality - Cascading Kategori PTK -> Jenis PTK
    const filterJenisPtkOptions = {
        'Pendidik': [
            { value: 'Guru Mapel', text: 'Guru Mapel' },
            { value: 'Guru BK', text: 'Guru BK' }
        ],
        'Tenaga Kependidikan': [
            { value: 'Kepala TU', text: 'Kepala TU' },
            { value: 'Staff TU', text: 'Staff TU' },
            { value: 'Bendahara', text: 'Bendahara' },
            { value: 'Laboran', text: 'Laboran' },
            { value: 'Pustakawan', text: 'Pustakawan' },
            { value: 'Cleaning Service', text: 'Cleaning Service' },
            { value: 'Satpam', text: 'Satpam' },
            { value: 'Lainnya', text: 'Lainnya' }
        ]
    };

    $('#filterKategoriPtk').on('change', function() {
        const kategori = $(this).val();
        const filterJenisPtk = $('#filterJenisPtk');
        const currentValue = filterJenisPtk.val();
        
        // Reset jenis_ptk filter
        filterJenisPtk.empty();
        filterJenisPtk.append('<option value="">Semua</option>');
        
        if (kategori && filterJenisPtkOptions[kategori]) {
            filterJenisPtkOptions[kategori].forEach(function(option) {
                filterJenisPtk.append(`<option value="${option.value}">${option.text}</option>`);
            });
        } else {
            // If no kategori selected, show all jenis options
            Object.values(filterJenisPtkOptions).flat().forEach(function(option) {
                filterJenisPtk.append(`<option value="${option.value}">${option.text}</option>`);
            });
        }
        
        // Reload table
        gtkTable.ajax.reload();
    });

    $('#filterJenisPtk, #filterJenisKelamin, #filterStatusKepegawaian, #filterStatus').on('change', function() {
        gtkTable.ajax.reload();
    });

    $('#btnResetFilter').on('click', function() {
        $('#filterKategoriPtk').val('');
        $('#filterJenisPtk').empty().append('<option value="">Semua</option>');
        // Repopulate all jenis options
        Object.values(filterJenisPtkOptions).flat().forEach(function(option) {
            $('#filterJenisPtk').append(`<option value="${option.value}">${option.text}</option>`);
        });
        $('#filterJenisKelamin').val('');
        $('#filterStatusKepegawaian').val('');
        $('#filterStatus').val('');
        gtkTable.ajax.reload();
    });

    // Add GTK Form Submit
    $('#addGtkForm').on('submit', function(e) {
        e.preventDefault();
        
        // Clear previous errors
        $('.invalid-feedback').text('');
        
        $.ajax({
            url: '{{ route('admin.gtk.store') }}',
            type: 'POST',
            data: $(this).serialize(),
            success: function(response) {
                $('#addGtkModal').modal('hide');
                $('#addGtkForm')[0].reset();
                
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: response.message,
                    timer: 3000
                });
                
                gtkTable.ajax.reload();
            },
            error: function(xhr) {
                if (xhr.status === 422) {
                    const errors = xhr.responseJSON.errors;
                    for (let field in errors) {
                        $('#error-' + field).text(errors[field][0]);
                    }
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal!',
                        text: xhr.responseJSON?.message || 'Terjadi kesalahan'
                    });
                }
            }
        });
    });

    // NIK input validation (only numbers, max 16)
    $('#nik').on('input', function() {
        this.value = this.value.replace(/[^0-9]/g, '').substring(0, 16);
    });

    // Cascading Dropdown: Kategori PTK → Jenis PTK
    const jenisPtkOptions = {
        'Pendidik': [
            { value: 'Guru Mapel', text: 'Guru Mata Pelajaran' },
            { value: 'Guru BK', text: 'Guru BK (Bimbingan Konseling)' }
        ],
        'Tenaga Kependidikan': [
            { value: 'Kepala TU', text: 'Kepala Tata Usaha' },
            { value: 'Staff TU', text: 'Staff Tata Usaha' },
            { value: 'Bendahara', text: 'Bendahara' },
            { value: 'Laboran', text: 'Laboran' },
            { value: 'Pustakawan', text: 'Pustakawan' },
            { value: 'Cleaning Service', text: 'Cleaning Service' },
            { value: 'Satpam', text: 'Satpam' },
            { value: 'Lainnya', text: 'Lainnya' }
        ]
    };

    $('#kategori_ptk').on('change', function() {
        const kategori = $(this).val();
        const jenisPtkSelect = $('#jenis_ptk');
        
        jenisPtkSelect.empty();
        jenisPtkSelect.prop('disabled', true);
        
        if (kategori && jenisPtkOptions[kategori]) {
            jenisPtkSelect.prop('disabled', false);
            jenisPtkSelect.append('<option value="">Pilih Jenis PTK</option>');
            
            jenisPtkOptions[kategori].forEach(function(option) {
                jenisPtkSelect.append(`<option value="${option.value}">${option.text}</option>`);
            });
        } else {
            jenisPtkSelect.append('<option value="">Pilih Kategori PTK terlebih dahulu</option>');
        }
    });
});

// Show GTK Detail
function showGtk(id) {
    $('#viewGtkModal').modal('show');
    $('#viewGtkContent').html('<div class="text-center"><i class="fas fa-spinner fa-spin fa-3x"></i><p>Loading...</p></div>');
    
    $.ajax({
        url: '/admin/gtk/' + id,
        type: 'GET',
        success: function(response) {
            const gtk = response.data;
            let html = `
                <div class="row">
                    <div class="col-md-6">
                        <h5 class="border-bottom pb-2">Data Pribadi</h5>
                        <table class="table table-sm">
                            <tr><th width="150">Nama Lengkap</th><td>${gtk.nama_lengkap}</td></tr>
                            <tr><th>NIK</th><td>${gtk.nik}</td></tr>
                            <tr><th>NUPTK</th><td>${gtk.nuptk || '-'}</td></tr>
                            <tr><th>NIP</th><td>${gtk.nip || '-'}</td></tr>
                            <tr><th>Jenis Kelamin</th><td>${gtk.jenis_kelamin === 'L' ? 'Laki-laki' : 'Perempuan'}</td></tr>
                            <tr><th>Tempat, Tgl Lahir</th><td>${gtk.tempat_lahir || '-'}, ${gtk.tanggal_lahir || '-'}</td></tr>
                            <tr><th>Email</th><td>${gtk.email || '-'}</td></tr>
                            <tr><th>No HP</th><td>${gtk.nomor_hp || '-'}</td></tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <h5 class="border-bottom pb-2">Data Kepegawaian</h5>
                        <table class="table table-sm">
                            <tr><th width="150">Kategori PTK</th><td>${gtk.kategori_ptk ? '<span class="badge badge-' + (gtk.kategori_ptk === 'Pendidik' ? 'primary' : 'info') + '">' + gtk.kategori_ptk + '</span>' : '-'}</td></tr>
                            <tr><th>Jenis PTK</th><td>${gtk.jenis_ptk || '-'}</td></tr>
                            <tr><th>Status Kepegawaian</th><td>${gtk.status_kepegawaian || '-'}</td></tr>
                            <tr><th>Jabatan</th><td>${gtk.jabatan || '-'}</td></tr>
                            <tr><th>TMT Kerja</th><td>${gtk.tmt_kerja || '-'}</td></tr>
                        </table>
                        
                        <h5 class="border-bottom pb-2 mt-3">Alamat</h5>
                        <table class="table table-sm">
                            <tr><th width="150">Alamat</th><td>${gtk.alamat || '-'}</td></tr>
                            <tr><th>RT/RW</th><td>${gtk.rt || '-'} / ${gtk.rw || '-'}</td></tr>
                            <tr><th>Kelurahan</th><td>${gtk.kelurahan?.name || '-'}</td></tr>
                            <tr><th>Kecamatan</th><td>${gtk.kecamatan?.name || '-'}</td></tr>
                            <tr><th>Kabupaten</th><td>${gtk.kabupaten?.name || '-'}</td></tr>
                            <tr><th>Provinsi</th><td>${gtk.provinsi?.name || '-'}</td></tr>
                            <tr><th>Kode Pos</th><td>${gtk.kodepos || '-'}</td></tr>
                        </table>
                    </div>
                </div>
            `;
            $('#viewGtkContent').html(html);
        },
        error: function() {
            $('#viewGtkContent').html('<div class="alert alert-danger">Gagal memuat data</div>');
        }
    });
}

// Edit GTK
function editGtk(id) {
    $('#editGtkModal').modal('show');
    $('#edit_gtk_id').val(id);
    $('#editGtkContent').html('<div class="text-center"><i class="fas fa-spinner fa-spin fa-3x"></i><p>Loading...</p></div>');
    
    $.ajax({
        url: '/admin/gtk/' + id,
        type: 'GET',
        success: function(response) {
            const gtk = response.data;
            let html = `
                <div class="row">
                    <div class="col-md-6">
                        <h5 class="border-bottom pb-2">Data Pribadi</h5>
                        <div class="form-group">
                            <label>Nama Lengkap <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="nama_lengkap" value="${gtk.nama_lengkap}" required>
                        </div>
                        <div class="form-group">
                            <label>NIK <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="nik" value="${gtk.nik}" maxlength="16" required>
                        </div>
                        <div class="form-group">
                            <label>NUPTK</label>
                            <input type="text" class="form-control" name="nuptk" value="${gtk.nuptk || ''}" maxlength="16">
                        </div>
                        <div class="form-group">
                            <label>NIP</label>
                            <input type="text" class="form-control" name="nip" value="${gtk.nip || ''}" maxlength="18">
                        </div>
                        <div class="form-group">
                            <label>Jenis Kelamin <span class="text-danger">*</span></label>
                            <select class="form-control" name="jenis_kelamin" required>
                                <option value="L" ${gtk.jenis_kelamin === 'L' ? 'selected' : ''}>Laki-laki</option>
                                <option value="P" ${gtk.jenis_kelamin === 'P' ? 'selected' : ''}>Perempuan</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Tempat Lahir</label>
                            <input type="text" class="form-control" name="tempat_lahir" value="${gtk.tempat_lahir || ''}">
                        </div>
                        <div class="form-group">
                            <label>Tanggal Lahir</label>
                            <input type="date" class="form-control" name="tanggal_lahir" value="${gtk.tanggal_lahir || ''}">
                        </div>
                        <div class="form-group">
                            <label>Email</label>
                            <input type="email" class="form-control" name="email" value="${gtk.email || ''}">
                        </div>
                        <div class="form-group">
                            <label>No HP</label>
                            <input type="text" class="form-control" name="nomor_hp" value="${gtk.nomor_hp || ''}">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <h5 class="border-bottom pb-2">Data Kepegawaian</h5>
                        <div class="form-group">
                            <label>Status Kepegawaian</label>
                            <select class="form-control" name="status_kepegawaian">
                                <option value="">Pilih Status</option>
                                <option value="PNS" ${gtk.status_kepegawaian === 'PNS' ? 'selected' : ''}>PNS</option>
                                <option value="PPPK" ${gtk.status_kepegawaian === 'PPPK' ? 'selected' : ''}>PPPK</option>
                                <option value="GTY" ${gtk.status_kepegawaian === 'GTY' ? 'selected' : ''}>GTY (Guru Tetap Yayasan)</option>
                                <option value="PTY" ${gtk.status_kepegawaian === 'PTY' ? 'selected' : ''}>PTY (Pegawai Tetap Yayasan)</option>
                                <option value="Honorer" ${gtk.status_kepegawaian === 'Honorer' ? 'selected' : ''}>Honorer</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Kategori PTK <span class="text-danger">*</span></label>
                            <select class="form-control" id="edit_kategori_ptk" name="kategori_ptk" required>
                                <option value="">Pilih Kategori PTK</option>
                                <option value="Pendidik" ${gtk.kategori_ptk === 'Pendidik' ? 'selected' : ''}>Pendidik (Guru)</option>
                                <option value="Tenaga Kependidikan" ${gtk.kategori_ptk === 'Tenaga Kependidikan' ? 'selected' : ''}>Tenaga Kependidikan (Staff TU, dll)</option>
                            </select>
                            <small class="form-text text-muted">Kategori PTK: Pendidik untuk Guru, Tenaga Kependidikan untuk Staff non-Guru</small>
                        </div>
                        <div class="form-group">
                            <label>Jenis PTK <span class="text-danger">*</span></label>
                            <select class="form-control" id="edit_jenis_ptk" name="jenis_ptk" required>
                                <option value="">Pilih Kategori PTK terlebih dahulu</option>
                            </select>
                            <small class="form-text text-muted">Jenis PTK akan muncul setelah memilih Kategori PTK</small>
                        </div>
                        <div class="form-group">
                            <label>Jabatan</label>
                            <input type="text" class="form-control" name="jabatan" value="${gtk.jabatan || ''}">
                        </div>
                        <div class="form-group">
                            <label>TMT Kerja</label>
                            <input type="date" class="form-control" name="tmt_kerja" value="${gtk.tmt_kerja || ''}">
                        </div>
                        
                        <h5 class="border-bottom pb-2 mt-3">Alamat</h5>
                        <div class="form-group">
                            <label>Alamat Lengkap</label>
                            <textarea class="form-control" name="alamat" rows="3">${gtk.alamat || ''}</textarea>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>RT</label>
                                    <input type="text" class="form-control" name="rt" value="${gtk.rt || ''}" maxlength="3">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>RW</label>
                                    <input type="text" class="form-control" name="rw" value="${gtk.rw || ''}" maxlength="3">
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Kode Pos</label>
                            <input type="text" class="form-control" name="kodepos" value="${gtk.kodepos || ''}" maxlength="5">
                        </div>
                    </div>
                </div>
            `;
            $('#editGtkContent').html(html);
            
            // Populate jenis_ptk dropdown based on current kategori_ptk
            const editJenisPtkOptions = {
                'Pendidik': [
                    { value: 'Guru Mapel', text: 'Guru Mata Pelajaran' },
                    { value: 'Guru BK', text: 'Guru BK (Bimbingan Konseling)' }
                ],
                'Tenaga Kependidikan': [
                    { value: 'Kepala TU', text: 'Kepala Tata Usaha' },
                    { value: 'Staff TU', text: 'Staff Tata Usaha' },
                    { value: 'Bendahara', text: 'Bendahara' },
                    { value: 'Laboran', text: 'Laboran' },
                    { value: 'Pustakawan', text: 'Pustakawan' },
                    { value: 'Cleaning Service', text: 'Cleaning Service' },
                    { value: 'Satpam', text: 'Satpam' },
                    { value: 'Lainnya', text: 'Lainnya' }
                ]
            };
            
            // Populate jenis_ptk based on current kategori_ptk value
            const currentKategori = gtk.kategori_ptk;
            const currentJenis = gtk.jenis_ptk;
            const editJenisPtkSelect = $('#edit_jenis_ptk');
            
            if (currentKategori && editJenisPtkOptions[currentKategori]) {
                editJenisPtkSelect.empty();
                editJenisPtkSelect.append('<option value="">Pilih Jenis PTK</option>');
                editJenisPtkOptions[currentKategori].forEach(function(option) {
                    const selected = option.value === currentJenis ? 'selected' : '';
                    editJenisPtkSelect.append(`<option value="${option.value}" ${selected}>${option.text}</option>`);
                });
            }
            
            // Cascading dropdown for edit form
            $('#edit_kategori_ptk').on('change', function() {
                const kategori = $(this).val();
                const jenisPtkSelect = $('#edit_jenis_ptk');
                jenisPtkSelect.empty().prop('disabled', true);
                
                if (kategori && editJenisPtkOptions[kategori]) {
                    jenisPtkSelect.prop('disabled', false);
                    jenisPtkSelect.append('<option value="">Pilih Jenis PTK</option>');
                    editJenisPtkOptions[kategori].forEach(function(option) {
                        jenisPtkSelect.append(`<option value="${option.value}">${option.text}</option>`);
                    });
                } else {
                    jenisPtkSelect.append('<option value="">Pilih Kategori PTK terlebih dahulu</option>');
                }
            });
        },
        error: function() {
            $('#editGtkContent').html('<div class="alert alert-danger">Gagal memuat data</div>');
        }
    });
}

// Edit GTK Form Submit
$(document).on('submit', '#editGtkForm', function(e) {
    e.preventDefault();
    const gtkId = $('#edit_gtk_id').val();
    
    $.ajax({
        url: '/admin/gtk/' + gtkId,
        type: 'PUT',
        data: $(this).serialize(),
        success: function(response) {
            $('#editGtkModal').modal('hide');
            
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: response.message,
                timer: 2000
            });
            
            $('#gtk-table').DataTable().ajax.reload();
        },
        error: function(xhr) {
            Swal.fire({
                icon: 'error',
                title: 'Gagal!',
                text: xhr.responseJSON?.message || 'Terjadi kesalahan'
            });
        }
    });
});

// Reset Password
function resetPassword(id) {
    Swal.fire({
        title: 'Reset Password?',
        text: 'Password akan direset menjadi NIK',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Ya, Reset!',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: '/admin/gtk/' + id + '/reset-password',
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: response.message,
                        timer: 2000
                    });
                },
                error: function(xhr) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal!',
                        text: xhr.responseJSON?.message || 'Terjadi kesalahan'
                    });
                }
            });
        }
    });
}

// Delete GTK
function deleteGtk(id) {
    Swal.fire({
        title: 'Hapus GTK?',
        text: 'Data GTK dan akun user akan dihapus permanen!',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Ya, Hapus!',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: '/admin/gtk/' + id,
                type: 'DELETE',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: response.message,
                        timer: 2000
                    });
                    
                    $('#gtk-table').DataTable().ajax.reload();
                },
                error: function(xhr) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal!',
                        text: xhr.responseJSON?.message || 'Terjadi kesalahan'
                    });
                }
            });
        }
    });
}
</script>
@stop
