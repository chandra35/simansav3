@extends('adminlte::page')

@section('title', 'Tahun Pelajaran')

@section('content_header')
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1><i class="fas fa-calendar-alt"></i> Tahun Pelajaran</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item active">Tahun Pelajaran</li>
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

    <section class="simansa-tp-hero">
        <div class="simansa-tp-hero__main">
            <span class="simansa-tp-hero__eyebrow">Administrasi Akademik</span>
            <h2 class="simansa-tp-hero__title">Kelola siklus tahun pelajaran dengan lebih jelas</h2>
            <p class="simansa-tp-hero__desc">
                Gunakan halaman ini untuk menentukan tahun aktif, memantau semester berjalan, dan menjaga transisi antar tahun
                ajaran tetap rapi.
            </p>
            <div class="simansa-tp-hero__meta">
                <div class="simansa-tp-chip">
                    <span class="simansa-tp-chip__label">Tahun Aktif</span>
                    <span class="simansa-tp-chip__value">{{ $tahunAktif?->nama ?? 'Belum ada' }}</span>
                </div>
                <div class="simansa-tp-chip">
                    <span class="simansa-tp-chip__label">Semester Berjalan</span>
                    <span class="simansa-tp-chip__value">{{ $tahunAktif?->semester_aktif ?? '-' }}</span>
                </div>
                <div class="simansa-tp-chip">
                    <span class="simansa-tp-chip__label">Kurikulum Aktif</span>
                    <span class="simansa-tp-chip__value">{{ $tahunAktif?->kurikulum?->formatted_name ?? '-' }}</span>
                </div>
            </div>
        </div>
        @can('create-tahun-pelajaran')
            <div class="simansa-tp-hero__actions">
                <a href="{{ route('admin.tahun-pelajaran.create') }}" class="btn btn-light btn-lg simansa-tp-hero__button">
                    <i class="fas fa-plus"></i> Tambah Tahun Pelajaran
                </a>
            </div>
        @endcan
    </section>

    <div class="row">
        <div class="col-lg-3 col-md-6">
            <div class="simansa-stat-card simansa-stat-card--primary">
                <span class="simansa-stat-card__label">Total Periode</span>
                <span class="simansa-stat-card__value">{{ $stats['total'] }}</span>
                <span class="simansa-stat-card__note">Semua tahun pelajaran yang tersimpan</span>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="simansa-stat-card simansa-stat-card--success">
                <span class="simansa-stat-card__label">Sedang Digunakan</span>
                <span class="simansa-stat-card__value">{{ $stats['aktif'] }}</span>
                <span class="simansa-stat-card__note">Target ideal hanya 1 periode aktif</span>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="simansa-stat-card simansa-stat-card--warning">
                <span class="simansa-stat-card__label">Belum Aktif</span>
                <span class="simansa-stat-card__value">{{ $stats['nonaktif'] }}</span>
                <span class="simansa-stat-card__note">Siap dipakai untuk periode berikutnya</span>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="simansa-stat-card simansa-stat-card--neutral">
                <span class="simansa-stat-card__label">Arsip</span>
                <span class="simansa-stat-card__value">{{ $stats['selesai'] }}</span>
                <span class="simansa-stat-card__note">Riwayat tahun pelajaran yang ditutup</span>
            </div>
        </div>
    </div>

    <div class="card simansa-panel">
        <div class="card-header border-0">
            <div>
                <h3 class="card-title mb-1"><i class="fas fa-stream"></i> Daftar Tahun Pelajaran</h3>
                <p class="text-muted mb-0">Status dibuat lebih jelas: `Sedang Digunakan`, `Belum Aktif`, dan `Arsip`.</p>
            </div>
        </div>
        <div class="card-body pt-0">
            <div class="table-responsive">
                <table id="tahunPelajaranTable" class="table table-bordered table-striped table-hover">
                    <thead>
                        <tr>
                            <th width="5%">No</th>
                            <th>Tahun Pelajaran</th>
                            <th>Kurikulum</th>
                            <th>Periode</th>
                            <th width="12%">Semester</th>
                            <th width="14%">Status</th>
                            <th width="18%">Aksi</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
@stop

