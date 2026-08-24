@extends('adminlte::page')

@section('title', 'Kurikulum')

@section('content_header')
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1><i class="fas fa-book-open"></i> Kurikulum</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item active">Kurikulum</li>
            </ol>
        </div>
    </div>
@stop

@section('content')
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle"></i> {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
        </div>
    @endif

    <section class="simansa-kurikulum-hero">
        <div class="simansa-kurikulum-hero__content">
            <div>
                <div class="simansa-kurikulum-hero__eyebrow">
                    <i class="fas fa-book"></i> Fondasi Kurikulum SIMANSA
                </div>
                <h2>Manajemen Kurikulum</h2>
                <p>Pantau kurikulum yang sedang dipakai, struktur peminatan, dan keterhubungannya dengan tahun pelajaran dari satu halaman yang lebih jelas.</p>
            </div>
            <div class="simansa-kurikulum-hero__meta">
                <div class="simansa-kurikulum-chip">
                    <span class="simansa-kurikulum-chip__label">Kurikulum Aktif</span>
                    <strong>{{ $kurikulumAktif?->kode ?? 'Belum ada' }}</strong>
                </div>
                <div class="simansa-kurikulum-chip">
                    <span class="simansa-kurikulum-chip__label">Nama</span>
                    <strong>{{ $kurikulumAktif?->nama_kurikulum ?? 'Belum ditetapkan' }}</strong>
                </div>
            </div>
        </div>
    </section>

    <div class="row">
        <div class="col-lg-3 col-md-6">
            <div class="simansa-kurikulum-stat simansa-kurikulum-stat--primary">
                <span class="simansa-kurikulum-stat__label">Total Kurikulum</span>
                <strong>{{ number_format($stats['total']) }}</strong>
                <small>Seluruh kurikulum yang tersimpan di sistem</small>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="simansa-kurikulum-stat simansa-kurikulum-stat--success">
                <span class="simansa-kurikulum-stat__label">Sedang Digunakan</span>
                <strong>{{ number_format($stats['aktif']) }}</strong>
                <small>Kurikulum yang aktif untuk operasional sekarang</small>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="simansa-kurikulum-stat simansa-kurikulum-stat--warning">
                <span class="simansa-kurikulum-stat__label">Dengan Jurusan</span>
                <strong>{{ number_format($stats['dengan_jurusan']) }}</strong>
                <small>Masih memakai struktur peminatan atau jurusan</small>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="simansa-kurikulum-stat simansa-kurikulum-stat--info">
                <span class="simansa-kurikulum-stat__label">Sudah Dipakai</span>
                <strong>{{ number_format($stats['dipakai']) }}</strong>
                <small>Sudah terhubung dengan setidaknya satu tahun pelajaran</small>
            </div>
        </div>
    </div>

    <div class="simansa-kurikulum-panel">
        <div class="simansa-kurikulum-panel__header">
            <div>
                <h3><i class="fas fa-list"></i> Daftar Kurikulum</h3>
                <p>Kelola kurikulum yang aktif, status penjurusan, dan akses detail tanpa mengubah workflow yang sudah berjalan.</p>
            </div>
            @can('create-kurikulum')
                <a href="{{ route('admin.kurikulum.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Tambah Kurikulum
                </a>
            @endcan
        </div>
        <div class="simansa-kurikulum-panel__body">
            <div class="table-responsive">
                <table id="kurikulumTable" class="table table-bordered table-striped table-hover simansa-kurikulum-table">
                    <thead>
                        <tr>
                            <th width="5%">No</th>
                            <th>Kode</th>
                            <th>Nama Kurikulum</th>
                            <th width="10%">Tahun Berlaku</th>
                            <th width="15%">Peminatan/Jurusan</th>
                            <th width="10%">Status</th>
                            <th width="15%">Aksi</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
@stop

