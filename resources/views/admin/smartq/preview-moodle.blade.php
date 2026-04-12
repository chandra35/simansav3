@extends('adminlte::page')

@section('title', 'Preview Scan Moodle - SMART-Q')

@section('content_header')
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1><i class="fas fa-cloud-download-alt"></i> Scan Peserta dari Moodle</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.smartq.index') }}">SMART-Q</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.smartq.show', $smartq) }}">{{ $smartq->nama }}</a></li>
                <li class="breadcrumb-item active">Scan Moodle</li>
            </ol>
        </div>
    </div>
@stop

@section('content')
@php
    // Collect all unique quizzes (mapel) from scores across all rows
    $allMapel = collect($rows)->flatMap(fn($r) => $r['scores'] ?? [])->unique('quiz_id')->sortBy('quiz_name')->values();
    $mapelCount = $allMapel->count();

    // Separate rows
    $matchedRows = collect($rows)->where('status', '!=', 'no_match')->values();
    $unmatchedRows = collect($rows)->where('status', 'no_match')->values();

    // Score analytics per mapel
    $totalStudents = count($rows);
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

    // Wajib vs Pilihan detection: if >50% students have score, it's wajib
    $mapelWajib = collect($mapelStats)->filter(fn($s) => $s['total_attempts'] > ($totalStudents * 0.5))->keys()->toArray();
    $mapelPilihan = collect($mapelStats)->reject(fn($s) => $s['total_attempts'] > ($totalStudents * 0.5))->keys()->toArray();

    // Unmatched grouping
    $unmatchedByKelas = $unmatchedRows->groupBy(fn($r) => $r['moodle_lastname'] ?? '-');
    $parseTingkat = function($lastname) {
        if (preg_match('/\bXII\b/i', $lastname)) return 12;
        if (preg_match('/\bXI\b/i', $lastname)) return 11;
        if (preg_match('/\bX\b/i', $lastname)) return 10;
        return null;
    };
    $kelasGrouped = ($kelasAvailable ?? collect())->groupBy('tingkat');
