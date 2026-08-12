@extends('adminlte::page')
@section('title', 'Beban Kerja GTK')

@section('content_header')
<div class="row mb-2">
    <div class="col-sm-6"><h1><i class="fas fa-balance-scale text-primary"></i> Beban Kerja GTK</h1></div>
    <div class="col-sm-6"><ol class="breadcrumb float-sm-right"><li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li><li class="breadcrumb-item"><a href="{{ route('admin.penugasan-gtk.index') }}">Penugasan GTK</a></li><li class="breadcrumb-item active">Beban Kerja</li></ol></div>
</div>
@stop

@section('content')
<div class="gtk-workload-page">
    <div class="card bg-gradient-primary text-white mb-3"><div class="card-body py-3"><div class="row align-items-center">
        <div class="col-lg-8"><h4 class="mb-1"><i class="fas fa-chart-bar mr-2"></i>Rekap Jam Mengajar & Ekuivalensi</h4><p class="mb-0">Jam aktual berasal dari jadwal, sedangkan ekuivalensi berasal dari penugasan aktif. Buka detail untuk memeriksa sumber perhitungannya.</p></div>
        <div class="col-lg-4 text-lg-right mt-3 mt-lg-0"><a href="{{ route('admin.penugasan-gtk.index') }}" class="btn btn-light btn-sm"><i class="fas fa-arrow-left"></i> Penugasan GTK</a></div>
    </div></div></div>

    <div class="row">@foreach([['GTK Terhitung',$stats['gtk'],'primary'],['Memenuhi',$stats['memenuhi'],'success'],['Kurang',$stats['kurang'],'warning'],['Perlu Verifikasi',$stats['review'],'danger']] as [$label,$value,$color])<div class="col-6 col-xl-3"><div class="card workload-stat border-{{ $color }}"><div class="card-body"><small>{{ $label }}</small><h3 class="text-{{ $color }}">{{ number_format($value) }}</h3></div></div></div>@endforeach</div>

    <div class="card card-outline card-primary"><div class="card-header"><h3 class="card-title"><i class="fas fa-users mr-1"></i>{{ $gtk ? 'Beban Kerja '.$gtk->nama_lengkap : 'Rekap Seluruh GTK' }}</h3></div><div class="card-body">
        <form method="GET" class="workload-filter mb-3"><div class="row">
            <div class="col-md-3"><label>Tahun Pelajaran</label><select name="tahun_pelajaran_id" class="form-control">@foreach($years as $item)<option value="{{ $item->id }}" @selected($year?->id===$item->id)>{{ $item->nama }}</option>@endforeach</select></div>
            <div class="col-md-2"><label>Semester</label><select name="semester" class="form-control"><option value="1" @selected($semester===1)>Ganjil</option><option value="2" @selected($semester===2)>Genap</option></select></div>
            @if($gtk)<input type="hidden" name="gtk_id" value="{{ $gtk->id }}"><div class="col-md-3"><label>GTK</label><input class="form-control" value="{{ $gtk->nama_lengkap }}" disabled></div>@else<div class="col-md-5"><label>Cari GTK</label><div class="input-group"><div class="input-group-prepend"><span class="input-group-text"><i class="fas fa-search"></i></span></div><input type="search" name="q" class="form-control" value="{{ $search }}" placeholder="Nama, NIP, atau NUPTK" autocomplete="off"></div></div>@endif
            <div class="col-md-2 d-flex align-items-end"><button class="btn btn-primary btn-block"><i class="fas fa-sync-alt"></i> Tampilkan</button></div>
        </div>@if($search)<div class="filter-result mt-2"><span><i class="fas fa-filter mr-1"></i>Hasil pencarian <strong>{{ $search }}</strong>: {{ $rows->count() }} GTK</span><a href="{{ route('admin.penugasan-gtk.workload',['tahun_pelajaran_id'=>$year?->id,'semester'=>$semester]) }}" class="ml-2"><i class="fas fa-times"></i> Hapus pencarian</a></div>@endif</form>

        <div class="table-responsive"><table class="table table-hover table-bordered workload-table"><thead><tr><th>GTK</th><th class="text-center">Mengajar</th><th class="text-center">Ekuivalensi</th><th class="text-center">Total</th><th>Penugasan</th><th>Status</th><th class="text-center">Detail</th></tr></thead><tbody>
        @forelse($rows as $row)
            @php($colors=['memenuhi'=>'success','kurang'=>'warning','lebih'=>'danger','review'=>'danger'])
            @php($detailId='workload-detail-'.$row['gtk']->id)
            <tr>
                <td><div class="d-flex"><img src="{{ $row['gtk']->foto_profile_url }}" class="workload-photo mr-2" alt=""><div><strong>{{ $row['gtk']->nama_lengkap }}</strong><small class="d-block text-muted">{{ $row['gtk']->nip ?: 'NIP -' }} · {{ $row['gtk']->jenis_ptk ?: 'GTK' }}</small></div></div></td>
                <td class="text-center"><strong>{{ $row['jtm_mengajar'] }}</strong><small class="d-block text-muted">JTM aktual</small></td>
                <td class="text-center"><strong class="text-primary">{{ $row['jtm_ekuivalensi'] }}</strong><small class="d-block text-muted">JTM diakui</small></td>
                <td class="text-center"><span class="workload-total">{{ $row['jtm_total'] }}</span><small class="d-block text-muted">JTM tercatat</small></td>
                <td>@if($row['tugas_tambahan']->isEmpty())<span class="text-muted">Tidak ada</span>@else<strong>{{ $row['tugas_tambahan']->where('diakui',true)->count() }} tugas diakui</strong><small class="d-block text-muted">{{ $row['tugas_tambahan']->pluck('label')->join(', ') }}</small>@endif</td>
                <td><span class="badge badge-{{ $colors[$row['status']] ?? 'secondary' }}">{{ ['memenuhi'=>'Memenuhi','kurang'=>'Kurang','lebih'=>'Lebih 40 JTM','review'=>'Perlu verifikasi'][$row['status']] ?? ucfirst($row['status']) }}</span>@foreach($row['warnings'] as $warning)<small class="d-block text-danger mt-1"><i class="fas fa-exclamation-triangle"></i> {{ $warning }}</small>@endforeach</td>
                <td class="text-center"><button type="button" class="btn btn-sm btn-primary workload-detail-toggle" data-toggle="collapse" data-target="#{{ $detailId }}" aria-expanded="false" aria-controls="{{ $detailId }}"><i class="fas fa-chevron-down mr-1"></i>Rincian</button></td>
            </tr>
            <tr class="workload-detail-row"><td colspan="7" class="p-0 border-0"><div class="collapse" id="{{ $detailId }}"><div class="workload-detail-panel"><div class="row">
                <div class="col-xl-7"><h6><i class="fas fa-calendar-alt text-primary mr-1"></i> Jadwal Mengajar Semester {{ $semester === 1 ? 'Ganjil' : 'Genap' }}</h6><div class="table-responsive"><table class="table table-sm detail-table mb-0"><thead><tr><th>Hari</th><th>Jam</th><th>Mata Pelajaran</th><th>Rombel</th><th>Ruang</th></tr></thead><tbody>@forelse($row['jadwal'] as $schedule)<tr><td>{{ $schedule->hari_label }}</td><td><strong>Ke-{{ $schedule->jam_ke }}</strong><small class="d-block text-muted">{{ $schedule->jam }}</small></td><td>{{ $schedule->mataPelajaran?->nama_mapel ?: '-' }}</td><td>{{ $schedule->kelas?->nama_kelas ?: '-' }}</td><td>{{ $schedule->ruangan ?: '-' }}</td></tr>@empty<tr><td colspan="5" class="text-center text-muted py-3">Tidak ada jadwal aktif pada semester ini.</td></tr>@endforelse</tbody></table></div></div>
                <div class="col-xl-5 mt-3 mt-xl-0"><h6><i class="fas fa-briefcase text-primary mr-1"></i> Tugas Tambahan & Ekuivalensi</h6><div class="assignment-detail-list">@forelse($row['tugas_tambahan'] as $task)<div class="assignment-detail-item"><div><i class="fas {{ $task['diakui'] ? 'fa-check-circle text-success' : 'fa-info-circle text-muted' }} mr-1"></i><strong>{{ $task['label'] }}</strong></div><span class="badge badge-{{ $task['diakui'] ? 'primary' : 'light' }}">{{ $task['jtm_diakui'] }}/{{ $task['jtm'] }} JTM</span>@if($task['catatan'])<small class="d-block text-warning mt-1">{{ $task['catatan'] }}</small>@endif</div>@empty<div class="empty-assignment"><i class="fas fa-info-circle mr-1"></i>Belum ada tugas tambahan aktif.</div>@endforelse</div><div class="workload-formula mt-3"><span>Mengajar <strong>{{ $row['jtm_mengajar'] }}</strong></span><i class="fas fa-plus"></i><span>Ekuivalensi <strong>{{ $row['jtm_ekuivalensi'] }}</strong></span><i class="fas fa-equals"></i><span>Total <strong>{{ $row['jtm_total'] }} JTM</strong></span></div></div>
            </div></div></div></td></tr>
        @empty<tr><td colspan="7" class="text-center text-muted py-4"><i class="fas fa-search fa-2x d-block mb-2"></i>{{ $search ? 'GTK tidak ditemukan pada periode ini.' : 'Belum ada jadwal atau penugasan pada periode ini.' }}</td></tr>@endforelse
        </tbody></table></div>
        <div class="alert alert-info mb-0"><i class="fas fa-info-circle mr-1"></i>Rekap ini adalah alat kontrol internal. Validasi final SKMT, SKBK, dan TPG tetap mengikuti ketentuan Kementerian Agama serta hasil verifikasi pejabat berwenang.</div>
    </div></div>
