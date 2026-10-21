@extends('adminlte::page')

@section('title', 'Surat Keterangan Siswa')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="m-0 text-dark"><i class="fas fa-file-signature text-primary mr-2"></i>Surat Keterangan Siswa</h1>
            <small class="text-muted">Layanan Surat Tata Usaha - Penerbitan Surat Keterangan Aktif Siswa</small>
        </div>
        <div>
            @can('manage-layanan-surat')
                <a href="{{ route('admin.surat-keterangan.template') }}" class="btn btn-outline-secondary btn-sm mr-1">
                    <i class="fas fa-file-alt mr-1"></i> Kelola Template
                </a>
                <a href="{{ route('admin.surat-keterangan.create') }}" class="btn btn-primary btn-sm">
                    <i class="fas fa-plus mr-1"></i> Buat Surat Baru
                </a>
            @endcan
        </div>
    </div>
@stop

@section('plugins.Datatables', true)

@section('content')
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    <div class="card card-outline card-primary shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table id="surat-table" class="table table-bordered table-striped table-hover w-100">
                    <thead class="thead-light">
                        <tr>
                            <th width="35" class="text-center">No</th>
                            <th width="190">Nomor Surat</th>
                            <th width="90">Tanggal</th>
                            <th>Nama Siswa</th>
                            <th width="80" class="text-center">Kelas</th>
                            <th>Keperluan</th>
                            <th width="90" class="text-center">Status</th>
                            <th width="120" class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
@stop

@section('js')
    <script>
        $(function() {
            var table = $('#surat-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('admin.surat-keterangan.index') }}",
                columns: [
                    { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, className: 'text-center' },
                    { data: 'nomor', name: 'nomor_surat' },
                    { data: 'tanggal', name: 'tanggal_surat' },
                    { data: 'siswa_nama', name: 'siswa.nama_lengkap' },
                    { data: 'kelas', name: 'data_tambahan', className: 'text-center' },
                    { data: 'keperluan', name: 'keperluan' },
                    { data: 'status_badge', name: 'status', className: 'text-center' },
                    { data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-center' }
                ],
                order: [[2, 'desc']],
                language: {
                    emptyTable: "Belum ada data surat keterangan",
                    info: "Menampilkan _START_ s/d _END_ dari _TOTAL_ data",
                    infoEmpty: "Menampilkan 0 data",
                    lengthMenu: "Tampilkan _MENU_ data",
                    loadingRecords: "Memuat...",
                    processing: "Memproses...",
                    search: "Cari:",
                    zeroRecords: "Tidak ditemukan data yang sesuai",
                    paginate: {
                        first: "Pertama",
                        last: "Terakhir",
                        next: "&raquo;",
                        previous: "&laquo;"
                    }
                }
            });

            // Delete handler
            $(document).on('click', '.btn-delete', function() {
                var id = $(this).data('id');
                if (confirm('Apakah Anda yakin ingin menghapus surat keterangan ini?')) {
                    $.ajax({
                        url: '{{ route("admin.surat-keterangan.index") }}/' + id,
                        type: 'DELETE',
                        data: { _token: '{{ csrf_token() }}' },
                        success: function(response) {
                            if (response.success) {
                                table.ajax.reload();
                                alert(response.message);
                            }
                        },
                        error: function() {
                            alert('Gagal menghapus surat keterangan.');
                        }
                    });
                }
            });
        });
    </script>
@stop