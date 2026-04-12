@extends('adminlte::page')

@section('title', 'Nilai CBT - ' . $smartq->nama)

@section('content_header')
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1><i class="fas fa-chart-line text-primary"></i> Smart Score — Nilai CBT Moodle</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.smartq.index') }}">SMART-Q</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.smartq.show', $smartq) }}">{{ $smartq->nama }}</a></li>
                <li class="breadcrumb-item active">Nilai CBT</li>
            </ol>
        </div>
    </div>
@stop

@section('content')
@php
    // ── Collect all unique quizzes (mapel) ──
    $allMapel = collect($rows)->flatMap(fn($r) => $r['scores'] ?? [])->unique('quiz_id')->sortBy('quiz_name')->values();
    $mapelCount = $allMapel->count();
    $totalStudents = count($rows);

    // ── Score analytics per mapel ──
    $mapelStats = [];
    foreach ($allMapel as $m) {
        $qid = $m['quiz_id'];
        $scores = collect($rows)->flatMap(fn($r) => $r['scores'] ?? [])->where('quiz_id', $qid);
        $nonZero = $scores->where('normalized_100', '>', 0);
        $mapelStats[$qid] = [
            'name' => $m['quiz_name'],
            'total_attempts' => $nonZero->count(),
            'avg' => $nonZero->count() > 0 ? round($nonZero->avg('normalized_100'), 1) : 0,
            'max' => $nonZero->count() > 0 ? round($nonZero->max('normalized_100'), 1) : 0,
            'min' => $nonZero->count() > 0 ? round($nonZero->min('normalized_100'), 1) : 0,
        ];
    }
    $mapelWajib = collect($mapelStats)->filter(fn($s) => $s['total_attempts'] > ($totalStudents * 0.5))->keys()->toArray();
    $mapelPilihan = collect($mapelStats)->reject(fn($s) => $s['total_attempts'] > ($totalStudents * 0.5))->keys()->toArray();

    // ── Group students by kelas (using siswa_kelas or moodle_lastname) ──
    $rowsCollection = collect($rows);
    $byKelas = $rowsCollection->groupBy(function($r) {
        return $r['siswa_kelas'] ?? $r['moodle_lastname'] ?? 'Tanpa Kelas';
    })->sortKeys();

    // Parse tingkat from kelas name
    $parseTingkat = function($kelas) {
        if (preg_match('/\bXII\b|tingkat\s*12|kelas\s*12/i', $kelas)) return 12;
        if (preg_match('/\bXI\b|tingkat\s*11|kelas\s*11/i', $kelas)) return 11;
        if (preg_match('/\bX\b|tingkat\s*10|kelas\s*10/i', $kelas)) return 10;
        return 0;
    };

    // Group by tingkat
    $byTingkat = [];
    foreach ($byKelas as $kelasName => $kelasRows) {
        $tkt = $parseTingkat($kelasName);
        $label = $tkt ? 'Tingkat ' . $tkt : 'Lainnya';
        $byTingkat[$label][$kelasName] = $kelasRows;
    }
    ksort($byTingkat);

    // ── Students with zero scores on ALL quizzes = Tidak Hadir ──
    $hadir = $rowsCollection->filter(fn($r) => ($r['has_attempt'] ?? false))->count();
    $tidakHadir = $totalStudents - $hadir;

    // ── Overall stats ──
    $avgAll = $rowsCollection->where('has_attempt', true)->avg('normalized_100');
    $maxAll = $rowsCollection->where('has_attempt', true)->max('normalized_100');
    $minAll = $rowsCollection->where('has_attempt', true)->min('normalized_100');
