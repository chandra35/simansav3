@extends('adminlte::page')

@section('title', 'Bimbingan & Konseling')

@section('content_header')
<div class="row mb-2">
    <div class="col-sm-6"><h1><i class="fas fa-comments text-primary"></i> Bimbingan & Konseling</h1></div>
    <div class="col-sm-6"><ol class="breadcrumb float-sm-right"><li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li><li class="breadcrumb-item active">Konseling</li></ol></div>
</div>
@stop

@section('content')
<div class="counseling-page">
    <div class="card bg-gradient-primary text-white mb-3 counseling-hero">
        <div class="card-body py-3">
            <div class="row align-items-center">
                <div class="col-lg-8"><h4 class="mb-1"><i class="fas fa-user-shield mr-2"></i>Ruang Kerja BK</h4><p class="mb-0">Catat layanan, pantau tindak lanjut, dan jaga kerahasiaan pendampingan siswa dalam satu alur.</p></div>
                <div class="col-lg-4 mt-3 mt-lg-0 text-lg-right">
                    @can('report-catatan-konseling')<a href="{{ route('admin.catatan-konseling.report-siswa') }}" class="btn btn-light btn-sm"><i class="fas fa-file-alt"></i> Riwayat Siswa</a>@endcan
                    @can('create-catatan-konseling')<a href="{{ route('admin.catatan-konseling.create') }}" class="btn btn-success btn-sm"><i class="fas fa-plus"></i> Catatan Baru</a>@endcan
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        @foreach([
            ['Total Layanan', $stats['total'], 'primary', 'clipboard-list'],
            ['Sedang Ditangani', $stats['aktif'], 'warning', 'hourglass-half'],
            ['Perlu Tindak Lanjut', $stats['tindak_lanjut'], 'danger', 'calendar-check'],
            ['Selesai', $stats['selesai'], 'success', 'check-circle'],
        ] as [$label, $value, $color, $icon])
        <div class="col-6 col-lg-3"><div class="info-box"><span class="info-box-icon bg-{{ $color }}"><i class="fas fa-{{ $icon }}"></i></span><div class="info-box-content"><span class="info-box-text">{{ $label }}</span><span class="info-box-number">{{ number_format($value) }}</span></div></div></div>
        @endforeach
    </div>

    <div class="card card-outline card-primary">
        <div class="card-header"><h3 class="card-title"><i class="fas fa-list mr-1"></i> Daftar Layanan Konseling</h3></div>
        <div class="card-body">
            <div class="counseling-filter rounded mb-3 p-3">
                <div class="row">
                    <div class="col-md-4"><div class="form-group mb-md-0"><label>Status</label><select id="filter-status" class="form-control"><option value="">Semua status</option>@foreach($status as $key => $label)<option value="{{ $key }}">{{ $label }}</option>@endforeach</select></div></div>
                    <div class="col-md-4"><div class="form-group mb-md-0"><label>Kategori</label><select id="filter-kategori" class="form-control"><option value="">Semua kategori</option>@foreach($kategori as $key => $label)<option value="{{ $key }}">{{ $label }}</option>@endforeach</select></div></div>
                    <div class="col-md-4 d-flex align-items-end"><button id="reset-filter" class="btn btn-secondary btn-block"><i class="fas fa-sync-alt"></i> Reset Filter</button></div>
                </div>
            </div>
            <div class="table-responsive"><table id="konseling-table" class="table table-bordered table-hover w-100"><thead><tr><th>No</th><th>Siswa</th><th>Konselor</th><th>Tanggal</th><th>Layanan</th><th>Status</th><th>Akses</th><th>Aksi</th></tr></thead></table></div>
        </div>
    </div>
</div>
@stop

@section('css')
<link rel="stylesheet" href="{{ asset('vendor/datatables/css/dataTables.bootstrap4.min.css') }}">
<style>
.counseling-page .counseling-hero{border-radius:.65rem;box-shadow:0 .4rem 1rem rgba(37,99,235,.14)}
.counseling-page .counseling-filter{background:#f8fafc;border:1px solid #e2e8f0}
.counseling-page .info-box{min-height:82px}.counseling-page .info-box-icon{width:66px}
.counseling-page table td{vertical-align:middle}
@media(max-width:767.98px){.counseling-page .info-box-icon{width:52px}.counseling-page .info-box-text{white-space:normal;font-size:.75rem}}
</style>
@stop

@section('js')
<script src="{{ asset('vendor/datatables/js/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('vendor/datatables/js/dataTables.bootstrap4.min.js') }}"></script>
<script>
$(function(){
    const table=$('#konseling-table').DataTable({processing:true,serverSide:true,ajax:{url:@json(route('admin.catatan-konseling.index')),data:d=>{d.status=$('#filter-status').val();d.kategori=$('#filter-kategori').val()}},columns:[
        {data:'DT_RowIndex',orderable:false,searchable:false},{data:'siswa_nama',name:'siswa_nama'},{data:'konselor_nama',name:'konselor_nama'},{data:'tanggal_konseling',name:'tanggal_konseling'},{data:'layanan',name:'jenis_konseling'},{data:'status_badge',name:'status'},{data:'kerahasiaan',name:'is_confidential',searchable:false},{data:'action',orderable:false,searchable:false}
    ],order:[[3,'desc']],language:{url:'//cdn.datatables.net/plug-ins/1.13.8/i18n/id.json'}});
    $('#filter-status,#filter-kategori').on('change',()=>table.ajax.reload());
    $('#reset-filter').on('click',()=>{$('#filter-status,#filter-kategori').val('');table.search('').ajax.reload()});
    $(document).on('click','.btn-delete',function(){const id=$(this).data('id');Swal.fire({title:'Hapus catatan?',text:'Catatan yang dihapus tidak tampil lagi pada riwayat.',icon:'warning',showCancelButton:true,confirmButtonColor:'#dc3545',confirmButtonText:'Ya, hapus',cancelButtonText:'Batal'}).then(result=>{if(!result.isConfirmed)return;$.ajax({url:@json(route('admin.catatan-konseling.index'))+'/'+id,type:'DELETE',data:{_token:@json(csrf_token())},success:r=>{table.ajax.reload(null,false);toastr.success(r.message)},error:x=>toastr.error(x.responseJSON?.message||'Catatan gagal dihapus.')})})});
});
</script>
@stop
