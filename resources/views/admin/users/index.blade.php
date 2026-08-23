@extends('adminlte::page')

@section('title', 'Data User - SIMANSA')

@section('content_header')
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1><i class="fas fa-users-cog text-primary mr-1"></i> User & Role</h1>
            {{--
                Kelola semua akun pengguna SIMANSA — admin, GTK, dan siswa — beserta role dan permission dari satu halaman.
            --}}
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item active">User & Role</li>
            </ol>
        </div>
    </div>
@stop

@section('content')
<div class="simansa-users-page">
<div class="card bg-gradient-primary text-white mb-3 simansa-users-operation-hero">
    <div class="card-body">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <h3 class="mb-1"><i class="fas fa-user-shield mr-1"></i> Manajemen User & Role</h3>
                <p class="mb-0 text-white-75">Kelola akun sistem, GTK aktif, dan siswa aktif beserta role, status akses, serta aktivitasnya dari satu halaman operasional.</p>
            </div>
            <div class="col-lg-4 mt-3 mt-lg-0">
                <div class="row text-center">
                    <div class="col-6"><div class="text-white-75 small text-uppercase font-weight-bold">Akun Aktif</div><h3 class="mb-0 text-white">{{ number_format($stats['active']) }}</h3></div>
                    <div class="col-6"><div class="text-white-75 small text-uppercase font-weight-bold">Nonaktif</div><h3 class="mb-0 text-white">{{ number_format($stats['inactive']) }}</h3></div>
                </div>
            </div>
        </div>
    </div>
