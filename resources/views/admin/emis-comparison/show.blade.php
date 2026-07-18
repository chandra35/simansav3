@extends('adminlte::page')

@section('title', 'Detail Pembanding EMIS')

@php
    $labels = [
        'exact' => ['Sama persis', 'success', 'fa-check-circle'],
        'equivalent' => ['Setara', 'info', 'fa-equals'],
        'similar' => ['Mirip', 'warning', 'fa-adjust'],
        'different' => ['Berbeda', 'danger', 'fa-times-circle'],
        'simansa_empty' => ['Kosong di SIMANSA', 'secondary', 'fa-arrow-right'],
        'emis_empty' => ['Kosong di EMIS', 'secondary', 'fa-arrow-left'],
        'both_empty' => ['Tidak tersedia', 'light', 'fa-minus-circle'],
    ];
    $overallLabels = [
        'exact' => ['Semua data sama', 'success', 'fa-check-circle'],
        'normalized' => ['Data setara setelah normalisasi', 'info', 'fa-equals'],
        'similar' => ['Ada data mirip yang perlu ditinjau', 'warning', 'fa-search'],
        'different' => ['Ada data yang berbeda', 'danger', 'fa-exclamation-triangle'],
    ];
    [$overallText, $overallColor, $overallIcon] = $comparison
        ? ($overallLabels[$comparison['status']] ?? ['Perlu diperiksa', 'secondary', 'fa-search'])
        : ['Tidak ditemukan pada snapshot EMIS', 'secondary', 'fa-cloud'];
    $differentCount = $comparison ? collect($comparison['details'])->whereIn('status', ['different', 'similar', 'simansa_empty', 'emis_empty'])->count() : 0;
@endphp

@section('content_header')
    <div class="simansa-detail-hero">
        <div>
            <div class="simansa-detail-hero__eyebrow"><i class="fas fa-columns"></i> Pembanding Data Siswa</div>
            <h1>{{ $siswa?->nama_lengkap ?? $snapshot?->full_name ?? 'Detail Siswa' }}</h1>
            <p>NISN {{ $siswa?->nisn ?? $snapshot?->nisn ?? '-' }} · Snapshot EMIS ditampilkan berdampingan dengan data SIMANSA.</p>
        </div>
        <div class="simansa-detail-hero__actions">
            <div class="simansa-detail-chip"><span>Field perlu dicek</span><strong>{{ $differentCount }}</strong></div>
            <a href="{{ route('admin.emis-comparison.index') }}" class="btn btn-light"><i class="fas fa-arrow-left mr-1"></i> Kembali ke Daftar</a>
        </div>
    </div>
@stop