@section('css')
    <link rel="stylesheet" href="{{ asset('css/custom-compact.css') }}">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.4.1/css/responsive.bootstrap4.min.css">
    <style>
        .simansa-kurikulum-hero{margin-bottom:1.5rem;padding:1.75rem 1.8rem;border-radius:24px;background:linear-gradient(135deg,#1947c7 0%,#2a7f88 100%);color:#fff;box-shadow:0 18px 40px rgba(25,71,199,.17)}
        .simansa-kurikulum-hero__content{display:flex;justify-content:space-between;gap:1.5rem;align-items:flex-start}
        .simansa-kurikulum-hero__eyebrow{display:inline-flex;align-items:center;gap:.45rem;font-size:.78rem;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:rgba(255,255,255,.84);margin-bottom:.7rem}
        .simansa-kurikulum-hero h2{margin:0 0 .4rem;font-size:2rem;font-weight:700}
        .simansa-kurikulum-hero p{margin:0;max-width:720px;color:rgba(255,255,255,.9)}
        .simansa-kurikulum-hero__meta{display:grid;grid-template-columns:repeat(2,minmax(180px,1fr));gap:.9rem;min-width:380px}
        .simansa-kurikulum-chip{padding:1rem 1.1rem;border-radius:18px;background:rgba(255,255,255,.12);border:1px solid rgba(255,255,255,.18)}
        .simansa-kurikulum-chip__label{display:block;margin-bottom:.35rem;font-size:.72rem;letter-spacing:.05em;text-transform:uppercase;color:rgba(255,255,255,.74)}
        .simansa-kurikulum-chip strong{color:#fff}
        .simansa-kurikulum-stat{height:100%;padding:1.3rem 1.25rem;border-radius:22px;color:#fff;box-shadow:0 16px 36px rgba(15,23,42,.08);margin-bottom:1rem}
        .simansa-kurikulum-stat--primary{background:linear-gradient(135deg,#6268f3 0%,#5b76d6 100%)}
        .simansa-kurikulum-stat--success{background:linear-gradient(135deg,#46c98a 0%,#57d2aa 100%)}
        .simansa-kurikulum-stat--warning{background:linear-gradient(135deg,#f4ac08 0%,#f6c453 100%);color:#17324d}
        .simansa-kurikulum-stat--info{background:linear-gradient(135deg,#2c8cff 0%,#52a8ff 100%)}
        .simansa-kurikulum-stat__label{display:block;font-size:.8rem;font-weight:700;text-transform:uppercase;letter-spacing:.04em;margin-bottom:.7rem;opacity:.9}
        .simansa-kurikulum-stat strong{display:block;font-size:2rem;line-height:1;margin-bottom:.55rem}
        .simansa-kurikulum-stat small{display:block;font-size:.88rem;opacity:.9}
        .simansa-kurikulum-panel{background:#fff;border-radius:22px;box-shadow:0 14px 34px rgba(15,23,42,.08);overflow:hidden}
        .simansa-kurikulum-panel__header{display:flex;justify-content:space-between;gap:1rem;align-items:center;padding:1.35rem 1.5rem;border-bottom:1px solid rgba(148,163,184,.18)}
        .simansa-kurikulum-panel__header h3{margin:0 0 .25rem;font-size:1.1rem;font-weight:700;color:#1f2a44}
        .simansa-kurikulum-panel__header p{margin:0;color:#60708b;font-size:.92rem}
        .simansa-kurikulum-panel__body{padding:1.5rem}
        .simansa-kurikulum-table thead th{white-space:nowrap;color:#596780;font-size:.8rem;text-transform:uppercase;letter-spacing:.04em}
        @media (max-width:992px){.simansa-kurikulum-hero__content,.simansa-kurikulum-panel__header{flex-direction:column;align-items:stretch}.simansa-kurikulum-hero__meta{grid-template-columns:1fr;min-width:0}}
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
            const table = $('#kurikulumTable').DataTable({
                processing: true, serverSide: true, responsive: true, ajax: "{{ route('admin.kurikulum.index') }}",
                columns: [
                    {data:'DT_RowIndex',name:'DT_RowIndex',orderable:false,searchable:false},
                    {data:'kode',name:'kode'},
                    {data:'formatted_name',name:'nama_kurikulum'},
                    {data:'tahun_berlaku',name:'tahun_berlaku'},
                    {data:'has_jurusan_badge',name:'has_jurusan',orderable:false},
                    {data:'status_badge',name:'is_active',orderable:false},
                    {data:'action',name:'action',orderable:false,searchable:false}
                ],
                order:[[3,'desc']],
                language:{
                    processing:'<i class="fa fa-spinner fa-spin fa-3x"></i>', search:"Cari:", lengthMenu:"Tampilkan _MENU_ data",
                    info:"Menampilkan _START_ - _END_ dari _TOTAL_ data", infoEmpty:"Tidak ada data",
                    paginate:{ first:"Pertama", last:"Terakhir", next:"Selanjutnya", previous:"Sebelumnya" },
                    zeroRecords:"Tidak ada data", emptyTable:"Belum ada data"
                }
            });

            $('#kurikulumTable').on('click','.btn-activate',function(){
                const id=$(this).data('id');
                Swal.fire({ title:'Aktifkan Kurikulum?', text:'Kurikulum ini akan dipakai sebagai kurikulum aktif sistem.', icon:'question', showCancelButton:true, confirmButtonColor:'#28a745', cancelButtonColor:'#6c757d', confirmButtonText:'<i class="fas fa-check"></i> Ya, aktifkan', cancelButtonText:'Batal' }).then((result)=>{
                    if(result.isConfirmed){ $.ajax({ url:`/admin/kurikulum/${id}/activate`, type:'POST', data:{ _token:'{{ csrf_token() }}' }, success:function(response){ Swal.fire({ icon:'success', title:'Berhasil', text:response.message, timer:2000, showConfirmButton:false }); table.ajax.reload(null,false); }, error:function(xhr){ Swal.fire({ icon:'error', title:'Gagal', text:xhr.responseJSON?.message || 'Terjadi kesalahan' }); } }); }
                });
            });

            $('#kurikulumTable').on('click','.btn-deactivate',function(){
                const id=$(this).data('id');
                Swal.fire({ title:'Nonaktifkan Kurikulum?', text:'Pastikan sudah tidak ada kebutuhan operasional yang bergantung pada kurikulum ini.', icon:'warning', showCancelButton:true, confirmButtonColor:'#ffc107', cancelButtonColor:'#6c757d', confirmButtonText:'<i class="fas fa-check"></i> Ya, nonaktifkan', cancelButtonText:'Batal' }).then((result)=>{
                    if(result.isConfirmed){ $.ajax({ url:`/admin/kurikulum/${id}/deactivate`, type:'POST', data:{ _token:'{{ csrf_token() }}' }, success:function(response){ Swal.fire({ icon:'success', title:'Berhasil', text:response.message, timer:2000, showConfirmButton:false }); table.ajax.reload(null,false); }, error:function(xhr){ Swal.fire({ icon:'error', title:'Gagal', text:xhr.responseJSON?.message || 'Terjadi kesalahan' }); } }); }
                });
            });

            $('#kurikulumTable').on('click','.btn-delete',function(){
                const id=$(this).data('id');
                Swal.fire({ title:'Hapus Kurikulum?', text:'Data kurikulum akan dihapus permanen jika tidak sedang dipakai.', icon:'warning', showCancelButton:true, confirmButtonColor:'#dc3545', cancelButtonColor:'#6c757d', confirmButtonText:'<i class="fas fa-trash"></i> Hapus', cancelButtonText:'Batal' }).then((result)=>{
                    if(result.isConfirmed){ $.ajax({ url:`/admin/kurikulum/${id}`, type:'DELETE', data:{ _token:'{{ csrf_token() }}' }, success:function(response){ Swal.fire({ icon:'success', title:'Berhasil', text:response.message, timer:2000, showConfirmButton:false }); table.ajax.reload(null,false); }, error:function(xhr){ Swal.fire({ icon:'error', title:'Gagal', text:xhr.responseJSON?.message || 'Terjadi kesalahan' }); } }); }
                });
            });
        });
    </script>
@stop
