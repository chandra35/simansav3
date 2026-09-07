@extends('adminlte::page')
@section('title', 'Smart Relasi Keluarga')
@section('plugins.Sweetalert2', true)
@section('content_header')
<div class="row mb-2"><div class="col-sm-6"><h1><i class="fas fa-project-diagram text-primary"></i> Smart Relasi Keluarga</h1></div><div class="col-sm-6"><ol class="breadcrumb float-sm-right"><li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li><li class="breadcrumb-item active">Relasi Keluarga</li></ol></div></div>
@stop
@section('content')
<div class="relasi-keluarga">
 <div class="card bg-gradient-primary text-white mb-4"><div class="card-body"><div class="row align-items-center"><div class="col-lg-8"><h4 class="mb-1">Deteksi relasi berbasis data yang sama</h4><p class="mb-0">Kandidat hanya memakai KK atau NIK yang identik. Konfirmasi operator diperlukan sebelum menjadi data relasi.</p></div><div class="col-lg-4 mt-3 mt-lg-0 text-lg-right"><strong>{{ $siblings->count() + $gtkCandidates->count() }}</strong> kandidat perlu ditinjau</div></div></div></div>
 <div class="row mb-4"><div class="col-md-4"><div class="relasi-stat"><span class="relasi-stat-icon bg-primary"><i class="fas fa-users"></i></span><div><small>Saudara terdeteksi</small><strong>{{ $siblings->count() }}</strong></div></div></div><div class="col-md-4"><div class="relasi-stat"><span class="relasi-stat-icon bg-success"><i class="fas fa-chalkboard-teacher"></i></span><div><small>Anak GTK</small><strong>{{ $gtkCandidates->count() }}</strong></div></div></div><div class="col-md-4"><div class="relasi-stat"><span class="relasi-stat-icon bg-info"><i class="fas fa-check-double"></i></span><div><small>Sudah diverifikasi</small><strong>{{ $verified->count() }}</strong></div></div></div></div>
 <div class="card card-outline card-primary"><div class="card-header"><h3 class="card-title">Potensi saudara kandung</h3></div><div class="card-body table-responsive p-0"><table class="table table-hover mb-0"><thead><tr><th>Siswa</th><th>Potensi saudara</th><th>Dasar kecocokan</th><th></th></tr></thead><tbody>@forelse($siblings as $item)<tr><td>{{ $item['siswa']->nama_lengkap }} <small class="d-block text-muted">{{ $item['siswa']->kelasSaatIni?->nama_kelas }}</small></td><td>{{ $item['terkait']->nama_lengkap }} <small class="d-block text-muted">{{ $item['terkait']->kelasSaatIni?->nama_kelas }}</small></td><td><span class="badge badge-success">{{ implode(', ', $item['bukti']) }}</span></td><td><form method="POST" action="{{ route('admin.relasi-keluarga.store') }}">@csrf<input type="hidden" name="jenis_relasi" value="saudara_kandung"><input type="hidden" name="siswa_id" value="{{ $item['siswa']->id }}"><input type="hidden" name="siswa_terkait_id" value="{{ $item['terkait']->id }}">@foreach($item['bukti'] as $bukti)<input type="hidden" name="bukti[]" value="{{ $bukti }}">@endforeach<button class="btn btn-sm btn-success">Verifikasi</button></form></td></tr>@empty<tr><td colspan="4" class="text-center text-muted py-4">Tidak ada kandidat dengan KK/NIK orang tua yang sama.</td></tr>@endforelse</tbody></table></div></div>
 <div class="card card-outline card-success"><div class="card-header"><h3 class="card-title">Siswa dengan orang tua GTK</h3></div><div class="card-body table-responsive p-0"><table class="table table-hover mb-0"><thead><tr><th>Siswa</th><th>GTK</th><th>Relasi</th><th></th></tr></thead><tbody>@forelse($gtkCandidates as $item)<tr><td>{{ $item['siswa']->nama_lengkap }}</td><td>{{ $item['gtk']->nama_lengkap }} <small class="d-block text-muted">{{ $item['gtk']->jenis_ptk }}</small></td><td><span class="badge badge-success">NIK {{ strtolower($item['peran']) }} identik</span></td><td><form method="POST" action="{{ route('admin.relasi-keluarga.store') }}">@csrf<input type="hidden" name="jenis_relasi" value="anak_gtk"><input type="hidden" name="siswa_id" value="{{ $item['siswa']->id }}"><input type="hidden" name="gtk_id" value="{{ $item['gtk']->id }}"><input type="hidden" name="bukti[]" value="NIK {{ strtolower($item['peran']) }} identik"><button class="btn btn-sm btn-success">Verifikasi</button></form></td></tr>@empty<tr><td colspan="4" class="text-center text-muted py-4">Tidak ada kecocokan NIK orang tua dengan GTK.</td></tr>@endforelse</tbody></table></div></div>
 <div class="card card-outline card-secondary"><div class="card-header"><h3 class="card-title">Riwayat verifikasi</h3></div><div class="card-body table-responsive p-0"><table class="table table-sm mb-0"><thead><tr><th>Siswa</th><th>Relasi</th><th>Dasar</th><th>Waktu</th></tr></thead><tbody>@forelse($verified as $row)<tr><td>{{ $row->siswa?->nama_lengkap }}</td><td>{{ $row->jenis_relasi === 'anak_gtk' ? 'Anak GTK: '.$row->gtk?->nama_lengkap : 'Saudara: '.$row->siswaTerkait?->nama_lengkap }}</td><td>{{ implode(', ', $row->bukti_kecocokan ?? []) }}</td><td>{{ $row->diverifikasi_pada?->format('d M Y H:i') }}</td></tr>@empty<tr><td colspan="4" class="text-center text-muted py-3">Belum ada relasi terverifikasi.</td></tr>@endforelse</tbody></table></div></div>
