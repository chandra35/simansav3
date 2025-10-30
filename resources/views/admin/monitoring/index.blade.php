@extends('adminlte::page')

@section('title', 'Monitoring Users')

@section('content_header')
    <div class="row">
        <div class="col-sm-6">
            <h1 class="m-0">Monitoring Users</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item active">Monitoring Users</li>
            </ol>
        </div>
    </div>
@stop

@section('content')
    {{-- Statistics Cards --}}
    <div class="row">
        <div class="col-lg-4 col-6">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3 id="online-count">{{ $onlineUsers }}</h3>
                    <p>Users Online</p>
                </div>
                <div class="icon">
                    <i class="fas fa-users"></i>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4 col-6">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>{{ $totalUsers }}</h3>
                    <p>Total Users</p>
                </div>
                <div class="icon">
                    <i class="fas fa-user-check"></i>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4 col-6">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3>{{ $totalSessions }}</h3>
                    <p>Total Sessions</p>
                </div>
                <div class="icon">
                    <i class="fas fa-network-wired"></i>
                </div>
            </div>
        </div>
    </div>

    {{-- Main Table --}}
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-desktop mr-1"></i>
                User Activity Monitor
            </h3>
            <div class="card-tools">
                <button type="button" class="btn btn-sm btn-primary" id="refresh-btn">
                    <i class="fas fa-sync-alt"></i> Refresh
                </button>
                <span class="badge badge-info ml-2">
                    <i class="fas fa-clock"></i> Auto-refresh: <span id="countdown">30</span>s
                </span>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="monitoring-table" class="table table-bordered table-striped table-hover">
                    <thead>
                        <tr>
                            <th width="5%">No</th>
                            <th width="20%">User</th>
                            <th width="10%">Status</th>
                            <th width="20%">Device & Browser</th>
                            <th width="20%">IP & Location</th>
                            <th width="15%">Last Activity</th>
                            <th width="10%">Action</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>

    {{-- Detail Modal --}}
    <div class="modal fade" id="detailModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header bg-info">
                    <h5 class="modal-title">
                        <i class="fas fa-user-circle"></i> User Session Details
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div id="detail-content">
                        <div class="text-center">
                            <i class="fas fa-spinner fa-spin fa-3x text-primary"></i>
                            <p class="mt-3">Loading...</p>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times"></i> Close
                    </button>
                </div>
            </div>
        </div>
    </div>
@stop

@section('plugins.Datatables', true)
@section('plugins.Sweetalert2', true)

@section('css')
<style>
    .badge {
        font-size: 0.85rem;
    }
    .table td {
        vertical-align: middle;
    }
    #monitoring-table tbody tr {
        cursor: pointer;
    }
    #monitoring-table tbody tr:hover {
        background-color: #f8f9fa;
    }
    .user-avatar {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        margin-right: 10px;
    }
    .session-item {
        border-left: 3px solid #007bff;
        padding-left: 15px;
        margin-bottom: 15px;
    }
    .session-item.offline {
        border-left-color: #6c757d;
        opacity: 0.7;
    }
</style>
@stop