@endphp

    {{-- HEADER INFO --}}
    <div class="callout callout-primary">
        <div class="d-flex justify-content-between align-items-center flex-wrap">
            <div>
                <h5 class="mb-1"><i class="fas fa-satellite-dish"></i> {{ $smartq->nama }} — Nilai CBT Moodle</h5>
                <span class="text-muted">
                    <i class="fas fa-clock"></i> Discan: <strong>{{ $smartq->last_scan_at?->format('d M Y H:i') ?? '-' }}</strong>
                    &middot; <i class="fas fa-server"></i> {{ $smartq->moodle_base_url }}
                    &middot; <strong>{{ $mapelCount }}</strong> mapel, <strong>{{ $totalStudents }}</strong> siswa
                </span>
            </div>
            <div class="mt-2 mt-md-0">
                <a href="{{ route('admin.smartq.moodle.scan', $smartq) }}" class="btn btn-sm btn-outline-primary mr-1" title="Scan ulang dari Moodle">
                    <i class="fas fa-sync"></i> Rescan
                </a>
                <a href="{{ route('admin.smartq.moodle.scan.export', ['smartq' => $smartq, 'format' => 'excel']) }}"
                   class="btn btn-sm btn-success mr-1" id="btnExportExcel">
                    <i class="fas fa-file-excel"></i> Excel
                </a>
                <a href="{{ route('admin.smartq.moodle.scan.export', ['smartq' => $smartq, 'format' => 'pdf']) }}"
                   class="btn btn-sm btn-danger" id="btnExportPdf">
                    <i class="fas fa-file-pdf"></i> PDF
                </a>
            </div>
        </div>
    </div>

    {{-- OVERVIEW STATS --}}
    <div class="row">
        <div class="col-lg-2 col-md-4 col-6">
            <div class="small-box bg-info">
                <div class="inner"><h3>{{ $totalStudents }}</h3><p>Total Siswa</p></div>
                <div class="icon"><i class="fas fa-users"></i></div>
            </div>
        </div>
        <div class="col-lg-2 col-md-4 col-6">
            <div class="small-box bg-success">
                <div class="inner"><h3>{{ $hadir }}</h3><p>Hadir (Mengerjakan)</p></div>
                <div class="icon"><i class="fas fa-user-check"></i></div>
            </div>
        </div>
        <div class="col-lg-2 col-md-4 col-6">
            <div class="small-box bg-danger">
                <div class="inner"><h3>{{ $tidakHadir }}</h3><p>Tidak Hadir</p></div>
                <div class="icon"><i class="fas fa-user-times"></i></div>
            </div>
        </div>
        <div class="col-lg-2 col-md-4 col-6">
            <div class="small-box bg-primary">
                <div class="inner"><h3>{{ round($avgAll ?? 0, 1) }}</h3><p>Rata-rata</p></div>
                <div class="icon"><i class="fas fa-chart-bar"></i></div>
            </div>
        </div>
        <div class="col-lg-2 col-md-4 col-6">
            <div class="small-box bg-warning">
                <div class="inner"><h3>{{ round($maxAll ?? 0, 1) }}</h3><p>Nilai Tertinggi</p></div>
                <div class="icon"><i class="fas fa-arrow-up"></i></div>
            </div>
        </div>
        <div class="col-lg-2 col-md-4 col-6">
            <div class="small-box bg-secondary">
                <div class="inner"><h3>{{ round($minAll ?? 0, 1) }}</h3><p>Nilai Terendah</p></div>
                <div class="icon"><i class="fas fa-arrow-down"></i></div>
            </div>
        </div>
    </div>

    {{-- ANALISIS PER MAPEL --}}
    <div class="card card-outline card-primary">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-chart-bar"></i> Analisis Nilai per Mata Pelajaran</h3>
            <div class="card-tools">
                <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i></button>
            </div>
        </div>
        <div class="card-body p-0">
            <table class="table table-sm table-bordered mb-0">
                <thead class="thead-light">
                    <tr>
                        <th width="30">#</th>
                        <th>Mata Pelajaran</th>
                        <th class="text-center" width="60">Tipe</th>
                        <th class="text-center" width="80">Mengerjakan</th>
                        <th class="text-center" width="80">Tidak Hadir</th>
                        <th class="text-center" width="70">Rata-rata</th>
                        <th class="text-center" width="70">Tertinggi</th>
                        <th class="text-center" width="70">Terendah</th>
                        <th>Keterangan</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($mapelStats as $qid => $stat)
                        @php
                            $isWajib = in_array($qid, $mapelWajib);
                            $absent = $isWajib ? ($totalStudents - $stat['total_attempts']) : 0;
                            if ($stat['total_attempts'] === 0) {
                                $ket = '<span class="text-danger"><i class="fas fa-exclamation-circle"></i> Belum ada yang mengerjakan</span>';
                            } elseif ($stat['avg'] >= 80) {
                                $ket = '<span class="text-success"><i class="fas fa-trophy"></i> Sangat baik</span>';
                            } elseif ($stat['avg'] >= 60) {
                                $ket = '<span class="text-primary"><i class="fas fa-thumbs-up"></i> Cukup baik</span>';
                            } elseif ($stat['avg'] >= 40) {
                                $ket = '<span class="text-warning"><i class="fas fa-exclamation-triangle"></i> Perlu perhatian</span>';
                            } else {
                                $ket = '<span class="text-danger"><i class="fas fa-times-circle"></i> Kritis</span>';
                            }
                            if (!$isWajib) {
                                $notTaken = $totalStudents - $stat['total_attempts'];
                                $ket .= '<br><small class="text-muted">' . $notTaken . ' siswa tidak mengambil mapel ini</small>';
                            } elseif ($absent > 0) {
                                $ket .= '<br><small class="text-danger">' . $absent . ' siswa tidak hadir / belum mengerjakan</small>';
                            }
                        @endphp
                        <tr>
                            <td class="text-muted">{{ $loop->iteration }}</td>
                            <td><strong>{{ $stat['name'] }}</strong></td>
                            <td class="text-center">
                                <span class="badge {{ $isWajib ? 'badge-dark' : 'border badge-light' }}">{{ $isWajib ? 'Wajib' : 'Pilihan' }}</span>
                            </td>
                            <td class="text-center"><strong>{{ $stat['total_attempts'] }}</strong> <small class="text-muted">/{{ $totalStudents }}</small></td>
                            <td class="text-center">
                                @if($isWajib && $absent > 0)
                                    <span class="text-danger"><strong>{{ $absent }}</strong></span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td class="text-center"><strong class="{{ $stat['avg'] >= 80 ? 'text-success' : ($stat['avg'] >= 60 ? 'text-primary' : ($stat['avg'] >= 40 ? 'text-warning' : 'text-danger')) }}">{{ $stat['avg'] }}</strong></td>
                            <td class="text-center text-success"><strong>{{ $stat['max'] }}</strong></td>
                            <td class="text-center text-danger"><strong>{{ $stat['min'] }}</strong></td>
                            <td style="white-space:normal;font-size:0.85rem">{!! $ket !!}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- NILAI PER TINGKAT/KELAS --}}
    @foreach($byTingkat as $tingkatLabel => $kelasList)
        <div class="card card-outline card-dark">
            <div class="card-header bg-gradient-dark">
                <h3 class="card-title">
                    <i class="fas fa-layer-group"></i> {{ $tingkatLabel }}
                    <span class="badge badge-light ml-1">{{ collect($kelasList)->flatten(1)->count() }} siswa</span>
                </h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-tool text-white" data-card-widget="collapse"><i class="fas fa-minus"></i></button>
                </div>
            </div>
            <div class="card-body p-0">
                @foreach($kelasList as $kelasName => $kelasRows)
                    @php
                        $kelasCollection = collect($kelasRows);
                        $kelasHadir = $kelasCollection->filter(fn($r) => ($r['has_attempt'] ?? false))->count();
                        $kelasTdkHadir = $kelasCollection->count() - $kelasHadir;
                        $kelasAvg = $kelasCollection->where('has_attempt', true)->avg('normalized_100');
                    @endphp
                    <div class="px-3 pt-3 pb-1">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h6 class="mb-0">
                                <i class="fas fa-chalkboard"></i> <strong>{{ $kelasName }}</strong>
                                <span class="badge badge-info">{{ $kelasCollection->count() }} siswa</span>
                                <span class="badge badge-success">{{ $kelasHadir }} hadir</span>
                                @if($kelasTdkHadir > 0)
                                    <span class="badge badge-danger">{{ $kelasTdkHadir }} tidak hadir</span>
                                @endif
                                @if($kelasAvg)
                                    <span class="badge badge-primary">Rata&sup2;: {{ round($kelasAvg, 1) }}</span>
                                @endif
                            </h6>
                            <div class="input-group input-group-sm" style="width:200px">
                                <input type="text" class="form-control search-kelas" data-target="kelas-{{ md5($kelasName) }}" placeholder="Cari..." autocomplete="off">
                                <div class="input-group-append"><span class="input-group-text"><i class="fas fa-search"></i></span></div>
                            </div>
                        </div>
                    </div>
                    <div style="overflow-x:auto">
                        <table class="table table-bordered table-hover table-sm mb-0" style="white-space:nowrap">
                            <thead class="thead-light">
                                <tr>
                                    <th width="30" class="text-center">#</th>
                                    <th style="min-width:180px">Nama Siswa</th>
                                    <th width="110">NISN</th>
                                    @foreach($allMapel as $mapel)
                                        <th class="text-center {{ in_array($mapel['quiz_id'], $mapelWajib) ? '' : 'bg-light' }}"
                                            style="min-width:70px;max-width:120px;white-space:normal;font-size:0.78rem">
                                            {{ $mapel['quiz_name'] }}
                                        </th>
                                    @endforeach
                                    @if($mapelCount > 1)
                                        <th width="70" class="text-center bg-primary text-white">Rata&sup2;</th>
                                    @endif
                                    <th width="100" class="text-center">Kehadiran</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($kelasCollection->sortByDesc('normalized_100') as $j => $row)
                                    @php
                                        $rowScores = collect($row['scores'] ?? [])->keyBy('quiz_id');
                                        $isHadir = $row['has_attempt'] ?? false;
                                        // Detect mapel pilihan siswa ini
                                        $pilihanScored = collect($row['scores'] ?? [])->filter(fn($s) => in_array($s['quiz_id'], $mapelPilihan) && $s['normalized_100'] > 0);
                                    @endphp
                                    <tr class="kelas-{{ md5($kelasName) }} {{ !$isHadir ? 'table-danger' : '' }}"
                                        data-search="{{ strtolower(($row['moodle_firstname'] ?? $row['moodle_fullname'] ?? '') . ' ' . ($row['moodle_username'] ?? '') . ' ' . ($row['siswa_nama'] ?? '')) }}">
                                        <td class="text-center text-muted">{{ $j + 1 }}</td>
                                        <td>
                                            <strong>{{ $row['siswa_nama'] ?? ($row['moodle_firstname'] ?? $row['moodle_fullname'] ?? '-') }}</strong>
                                            @if(($row['siswa_nama'] ?? null) && ($row['moodle_firstname'] ?? null) && strtolower($row['siswa_nama']) !== strtolower($row['moodle_firstname']))
                                                <br><small class="text-muted">&crarr; {{ $row['moodle_firstname'] }}</small>
                                            @endif
                                        </td>
                                        <td><code>{{ $row['siswa_nisn'] ?? $row['moodle_username'] ?? '-' }}</code></td>
                                        @foreach($allMapel as $mapel)
                                            @php $score = $rowScores->get($mapel['quiz_id']); @endphp
                                            <td class="text-center">
                                                @if($score)
                                                    @php $val = $score['normalized_100']; @endphp
                                                    <span class="badge {{ $val >= 80 ? 'badge-success' : ($val >= 60 ? 'badge-primary' : ($val >= 40 ? 'badge-warning' : 'badge-danger')) }}">{{ $val }}</span>
                                                @elseif(!$isHadir)
                                                    <span class="text-danger" title="Tidak hadir"><i class="fas fa-times fa-xs"></i></span>
                                                @elseif(in_array($mapel['quiz_id'], $mapelPilihan))
                                                    <span class="text-muted" title="Bukan mapel pilihan siswa">&middot;</span>
                                                @else
                                                    <span class="text-danger" title="Tidak mengerjakan mapel wajib"><i class="fas fa-exclamation-circle fa-xs"></i></span>
                                                @endif
                                            </td>
                                        @endforeach
                                        @if($mapelCount > 1)
                                            <td class="text-center">
                                                @if($isHadir)
                                                    <strong class="{{ ($row['normalized_100'] ?? 0) >= 70 ? 'text-success' : (($row['normalized_100'] ?? 0) >= 50 ? 'text-primary' : 'text-danger') }}">
                                                        {{ $row['normalized_100'] ?? 0 }}
                                                    </strong>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                        @endif
                                        <td class="text-center">
                                            @if($isHadir)
                                                <span class="badge badge-success"><i class="fas fa-check"></i> Hadir</span>
                                            @else
                                                <span class="badge badge-danger"><i class="fas fa-times"></i> Tidak Hadir</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="bg-light">
                                <tr>
                                    <td colspan="3" class="text-right"><strong>Rata-rata Kelas:</strong></td>
                                    @foreach($allMapel as $mapel)
                                        @php
                                            $qid = $mapel['quiz_id'];
                                            $kelasMapelScores = $kelasCollection->flatMap(fn($r) => $r['scores'] ?? [])->where('quiz_id', $qid)->where('normalized_100', '>', 0);
                                            $kelasMapelAvg = $kelasMapelScores->count() > 0 ? round($kelasMapelScores->avg('normalized_100'), 1) : 0;
                                        @endphp
                                        <td class="text-center">
                                            @if($kelasMapelAvg > 0)
                                                <strong class="{{ $kelasMapelAvg >= 80 ? 'text-success' : ($kelasMapelAvg >= 60 ? 'text-primary' : ($kelasMapelAvg >= 40 ? 'text-warning' : 'text-danger')) }}">{{ $kelasMapelAvg }}</strong>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                    @endforeach
                                    @if($mapelCount > 1)
                                        <td class="text-center"><strong class="text-primary">{{ round($kelasAvg ?? 0, 1) }}</strong></td>
                                    @endif
                                    <td></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                    @if(!$loop->last)
                        <hr class="my-0">
                    @endif
                @endforeach
            </div>
        </div>
    @endforeach

    {{-- LEGEND --}}
    <div class="card">
        <div class="card-body py-2">
            <div class="d-flex justify-content-between align-items-center flex-wrap">
                <small>
                    <strong>Legenda Nilai:</strong>
                    <span class="badge badge-success">&ge;80 Sangat Baik</span>
                    <span class="badge badge-primary">60-79 Baik</span>
                    <span class="badge badge-warning">40-59 Cukup</span>
                    <span class="badge badge-danger">&lt;40 Kurang</span>
                    &nbsp;&middot;&nbsp;
                    <span class="text-muted">&middot;</span> = bukan mapel pilihan
                    &nbsp;&middot;&nbsp;
                    <span class="text-danger"><i class="fas fa-exclamation-circle fa-xs"></i></span> = tidak mengerjakan mapel wajib
                    &nbsp;&middot;&nbsp;
                    <span class="text-danger"><i class="fas fa-times fa-xs"></i></span> = tidak hadir
                </small>
                <small class="text-muted">
                    <i class="fas fa-info-circle"></i> Siswa yang tidak mengerjakan satupun kuis dipastikan <strong>Tidak Hadir</strong>
                </small>
            </div>
        </div>
    </div>

@stop

@section('js')
@include('admin.smartq._overlay')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Per-kelas search
    document.querySelectorAll('.search-kelas').forEach(function(input) {
        input.addEventListener('input', function() {
            var q = this.value.toLowerCase().trim();
            var target = this.dataset.target;
            document.querySelectorAll('.' + target).forEach(function(row) {
                var match = !q || (row.dataset.search || '').indexOf(q) !== -1;
                row.style.display = match ? '' : 'none';
            });
        });
    });

    // Export overlay
    ['btnExportExcel','btnExportPdf'].forEach(function(id) {
        var btn = document.getElementById(id);
        if (btn) btn.addEventListener('click', function() {
            showSmartqOverlay('Membuat file export...', 'Menyiapkan laporan nilai CBT seluruh siswa', id.includes('Excel') ? 'file-excel' : 'file-pdf');
            setTimeout(hideSmartqOverlay, 10000);
        });
    });
});
</script>
@stop