@section('css')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.4.1/css/responsive.bootstrap4.min.css">
    <style>
        .simansa-tp-hero {
            display: flex;
            justify-content: space-between;
            gap: 1.5rem;
            padding: 2rem;
            margin-bottom: 1.5rem;
            border-radius: 24px;
            background: linear-gradient(135deg, #2f4fd3 0%, #2d7c8f 100%);
            color: #fff;
            box-shadow: 0 20px 45px rgba(47, 79, 211, 0.16);
        }
        .simansa-tp-hero__main {
            max-width: 820px;
        }
        .simansa-tp-hero__eyebrow {
            display: inline-block;
            margin-bottom: 0.75rem;
            font-size: 0.78rem;
            font-weight: 700;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            opacity: 0.85;
        }
        .simansa-tp-hero__title {
            margin: 0 0 0.55rem;
            font-size: 2rem;
            font-weight: 800;
            line-height: 1.15;
        }
        .simansa-tp-hero__desc {
            margin: 0;
            max-width: 720px;
            color: rgba(255, 255, 255, 0.9);
            font-size: 1rem;
        }
        .simansa-tp-hero__meta {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 0.85rem;
            margin-top: 1.4rem;
        }
        .simansa-tp-chip {
            padding: 0.9rem 1rem;
            border-radius: 18px;
            background: rgba(255, 255, 255, 0.14);
            backdrop-filter: blur(4px);
        }
        .simansa-tp-chip__label {
            display: block;
            margin-bottom: 0.2rem;
            font-size: 0.75rem;
            font-weight: 700;
            letter-spacing: 0.06em;
            text-transform: uppercase;
            opacity: 0.82;
        }
        .simansa-tp-chip__value {
            display: block;
            font-size: 1rem;
            font-weight: 700;
            line-height: 1.35;
        }
        .simansa-tp-hero__actions {
            display: flex;
            align-items: flex-start;
        }
        .simansa-tp-hero__button {
            border: 0;
            border-radius: 16px;
            font-weight: 700;
            color: #21405f;
        }
        .simansa-stat-card {
            display: flex;
            flex-direction: column;
            gap: 0.45rem;
            min-height: 160px;
            padding: 1.4rem;
            margin-bottom: 1rem;
            border-radius: 22px;
            color: #fff;
            box-shadow: 0 16px 36px rgba(15, 23, 42, 0.08);
        }
        .simansa-stat-card__label {
            font-size: 0.8rem;
            font-weight: 700;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            opacity: 0.9;
        }
        .simansa-stat-card__value {
            font-size: 2.2rem;
            font-weight: 800;
            line-height: 1;
        }
        .simansa-stat-card__note {
            margin-top: auto;
            font-size: 0.92rem;
            opacity: 0.92;
        }
        .simansa-stat-card--primary {
            background: linear-gradient(135deg, #5b61f2 0%, #6d7cff 100%);
        }
        .simansa-stat-card--success {
            background: linear-gradient(135deg, #39c98a 0%, #67d6a3 100%);
        }
        .simansa-stat-card--warning {
            background: linear-gradient(135deg, #f0a202 0%, #f6be45 100%);
            color: #1f2937;
        }
        .simansa-stat-card--neutral {
            background: linear-gradient(135deg, #62748e 0%, #8b9bb0 100%);
        }
        .simansa-panel {
            border: 0;
            border-radius: 22px;
            overflow: hidden;
            box-shadow: 0 18px 36px rgba(15, 23, 42, 0.08);
        }
        .simansa-panel .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1.25rem 1.4rem;
            background: #fff;
        }
        .simansa-panel .card-title {
            font-weight: 700;
        }
        .badge {
            font-size: 0.85rem;
            padding: 0.35em 0.65em;
        }
        @media (max-width: 991.98px) {
            .simansa-tp-hero {
                flex-direction: column;
            }
            .simansa-tp-hero__meta {
                grid-template-columns: 1fr;
            }
        }
    </style>
@stop

@section('js')
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap4.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.4.1/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.4.1/js/responsive.bootstrap4.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        $(document).ready(function() {
            const table = $('#tahunPelajaranTable').DataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                ajax: "{{ route('admin.tahun-pelajaran.index') }}",
                columns: [
                    {data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false},
                    {data: 'nama', name: 'nama'},
                    {data: 'kurikulum_nama', name: 'kurikulum.nama_kurikulum'},
                    {data: 'periode', name: 'periode', orderable: false},
                    {data: 'semester_badge', name: 'semester_aktif', orderable: false},
                    {data: 'status_badge', name: 'status', orderable: false},
                    {data: 'action', name: 'action', orderable: false, searchable: false}
                ],
                order: [[1, 'desc']],
                language: {
                    processing: '<i class="fa fa-spinner fa-spin fa-3x fa-fw"></i><span class="sr-only">Loading...</span>',
                    search: "Cari:",
                    lengthMenu: "Tampilkan _MENU_ data per halaman",
                    info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
                    infoEmpty: "Menampilkan 0 sampai 0 dari 0 data",
                    infoFiltered: "(difilter dari _MAX_ total data)",
                    paginate: {
                        first: "Pertama",
                        last: "Terakhir",
                        next: "Selanjutnya",
                        previous: "Sebelumnya"
                    },
                    zeroRecords: "Tidak ada data yang ditemukan",
                    emptyTable: "Belum ada data tahun pelajaran"
                }
            });

            $('#tahunPelajaranTable').on('click', '.btn-set-active', function() {
                const id = $(this).data('id');

                Swal.fire({
                    title: 'Aktifkan Tahun Pelajaran?',
                    text: 'Periode aktif yang lama akan otomatis dinonaktifkan.',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#28a745',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: '<i class="fas fa-check"></i> Ya, Aktifkan',
                    cancelButtonText: '<i class="fas fa-times"></i> Batal'
                }).then((result) => {
                    if (!result.isConfirmed) return;

                    $.ajax({
                        url: `/admin/tahun-pelajaran/${id}/set-active`,
                        type: 'POST',
                        data: {_token: '{{ csrf_token() }}'},
                        success: function(response) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil',
                                text: response.message,
                                timer: 2000,
                                showConfirmButton: false
                            });
                            table.ajax.reload(null, false);
                        },
                        error: function(xhr) {
                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal',
                                text: xhr.responseJSON?.message || 'Terjadi kesalahan'
                            });
                        }
                    });
                });
            });

            $('#tahunPelajaranTable').on('click', '.btn-change-semester', function() {
                const id = $(this).data('id');
                const nextSemester = $(this).data('semester');

                Swal.fire({
                    title: 'Ganti Semester?',
                    text: `Semester aktif akan diubah ke ${nextSemester}.`,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#17a2b8',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: '<i class="fas fa-sync-alt"></i> Ya, Ubah',
                    cancelButtonText: '<i class="fas fa-times"></i> Batal'
                }).then((result) => {
                    if (!result.isConfirmed) return;

                    $.ajax({
                        url: `/admin/tahun-pelajaran/${id}/change-semester`,
                        type: 'POST',
                        data: {_token: '{{ csrf_token() }}'},
                        success: function(response) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil',
                                text: response.message,
                                timer: 2000,
                                showConfirmButton: false
                            });
                            table.ajax.reload(null, false);
                        },
                        error: function(xhr) {
                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal',
                                text: xhr.responseJSON?.message || 'Terjadi kesalahan'
                            });
                        }
                    });
                });
            });

            $('#tahunPelajaranTable').on('click', '.btn-delete', function() {
                const id = $(this).data('id');

                Swal.fire({
                    title: 'Hapus Tahun Pelajaran?',
                    text: 'Data akan dihapus permanen jika belum dipakai.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#dc3545',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: '<i class="fas fa-trash"></i> Ya, Hapus',
                    cancelButtonText: '<i class="fas fa-times"></i> Batal'
                }).then((result) => {
                    if (!result.isConfirmed) return;

                    $.ajax({
                        url: `/admin/tahun-pelajaran/${id}`,
                        type: 'DELETE',
                        data: {_token: '{{ csrf_token() }}'},
                        success: function(response) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil',
                                text: response.message,
                                timer: 2000,
                                showConfirmButton: false
                            });
                            table.ajax.reload(null, false);
                        },
                        error: function(xhr) {
                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal',
                                text: xhr.responseJSON?.message || 'Terjadi kesalahan'
                            });
                        }
                    });
                });
            });
        });
    </script>
@stop
