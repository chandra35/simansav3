@extends('adminlte::page')
@section('title', 'Surat Masuk')

@section('content_header')
<div class="row mb-2">
    <div class="col-sm-6"><h1><i class="fas fa-inbox text-primary"></i> Surat Masuk</h1></div>
    <div class="col-sm-6"><ol class="breadcrumb float-sm-right"><li class="breadcrumb-item">Tata Usaha</li><li class="breadcrumb-item active">Surat Masuk</li></ol></div>
</div>
@stop

@section('content')
<div class="tu-surat-masuk">
    <div class="card bg-gradient-primary text-white mb-4">
        <div class="card-body"><div class="row align-items-center">
            <div class="col-lg-8"><h3 class="mb-1">Arsip Surat Masuk & Disposisi</h3><p class="mb-0">PTSP mencatat data surat, mencetak lembar disposisi, lalu mengunggah hasil yang sudah diisi manual.</p></div>
            <div class="col-lg-4 text-lg-right mt-3 mt-lg-0"><a href="{{ route('admin.tata-usaha.surat-masuk.create') }}" class="btn btn-light"><i class="fas fa-plus mr-1"></i> Catat Surat Masuk</a></div>
        </div></div>
    </div>

    <div class="row">
        @foreach ([['total','Total Surat','primary','inbox'],['dicatat','Dicatat PTSP','secondary','edit'],['diprint','Sudah Diprint','info','print'],['selesai','Selesai','success','check-circle']] as [$key,$label,$color,$icon])
        <div class="col-md-3 col-sm-6"><div class="small-box bg-{{ $color }}"><div class="inner"><h3>{{ $stats[$key] }}</h3><p>{{ $label }}</p></div><div class="icon"><i class="fas fa-{{ $icon }}"></i></div></div></div>
        @endforeach
    </div>

    <div class="card card-outline card-primary">
        <div class="card-header"><h3 class="card-title"><i class="fas fa-list mr-1"></i> Daftar Surat Masuk</h3></div>
        <div class="card-body">
            <form class="bg-light rounded p-3 mb-3" method="get">
                <div class="form-row align-items-end">
                    <div class="col-md-4"><label>Pencarian</label><input class="form-control" name="q" value="{{ request('q') }}" placeholder="Berkas, asal, nomor, ringkasan"></div>
                    <div class="col-md-2"><label>Tahun</label><select class="form-control" name="tahun"><option value="">Semua tahun</option>@foreach($years as $year)<option value="{{ $year }}" @selected(request('tahun') == $year)>{{ $year }}</option>@endforeach</select></div>
                    <div class="col-md-2"><label>Status</label><select class="form-control" name="status"><option value="">Semua status</option><option value="dicatat" @selected(request('status') === 'dicatat')>Dicatat PTSP</option><option value="sudah_diprint" @selected(request('status') === 'sudah_diprint')>Sudah Diprint</option><option value="selesai" @selected(request('status') === 'selesai')>Selesai</option></select></div>
                    <div class="col-md-4"><button class="btn btn-primary mr-1"><i class="fas fa-search mr-1"></i>Filter</button><a href="{{ route('admin.tata-usaha.surat-masuk.index') }}" class="btn btn-secondary">Reset</a></div>
                </div>
            </form>
            <div class="table-responsive"><table class="table table-hover align-middle"><thead><tr><th>Berkas</th><th>Tanggal/Nomor</th><th>Asal</th><th>Diterima</th><th>Status</th><th class="text-right">Aksi</th></tr></thead><tbody>
                @forelse($items as $item)<tr><td><strong>{{ $item->nomor_berkas }}</strong><br><small class="text-muted">{{ $item->tahun }}</small></td><td>{{ $item->tanggal_nomor }}<br><small class="text-muted">{{ Str::limit($item->isi_ringkasan, 60) }}</small></td><td>{{ $item->asal }}</td><td>{{ $item->diterima_tanggal?->format('d-m-Y') }}</td><td><span class="badge badge-{{ $item->status_badge }}">{{ $item->status_label }}</span></td><td class="text-right text-nowrap"><a href="{{ route('admin.tata-usaha.surat-masuk.show', $item) }}" class="btn btn-sm btn-primary"><i class="fas fa-eye"></i></a> <a href="{{ route('admin.tata-usaha.surat-masuk.print', $item) }}" target="_blank" class="btn btn-sm btn-info"><i class="fas fa-print"></i></a></td></tr>@empty<tr><td colspan="6" class="text-center text-muted py-4">Belum ada data</td></tr>@endforelse
            </tbody></table></div>
            {{ $items->links() }}
        </div>
    </div>
</div>
@stop

@push('css')<style>.tu-surat-masuk .table td{vertical-align:middle}.tu-surat-masuk .small-box{box-shadow:0 3px 12px rgba(31,41,55,.08)}</style>@endpush
