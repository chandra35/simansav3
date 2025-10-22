@extends('adminlte::page')

@section('title', 'Data User - SIMANSA')

@section('content_header')
    <h1>Data User</h1>
@stop

@section('content')
{{-- Card Informasi Users --}}
<div class="row mb-3">
    <div class="col-md-3 col-sm-6">
        <div class="info-box bg-info">
            <span class="info-box-icon"><i class="fas fa-users"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Total User</span>
                <span class="info-box-number">{{ $stats['total_users'] }} User</span>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6">
        <div class="info-box bg-danger">
            <span class="info-box-icon"><i class="fas fa-user-shield"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Admin</span>
                <span class="info-box-number">{{ $stats['admin'] }} User</span>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6">
        <div class="info-box bg-success">
            <span class="info-box-icon"><i class="fas fa-chalkboard-teacher"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">GTK</span>
                <span class="info-box-number">{{ $stats['gtk'] }} User</span>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6">
        <div class="info-box bg-primary">
            <span class="info-box-icon"><i class="fas fa-user-graduate"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Siswa</span>
                <span class="info-box-number">{{ $stats['siswa'] }} User</span>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card card-primary card-outline">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-users mr-1"></i>
                    Manajemen Data User
                </h3>
                <div class="card-tools">
                    @can('view-permission')
                    <a href="{{ route('admin.users.permission-matrix') }}" class="btn btn-info btn-sm mr-2">
                        <i class="fas fa-shield-alt"></i> Permission Matrix
                    </a>
                    @endcan
                    @can('create-user')
                    <a href="{{ route('admin.users.create') }}" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus"></i> Tambah User
                    </a>
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
                                        <label for="filterRole" class="mr-2">
                                            <i class="fas fa-user-tag"></i> Role:
                                        </label>
                                        <select id="filterRole" class="form-control form-control-sm" style="width: 200px;">
                                            <option value="">Semua Role</option>
                                            @foreach($roles as $role)
                                                <option value="{{ $role->name }}">{{ $role->name }}</option>
                                            @endforeach
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
                    <table id="usersTable" class="table table-bordered table-striped table-hover" style="width: 100%">
                        <thead class="bg-light">
                            <tr>
                                <th style="width: 40px; text-align: center;">No</th>
                                <th style="width: 150px;">Nama</th>
                                <th style="width: 120px;">Username</th>
                                <th style="width: 180px;">Email</th>
                                <th style="width: 110px;">Telepon</th>
                                <th style="width: 150px;">Roles</th>
                                <th style="width: 80px; text-align: center;">Status</th>
                                <th style="width: 120px; text-align: center;">Aksi</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Assign Role -->
<div class="modal fade" id="assignRoleModal" tabindex="-1" aria-labelledby="assignRoleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
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
                    
                    <div class="form-group">
                        <label>Roles <span class="text-muted">(bisa pilih lebih dari satu)</span></label>
                        <div id="rolesCheckboxes" class="border rounded p-3 bg-light">
                            <!-- Roles akan dimuat via AJAX -->
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Custom Permissions <span class="text-muted">(opsional, override dari roles)</span></label>
                        <div class="accordion" id="permissionsAccordion">
                            <!-- Permissions akan dimuat via AJAX -->
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
@stop

@section('css')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap4.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap4.min.css">
<style>
    .custom-control-label {
        cursor: pointer;
    }
    .badge {
        font-size: 85%;
        margin: 2px;
        display: inline-block;
    }
    .accordion .card {
        margin-bottom: 5px;
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
        scrollX: true,
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
            { data: 'name', name: 'name', width: '150px' },
            { data: 'username', name: 'username', width: '120px' },
            { data: 'email', name: 'email', width: '180px' },
            { data: 'phone', name: 'phone', defaultContent: '-', width: '110px' },
            { data: 'roles', name: 'roles', orderable: false, searchable: false, width: '150px' },
            { data: 'status', name: 'is_active', orderable: false, width: '80px' },
            { data: 'action', name: 'action', orderable: false, searchable: false, width: '120px' }
        ],
        columnDefs: [
            { 
                targets: [0, 6, 7],
                className: 'text-center'
            },
            {
                targets: 5,
                render: function(data) {
                    return '<div style="white-space: normal; word-wrap: break-word;">' + data + '</div>';
                }
            },
            {
                targets: 7,
                render: function(data) {
                    return '<div style="white-space: nowrap;">' + data + '</div>';
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
    $('#usersTable').on('click', '.btn-assign-role', function() {
        const userId = $(this).data('id');
        const userName = $(this).data('name');
        
        $('#user_id').val(userId);
        $('#userName').text(userName);
        $('#assignRoleForm').attr('action', `/admin/users/${userId}/assign-role`);
        
        // Load roles dan permissions via AJAX
        $.ajax({
            url: `/admin/users/${userId}/assign-role-form`,
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
                        const checked = response.userPermissions.includes(perm.id) ? 'checked' : '';
                        permsHtml += `
                            <div class="custom-control custom-checkbox">
                                <input class="custom-control-input" type="checkbox" name="permissions[]" value="${perm.id}" id="perm${perm.id}" ${checked}>
                                <label class="custom-control-label" for="perm${perm.id}">
                                    ${perm.name}
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
                
                $('#assignRoleModal').modal('show');
            },
            error: function() {
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: 'Gagal memuat data roles dan permissions'
                });
            }
        });
    });

    // Submit Assign Role Form
    $('#assignRoleForm').on('submit', function(e) {
        e.preventDefault();
        
        const formData = $(this).serialize();
        const url = $(this).attr('action');
        
        $.ajax({
            url: url,
            type: 'POST',
            data: formData,
            success: function(response) {
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
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal!',
                    text: 'Terjadi kesalahan saat assign role'
                });
            }
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
    
    $('#btnResetFilter').on('click', function() {
        $('#filterRole').val('');
        applyFilters();
    });
    
    function applyFilters() {
        let role = $('#filterRole').val();
        
        // Build filter parameters
        let filterParams = {};
        if (role) filterParams.role = role;
        
        // Reload DataTable with filters
        table.settings()[0].ajax.data = function(d) {
            return $.extend({}, d, filterParams);
        };
        table.ajax.reload();
    }
});
</script>
@stop
