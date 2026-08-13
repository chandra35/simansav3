@extends('adminlte::page')

@section('title', 'Surat Keterangan')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1><i class="fas fa-file-signature mr-2"></i>Surat Keterangan</h1>
        @can('manage-layanan-surat')
            <a href="{{ route('admin.surat-keterangan.create') }}" class="btn btn-primary">
                <i class="fas fa-plus mr-1"></i> Buat Surat
            </a>
        @endcan
    </div>
@stop

@section('content')
    <div class="card">
        <div class="card-body">
            <table id="surat-table" class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th width="30">No</th>
                        <th>Nomor Surat</th>
                        <th>Siswa</th>
                        <th>Jenis Surat</th>
                        <th>Tanggal</th>
                        <th width="100">Status</th>
                        <th width="180">Aksi</th>
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
            var table = $('#surat-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('admin.surat-keterangan.index') }}",
                columns: [
                    { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                    { data: 'nomor', name: 'nomor_surat' },
                    { data: 'siswa_nama', name: 'siswa.nama' },
                    { data: 'jenis', name: 'template.nama' },
                    { data: 'tanggal', name: 'tanggal_surat' },
                    { data: 'status_badge', name: 'status' },
                    { data: 'action', name: 'action', orderable: false, searchable: false }
                ],
                order: [[4, 'desc']],
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.10.24/i18n/Indonesian.json'
                }
            });

            // Approve handler
            $(document).on('click', '.btn-approve', function() {
                var id = $(this).data('id');
                if (confirm('Apakah Anda yakin ingin menyetujui surat ini?')) {
                    $.ajax({
                        url: '{{ route("admin.surat-keterangan.index") }}/' + id + '/approve',
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

            // Reject handler
            $(document).on('click', '.btn-reject', function() {
                var id = $(this).data('id');
                if (confirm('Apakah Anda yakin ingin menolak surat ini?')) {
                    $.ajax({
                        url: '{{ route("admin.surat-keterangan.index") }}/' + id + '/reject',
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
                if (confirm('Apakah Anda yakin ingin menghapus surat ini?')) {
                    $.ajax({
                        url: '{{ route("admin.surat-keterangan.index") }}/' + id,
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
