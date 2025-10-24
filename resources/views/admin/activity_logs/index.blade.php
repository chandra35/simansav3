@extends('adminlte::page')

@section('title', 'Activity Logs - SIMANSA')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1><i class="fas fa-history"></i> Activity Logs</h1>
            @if(!auth()->user()->hasRole('Super Admin'))
                <small class="text-muted"><i class="fas fa-info-circle"></i> Menampilkan aktivitas Anda saja</small>
            @else
                <small class="text-muted"><i class="fas fa-info-circle"></i> Menampilkan semua aktivitas pengguna</small>
            @endif
        </div>
        <button class="btn btn-success" onclick="exportLogs()">
            <i class="fas fa-download"></i> Export CSV
        </button>
    </div>
@stop

@section('content')
<!-- Statistics Cards -->
<div class="row">
    <div class="col-lg-3 col-6">
        <div class="small-box bg-info">
            <div class="inner">
                <h3 id="stat-total">0</h3>
                <p>Total Aktivitas</p>
            </div>
            <div class="icon">
                <i class="fas fa-chart-line"></i>
            </div>
        </div>
    </div>
    @if(auth()->user()->hasRole('Super Admin'))
    <div class="col-lg-3 col-6">
        <div class="small-box bg-success">
            <div class="inner">
                <h3 id="stat-users">0</h3>
                <p>Unique Users</p>
            </div>
            <div class="icon">
                <i class="fas fa-users"></i>
            </div>
        </div>
    </div>
    @else
    <div class="col-lg-3 col-6">
        <div class="small-box bg-success">
            <div class="inner">
                <h3 id="stat-users">1</h3>
                <p>User (Anda)</p>
            </div>
            <div class="icon">
                <i class="fas fa-user"></i>
            </div>
        </div>
    </div>
    @endif
    <div class="col-lg-3 col-6">
        <div class="small-box bg-warning">
            <div class="inner">
                <h3 id="stat-mobile">0</h3>
                <p>Mobile Devices</p>
            </div>
            <div class="icon">
                <i class="fas fa-mobile-alt"></i>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-6">
        <div class="small-box bg-danger">
            <div class="inner">
                <h3 id="stat-desktop">0</h3>
                <p>Desktop Devices</p>
            </div>
            <div class="icon">
                <i class="fas fa-desktop"></i>
            </div>
        </div>
    </div>
</div>

<!-- Filters -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-filter"></i> Filter Logs</h3>
        <div class="card-tools">
            <button type="button" class="btn btn-tool" data-card-widget="collapse">
                <i class="fas fa-minus"></i>
            </button>
        </div>
    </div>
    <div class="card-body">
        <form id="filterForm">
            <div class="row">
                <div class="col-md-3">
                    <div class="form-group">
                        <label>Tanggal Dari</label>
                        <input type="date" class="form-control" id="date_from" name="date_from">
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label>Tanggal Sampai</label>
                        <input type="date" class="form-control" id="date_to" name="date_to">
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label>Tipe Aktivitas</label>
                        <select class="form-control" id="activity_type" name="activity_type">
                            <option value="">Semua</option>
                            <option value="login">Login</option>
                            <option value="logout">Logout</option>
                            <option value="upload_foto">Upload Foto</option>
                            <option value="upload_dokumen">Upload Dokumen</option>
                            <option value="update_data_diri">Update Data Diri</option>
                            <option value="update_data_ortu">Update Data Ortu</option>
                            <option value="delete_dokumen">Delete Dokumen</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label>Device Type</label>
                        <select class="form-control" id="device_type" name="device_type">
                            <option value="">Semua</option>
                            <option value="mobile">Mobile</option>
                            <option value="tablet">Tablet</option>
                            <option value="desktop">Desktop</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <button type="button" class="btn btn-primary" onclick="applyFilter()">
                        <i class="fas fa-search"></i> Terapkan Filter
                    </button>
                    <button type="button" class="btn btn-secondary" onclick="resetFilter()">
                        <i class="fas fa-undo"></i> Reset
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Logs Table -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-list"></i> Activity Logs</h3>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table id="logsTable" class="table table-bordered table-striped table-hover">
                <thead>
                    <tr>
                        <th width="15%">User</th>
                        <th width="15%">Activity</th>
                        <th width="15%">Device</th>
                        <th width="20%">Location</th>
                        <th width="10%">Changes</th>
                        <th width="15%">Timestamp</th>
                        <th width="10%">Action</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Detail -->
