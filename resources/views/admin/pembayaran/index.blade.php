@extends('adminlte::page')

@section('title', 'Pembayaran')

@section('content_header')
    <h1><i class="fas fa-cash-register mr-2"></i>Pembayaran</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-header">
            <div class="row">
                <div class="col-md-4">
                    <select id="filter-status" class="form-control">
                        <option value="">-- Semua Status --</option>
                        @foreach($status as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <button id="btn-filter" class="btn btn-info">
                        <i class="fas fa-filter mr-1"></i> Filter
                    </button>
                </div>
            </div>
        </div>
        <div class="card-body">
            <table id="pembayaran-table" class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th width="30">No</th>
                        <th>No. Transaksi</th>
                        <th>Siswa</th>
                        <th>Jenis</th>
                        <th>Jumlah</th>
                        <th>Metode</th>
                        <th>Tanggal</th>
                        <th width="100">Status</th>
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
            var table = $('#pembayaran-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('admin.pembayaran.index') }}",
                    data: function(d) {
                        d.status = $('#filter-status').val();
                    }
                },
                columns: [
                    { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                    { data: 'nomor', name: 'nomor_transaksi' },
                    { data: 'siswa_nama', name: 'tagihan.siswa.nama' },
                    { data: 'jenis', name: 'tagihan.jenisPembayaran.nama' },
                    { data: 'jumlah', name: 'jumlah_bayar' },
                    { data: 'metode', name: 'metode_pembayaran' },
                    { data: 'tanggal', name: 'tanggal_bayar' },
                    { data: 'status_badge', name: 'status' },
                    { data: 'action', name: 'action', orderable: false, searchable: false }
                ],
                order: [[6, 'desc']],
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.10.24/i18n/Indonesian.json'
                }
            });

            $('#btn-filter').click(function() {
                table.ajax.reload();
            });

            // Verify handler
            $(document).on('click', '.btn-verify', function() {
                var id = $(this).data('id');
                if (confirm('Apakah Anda yakin ingin memverifikasi pembayaran ini?')) {
                    $.ajax({
                        url: '{{ route("admin.pembayaran.index") }}/' + id + '/verify',
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
                var alasan = prompt('Masukkan alasan penolakan:');
                if (alasan) {
                    $.ajax({
                        url: '{{ route("admin.pembayaran.index") }}/' + id + '/reject',
                        type: 'POST',
                        data: { 
                            _token: '{{ csrf_token() }}',
                            alasan: alasan
                        },
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
