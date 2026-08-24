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

    <section class="simansa-kelas-hero">
        <div class="simansa-kelas-hero__content">
            <div>
                <div class="simansa-kelas-hero__eyebrow">
                    <i class="fas fa-layer-group"></i> Struktur Akademik SIMANSA
                </div>
                <h2>Daftar Kelas</h2>
                <p>Kelola rombongan belajar, wali kelas, ketua kelas, kapasitas, dan distribusi siswa dari satu halaman yang lebih mudah dipantau.</p>
            </div>
            <div class="simansa-kelas-hero__meta">
                <div class="simansa-kelas-chip">
                    <span class="simansa-kelas-chip__label">Tahun Aktif</span>
                    <strong>{{ $tahunAktif?->nama ?? 'Belum diatur' }}</strong>
                </div>
                <div class="simansa-kelas-chip">
                    <span class="simansa-kelas-chip__label">Semester</span>
                    <strong>{{ $tahunAktif?->semester_label ?? '-' }}</strong>
                </div>
                <div class="simansa-kelas-chip">
                    <span class="simansa-kelas-chip__label">Kurikulum Aktif</span>
                    <strong>{{ $tahunAktif?->kurikulum?->kode ?? '-' }}</strong>
                </div>
            </div>
        </div>
    </section>

    <div class="row">
        <div class="col-lg-3 col-md-6">
            <div class="simansa-kelas-stat simansa-kelas-stat--primary">
                <span class="simansa-kelas-stat__label">Total Kelas</span>
                <strong>{{ number_format($stats['total']) }}</strong>
                <small>Seluruh kelas yang tersimpan di sistem</small>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="simansa-kelas-stat simansa-kelas-stat--success">
                <span class="simansa-kelas-stat__label">Sedang Digunakan</span>
                <strong>{{ number_format($stats['aktif']) }}</strong>
                <small>Kelas yang aktif dipakai operasional</small>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="simansa-kelas-stat simansa-kelas-stat--warning">
                <span class="simansa-kelas-stat__label">Tahun Aktif</span>
                <strong>{{ number_format($stats['tahun_aktif']) }}</strong>
                <small>Kelas pada tahun pelajaran yang sedang berjalan</small>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="simansa-kelas-stat simansa-kelas-stat--danger">
                <span class="simansa-kelas-stat__label">Kelas Penuh</span>
                <strong>{{ number_format($stats['kapasitas_penuh']) }}</strong>
                <small>Perlu evaluasi kapasitas atau redistribusi siswa</small>
            </div>
        </div>
    </div>

    <div class="simansa-kelas-panel">
        <div class="simansa-kelas-panel__header">
            <div>
                <h3><i class="fas fa-filter"></i> Filter Kelas</h3>
                <p>Pilih tahun pelajaran, tingkat, kurikulum, atau jurusan; daftar akan diperbarui otomatis.</p>
            </div>
        </div>
        <div class="simansa-kelas-panel__body">
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
                        <div class="simansa-kelas-actions">
                            <button type="button" class="btn btn-outline-secondary" id="btn-reset-filter">
                                <i class="fas fa-undo"></i> Reset
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="simansa-kelas-panel">
        <div class="simansa-kelas-panel__header">
            <div>
                <h3><i class="fas fa-list"></i> Daftar Kelas</h3>
                <p>Pantau kode kelas, wali kelas, ketua kelas, kapasitas, dan status pemakaian tanpa pindah-pindah halaman.</p>
            </div>
            @can('create-kelas')
                <a href="{{ route('admin.kelas.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Tambah Kelas
                </a>
            @endcan
        </div>
        <div class="simansa-kelas-panel__body">
            <div class="table-responsive">
                <table id="kelasTable" class="table table-bordered table-hover simansa-kelas-table">
                    <thead>
                        <tr>
                            <th width="3%">No</th>
                            <th>Kode Kelas</th>
                            <th>Nama Kelas</th>
                            <th>Tingkat</th>
                            <th>Jurusan</th>
                            <th>Tahun Pelajaran</th>
                            <th>Wali Kelas</th>
                            <th>Ketua Kelas</th>
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
    <style>
        .simansa-kelas-hero{margin-bottom:1.5rem;padding:1.75rem 1.8rem;border-radius:24px;background:linear-gradient(135deg,#1f4fd1 0%,#2f8ca3 100%);color:#fff;box-shadow:0 20px 45px rgba(31,79,209,.18)}
        .simansa-kelas-hero__content{display:flex;justify-content:space-between;gap:1.5rem;align-items:flex-start}
        .simansa-kelas-hero__eyebrow{display:inline-flex;align-items:center;gap:.45rem;font-size:.78rem;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:rgba(255,255,255,.84);margin-bottom:.7rem}
        .simansa-kelas-hero h2{margin:0 0 .4rem;font-size:2rem;font-weight:700}
        .simansa-kelas-hero p{margin:0;max-width:700px;color:rgba(255,255,255,.9);font-size:1rem}
        .simansa-kelas-hero__meta{display:grid;grid-template-columns:repeat(3,minmax(160px,1fr));gap:.9rem;min-width:440px}
        .simansa-kelas-chip{padding:1rem 1.1rem;border-radius:18px;background:rgba(255,255,255,.12);backdrop-filter:blur(8px);border:1px solid rgba(255,255,255,.18)}
        .simansa-kelas-chip__label{display:block;margin-bottom:.35rem;font-size:.72rem;letter-spacing:.05em;text-transform:uppercase;color:rgba(255,255,255,.74)}
        .simansa-kelas-chip strong{font-size:1rem;color:#fff}
        .simansa-kelas-stat{height:100%;padding:1.3rem 1.25rem;border-radius:22px;color:#fff;box-shadow:0 16px 36px rgba(15,23,42,.09);margin-bottom:1rem}
        .simansa-kelas-stat--primary{background:linear-gradient(135deg,#6268f3 0%,#5b76d6 100%)}
        .simansa-kelas-stat--success{background:linear-gradient(135deg,#46c98a 0%,#57d2aa 100%)}
        .simansa-kelas-stat--warning{background:linear-gradient(135deg,#f4ac08 0%,#f6c453 100%);color:#17324d}
        .simansa-kelas-stat--danger{background:linear-gradient(135deg,#f37f88 0%,#ee8e98 100%)}
        .simansa-kelas-stat__label{display:block;font-size:.8rem;font-weight:700;letter-spacing:.04em;text-transform:uppercase;margin-bottom:.7rem;opacity:.88}
        .simansa-kelas-stat strong{display:block;font-size:2rem;line-height:1;margin-bottom:.55rem}
        .simansa-kelas-stat small{display:block;font-size:.88rem;opacity:.88}
        .simansa-kelas-panel{background:#fff;border-radius:22px;box-shadow:0 14px 34px rgba(15,23,42,.08);margin-bottom:1.5rem;overflow:hidden}
        .simansa-kelas-panel__header{display:flex;justify-content:space-between;gap:1rem;align-items:center;padding:1.35rem 1.5rem;border-bottom:1px solid rgba(148,163,184,.18)}
        .simansa-kelas-panel__header h3{margin:0 0 .25rem;font-size:1.1rem;font-weight:700;color:#1f2a44}
        .simansa-kelas-panel__header p{margin:0;color:#60708b;font-size:.92rem}
        .simansa-kelas-panel__body{padding:1.5rem}
        .simansa-kelas-actions{display:flex;gap:.5rem;flex-wrap:wrap}
        .simansa-kelas-table thead th{white-space:nowrap;color:#596780;font-size:.8rem;text-transform:uppercase;letter-spacing:.04em}
        @media (max-width:992px){.simansa-kelas-hero__content,.simansa-kelas-panel__header{flex-direction:column;align-items:stretch}.simansa-kelas-hero__meta{grid-template-columns:1fr;min-width:0}}
    </style>
@stop

@section('js')
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap4.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap4.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        $(document).ready(function() {
            var table = $('#kelasTable').DataTable({
                processing: true, serverSide: true, responsive: true,
                ajax: { url: "{{ route('admin.kelas.index') }}", data: function(d){ d.tahun_pelajaran_id=$('#filter_tahun_pelajaran').val(); d.tingkat=$('#filter_tingkat').val(); d.kurikulum_id=$('#filter_kurikulum').val(); d.jurusan_id=$('#filter_jurusan').val(); } },
                columns: [
                    { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                    { data: 'kode_kelas', name: 'kode_kelas' },
                    { data: 'nama_lengkap', name: 'nama_kelas' },
                    { data: 'tingkat_romawi', name: 'tingkat' },
                    { data: 'jurusan_nama', name: 'jurusan.singkatan' },
                    { data: 'tahun_pelajaran', name: 'tahunPelajaran.nama' },
                    { data: 'wali_kelas', name: 'waliKelas.name' },
                    { data: 'ketua_kelas', name: 'ketua_kelas', orderable: false, searchable: false },
                    { data: 'kapasitas_info', name: 'kapasitas', orderable: false },
                    { data: 'status_badge', name: 'is_active' },
                    { data: 'action', name: 'action', orderable: false, searchable: false }
                ],
                order: [[5,'desc'],[3,'asc'],[2,'asc']],
                language: {
                    processing: "Memuat data...", search: "Cari:", lengthMenu: "Tampilkan _MENU_ data per halaman",
                    info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ data", infoEmpty: "Menampilkan 0 sampai 0 dari 0 data",
                    infoFiltered: "(disaring dari _MAX_ total data)", zeroRecords: "Tidak ada data yang ditemukan",
                    emptyTable: "Belum ada data",
                    paginate: { first: "Pertama", last: "Terakhir", next: "Selanjutnya", previous: "Sebelumnya" }
                }
            });

            $('#btn-reset-filter').on('click', function(){ $('#filter_tahun_pelajaran').val(''); $('#filter_tingkat').val(''); $('#filter_kurikulum').val(''); $('#filter_jurusan').val(''); table.ajax.reload(); });
            $('#filter_tahun_pelajaran, #filter_tingkat, #filter_kurikulum, #filter_jurusan').on('change', function(){
                table.ajax.reload();
            });

            $(document).on('click', '.btn-delete', function() {
                let kelasId=$(this).data('id'); let namaKelas=$(this).data('nama');
                Swal.fire({
                    title:'Hapus Kelas?',
                    html:`Apakah Anda yakin ingin menghapus kelas <strong>${namaKelas}</strong>?<br><br><small class="text-muted">Kelas hanya dapat dihapus jika tidak ada siswa aktif di tahun pelajaran saat ini.</small>`,
                    icon:'warning', showCancelButton:true, confirmButtonColor:'#d33',
                    confirmButtonText:'<i class="fas fa-trash"></i> Ya, Hapus', cancelButtonText:'<i class="fas fa-times"></i> Batal', reverseButtons:true
                }).then((result)=>{
                    if(result.isConfirmed){
                        $.ajax({
                            url:"/admin/kelas/"+kelasId, type:'DELETE', data:{ _token:'{{ csrf_token() }}' },
                            success:function(response){ Swal.fire({ icon:'success', title:'Berhasil', text:response.message }).then(()=>table.ajax.reload()); },
                            error:function(xhr){ Swal.fire({ icon:'error', title:'Gagal', text:xhr.responseJSON?.message || 'Terjadi kesalahan' }); }
                        });
                    }
                });
            });
        });
    </script>
@stop