@section('js')
<script>
$(document).ready(function() {
    let table;
    let countdown = 30;
    let countdownInterval;
    
    // Initialize DataTable
    function initTable() {
        table = $('#monitoring-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: '{{ route('admin.monitoring.users') }}',
                type: 'GET'
            },
            columns: [
                {
                    data: 'DT_RowIndex',
                    name: 'DT_RowIndex',
                    orderable: false,
                    searchable: false
                },
                {
                    data: 'user_info',
                    name: 'user_info',
                    orderable: false
                },
                {
                    data: 'status',
                    name: 'status',
                    orderable: false
                },
                {
                    data: 'device_info',
                    name: 'device_info',
                    orderable: false
                },
                {
                    data: 'location_info',
                    name: 'location_info',
                    orderable: false
                },
                {
                    data: 'last_activity',
                    name: 'last_activity'
                },
                {
                    data: 'action',
                    name: 'action',
                    orderable: false,
                    searchable: false
                }
            ],
            order: [[5, 'desc']],
            language: {
                url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/id.json'
            },
            pageLength: 25,
            drawCallback: function() {
                $('[data-toggle="tooltip"]').tooltip();
            }
        });
    }
    
    initTable();
    
    // Refresh table
    function refreshTable() {
        table.ajax.reload(null, false);
        updateOnlineCount();
    }
    
    // Update online count
    function updateOnlineCount() {
        $.get('{{ route('admin.monitoring.online-count') }}', function(data) {
            $('#online-count').text(data.online_count);
        });
    }
    
    // Manual refresh button
    $('#refresh-btn').click(function() {
        refreshTable();
        resetCountdown();
        
        // Visual feedback
        $(this).find('i').addClass('fa-spin');
        setTimeout(() => {
            $(this).find('i').removeClass('fa-spin');
        }, 1000);
    });
    
    // Auto refresh countdown
    function startCountdown() {
        countdownInterval = setInterval(function() {
            countdown--;
            $('#countdown').text(countdown);
            
            if (countdown <= 0) {
                refreshTable();
                resetCountdown();
            }
        }, 1000);
    }
    
    function resetCountdown() {
        countdown = 30;
        $('#countdown').text(countdown);
    }
    
    startCountdown();
    
    // View detail
    $(document).on('click', '.view-detail', function(e) {
        e.stopPropagation();
        const userId = $(this).data('user-id');
        
        $('#detailModal').modal('show');
        $('#detail-content').html(`
            <div class="text-center">
                <i class="fas fa-spinner fa-spin fa-3x text-primary"></i>
                <p class="mt-3">Loading...</p>
            </div>
        `);
        
        $.get(`/admin/monitoring/users/${userId}`, function(data) {
            let html = `
                <div class="row">
                    <div class="col-md-12">
                        <h5><i class="fas fa-user"></i> ${data.user.name}</h5>
                        <p class="text-muted">${data.user.email} • <span class="badge badge-primary">${data.user.role}</span></p>
                        <hr>
                    </div>
                </div>
            `;
            
            if (data.current_session) {
                const statusBadge = data.current_session.is_online 
                    ? '<span class="badge badge-success"><i class="fas fa-circle"></i> Online</span>'
                    : '<span class="badge badge-secondary"><i class="fas fa-circle"></i> Offline</span>';
                
                html += `
                    <div class="row">
                        <div class="col-md-12">
                            <h6><i class="fas fa-desktop"></i> Current Session ${statusBadge}</h6>
                        </div>
                    </div>
                    <div class="row mt-2">
                        <div class="col-md-6">
                            <strong>Device:</strong> ${data.current_session.device_type}<br>
                            <strong>Browser:</strong> ${data.current_session.browser}<br>
                            <strong>Platform:</strong> ${data.current_session.platform}
                        </div>
                        <div class="col-md-6">
                            <strong>IP Address:</strong> ${data.current_session.ip_address}<br>
                            <strong>Location:</strong> ${data.current_session.location || 'Unknown'}<br>
                            <strong>Last Active:</strong> ${data.current_session.last_activity_human}
                        </div>
                    </div>
                    <hr>
                `;
            }
            
            if (data.recent_sessions.length > 0) {
                html += `
                    <div class="row">
                        <div class="col-md-12">
                            <h6><i class="fas fa-history"></i> Recent Sessions (Last 10)</h6>
                        </div>
                    </div>
                `;
                
                data.recent_sessions.forEach((session, index) => {
                    const sessionClass = session.is_online ? '' : 'offline';
                    const statusBadge = session.is_online 
                        ? '<span class="badge badge-success badge-sm">Online</span>'
                        : '<span class="badge badge-secondary badge-sm">Offline</span>';
                    
                    html += `
                        <div class="session-item ${sessionClass}">
                            <div class="row">
                                <div class="col-md-12">
                                    <strong>#${index + 1}</strong> ${statusBadge}
                                </div>
                            </div>
                            <div class="row mt-2">
                                <div class="col-md-6">
                                    <small>
                                        <i class="fas fa-mobile-alt"></i> ${session.device_type}<br>
                                        <i class="fas fa-globe"></i> ${session.browser}<br>
                                        <i class="fas fa-laptop"></i> ${session.platform}
                                    </small>
                                </div>
                                <div class="col-md-6">
                                    <small>
                                        <i class="fas fa-network-wired"></i> ${session.ip_address}<br>
                                        <i class="fas fa-clock"></i> ${session.last_activity}
                                    </small>
                                </div>
                            </div>
                        </div>
                    `;
                });
            } else {
                html += '<p class="text-muted">No recent sessions found.</p>';
            }
            
            $('#detail-content').html(html);
        }).fail(function() {
            $('#detail-content').html(`
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle"></i> Failed to load user details.
                </div>
            `);
        });
    });
    
    // Cleanup on page unload
    $(window).on('beforeunload', function() {
        clearInterval(countdownInterval);
    });
});
</script>
@stop