</div>
{{-- Stat Cards --}}
<div class="row simansa-users-stats mb-2">
    <div class="col-6 col-xl-3 mb-2">
        <div class="simansa-stat-card simansa-stat-card--blue">
            <div class="simansa-stat-card__icon"><i class="fas fa-users"></i></div>
            <div class="simansa-stat-card__body">
                <div class="simansa-stat-card__label">Total User</div>
                <div class="simansa-stat-card__value">{{ number_format($stats['total_users']) }}</div>
                <div class="simansa-stat-card__desc">Akun operasional; histori alumni dan mutasi tidak dimuat.</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-xl-3 mb-2">
        <div class="simansa-stat-card simansa-stat-card--rose">
            <div class="simansa-stat-card__icon"><i class="fas fa-user-shield"></i></div>
            <div class="simansa-stat-card__body">
                <div class="simansa-stat-card__label">Akun Aktif</div>
                <div class="simansa-stat-card__value">{{ number_format($stats['active']) }}</div>
                <div class="simansa-stat-card__desc">{{ number_format($stats['inactive']) }} akun nonaktif · {{ number_format($stats['admin']) }} administrator.</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-xl-3 mb-2">
        <div class="simansa-stat-card simansa-stat-card--teal">
            <div class="simansa-stat-card__icon"><i class="fas fa-chalkboard-teacher"></i></div>
            <div class="simansa-stat-card__body">
                <div class="simansa-stat-card__label">GTK</div>
                <div class="simansa-stat-card__value">{{ number_format($stats['gtk']) }}</div>
                <div class="simansa-stat-card__desc">Guru dan tenaga kependidikan yang aktif.</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-xl-3 mb-2">
        <div class="simansa-stat-card simansa-stat-card--green">
            <div class="simansa-stat-card__icon"><i class="fas fa-user-graduate"></i></div>
            <div class="simansa-stat-card__body">
                <div class="simansa-stat-card__label">Siswa</div>
                <div class="simansa-stat-card__value">{{ number_format($stats['siswa']) }}</div>
                <div class="simansa-stat-card__desc">Akun siswa aktif; alumni dan mutasi keluar dikecualikan.</div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card simansa-management-card">
            <div class="card-header">
                <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between">
                    <h3 class="card-title mb-3 mb-lg-0">
                        <i class="fas fa-users mr-2"></i>
                        Daftar Akun Pengguna
                    </h3>
                    <div class="card-tools ml-0">
                    @can('view-permission')
                    <a href="{{ route('admin.users.permission-matrix') }}" class="btn btn-sm simansa-btn-contrast mr-2">
                        <i class="fas fa-shield-alt"></i> Permission Matrix
                    </a>
                    @endcan
                    @can('create-users')
                    <a href="{{ route('admin.users.create') }}" class="btn btn-sm simansa-btn-strong">
                        <i class="fas fa-plus"></i> Tambah User
                    </a>
                    @endcan
                    </div>
                </div>
            </div>
            <div class="card-body">
                {{-- Filter Section --}}
                <div class="simansa-filter-panel simansa-user-filter mb-3">
                    <form id="filterForm">
                        <div class="form-row align-items-end">
                            <div class="col-md-4 col-xl-3 mb-2 mb-md-0">
                                <label class="simansa-filter-label"><i class="fas fa-user-tag mr-1"></i> Role</label>
                                <select id="filterRole" class="custom-select custom-select-sm">
                                    <option value="">Semua Role</option>
                                    @foreach($roles as $role)
                                        <option value="{{ $role->name }}">{{ $role->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4 col-xl-3 mb-2 mb-md-0">
                                <label class="simansa-filter-label"><i class="fas fa-users mr-1"></i> Tipe Akun</label>
                                <select id="filterAccountType" class="custom-select custom-select-sm">
                                    <option value="">Semua Tipe</option>
                                    <option value="gtk">GTK</option>
                                    <option value="siswa">Siswa</option>
                                    <option value="lainnya">Admin / Akun Lainnya</option>
                                </select>
                            </div>
                            <div class="col-md-auto ml-md-auto">
                                <button type="button" id="btnResetFilter" class="btn btn-sm btn-light border simansa-reset-filter">
                                    <i class="fas fa-undo-alt mr-1"></i> Reset Filter
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
                
                <div class="simansa-users-table-wrap">
                    <table id="usersTable" class="table table-hover" style="width: 100%">
                        <thead class="bg-light">
                            <tr>
                                <th style="width: 40px; text-align: center;">No</th>
                                <th>Akun</th>
                                <th>Kontak</th>
                                <th>Akses</th>
                                <th>Status & Aktivitas</th>
                                <th style="width: 90px; text-align: center;">Aksi</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
</div>

<!-- Modal Assign Role -->
<div class="modal fade" id="assignRoleModal" tabindex="-1" aria-labelledby="assignRoleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title" id="assignRoleModalLabel">
                    <i class="fas fa-user-tag"></i> Assign Role & Permission
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="assignRoleForm" method="POST">
                @csrf
                <div class="modal-body">
                    <input type="hidden" id="user_id" name="user_id">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> 
                        <strong>User:</strong> <span id="userName"></span>
                    </div>

                    <!-- Nav Tabs -->
                    <ul class="nav nav-tabs" id="assignRoleTabs" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" id="roles-tab" data-toggle="tab" href="#rolesTab" role="tab">
                                <i class="fas fa-user-shield"></i> Roles
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="permissions-tab" data-toggle="tab" href="#permissionsTab" role="tab">
                                <i class="fas fa-key"></i> Akses Khusus User
                            </a>
                        </li>
                        <li class="nav-item" id="tugasTambahanTab" style="display: none;">
                            <a class="nav-link" id="tugas-tab" data-toggle="tab" href="#tugasTab" role="tab">
                                <i class="fas fa-briefcase"></i> Tugas Tambahan
                                <span class="badge badge-warning">GTK</span>
                            </a>
                        </li>
                    </ul>

                    <!-- Tab Content -->
                    <div class="tab-content mt-3" id="assignRoleTabContent">
                        <!-- Roles Tab -->
                        <div class="tab-pane fade show active" id="rolesTab" role="tabpanel">
                            <div class="form-group">
                                <label>Pilih Roles <span class="text-muted">(bisa pilih lebih dari satu)</span></label>
                                <div id="rolesCheckboxes" class="border rounded p-3 bg-light">
                                    <!-- Roles akan dimuat via AJAX -->
                                </div>
                            </div>
                        </div>

                        <!-- Permissions Tab -->
                        <div class="tab-pane fade" id="permissionsTab" role="tabpanel">
                            <div class="alert alert-info mb-3">
                                <i class="fas fa-user-shield mr-1"></i>
                                <strong>Akses khusus hanya untuk user terpilih.</strong> Role asal tidak diubah, sehingga BK lain tidak ikut menerima permission ini.
                                <ul class="mb-0 mt-2">
                                    <li><strong>Dari role</strong> ditandai badge dan tidak dapat diubah dari sini.</li>
                                    <li><strong>Checklist aktif</strong> adalah permission langsung yang dapat ditambah atau dicabut hanya dari akun ini.</li>
                                    <li>Untuk seluruh Data Siswa dan Kelas, pilih <code>view-siswa</code>, <code>view-kelas</code>, dan <code>access-global-siswa-kelas</code>.</li>
                                    <li>Tambahkan <code>reset-password-siswa</code> bila user juga boleh mereset password siswa.</li>
                                </ul>
                            </div>
                            <div class="form-group">
                                <label>Akses Khusus <span class="text-muted">(tambahan di luar role, hanya untuk user ini)</span></label>
                                <div class="accordion" id="permissionsAccordion">
                                    <!-- Permissions akan dimuat via AJAX -->
                                </div>
                            </div>
                        </div>

                        <!-- Tugas Tambahan Tab (Only for GTK) -->
                        <div class="tab-pane fade" id="tugasTab" role="tabpanel">
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle"></i> 
                                Tugas tambahan adalah role tambahan yang diberikan kepada GTK seperti Wali Kelas, BK, Admin, Operator, dll.
                            </div>

                            <!-- Button to add new tugas tambahan -->
                            <div class="mb-3">
                                <button type="button" class="btn btn-primary" id="btnOpenAddTugasTambahan">
                                    <i class="fas fa-plus-circle"></i> Tambah Tugas Tambahan Baru
                                </button>
                            </div>

                            <!-- List Tugas Tambahan -->
                            <div class="card border-secondary">
                                <div class="card-header bg-secondary text-white">
                                    <i class="fas fa-list"></i> Daftar Tugas Tambahan
                                </div>
                                <div class="card-body p-0">
                                    <div id="tugasTambahanList" class="table-responsive">
                                        <!-- Will be populated via AJAX -->
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times"></i> Batal
                    </button>
                    <button type="submit" class="btn btn-warning">
                        <i class="fas fa-save"></i> Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Add Tugas Tambahan -->
<div class="modal fade" id="addTugasTambahanModal" tabindex="-1" aria-labelledby="addTugasTambahanModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="addTugasTambahanModalLabel">
                    <i class="fas fa-plus-circle"></i> Tambah Tugas Tambahan
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="addTugasTambahanForm">
                <div class="modal-body">
                    <input type="hidden" id="tugas_user_id" name="user_id">
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Role Tugas Tambahan <span class="text-danger">*</span></label>
                                <select class="form-control" id="tugasTambahanRoleSelect" name="role_id" required>
                                    <option value="">Pilih Role</option>
                                </select>
                                <small class="form-text text-muted">Pilih role tambahan yang akan diberikan</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Mulai Tugas <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" name="mulai_tugas" required>
                                <small class="form-text text-muted">Tanggal mulai bertugas</small>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Nomor SK <small class="text-muted">(opsional)</small></label>
                                <input type="text" class="form-control" name="sk_number" placeholder="Contoh: 001/SK/2025">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Tanggal SK <small class="text-muted">(opsional)</small></label>
                                <input type="date" class="form-control" name="sk_date">
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Keterangan <small class="text-muted">(opsional)</small></label>
                        <textarea class="form-control" name="keterangan" rows="2" placeholder="Keterangan tambahan"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times"></i> Batal
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Simpan Tugas Tambahan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@stop

@section('css')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap4.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap4.min.css">
<style>
    .simansa-users-operation-hero{overflow:hidden;border:0;border-radius:12px;box-shadow:0 8px 20px rgba(37,99,235,.16)}.simansa-users-operation-hero .card-body{padding:1rem 1.2rem}.simansa-users-operation-hero h3,.simansa-users-operation-hero p{color:#fff!important}.simansa-users-operation-hero p{font-size:.78rem}.simansa-users-page .simansa-stat-card{min-height:88px;padding:.75rem}.simansa-users-page .simansa-stat-card__icon{width:38px;height:38px;font-size:1rem}.simansa-users-page .simansa-stat-card__value{font-size:1.3rem}.simansa-users-page .simansa-stat-card__desc{display:none}.simansa-users-page .simansa-management-card .card-body{padding:.85rem}.simansa-user-filter{padding:.7rem .8rem;background:#f8fafc}.simansa-user-filter label{margin-bottom:.25rem;font-size:.72rem}.simansa-user-filter .custom-select{height:34px;border-color:#cbd5e1;border-radius:7px;background-color:#fff;color:#334155;font-size:.76rem;font-weight:600}.simansa-user-filter .custom-select:focus{border-color:#60a5fa;box-shadow:0 0 0 .15rem rgba(37,99,235,.12)}.simansa-user-filter .simansa-reset-filter{height:34px;padding:.35rem .75rem;border-color:#cbd5e1!important;border-radius:7px;background:#fff;color:#475569;font-size:.74rem;font-weight:700;white-space:nowrap}.simansa-user-filter .simansa-reset-filter:hover{border-color:#94a3b8!important;background:#f1f5f9;color:#1e293b}
    .simansa-users-table-wrap{position:relative;overflow:visible}.simansa-users-page #usersTable{margin:0!important;font-size:.78rem}.simansa-users-page #usersTable thead th{padding:.58rem .65rem;border-top:1px solid #e2e8f0;border-bottom:1px solid #e2e8f0;background:#f8fafc;color:#64748b;font-size:.66rem;letter-spacing:.04em;text-transform:uppercase}.simansa-users-page #usersTable tbody td{padding:.58rem .65rem;border-top:0;border-bottom:1px solid #edf2f7;vertical-align:middle}.simansa-user-identity{display:flex;align-items:center;gap:.65rem;min-width:230px}.simansa-user-avatar{position:relative;display:grid;width:42px;height:50px;flex:0 0 42px;overflow:hidden;place-items:center;border:2px solid #fff;border-radius:9px;background:linear-gradient(145deg,#4f46e5,#2563eb);box-shadow:0 0 0 1px #dbeafe;color:#fff;font-size:.72rem;font-weight:900}.simansa-user-avatar b{font:inherit}.simansa-user-avatar img{position:absolute;inset:0;width:100%;height:100%;object-fit:cover;background:#eef2ff}.simansa-user-identity strong,.simansa-user-identity span,.simansa-user-identity small{display:block}.simansa-user-identity strong{color:#0f172a;font-size:.8rem}.simansa-user-identity span{color:#475569;font-size:.69rem}.simansa-user-identity span i{margin-right:.25rem;color:#94a3b8}.simansa-user-identity small{color:#64748b;font-size:.62rem}.simansa-user-contact{display:flex;min-width:190px;flex-direction:column;gap:.25rem}.simansa-user-contact span{max-width:230px;overflow:hidden;color:#475569;font-size:.69rem;text-overflow:ellipsis;white-space:nowrap}.simansa-user-contact i{width:16px;color:#94a3b8}.simansa-users-page .roles-badges{max-width:220px}.simansa-user-status{display:grid;grid-template-columns:auto auto;align-items:center;justify-content:start;gap:.25rem .6rem;min-width:160px}.simansa-user-status small{grid-column:1/-1;color:#64748b;font-size:.62rem}.simansa-user-presence{display:inline-flex;align-items:center;gap:.3rem;font-size:.68rem;font-weight:800}.simansa-user-presence i{width:7px;height:7px;border-radius:50%}.simansa-user-presence.is-online{color:#047857}.simansa-user-presence.is-online i{background:#22c55e}.simansa-user-presence.is-offline{color:#64748b}.simansa-user-presence.is-offline i{background:#94a3b8}.simansa-user-status .custom-switch{min-height:1rem;padding-left:2.25rem}.simansa-user-actions .btn{font-size:.7rem;font-weight:800}.simansa-user-actions .dropdown-menu{min-width:190px;padding:.35rem;z-index:1070}.simansa-user-actions .dropdown-item{display:flex;align-items:center;gap:.55rem;padding:.45rem .55rem;border-radius:6px;font-size:.72rem;font-weight:700}.simansa-user-actions .dropdown-item i{width:16px;text-align:center}.simansa-users-page .dataTables_wrapper>.row{margin-bottom:.35rem}.simansa-users-page .dataTables_length,.simansa-users-page .dataTables_filter{margin-bottom:.35rem}.simansa-users-page .dataTables_filter input{width:210px!important}
    @media(max-width:767.98px){.simansa-users-operation-hero .card-body{padding:.9rem}.simansa-users-page .simansa-stat-card{min-height:76px}.simansa-user-filter .simansa-reset-filter{width:100%}.simansa-users-table-wrap{overflow-x:auto}.simansa-users-page #usersTable{min-width:850px}.simansa-users-page .dataTables_filter input{width:auto!important}}
    .custom-control-label {
        cursor: pointer;
    }
    .badge {
        font-size: 85%;
        margin: 2px;
        display: inline-block;
    }
    .badge-lg {
        font-size: 90%;
        padding: 0.35rem 0.6rem;
    }
    .accordion .card {
        margin-bottom: 5px;
    }
    
    /* Tab styling */
    .nav-tabs .nav-link {
        color: #495057;
    }
    .nav-tabs .nav-link.active {
        color: #007bff;
        font-weight: 600;
    }
    .nav-tabs .badge {
        font-size: 75%;
        vertical-align: middle;
    }
    
    /* Tugas Tambahan Table */
    #tugasTambahanList table th {
        font-weight: 600;
        font-size: 0.85rem;
    }
    #tugasTambahanList table td {
        font-size: 0.85rem;
        vertical-align: middle;
    }
    #tugasTambahanList .btn-group {
        display: flex;
        gap: 2px;
    }
    
    /* DataTables styling */
    #usersTable {
        font-size: 0.9rem;
    }
    
    #usersTable thead th {
        vertical-align: middle;
        white-space: nowrap;
    }
    
    #usersTable tbody td {
        vertical-align: middle;
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

    /* Kolom aksi */
    #usersTable .btn-group {
        display: flex;
        flex-wrap: nowrap;
        gap: 2px;
    }
    
    #usersTable .btn-sm {
        padding: 0.25rem 0.4rem;
        font-size: 0.8rem;
    }
    
    /* Toggle switch */
    .custom-switch {
        padding-left: 2.25rem;
    }
    
    /* Responsive table wrapper */
    .table-responsive {
        overflow-x: auto;
    }
    
    /* Badge container */
    .roles-badges {
        display: flex;
        flex-wrap: wrap;
        gap: 3px;
    }
    
    /* Fix DataTables length selector layout */
    .dataTables_length {
        margin-bottom: 15px;
    }
    
    .dataTables_length label {
        display: flex;
        align-items: center;
        gap: 8px;
    }
    
    .dataTables_length select {
        width: auto !important;
        margin: 0 5px;
    }
    
    /* Improve DataTables wrapper spacing */
    .dataTables_wrapper .row {
        margin-bottom: 10px;
    }
    
    .dataTables_wrapper .row:first-child {
        margin-bottom: 15px;
    }
