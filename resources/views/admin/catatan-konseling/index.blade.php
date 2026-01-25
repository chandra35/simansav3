@extends('adminlte::page')

@section('title', 'Catatan Konseling')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1><i class="fas fa-comments mr-2"></i>Catatan Konseling (BK)</h1>
        <div>
            <a href="{{ route('admin.catatan-konseling.report-siswa') }}" class="btn btn-info">
                <i class="fas fa-chart-bar mr-1"></i> Laporan Per Siswa
            </a>
            <a href="{{ route('admin.catatan-konseling.create') }}" class="btn btn-primary">
                <i class="fas fa-plus mr-1"></i> Tambah Catatan
            </a>
        </div>
    </div>
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
                    <select id="filter-kategori" class="form-control">
                        <option value="">-- Semua Kategori --</option>
                        @foreach($kategori as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <button id="btn-filter" class="btn btn-info btn-block">
                        <i class="fas fa-filter mr-1"></i> Filter
                    </button>
                </div>
            </div>
        </div>
        <div class="card-body">
            <table id="konseling-table" class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th width="30">No</th>
                        <th>Nama Siswa</th>
                        <th>Konselor</th>
                        <th>Tanggal</th>
                        <th>Jenis</th>
                        <th>Kategori</th>
                        <th width="100">Status</th>
                        <th width="50">🔒</th>
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
            var table = $('#konseling-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('admin.catatan-konseling.index') }}",
                    data: function(d) {
                        d.status = $('#filter-status').val();
                        d.kategori = $('#filter-kategori').val();
                    }
                },
                columns: [
                    { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                    { data: 'siswa_nama', name: 'siswa.nama' },
                    { data: 'konselor_nama', name: 'konselor.nama' },
                    { data: 'tanggal', name: 'tanggal_konseling' },
                    { data: 'jenis_label', name: 'jenis_konseling' },
                    { data: 'kategori_label', name: 'kategori_masalah' },
                    { data: 'status_badge', name: 'status' },
                    { data: 'rahasia', name: 'is_rahasia' },
                    { data: 'action', name: 'action', orderable: false, searchable: false }
                ],
                order: [[3, 'desc']],
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.10.24/i18n/Indonesian.json'
                }
            });

            $('#btn-filter').click(function() {
                table.ajax.reload();
            });

            // Delete handler
            $(document).on('click', '.btn-delete', function() {
                var id = $(this).data('id');
                if (confirm('Apakah Anda yakin ingin menghapus catatan ini?')) {
                    $.ajax({
                        url: '{{ route("admin.catatan-konseling.index") }}/' + id,
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