<div class="modal fade" id="detailModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary">
                <h5 class="modal-title text-white">
                    <i class="fas fa-info-circle"></i> Detail Activity Log
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body" id="detailContent">
                <div class="text-center">
                    <i class="fas fa-spinner fa-spin fa-3x"></i>
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

<!-- Modal Changes -->
<div class="modal fade" id="changesModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-info">
                <h5 class="modal-title text-white">
                    <i class="fas fa-exchange-alt"></i> Data Changes
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body" id="changesContent">
                <div class="text-center">
                    <i class="fas fa-spinner fa-spin fa-3x"></i>
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
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <style>
        .user-info strong { display: block; }
        .device-info, .location-info, .timestamp-info { line-height: 1.6; }
        .comparison-table td { padding: 8px; vertical-align: top; }
        .old-value { background-color: #ffebee; }
        .new-value { background-color: #e8f5e9; }
    </style>
@stop

@section('js')
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap4.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
let table;

$(document).ready(function() {
    // Initialize DataTable
    table = $('#logsTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route('admin.activity-logs.data') }}',
            data: function(d) {
                d.date_from = $('#date_from').val();
                d.date_to = $('#date_to').val();
                d.activity_type = $('#activity_type').val();
                d.device_type = $('#device_type').val();
            }
        },
        columns: [
            { data: 'user_info', name: 'user.name' },
            { data: 'activity', name: 'activity_type' },
            { data: 'device_info', name: 'device_type', orderable: false },
            { data: 'location', name: 'country', orderable: false },
            { data: 'changes', name: 'changed_fields', orderable: false, searchable: false },
            { data: 'timestamp', name: 'created_at' },
            { data: 'action', name: 'action', orderable: false, searchable: false }
        ],
        order: [[5, 'desc']],
        pageLength: 25,
        language: {
            processing: '<i class="fas fa-spinner fa-spin"></i> Loading...',
            search: 'Cari:',
            lengthMenu: 'Tampilkan _MENU_ data',
            info: 'Menampilkan _START_ sampai _END_ dari _TOTAL_ data',
            infoEmpty: 'Tidak ada data',
            infoFiltered: '(difilter dari _MAX_ total data)',
            paginate: {
                first: 'Pertama',
                last: 'Terakhir',
                next: 'Selanjutnya',
                previous: 'Sebelumnya'
            }
        }
    });

    // Load statistics
    loadStatistics();
});

function applyFilter() {
    table.ajax.reload();
    loadStatistics();
}

function resetFilter() {
    $('#filterForm')[0].reset();
    table.ajax.reload();
    loadStatistics();
}

function loadStatistics() {
    $.ajax({
        url: '{{ route('admin.activity-logs.statistics') }}',
        data: {
            date_from: $('#date_from').val(),
            date_to: $('#date_to').val()
        },
        success: function(data) {
            $('#stat-total').text(data.total_activities || 0);
            $('#stat-users').text(data.unique_users || 0);
            
            let mobileCount = 0, desktopCount = 0;
            if (data.by_device) {
                data.by_device.forEach(function(item) {
                    if (item.device_type === 'mobile') mobileCount = item.total;
                    if (item.device_type === 'desktop') desktopCount = item.total;
                });
            }
            $('#stat-mobile').text(mobileCount);
            $('#stat-desktop').text(desktopCount);
        }
    });
}