</div>
@stop

@section('css')
<style>
.gtk-workload-page .workload-stat{border:1px solid #e2e8f0;border-top-width:3px;box-shadow:0 4px 14px rgba(15,23,42,.06)}.gtk-workload-page .workload-stat .card-body{padding:.8rem}.gtk-workload-page .workload-stat h3{margin:0;font-size:1.4rem;font-weight:700}.gtk-workload-page .workload-stat small{color:#64748b;font-weight:600}.gtk-workload-page .workload-filter{padding:.8rem;background:#f8fafc;border:1px solid #e2e8f0;border-radius:.4rem}.gtk-workload-page .workload-filter label{font-size:.75rem}.gtk-workload-page .filter-result{color:#475569;font-size:.78rem}.gtk-workload-page table{font-size:.82rem}.gtk-workload-page .workload-photo{width:42px;height:52px;object-fit:cover;border-radius:.35rem;border:1px solid #dbe3ef}.gtk-workload-page .workload-total{display:inline-flex;align-items:center;justify-content:center;width:42px;height:42px;border-radius:50%;background:#2563eb;color:#fff;font-weight:700;font-size:1rem}.gtk-workload-page .workload-detail-row:hover{background:transparent}.gtk-workload-page .workload-detail-panel{padding:1rem;border-left:3px solid #2563eb;background:#f8fafc}.gtk-workload-page .workload-detail-panel h6{padding-bottom:.5rem;border-bottom:1px solid #dbe3ef;font-weight:700}.gtk-workload-page .detail-table{background:#fff}.gtk-workload-page .detail-table th{white-space:nowrap;background:#eff6ff}.gtk-workload-page .assignment-detail-item{padding:.65rem;border:1px solid #dbe3ef;border-radius:.4rem;background:#fff}.gtk-workload-page .assignment-detail-item+.assignment-detail-item{margin-top:.5rem}.gtk-workload-page .empty-assignment{padding:.8rem;border:1px dashed #cbd5e1;border-radius:.4rem;color:#64748b;background:#fff}.gtk-workload-page .workload-formula{display:flex;align-items:center;justify-content:center;gap:.65rem;flex-wrap:wrap;padding:.7rem;border-radius:.4rem;background:#e0ecff;color:#1e3a8a}.gtk-workload-page .workload-detail-toggle[aria-expanded=true] i{transform:rotate(180deg)}.gtk-workload-page .workload-detail-toggle i{transition:transform .18s ease}@media(max-width:767.98px){.gtk-workload-page .workload-filter [class*=col-]+[class*=col-]{margin-top:.6rem}.gtk-workload-page .workload-table{min-width:980px}}
</style>
@stop
