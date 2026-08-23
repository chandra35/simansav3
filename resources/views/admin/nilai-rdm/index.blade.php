@extends('adminlte::page')

@section('title', 'Rekap Nilai RDM')

@section('content_header')
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1><i class="fas fa-clipboard-check text-primary mr-2"></i>Rekap Nilai RDM</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('admin.gtk.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item">Akademik</li>
                <li class="breadcrumb-item active">Rekap Nilai RDM</li>
            </ol>
        </div>
    </div>
@stop

@section('content')
<div class="nilai-rdm-page pb-4">
    <div class="card bg-gradient-primary text-white mb-3">
        <div class="card-body py-3 px-4">
            <div class="row align-items-center">
                <div class="col-lg-8">
                    <div class="small font-weight-bold text-uppercase mb-1"><i class="fas fa-database mr-1"></i> Data akademik terintegrasi</div>
                    <h2 class="h4 font-weight-bold mb-1">Rekap Nilai RDM Rombel Saya</h2>
                    <p class="mb-0">Pantau nilai yang telah masuk dari RDM pada rombel aktif. Halaman ini hanya-baca; pembaruan nilai dilakukan melalui proses sinkronisasi RDM oleh tim akademik.</p>
                </div>
                <div class="col-lg-4 mt-3 mt-lg-0">
                    <div class="rdm-year-box">
                        <small>Tahun pelajaran aktif</small>
                        <strong>{{ $year->nama }}</strong>
                        <span>{{ $classes->count() }} rombel dalam cakupan</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-3">
        <div class="col-md-4 mb-2 mb-md-0">
            <div class="info-box mb-0">
                <span class="info-box-icon bg-primary elevation-1"><i class="fas fa-users"></i></span>
                <div class="info-box-content"><span class="info-box-text">Siswa dalam rombel</span><span class="info-box-number">{{ number_format($rows->count()) }}</span></div>
            </div>
        </div>
        <div class="col-md-4 mb-2 mb-md-0">
            <div class="info-box mb-0">
                <span class="info-box-icon bg-success elevation-1"><i class="fas fa-list-check"></i></span>
                <div class="info-box-content"><span class="info-box-text">Nilai dari RDM</span><span class="info-box-number">{{ number_format($scoreCount) }}</span></div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="info-box mb-0">
                <span class="info-box-icon bg-info elevation-1"><i class="fas fa-book"></i></span>
                <div class="info-box-content"><span class="info-box-text">Mapel telah tersinkron</span><span class="info-box-number">{{ number_format($mapelSummary->count()) }}</span></div>
            </div>
        </div>
    </div>

    <div class="card card-outline card-primary mb-3">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-lock text-primary mr-2"></i>Cakupan Rombel</h3>
        </div>
        <div class="card-body py-3">
            <div class="rdm-class-list">
                @foreach($classes as $class)
                    <span class="badge badge-light border"><i class="fas fa-chalkboard mr-1 text-primary"></i>{{ $class->nama_kelas }}{{ $class->asrama_suffix }}</span>
                @endforeach
            </div>
            <small class="text-muted d-block mt-2">Cakupan ditentukan oleh penugasan aktif dan tidak dapat diubah dari halaman ini.</small>
        </div>
    </div>

    <div class="card card-outline card-primary">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-user-graduate text-primary mr-2"></i>Status Nilai per Siswa</h3>
            <div class="card-tools"><span class="badge badge-light">Sumber: RDM</span></div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0 nilai-rdm-table">
                    <thead><tr><th>Siswa</th><th>Nilai Masuk</th><th>Mapel</th><th>Semester</th><th>Rata-rata</th><th>Sinkron Terakhir</th><th>Status</th><th class="text-center">Detail</th></tr></thead>
                    <tbody>
                    @forelse($rows as $row)
                        <tr>
                            <td><strong>{{ $row->siswa?->nama_lengkap ?? '-' }}</strong><small>NISN {{ $row->siswa?->nisn ?: '-' }}</small></td>
                            <td>{{ number_format($row->nilai_count) }}</td>
                            <td>{{ number_format($row->mapel_count) }}</td>
                            <td>{{ number_format($row->semester_count) }}</td>
                            <td>{{ $row->average !== null ? number_format($row->average, 2) : '-' }}</td>
                            <td>{{ $row->latest_import ? \Carbon\Carbon::parse($row->latest_import)->translatedFormat('d M Y H:i') : '-' }}</td>
                            <td>@if($row->nilai_count)<span class="badge badge-success">Nilai tersedia</span>@else<span class="badge badge-warning">Belum tersinkron</span>@endif</td>
                            <td class="text-center"><a href="{{ route('admin.nilai-rdm.show', $row->siswa->id) }}" class="btn btn-sm btn-outline-primary nilai-rdm-detail-link" aria-label="Detail nilai {{ $row->siswa?->nama_lengkap }}"><i class="fas fa-list-alt mr-1"></i><span>Detail</span></a></td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="text-center text-muted py-5"><i class="fas fa-users d-block mb-2 fa-lg"></i>Belum ada siswa aktif pada rombel dalam cakupan Anda.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="card card-outline card-primary mt-3">
        <div class="card-header"><h3 class="card-title"><i class="fas fa-book-open text-primary mr-2"></i>Ringkasan Mata Pelajaran dari RDM</h3></div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-sm table-hover mb-0 nilai-rdm-table">
                    <thead><tr><th>Mata Pelajaran</th><th>Siswa Bernilai</th><th>Jumlah Nilai</th><th>Rata-rata</th></tr></thead>
                    <tbody>
                    @forelse($mapelSummary as $mapel)
                        <tr><td><strong>{{ $mapel->nama_mapel }}</strong><small>{{ $mapel->kode_mapel }}</small></td><td>{{ number_format($mapel->student_count) }}</td><td>{{ number_format($mapel->nilai_count) }}</td><td>{{ $mapel->average !== null ? number_format($mapel->average, 2) : '-' }}</td></tr>
                    @empty
                        <tr><td colspan="4" class="text-center text-muted py-4">Belum ada nilai RDM tersinkron pada tahun pelajaran aktif.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@stop

@section('css')
<style>
.nilai-rdm-page .card{border-radius:14px;box-shadow:0 7px 18px rgba(15,23,42,.05)}.nilai-rdm-page .rdm-year-box{padding:12px 14px;border:1px solid rgba(255,255,255,.3);border-radius:11px;background:rgba(255,255,255,.12)}.nilai-rdm-page .rdm-year-box small,.nilai-rdm-page .rdm-year-box strong,.nilai-rdm-page .rdm-year-box span{display:block}.nilai-rdm-page .rdm-year-box small{font-size:.72rem;text-transform:uppercase}.nilai-rdm-page .rdm-year-box strong{font-size:1.08rem}.nilai-rdm-page .rdm-year-box span{font-size:.78rem;opacity:.9}.nilai-rdm-page .rdm-class-list{display:flex;gap:8px;flex-wrap:wrap}.nilai-rdm-page .rdm-class-list .badge{padding:7px 10px;font-size:.82rem;font-weight:600}.nilai-rdm-page .nilai-rdm-table thead th{border-top:0;background:#f6f8fc;color:#52627a;font-size:.72rem;text-transform:uppercase;white-space:nowrap}.nilai-rdm-page .nilai-rdm-table td{vertical-align:middle}.nilai-rdm-page .nilai-rdm-table td small{display:block;color:#718096;font-size:.75rem}.nilai-rdm-page .nilai-rdm-detail-link{white-space:nowrap}@media(max-width:767px){.nilai-rdm-page .info-box{min-height:78px}.nilai-rdm-page .card-body{padding-left:1rem;padding-right:1rem}.nilai-rdm-page .nilai-rdm-detail-link span{display:none}.nilai-rdm-page .nilai-rdm-detail-link .mr-1{margin-right:0!important}}
</style>
@stop
