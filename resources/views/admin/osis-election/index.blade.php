@extends('adminlte::page')
@section('title', 'Pemilihan OSIS')

@section('content_header')
<div class="osis-hero">
    <div><span><i class="fas fa-vote-yea mr-2"></i>Kesiswaan Digital</span><h1>Pemilihan OSIS</h1><p>Kelola paket kandidat, hak pilih, jadwal, dan hasil pemilihan dalam satu alur yang transparan.</p></div>
    @can('manage-osis-election')
        @if($ongoingElection)
            <a href="{{ route('admin.osis-election.show', $ongoingElection) }}" class="btn btn-light"><i class="fas fa-arrow-right mr-1"></i> Buka Pemilihan Aktif</a>
        @else
            <a href="{{ route('admin.osis-election.create') }}" class="btn btn-light"><i class="fas fa-plus mr-1"></i> Buat Pemilihan</a>
        @endif
    @endcan
</div>
@stop

@section('content')
@php
    $all = collect($elections->items());
    $phaseLabels = ['draft'=>['Draft','secondary'],'scheduled'=>['Terjadwal','info'],'open'=>['Sedang Dibuka','success'],'closed'=>['Ditutup','dark']];
@endphp
<div class="row osis-summary">
    @foreach([['Total',$elections->total(),'primary'],['Sedang Dibuka',$all->where('phase','open')->count(),'success'],['Terjadwal',$all->where('phase','scheduled')->count(),'info'],['Draft',$all->where('phase','draft')->count(),'secondary']] as $item)
    <div class="col-6 col-lg-3 mb-3"><div class="osis-stat osis-stat--{{ $item[2] }}"><span>{{ $item[0] }}</span><strong>{{ number_format($item[1]) }}</strong></div></div>
    @endforeach
</div>

<section class="osis-panel">
    <div class="osis-panel-head"><div><h2>Daftar Pemilihan</h2><p>Draft dapat diedit. Setelah dipublikasikan, paket dan daftar pemilih akan dikunci.</p></div></div>
    <div class="row">
        @forelse($elections as $election)
        @php([$phaseText,$phaseColor] = $phaseLabels[$election->phase] ?? ['Status','secondary'])
        <div class="col-md-6 col-xl-4 mb-4"><article class="election-card">
            <div class="election-card-top"><span class="badge badge-{{ $phaseColor }}">{{ $phaseText }}</span><span>{{ $election->tahunPelajaran?->nama }}</span></div>
            <h3>{{ $election->title }}</h3><p>{{ $election->theme ?: 'Pemilihan pengurus OSIS yang aman dan tertib.' }}</p>
            <div class="election-dates"><div><i class="far fa-calendar-alt"></i><span>Mulai<strong>{{ $election->starts_at->format('d M Y · H:i') }}</strong></span></div><div><i class="far fa-clock"></i><span>Selesai<strong>{{ $election->ends_at->format('d M Y · H:i') }}</strong></span></div></div>
            <div class="election-metrics"><span><strong>{{ $election->packages_count }}</strong>Paket</span><span><strong>{{ $election->voters_count }}</strong>Pemilih</span><span><strong>{{ $election->voted_count }}</strong>Suara</span></div>
            <a href="{{ route('admin.osis-election.show',$election) }}" class="btn btn-outline-primary btn-block">Buka Panel Pemilihan <i class="fas fa-arrow-right ml-1"></i></a>
        </article></div>
        @empty
        <div class="col-12"><div class="osis-empty"><i class="fas fa-vote-yea"></i><h3>Belum ada pemilihan</h3><p>Buat periode pemilihan pertama, lalu susun minimal dua paket kandidat.</p></div></div>
        @endforelse
    </div>
    @if($elections->hasPages())<div class="mt-2">{{ $elections->links() }}</div>@endif
</section>
@stop

@section('css')
<style>
.osis-hero{display:flex;justify-content:space-between;align-items:center;gap:1rem;padding:1.4rem 1.55rem;border-radius:17px;background:linear-gradient(135deg,#2563eb,#0f766e);color:#fff;box-shadow:0 16px 36px rgba(37,99,235,.2)}.osis-hero span{font-size:.78rem;font-weight:800;text-transform:uppercase;letter-spacing:.05em}.osis-hero h1{font-size:1.55rem;font-weight:800;color:#fff;margin:.35rem 0}.osis-hero p{margin:0;color:rgba(255,255,255,.86)}.osis-hero .btn{font-weight:700;border-radius:10px;white-space:nowrap}.osis-stat{padding:1rem 1.1rem;border-radius:13px;background:#fff;border:1px solid #dbe4f0;border-top:4px solid #3b82f6;box-shadow:0 8px 22px rgba(15,23,42,.05)}.osis-stat span{display:block;color:#64748b;font-size:.72rem;font-weight:800;text-transform:uppercase}.osis-stat strong{font-size:1.55rem;color:#1d4ed8}.osis-stat--success{border-top-color:#22c55e}.osis-stat--success strong{color:#15803d}.osis-stat--info{border-top-color:#06b6d4}.osis-stat--info strong{color:#0e7490}.osis-stat--secondary{border-top-color:#94a3b8}.osis-stat--secondary strong{color:#475569}.osis-panel{padding:1.25rem;border:1px solid #dbe4f0;border-radius:15px;background:#fff;box-shadow:0 12px 30px rgba(15,23,42,.05)}.osis-panel-head h2{font-size:1.15rem;font-weight:800;color:#0f172a;margin:0}.osis-panel-head p{color:#64748b;margin:.25rem 0 1.1rem}.election-card{height:100%;padding:1.1rem;border:1px solid #dbe4f0;border-radius:14px;background:#fff;box-shadow:0 8px 22px rgba(15,23,42,.04);transition:.18s}.election-card:hover{transform:translateY(-2px);box-shadow:0 14px 28px rgba(15,23,42,.09)}.election-card-top{display:flex;justify-content:space-between;color:#64748b;font-size:.76rem}.election-card h3{font-size:1.05rem;font-weight:800;color:#0f172a;margin:1rem 0 .3rem}.election-card>p{color:#64748b;min-height:42px}.election-dates{display:grid;gap:.5rem;padding:.8rem 0;border-top:1px solid #edf2f7}.election-dates div{display:flex;gap:.65rem;color:#3b82f6}.election-dates span{font-size:.7rem;color:#94a3b8}.election-dates strong{display:block;color:#334155;font-size:.8rem}.election-metrics{display:grid;grid-template-columns:repeat(3,1fr);gap:.5rem;margin:.7rem 0 1rem}.election-metrics span{text-align:center;padding:.55rem;border-radius:9px;background:#f8fafc;color:#64748b;font-size:.68rem}.election-metrics strong{display:block;color:#0f172a;font-size:1rem}.osis-empty{text-align:center;padding:3.5rem 1rem;color:#64748b}.osis-empty>i{font-size:2.6rem;color:#bfdbfe}.osis-empty h3{font-size:1.1rem;color:#1e293b;margin:1rem 0 .3rem}@media(max-width:767.98px){.osis-hero{align-items:flex-start;flex-direction:column}.osis-hero .btn{width:100%}}
</style>
@stop

@section('js')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>@if(session('success'))Swal.fire({icon:'success',title:'Berhasil',text:@json(session('success')),confirmButtonColor:'#2563eb'});@endif @if(session('error'))Swal.fire({icon:'error',title:'Belum dapat diproses',text:@json(session('error')),confirmButtonColor:'#2563eb'});@endif</script>
@stop
