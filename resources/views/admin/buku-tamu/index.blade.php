@extends('adminlte::page')

@section('title', 'Buku Tamu PTSP')

@section('css')
<style>
    .guest-admin-hero { position: relative; overflow: hidden; border-radius: 18px; background: linear-gradient(135deg, #0f2b4e, #0f67b1 68%, #19a89e); color: #fff; box-shadow: 0 14px 30px rgba(15, 43, 78, .18); }
    .guest-admin-hero::after { position: absolute; right: -70px; bottom: -100px; width: 280px; height: 280px; border: 32px solid rgba(255,255,255,.08); border-radius: 50%; content: ''; }
    .guest-admin-hero__logo { display: inline-flex; width: 72px; height: 72px; align-items: center; justify-content: center; padding: .6rem; border-radius: 18px; background: rgba(255,255,255,.96); box-shadow: 0 8px 20px rgba(0,0,0,.14); }
    .guest-admin-hero__logo img { width: 100%; height: 100%; object-fit: contain; }
    .guest-admin-hero h2 { margin: 0; font-size: 1.35rem; font-weight: 800; }
    .guest-admin-hero p { max-width: 700px; margin: .35rem 0 0; color: rgba(255,255,255,.78); }
    .guest-admin-hero__meta { position: relative; z-index: 1; min-width: 190px; padding: .8rem 1rem; border: 1px solid rgba(255,255,255,.18); border-radius: 14px; background: rgba(255,255,255,.1); }
    .guest-admin-hero__meta small, .guest-admin-hero__meta strong { display: block; }
    .guest-admin-hero__meta small { color: rgba(255,255,255,.7); }
    .guest-stat { min-height: 108px; border: 1px solid #e2e8f0; border-radius: 14px; background: #fff; box-shadow: 0 8px 20px rgba(15,23,42,.05); }
    .guest-stat__label { color: #64748b; font-size: .72rem; font-weight: 800; letter-spacing: .04em; text-transform: uppercase; }
    .guest-stat__value { margin-top: .15rem; color: #0f172a; font-size: 1.45rem; font-weight: 800; }
    .guest-stat__icon { display: inline-flex; width: 38px; height: 38px; align-items: center; justify-content: center; border-radius: 11px; color: #fff; }
    .visitor-chart { display: grid; gap: .85rem; }
    .visitor-chart__row { display: grid; grid-template-columns: 92px 1fr 42px; gap: .7rem; align-items: center; }
    .visitor-chart__label { color: #475569; font-size: .78rem; font-weight: 700; }
    .visitor-chart__track { height: 12px; overflow: hidden; border-radius: 99px; background: #eaf1f8; }
    .visitor-chart__bar { height: 100%; border-radius: inherit; background: linear-gradient(90deg, #0f67b1, #19a89e); }
    .visitor-chart__value { color: #0f67b1; font-size: .8rem; font-weight: 800; text-align: right; }
    .guest-table td { vertical-align: middle; }
    .guest-table .guest-name { color: #0f2b4e; font-weight: 800; }
    .guest-table .guest-meta { display: block; margin-top: .18rem; color: #64748b; font-size: .7rem; }
    .detail-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: .75rem; }
    .detail-item { min-width: 0; padding: .7rem .8rem; border: 1px solid #e2e8f0; border-radius: 11px; background: #f8fafc; }
    .detail-item small, .detail-item strong { display: block; }
    .detail-item small { margin-bottom: .2rem; color: #64748b; font-size: .68rem; font-weight: 700; text-transform: uppercase; }
    .detail-item strong { color: #1e293b; word-break: break-word; }
    .detail-item--wide { grid-column: 1 / -1; }
    .guest-swal { border-radius: 18px !important; }
    .qr-print-card { display: none; }
    @media print {
        body * { visibility: hidden !important; }
        #qrPrintCard, #qrPrintCard * { visibility: visible !important; }
        #qrPrintCard { position: absolute; top: 0; left: 50%; display: block; width: 70mm; transform: translateX(-50%); }
        .qr-print-card { padding: 7mm 5mm; border: 1px solid #d8e1eb; border-radius: 4mm; color: #0f2b4e; text-align: center; }
        .qr-print-card__logo { width: 16mm; height: 16mm; margin: 0 auto 3mm; object-fit: contain; }
        .qr-print-card__school { margin: 0; font-size: 12pt; font-weight: 800; }
        .qr-print-card__title { margin: 1mm 0 4mm; color: #0f67b1; font-size: 10pt; font-weight: 700; }
        .qr-print-card__code { width: 42mm; height: 42mm; margin: 0 auto 4mm; }
        .qr-print-card__code svg { width: 42mm !important; height: 42mm !important; }
        .qr-print-card__url { margin: 0; overflow-wrap: anywhere; color: #516273; font-size: 7pt; }
        .qr-print-card__hint { margin: 3mm 0 0; color: #64748b; font-size: 8pt; }
    }
    @media (max-width: 767.98px) { .guest-admin-hero__meta { margin-top: 1rem; width: 100%; } .detail-grid { grid-template-columns: 1fr; } .detail-item--wide { grid-column: auto; } }
</style>
@stop

@section('content_header')
<div class="row mb-2"><div class="col-sm-6"><h1><i class="fas fa-book-open text-primary mr-2"></i>Buku Tamu PTSP</h1></div><div class="col-sm-6"><ol class="breadcrumb float-sm-right"><li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li><li class="breadcrumb-item active">Buku Tamu</li></ol></div></div>
@stop

@section('content')
<div class="container-fluid px-0">
    @if(session('success'))<div class="alert alert-success alert-dismissible fade show"><i class="fas fa-check-circle mr-1"></i>{{ session('success') }}<button type="button" class="close" data-dismiss="alert">&times;</button></div>@endif
    <section class="guest-admin-hero p-4 mb-3"><div class="d-flex flex-wrap align-items-center justify-content-between"><div class="d-flex align-items-center"><span class="guest-admin-hero__logo mr-3"><img src="{{ $setting->logo_sekolah_url }}" alt="Logo {{ $setting->nama_sekolah ?? 'sekolah' }}"></span><div><div class="small text-uppercase font-weight-bold" style="letter-spacing:.08em;opacity:.75">Administrasi PTSP</div><h2>Rekap Kunjungan Buku Tamu</h2><p>Kelola data pengunjung, statistik jenis kunjungan, dan laporan resmi dari satu halaman.</p></div></div><div class="guest-admin-hero__meta"><small>QR Code aktif</small><strong><i class="fas fa-qrcode mr-1"></i> Siap dipindai</strong><small class="mt-1">Dibuat {{ $qrToken->created_at?->timezone('Asia/Jakarta')->format('d-m-Y H:i') }} WIB</small></div></div></section>

    <div class="row mb-3"><div class="col-md-3 mb-3 mb-md-0"><div class="guest-stat p-3 d-flex justify-content-between align-items-center"><div><div class="guest-stat__label">Total kunjungan</div><div class="guest-stat__value">{{ number_format($stats['total']) }}</div></div><span class="guest-stat__icon bg-primary"><i class="fas fa-users"></i></span></div></div><div class="col-md-3 mb-3 mb-md-0"><div class="guest-stat p-3 d-flex justify-content-between align-items-center"><div><div class="guest-stat__label">Instansi</div><div class="guest-stat__value">{{ number_format($stats['types']['instansi']) }}</div></div><span class="guest-stat__icon bg-info"><i class="fas fa-building"></i></span></div></div><div class="col-md-3 mb-3 mb-md-0"><div class="guest-stat p-3 d-flex justify-content-between align-items-center"><div><div class="guest-stat__label">Lembaga</div><div class="guest-stat__value">{{ number_format($stats['types']['lembaga']) }}</div></div><span class="guest-stat__icon bg-success"><i class="fas fa-landmark"></i></span></div></div><div class="col-md-3"><div class="guest-stat p-3 d-flex justify-content-between align-items-center"><div><div class="guest-stat__label">Individu</div><div class="guest-stat__value">{{ number_format($stats['types']['individu']) }}</div></div><span class="guest-stat__icon bg-warning"><i class="fas fa-user"></i></span></div></div></div>

    <div class="row"><div class="col-lg-5"><div class="card card-outline card-info h-100"><div class="card-header"><h3 class="card-title"><i class="fas fa-chart-bar mr-1"></i> Statistik Jenis Pengunjung</h3></div><div class="card-body"><div class="visitor-chart">@foreach(['instansi' => ['label' => 'Instansi', 'color' => 'info'], 'lembaga' => ['label' => 'Lembaga', 'color' => 'success'], 'individu' => ['label' => 'Individu', 'color' => 'warning']] as $key => $chart)<div class="visitor-chart__row"><span class="visitor-chart__label">{{ $chart['label'] }}</span><span class="visitor-chart__track"><span class="visitor-chart__bar bg-{{ $chart['color'] }}" style="display:block;width:{{ ($stats['types'][$key] / $maxTypeCount) * 100 }}%"></span></span><span class="visitor-chart__value">{{ $stats['types'][$key] }}</span></div>@endforeach</div><p class="text-muted small mt-3 mb-0"><i class="fas fa-filter mr-1"></i> Statistik mengikuti filter tanggal dan pencarian yang aktif.</p></div></div></div><div class="col-lg-7"><div class="card card-outline card-secondary h-100"><div class="card-header d-flex justify-content-between align-items-center"><h3 class="card-title"><i class="fas fa-qrcode mr-1"></i> QR Code PTSP</h3><div>@can('view-buku-tamu')<button id="btnPrintQr" type="button" class="btn btn-outline-secondary btn-sm mr-1"><i class="fas fa-print mr-1"></i>Cetak QR</button>@endcan @can('edit-buku-tamu')<form id="regenerateQrForm" method="POST" action="{{ route('admin.buku-tamu.qr.regenerate') }}" class="d-none">@csrf</form><button id="btnRegenerateQr" type="button" class="btn btn-outline-primary btn-sm"><i class="fas fa-sync-alt mr-1"></i>Ganti QR</button>@endcan</div></div><div class="card-body d-flex flex-wrap align-items-center"><div class="mr-4 mb-2">{!! $qrSvg !!}</div><div><div class="font-weight-bold text-primary mb-1">QR aktif untuk meja PTSP</div><code class="d-block text-wrap">{{ $publicUrl }}</code><p class="text-muted small mt-2 mb-0">QR ini berlaku tanpa batas waktu dan tetap aktif selama belum dibuat QR baru.</p></div></div></div></div></div>

    <div id="qrPrintCard" class="qr-print-card"><img class="qr-print-card__logo" src="{{ $setting->logo_sekolah_url }}" alt="Logo {{ $setting->nama_sekolah ?? 'sekolah' }}"><p class="qr-print-card__school">{{ $setting->nama_sekolah ?? 'SIMANSA' }}</p><p class="qr-print-card__title">BUKU TAMU PTSP</p><div class="qr-print-card__code">{!! $qrSvg !!}</div><p class="qr-print-card__url">{{ $publicUrl }}</p><p class="qr-print-card__hint">Scan untuk mengisi data kunjungan</p></div>

    <div class="card card-outline card-primary mt-3"><div class="card-header"><h3 class="card-title"><i class="fas fa-list mr-1"></i> Detail Kunjungan</h3><div class="card-tools d-flex flex-wrap" style="gap:.35rem">@can('export-buku-tamu')<a href="{{ route('admin.buku-tamu.export.pdf', request()->only(['tanggal_mulai', 'tanggal_selesai'])) }}" class="btn btn-danger btn-sm"><i class="fas fa-file-pdf mr-1"></i> PDF</a><a href="{{ route('admin.buku-tamu.export', request()->only(['tanggal_mulai', 'tanggal_selesai'])) }}" class="btn btn-success btn-sm"><i class="fas fa-file-excel mr-1"></i> Excel</a>@endcan</div></div><div class="card-body"><form class="row mb-3" method="GET"><div class="col-md-3 mb-2"><label class="small font-weight-bold">Mulai</label><input type="date" name="tanggal_mulai" class="form-control" value="{{ request('tanggal_mulai') }}"></div><div class="col-md-3 mb-2"><label class="small font-weight-bold">Selesai</label><input type="date" name="tanggal_selesai" class="form-control" value="{{ request('tanggal_selesai') }}"></div><div class="col-md-4 mb-2"><label class="small font-weight-bold">Cari</label><input name="q" class="form-control" placeholder="Nama, instansi, alamat, keperluan" value="{{ request('q') }}"></div><div class="col-md-2 d-flex align-items-end mb-2"><button class="btn btn-primary mr-1">Filter</button><a href="{{ route('admin.buku-tamu.index') }}" class="btn btn-outline-secondary">Reset</a></div></form><div class="table-responsive"><table class="table table-hover guest-table"><thead><tr><th>Waktu</th><th>Pengunjung</th><th>Jenis</th><th>Asal</th><th>No. HP</th><th>Keperluan</th><th class="text-right">Aksi</th></tr></thead><tbody>@forelse($items as $item)<tr><td><strong>{{ $item->created_at?->timezone('Asia/Jakarta')->format('d-m-Y') }}</strong><span class="guest-meta">{{ $item->created_at?->timezone('Asia/Jakarta')->format('H:i:s') }} WIB</span></td><td><span class="guest-name">{{ $item->nama }}</span><span class="guest-meta"><i class="fas fa-map-marker-alt mr-1"></i>{{ $item->kota?->name ?: '-' }}</span></td><td><span class="badge badge-{{ $item->jenis_tamu === 'instansi' ? 'info' : ($item->jenis_tamu === 'lembaga' ? 'success' : 'warning') }}">{{ ucfirst($item->jenis_tamu ?? 'individu') }}</span></td><td>{{ $item->alamat_instansi ?: '-' }}<span class="guest-meta">{{ $item->alamat ?: '-' }}</span></td><td>{{ $item->nomor_hp }}</td><td>{{ \Illuminate\Support\Str::limit($item->keperluan, 70) }}</td><td class="text-right"><button type="button" class="btn btn-outline-primary btn-sm js-guest-detail" data-url="{{ route('admin.buku-tamu.show', $item) }}"><i class="fas fa-eye mr-1"></i>Detail</button>@can('delete-buku-tamu')<form class="d-inline" method="POST" action="{{ route('admin.buku-tamu.destroy', $item) }}" onsubmit="return confirm('Arsipkan data kunjungan ini?')">@csrf @method('DELETE')<button class="btn btn-outline-danger btn-sm" title="Arsipkan"><i class="fas fa-archive"></i></button></form>@endcan</td></tr>@empty<tr><td colspan="7" class="text-center text-muted py-4">Belum ada data kunjungan.</td></tr>@endforelse</tbody></table></div>{{ $items->links() }}</div></div>
</div>

<div class="modal fade" id="guestDetailModal" tabindex="-1" aria-labelledby="guestDetailTitle" aria-hidden="true"><div class="modal-dialog modal-lg modal-dialog-centered"><div class="modal-content border-0 shadow-lg"><div class="modal-header bg-gradient-primary text-white"><div><h5 class="modal-title mb-1" id="guestDetailTitle"><i class="fas fa-id-card mr-2"></i>Detail Pengunjung</h5><small id="guestDetailTime" class="text-white-50">Memuat...</small></div><button type="button" class="close text-white" data-dismiss="modal" aria-label="Tutup"><span aria-hidden="true">&times;</span></button></div><div class="modal-body"><div id="guestDetailLoading" class="text-center py-5"><i class="fas fa-circle-notch fa-spin fa-2x text-primary"></i><p class="text-muted mt-2 mb-0">Menyiapkan detail kunjungan...</p></div><div id="guestDetailContent" class="detail-grid d-none"><div class="detail-item"><small>Nama</small><strong data-field="nama"></strong></div><div class="detail-item"><small>Jenis Pengunjung</small><strong data-field="jenis"></strong></div><div class="detail-item"><small>Instansi/Lembaga</small><strong data-field="asal"></strong></div><div class="detail-item"><small>No. HP</small><strong data-field="nomor_hp"></strong></div><div class="detail-item detail-item--wide"><small>Alamat</small><strong data-field="alamat"></strong></div><div class="detail-item detail-item--wide"><small>Keperluan</small><strong data-field="keperluan"></strong></div><div class="detail-item"><small>Waktu Input</small><strong data-field="waktu"></strong></div><div class="detail-item"><small>IP Address</small><strong data-field="ip_address"></strong></div><div class="detail-item"><small>Device</small><strong data-field="device"></strong></div><div class="detail-item"><small>Platform</small><strong data-field="platform"></strong></div><div class="detail-item"><small>Browser</small><strong data-field="browser"></strong></div><div class="detail-item"><small>QR Token</small><strong data-field="qr_token"></strong></div><div class="detail-item detail-item--wide"><small>User-Agent</small><strong data-field="user_agent"></strong></div></div><div id="guestDetailError" class="alert alert-danger d-none">Detail pengunjung tidak dapat dimuat.</div></div><div class="modal-footer"><button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button></div></div></div></div>
@stop

@section('js')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
$(function () {
    const modal = $('#guestDetailModal');
    $('.js-guest-detail').on('click', function () {
        const button = $(this); $('#guestDetailLoading').removeClass('d-none'); $('#guestDetailContent').addClass('d-none'); $('#guestDetailError').addClass('d-none'); $('#guestDetailTime').text('Memuat...'); modal.modal('show');
        $.get(button.data('url')).done(function (data) { Object.keys(data).forEach(function (key) { $('[data-field="' + key + '"]').text(data[key] || '-'); }); $('#guestDetailTime').text(data.waktu || '-'); $('#guestDetailLoading').addClass('d-none'); $('#guestDetailContent').removeClass('d-none'); }).fail(function () { $('#guestDetailLoading').addClass('d-none'); $('#guestDetailError').removeClass('d-none'); });
    });
    $('#btnRegenerateQr').on('click', function () {
        Swal.fire({ icon: 'warning', title: 'Ganti QR Code PTSP?', html: 'QR Code aktif saat ini akan dicabut.<br><strong>QR baru tidak memiliki waktu kedaluwarsa</strong> dan berlaku sampai Anda membuat QR berikutnya.', showCancelButton: true, confirmButtonText: 'Ya, buat QR baru', cancelButtonText: 'Batal', confirmButtonColor: '#0f67b1', cancelButtonColor: '#6c757d', customClass: { popup: 'guest-swal' } }).then(function (result) {
            if (!result.isConfirmed) return;
            Swal.fire({ title: 'Membuat QR Code...', allowOutsideClick: false, allowEscapeKey: false, didOpen: function () { Swal.showLoading(); } });
            document.getElementById('regenerateQrForm').submit();
        });
    });
    $('#btnPrintQr').on('click', function () { window.print(); });
    @if(session('success'))
    Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: @json(session('success')), showConfirmButton: false, timer: 3800, timerProgressBar: true });
    @endif
});
</script>
@stop