</style>
@stop

@section('js')
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap4.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap4.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
$(document).ready(function() {
    // Debug log
    console.log('Initializing DataTable...');
    console.log('AJAX URL:', '{{ route("admin.users.index") }}');
    
    // DataTable
    const table = $('#usersTable').DataTable({
        processing: true,
        serverSide: true,
        responsive: false,
        autoWidth: false,
        lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "Semua"]],
        pageLength: 10,
        ajax: {
            url: '{{ route("admin.users.data") }}',
            type: 'GET',
            error: function(xhr, error, code) {
                console.error('DataTables AJAX Error:', error);
                console.error('Status:', xhr.status);
                console.error('Response:', xhr.responseText);
                alert('Error loading data: ' + error + '\nStatus: ' + xhr.status);
            }
        },
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, width: '40px' },
            { data: 'identity', name: 'name' },
            { data: 'contact', name: 'email' },
            { data: 'roles', name: 'roles', orderable: false, searchable: false },
            { data: 'status', name: 'is_active', orderable: false },
            { data: 'action', name: 'action', orderable: false, searchable: false, width: '90px' }
        ],
        columnDefs: [
            { 
                targets: [0, 4],
                className: 'text-center'
            },
            {
                targets: 3,
                render: function(data) {
                    return '<div style="white-space: normal; word-wrap: break-word;">' + data + '</div>';
                }
            }
        ],
        order: [[1, 'asc']],  // Default sorting by name
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
        },
        drawCallback: function() {
            const $rows = $('#usersTable tbody tr');
            $rows.find('.simansa-user-actions').removeClass('dropup');
            $rows.slice(-3).find('.simansa-user-actions').addClass('dropup');
        }
    });

    // Toggle Status
    $('#usersTable').on('change', '.toggle-status', function() {
        const userId = $(this).data('id');
        const isChecked = $(this).is(':checked');
        
        $.ajax({
            url: `/admin/users/${userId}/toggle-status`,
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                if (response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: response.message,
                        timer: 1500,
                        showConfirmButton: false
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal!',
                        text: response.message
                    });
                }
            },
            error: function(xhr) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: 'Terjadi kesalahan saat mengubah status'
                });
            }
        });
    });

    // Assign Role Button
    $('#usersTable').on('click', '.btn-assign-role', function(event) {
        event.preventDefault();

        const $button = $(this);
        const userId = $button.data('id');
        const userName = $button.data('name');
        const formUrl = $button.data('form-url');
        const assignmentUrl = $button.data('assignment-url');

        if (!formUrl || !assignmentUrl) {
            Swal.fire({
                icon: 'error',
                title: 'Aksi tidak tersedia',
                text: 'URL pengaturan akses user tidak ditemukan. Muat ulang halaman lalu coba kembali.'
            });
            return;
        }
        
        $('#user_id').val(userId);
        $('#userName').text(userName);
        $('#assignRoleForm').attr('action', assignmentUrl);
        $button.prop('disabled', true);

        Swal.fire({
            title: 'Memuat akses user…',
            text: `Menyiapkan role dan akses khusus untuk ${userName}.`,
            allowOutsideClick: false,
            allowEscapeKey: false,
            didOpen: () => Swal.showLoading()
        });
        
        // Load roles dan permissions via AJAX
        $.ajax({
            url: formUrl,
            type: 'GET',
            success: function(response) {
                // Populate roles
                let rolesHtml = '';
                response.roles.forEach(role => {
                    const checked = response.userRoles.includes(role.id) ? 'checked' : '';
                    rolesHtml += `
                        <div class="custom-control custom-checkbox">
                            <input class="custom-control-input" type="checkbox" name="roles[]" value="${role.id}" id="role${role.id}" ${checked}>
                            <label class="custom-control-label" for="role${role.id}">
                                ${role.name} <small class="text-muted">(${role.permissions_count} permissions)</small>
                            </label>
                        </div>
                    `;
                });
                $('#rolesCheckboxes').html(rolesHtml);

                // Populate permissions (grouped)
                let permsHtml = '';
                let index = 0;
                for (const [module, permissions] of Object.entries(response.permissions)) {
                    const collapseId = `collapse${index}`;
                    permsHtml += `
                        <div class="card">
                            <div class="card-header p-2" id="heading${index}">
                                <h5 class="mb-0">
                                    <button class="btn btn-link btn-block text-left" type="button" data-toggle="collapse" data-target="#${collapseId}">
                                        <i class="fas fa-cube"></i> ${module.toUpperCase()}
                                    </button>
                                </h5>
                            </div>
                            <div id="${collapseId}" class="collapse" data-parent="#permissionsAccordion">
                                <div class="card-body">
                    `;
                    
                    permissions.forEach(perm => {
                        // Check if user has this permission (via role OR direct)
                        const hasPermission = response.userAllPermissions.includes(perm.id);
                        // Check if it's a DIRECT permission (editable)
                        const isDirectPermission = response.userPermissions.includes(perm.id);
                        // Permission is inherited from role (readonly)
                        const isInherited = hasPermission && !isDirectPermission;
                        
                        // Find which roles grant this permission (for tooltip)
                        let inheritedFromRoles = [];
                        if (isInherited) {
                            response.roles.forEach(role => {
                                // Check if user has this role AND role has this permission
                                if (response.userRoles.includes(role.id)) {
                                    // Check if role has this permission (simple check: role.permissions_count > 0 means it might have it)
                                    // We'll show all user's roles in tooltip for simplicity
                                    inheritedFromRoles.push(role.name);
                                }
                            });
                        }
                        
                        const checked = hasPermission ? 'checked' : '';
                        const disabled = isInherited ? 'disabled' : '';
                        const inheritedClass = isInherited ? 'text-muted' : '';
                        const inheritedBadge = isInherited ? '<small class="badge badge-info ml-1">dari role</small>' : '';
                        const tooltipTitle = isInherited ? `Permission ini didapat dari role: ${inheritedFromRoles.join(', ')}` : '';
                        const tooltipAttr = isInherited ? `data-toggle="tooltip" data-placement="top" title="${tooltipTitle}"` : '';
                        
                        permsHtml += `
                            <div class="custom-control custom-checkbox" ${tooltipAttr}>
                                <input class="custom-control-input" type="checkbox" name="permissions[]" value="${perm.id}" id="perm${perm.id}" ${checked} ${disabled}>
                                <label class="custom-control-label ${inheritedClass}" for="perm${perm.id}">
                                    ${perm.name} ${inheritedBadge}
                                </label>
                            </div>
                        `;
                    });
                    
                    permsHtml += `
                                </div>
                            </div>
                        </div>
                    `;
                    index++;
                }
                $('#permissionsAccordion').html(permsHtml);
                
                // Check if user has GTK role - show tugas tambahan tab
                const hasGtkRole = response.userRoles.some(roleId => {
                    const role = response.roles.find(r => r.id === roleId);
                    return role && role.name === 'GTK';
                });

                if (hasGtkRole) {
                    $('#tugasTambahanTab').show();
                    
                    // Build options HTML for tugas tambahan roles
                    let tugasRolesHtml = '<option value="">Pilih Role</option>';
                    
                    if (response.tugasTambahanRoles && response.tugasTambahanRoles.length > 0) {
                        response.tugasTambahanRoles.forEach(role => {
                            tugasRolesHtml += `<option value="${role.id}">${role.name}</option>`;
                        });
                    } else {
                        tugasRolesHtml += '<option value="" disabled>Tidak ada role tersedia</option>';
                    }
                    
                    // Store options and user ID globally for modal
                    window.tugasRolesOptionsHtml = tugasRolesHtml;
                    window.currentTugasUserId = userId;
                    
                    // Display existing tugas tambahan
                    renderTugasTambahanList(response.tugasTambahan || []);
                } else {
                    $('#tugasTambahanTab').hide();
                }
                
                // Reset to first tab
                $('#assignRoleTabs a[href="#rolesTab"]').tab('show');
                
                // Show modal
                Swal.close();
                $('#assignRoleModal').modal('show');
                
                // Initialize Bootstrap tooltips for inherited permissions
                $('[data-toggle="tooltip"]').tooltip();
            },
            error: function(xhr) {
                Swal.close();
                const message = xhr.status === 403
                    ? 'Akun Anda tidak memiliki izin untuk mengatur role dan akses user.'
                    : (xhr.responseJSON?.message || 'Gagal memuat data role dan permission. Silakan coba kembali.');

                Swal.fire({
                    icon: 'error',
                    title: 'Akses user tidak dapat dibuka',
                    text: message
                });
            },
            complete: function() {
                $button.prop('disabled', false);
            }
        });
    });

    // Populate Tugas Tambahan dropdown when modal is fully shown
    $('#assignRoleModal').on('shown.bs.modal', function() {
        if (window.tugasRolesOptionsHtml) {
            const roleSelect = $('#tugasTambahanRoleSelect');
            if (roleSelect.length > 0) {
                roleSelect.html(window.tugasRolesOptionsHtml);
            }
        }
    });

    // Clean up when modal is hidden
    $('#assignRoleModal').on('hidden.bs.modal', function() {
        window.tugasRolesOptionsHtml = null;
        window.currentTugasUserId = null;
    });
    
    // Clean up when tugas tambahan modal is hidden
    $('#addTugasTambahanModal').on('hidden.bs.modal', function() {
        $('#addTugasTambahanForm')[0].reset();
    });

    // Button to open Add Tugas Tambahan Modal
    $(document).on('click', '#btnOpenAddTugasTambahan', function() {
        // Populate select options
        if (window.tugasRolesOptionsHtml) {
            $('#tugasTambahanRoleSelect').html(window.tugasRolesOptionsHtml);
        }
        
        // Set user ID
        $('#tugas_user_id').val(window.currentTugasUserId);
        
        // Show modal
        $('#addTugasTambahanModal').modal('show');
    });

    // Render Tugas Tambahan List
    function renderTugasTambahanList(tugasTambahan) {
        if (tugasTambahan.length === 0) {
            $('#tugasTambahanList').html('<div class="p-4 text-center text-muted"><i class="fas fa-inbox fa-3x mb-2"></i><p>Belum ada tugas tambahan</p></div>');
            return;
        }

        let html = '<table class="table table-hover table-striped mb-0">';
        html += '<thead class="thead-light"><tr><th width="20%">Role</th><th width="25%">Periode</th><th width="20%">SK</th><th width="15%" class="text-center">Status</th><th width="20%" class="text-center">Aksi</th></tr></thead><tbody>';
        
        tugasTambahan.forEach(tugas => {
            const statusBadge = tugas.is_active 
                ? '<span class="badge badge-success badge-lg"><i class="fas fa-check-circle"></i> Aktif</span>' 
                : '<span class="badge badge-secondary badge-lg"><i class="fas fa-pause-circle"></i> Nonaktif</span>';
            
            const skInfo = tugas.sk_number 
                ? `<strong>${tugas.sk_number}</strong><br><small class="text-muted"><i class="far fa-calendar-alt"></i> ${tugas.sk_date || ''}</small>` 
                : '<span class="text-muted">-</span>';
            
            const mulaiTugas = tugas.mulai_tugas ? new Date(tugas.mulai_tugas).toLocaleDateString('id-ID', {day: '2-digit', month: 'short', year: 'numeric'}) : '-';
            const periode = `<i class="fas fa-calendar-check"></i> ${mulaiTugas}`;
            
            const toggleBtn = tugas.is_active
                ? `<button class="btn btn-sm btn-warning btn-deactivate-tugas" data-id="${tugas.id}" title="Nonaktifkan"><i class="fas fa-pause"></i></button>`
                : `<button class="btn btn-sm btn-success btn-activate-tugas" data-id="${tugas.id}" title="Aktifkan"><i class="fas fa-play"></i></button>`;
            
            const keterangan = tugas.keterangan ? `<br><small class="text-muted"><i class="fas fa-info-circle"></i> ${tugas.keterangan}</small>` : '';
            
            html += `<tr>
                <td><strong class="text-primary">${tugas.role.name}</strong>${keterangan}</td>
                <td>${periode}</td>
                <td>${skInfo}</td>
                <td class="text-center">${statusBadge}</td>
                <td class="text-center">
                    <div class="btn-group" role="group">
                        ${toggleBtn}
                        <button class="btn btn-sm btn-danger btn-delete-tugas" data-id="${tugas.id}" title="Hapus"><i class="fas fa-trash"></i></button>
                    </div>
                </td>
            </tr>`;
        });
        
        html += '</tbody></table>';
        $('#tugasTambahanList').html(html);
    }

    // Submit Assign Role Form
    $('#assignRoleForm').on('submit', function(e) {
        e.preventDefault();
        
        console.log('Form submitted!');
        
        const formData = $(this).serialize();
        const url = $(this).attr('action');
        
        console.log('URL:', url);
        console.log('FormData:', formData);
        
        // Show loading
        Swal.fire({
            title: 'Menyimpan...',
            text: 'Mohon tunggu',
            allowOutsideClick: false,
            allowEscapeKey: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });
        
        $.ajax({
            url: url,
            type: 'POST',
            data: formData,
            success: function(response) {
                console.log('Success response:', response);
                $('#assignRoleModal').modal('hide');
                
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: 'Role dan permission berhasil diassign',
                    timer: 1500,
                    showConfirmButton: false
                });
                
                table.ajax.reload(null, false);
            },
            error: function(xhr) {
                console.log('Error response:', xhr);
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal!',
                    text: xhr.responseJSON?.message || 'Terjadi kesalahan saat assign role'
                });
            }
        });
    });

    // Reset Password Button
    $('#usersTable').on('click', '.btn-reset-password', function() {
        const url = $(this).data('url');
        const userName = $(this).data('name');
        const username = $(this).data('username');

        Swal.fire({
            icon: 'warning',
            title: 'Reset password user?',
            html: `Password <strong>${userName}</strong> akan direset menjadi username:<br><code>${username}</code>`,
            showCancelButton: true,
            confirmButtonColor: '#f59e0b',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Ya, Reset',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (!result.isConfirmed) return;

            Swal.fire({
                title: 'Mereset password...',
                text: 'Mohon tunggu',
                allowOutsideClick: false,
                allowEscapeKey: false,
                didOpen: () => Swal.showLoading()
            });

            $.ajax({
                url: url,
                type: 'PUT',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    if (response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Password direset',
                            html: `${response.message}<br>Password default: <code>${response.default_password}</code>`
                        });
                        table.ajax.reload(null, false);
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal!',
                            text: response.message
                        });
                    }
                },
                error: function(xhr) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal!',
                        text: xhr.responseJSON?.message || 'Terjadi kesalahan saat reset password'
                    });
                }
            });
        });
    });

    // Delete Button
    $('#usersTable').on('click', '.btn-delete', function() {
        const userId = $(this).data('id');
        const userName = $(this).data('name');
        
        Swal.fire({
            title: 'Yakin ingin menghapus?',
            html: `User <strong>${userName}</strong> akan dihapus permanen!`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: `/admin/users/${userId}`,
                    type: 'DELETE',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        if (response.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Terhapus!',
                                text: response.message,
                                timer: 1500,
                                showConfirmButton: false
                            });
                            table.ajax.reload(null, false);
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal!',
                                text: response.message
                            });
                        }
                    },
                    error: function(xhr) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: xhr.responseJSON?.message || 'Terjadi kesalahan'
                        });
                    }
                });
            }
        });
    });

    // Filter Functions
    $('#filterRole').on('change', function() {
        applyFilters();
    });
    $('#filterAccountType').on('change', function() {
        applyFilters();
    });
    
    $('#btnResetFilter').on('click', function() {
        $('#filterRole, #filterAccountType').val('');
        applyFilters();
    });
    
    function applyFilters() {
        let role = $('#filterRole').val();
        let accountType = $('#filterAccountType').val();
        
        // Build filter parameters
        let filterParams = {};
        if (role) filterParams.role = role;
        if (accountType) filterParams.account_type = accountType;
        
        // Reload DataTable with filters
        table.settings()[0].ajax.data = function(d) {
            return $.extend({}, d, filterParams);
        };
        table.ajax.reload();
    }

    // ===== TUGAS TAMBAHAN HANDLERS =====
    
    // Submit Add Tugas Tambahan Form
    $('#addTugasTambahanForm').on('submit', function(e) {
        e.preventDefault();
        
        const userId = $('#tugas_user_id').val();
        const formData = $(this).serialize();
        
        console.log('Submit tugas tambahan for user:', userId);
        
        $.ajax({
            url: `/admin/users/${userId}/tugas-tambahan`,
            type: 'POST',
            data: formData,
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            success: function(response) {
                // Close modal
                $('#addTugasTambahanModal').modal('hide');
                
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: response.message,
                    timer: 1500,
                    showConfirmButton: false
                });
                
                // Reset form
                $('#addTugasTambahanForm')[0].reset();
                
                // Reload tugas tambahan list in the assign role modal
                $.get(`/admin/users/${userId}/assign-role-form`, function(data) {
                    renderTugasTambahanList(data.tugasTambahan);
                });
                
                // Reload main table
                table.ajax.reload(null, false);
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

    // Deactivate Tugas Tambahan
    $(document).on('click', '.btn-deactivate-tugas', function() {
        const tugasId = $(this).data('id');
        const userId = window.currentTugasUserId;
        
        Swal.fire({
            title: 'Nonaktifkan Tugas Tambahan?',
            text: 'Tugas tambahan ini akan dinonaktifkan',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#f39c12',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Ya, Nonaktifkan',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: `/admin/tugas-tambahan/${tugasId}/deactivate`,
                    type: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil!',
                            text: response.message,
                            timer: 1500,
                            showConfirmButton: false
                        });
                        
                        // Reload tugas tambahan list
                        $.get(`/admin/users/${userId}/assign-role-form`, function(data) {
                            renderTugasTambahanList(data.tugasTambahan);
                        });
                        
                        table.ajax.reload(null, false);
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
    });

    // Activate Tugas Tambahan
    $(document).on('click', '.btn-activate-tugas', function() {
        const tugasId = $(this).data('id');
        const userId = $('#user_id').val();
        
        $.ajax({
            url: `/admin/tugas-tambahan/${tugasId}/activate`,
            type: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            success: function(response) {
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: response.message,
                    timer: 1500,
                    showConfirmButton: false
                });
                
                // Reload tugas tambahan list
                $.get(`/admin/users/${userId}/assign-role-form`, function(data) {
                    renderTugasTambahanList(data.tugasTambahan);
                });
                
                table.ajax.reload(null, false);
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

    // Delete Tugas Tambahan
    $(document).on('click', '.btn-delete-tugas', function() {
        const tugasId = $(this).data('id');
        const userId = $('#user_id').val();
        
        Swal.fire({
            title: 'Hapus Tugas Tambahan?',
            text: 'Data tugas tambahan akan dihapus permanen!',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: `/admin/tugas-tambahan/${tugasId}`,
                    type: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Terhapus!',
                            text: response.message,
                            timer: 1500,
                            showConfirmButton: false
                        });
                        
                        // Reload tugas tambahan list
                        $.get(`/admin/users/${userId}/assign-role-form`, function(data) {
                            renderTugasTambahanList(data.tugasTambahan);
                        });
                        
                        table.ajax.reload(null, false);
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
    });
});
</script>
@stop