</div>
@stop
@section('css')
<style>
.relasi-keluarga .card{border-radius:12px}.relasi-keluarga .card-header{display:flex;align-items:center;justify-content:space-between}.relasi-keluarga .card-title{font-weight:600}.relasi-keluarga .relasi-stat{display:flex;align-items:center;gap:14px;background:#fff;border:1px solid #e5eaf2;border-radius:12px;padding:16px;box-shadow:0 4px 14px rgba(31,45,61,.06);height:100%}.relasi-keluarga .relasi-stat small{display:block;color:#6c757d;font-size:.78rem}.relasi-keluarga .relasi-stat strong{display:block;color:#1f2d3d;font-size:1.55rem;line-height:1.15}.relasi-keluarga .relasi-stat-icon{width:42px;height:42px;border-radius:10px;display:grid;place-items:center;color:#fff}.relasi-keluarga .table thead th{background:#f7f9fc;border-top:0;color:#536273;font-size:.78rem;text-transform:uppercase;letter-spacing:.03em}.relasi-keluarga .table td{vertical-align:middle}.relasi-keluarga .badge{font-weight:500}
</style>
@endsection
@section('js')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
<script>
$(function () {
    toastr.options = {closeButton:true, progressBar:true, positionClass:'toast-top-right', timeOut:4200, preventDuplicates:true};
    @if(session('toastr_success')) toastr.success(@json(session('toastr_success')), 'Berhasil'); @endif
    $('.relasi-keluarga form').on('submit', function (event) {
        event.preventDefault();
        const form = this;
        Swal.fire({title:'Verifikasi relasi ini?', text:'Relasi akan dicatat sebagai data terverifikasi.', icon:'question', showCancelButton:true, confirmButtonText:'Ya, verifikasi', cancelButtonText:'Batal', confirmButtonColor:'#2563eb', reverseButtons:true}).then(function (result) { if (result.isConfirmed) form.submit(); });
    });
});
</script>
@endsection