@section('content')
    <section class="simansa-detail-status simansa-detail-status--{{ $overallColor }} mb-4">
        <div class="simansa-detail-status__icon"><i class="fas {{ $overallIcon }}"></i></div>
        <div><span>Hasil pembandingan</span><h2>{{ $overallText }}</h2><p>Nilai dibandingkan dengan normalisasi huruf, spasi, tanggal, dan variasi penulisan yang wajar.</p></div>
        @if($snapshot?->synced_at)<div class="simansa-detail-status__time"><span>Waktu snapshot</span><strong>{{ $snapshot->synced_at->format('d/m/Y H:i:s') }} WIB</strong></div>@endif
    </section>

    @if(!$tokenStatus['usable'])
        <section class="simansa-snapshot-note mb-4"><i class="fas fa-lock"></i><div><strong>Mode snapshot tetap aktif</strong><span>Token EMIS sedang tidak aktif. Detail ini tetap dapat dibuka tanpa melakukan request ke API.</span></div></section>
    @endif

    <section class="simansa-detail-section mb-4">
        <div class="simansa-section-head"><div><h3>Perbandingan Field</h3><p>SIMANSA berada di kiri dan EMIS Lembaga di kanan. Baris berwarna menunjukkan data yang perlu ditinjau admin.</p></div></div>
        <div class="table-responsive simansa-comparison-shell">
            <table class="table simansa-comparison-table mb-0">
                <thead><tr><th>Field</th><th class="simansa-head-simansa"><i class="fas fa-database mr-1"></i> SIMANSA</th><th class="text-center">Hasil</th><th class="simansa-head-emis"><i class="fas fa-cloud mr-1"></i> EMIS Lembaga</th></tr></thead>
                <tbody>
                @if($comparison)
                    @foreach($comparison['details'] as $field => $detail)
                        @php
                            [$text, $color, $icon] = $labels[$detail['status']] ?? [ucfirst($detail['status']), 'secondary', 'fa-question-circle'];
                            $left = $detail['simansa']; $right = $detail['emis'];
                            if($field === 'tanggal_lahir') {
                                try { $left = $left ? \Carbon\Carbon::parse($left)->translatedFormat('d F Y') : null; } catch(\Throwable $e) {}
                                try { $right = $right ? \Carbon\Carbon::parse($right)->translatedFormat('d F Y') : null; } catch(\Throwable $e) {}
                            }
                            $rowAttention = in_array($detail['status'], ['different', 'similar', 'simansa_empty', 'emis_empty'], true);
                        @endphp
                        <tr class="{{ $rowAttention ? 'is-attention is-'.$color : '' }}">
                            <th>{{ $detail['label'] }}</th>
                            <td class="simansa-value">{{ filled($left) ? $left : '—' }}</td>
                            <td class="text-center"><span class="badge badge-{{ $color }} simansa-result-badge"><i class="fas {{ $icon }} mr-1"></i>{{ $text }}</span>@if($detail['score'] !== null)<small class="d-block text-muted mt-1">Kemiripan {{ number_format($detail['score'], 1) }}%</small>@endif</td>
                            <td class="simansa-value">{{ filled($right) ? $right : '—' }}</td>
                        </tr>
                    @endforeach
                @else
                    <tr><td colspan="4" class="text-center text-muted py-5"><i class="fas fa-cloud d-block fa-2x mb-2 text-light"></i>Siswa ini tidak ditemukan pada snapshot EMIS terakhir.</td></tr>
                @endif
                </tbody>
            </table>
        </div>
    </section>

    <div class="row">
        <div class="col-md-6 mb-4">
            <section class="simansa-info-card simansa-info-card--blue">
                <div class="simansa-info-card__head"><div class="simansa-info-card__icon"><i class="fas fa-database"></i></div><div><h3>Informasi SIMANSA</h3><p>Data operasional siswa di SIMANSA.</p></div></div>
                <dl class="simansa-info-list"><dt>Nama</dt><dd>{{ $siswa?->nama_lengkap ?? '—' }}</dd><dt>NISN</dt><dd>{{ $siswa?->nisn ?? '—' }}</dd><dt>Kelas</dt><dd>{{ $siswa?->kelasSaatIni?->nama_kelas ?? '—' }}</dd></dl>
                @if($siswa)<a href="{{ route('admin.siswa.show', $siswa) }}" class="btn btn-sm btn-outline-primary"><i class="fas fa-user mr-1"></i> Buka Data Siswa</a>@endif
            </section>
        </div>
        <div class="col-md-6 mb-4">
            <section class="simansa-info-card simansa-info-card--green">
                <div class="simansa-info-card__head"><div class="simansa-info-card__icon"><i class="fas fa-cloud"></i></div><div><h3>Informasi EMIS</h3><p>Metadata siswa pada snapshot Lembaga.</p></div></div>
                <dl class="simansa-info-list"><dt>ID Siswa EMIS</dt><dd>{{ $snapshot?->emis_student_id ?? '—' }}</dd><dt>Tingkat / Rombel</dt><dd>{{ collect([$snapshot?->level_name, $snapshot?->study_group_name])->filter()->implode(' · ') ?: '—' }}</dd><dt>Jurusan</dt><dd>{{ $snapshot?->major_name ?? '—' }}</dd><dt>Tahun Pelajaran</dt><dd>{{ $snapshot?->academic_year ?? '—' }}</dd><dt>NISN Valid</dt><dd>@if($snapshot && $snapshot->valid_nisn !== null)<span class="badge badge-{{ $snapshot->valid_nisn ? 'success' : 'danger' }}">{{ $snapshot->valid_nisn ? 'Ya' : 'Tidak' }}</span>@else — @endif</dd></dl>
            </section>
        </div>
    </div>
@stop

