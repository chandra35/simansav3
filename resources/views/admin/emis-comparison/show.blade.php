@extends('adminlte::page')

@section('title', 'Detail Pembanding EMIS')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="mb-1"><i class="fas fa-columns mr-2 text-primary"></i>Detail Pembanding</h1>
            <p class="text-muted mb-0">{{ $siswa?->nama_lengkap ?? $snapshot?->full_name ?? 'Data siswa' }}</p>
        </div>
        <a href="{{ route('admin.emis-comparison.index') }}" class="btn btn-default"><i class="fas fa-arrow-left mr-1"></i>Kembali</a>
    </div>
@stop

@section('content')
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
            'exact' => ['Semua data sama', 'success'],
            'normalized' => ['Data sama setelah normalisasi', 'info'],
            'similar' => ['Ada data mirip yang perlu diperiksa', 'warning'],
            'different' => ['Ada data yang berbeda', 'danger'],
        ];
        [$overallText, $overallColor] = $comparison
            ? ($overallLabels[$comparison['status']] ?? ['Perlu diperiksa', 'secondary'])
            : ['Siswa tidak ditemukan pada snapshot EMIS', 'secondary'];
    @endphp

    @if(!$tokenStatus['usable'])
        <div class="alert alert-warning">
            <i class="fas fa-lock mr-1"></i>
            Token EMIS tidak aktif. Detail ini berasal dari snapshot terakhir dan tidak memanggil API.
        </div>
    @endif

    <div class="card card-outline card-{{ $overallColor }}">
        <div class="card-header d-flex align-items-center">
            <h3 class="card-title"><span class="badge badge-{{ $overallColor }} p-2">{{ $overallText }}</span></h3>
            @if($snapshot?->synced_at)
                <span class="ml-auto text-muted small"><i class="fas fa-clock mr-1"></i>Snapshot {{ $snapshot->synced_at->format('d/m/Y H:i:s') }} WIB</span>
            @endif
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table comparison-table mb-0">
                    <thead>
                    <tr>
                        <th style="width:18%">Field</th>
                        <th style="width:31%" class="bg-light-blue">SIMANSA</th>
                        <th style="width:20%" class="text-center">Hasil</th>
                        <th style="width:31%" class="bg-light-green">EMIS Lembaga</th>
                    </tr>
                    </thead>
                    <tbody>
                    @if($comparison)
                        @foreach($comparison['details'] as $field => $detail)
                            @php
                                [$text, $color, $icon] = $labels[$detail['status']] ?? [ucfirst($detail['status']), 'secondary', 'fa-question-circle'];
                                $left = $detail['simansa'];
                                $right = $detail['emis'];
                                if($field === 'tanggal_lahir') {
                                    try { $left = $left ? \Carbon\Carbon::parse($left)->translatedFormat('d F Y') : null; } catch(\Throwable $e) {}
                                    try { $right = $right ? \Carbon\Carbon::parse($right)->translatedFormat('d F Y') : null; } catch(\Throwable $e) {}
                                }
                            @endphp
                            <tr class="{{ $detail['status'] === 'different' ? 'table-danger' : ($detail['status'] === 'similar' ? 'table-warning' : '') }}">
                                <th>{{ $detail['label'] }}</th>
                                <td class="value-cell">{{ filled($left) ? $left : '—' }}</td>
                                <td class="text-center align-middle">
                                    <span class="badge badge-{{ $color }} p-2"><i class="fas {{ $icon }} mr-1"></i>{{ $text }}</span>
                                    @if($detail['score'] !== null)
                                        <small class="d-block text-muted mt-1">Kemiripan {{ number_format($detail['score'], 1) }}%</small>
                                    @endif
                                </td>
                                <td class="value-cell">{{ filled($right) ? $right : '—' }}</td>
                            </tr>
                        @endforeach
                    @else
                        <tr><td colspan="4" class="text-center py-5 text-muted">Siswa ini tidak ditemukan pada snapshot EMIS terakhir.</td></tr>
                    @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="card card-outline card-primary">
                <div class="card-header"><h3 class="card-title"><i class="fas fa-database mr-1"></i>Informasi SIMANSA</h3></div>
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-sm-4">Nama</dt><dd class="col-sm-8">{{ $siswa?->nama_lengkap ?? '—' }}</dd>
                        <dt class="col-sm-4">NISN</dt><dd class="col-sm-8">{{ $siswa?->nisn ?? '—' }}</dd>
                        <dt class="col-sm-4">Kelas</dt><dd class="col-sm-8">{{ $siswa?->kelasSaatIni?->nama_kelas ?? '—' }}</dd>
                    </dl>
                    @if($siswa)
                        <a href="{{ route('admin.siswa.show', $siswa) }}" class="btn btn-sm btn-outline-primary mt-3"><i class="fas fa-user mr-1"></i>Buka Data Siswa</a>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card card-outline card-success">
                <div class="card-header"><h3 class="card-title"><i class="fas fa-cloud mr-1"></i>Informasi EMIS</h3></div>
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-sm-5">ID Siswa EMIS</dt><dd class="col-sm-7">{{ $snapshot?->emis_student_id ?? '—' }}</dd>
                        <dt class="col-sm-5">Tingkat</dt><dd class="col-sm-7">{{ $snapshot?->level_name ?? '—' }}</dd>
                        <dt class="col-sm-5">Rombel</dt><dd class="col-sm-7">{{ $snapshot?->study_group_name ?? '—' }}</dd>
                        <dt class="col-sm-5">Jurusan</dt><dd class="col-sm-7">{{ $snapshot?->major_name ?? '—' }}</dd>
                        <dt class="col-sm-5">Tahun Pelajaran</dt><dd class="col-sm-7">{{ $snapshot?->academic_year ?? '—' }}</dd>
                        <dt class="col-sm-5">NISN Valid</dt><dd class="col-sm-7">
                            @if($snapshot && $snapshot->valid_nisn !== null)
                                <span class="badge badge-{{ $snapshot->valid_nisn ? 'success' : 'danger' }}">{{ $snapshot->valid_nisn ? 'Ya' : 'Tidak' }}</span>
                            @else — @endif
                        </dd>
                    </dl>
                </div>
            </div>
        </div>
    </div>
@stop

@section('css')
<style>
    .comparison-table th, .comparison-table td { vertical-align: middle; padding: 1rem; }
    .comparison-table .value-cell { font-size: 1.05rem; font-weight: 600; }
    .bg-light-blue { background: #eef6ff; }
    .bg-light-green { background: #effaf3; }
</style>
@stop