@endphp

    {{-- CONTEXT INFO + EXPORT BUTTONS --}}
    <div class="callout callout-info">
        <div class="d-flex justify-content-between align-items-center flex-wrap">
            <div>
                <h5 class="mb-1"><i class="fas fa-satellite-dish"></i> {{ $smartq->nama }}</h5>
                <span class="text-muted">
                    <i class="fas fa-server"></i> {{ $smartq->moodle_base_url }}
                    @if(!empty($smartq->moodle_quizzes))
                        &middot; <strong>{{ count($smartq->moodle_course_ids) }}</strong> course,
                        <strong>{{ count($smartq->moodle_quizzes) }}</strong> kuis
                    @endif
                </span>
            </div>
            <div class="mt-2 mt-md-0">
                <a href="{{ route('admin.smartq.moodle.scan.export', ['smartq' => $smartq, 'cache_key' => $cacheKey, 'format' => 'excel']) }}"
                   class="btn btn-sm btn-success mr-1" id="btnExportExcel">
                    <i class="fas fa-file-excel"></i> Export Excel
                </a>
                <a href="{{ route('admin.smartq.moodle.scan.export', ['smartq' => $smartq, 'cache_key' => $cacheKey, 'format' => 'pdf']) }}"
                   class="btn btn-sm btn-danger" id="btnExportPdf">
                    <i class="fas fa-file-pdf"></i> Export PDF
                </a>
            </div>
        </div>
    </div>

    {{-- SUMMARY STATS --}}
    <div class="row">
        <div class="col-lg-3 col-6">
            <div class="small-box bg-info">
                <div class="inner"><h3>{{ $summary['total_moodle'] }}</h3><p>Total Siswa Moodle</p></div>
                <div class="icon"><i class="fas fa-users"></i></div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-success">
                <div class="inner"><h3>{{ $summary['matched'] }}</h3><p>Siap Import</p></div>
                <div class="icon"><i class="fas fa-user-check"></i></div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-warning">
                <div class="inner"><h3>{{ $summary['already_registered'] }}</h3><p>Sudah Terdaftar</p></div>
                <div class="icon"><i class="fas fa-user-clock"></i></div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-secondary">
                <div class="inner"><h3>{{ $summary['no_match'] }}</h3><p>Tidak Ada di SIMANSA</p></div>
                <div class="icon"><i class="fas fa-user-times"></i></div>
                @if($summary['no_match'] > 0)
                    <a href="#cardUnmatched" class="small-box-footer">Lihat Detail <i class="fas fa-arrow-circle-down"></i></a>
                @endif
            </div>
        </div>
    </div>

    {{-- RINGKASAN ANALISIS NILAI --}}
    <div class="card card-outline card-primary">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-chart-bar"></i> Ringkasan Analisis Nilai per Mapel</h3>
            <div class="card-tools">
                <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i></button>
            </div>
        </div>
        <div class="card-body p-0">
            <table class="table table-sm table-bordered mb-0">
                <thead class="thead-light">
                    <tr>
                        <th>Mapel (Quiz)</th>
                        <th class="text-center" width="60">Tipe</th>
                        <th class="text-center" width="80">Peserta</th>
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
                            $pct = $totalStudents > 0 ? round($stat['total_attempts'] / $totalStudents * 100) : 0;
                            if ($stat['total_attempts'] === 0) {
                                $keterangan = '<span class="text-danger"><i class="fas fa-exclamation-circle"></i> Belum ada siswa mengerjakan</span>';
                            } elseif ($stat['avg'] >= 80) {
                                $keterangan = '<span class="text-success"><i class="fas fa-trophy"></i> Sangat baik &mdash; rata-rata di atas 80</span>';
                            } elseif ($stat['avg'] >= 60) {
                                $keterangan = '<span class="text-primary"><i class="fas fa-thumbs-up"></i> Cukup baik &mdash; rata-rata di atas 60</span>';
                            } elseif ($stat['avg'] >= 40) {
                                $keterangan = '<span class="text-warning"><i class="fas fa-exclamation-triangle"></i> Perlu perhatian &mdash; rata-rata di bawah 60</span>';
                            } else {
                                $keterangan = '<span class="text-danger"><i class="fas fa-times-circle"></i> Kritis &mdash; rata-rata sangat rendah</span>';
                            }
                            if (!$isWajib && $stat['total_attempts'] > 0) {
                                $notTaken = $totalStudents - $stat['total_attempts'];
                                $keterangan .= '<br><small class="text-muted"><i class="fas fa-info-circle"></i> ' . $notTaken . ' siswa tidak mengambil mapel pilihan ini</small>';
                            }
                        @endphp
                        <tr>
                            <td><strong>{{ $stat['name'] }}</strong></td>
                            <td class="text-center">
                                @if($isWajib)
                                    <span class="badge badge-dark">Wajib</span>
                                @else
                                    <span class="badge badge-outline-secondary border">Pilihan</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <strong>{{ $stat['total_attempts'] }}</strong>
                                <small class="text-muted">/{{ $totalStudents }}</small>
                            </td>
                            <td class="text-center">
                                <span class="font-weight-bold {{ $stat['avg'] >= 80 ? 'text-success' : ($stat['avg'] >= 60 ? 'text-primary' : ($stat['avg'] >= 40 ? 'text-warning' : 'text-danger')) }}">
                                    {{ $stat['avg'] }}
                                </span>
                            </td>
                            <td class="text-center text-success"><strong>{{ $stat['max'] }}</strong></td>
                            <td class="text-center text-danger"><strong>{{ $stat['min'] }}</strong></td>
                            <td style="white-space:normal;font-size:0.85rem">{!! $keterangan !!}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- REKAP NILAI SEMUA SISWA --}}
    <div class="card card-outline card-dark">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-th-list"></i> Rekap Nilai Seluruh Siswa <span class="badge badge-light ml-1">{{ count($rows) }}</span></h3>
            <div class="card-tools">
                <div class="input-group input-group-sm" style="width:250px">
                    <input type="text" class="form-control" placeholder="Cari nama/NISN..." id="searchAll" autocomplete="off">
                    <div class="input-group-append">
                        <span class="input-group-text"><i class="fas fa-search"></i></span>
                    </div>
                </div>
            </div>
        </div>
        <div class="card-body p-0" style="overflow-x:auto">
            <table class="table table-bordered table-hover table-sm mb-0" id="tableAllScores" style="white-space:nowrap">
                <thead class="bg-dark text-white" style="position:sticky;top:0;z-index:2">
                    <tr>
                        <th width="30" class="text-center">#</th>
                        <th style="min-width:160px">Nama Siswa</th>
                        <th width="110">NISN</th>
                        <th width="110">Kelas</th>
                        <th width="90" class="text-center">Match</th>
                        @foreach($allMapel as $mapel)
                            <th class="text-center {{ in_array($mapel['quiz_id'], $mapelWajib) ? '' : 'bg-secondary' }}"
                                style="min-width:75px;max-width:130px;white-space:normal;font-size:0.78rem">
                                {{ $mapel['quiz_name'] }}
                                @if(!in_array($mapel['quiz_id'], $mapelWajib))
                                    <br><small>(pilihan)</small>
                                @endif
                            </th>
                        @endforeach
                        @if($mapelCount > 1)
                            <th width="70" class="text-center bg-primary">Rata&sup2;</th>
                        @endif
                        <th width="90" class="text-center">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($rows as $i => $row)
                        @php
                            $rowScores = collect($row['scores'] ?? [])->keyBy('quiz_id');
                            $badgeClass = match($row['status']) {
                                'ready' => 'badge-success',
                                'ready_no_score' => 'badge-info',
                                'already_registered' => 'badge-warning',
                                'no_match' => 'badge-secondary',
                                default => 'badge-light',
                            };
                            $statusLabel = match($row['status']) {
                                'ready' => 'SIAP',
                                'ready_no_score' => 'TANPA NILAI',
                                'already_registered' => 'TERDAFTAR',
                                'no_match' => 'TIDAK ADA',
                                default => '-',
                            };
                            $matchBadge = match($row['match_method'] ?? null) {
                                'nisn' => '<span class="badge badge-success badge-sm">NISN</span>',
                                'nama' => '<span class="badge badge-warning badge-sm">Nama</span>',
                                default => '<span class="text-muted">-</span>',
                            };
                        @endphp
                        <tr class="search-row {{ $row['status'] === 'no_match' ? 'table-secondary' : '' }}"
                            data-search="{{ strtolower(($row['moodle_firstname'] ?: $row['moodle_fullname']) . ' ' . $row['moodle_username'] . ' ' . ($row['siswa_nama'] ?? '') . ' ' . ($row['siswa_nisn'] ?? '')) }}">
                            <td class="text-center text-muted">{{ $i + 1 }}</td>
                            <td>
                                <strong>{{ $row['moodle_firstname'] ?: $row['moodle_fullname'] }}</strong>
                                @if($row['siswa_nama'] && strtolower($row['siswa_nama']) !== strtolower($row['moodle_firstname'] ?: $row['moodle_fullname']))
                                    <br><small class="text-muted" title="Nama di SIMANSA">&crarr; {{ $row['siswa_nama'] }}</small>
                                @endif
                            </td>
                            <td><code>{{ $row['moodle_username'] }}</code></td>
                            <td>{{ $row['siswa_kelas'] ?? ($row['moodle_lastname'] ?? '-') }}</td>
                            <td class="text-center">{!! $matchBadge !!}</td>
                            @foreach($allMapel as $mapel)
                                @php $score = $rowScores->get($mapel['quiz_id']); @endphp
                                <td class="text-center">
                                    @if($score)
                                        @php $val = $score['normalized_100']; @endphp
                                        <span class="badge {{ $val >= 80 ? 'badge-success' : ($val >= 60 ? 'badge-primary' : ($val >= 40 ? 'badge-warning' : 'badge-danger')) }}">
                                            {{ $val }}
                                        </span>
                                    @elseif(in_array($mapel['quiz_id'], $mapelPilihan))
                                        <span class="text-muted" title="Bukan mapel pilihan siswa ini">&middot;</span>
                                    @else
                                        <span class="text-danger" title="Belum mengerjakan mapel wajib"><i class="fas fa-exclamation-circle fa-xs"></i></span>
                                    @endif
                                </td>
                            @endforeach
                            @if($mapelCount > 1)
                                <td class="text-center">
                                    @if($row['has_attempt'])
                                        <strong class="{{ $row['normalized_100'] >= 70 ? 'text-success' : ($row['normalized_100'] >= 50 ? 'text-primary' : 'text-danger') }}">
                                            {{ $row['normalized_100'] }}
                                        </strong>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                            @endif
                            <td class="text-center"><span class="badge {{ $badgeClass }}">{{ $statusLabel }}</span></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="card-footer py-2">
            <div class="d-flex justify-content-between align-items-center">
                <small class="text-muted">
                    <span class="badge badge-success">&ge;80</span>
                    <span class="badge badge-primary">60-79</span>
                    <span class="badge badge-warning">40-59</span>
                    <span class="badge badge-danger">&lt;40</span>
                    &nbsp;&middot;&nbsp; <span class="text-muted">&middot;</span> = bukan mapel pilihan
                    &nbsp;&middot;&nbsp; <span class="text-danger"><i class="fas fa-exclamation-circle fa-xs"></i></span> = belum mengerjakan
                </small>
                <small class="text-muted" id="searchResultCount"></small>
            </div>
        </div>
    </div>

    {{-- IMPORT TABLE --}}
    @if($matchedRows->isNotEmpty())
    <form action="{{ route('admin.smartq.moodle.scan.confirm', $smartq) }}" method="POST" id="formConfirm">
        @csrf
        <input type="hidden" name="cache_key" value="{{ $cacheKey }}">
        <div class="card card-outline card-success">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-file-import text-success"></i> Import Peserta ke SMART-Q</h3>
                <div class="card-tools">
                    <div class="d-inline-flex align-items-center">
                        <input type="text" class="form-control form-control-sm mr-2" placeholder="Cari..." id="searchImport" style="width:180px" autocomplete="off">
                        <span class="badge badge-success" id="selectedCount">0</span>&nbsp;dipilih
                    </div>
                </div>
            </div>
            <div class="card-body p-0" style="overflow-x:auto;max-height:500px">
                <table class="table table-bordered table-striped table-sm mb-0" id="tableImport" style="white-space:nowrap">
                    <thead class="thead-dark" style="position:sticky;top:0;z-index:2">
                        <tr>
                            <th width="35" class="text-center"><input type="checkbox" id="checkAll" title="Pilih Semua yang Siap"></th>
                            <th width="30">#</th>
                            <th>Siswa</th>
                            <th width="100">Kelas</th>
                            @foreach($allMapel as $mapel)
                                <th class="text-center" style="min-width:65px;max-width:110px;white-space:normal;font-size:0.78rem">{{ $mapel['quiz_name'] }}</th>
                            @endforeach
                            @if($mapelCount > 1)
                                <th width="65" class="text-center">Rata&sup2;</th>
                            @endif
                            <th width="105" class="text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($matchedRows as $i => $row)
                            @php
                                $canImport = in_array($row['status'], ['ready', 'ready_no_score']);
                                $badgeClass = match($row['status']) {
                                    'ready' => 'badge-success',
                                    'ready_no_score' => 'badge-info',
                                    'already_registered' => 'badge-warning',
                                    default => 'badge-light',
                                };
                                $statusLabel = match($row['status']) {
                                    'ready' => 'SIAP IMPORT',
                                    'ready_no_score' => 'TANPA NILAI',
                                    'already_registered' => 'TERDAFTAR',
                                    default => '-',
                                };
                                $rowScores = collect($row['scores'] ?? [])->keyBy('quiz_id');
                            @endphp
                            <tr class="import-row {{ $canImport ? '' : 'text-muted' }}"
                                data-search="{{ strtolower(($row['siswa_nama'] ?? '') . ' ' . ($row['siswa_nisn'] ?? '') . ' ' . $row['moodle_username']) }}">
                                <td class="text-center">
                                    @if($canImport)
                                        <input type="checkbox" name="selected[]" value="{{ $row['moodle_username'] }}"
                                               class="row-check" {{ $row['status'] === 'ready' ? 'checked' : '' }}>
                                    @else
                                        <i class="fas fa-minus text-muted"></i>
                                    @endif
                                </td>
                                <td class="text-muted">{{ $i + 1 }}</td>
                                <td>
                                    <strong>{{ $row['siswa_nama'] ?? $row['moodle_fullname'] }}</strong>
                                    <br><small class="text-muted">{{ $row['siswa_nisn'] ?? $row['moodle_username'] }}
                                        @if(($row['match_method'] ?? '') === 'nama')
                                            &middot; <span class="badge badge-warning badge-sm">via Nama</span>
                                        @elseif(($row['match_method'] ?? '') === 'nisn')
                                            &middot; <span class="badge badge-success badge-sm">via NISN</span>
                                        @endif
                                    </small>
                                </td>
                                <td>{{ $row['siswa_kelas'] ?? '-' }}</td>
                                @foreach($allMapel as $mapel)
                                    <td class="text-center">
                                        @if($rowScores->has($mapel['quiz_id']))
                                            @php $v = $rowScores[$mapel['quiz_id']]['normalized_100']; @endphp
                                            <span class="badge {{ $v >= 80 ? 'badge-success' : ($v >= 60 ? 'badge-primary' : ($v >= 40 ? 'badge-warning' : 'badge-danger')) }}">{{ $v }}</span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                @endforeach
                                @if($mapelCount > 1)
                                    <td class="text-center">
                                        @if($row['has_attempt'])
                                            <strong>{{ $row['normalized_100'] }}</strong>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                @endif
                                <td class="text-center"><span class="badge {{ $badgeClass }}">{{ $statusLabel }}</span></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="card-footer">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="importScores" name="import_scores" value="1" checked>
                            <label class="custom-control-label" for="importScores">
                                <strong>Otomatis isi nilai CBT</strong> dari Moodle
                            </label>
                        </div>
                    </div>
                    <div class="col-md-6 text-right">
                        <a href="{{ route('admin.smartq.peserta', $smartq) }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left"></i> Kembali
                        </a>
                        <button type="submit" class="btn btn-success" id="btnSimpan"
                                {{ $matchedRows->whereIn('status', ['ready', 'ready_no_score'])->isEmpty() ? 'disabled' : '' }}>
                            <i class="fas fa-file-import"></i> Import <span id="importCount">{{ $matchedRows->where('status', 'ready')->count() }}</span> Peserta
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
    @endif

    {{-- UNMATCHED USERS --}}
    @if($unmatchedRows->isNotEmpty())
        <form action="{{ route('admin.smartq.moodle.scan.addToSimansa', $smartq) }}" method="POST" id="formAddSimansa">
            @csrf
            <input type="hidden" name="cache_key" value="{{ $cacheKey }}">
            <div class="card card-danger card-outline" id="cardUnmatched">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-exclamation-triangle text-danger"></i>
                        Siswa Moodle Tidak Ada di SIMANSA
                        <span class="badge badge-danger ml-1">{{ $unmatchedRows->count() }}</span>
                    </h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-sm btn-outline-success mr-1" onclick="downloadUnmatchedCSV()">
                            <i class="fas fa-file-csv"></i> CSV
                        </button>
                        <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i></button>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="alert alert-warning m-3 mb-2" style="font-size:0.9rem">
                        <i class="fas fa-info-circle"></i>
                        <strong>{{ $unmatchedRows->count() }} siswa</strong> terdaftar di Moodle tetapi <strong>belum ada di SIMANSA</strong>.
                        Petakan kelas lalu klik "Tambahkan ke SIMANSA".
                    </div>
                    {{-- Kelas Mapping --}}
                    <div class="mx-3 mb-2">
                        <h6><i class="fas fa-exchange-alt"></i> Pemetaan Kelas</h6>
                        <table class="table table-sm table-bordered mb-2" style="max-width:700px">
                            <thead class="thead-light">
                                <tr><th>Kelas Moodle</th><th width="50" class="text-center">Jml</th><th>Kelas SIMANSA</th></tr>
                            </thead>
                            <tbody>
                                @foreach($unmatchedByKelas as $moodleKelas => $kelasRows)
                                    @php $tingkat = $parseTingkat($moodleKelas); @endphp
                                    <tr>
                                        <td>
                                            <strong>{{ $moodleKelas }}</strong>
                                            @if($tingkat) <span class="badge badge-info">Tkt {{ $tingkat }}</span> @endif
                                        </td>
                                        <td class="text-center">{{ $kelasRows->count() }}</td>
                                        <td>
                                            <select name="kelas_mapping[{{ $moodleKelas }}]" class="form-control form-control-sm kelas-mapping-select">
                                                <option value="">-- Tanpa Kelas --</option>
                                                @foreach($kelasGrouped as $tkt => $kelasList)
                                                    <optgroup label="Tingkat {{ $tkt }}">
                                                        @foreach($kelasList as $kls)
                                                            <option value="{{ $kls->id }}" {{ $tingkat == $tkt ? 'data-auto' : '' }}>
                                                                {{ $kls->nama_lengkap ?? $kls->nama_kelas }}
                                                            </option>
                                                        @endforeach
                                                    </optgroup>
                                                @endforeach
                                            </select>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    {{-- Unmatched Table --}}
                    <div style="overflow-x:auto">
                        <table class="table table-bordered table-striped table-sm mb-0" id="tableUnmatched" style="white-space:nowrap">
                            <thead class="thead-light">
                                <tr>
                                    <th width="35" class="text-center"><input type="checkbox" id="checkAllUnmatched"></th>
                                    <th width="30">#</th>
                                    <th>NISN</th>
                                    <th>Nama Lengkap</th>
                                    <th>Kelas Moodle</th>
                                    @foreach($allMapel as $mapel)
                                        <th class="text-center" style="min-width:65px;max-width:110px;white-space:normal;font-size:0.78rem">{{ $mapel['quiz_name'] }}</th>
                                    @endforeach
                                    @if($mapelCount > 1)
                                        <th width="65" class="text-center">Rata&sup2;</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($unmatchedRows as $j => $urow)
                                    @php $uScores = collect($urow['scores'] ?? [])->keyBy('quiz_id'); @endphp
                                    <tr>
                                        <td class="text-center"><input type="checkbox" name="selected_unmatched[]" value="{{ $urow['moodle_username'] }}" class="unmatched-check"></td>
                                        <td class="text-muted">{{ $j + 1 }}</td>
                                        <td><code>{{ $urow['moodle_username'] }}</code></td>
                                        <td><strong>{{ $urow['moodle_firstname'] ?: $urow['moodle_fullname'] }}</strong></td>
                                        <td>{{ $urow['moodle_lastname'] ?? '-' }}</td>
                                        @foreach($allMapel as $mapel)
                                            <td class="text-center">
                                                @if($uScores->has($mapel['quiz_id']))
                                                    @php $v = $uScores[$mapel['quiz_id']]['normalized_100']; @endphp
                                                    <span class="badge {{ $v >= 80 ? 'badge-success' : ($v >= 60 ? 'badge-primary' : ($v >= 40 ? 'badge-warning' : 'badge-danger')) }}">{{ $v }}</span>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                        @endforeach
                                        @if($mapelCount > 1)
                                            <td class="text-center">
                                                @if($urow['has_attempt']) <strong>{{ $urow['normalized_100'] }}</strong>
                                                @else <span class="text-muted">-</span> @endif
                                            </td>
                                        @endif
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer">
                    <div class="row align-items-center">
                        <div class="col-md-6">
                            <small class="text-muted"><i class="fas fa-key"></i> Username & Password = NISN, Role = Siswa</small>
                        </div>
                        <div class="col-md-6 text-right">
                            <span class="badge badge-info mr-1" id="unmatchedSelectedCount">0</span> dipilih
                            <button type="submit" class="btn btn-danger" id="btnAddSimansa" disabled>
                                <i class="fas fa-user-plus"></i> Tambahkan ke SIMANSA
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    @endif

