@extends('adminlte::page')

@section('title', 'Monitoring Users')

@section('content_header')
    <div class="monitoring-hero">
        <div class="monitoring-hero__main">
            <div class="monitoring-hero__eyebrow">
                <i class="fas fa-desktop"></i>
                Laporan & Monitoring
            </div>
            <h1 class="monitoring-hero__title">Monitoring Users</h1>
            <p class="monitoring-hero__subtitle">
                Pantau sesi aktif, perangkat yang digunakan, dan aktivitas pengguna dari satu halaman pengawasan yang lebih jelas dan modern.
            </p>
        </div>
        <div class="monitoring-hero__side">
            <div class="monitoring-hero-chip">
                <span class="monitoring-hero-chip__label">Users Online</span>
                <span class="monitoring-hero-chip__value" id="hero-online-count">{{ $onlineUsers }}</span>
            </div>
            <div class="monitoring-hero-chip">
                <span class="monitoring-hero-chip__label">Auto Refresh</span>
                <span class="monitoring-hero-chip__value"><span id="countdown">30</span> detik</span>
            </div>
        </div>
    </div>
@stop

@section('content')
    {{-- Statistics Cards --}}
    <div class="row mb-4">
        <div class="col-md-6 col-xl-4 mb-4">
            <div class="monitoring-stat-card monitoring-stat-card--info">
                <div class="monitoring-stat-card__icon"><i class="fas fa-users"></i></div>
                <div class="monitoring-stat-card__body">
                    <div class="monitoring-stat-card__label">Users Online</div>
                    <div class="monitoring-stat-card__value" id="online-count">{{ $onlineUsers }}</div>
                    <div class="monitoring-stat-card__desc">Pengguna yang terdeteksi aktif dalam jendela sesi terbaru.</div>
                </div>
            </div>
        </div>
        
        <div class="col-md-6 col-xl-4 mb-4">
            <div class="monitoring-stat-card monitoring-stat-card--success">
                <div class="monitoring-stat-card__icon"><i class="fas fa-user-check"></i></div>
                <div class="monitoring-stat-card__body">
                    <div class="monitoring-stat-card__label">Total Users</div>
                    <div class="monitoring-stat-card__value">{{ $totalUsers }}</div>
                    <div class="monitoring-stat-card__desc">Seluruh akun yang tercatat di SIMANSA dan dapat dimonitor.</div>
                </div>
            </div>
        </div>
        
        <div class="col-md-6 col-xl-4 mb-4">
            <div class="monitoring-stat-card monitoring-stat-card--warning">
                <div class="monitoring-stat-card__icon"><i class="fas fa-network-wired"></i></div>
                <div class="monitoring-stat-card__body">
                    <div class="monitoring-stat-card__label">Total Sessions</div>
                    <div class="monitoring-stat-card__value">{{ $totalSessions }}</div>
                    <div class="monitoring-stat-card__desc">Akumulasi sesi yang masih tersimpan dan bisa ditinjau operator.</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Main Table --}}
    <div class="card monitoring-management-card">
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
                    <i class="fas fa-clock"></i> Auto-refresh: <span id="countdown-inline">30</span>s
                </span>
            </div>
        </div>
        <div class="card-body">
            <p class="monitoring-table-note">
                Daftar ini membantu memantau perangkat, IP, lokasi, dan aktivitas terakhir pengguna. Gunakan tombol refresh bila ingin menarik data terbaru di luar interval otomatis.
            </p>
            <div class="table-responsive">
                <table id="monitoring-table" class="table table-bordered table-striped table-hover">
                    <thead>
                        <tr>
                            <th width="4%">No</th>
                            <th width="18%">User</th>
                            <th width="8%">Status</th>
                            <th width="18%">Device & Browser</th>
                            <th width="15%">IP & Lokasi</th>
                            <th width="18%">Halaman Diakses</th>
                            <th width="11%">Last Activity</th>
                            <th width="8%">Aksi</th>
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
    .monitoring-hero {
        display: grid;
        grid-template-columns: minmax(0, 1.35fr) minmax(280px, .85fr);
        gap: 1.1rem;
        align-items: stretch;
        margin-bottom: 1.1rem;
    }

    .monitoring-hero__main {
        background: linear-gradient(135deg, rgba(37, 99, 235, .16), rgba(13, 148, 136, .10));
        border: 1px solid rgba(148, 163, 184, .16);
        border-radius: 26px;
        padding: 1.35rem 1.45rem;
        box-shadow: 0 20px 45px rgba(15, 23, 42, .06);
    }

    .monitoring-hero__eyebrow {
        display: inline-flex;
        align-items: center;
        gap: .45rem;
        color: #334155;
        font-size: .82rem;
        font-weight: 700;
        letter-spacing: .05em;
        text-transform: uppercase;
        margin-bottom: .65rem;
    }

    .monitoring-hero__title {
        font-size: 2rem;
        font-weight: 800;
        color: #0f172a;
        line-height: 1.1;
        margin: 0 0 .55rem 0;
    }

    .monitoring-hero__subtitle {
        color: #475569;
        font-size: 1rem;
        line-height: 1.7;
        margin: 0;
        max-width: 760px;
    }

    .monitoring-hero__side {
        display: grid;
        gap: .9rem;
    }

    .monitoring-hero-chip {
        background: rgba(255, 255, 255, .92);
        border: 1px solid rgba(148, 163, 184, .18);
        border-radius: 20px;
        padding: 1rem 1.1rem;
        box-shadow: 0 16px 35px rgba(15, 23, 42, .06);
    }

    .monitoring-hero-chip__label {
        display: block;
        color: #64748b;
        font-size: .78rem;
        font-weight: 700;
        letter-spacing: .06em;
        text-transform: uppercase;
        margin-bottom: .35rem;
    }

    .monitoring-hero-chip__value {
        display: block;
        color: #0f172a;
        font-size: 1.45rem;
        font-weight: 800;
        line-height: 1.2;
    }

    .monitoring-stat-card {
        position: relative;
        overflow: hidden;
        min-height: 176px;
        border: 0;
        border-radius: 22px;
        padding: 1.25rem 1.2rem 1rem;
        color: #fff;
        box-shadow: 0 24px 50px rgba(15, 23, 42, .10);
        display: flex;
        align-items: flex-start;
        gap: 1rem;
    }

    .monitoring-stat-card::after {
        content: "";
        position: absolute;
        right: -32px;
        bottom: -40px;
        width: 132px;
        height: 132px;
        border-radius: 999px;
        background: rgba(255, 255, 255, .12);
    }

    .monitoring-stat-card--info { background: linear-gradient(135deg, #3b82f6, #60a5fa); }
    .monitoring-stat-card--success { background: linear-gradient(135deg, #10b981, #34d399); }
    .monitoring-stat-card--warning { background: linear-gradient(135deg, #f59e0b, #fbbf24); }

    .monitoring-stat-card__icon {
        width: 56px;
        height: 56px;
        border-radius: 18px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: rgba(255, 255, 255, .16);
        font-size: 1.25rem;
        position: relative;
        z-index: 1;
        flex: 0 0 56px;
    }

    .monitoring-stat-card__body {
        position: relative;
        z-index: 1;
        flex: 1 1 auto;
        min-width: 0;
    }

    .monitoring-stat-card__label {
        font-size: .78rem;
        font-weight: 700;
        letter-spacing: .08em;
        text-transform: uppercase;
        opacity: .9;
        margin-bottom: .55rem;
    }

    .monitoring-stat-card__value {
        font-size: 2rem;
        font-weight: 800;
        line-height: 1;
        margin-bottom: .7rem;
    }

    .monitoring-stat-card__desc {
        opacity: .92;
        line-height: 1.5;
        font-size: .92rem;
    }

    .monitoring-management-card {
        border: 0;
        border-radius: 24px;
        box-shadow: 0 22px 48px rgba(15, 23, 42, .08);
        overflow: hidden;
    }

    .monitoring-management-card .card-header {
        background: linear-gradient(135deg, rgba(37, 99, 235, .98), rgba(13, 148, 136, .9));
        color: #fff;
        border-bottom: 0;
        padding: 1rem 1.25rem;
    }

    .monitoring-table-note {
        color: #64748b;
        font-size: .92rem;
        line-height: 1.5;
        margin-bottom: 1rem;
    }

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

    @media (max-width: 991.98px) {
        .monitoring-hero {
            grid-template-columns: 1fr;
        }

        .monitoring-hero__side {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 575.98px) {
        .monitoring-hero__title {
            font-size: 1.7rem;
        }

        .monitoring-hero__side {
            grid-template-columns: 1fr;
        }

        .monitoring-stat-card {
            flex-direction: column;
            gap: .9rem;
        }
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
                    data: 'current_page',
                    name: 'current_page',
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
            $('#countdown-inline').text(countdown);
            
            if (countdown <= 0) {
                refreshTable();
                resetCountdown();
            }
        }, 1000);
    }
    
    function resetCountdown() {
        countdown = 30;
        $('#countdown').text(countdown);
        $('#countdown-inline').text(countdown);
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
