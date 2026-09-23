@extends('adminlte::page')

@section('title', 'Buku Tamu')

@section('content_header')
    <div class="row mb-2"><div class="col-sm-6"><h1><i class="fas fa-book-open"></i> Buku Tamu</h1></div><div class="col-sm-6"><ol class="breadcrumb float-sm-right"><li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li><li class="breadcrumb-item active">Buku Tamu</li></ol></div></div>
@stop

@section('content')
<div class="container-fluid px-0">
    @if(session('success'))<div class="alert alert-success alert-dismissible fade show"><i class="fas fa-check-circle mr-1"></i>{{ session('success') }}<button type="button" class="close" data-dismiss="alert">&times;</button></div>@endif
    <div class="card bg-gradient-primary text-white mb-3">
        <div class="card-body"><h4 class="mb-1"><i class="fas fa-qrcode mr-2"></i>Buku Tamu PTSP</h4><p class="mb-0">Tamu memindai QR Code sekolah, lalu mengisi data kunjungan melalui halaman publik.</p></div>
    </div>
    <div class="row mb-3">
        <div class="col-md-6"><div class="small-box bg-info"><div class="inner"><h3>{{ $todayCount }}</h3><p>Kunjungan Hari Ini</p></div><div class="icon"><i class="fas fa-calendar-day"></i></div></div></div>
        <div class="col-md-6"><div class="small-box bg-success"><div class="inner"><h3>{{ $monthCount }}</h3><p>Kunjungan Bulan Ini</p></div><div class="icon"><i class="fas fa-users"></i></div></div></div>
    </div>
    <div class="card card-outline card-primary">
        <div class="card-header"><h3 class="card-title"><i class="fas fa-list mr-1"></i> Rekap Kunjungan</h3><div class="card-tools">@can('export-buku-tamu')<a href="{{ route('admin.buku-tamu.export', request()->query()) }}" class="btn btn-success btn-sm"><i class="fas fa-file-csv mr-1"></i> Export CSV</a>@endcan</div></div>
        <div class="card-body">
            <form class="row g-2 mb-3" method="GET"><div class="col-md-3"><input type="date" name="tanggal_mulai" class="form-control" value="{{ request('tanggal_mulai') }}"></div><div class="col-md-3"><input type="date" name="tanggal_selesai" class="form-control" value="{{ request('tanggal_selesai') }}"></div><div class="col-md-4"><input name="q" class="form-control" placeholder="Cari nama, instansi, keperluan" value="{{ request('q') }}"></div><div class="col-md-2 d-flex gap-1"><button class="btn btn-primary flex-fill">Filter</button><a href="{{ route('admin.buku-tamu.index') }}" class="btn btn-outline-secondary">Reset</a></div></form>
            <div class="table-responsive"><table class="table table-hover table-striped"><thead><tr><th>Tanggal</th><th>Jenis</th><th>Nama</th><th>Instansi/Lembaga</th><th>Alamat</th><th>Nomor HP</th><th>Keperluan</th>@can('delete-buku-tamu')<th>Aksi</th>@endcan</tr></thead><tbody>@forelse($items as $item)<tr><td>{{ $item->tanggal_kunjungan?->format('d-m-Y') }}</td><td><span class="badge badge-info">{{ ucfirst($item->jenis_tamu ?? 'individu') }}</span></td><td>{{ $item->nama }}</td><td>{{ $item->alamat_instansi }}</td><td>{{ $item->alamat_lengkap ?: ($item->alamat_instansi ?? '-') }}</td><td>{{ $item->nomor_hp }}</td><td>{{ $item->keperluan }}</td>@can('delete-buku-tamu')<td><form method="POST" action="{{ route('admin.buku-tamu.destroy', $item) }}" onsubmit="return confirm('Arsipkan data kunjungan ini?')">@csrf @method('DELETE')<button class="btn btn-outline-danger btn-sm" title="Arsipkan"><i class="fas fa-archive"></i></button></form></td>@endcan</tr>@empty<tr><td colspan="8" class="text-center text-muted">Belum ada data kunjungan.</td></tr>@endforelse</tbody></table></div>
            {{ $items->links() }}
        </div>
    </div>
    <div class="card card-outline card-secondary"><div class="card-header d-flex justify-content-between align-items-center"><h3 class="card-title"><i class="fas fa-qrcode mr-1"></i> QR Code PTSP</h3>@can('edit-buku-tamu')<form method="POST" action="{{ route('admin.buku-tamu.qr.regenerate') }}" onsubmit="return confirm('Buat QR Code baru? QR Code yang sekarang akan dicabut.')">@csrf<button class="btn btn-outline-primary btn-sm"><i class="fas fa-sync-alt mr-1"></i>Ganti QR</button></form>@endcan</div><div class="card-body text-center"><div class="mb-2">{!! $qrSvg !!}</div><code>{{ $publicUrl }}</code><p class="text-muted mt-2 mb-0">Token QR aktif dibuat {{ $qrToken->created_at?->timezone('Asia/Jakarta')->format('d-m-Y H:i') }} WIB. Cetak QR terbaru untuk meja PTSP.</p></div></div>
</div>
@stop
