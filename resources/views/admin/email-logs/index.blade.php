@extends('adminlte::page')

@section('title', 'Log Email - SIMANSA')

@section('content_header')
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1><i class="fas fa-envelope-open-text"></i> Log Email</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item active">Log Email</li>
            </ol>
        </div>
    </div>
@stop

@section('content')
<div class="container-fluid">
    <!-- Statistics Cards -->
    <div class="row">
        <div class="col-lg-3 col-6">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>{{ number_format($stats['total']) }}</h3>
                    <p>Total Email</p>
                </div>
                <div class="icon">
                    <i class="fas fa-envelope"></i>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>{{ number_format($stats['sent']) }}</h3>
                    <p>Terkirim</p>
                </div>
                <div class="icon">
                    <i class="fas fa-check-circle"></i>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-danger">
                <div class="inner">
                    <h3>{{ number_format($stats['failed']) }}</h3>
                    <p>Gagal</p>
                </div>
                <div class="icon">
                    <i class="fas fa-times-circle"></i>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3>{{ number_format($stats['today']) }}</h3>
                    <p>Hari Ini</p>
                </div>
                <div class="icon">
                    <i class="fas fa-calendar-day"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Card -->
    <div class="card card-primary card-outline">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-list"></i> Daftar Log Email
            </h3>
            <div class="card-tools">
                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                    <i class="fas fa-minus"></i>
                </button>
            </div>
        </div>
        <div class="card-body">
            <!-- Filters -->
            <div class="row mb-3">
                <div class="col-md-2">
                    <select id="filterStatus" class="form-control form-control-sm">
                        <option value="">Semua Status</option>
                        <option value="sent">Terkirim</option>
                        <option value="failed">Gagal</option>
                        <option value="pending">Pending</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select id="filterType" class="form-control form-control-sm">
                        <option value="">Semua Tipe</option>
                        <option value="test">Test Email</option>
                        <option value="password_reset">Reset Password</option>
                        <option value="notification">Notifikasi</option>
                        <option value="general">Umum</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <input type="date" id="filterDateFrom" class="form-control form-control-sm" placeholder="Dari Tanggal">
                </div>
                <div class="col-md-2">
                    <input type="date" id="filterDateTo" class="form-control form-control-sm" placeholder="Sampai Tanggal">
                </div>
                <div class="col-md-2">
                    <button type="button" class="btn btn-info btn-sm btn-block" onclick="applyFilter()">
                        <i class="fas fa-filter"></i> Filter
                    </button>
                </div>
                <div class="col-md-2">
                    <button type="button" class="btn btn-warning btn-sm btn-block" onclick="cleanupLogs()">
                        <i class="fas fa-broom"></i> Cleanup
                    </button>
                </div>
            </div>

            <!-- DataTable -->
            <div class="table-responsive">
                <table id="emailLogsTable" class="table table-bordered table-striped table-sm">
                    <thead>
                        <tr>
                            <th width="5%">No</th>
                            <th>Penerima</th>
                            <th>Subject</th>
                            <th width="10%">Tipe</th>
                            <th width="10%">Status</th>
                            <th>Pengirim</th>
                            <th width="12%">Waktu</th>
                            <th width="5%">Aksi</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Detail Modal -->
<div class="modal fade" id="detailModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-info">
                <h5 class="modal-title text-white"><i class="fas fa-envelope-open-text"></i> Detail Email</h5>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-sm table-borderless">
                            <tr><td width="35%"><strong>Penerima:</strong></td><td id="detail-to"></td></tr>
                            <tr><td><strong>Pengirim:</strong></td><td id="detail-from"></td></tr>
                            <tr><td><strong>Subject:</strong></td><td id="detail-subject"></td></tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-sm table-borderless">
                            <tr><td width="35%"><strong>Tipe:</strong></td><td id="detail-type"></td></tr>
                            <tr><td><strong>Status:</strong></td><td id="detail-status"></td></tr>
                            <tr><td><strong>Waktu:</strong></td><td id="detail-time"></td></tr>
                        </table>
                    </div>
                </div>
                
                <div id="detail-error" class="alert alert-danger" style="display: none;">
                    <strong><i class="fas fa-exclamation-triangle"></i> Error:</strong>
                    <span id="detail-error-message"></span>
                </div>

                <hr>
                <h6><i class="fas fa-file-alt"></i> Isi Email:</h6>
                <div id="detail-body" class="border rounded p-3 bg-light" style="max-height: 400px; overflow-y: auto;"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<!-- Cleanup Modal -->