@section('css')
<style>
    .simansa-detail-hero{display:flex;justify-content:space-between;align-items:center;gap:1rem;padding:1.35rem 1.45rem;border-radius:16px;background:#3b82f6;color:#fff;box-shadow:0 14px 32px rgba(59,130,246,.22)}.simansa-detail-hero__eyebrow{font-size:.8rem;font-weight:700;text-transform:uppercase;letter-spacing:.04em;color:rgba(255,255,255,.9);margin-bottom:.55rem}.simansa-detail-hero h1{font-size:1.45rem;font-weight:700;color:#fff;margin:0 0 .35rem}.simansa-detail-hero p{margin:0;color:rgba(255,255,255,.88)}.simansa-detail-hero__actions{display:flex;gap:.7rem;align-items:stretch}.simansa-detail-chip{display:flex;flex-direction:column;justify-content:center;padding:.7rem 1rem;border:1px solid rgba(255,255,255,.2);background:rgba(255,255,255,.14);border-radius:12px;min-width:135px}.simansa-detail-chip span{font-size:.75rem;color:rgba(255,255,255,.78)}.simansa-detail-chip strong{font-size:1.25rem}.simansa-detail-hero .btn{display:flex;align-items:center;border-radius:10px;color:#2563eb;font-weight:600}
    .simansa-detail-status{display:flex;align-items:center;gap:1rem;padding:1rem 1.15rem;background:#fff;border:1px solid #dbe4f0;border-left:4px solid #22c55e;border-radius:14px;box-shadow:0 8px 24px rgba(15,23,42,.04)}.simansa-detail-status--info{border-left-color:#0ea5e9}.simansa-detail-status--warning{border-left-color:#f59e0b}.simansa-detail-status--danger{border-left-color:#e11d48}.simansa-detail-status--secondary{border-left-color:#64748b}.simansa-detail-status__icon{width:46px;height:46px;display:flex;align-items:center;justify-content:center;border-radius:12px;background:#ecfdf5;color:#15803d}.simansa-detail-status--info .simansa-detail-status__icon{background:#f0f9ff;color:#0284c7}.simansa-detail-status--warning .simansa-detail-status__icon{background:#fffbeb;color:#b45309}.simansa-detail-status--danger .simansa-detail-status__icon{background:#fff1f2;color:#be123c}.simansa-detail-status>div:nth-child(2){flex:1}.simansa-detail-status span{font-size:.75rem;text-transform:uppercase;letter-spacing:.04em;color:#64748b;font-weight:700}.simansa-detail-status h2{font-size:1rem;font-weight:700;color:#0f172a;margin:.15rem 0}.simansa-detail-status p{margin:0;color:#64748b}.simansa-detail-status__time{display:flex;flex-direction:column;padding-left:1rem;border-left:1px solid #e2e8f0}.simansa-detail-status__time strong{font-size:.85rem;color:#334155}
    .simansa-snapshot-note{display:flex;align-items:center;gap:.8rem;padding:.85rem 1rem;border:1px solid #fde68a;background:#fffbeb;border-radius:12px;color:#92400e}.simansa-snapshot-note>div{display:flex;flex-direction:column}.simansa-snapshot-note span{font-size:.84rem;color:#a16207}
    .simansa-detail-section,.simansa-info-card{padding:1.1rem 1.25rem;border-radius:14px;background:#fff;border:1px solid #dbe4f0;box-shadow:0 10px 28px rgba(15,23,42,.05)}.simansa-section-head{margin-bottom:1rem}.simansa-section-head h3,.simansa-info-card h3{font-size:1.05rem;font-weight:700;color:#0f172a;margin:0 0 .3rem}.simansa-section-head p,.simansa-info-card p{color:#64748b;line-height:1.5;margin:0}.simansa-comparison-shell{border:1px solid #e5edf7;border-radius:12px;overflow:hidden}.simansa-comparison-table th,.simansa-comparison-table td{vertical-align:middle;padding:1rem;border-color:#edf2f7}.simansa-comparison-table thead th{border:0;background:#f8fafc;color:#64748b;font-size:.78rem;text-transform:uppercase;letter-spacing:.03em}.simansa-head-simansa{background:#eff6ff!important;color:#1d4ed8!important}.simansa-head-emis{background:#ecfdf5!important;color:#15803d!important}.simansa-comparison-table tbody th{color:#475569;font-size:.85rem}.simansa-value{font-size:1rem;font-weight:700;color:#1e293b}.simansa-comparison-table tr.is-danger{background:#fff7f7}.simansa-comparison-table tr.is-warning{background:#fffdf4}.simansa-result-badge{padding:.45rem .65rem}
    .simansa-info-card{height:100%;border-top:4px solid #3b82f6}.simansa-info-card--green{border-top-color:#22c55e}.simansa-info-card__head{display:flex;gap:.8rem;align-items:center;padding-bottom:1rem;border-bottom:1px solid #edf2f7}.simansa-info-card__icon{width:42px;height:42px;display:flex;align-items:center;justify-content:center;border-radius:11px;background:#eef4ff;color:#2563eb}.simansa-info-card--green .simansa-info-card__icon{background:#ecfdf5;color:#15803d}.simansa-info-list{display:grid;grid-template-columns:150px minmax(0,1fr);gap:.65rem 1rem;padding:1rem 0;margin:0}.simansa-info-list dt{color:#64748b;font-size:.82rem}.simansa-info-list dd{margin:0;color:#1e293b;font-weight:600}
    @media(max-width:767.98px){.simansa-detail-hero{align-items:flex-start;flex-direction:column}.simansa-detail-hero__actions{width:100%;flex-wrap:wrap}.simansa-detail-status{align-items:flex-start;flex-wrap:wrap}.simansa-detail-status__time{width:100%;padding-left:0;border-left:0}.simansa-info-list{grid-template-columns:1fr}.simansa-info-list dd{margin-bottom:.35rem}}
</style>
@stop
