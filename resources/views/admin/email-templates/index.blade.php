@extends('adminlte::page')

@section('title', 'Template Email')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1><i class="fas fa-envelope-open-text text-primary"></i> Template Email</h1>
        <div>
            <button type="button" class="btn btn-success btn-sm" id="btnSeedDefaults">
                <i class="fas fa-sync"></i> Muat Default
            </button>
            <a href="{{ route('admin.email-templates.create') }}" class="btn btn-primary btn-sm">
                <i class="fas fa-plus"></i> Buat Template
            </a>
        </div>
    </div>
@stop

@section('content')
    {{-- Statistics --}}
    <div class="row mb-3">
        <div class="col-md-2 col-sm-4 col-6">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>{{ $stats['total'] }}</h3>
                    <p>Total Template</p>
                </div>
                <div class="icon"><i class="fas fa-envelope"></i></div>
            </div>
        </div>
        <div class="col-md-2 col-sm-4 col-6">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>{{ $stats['active'] }}</h3>
                    <p>Aktif</p>
                </div>
                <div class="icon"><i class="fas fa-check-circle"></i></div>
            </div>
        </div>
        <div class="col-md-2 col-sm-4 col-6">
            <div class="small-box bg-danger">
                <div class="inner">
                    <h3>{{ $stats['inactive'] }}</h3>
                    <p>Nonaktif</p>
                </div>
                <div class="icon"><i class="fas fa-times-circle"></i></div>
            </div>
        </div>
        <div class="col-md-2 col-sm-4 col-6">
            <div class="small-box bg-primary">
                <div class="inner">
                    <h3>{{ $stats['system'] }}</h3>
                    <p>Sistem</p>
                </div>
                <div class="icon"><i class="fas fa-cog"></i></div>
            </div>
        </div>
        <div class="col-md-2 col-sm-4 col-6">
            <div class="small-box bg-secondary">
                <div class="inner">
                    <h3>{{ $stats['custom'] }}</h3>
                    <p>Custom</p>
                </div>
                <div class="icon"><i class="fas fa-user-edit"></i></div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <div class="row">
                <div class="col-md-3 mb-2">
                    <select class="form-control form-control-sm" id="filterStatus">
                        <option value="">Semua Status</option>
                        <option value="1">Aktif</option>
                        <option value="0">Nonaktif</option>
                    </select>
                </div>
                <div class="col-md-3 mb-2">
                    <select class="form-control form-control-sm" id="filterType">
                        <option value="">Semua Tipe</option>
                        <option value="system">Sistem</option>
                        <option value="custom">Custom</option>
                    </select>
                </div>
                <div class="col-md-3 mb-2">
                    <button type="button" class="btn btn-outline-secondary btn-sm" id="btnReset">
                        <i class="fas fa-undo"></i> Reset Filter
                    </button>
                </div>
            </div>
        </div>
        <div class="card-body">
            <table id="templatesTable" class="table table-bordered table-striped table-sm">
                <thead>
                    <tr>
                        <th style="width: 40px">No</th>
                        <th>Kode</th>
                        <th>Nama</th>
                        <th>Subject</th>
                        <th style="width: 80px">Status</th>
                        <th style="width: 80px">Tipe</th>
                        <th style="width: 120px">Terakhir Update</th>
                        <th style="width: 130px">Aksi</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>

    {{-- Preview Modal --}}
    <div class="modal fade" id="previewModal" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header bg-info">
                    <h5 class="modal-title text-white"><i class="fas fa-eye"></i> Preview Template</h5>
                    <button type="button" class="close text-white" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="font-weight-bold">Subject:</label>
                        <div id="previewSubject" class="border rounded p-2 bg-light"></div>
                    </div>
                    <div>
                        <label class="font-weight-bold">Isi Email:</label>
                        <div class="border rounded">
                            <iframe id="previewFrame" style="width: 100%; height: 400px; border: none;"></iframe>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>
@stop

@section('css')
<style>
    .small-box .inner h3 { font-size: 1.8rem; }
    .small-box .inner p { font-size: 0.85rem; }
    .small-box .icon { font-size: 50px; top: 5px; }
    .table td { vertical-align: middle; }
</style>
@stop

@section('js')
<script>
$(function() {
    // Initialize DataTable
    var table = $('#templatesTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route("admin.email-templates.index") }}',
            type: 'GET',
            data: function(d) {
                d.status = $('#filterStatus').val();
                d.type = $('#filterType').val();
            },
            error: function(xhr, error, thrown) {
                console.error('DataTables Error:', error, thrown);
                console.log('Response:', xhr.responseText);
            }
        },
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'code', name: 'code' },
            { data: 'name', name: 'name' },
            { data: 'subject', name: 'subject' },
            { data: 'is_active_badge', name: 'is_active', orderable: false, searchable: false },
            { data: 'is_system_badge', name: 'is_system', orderable: false, searchable: false },
            { data: 'updated_at_formatted', name: 'updated_at' },
            { data: 'action', name: 'action', orderable: false, searchable: false }
        ],
        order: [[1, 'asc']],
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/id.json'
        }
    });

    // Filter change
    $('#filterStatus, #filterType').on('change', function() {
        table.ajax.reload();
    });

    // Reset filter
    $('#btnReset').on('click', function() {
        $('#filterStatus, #filterType').val('');
        table.ajax.reload();
    });

    // Preview
    $(document).on('click', '.btn-preview', function() {
        var id = $(this).data('id');
        $.get('{{ url("admin/email-templates") }}/' + id + '/preview', function(response) {
            if (response.success) {
                $('#previewSubject').text(response.subject);
                var iframe = document.getElementById('previewFrame');
                iframe.srcdoc = response.body;
                $('#previewModal').modal('show');
            }
        });
    });

    // Duplicate
    $(document).on('click', '.btn-duplicate', function() {
        var id = $(this).data('id');
        if (confirm('Duplikasi template ini?')) {
            $.post('{{ url("admin/email-templates") }}/' + id + '/duplicate', {
                _token: '{{ csrf_token() }}'
            }, function(response) {
                if (response.success) {
                    toastr.success(response.message);
                    window.location.href = response.redirect;
                }
            });
        }
    });

    // Delete
    $(document).on('click', '.btn-delete', function() {
        var id = $(this).data('id');
        var name = $(this).data('name');
        
        if (confirm('Hapus template "' + name + '"?')) {
            $.ajax({
                url: '{{ url("admin/email-templates") }}/' + id,
                type: 'DELETE',
                data: { _token: '{{ csrf_token() }}' },
                success: function(response) {
                    if (response.success) {
                        toastr.success(response.message);
                        table.ajax.reload();
                    } else {
                        toastr.error(response.message);
                    }
                },
                error: function(xhr) {
                    toastr.error(xhr.responseJSON?.message || 'Terjadi kesalahan');
                }
            });
        }
    });

    // Seed defaults
    $('#btnSeedDefaults').on('click', function() {
        if (confirm('Muat template default? Template yang sudah ada akan diperbarui.')) {
            $.post('{{ route("admin.email-templates.seed-defaults") }}', {
                _token: '{{ csrf_token() }}'
            }, function(response) {
                if (response.success) {
                    toastr.success(response.message);
                    table.ajax.reload();
                }
            });
        }
    });
});
</script>
@stop