function showDetail(id) {
    $('#detailModal').modal('show');
    $('#detailContent').html('<div class="text-center"><i class="fas fa-spinner fa-spin fa-3x"></i></div>');
    
    $.ajax({
        url: '{{ route('admin.activity-logs.show', '') }}/' + id,
        success: function(response) {
            if (response.success) {
                let log = response.log;
                let user = response.user;
                
                let html = `
                    <div class="row">
                        <div class="col-md-6">
                            <h5><i class="fas fa-user"></i> User Information</h5>
                            <table class="table table-sm">
                                <tr><th width="40%">Nama:</th><td>${user ? user.name : 'Unknown'}</td></tr>
                                <tr><th>Username:</th><td>${user ? user.username : 'N/A'}</td></tr>
                                <tr><th>Email:</th><td>${user ? user.email : 'N/A'}</td></tr>
                            </table>
                            
                            <h5><i class="fas fa-laptop"></i> Device Information</h5>
                            <table class="table table-sm">
                                <tr><th width="40%">Device Type:</th><td>${log.device_type || '-'}</td></tr>
                                <tr><th>Browser:</th><td>${log.browser || '-'} ${log.browser_version || ''}</td></tr>
                                <tr><th>Platform:</th><td>${log.platform || '-'} ${log.platform_version || ''}</td></tr>
                                <tr><th>User Agent:</th><td><small>${log.user_agent || '-'}</small></td></tr>
                            </table>
                        </div>
                        
                        <div class="col-md-6">
                            <h5><i class="fas fa-tasks"></i> Activity Information</h5>
                            <table class="table table-sm">
                                <tr><th width="40%">Activity Type:</th><td><span class="badge badge-info">${log.activity_type}</span></td></tr>
                                <tr><th>Description:</th><td>${log.description}</td></tr>
                                <tr><th>URL:</th><td><small>${log.url || '-'}</small></td></tr>
                                <tr><th>Method:</th><td>${log.method || '-'}</td></tr>
                                <tr><th>Timestamp:</th><td>${new Date(log.created_at).toLocaleString('id-ID')}</td></tr>
                            </table>
                            
                            <h5><i class="fas fa-map-marker-alt"></i> Location Information</h5>
                            <table class="table table-sm">
                                <tr><th width="40%">IP Address:</th><td>${log.ip_address}</td></tr>
                                <tr><th>Country:</th><td>${log.country || '-'} (${log.country_code || '-'})</td></tr>
                                <tr><th>Region:</th><td>${log.region || '-'}</td></tr>
                                <tr><th>City:</th><td>${log.city || '-'}</td></tr>
                                <tr><th>Coordinates:</th><td>${log.latitude || '-'}, ${log.longitude || '-'}</td></tr>
                                <tr><th>Timezone:</th><td>${log.timezone || '-'}</td></tr>
                            </table>
                        </div>
                    </div>
                `;
                
                $('#detailContent').html(html);
            }
        },
        error: function() {
            $('#detailContent').html('<div class="alert alert-danger">Error loading detail</div>');
        }
    });
}

function showChanges(id) {
    $('#changesModal').modal('show');
    $('#changesContent').html('<div class="text-center"><i class="fas fa-spinner fa-spin fa-3x"></i></div>');
    
    $.ajax({
        url: '{{ route('admin.activity-logs.show', '') }}/' + id,
        success: function(response) {
            if (response.success) {
                let log = response.log;
                
                if (!log.changed_fields || log.changed_fields.length === 0) {
                    $('#changesContent').html('<div class="alert alert-info">Tidak ada perubahan data</div>');
                    return;
                }
                
                let html = '<table class="table table-bordered comparison-table">';
                html += '<thead><tr><th width="25%">Field</th><th width="37.5%">Old Value</th><th width="37.5%">New Value</th></tr></thead>';
                html += '<tbody>';
                
                log.changed_fields.forEach(function(field) {
                    let oldVal = log.old_values && log.old_values[field] ? log.old_values[field] : '-';
                    let newVal = log.new_values && log.new_values[field] ? log.new_values[field] : '-';
                    
                    html += `<tr>
                        <td><strong>${field}</strong></td>
                        <td class="old-value">${oldVal}</td>
                        <td class="new-value">${newVal}</td>
                    </tr>`;
                });
                
                html += '</tbody></table>';
                $('#changesContent').html(html);
            }
        },
        error: function() {
            $('#changesContent').html('<div class="alert alert-danger">Error loading changes</div>');
        }
    });
}

function exportLogs() {
    let params = new URLSearchParams({
        date_from: $('#date_from').val(),
        date_to: $('#date_to').val(),
        activity_type: $('#activity_type').val(),
        device_type: $('#device_type').val()
    });
    
    window.location.href = '{{ route('admin.activity-logs.export') }}?' + params.toString();
}
</script>
@stop
