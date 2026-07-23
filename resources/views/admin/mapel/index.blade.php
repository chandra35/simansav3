@extends('adminlte::page')

@section('title', 'Mata Pelajaran')

@section('content_header')
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1><i class="fas fa-book"></i> Mata Pelajaran</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item active">Mata Pelajaran</li>
            </ol>
        </div>
    </div>
@stop

@section('content')
    <div class="mapel-hero">
        <div>
            <div class="mapel-hero__eyebrow"><i class="fas fa-layer-group"></i> AKADEMIK · KMA 1503/2025</div>
            <h2>Katalog Mata Pelajaran MAN</h2>
            <p>Pastikan struktur, fase, JP, dan mapping RDM siap sebelum menyusun jadwal mengajar.</p>
        </div>
        <div class="mapel-hero__actions">
            <a href="{{ route('admin.rdm-mapel-mapping.index') }}" class="btn btn-light">
                <i class="fas fa-exchange-alt"></i> Mapping RDM
            </a>
            @can('view-jadwal-pelajaran')
                <a href="{{ route('admin.jadwal-pelajaran.index') }}" class="btn btn-warning">
                    <i class="fas fa-calendar-alt"></i> Isi Jadwal Mengajar
                </a>
            @endcan
        </div>
    </div>

    <div class="row mapel-stats">
        @foreach([
            ['label' => 'Katalog MAN', 'value' => $stats['total'], 'icon' => 'fa-book', 'tone' => 'blue'],
            ['label' => 'Siap Dijadwalkan', 'value' => $stats['ready'], 'icon' => 'fa-check-circle', 'tone' => 'green'],
            ['label' => 'Terhubung RDM', 'value' => $stats['mapped'], 'icon' => 'fa-link', 'tone' => 'purple'],
            ['label' => 'Sudah Dipakai Jadwal', 'value' => $stats['scheduled'], 'icon' => 'fa-calendar-check', 'tone' => 'orange'],
        ] as $stat)
            <div class="col-lg-3 col-sm-6">
                <div class="mapel-stat mapel-stat--{{ $stat['tone'] }}">
                    <i class="fas {{ $stat['icon'] }}"></i>
                    <div><small>{{ $stat['label'] }}</small><strong>{{ number_format($stat['value']) }}</strong></div>
                </div>
            </div>
        @endforeach
    </div>

    {{-- Alert Messages --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle"></i> {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert">
                <span>&times;</span>
            </button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
            <button type="button" class="close" data-dismiss="alert">
                <span>&times;</span>
            </button>
        </div>
    @endif

    {{-- Filter Card --}}
    <div class="card card-outline card-secondary collapsed-card">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-filter"></i> Filter</h3>
            <div class="card-tools">
                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                    <i class="fas fa-plus"></i>
                </button>
            </div>
        </div>
        <div class="card-body">
            <form id="filter-form">
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Kurikulum</label>
                            <select class="form-control" id="filter-kurikulum" name="kurikulum_id">
                                <option value="">Semua</option>
                                @foreach($kurikulums as $kurikulum)
                                    <option value="{{ $kurikulum->id }}" @selected($kurikulum->id === $kurikulumAktifId)>
                                        {{ $kurikulum->nama_kurikulum }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Tahun Pelajaran</label>
                            <select class="form-control" id="filter-tahun-pelajaran" name="tahun_pelajaran_id">
                                <option value="">Semua</option>
                                @if(isset($tahunPelajarans))
                                    @foreach($tahunPelajarans as $tp)
                                        <option value="{{ $tp->id }}">
                                            {{ $tp->nama_tahun_pelajaran }} {{ $tp->is_active ? '(Aktif)' : '' }}
                                        </option>
                                    @endforeach
                                @endif
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label>Jurusan</label>
                            <select class="form-control" id="filter-jurusan" name="jurusan_id">
                                <option value="">Semua</option>
                                @foreach($jurusans as $jurusan)
                                    <option value="{{ $jurusan->id }}">{{ $jurusan->nama_jurusan }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label>Struktur</label>
                            <select class="form-control" id="filter-kelompok" name="kelompok">
                                <option value="">Semua</option>
                                <option value="wajib_umum">Wajib / Umum</option>
                                <option value="pilihan">Pilihan</option>
                                <option value="muatan_lokal">Muatan Lokal</option>
                                <option value="penguatan_program">Penguatan Program</option>
                                <option value="kokurikuler">Kokurikuler</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label>Status</label>
                            <select class="form-control" id="filter-status" name="is_active">
                                <option value="">Semua</option>
                                <option value="1" selected>Aktif</option>
                                <option value="0">Nonaktif</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <button type="button" class="btn btn-primary" id="btn-filter">
                            <i class="fas fa-filter"></i> Filter
                        </button>
                        <button type="button" class="btn btn-secondary" id="btn-reset">
                            <i class="fas fa-redo"></i> Reset
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Data Card --}}
    <div class="card card-outline card-primary">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-list"></i> Daftar Mata Pelajaran Aktif</h3>
            <div class="card-tools">
                <a href="{{ route('admin.mapel.create') }}" class="btn btn-primary btn-sm">
                    <i class="fas fa-magic"></i> Siapkan dari Template MAN
                </a>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="mapel-table" class="table table-bordered table-striped table-hover">
                    <thead>
                        <tr>
                            <th width="5%">No</th>
                            <th>Kode</th>
                            <th>Nama Mata Pelajaran</th>
                            <th>Kurikulum</th>
                            <th>Struktur</th>
                            <th>Fase</th>
                            <th>Rumpun</th>
                            <th>JP Acuan</th>
                            <th>Integrasi</th>
                            <th>Status</th>
                            <th width="15%">Aksi</th>
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
        .mapel-hero{display:flex;align-items:center;justify-content:space-between;gap:24px;padding:28px;border-radius:22px;margin-bottom:18px;color:#fff;background:linear-gradient(120deg,#3f63e9,#247f93);box-shadow:0 16px 34px rgba(43,75,153,.18)}
        .mapel-hero h2{font-weight:800;margin:7px 0 3px}.mapel-hero p{margin:0;color:rgba(255,255,255,.86)}
        .mapel-hero__eyebrow{font-weight:700;font-size:.8rem}.mapel-hero__actions{display:flex;gap:10px;flex-wrap:wrap}.mapel-hero__actions .btn{font-weight:700;border:0}
        .mapel-stat{display:flex;align-items:center;gap:14px;padding:18px;background:#fff;border:1px solid #dfe7f4;border-top:4px solid #4770ef;border-radius:18px;margin-bottom:18px;box-shadow:0 10px 25px rgba(31,53,91,.07)}
        .mapel-stat>i{display:grid;place-items:center;width:46px;height:46px;border-radius:14px;background:#edf2ff;color:#4770ef;font-size:1.15rem}
        .mapel-stat small,.mapel-stat strong{display:block}.mapel-stat small{font-weight:700;color:#66728a;text-transform:uppercase;font-size:.72rem}.mapel-stat strong{font-size:1.55rem;line-height:1.15}
        .mapel-stat--green{border-top-color:#31b978}.mapel-stat--green>i{color:#229460;background:#eaf9f1}.mapel-stat--purple{border-top-color:#7957df}.mapel-stat--purple>i{color:#6943d2;background:#f2edff}.mapel-stat--orange{border-top-color:#ef9d21}.mapel-stat--orange>i{color:#c97800;background:#fff6e5}
        @media(max-width:767px){.mapel-hero{align-items:flex-start;flex-direction:column}.mapel-hero__actions{width:100%}.mapel-hero__actions .btn{flex:1}}
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
            // Initialize DataTable
            let table = $('#mapel-table').DataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                ajax: {
                    url: '{{ route('admin.mapel.data') }}',
                    data: function(d) {
                        d.kurikulum_id = $('#filter-kurikulum').val();
                        d.tahun_pelajaran_id = $('#filter-tahun-pelajaran').val();
                        d.jurusan_id = $('#filter-jurusan').val();
                        d.kelompok = $('#filter-kelompok').val();
                        d.is_active = $('#filter-status').val();
                    }
                },
                columns: [
                    {
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        orderable: false,
                        searchable: false,
                        className: 'text-center'
                    },
                    {data: 'kode_mapel', name: 'kode_mapel'},
                    {data: 'nama_mapel', name: 'nama_mapel'},
                    {
                        data: 'kurikulum.nama_kurikulum',
                        name: 'kurikulum.nama_kurikulum',
                        defaultContent: '-'
                    },
                    {
                        data: 'kelompok_badge',
                        name: 'kelompok',
                        orderable: false,
                        render: function(data) {
                            return data || '-';
                        }
                    },
                    {
                        data: 'fase_display',
                        name: 'fase_display',
                        orderable: false
                    },
                    {
                        data: 'rumpun_display',
                        name: 'rumpun',
                        className: 'text-capitalize'
                    },
                    {
                        data: 'jam_pelajaran',
                        name: 'jam_pelajaran',
                        className: 'text-center',
                        render: data => `${data || 0} JP`
                    },
                    {
                        data: 'integrasi_badge',
                        name: 'integrasi_badge',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'status_badge',
                        name: 'is_active',
                        orderable: false,
                        className: 'text-center'
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false,
                        className: 'text-center'
                    }
                ],
                order: [[1, 'asc']],
                language: {
                    processing: '<i class="fa fa-spinner fa-spin fa-3x fa-fw"></i><span class="sr-only">Loading...</span>',
                    search: "Cari:",
                    lengthMenu: "Tampilkan _MENU_ data",
                    info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
                    infoEmpty: "Menampilkan 0 sampai 0 dari 0 data",
                    infoFiltered: "(disaring dari _MAX_ total data)",
                    paginate: {
                        first: "Pertama",
                        last: "Terakhir",
                        next: "Selanjutnya",
                        previous: "Sebelumnya"
                    },
                    zeroRecords: "Data tidak ditemukan",
                }
            });

            // Filter
            $('#btn-filter').click(function() {
                table.draw();
            });

            // Reset Filter
            $('#btn-reset').click(function() {
                $('#filter-form')[0].reset();
                $('#filter-kurikulum').val(@json($kurikulumAktifId));
                $('#filter-tahun-pelajaran').val('');
                $('#filter-status').val('1');
                table.draw();
            });

            // Delete
            $(document).on('click', '.delete-btn', function() {
                const id = $(this).data('id');
                
                Swal.fire({
                    title: 'Apakah Anda yakin?',
                    text: "Data mata pelajaran akan dihapus!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Ya, Hapus!',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: `/admin/mapel/${id}`,
                            type: 'DELETE',
                            data: {
                                _token: '{{ csrf_token() }}'
                            },
                            success: function(response) {
                                if (response.success) {
                                    Swal.fire({
                                        icon: 'success',
                                        title: 'Berhasil!',
                                        text: response.message,
                                        timer: 2000,
                                        showConfirmButton: false
                                    });
                                    table.ajax.reload();
                                } else {
                                    Swal.fire({
                                        icon: 'error',
                                        title: 'Gagal!',
                                        text: response.message
                                    });
                                }
                            },
                            error: function(xhr) {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Gagal!',
                                    text: xhr.responseJSON?.message || 'Terjadi kesalahan saat menghapus data'
                                });
                            }
                        });
                    }
                });
            });

            // Duplicate
            $(document).on('click', '.duplicate-btn', function() {
                const id = $(this).data('id');
                
                Swal.fire({
                    title: 'Duplikat Mata Pelajaran',
                    html: `
                        <div class="form-group">
                            <label>Kode Mapel (opsional)</label>
                            <input type="text" id="kode-mapel" class="form-control" placeholder="Kosongkan untuk auto generate">
                        </div>
                        <div class="form-group">
                            <label>Nama Mapel (opsional)</label>
                            <input type="text" id="nama-mapel" class="form-control" placeholder="Kosongkan untuk auto generate">
                        </div>
                    `,
                    showCancelButton: true,
                    confirmButtonText: 'Duplikat',
                    cancelButtonText: 'Batal',
                    preConfirm: () => {
                        return {
                            kode_mapel: $('#kode-mapel').val(),
                            nama_mapel: $('#nama-mapel').val()
                        }
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: `/admin/mapel/${id}/duplicate`,
                            type: 'POST',
                            data: {
                                _token: '{{ csrf_token() }}',
                                kode_mapel: result.value.kode_mapel,
                                nama_mapel: result.value.nama_mapel
                            },
                            success: function(response) {
                                if (response.success) {
                                    Swal.fire({
                                        icon: 'success',
                                        title: 'Berhasil!',
                                        text: response.message,
                                        timer: 2000,
                                        showConfirmButton: false
                                    });
                                    table.ajax.reload();
                                } else {
                                    Swal.fire({
                                        icon: 'error',
                                        title: 'Gagal!',
                                        text: response.message
                                    });
                                }
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
