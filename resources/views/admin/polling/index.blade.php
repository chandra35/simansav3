@extends('adminlte::page')

@section('title', 'Polling & Survei')

@section('content_header')
<div class="row mb-2">
    <div class="col-sm-6"><h1><i class="fas fa-poll-h text-primary mr-2"></i>Polling & Survei</h1></div>
    <div class="col-sm-6"><ol class="breadcrumb float-sm-right"><li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li><li class="breadcrumb-item active">Polling & Survei</li></ol></div>
</div>
@stop

@section('content')
<div class="simansa-polling-admin">
    <div class="card bg-gradient-primary text-white mb-4 simansa-polling-hero">
        <div class="card-body">
            <div class="row align-items-center">
                <div class="col-lg-8">
                    <div class="text-uppercase font-weight-bold small mb-2"><i class="fas fa-users mr-1"></i> Respons Terarah</div>
                    <h2 class="h3 font-weight-bold mb-2">Polling & Survei</h2>
                    <p class="mb-0">Susun polling baru atau gunakan kembali riwayat lengkap sebagai preset tanpa mengubah hasil sebelumnya.</p>
                </div>
                @can('manage-polling')
                <div class="col-lg-4 mt-3 mt-lg-0 text-lg-right">
                    <a href="{{ route('admin.polling.create') }}" class="btn btn-light font-weight-bold"><i class="fas fa-plus mr-1"></i> Buat Polling</a>
                </div>
                @endcan
            </div>
        </div>
    </div>

    <div class="row">
        @foreach([
            ['Total Polling', $summary['total'], 'fa-layer-group', 'primary'],
            ['Sedang Dibuka', $summary['open'], 'fa-door-open', 'success'],
            ['Terjadwal', $summary['scheduled'], 'fa-clock', 'warning'],
            ['Selesai', $summary['closed'], 'fa-check-circle', 'secondary'],
        ] as [$label, $value, $icon, $color])
        <div class="col-6 col-lg-3"><div class="info-box bg-white shadow-sm"><span class="info-box-icon bg-{{ $color }}"><i class="fas {{ $icon }}"></i></span><div class="info-box-content"><span class="info-box-text">{{ $label }}</span><span class="info-box-number">{{ $value }}</span></div></div></div>
        @endforeach
    </div>

    <div class="card card-outline card-primary">
        <div class="card-header" id="pollingHistory"><h3 class="card-title font-weight-bold"><i class="fas fa-history text-primary mr-2"></i>Riwayat Polling & Preset</h3></div>
        <div class="card-body border-bottom py-3"><div class="callout callout-info mb-0 py-2"><small>Riwayat tidak dihapus. Tahun ajaran, semester, jadwal, hasil, dan sumber salinan tetap tercatat; gunakan tombol <i class="fas fa-copy"></i> untuk membuat polling baru dari konfigurasi lama.</small></div></div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="bg-light"><tr><th>Polling</th><th>Responden</th><th>Tahun Ajaran / Dibuat</th><th>Jadwal</th><th>Status</th><th class="text-center">Respons</th><th class="text-right">Aksi</th></tr></thead>
                    <tbody>
                    @forelse($pollings as $polling)
                        @php($phase = $polling->phase)
                        <tr class="polling-data-row">
                            <td><button type="button" class="polling-mobile-toggle d-md-none" aria-expanded="false" aria-label="Tampilkan detail {{ $polling->title }}"><i class="fas fa-caret-right"></i></button><strong>{{ $polling->title }}</strong><div class="small text-muted text-truncate" style="max-width:360px">{{ $polling->description_plain ?: 'Tanpa deskripsi' }}</div>@if($polling->sourcePolling)<div class="small text-info mt-1"><i class="fas fa-copy mr-1"></i>Salinan dari {{ $polling->sourcePolling->title }}</div>@endif</td>
                            <td><span class="badge badge-light border text-uppercase">{{ $polling->audience === 'both' ? 'Siswa & GTK' : $polling->audience }}</span></td>
                            <td class="small text-nowrap"><strong>{{ $polling->tahun_pelajaran_snapshot ?: 'Belum tercatat' }}</strong><div class="text-muted">{{ $polling->semester_snapshot ? 'Semester '.$polling->semester_snapshot.' · ' : '' }}{{ $polling->created_at->format('d/m/Y') }}</div></td>
                            <td class="small"><div>{{ $polling->starts_at->format('d/m/Y H:i') }}</div><div class="text-muted">s.d. {{ $polling->ends_at->format('d/m/Y H:i') }}</div></td>
                            <td><span class="badge badge-{{ ['draft'=>'secondary','scheduled'=>'info','open'=>'success','closed'=>'dark'][$phase] }}">{{ ['draft'=>'Draft','scheduled'=>'Terjadwal','open'=>'Dibuka','closed'=>'Ditutup'][$phase] }}</span></td>
                            <td class="text-center"><span class="badge badge-primary badge-pill px-3">{{ $polling->responses_count }}</span></td>
                            <td class="text-right text-nowrap">
                                <a href="{{ route('admin.polling.show', $polling) }}" class="btn btn-sm btn-primary" title="Lihat laporan"><i class="fas fa-chart-bar"></i></a>
                                @can('manage-polling')
                                    <a href="{{ route('admin.polling.duplicate', $polling) }}" class="btn btn-sm btn-info" title="Gunakan sebagai preset"><i class="fas fa-copy"></i></a>
                                    @if(!$polling->responses_count)<a href="{{ route('admin.polling.edit', $polling) }}" class="btn btn-sm btn-warning" title="Edit"><i class="fas fa-edit"></i></a>@endif
                                @endcan
                            </td>
                        </tr>
                        <tr class="polling-mobile-detail-row d-md-none">
                            <td colspan="7">
                                <div class="polling-mobile-details">
                                    <div><span>Responden</span><strong>{{ $polling->audience === 'both' ? 'Siswa & GTK' : Str::upper($polling->audience) }}</strong></div>
                                    <div><span>Tahun ajaran</span><strong>{{ $polling->tahun_pelajaran_snapshot ?: 'Belum tercatat' }}</strong><small>{{ $polling->semester_snapshot ? 'Semester '.$polling->semester_snapshot.' · ' : '' }}dibuat {{ $polling->created_at->format('d/m/Y') }}</small></div>
                                    <div><span>Jadwal</span><strong>{{ $polling->starts_at->format('d/m/Y H:i') }}</strong><small>s.d. {{ $polling->ends_at->format('d/m/Y H:i') }}</small></div>
                                    <div><span>Aksi</span><div class="polling-mobile-actions" role="group" aria-label="Aksi polling {{ $polling->title }}">
                                        <a href="{{ route('admin.polling.show', $polling) }}" class="btn btn-sm btn-primary" title="Lihat laporan" aria-label="Lihat laporan"><i class="fas fa-chart-bar"></i></a>
                                        @can('manage-polling')
                                            <a href="{{ route('admin.polling.duplicate', $polling) }}" class="btn btn-sm btn-info" title="Gunakan sebagai preset" aria-label="Gunakan sebagai preset"><i class="fas fa-copy"></i></a>
                                            @if(!$polling->responses_count)<a href="{{ route('admin.polling.edit', $polling) }}" class="btn btn-sm btn-warning" title="Edit" aria-label="Edit polling"><i class="fas fa-edit"></i></a>@endif
                                        @endcan
                                    </div></div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center text-muted py-5"><i class="fas fa-poll-h fa-3x mb-3 d-block text-light"></i>Belum ada polling.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($pollings->hasPages())<div class="card-footer">{{ $pollings->links() }}</div>@endif
    </div>
