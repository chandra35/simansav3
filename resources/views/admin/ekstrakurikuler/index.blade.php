@extends('adminlte::page')

@section('title', 'Ekstrakurikuler')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1><i class="fas fa-futbol mr-2"></i>Ekstrakurikuler</h1>
        @can('create-ekstrakurikuler')
            <a href="{{ route('admin.ekstrakurikuler.create') }}" class="btn btn-primary">
                <i class="fas fa-plus mr-1"></i> Tambah Ekskul
            </a>
        @endcan
    </div>
@stop

@section('content')
    <div class="card">
        <div class="card-body">
            <table id="ekskul-table" class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th width="30">No</th>
                        <th>Nama Ekskul</th>
                        <th>Pembina</th>
                        <th>Jadwal</th>
                        <th width="100">Anggota</th>
                        <th width="80">Status</th>
                        <th width="150">Aksi</th>
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
            var table = $('#ekskul-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('admin.ekstrakurikuler.index') }}",
                columns: [
                    { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                    { data: 'nama', name: 'nama' },
                    { data: 'pembina_nama', name: 'pembina.nama' },
                    { data: 'jadwal', name: 'hari_kegiatan' },
                    { data: 'jumlah_anggota', name: 'jumlah_anggota', orderable: false },
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
                if (confirm('Apakah Anda yakin ingin menghapus ekstrakurikuler ini?')) {
                    $.ajax({
                        url: '{{ route("admin.ekstrakurikuler.index") }}/' + id,
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
