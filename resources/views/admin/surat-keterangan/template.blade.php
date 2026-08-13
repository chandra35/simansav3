@extends('adminlte::page')

@section('title', 'Template Surat')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1><i class="fas fa-file-alt mr-2"></i>Template Surat</h1>
        @can('manage-layanan-surat')<a href="{{ route('admin.surat-keterangan.template.create') }}" class="btn btn-primary">
            <i class="fas fa-plus mr-1"></i> Tambah Template
        </a>@endcan
    </div>
@stop

@section('content')
    <div class="card">
        <div class="card-body">
            <table id="template-table" class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th width="30">No</th>
                        <th>Kode</th>
                        <th>Nama Template</th>
                        <th>Kategori</th>
                        <th width="80">Status</th>
                        <th width="120">Aksi</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
@stop

@section('css')
    <link rel="stylesheet" href="{{ asset('vendor/datatables/css/dataTables.bootstrap4.min.css') }}">
@stop

@section('js')
    <script src="{{ asset('vendor/datatables/js/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('vendor/datatables/js/dataTables.bootstrap4.min.js') }}"></script>
    <script>
        $(function() {
            var table = $('#template-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('admin.surat-keterangan.template') }}",
                columns: [
                    { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                    { data: 'kode', name: 'kode' },
                    { data: 'nama', name: 'nama' },
                    { data: 'kategori_label', name: 'kategori' },
                    { data: 'status', name: 'is_aktif' },
                    { data: 'action', name: 'action', orderable: false, searchable: false }
                ],
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.10.24/i18n/Indonesian.json'
                }
            });

            // Delete handler
            $(document).on('click', '.btn-delete', function() {
                var id = $(this).data('id');
                if (confirm('Apakah Anda yakin ingin menghapus template ini?')) {
                    $.ajax({
                        url: '{{ route("admin.surat-keterangan.template") }}/' + id,
                        type: 'DELETE',
                        data: { _token: '{{ csrf_token() }}' },
                        success: function(response) {
                            if (response.success) {
                                table.ajax.reload();
                                toastr.success(response.message);
                            }
                        }
                    });
                }
            });
        });
    </script>
@stop
