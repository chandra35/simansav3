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
        <div>
            <button class="btn btn-info" onclick="loadStatistics()">
                <i class="fas fa-sync"></i> Refresh
            </button>
            <button class="btn btn-success" onclick="exportLogs()">
                <i class="fas fa-download"></i> Export CSV
            </button>
        </div>
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
                <div class="col-md-2">
                    <div class="form-group">
                        <label>Tanggal Dari</label>
                        <input type="date" class="form-control" id="date_from" name="date_from">
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <label>Tanggal Sampai</label>
                        <input type="date" class="form-control" id="date_to" name="date_to">
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <label>Tipe Aktivitas</label>
                        <select class="form-control" id="activity_type" name="activity_type">
                            <option value="">Semua</option>
                            <optgroup label="Autentikasi">
                                <option value="login">Login</option>
                                <option value="logout">Logout</option>
                                <option value="password_changed">Ganti Password</option>
                            </optgroup>
                            <optgroup label="Data Siswa">
                                <option value="update_data_diri">Update Data Diri</option>
                                <option value="update_data_ortu">Update Data Ortu</option>
                                <option value="update_data_alamat">Update Alamat</option>
                                <option value="update_data_pendidikan">Update Pendidikan</option>
                            </optgroup>
                            <optgroup label="Dokumen">
                                <option value="upload_foto">Upload Foto</option>
                                <option value="upload_dokumen">Upload Dokumen</option>
                                <option value="delete_dokumen">Hapus Dokumen</option>
                                <option value="download">Download</option>
                            </optgroup>
                            <optgroup label="Lainnya">
                                <option value="view_page">Lihat Halaman</option>
                                <option value="create">Tambah Data</option>
                                <option value="update">Update Data</option>
                                <option value="delete">Hapus Data</option>
                            </optgroup>
                        </select>
                    </div>
                </div>
                <div class="col-md-2">
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
                @if(auth()->user()->hasRole('Super Admin'))
                <div class="col-md-2">
                    <div class="form-group">
                        <label>Role Pengguna</label>
                        <select class="form-control" id="user_role" name="user_role">
                            <option value="">Semua</option>
                            <option value="siswa">Siswa</option>
                            <option value="admin">Admin</option>
                            <option value="guru">Guru/GTK</option>
                        </select>
                    </div>
                </div>
                @endif
                <div class="col-md-2">
                    <div class="form-group">
                        <label>&nbsp;</label>
                        <div>
                            <button type="button" class="btn btn-primary btn-block" onclick="applyFilter()">
                                <i class="fas fa-search"></i> Filter
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <button type="button" class="btn btn-secondary btn-sm" onclick="resetFilter()">
                        <i class="fas fa-undo"></i> Reset Filter
                    </button>
                    @if(auth()->user()->hasRole('Super Admin'))
                    <button type="button" class="btn btn-info btn-sm ml-2" onclick="filterSiswaOnly()">
                        <i class="fas fa-user-graduate"></i> Hanya Siswa
                    </button>
                    <button type="button" class="btn btn-warning btn-sm ml-2" onclick="filterLoginOnly()">
                        <i class="fas fa-sign-in-alt"></i> Hanya Login
                    </button>
                    @endif
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
                        <th width="5%">Foto</th>
                        <th width="15%">User</th>
                        <th width="15%">Activity</th>
                        <th width="15%">Device</th>
                        <th width="15%">Location</th>
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
    <div class="modal-dialog modal-xl" role="document">
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
                <div class="text-center py-5">
                    <i class="fas fa-spinner fa-spin fa-3x text-primary"></i>
                    <p class="mt-3">Loading...</p>
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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    <style>
        .user-info strong { display: block; }
        .device-info, .location-info, .timestamp-info { line-height: 1.6; }
        .comparison-table td { padding: 8px; vertical-align: top; }
        .old-value { background-color: #ffebee; }
        .new-value { background-color: #e8f5e9; }
        .detail-card { border-left: 4px solid #007bff; margin-bottom: 15px; }
        .detail-card .card-header { background-color: #f8f9fa; font-weight: 600; }
        .detail-table th { width: 35%; background-color: #f8f9fa; }
        .activity-badge { font-size: 0.9rem; }
        .map-container { height: 200px; background: #e9ecef; border-radius: 5px; display: flex; align-items: center; justify-content: center; }
        .timeline-item { padding-left: 30px; border-left: 2px solid #007bff; position: relative; margin-bottom: 15px; }
        .timeline-item::before { content: ''; position: absolute; left: -8px; top: 0; width: 14px; height: 14px; background: #007bff; border-radius: 50%; }
        .stat-icon { font-size: 2rem; opacity: 0.3; }
        #logsTable td { position: relative; }
        #logsTable .btn { position: relative; z-index: 2; }
    </style>
@stop

@section('js')
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap4.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

<script>
let table;

// Toastr config
toastr.options = {
    "closeButton": true,
    "progressBar": true,
    "positionClass": "toast-top-right",
    "timeOut": "3000"
};

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
                d.user_role = $('#user_role').val();
            }
        },
        columns: [
            { data: 'foto', name: 'foto', orderable: false, searchable: false },
            { data: 'user_info', name: 'user.name' },
            { data: 'activity', name: 'activity_type' },
            { data: 'device_info', name: 'device_type', orderable: false },
            { data: 'location', name: 'country', orderable: false },
            { data: 'changes', name: 'changed_fields', orderable: false, searchable: false },
            { data: 'timestamp', name: 'created_at' },
            { data: 'action', name: 'action', orderable: false, searchable: false }
        ],
        order: [[6, 'desc']],
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

    $('#logsTable').on('click', '.js-show-detail', function (event) {
        event.preventDefault();
        event.stopPropagation();
        showDetail($(this).data('id'));
    });

    $('#logsTable').on('click', '.js-show-changes', function (event) {
        event.preventDefault();
        event.stopPropagation();
        showChanges($(this).data('id'));
    });
});

function applyFilter() {
    table.ajax.reload();
    loadStatistics();
    toastr.info('Filter diterapkan');
}

function resetFilter() {
    $('#filterForm')[0].reset();
    table.ajax.reload();
    loadStatistics();
    toastr.info('Filter direset');
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
    $('#detailContent').html(`
        <div class="text-center py-5">
            <i class="fas fa-spinner fa-spin fa-3x text-primary"></i>
            <p class="mt-3">Loading...</p>
        </div>
    `);
    
    $.ajax({
        url: '{{ route('admin.activity-logs.show', '') }}/' + id,
        success: function(response) {
            if (response.success) {
                let log = response.log;
                let user = response.user;
                
                // Format timestamp
                let timestamp = new Date(log.created_at);
                let formattedDate = timestamp.toLocaleDateString('id-ID', { 
                    weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' 
                });
                let formattedTime = timestamp.toLocaleTimeString('id-ID', {
                    hour: '2-digit', minute: '2-digit', second: '2-digit'
                });
                
                // Activity badge color
                let activityColor = getActivityColor(log.activity_type);
                
                // Build location string
                let locationParts = [];
                if (log.city) locationParts.push(log.city);
                if (log.region && log.region !== log.city) locationParts.push(log.region);
                if (log.country) locationParts.push(log.country);
                let locationStr = locationParts.join(', ') || 'Tidak diketahui';
                let locationMeta = log.properties && log.properties.location_meta ? log.properties.location_meta : null;
                let requestMeta = log.properties && log.properties.request_meta ? log.properties.request_meta : null;
                let locationSource = locationMeta && locationMeta.source ? locationMeta.source : 'N/A';
                let locationStatus = locationMeta && locationMeta.status ? locationMeta.status : 'N/A';
                let locationAccuracy = locationMeta && locationMeta.accuracy !== null && locationMeta.accuracy !== undefined
                    ? '±' + Number(locationMeta.accuracy).toFixed(0) + ' meter'
                    : 'N/A';
                let locationSourceBadge = formatLocationSourceBadge(locationSource);
                let locationStatusBadge = formatLocationStatusBadge(locationStatus);
                let ipSourceBadge = formatIpSourceBadge(requestMeta && requestMeta.ip_source ? requestMeta.ip_source : 'remote-addr');
                
                // Map link
                let mapLink = '';
                if (log.latitude && log.longitude) {
                    mapLink = `<a href="https://www.google.com/maps?q=${log.latitude},${log.longitude}" target="_blank" class="btn btn-sm btn-outline-primary">
                        <i class="fas fa-map-marker-alt"></i> Lihat di Google Maps
                    </a>`;
                }
                
                // Changes section
                let changesHtml = '';
                if (log.changed_fields && log.changed_fields.length > 0) {
                    changesHtml = `
                        <div class="card detail-card">
                            <div class="card-header">
                                <i class="fas fa-exchange-alt text-warning"></i> Perubahan Data (${log.changed_fields.length} field)
                            </div>
                            <div class="card-body p-0">
                                <table class="table table-sm table-bordered mb-0">
                                    <thead class="thead-light">
                                        <tr>
                                            <th width="25%">Field</th>
                                            <th width="37.5%">Nilai Lama</th>
                                            <th width="37.5%">Nilai Baru</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                    `;
                    
                    log.changed_fields.forEach(function(field) {
                        let oldVal = log.old_values && log.old_values[field] !== null ? log.old_values[field] : '<em class="text-muted">kosong</em>';
                        let newVal = log.new_values && log.new_values[field] !== null ? log.new_values[field] : '<em class="text-muted">kosong</em>';
                        
                        changesHtml += `
                            <tr>
                                <td><strong>${formatFieldName(field)}</strong></td>
                                <td class="old-value">${oldVal}</td>
                                <td class="new-value">${newVal}</td>
                            </tr>
                        `;
                    });
                    
                    changesHtml += `
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    `;
                }
                
                let html = `
                    <!-- Activity Header -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="d-flex align-items-center">
                                <span class="badge badge-${activityColor} activity-badge mr-3 p-2">
                                    <i class="${getActivityIcon(log.activity_type)}"></i> ${formatActivityType(log.activity_type)}
                                </span>
                                <div>
                                    <strong>${formattedDate}</strong><br>
                                    <span class="text-muted">${formattedTime}</span>
                                </div>
                            </div>
                            <p class="mt-2 mb-0">${log.description || 'Tidak ada deskripsi'}</p>
                        </div>
                    </div>
                    
                    <div class="row">
                        <!-- User Information -->
                        <div class="col-md-6">
                            <div class="card detail-card">
                                <div class="card-header">
                                    <i class="fas fa-user text-primary"></i> Informasi Pengguna
                                </div>
                                <div class="card-body p-0">
                                    <table class="table table-sm mb-0 detail-table">
                                        <tr><th>Nama Lengkap</th><td>${user ? user.name : 'Unknown'}</td></tr>
                                        <tr><th>Username</th><td>${user ? user.username : 'N/A'}</td></tr>
                                        <tr><th>Email</th><td>${user && user.email ? user.email : 'N/A'}</td></tr>
                                        <tr><th>Role</th><td><span class="badge badge-info">${user && user.roles && user.roles[0] ? user.roles[0].name : 'N/A'}</span></td></tr>
                                    </table>
                                </div>
                            </div>
                            
                            <div class="card detail-card">
                                <div class="card-header">
                                    <i class="fas fa-laptop text-success"></i> Informasi Perangkat
                                </div>
                                <div class="card-body p-0">
                                    <table class="table table-sm mb-0 detail-table">
                                        <tr>
                                            <th>Tipe Perangkat</th>
                                            <td>
                                                <i class="${getDeviceIcon(log.device_type)}"></i>
                                                ${capitalizeFirst(log.device_type) || 'Unknown'}
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>Browser</th>
                                            <td>
                                                <i class="${getBrowserIcon(log.browser)}"></i>
                                                ${log.browser || 'Unknown'} ${log.browser_version || ''}
                                            </td>
                                        </tr>
                                        <tr><th>Sistem Operasi</th><td>${log.platform || 'Unknown'} ${log.platform_version || ''}</td></tr>
                                        <tr><th>User Agent</th><td><small class="text-muted" style="word-break: break-all;">${log.user_agent || 'N/A'}</small></td></tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Location & Request -->
                        <div class="col-md-6">
                            <div class="card detail-card">
                                <div class="card-header">
                                    <i class="fas fa-map-marker-alt text-danger"></i> Informasi Lokasi
                                </div>
                                <div class="card-body p-0">
                                    <table class="table table-sm mb-0 detail-table">
                                        <tr><th>Alamat IP</th><td><code>${log.ip_address || 'N/A'}</code></td></tr>
                                        <tr><th>Sumber IP</th><td>${ipSourceBadge}</td></tr>
                                        <tr><th>Lokasi</th><td>${locationStr}</td></tr>
                                        <tr><th>Negara</th><td>${log.country || 'N/A'} ${log.country_code ? '(' + log.country_code + ')' : ''}</td></tr>
                                        <tr><th>Kota</th><td>${log.city || 'N/A'}</td></tr>
                                        <tr><th>Koordinat</th><td>${log.latitude && log.longitude ? log.latitude + ', ' + log.longitude : 'N/A'}</td></tr>
                                        <tr><th>Sumber Lokasi</th><td>${locationSourceBadge}</td></tr>
                                        <tr><th>Status Lokasi</th><td>${locationStatusBadge}</td></tr>
                                        <tr><th>Akurasi GPS</th><td>${locationAccuracy}</td></tr>
                                        <tr><th>Timezone</th><td>${log.timezone || 'N/A'}</td></tr>
                                        <tr><th>Peta</th><td>${mapLink || '<span class="text-muted">Tidak tersedia</span>'}</td></tr>
                                    </table>
                                </div>
                            </div>
                            
                            <div class="card detail-card">
                                <div class="card-header">
                                    <i class="fas fa-globe text-info"></i> Informasi Request
                                </div>
                                <div class="card-body p-0">
                                    <table class="table table-sm mb-0 detail-table">
                                        <tr><th>URL</th><td><small style="word-break: break-all;">${log.url || 'N/A'}</small></td></tr>
                                        <tr><th>Method</th><td><span class="badge badge-secondary">${log.method || 'N/A'}</span></td></tr>
                                        <tr><th>Resolved IP</th><td><code>${requestMeta && requestMeta.resolved_ip ? requestMeta.resolved_ip : (log.ip_address || 'N/A')}</code></td></tr>
                                        <tr><th>X-Forwarded-For</th><td><small style="word-break: break-all;">${requestMeta && requestMeta.forwarded_for ? requestMeta.forwarded_for : 'N/A'}</small></td></tr>
                                        <tr><th>Model</th><td>${log.model_type ? log.model_type.split('\\').pop() : 'N/A'}</td></tr>
                                        <tr><th>Model ID</th><td><code>${log.model_id || 'N/A'}</code></td></tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    ${changesHtml}
                `;
                
                $('#detailContent').html(html);
            }
        },
        error: function() {
            $('#detailContent').html('<div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> Error loading detail</div>');
        }
    });
}

function showChanges(id) {
    // Just redirect to show detail since changes are now included
    showDetail(id);
}

function getActivityColor(type) {
    const colors = {
        'login': 'success',
        'logout': 'secondary',
        'upload_foto': 'primary',
        'upload_dokumen': 'primary',
        'update_data_diri': 'warning',
        'update_data_ortu': 'warning',
        'update_data_alamat': 'warning',
        'update_data_pendidikan': 'warning',
        'delete_dokumen': 'danger',
        'view_page': 'info',
        'download': 'info'
    };
    return colors[type] || 'info';
}

function getActivityIcon(type) {
    const icons = {
        'login': 'fas fa-sign-in-alt',
        'logout': 'fas fa-sign-out-alt',
        'upload_foto': 'fas fa-camera',
        'upload_dokumen': 'fas fa-file-upload',
        'update_data_diri': 'fas fa-user-edit',
        'update_data_ortu': 'fas fa-users',
        'update_data_alamat': 'fas fa-map-marker-alt',
        'update_data_pendidikan': 'fas fa-graduation-cap',
        'delete_dokumen': 'fas fa-trash',
        'view_page': 'fas fa-eye',
        'download': 'fas fa-download'
    };
    return icons[type] || 'fas fa-info-circle';
}

function formatActivityType(type) {
    const names = {
        'login': 'Login',
        'logout': 'Logout',
        'upload_foto': 'Upload Foto',
        'upload_dokumen': 'Upload Dokumen',
        'update_data_diri': 'Update Data Diri',
        'update_data_ortu': 'Update Data Orang Tua',
        'update_data_alamat': 'Update Alamat',
        'update_data_pendidikan': 'Update Pendidikan',
        'delete_dokumen': 'Hapus Dokumen',
        'view_page': 'Lihat Halaman',
        'download': 'Download'
    };
    return names[type] || type;
}

function formatFieldName(field) {
    return field.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
}

function capitalizeFirst(str) {
    if (!str) return '';
    return str.charAt(0).toUpperCase() + str.slice(1);
}

function getDeviceIcon(type) {
    const icons = {
        'desktop': 'fas fa-desktop',
        'mobile': 'fas fa-mobile-alt',
        'tablet': 'fas fa-tablet-alt'
    };
    return icons[type] || 'fas fa-question-circle';
}

function getBrowserIcon(browser) {
    if (!browser) return 'fas fa-globe';
    browser = browser.toLowerCase();
    if (browser.includes('chrome')) return 'fab fa-chrome';
    if (browser.includes('firefox')) return 'fab fa-firefox';
    if (browser.includes('safari')) return 'fab fa-safari';
    if (browser.includes('edge')) return 'fab fa-edge';
    if (browser.includes('opera')) return 'fab fa-opera';
    return 'fas fa-globe';
}

function formatLocationSourceBadge(source) {
    const map = {
        payload: ['success', 'GPS dikirim form'],
        request: ['success', 'GPS request'],
        header: ['success', 'GPS header'],
        session: ['primary', 'GPS session'],
        geoip: ['warning', 'GeoIP'],
        private_ip: ['secondary', 'IP private'],
        missing: ['secondary', 'Tidak ada'],
        'N/A': ['secondary', 'N/A']
    };
    const config = map[source] || ['secondary', source];
    return `<span class="badge badge-${config[0]}">${config[1]}</span>`;
}

function formatLocationStatusBadge(status) {
    const map = {
        device_location: ['success', 'GPS perangkat'],
        geoip_fallback: ['warning', 'Fallback GeoIP'],
        private_ip_unresolvable: ['warning', 'IP private, perlu GPS'],
        missing_optional: ['secondary', 'Tidak ada, tapi opsional'],
        missing_required: ['danger', 'Wajib, tapi kosong'],
        missing: ['secondary', 'Tidak tersedia']
    };
    const config = map[status] || ['secondary', status];
    return `<span class="badge badge-${config[0]}">${config[1]}</span>`;
}

function formatIpSourceBadge(source) {
    const map = {
        'cf-connecting-ip': ['info', 'Cloudflare'],
        'true-client-ip': ['info', 'True-Client-IP'],
        'x-real-ip': ['info', 'X-Real-IP'],
        'x-forwarded-for': ['success', 'X-Forwarded-For'],
        'forwarded': ['success', 'Forwarded'],
        'remote-addr': ['secondary', 'Remote Addr']
    };
    const config = map[source] || ['secondary', source];
    return `<span class="badge badge-${config[0]}">${config[1]}</span>`;
}

function exportLogs() {
    let params = new URLSearchParams({
        date_from: $('#date_from').val(),
        date_to: $('#date_to').val(),
        activity_type: $('#activity_type').val(),
        device_type: $('#device_type').val()
    });
    
    toastr.info('Mengunduh file CSV...');
    window.location.href = '{{ route('admin.activity-logs.export') }}?' + params.toString();
}

function filterSiswaOnly() {
    $('#user_role').val('siswa');
    applyFilter();
    toastr.info('Menampilkan aktivitas siswa saja');
}

function filterLoginOnly() {
    $('#activity_type').val('login');
    applyFilter();
    toastr.info('Menampilkan aktivitas login saja');
}
</script>
@stop
