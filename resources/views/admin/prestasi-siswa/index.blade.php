@extends('adminlte::page')

@section('title', 'Prestasi Siswa')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1><i class="fas fa-trophy mr-2"></i>Prestasi Siswa</h1>
        @can('create-prestasi-siswa')
            <a href="{{ route('admin.prestasi-siswa.create') }}" class="btn btn-primary">
                <i class="fas fa-plus mr-1"></i> Tambah Prestasi
            </a>
        @endcan
    </div>
@stop

@section('content')
    <div class="card">
        <div class="card-body">
            <table id="prestasi-table" class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th width="30">No</th>
                        <th>Nama Siswa</th>
                        <th>Nama Prestasi</th>
                        <th width="100">Tingkat</th>
                        <th width="100">Peringkat</th>
                        <th width="100">Tanggal</th>
                        <th width="100">Verifikasi</th>
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
            var table = $('#prestasi-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('admin.prestasi-siswa.index') }}",
                columns: [
                    { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                    { data: 'siswa_nama', name: 'siswa.nama' },
                    { data: 'nama_prestasi', name: 'nama_prestasi' },
                    { data: 'tingkat_label', name: 'tingkat' },
                    { data: 'peringkat_label', name: 'peringkat' },
                    { data: 'tanggal_prestasi', name: 'tanggal_prestasi' },
                    { data: 'verified', name: 'is_verified' },
                    { data: 'action', name: 'action', orderable: false, searchable: false }
                ],
                order: [[5, 'desc']],
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.10.24/i18n/Indonesian.json'
                }
            });

            // Verify handler
            $(document).on('click', '.btn-verify', function() {
                var id = $(this).data('id');
                if (confirm('Apakah Anda yakin ingin memverifikasi prestasi ini?')) {
                    $.ajax({
                        url: '{{ route("admin.prestasi-siswa.index") }}/' + id + '/verify',
                        type: 'POST',
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

            // Delete handler
            $(document).on('click', '.btn-delete', function() {
                var id = $(this).data('id');
                if (confirm('Apakah Anda yakin ingin menghapus prestasi ini?')) {
                    $.ajax({
                        url: '{{ route("admin.prestasi-siswa.index") }}/' + id,
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