<div class="modal fade" id="cleanupModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title"><i class="fas fa-broom"></i> Cleanup Log Email</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <p>Hapus log email yang lebih lama dari:</p>
                <div class="form-group">
                    <select id="cleanupDays" class="form-control">
                        <option value="7">7 hari</option>
                        <option value="14">14 hari</option>
                        <option value="30" selected>30 hari</option>
                        <option value="60">60 hari</option>
                        <option value="90">90 hari</option>
                    </select>
                </div>
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle"></i> Tindakan ini tidak dapat dibatalkan!
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-warning" onclick="doCleanup()">
                    <i class="fas fa-broom"></i> Hapus Log Lama
                </button>
            </div>
        </div>
    </div>
</div>
@stop

@section('css')
<style>
    .table td, .table th {
        vertical-align: middle;
    }
</style>
@stop

@section('js')
<script>
var table;

$(document).ready(function() {
    table = $('#emailLogsTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route("admin.email-logs.index") }}',
            type: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            },
            data: function(d) {
                d.status = $('#filterStatus').val();
                d.type = $('#filterType').val();
                d.date_from = $('#filterDateFrom').val();
                d.date_to = $('#filterDateTo').val();
            },
            error: function(xhr, error, thrown) {
                console.error('DataTables Error:', error, thrown);
                console.log('Response:', xhr.responseText);
                toastr.error('Gagal memuat data: ' + thrown);
            }
        },
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'to_email', name: 'to_email' },
            { data: 'subject', name: 'subject' },
            { data: 'type_label', name: 'type', orderable: false },
            { data: 'status_badge', name: 'status', orderable: false },
            { data: 'sender_name', name: 'sender_name', orderable: false },
            { data: 'created_at_formatted', name: 'created_at' },
            { data: 'action', name: 'action', orderable: false, searchable: false },
        ],
        order: [[6, 'desc']],
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/id.json'
        }
    });
});

function applyFilter() {
    table.ajax.reload();
}

function showDetail(id) {
    $.get('{{ url("admin/email-logs") }}/' + id, function(response) {
        if (response.success) {
            var data = response.data;
            
            $('#detail-to').html(data.to_email + (data.to_name ? ' (' + data.to_name + ')' : ''));
            $('#detail-from').html(data.from_email + (data.from_name ? ' (' + data.from_name + ')' : ''));
            $('#detail-subject').text(data.subject);
            $('#detail-type').html('<span class="badge badge-info">' + data.type_label + '</span>');
            $('#detail-status').html('<span class="badge badge-' + data.status_badge + '">' + data.status.toUpperCase() + '</span>');
            $('#detail-time').text(data.created_at + (data.sent_at ? ' (Terkirim: ' + data.sent_at + ')' : ''));
            
            if (data.error_message) {
                $('#detail-error').show();
                $('#detail-error-message').text(data.error_message);
            } else {
                $('#detail-error').hide();
            }
            
            if (data.body) {
                // Render HTML body in iframe-like container
                $('#detail-body').html(data.body);
            } else {
                $('#detail-body').html('<em class="text-muted">Tidak ada konten</em>');
            }
            
            $('#detailModal').modal('show');
        }
    });
}

function cleanupLogs() {
    $('#cleanupModal').modal('show');
}

function doCleanup() {
    var days = $('#cleanupDays').val();
    
    $.ajax({
        url: '{{ route("admin.email-logs.cleanup") }}',
        method: 'POST',
        data: {
            _token: '{{ csrf_token() }}',
            days: days
        },
        success: function(response) {
            $('#cleanupModal').modal('hide');
            if (response.success) {
                Swal.fire('Berhasil', response.message, 'success');
                table.ajax.reload();
                // Reload page to update stats
                setTimeout(function() {
                    location.reload();
                }, 1500);
            } else {
                Swal.fire('Gagal', response.message, 'error');
            }
        },
        error: function() {
            Swal.fire('Error', 'Terjadi kesalahan saat cleanup', 'error');
        }
    });
}
</script>
@stop