</div>
@stop

@section('css')
<style>
.simansa-polling-admin .simansa-polling-hero{border:0;border-radius:18px;overflow:hidden;box-shadow:0 18px 38px rgba(37,99,235,.16)}
.simansa-polling-admin .info-box{border:1px solid #e2e8f0;border-radius:14px;overflow:hidden}.simansa-polling-admin .info-box-icon{width:62px;font-size:1.35rem}
.simansa-polling-admin .table td,.simansa-polling-admin .table th{vertical-align:middle}
.simansa-polling-admin .polling-mobile-toggle{width:28px;height:28px;margin-right:.35rem;padding:0;border:0;border-radius:50%;background:#e8efff;color:#2563eb}
.simansa-polling-admin .polling-mobile-detail-row{display:none}
@media(max-width:767.98px){
    .simansa-polling-admin .card-body{padding:1rem}
    .simansa-polling-admin .table-responsive{overflow-x:visible}
    .simansa-polling-admin .table{min-width:0;table-layout:fixed}
    .simansa-polling-admin .table th:nth-child(2),.simansa-polling-admin .table td:nth-child(2),.simansa-polling-admin .table th:nth-child(3),.simansa-polling-admin .table td:nth-child(3),.simansa-polling-admin .table th:nth-child(4),.simansa-polling-admin .table td:nth-child(4),.simansa-polling-admin .table th:nth-child(7),.simansa-polling-admin .polling-data-row td:nth-child(7){display:none}
    .simansa-polling-admin .table th:first-child{width:58%}.simansa-polling-admin .table th:nth-child(5){width:24%}.simansa-polling-admin .table th:nth-child(6){width:18%}
    .simansa-polling-admin .polling-data-row td{padding:.75rem .4rem}
    .simansa-polling-admin .polling-mobile-detail-row.is-open{display:table-row!important}
    .simansa-polling-admin .polling-mobile-detail-row>td{display:table-cell!important;padding:.75rem;background:#f8fafc;border-top:0}
    .simansa-polling-admin .polling-mobile-details{display:grid;gap:.65rem}
    .simansa-polling-admin .polling-mobile-details>div{display:grid;grid-template-columns:minmax(92px,34%) minmax(0,1fr);gap:.75rem;padding-bottom:.55rem;border-bottom:1px solid #e2e8f0}
    .simansa-polling-admin .polling-mobile-details>div:last-child{padding-bottom:0;border-bottom:0}
    .simansa-polling-admin .polling-mobile-details span{color:#475569;font-size:.72rem;font-weight:800;letter-spacing:.035em;text-transform:uppercase}
    .simansa-polling-admin .polling-mobile-details strong,.simansa-polling-admin .polling-mobile-details small{display:block;min-width:0}
    .simansa-polling-admin .polling-mobile-actions{display:flex;flex-wrap:wrap;gap:.4rem}
    .simansa-polling-admin .polling-mobile-actions .btn{display:inline-flex;width:40px;height:40px;align-items:center;justify-content:center;border-radius:.55rem}
}
</style>
@stop

@section('js')
<script>
$(function(){
    $(document).on('click', '.polling-mobile-toggle', function(event){
        event.preventDefault();
        event.stopPropagation();
        const button = $(this);
        const detailRow = button.closest('.polling-data-row').next('.polling-mobile-detail-row');
        const expanded = !detailRow.hasClass('is-open');
        detailRow.toggleClass('is-open', expanded);
        button.attr('aria-expanded', expanded).find('i').toggleClass('fa-caret-right', !expanded).toggleClass('fa-caret-down', expanded);
    });
});
</script>
@stop
