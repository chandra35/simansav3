@extends('adminlte::page')

@section('title', 'Manajemen Kelas')

@section('content_header')
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1><i class="fas fa-chalkboard-teacher"></i> Manajemen Kelas</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item active">Kelas</li>
            </ol>
        </div>
    </div>
@stop

@section('content')
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle"></i> {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    {{-- Filter Card --}}
    <div class="card card-primary card-outline collapsed-card">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-filter"></i> Filter</h3>
            <div class="card-tools">
                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                    <i class="fas fa-plus"></i>
                </button>
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="filter_tahun_pelajaran">Tahun Pelajaran</label>
                        <select class="form-control" id="filter_tahun_pelajaran">
                            <option value="">Semua</option>
                            @foreach($tahunPelajarans as $tp)
                                <option value="{{ $tp->id }}" {{ $tp->is_active ? 'selected' : '' }}>
                                    {{ $tp->nama }} {{ $tp->is_active ? '(Aktif)' : '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <label for="filter_tingkat">Tingkat</label>
                        <select class="form-control" id="filter_tingkat">
                            <option value="">Semua</option>
                            @foreach($tingkatOptions as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <label for="filter_kurikulum">Kurikulum</label>
                        <select class="form-control" id="filter_kurikulum">
                            <option value="">Semua</option>
                            @foreach($kurikulums as $k)
                                <option value="{{ $k->id }}">{{ $k->kode }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <label for="filter_jurusan">Jurusan</label>
                        <select class="form-control" id="filter_jurusan">
                            <option value="">Semua</option>
                            @foreach($jurusans as $j)
                                <option value="{{ $j->id }}">{{ $j->singkatan }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label>&nbsp;</label>
                        <div>
                            <button type="button" class="btn btn-primary" id="btn-filter">
                                <i class="fas fa-search"></i> Terapkan Filter
                            </button>
                            <button type="button" class="btn btn-secondary" id="btn-reset-filter">
                                <i class="fas fa-redo"></i> Reset
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Kelas List --}}
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Daftar Kelas</h3>
            @can('create-kelas')
            <div class="card-tools">
                <a href="{{ route('admin.kelas.create') }}" class="btn btn-primary btn-sm">
                    <i class="fas fa-plus"></i> Tambah Kelas
                </a>
            </div>
            @endcan
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="kelasTable" class="table table-bordered table-hover">
                    <thead class="thead-light">
                        <tr>
                            <th width="3%">No</th>
                            <th>Kode Kelas</th>
                            <th>Nama Kelas</th>
                            <th>Tingkat</th>
                            <th>Jurusan</th>
                            <th>Tahun Pelajaran</th>
                            <th>Wali Kelas</th>
                            <th>Kapasitas</th>
                            <th>Status</th>
                            <th width="12%">Aksi</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
@stop

@section('css')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap4.min.css">
@stop

@section('js')
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap4.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap4.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        $(document).ready(function() {
            // DataTables
            var table = $('#kelasTable').DataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                ajax: {
                    url: "{{ route('admin.kelas.index') }}",
                    data: function(d) {
                        d.tahun_pelajaran_id = $('#filter_tahun_pelajaran').val();
                        d.tingkat = $('#filter_tingkat').val();
                        d.kurikulum_id = $('#filter_kurikulum').val();
                        d.jurusan_id = $('#filter_jurusan').val();
                    }
                },
                columns: [
                    { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                    { data: 'kode_kelas', name: 'kode_kelas' },
                    { data: 'nama_lengkap', name: 'nama_kelas' },
                    { data: 'tingkat_romawi', name: 'tingkat' },
                    { data: 'jurusan_nama', name: 'jurusan.singkatan' },
                    { data: 'tahun_pelajaran', name: 'tahunPelajaran.nama' },
                    { data: 'wali_kelas', name: 'waliKelas.name' },
                    { data: 'kapasitas_info', name: 'kapasitas', orderable: false },
                    { data: 'status_badge', name: 'is_active' },
                    { data: 'action', name: 'action', orderable: false, searchable: false }
                ],
                order: [[5, 'desc'], [3, 'asc'], [2, 'asc']],
                language: {
                    processing: "Memuat data...",
                    search: "Cari:",
                    lengthMenu: "Tampilkan _MENU_ data per halaman",
                    info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
                    infoEmpty: "Menampilkan 0 sampai 0 dari 0 data",
                    infoFiltered: "(disaring dari _MAX_ total data)",
                    zeroRecords: "Tidak ada data yang ditemukan",
                    emptyTable: "Tidak ada data tersedia",
                    paginate: {
                        first: "Pertama",
                        last: "Terakhir",
                        next: "Selanjutnya",
                        previous: "Sebelumnya"
                    }
                }
            });

            // Apply filter
            $('#btn-filter').on('click', function() {
                table.ajax.reload();
            });

            // Reset filter
            $('#btn-reset-filter').on('click', function() {
                $('#filter_tahun_pelajaran').val('');
                $('#filter_tingkat').val('');
                $('#filter_kurikulum').val('');
                $('#filter_jurusan').val('');
                table.ajax.reload();
            });

            // Auto reload on tahun pelajaran change
            $('#filter_tahun_pelajaran').on('change', function() {
                table.ajax.reload();
            });

            // Delete kelas
            $(document).on('click', '.btn-delete', function() {
                let kelasId = $(this).data('id');
                let namaKelas = $(this).data('nama');
                
                Swal.fire({
                    title: 'Konfirmasi Hapus Kelas',
                    html: `Apakah Anda yakin ingin menghapus kelas <strong>${namaKelas}</strong>?<br><br>
                           <small class="text-muted">Kelas hanya dapat dihapus jika tidak ada siswa aktif di tahun pelajaran saat ini.</small>`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    confirmButtonText: '<i class="fas fa-trash"></i> Ya, Hapus!',
                    cancelButtonText: '<i class="fas fa-times"></i> Batal',
                    reverseButtons: true
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: "/admin/kelas/" + kelasId,
                            type: 'DELETE',
                            data: { _token: '{{ csrf_token() }}' },
                            success: function(response) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Berhasil!',
                                    text: response.message,
                                }).then(() => table.ajax.reload());
                            },
                            error: function(xhr) {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Gagal!',
                                    text: xhr.responseJSON?.message || 'Terjadi kesalahan'
                                });
                            }
                        });
                    }
                });
            });
        });
    </script>
@stop
