@extends('adminlte::page')

@section('title', 'Riwayat Presensi Wajah Saya')

@section('content_header')
    <div class="d-flex flex-wrap justify-content-between align-items-center">
        <div><h1 class="m-0"><i class="fas fa-history text-primary mr-1"></i> Riwayat Presensi Saya</h1><small class="text-muted">Rekaman masuk dan pulang dari kiosk resmi madrasah.</small></div>
        <a href="{{ request()->routeIs('siswa.*') ? route('siswa.face-register') : route('admin.absensi.face-register') }}" class="btn btn-outline-primary btn-sm mt-2 mt-md-0"><i class="fas fa-user-shield mr-1"></i>Status Wajah</a>
    </div>
@stop

@section('content')
<div class="face-history-page pb-4">
    <section class="card bg-gradient-primary text-white border-0 history-hero">
        <div class="d-flex align-items-center">
            <img src="{{ $registrant['avatar_url'] }}" alt="Foto {{ $registrant['name'] }}" class="history-avatar mr-3">
            <div><small class="font-weight-bold text-uppercase">{{ strtoupper($userType) }} · Rekap Pribadi</small><h2 class="h4 font-weight-bold mb-1">{{ $registrant['name'] }}</h2><div class="text-white-50">{{ $userType === 'gtk' ? 'NIP' : 'NISN' }}: {{ $registrant['identifier'] ?: '-' }}</div></div>
        </div>
        <div class="history-period"><small>Periode</small><strong>{{ \Carbon\Carbon::create($year, $month, 1)->translatedFormat('F Y') }}</strong></div>
    </section>

    <div class="row">
        @foreach([
            ['primary','calendar-check','Tercatat',$summary['recorded']],
            ['success','check-circle','Tepat Waktu',$summary['present']],
            ['warning','clock','Terlambat',$summary['late']],
            ['info','sign-out-alt','Sudah Pulang',$summary['checked_out']],
        ] as $item)
            <div class="col-6 col-xl-3"><div class="card history-stat"><div class="card-body"><span class="history-stat__icon bg-{{ $item[0] }}"><i class="fas fa-{{ $item[1] }}"></i></span><div><small>{{ $item[2] }}</small><strong>{{ $item[3] }}</strong></div></div></div></div>
        @endforeach
    </div>

    <div class="card card-outline card-primary">
        <div class="card-header">
            <form method="GET" class="form-inline justify-content-end">
                <label class="mr-2" for="historyMonth">Periode</label>
                <select name="month" id="historyMonth" class="form-control mr-2">
                    @foreach(range(1, 12) as $optionMonth)<option value="{{ $optionMonth }}" @selected($month === $optionMonth)>{{ \Carbon\Carbon::create(2000, $optionMonth, 1)->translatedFormat('F') }}</option>@endforeach
                </select>
                <select name="year" class="form-control mr-2">
                    @foreach($years as $optionYear)<option value="{{ $optionYear }}" @selected($year === (int) $optionYear)>{{ $optionYear }}</option>@endforeach
                    @if(!$years->contains($year))<option value="{{ $year }}" selected>{{ $year }}</option>@endif
                </select>
                <button class="btn btn-primary"><i class="fas fa-filter mr-1"></i>Tampilkan</button>
            </form>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead><tr><th>Tanggal</th><th>Status</th><th>Jam Masuk</th><th>Jam Pulang</th><th>Metode</th><th>Lokasi</th><th>Catatan</th></tr></thead>
                    <tbody>
                    @forelse($attendances as $attendance)
                        <tr>
                            <td><strong>{{ $attendance->tanggal->translatedFormat('d F Y') }}</strong><small class="d-block text-muted">{{ $attendance->tanggal->translatedFormat('l') }}</small></td>
                            <td><span class="badge badge-{{ $attendance->status_badge }} px-2 py-1">{{ ucfirst(str_replace('_', ' ', $attendance->status)) }}</span></td>
                            <td>{{ $attendance->waktu_masuk_formatted }}</td><td>{{ $attendance->waktu_pulang_formatted }}</td>
                            <td>{{ ucfirst(str_replace('_', ' ', $attendance->metode_masuk ?? '-')) }}</td><td>{{ $attendance->location?->nama ?? '-' }}</td><td>{{ $attendance->catatan ?: '-' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center text-muted py-5"><i class="fas fa-calendar-times fa-2x mb-2 d-block"></i>Belum ada rekaman presensi pada periode ini.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($attendances->hasPages())<div class="card-footer">{{ $attendances->links() }}</div>@endif
    </div>
    <div class="alert alert-light border"><i class="fas fa-info-circle text-primary mr-1"></i>Halaman ini hanya menampilkan riwayat. Proses absen wajah dilakukan melalui kamera kiosk yang dipasang di lokasi sekolah.</div>
</div>
@stop

@section('css')
<style>
.history-hero{display:flex;justify-content:space-between;align-items:center;gap:20px;padding:22px;border-radius:16px}.history-avatar{width:72px;height:72px;border-radius:16px;object-fit:cover;border:3px solid rgba(255,255,255,.8)}.history-period{min-width:180px;padding:12px 16px;border:1px solid rgba(255,255,255,.3);border-radius:12px;background:rgba(255,255,255,.12)}.history-period small,.history-period strong{display:block}.history-stat .card-body{display:flex;align-items:center;gap:12px}.history-stat__icon{display:grid;place-items:center;width:46px;height:46px;border-radius:12px;color:#fff}.history-stat small,.history-stat strong{display:block}.history-stat strong{font-size:1.25rem}@media(max-width:767.98px){.history-hero{align-items:flex-start;flex-direction:column}.history-period{width:100%}.face-history-page .form-inline{align-items:stretch;flex-direction:column}.face-history-page .form-inline>*{margin:0 0 8px!important}}
</style>
@stop