@stop

@section('js')
@include('admin.smartq._overlay')
<script>
document.addEventListener('DOMContentLoaded', function() {

    // Generic search helper
    function bindSearch(inputId, rowClass) {
        var input = document.getElementById(inputId);
        if (!input) return;
        input.addEventListener('input', function() {
            var q = this.value.toLowerCase().trim();
            var rows = document.querySelectorAll('.' + rowClass);
            var visible = 0;
            rows.forEach(function(row) {
                var match = !q || (row.dataset.search || '').indexOf(q) !== -1;
                row.style.display = match ? '' : 'none';
                if (match) visible++;
            });
            var counter = document.getElementById('searchResultCount');
            if (counter && inputId === 'searchAll') {
                counter.textContent = q ? visible + ' dari ' + rows.length + ' ditampilkan' : '';
            }
        });
    }
    bindSearch('searchAll', 'search-row');
    bindSearch('searchImport', 'import-row');

    // Import checkboxes
    var checkAll = document.getElementById('checkAll');
    var rowChecks = document.querySelectorAll('.row-check');
    var selectedCount = document.getElementById('selectedCount');
    var importCount = document.getElementById('importCount');
    var btnSimpan = document.getElementById('btnSimpan');

    function updateCounts() {
        var c = document.querySelectorAll('.row-check:checked').length;
        if (selectedCount) selectedCount.textContent = c;
        if (importCount) importCount.textContent = c;
        if (btnSimpan) btnSimpan.disabled = c === 0;
    }
    if (checkAll) {
        checkAll.addEventListener('change', function() {
            rowChecks.forEach(function(cb) { cb.checked = checkAll.checked; });
            updateCounts();
        });
        rowChecks.forEach(function(cb) { cb.addEventListener('change', function() {
            checkAll.checked = document.querySelectorAll('.row-check:checked').length === rowChecks.length;
            updateCounts();
        }); });
        updateCounts();
    }

    // Import confirm (SweetAlert)
    var formConfirm = document.getElementById('formConfirm');
    if (formConfirm) {
        formConfirm.addEventListener('submit', function(e) {
            e.preventDefault();
            var form = this;
            var count = document.querySelectorAll('.row-check:checked').length;
            smartqConfirm(null, {
                title: 'Import Peserta?',
                text: '<p>Import <b>' + count + '</b> peserta ke SMART-Q dari data scan Moodle.</p>'
                    + (document.getElementById('importScores').checked
                        ? '<p><i class="fas fa-check-circle text-success"></i> Nilai CBT otomatis terisi.</p>'
                        : '<p><i class="fas fa-times-circle text-muted"></i> Nilai CBT <b>tidak</b> diisi.</p>'),
                icon: 'question',
                confirmText: '<i class="fas fa-file-import"></i> Ya, Import ' + count + ' Peserta',
                confirmColor: '#28a745',
            }).then(function(r) {
                if (r.isConfirmed) {
                    showSmartqOverlay('Mengimport ' + count + ' peserta...', 'Membuat data peserta dan mengisi nilai CBT', 'cloud-download-alt');
                    smartqOverlayMessages(['Mengimport peserta...','Mengisi nilai CBT...','Menghitung ranking...','Hampir selesai...'], 2000);
                    form.submit();
                }
            });
        });
    }

    // CSV download
    window.downloadUnmatchedCSV = function() {
        var table = document.getElementById('tableUnmatched');
        if (!table) return;
        var csv = [];
        table.querySelectorAll('tr').forEach(function(row) {
            var rd = [];
            row.querySelectorAll('th, td').forEach(function(col) {
                rd.push('"' + col.innerText.replace(/[\r\n]+/g,' ').trim().replace(/"/g,'""') + '"');
            });
            csv.push(rd.join(','));
        });
        var blob = new Blob([csv.join('\n')], {type:'text/csv;charset=utf-8;'});
        var a = document.createElement('a'); a.href = URL.createObjectURL(blob);
        a.download = 'siswa_moodle_tidak_ditemukan_{{ $smartq->id }}.csv'; a.click();
    };

    // Unmatched checkboxes
    var checkAllU = document.getElementById('checkAllUnmatched');
    var uChecks = document.querySelectorAll('.unmatched-check');
    var uCount = document.getElementById('unmatchedSelectedCount');
    var btnAdd = document.getElementById('btnAddSimansa');
    if (checkAllU) {
        function updateU() {
            var c = document.querySelectorAll('.unmatched-check:checked').length;
            uCount.textContent = c; btnAdd.disabled = c === 0;
        }
        checkAllU.addEventListener('change', function() {
            uChecks.forEach(function(cb){ cb.checked = checkAllU.checked; }); updateU();
        });
        uChecks.forEach(function(cb){ cb.addEventListener('change', function() {
            checkAllU.checked = document.querySelectorAll('.unmatched-check:checked').length === uChecks.length; updateU();
        }); });
        updateU();

        // Auto-select kelas mapping
        document.querySelectorAll('.kelas-mapping-select').forEach(function(sel) {
            var auto = sel.querySelectorAll('option[data-auto]');
            if (auto.length === 1) auto[0].selected = true;
        });

        // Add to SIMANSA confirm
        document.getElementById('formAddSimansa').addEventListener('submit', function(e) {
            e.preventDefault(); var form = this;
            var count = document.querySelectorAll('.unmatched-check:checked').length;
            smartqConfirm(null, {
                title: 'Tambahkan ke SIMANSA?',
                text: '<div class="text-left"><p><b>' + count + ' siswa</b> akan ditambahkan:</p>'
                    + '<ul><li>Username & Password = NISN</li><li>Role = Siswa</li><li>Kelas sesuai pemetaan</li></ul></div>',
                icon: 'question',
                confirmText: '<i class="fas fa-user-plus"></i> Tambahkan ' + count + ' Siswa',
                confirmColor: '#dc3545',
            }).then(function(r) {
                if (r.isConfirmed) {
                    showSmartqOverlay('Menambahkan ' + count + ' siswa...', 'Membuat user & assign kelas...', 'user-plus');
                    smartqOverlayMessages(['Membuat akun user...','Membuat data siswa...','Mengassign kelas...','Hampir selesai...'], 2000);
                    form.submit();
                }
            });
        });
    }

    // Export overlay
    ['btnExportExcel','btnExportPdf'].forEach(function(id) {
        var btn = document.getElementById(id);
        if (btn) btn.addEventListener('click', function() {
            showSmartqOverlay('Membuat file export...', 'Menyiapkan data nilai seluruh siswa', id.includes('Excel') ? 'file-excel' : 'file-pdf');
            setTimeout(hideSmartqOverlay, 10000);
        });
    });
});
</script>
@stop
