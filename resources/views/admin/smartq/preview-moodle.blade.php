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
    {{-- Context Info --}}
    <div class="alert alert-info">
        <i class="fas fa-info-circle"></i>
        <strong>Periode:</strong> {{ $smartq->nama }} |
        <strong>Moodle:</strong> {{ $smartq->moodle_base_url }}
        @if(!empty($smartq->moodle_quizzes))
            | <strong>{{ count($smartq->moodle_course_ids) }} course</strong>, <strong>{{ count($smartq->moodle_quizzes) }} kuis</strong> di-scan
        @elseif($smartq->moodle_course_id)
            | <strong>Course ID:</strong> {{ $smartq->moodle_course_id }}
            @if($smartq->moodle_quiz_id)
                | <strong>Quiz:</strong> {{ $smartq->moodle_quiz_name ?? 'ID ' . $smartq->moodle_quiz_id }}
            @endif
        @endif
    </div>

    {{-- Summary Stats --}}
    <div class="row">
        <div class="col-lg-3 col-6">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>{{ $summary['total_moodle'] }}</h3>
                    <p>Total User Moodle</p>
                </div>
                <div class="icon"><i class="fas fa-users"></i></div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>{{ $summary['matched'] }}</h3>
                    <p>Siap Import</p>
                </div>
                <div class="icon"><i class="fas fa-user-check"></i></div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3>{{ $summary['already_registered'] }}</h3>
                    <p>Sudah Terdaftar</p>
                </div>
                <div class="icon"><i class="fas fa-user-clock"></i></div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-secondary">
                <div class="inner">
                    <h3>{{ $summary['no_match'] }}</h3>
                    <p>Tidak Ada di SIMANSA</p>
                </div>
                <div class="icon"><i class="fas fa-user-times"></i></div>
                @if($summary['no_match'] > 0)
                    <a href="#cardUnmatched" class="small-box-footer">
                        Lihat Detail <i class="fas fa-arrow-circle-down"></i>
                    </a>
                @endif
            </div>
        </div>
    </div>

    {{-- Preview Table --}}
    @php
        // Collect all unique quizzes (mapel) from scores across all rows
        $allMapel = collect($rows)
            ->flatMap(fn($r) => $r['scores'] ?? [])
            ->unique('quiz_id')
            ->sortBy('quiz_name')
            ->values();
        $mapelCount = $allMapel->count();
        $totalCols = 6 + $mapelCount + ($mapelCount > 1 ? 1 : 0); // checkbox, #, moodle user, siswa, kelas, [mapel cols], [rata-rata if >1], status
    @endphp
    <form action="{{ route('admin.smartq.moodle.scan.confirm', $smartq) }}" method="POST" id="formConfirm">
        @csrf
        <input type="hidden" name="cache_key" value="{{ $cacheKey }}">

        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-table"></i> Hasil Scan — Pilih Peserta untuk Diimport</h3>
                <div class="card-tools">
                    <span class="badge badge-success" id="selectedCount">0</span> dipilih
                </div>
            </div>
            <div class="card-body p-0" style="overflow-x:auto">
                <table class="table table-bordered table-striped table-sm mb-0" style="white-space:nowrap">
                    <thead class="thead-dark">
                        <tr>
                            <th width="40" class="text-center">
                                <input type="checkbox" id="checkAll" title="Pilih Semua yang Siap">
                            </th>
                            <th width="30">#</th>
                            <th>User Moodle</th>
                            <th>Siswa SIMANSA</th>
                            <th width="100">Kelas</th>
                            @foreach($allMapel as $mapel)
                                <th class="text-center" style="min-width:70px;max-width:120px;white-space:normal;font-size:0.8rem">
                                    {{ $mapel['quiz_name'] }}
                                </th>
                            @endforeach
                            @if($mapelCount > 1)
                                <th width="70" class="text-center">Rata-rata</th>
                            @endif
                            <th width="120" class="text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $matchedRows = collect($rows)->where('status', '!=', 'no_match')->values(); @endphp
                        @forelse($matchedRows as $i => $row)
                            @php
                                $canImport = in_array($row['status'], ['ready', 'ready_no_score']);
                                $badgeClass = match($row['status']) {
                                    'ready' => 'badge-success',
                                    'ready_no_score' => 'badge-info',
                                    'already_registered' => 'badge-warning',
                                    'no_match' => 'badge-secondary',
                                    default => 'badge-light',
                                };
                                $statusLabel = match($row['status']) {
                                    'ready' => 'SIAP IMPORT',
                                    'ready_no_score' => 'SIAP (Tanpa Nilai)',
                                    'already_registered' => 'SUDAH TERDAFTAR',
                                    'no_match' => 'TIDAK COCOK',
                                    default => '-',
                                };
                                $rowScores = collect($row['scores'] ?? [])->keyBy('quiz_id');
                            @endphp
                            <tr class="{{ $canImport ? '' : 'text-muted' }}">
                                <td class="text-center">
                                    @if($canImport)
                                        <input type="checkbox" name="selected[]" value="{{ $row['moodle_username'] }}"
                                               class="row-check" {{ $row['status'] === 'ready' ? 'checked' : '' }}>
                                    @else
                                        <i class="fas fa-minus text-muted"></i>
                                    @endif
                                </td>
                                <td>{{ $i + 1 }}</td>
                                <td>
                                    <strong>{{ $row['moodle_fullname'] }}</strong><br>
                                    <small class="text-muted">
                                        <i class="fas fa-at"></i> {{ $row['moodle_username'] }}
                                        @if(!empty($row['moodle_firstname']) && $row['moodle_firstname'] !== $row['moodle_fullname'])
                                            · <i class="fas fa-user"></i> {{ $row['moodle_firstname'] }}
                                        @endif
                                        @if($row['moodle_email'])
                                            · {{ $row['moodle_email'] }}
                                        @endif
                                    </small>
                                </td>
                                <td>
                                    @if($row['siswa_id'])
                                        <strong>{{ $row['siswa_nama'] }}</strong><br>
                                        <small class="text-muted">NISN: {{ $row['siswa_nisn'] }}</small>
                                        @if(($row['match_method'] ?? '') === 'nama')
                                            <span class="badge badge-warning" title="Dicocokkan berdasarkan nama">via Nama</span>
                                        @elseif(($row['match_method'] ?? '') === 'nisn')
                                            <span class="badge badge-success" title="Dicocokkan berdasarkan NISN=Username">via NISN</span>
                                        @endif
                                    @else
                                        <span class="text-muted"><i class="fas fa-times-circle"></i> Tidak ditemukan</span>
                                    @endif
                                </td>
                                <td>{{ $row['siswa_kelas'] ?? '-' }}</td>
                                @foreach($allMapel as $mapel)
                                    <td class="text-center">
                                        @if($rowScores->has($mapel['quiz_id']))
                                            <span class="badge badge-primary">{{ $rowScores[$mapel['quiz_id']]['normalized_100'] }}</span>
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
                                <td class="text-center">
                                    <span class="badge {{ $badgeClass }}">{{ $statusLabel }}</span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ $totalCols }}" class="text-center py-3 text-muted">Tidak ada data dari Moodle.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="card-footer">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="importScores" name="import_scores" value="1" checked>
                            <label class="custom-control-label" for="importScores">
                                <strong>Otomatis isi nilai CBT</strong> dari Moodle (komponen sumber "moodle")
                            </label>
                        </div>
                    </div>
                    <div class="col-md-6 text-right">
                        <a href="{{ route('admin.smartq.peserta', $smartq) }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Kembali
                        </a>
                        <button type="submit" class="btn btn-success" id="btnSimpan"
                                {{ collect($rows)->whereIn('status', ['ready', 'ready_no_score'])->isEmpty() ? 'disabled' : '' }}>
                            <i class="fas fa-save"></i> Import <span id="importCount">{{ collect($rows)->where('status', 'ready')->count() }}</span> Peserta
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>

    {{-- Unmatched Moodle Users (Data Checking + Add to SIMANSA) --}}
    @php
        $unmatchedRows = collect($rows)->where('status', 'no_match')->values();
        // Group by moodle lastname (kelas) for mapping
        $unmatchedByKelas = $unmatchedRows->groupBy(fn($r) => $r['moodle_lastname'] ?? '-');
        // Parse tingkat from Moodle lastname: "Kelas X-1" → 10, "Kelas XI-A3" → 11, "Kelas XII IPA 1" → 12
        $parseTingkat = function($lastname) {
            if (preg_match('/\bXII\b/i', $lastname)) return 12;
            if (preg_match('/\bXI\b/i', $lastname)) return 11;
            if (preg_match('/\bX\b/i', $lastname)) return 10;
            return null;
        };
        $kelasGrouped = ($kelasAvailable ?? collect())->groupBy('tingkat');
    @endphp
    @if($unmatchedRows->isNotEmpty())
        <form action="{{ route('admin.smartq.moodle.scan.addToSimansa', $smartq) }}" method="POST" id="formAddSimansa">
            @csrf
            <input type="hidden" name="cache_key" value="{{ $cacheKey }}">

            <div class="card card-danger card-outline" id="cardUnmatched">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-exclamation-triangle text-danger"></i>
                        Siswa Moodle yang Tidak Ditemukan di SIMANSA
                        <span class="badge badge-danger ml-1">{{ $unmatchedRows->count() }}</span>
                    </h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-sm btn-outline-success" onclick="downloadUnmatchedCSV()">
                            <i class="fas fa-file-csv"></i> Download CSV
                        </button>
                        <button type="button" class="btn btn-tool" data-card-widget="collapse">
                            <i class="fas fa-minus"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="alert alert-warning m-3 mb-2">
                        <i class="fas fa-info-circle"></i>
                        <strong>Perhatian:</strong> {{ $unmatchedRows->count() }} siswa berikut terdaftar di Moodle (CBT) tetapi <strong>tidak ditemukan di SIMANSA</strong>.
                        Anda bisa menambahkan mereka ke SIMANSA secara otomatis. Petakan kelas Moodle ke kelas SIMANSA terlebih dahulu.
                    </div>

                    {{-- Kelas Mapping Section --}}
                    <div class="mx-3 mb-2">
                        <h6><i class="fas fa-exchange-alt"></i> Pemetaan Kelas Moodle → SIMANSA</h6>
                        <table class="table table-sm table-bordered mb-2" style="max-width:700px">
                            <thead class="thead-light">
                                <tr>
                                    <th>Kelas Moodle</th>
                                    <th width="50" class="text-center">Jml</th>
                                    <th>Kelas SIMANSA</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($unmatchedByKelas as $moodleKelas => $kelasRows)
                                    @php $tingkat = $parseTingkat($moodleKelas); @endphp
                                    <tr>
                                        <td>
                                            <strong>{{ $moodleKelas }}</strong>
                                            @if($tingkat)
                                                <span class="badge badge-info">Tingkat {{ $tingkat }}</span>
                                            @endif
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

                    {{-- Unmatched Users Table --}}
                    <div style="overflow-x:auto">
                        <table class="table table-bordered table-striped table-sm mb-0" id="tableUnmatched" style="white-space:nowrap">
                            <thead class="thead-light">
                                <tr>
                                    <th width="40" class="text-center">
                                        <input type="checkbox" id="checkAllUnmatched" title="Pilih Semua">
                                    </th>
                                    <th width="30">#</th>
                                    <th>NISN <small class="text-muted">(username)</small></th>
                                    <th>Nama Lengkap <small class="text-muted">(firstname)</small></th>
                                    <th>Kelas Moodle <small class="text-muted">(lastname)</small></th>
                                    <th>Email</th>
                                    @foreach($allMapel as $mapel)
                                        <th class="text-center" style="min-width:70px;max-width:120px;white-space:normal;font-size:0.8rem">
                                            {{ $mapel['quiz_name'] }}
                                        </th>
                                    @endforeach
                                    @if($mapelCount > 1)
                                        <th width="70" class="text-center">Rata-rata</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($unmatchedRows as $j => $urow)
                                    @php $uScores = collect($urow['scores'] ?? [])->keyBy('quiz_id'); @endphp
                                    <tr>
                                        <td class="text-center">
                                            <input type="checkbox" name="selected_unmatched[]" value="{{ $urow['moodle_username'] }}" class="unmatched-check">
                                        </td>
                                        <td>{{ $j + 1 }}</td>
                                        <td><strong>{{ $urow['moodle_username'] }}</strong></td>
                                        <td>{{ $urow['moodle_firstname'] ?: $urow['moodle_fullname'] }}</td>
                                        <td>{{ $urow['moodle_lastname'] ?? '-' }}</td>
                                        <td><small>{{ $urow['moodle_email'] }}</small></td>
                                        @foreach($allMapel as $mapel)
                                            <td class="text-center">
                                                @if($uScores->has($mapel['quiz_id']))
                                                    <span class="badge badge-primary">{{ $uScores[$mapel['quiz_id']]['normalized_100'] }}</span>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                        @endforeach
                                        @if($mapelCount > 1)
                                            <td class="text-center">
                                                @if($urow['has_attempt'])
                                                    <strong>{{ $urow['normalized_100'] }}</strong>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
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
                            <small class="text-muted">
                                <i class="fas fa-info-circle"></i> Siswa akan ditambahkan dengan: Username & Password = NISN, Role = Siswa
                            </small>
                        </div>
                        <div class="col-md-6 text-right">
                            <span class="badge badge-info mr-2" id="unmatchedSelectedCount">0</span> dipilih
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
    const checkAll = document.getElementById('checkAll');
    const rowChecks = document.querySelectorAll('.row-check');
    const selectedCount = document.getElementById('selectedCount');
    const importCount = document.getElementById('importCount');
    const btnSimpan = document.getElementById('btnSimpan');

    function updateCounts() {
        const count = document.querySelectorAll('.row-check:checked').length;
        selectedCount.textContent = count;
        importCount.textContent = count;
        btnSimpan.disabled = count === 0;
    }

    checkAll.addEventListener('change', function() {
        rowChecks.forEach(cb => cb.checked = this.checked);
        updateCounts();
    });

    rowChecks.forEach(cb => cb.addEventListener('change', function() {
        checkAll.checked = document.querySelectorAll('.row-check:checked').length === rowChecks.length;
        updateCounts();
    }));

    updateCounts();

    // CSV download for unmatched users
    window.downloadUnmatchedCSV = function() {
        var table = document.getElementById('tableUnmatched');
        if (!table) return;
        var rows = table.querySelectorAll('tr');
        var csv = [];
        rows.forEach(function(row) {
            var cols = row.querySelectorAll('th, td');
            var rowData = [];
            cols.forEach(function(col) {
                var text = col.innerText.replace(/[\r\n]+/g, ' ').trim();
                rowData.push('"' + text.replace(/"/g, '""') + '"');
            });
            csv.push(rowData.join(','));
        });
        var blob = new Blob([csv.join('\n')], {type: 'text/csv;charset=utf-8;'});
        var link = document.createElement('a');
        link.href = URL.createObjectURL(blob);
        link.download = 'siswa_moodle_tidak_ditemukan_{{ $smartq->id }}.csv';
        link.click();
    };

    // Submit overlay — import SMART-Q
    document.getElementById('formConfirm').addEventListener('submit', function(e) {
        var count = document.querySelectorAll('.row-check:checked').length;
        showSmartqOverlay('Mengimport ' + count + ' peserta dari Moodle...', 'Mohon tunggu, jangan tutup halaman ini', 'cloud-download-alt');
        smartqOverlayMessages([
            'Mengimport ' + count + ' peserta dari Moodle...',
            'Membuat data peserta SMART-Q...',
            'Mengisi nilai CBT otomatis...',
            'Menghitung ranking...',
            'Hampir selesai...',
        ], 2000);
    });

    // === Unmatched users: Add to SIMANSA ===
    var checkAllUnmatched = document.getElementById('checkAllUnmatched');
    var unmatchedChecks = document.querySelectorAll('.unmatched-check');
    var unmatchedCount = document.getElementById('unmatchedSelectedCount');
    var btnAddSimansa = document.getElementById('btnAddSimansa');

    if (checkAllUnmatched) {
        function updateUnmatchedCount() {
            var count = document.querySelectorAll('.unmatched-check:checked').length;
            unmatchedCount.textContent = count;
            btnAddSimansa.disabled = count === 0;
        }

        checkAllUnmatched.addEventListener('change', function() {
            unmatchedChecks.forEach(function(cb) { cb.checked = checkAllUnmatched.checked; });
            updateUnmatchedCount();
        });

        unmatchedChecks.forEach(function(cb) {
            cb.addEventListener('change', function() {
                checkAllUnmatched.checked = document.querySelectorAll('.unmatched-check:checked').length === unmatchedChecks.length;
                updateUnmatchedCount();
            });
        });

        updateUnmatchedCount();

        // Auto-select best matching kelas per mapping row
        document.querySelectorAll('.kelas-mapping-select').forEach(function(select) {
            // Find options with data-auto attribute (matching tingkat)
            var autoOptions = select.querySelectorAll('option[data-auto]');
            if (autoOptions.length === 1) {
                autoOptions[0].selected = true;
            }
        });

        // Confirmation + overlay
        document.getElementById('formAddSimansa').addEventListener('submit', function(e) {
            var count = document.querySelectorAll('.unmatched-check:checked').length;
            if (!confirm('Tambahkan ' + count + ' siswa ke SIMANSA?\n\nUsername & Password = NISN\nRole = Siswa\n\nLanjutkan?')) {
                e.preventDefault();
                return;
            }
            showSmartqOverlay('Menambahkan ' + count + ' siswa ke SIMANSA...', 'Membuat user, data siswa, dan assign kelas...', 'user-plus');
            smartqOverlayMessages([
                'Menambahkan ' + count + ' siswa ke SIMANSA...',
                'Membuat akun user...',
                'Membuat data siswa...',
                'Mengassign kelas...',
                'Hampir selesai...',
            ], 2000);
        });
    }
});
</script>
@stop
