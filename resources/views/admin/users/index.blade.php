@extends('adminlte::page')

@section('title', 'Data User - SIMANSA')

@section('content_header')
    <h1>Data User</h1>
@stop

@section('content')
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
                @if(auth()->user()->hasRole('Super Admin'))
                <div class="mb-3">
                    <button type="button" id="bulkDeleteBtn" class="btn btn-danger btn-sm" disabled>
                        <i class="fas fa-trash"></i> Hapus Terpilih (<span id="selectedCount">0</span>)
                    </button>
                </div>
                @endif
                
                <div class="table-responsive">
                    <table id="usersTable" class="table table-bordered table-striped table-hover" style="width: 100%">
                        <thead class="bg-light">
                            <tr>
                                @if(auth()->user()->hasRole('Super Admin'))
                                <th style="width: 30px; text-align: center;">
                                    <input type="checkbox" id="checkAll">
                                </th>
                                @endif
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
    
    /* Checkbox styling */
    #checkAll, .user-checkbox {
        width: 18px;
        height: 18px;
        cursor: pointer;
    }
    
    /* Bulk delete button */
    #bulkDeleteBtn {
        transition: all 0.3s ease;
    }
    
    #bulkDeleteBtn:disabled {
        opacity: 0.5;
        cursor: not-allowed;
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
    
    /* Remove sorting indicator from checkbox column */
    @if(auth()->user()->hasRole('Super Admin'))
    #usersTable thead th:first-child {
        cursor: default !important;
        background-image: none !important;
    }
    
    #usersTable thead th:first-child::after,
    #usersTable thead th:first-child::before {
        display: none !important;
    }
    
    #usersTable thead th:first-child.sorting,
    #usersTable thead th:first-child.sorting_asc,
    #usersTable thead th:first-child.sorting_desc {
        cursor: default !important;
        background-image: none !important;
    }
    @endif
    
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
            @if(auth()->user()->hasRole('Super Admin'))
            { 
                data: 'checkbox', 
                name: 'checkbox', 
                orderable: false, 
                searchable: false, 
                width: '30px',
                className: 'text-center'
            },
            @endif
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
            @if(auth()->user()->hasRole('Super Admin'))
            {
                targets: 0,
                orderable: false,
                className: 'text-center'
            },
            { 
                targets: [1, 7, 8],
                className: 'text-center'
            },
            {
                targets: 6,
                render: function(data) {
                    return '<div style="white-space: normal; word-wrap: break-word;">' + data + '</div>';
                }
            },
            {
                targets: 8,
                render: function(data) {
                    return '<div style="white-space: nowrap;">' + data + '</div>';
                }
            }
            @else
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
            @endif
        ],
        @if(auth()->user()->hasRole('Super Admin'))
        order: [[2, 'asc']],  // Default sorting by name (index 2 karena ada checkbox di index 0)
        @else
        order: [[1, 'asc']],  // Default sorting by name (index 1)
        @endif
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

    @if(auth()->user()->hasRole('Super Admin'))
    // Prevent sorting on checkbox column header
    $('#usersTable thead th:first-child').on('click', function(e) {
        e.stopPropagation();
    });
    
    // Remove sorting class from checkbox column
    $('#usersTable thead th:first-child').removeClass('sorting sorting_asc sorting_desc');
    
    // Check All functionality
    $('#checkAll').on('click', function() {
        const isChecked = $(this).prop('checked');
        $('.user-checkbox').prop('checked', isChecked);
        updateBulkDeleteButton();
    });

    // Individual checkbox
    $('#usersTable').on('change', '.user-checkbox', function() {
        updateBulkDeleteButton();
        
        // Update check all status
        const total = $('.user-checkbox').length;
        const checked = $('.user-checkbox:checked').length;
        $('#checkAll').prop('checked', total === checked);
    });

    // Update bulk delete button state
    function updateBulkDeleteButton() {
        const checkedCount = $('.user-checkbox:checked').length;
        $('#selectedCount').text(checkedCount);
        $('#bulkDeleteBtn').prop('disabled', checkedCount === 0);
    }

    // Bulk Delete
    $('#bulkDeleteBtn').on('click', function() {
        const selectedIds = [];
        $('.user-checkbox:checked').each(function() {
            selectedIds.push($(this).val());
        });

        if (selectedIds.length === 0) {
            Swal.fire({
                icon: 'warning',
                title: 'Perhatian!',
                text: 'Silakan pilih user yang ingin dihapus'
            });
            return;
        }

        Swal.fire({
            title: 'Konfirmasi Hapus',
            text: `Apakah Anda yakin ingin menghapus ${selectedIds.length} user yang dipilih?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '{{ route("admin.users.bulk-delete") }}',
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        ids: selectedIds
                    },
                    success: function(response) {
                        if (response.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil!',
                                text: response.message,
                                timer: 2000,
                                showConfirmButton: false
                            });
                            table.ajax.reload();
                            $('#checkAll').prop('checked', false);
                            updateBulkDeleteButton();
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal!',
                                text: response.message
                            });
                        }
                    },
                    error: function(xhr) {
                        let errorMsg = 'Terjadi kesalahan saat menghapus user';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMsg = xhr.responseJSON.message;
                        }
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: errorMsg
                        });
                    }
                });
            }
        });
    });
    @endif

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
});
</script>
@stop
