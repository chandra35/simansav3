@extends('adminlte::page')

@section('title', 'Pengumuman')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1><i class="fas fa-bullhorn mr-2"></i>Pengumuman</h1>
        <a href="{{ route('admin.pengumuman.create') }}" class="btn btn-primary">
            <i class="fas fa-plus mr-1"></i> Tambah Pengumuman
        </a>
    </div>
@stop

@section('content')
    <div class="card">
        <div class="card-body">
            <table id="pengumuman-table" class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th width="30">No</th>
                        <th>Judul</th>
                        <th width="100">Kategori</th>
                        <th width="100">Prioritas</th>
                        <th width="100">Target</th>
                        <th width="50">Pin</th>
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
            var table = $('#pengumuman-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('admin.pengumuman.index') }}",
                columns: [
                    { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                    { data: 'judul', name: 'judul' },
                    { data: 'kategori_badge', name: 'kategori' },
                    { data: 'prioritas_badge', name: 'prioritas' },
                    { data: 'target', name: 'target' },
                    { data: 'pinned', name: 'is_pinned' },
                    { data: 'status', name: 'status' },
                    { data: 'action', name: 'action', orderable: false, searchable: false }
                ],
                order: [[0, 'desc']],
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.10.24/i18n/Indonesian.json'
                }
            });

            // Delete handler
            $(document).on('click', '.btn-delete', function() {
                var id = $(this).data('id');
                if (confirm('Apakah Anda yakin ingin menghapus pengumuman ini?')) {
                    $.ajax({
                        url: '{{ route("admin.pengumuman.index") }}/' + id,
                        type: 'DELETE',
                        data: { _token: '{{ csrf_token() }}' },
                        success: function(response) {
                            if (response.success) {
                                table.ajax.reload();
                                toastr.success(response.message);
                            }
                        },
                        error: function() {
                            toastr.error('Terjadi kesalahan');
                        }
                    });
                }
            });
        });
    </script>
@stop
