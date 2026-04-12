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
                    <p>Tidak Cocok (NISN)</p>
                </div>
                <div class="icon"><i class="fas fa-user-times"></i></div>
            </div>
        </div>
    </div>

    {{-- Preview Table --}}
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
            <div class="card-body p-0">
                <table class="table table-bordered table-striped table-sm mb-0">
                    <thead class="thead-dark">
                        <tr>
                            <th width="40" class="text-center">
                                <input type="checkbox" id="checkAll" title="Pilih Semua yang Siap">
                            </th>
                            <th width="30">#</th>
                            <th>User Moodle</th>
                            <th>Siswa SIMANSA</th>
                            <th width="120">Kelas</th>
                            <th width="90" class="text-center">Nilai CBT</th>
                            <th width="130" class="text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($rows as $i => $row)
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
                                        @if($row['moodle_email'])
                                            · {{ $row['moodle_email'] }}
                                        @endif
                                    </small>
                                </td>
                                <td>
                                    @if($row['siswa_id'])
                                        <strong>{{ $row['siswa_nama'] }}</strong><br>
                                        <small class="text-muted">NISN: {{ $row['siswa_nisn'] }}</small>
                                    @else
                                        <span class="text-muted"><i class="fas fa-times-circle"></i> Tidak ditemukan</span>
                                    @endif
                                </td>
                                <td>{{ $row['siswa_kelas'] ?? '-' }}</td>
                                <td class="text-center">
                                    @if($row['has_attempt'])
                                        <span class="badge badge-primary" title="{{ count($row['scores'] ?? []) }} kuis dijawab">
                                            {{ $row['normalized_100'] }}
                                        </span>
                                        @if(count($row['scores'] ?? []) > 1)
                                            <br><small class="text-muted">{{ count($row['scores']) }} kuis</small>
                                        @endif
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <span class="badge {{ $badgeClass }}">{{ $statusLabel }}</span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-3 text-muted">Tidak ada data dari Moodle.</td>
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

    {{-- Loading Overlay --}}
    <div id="loadingOverlay" style="display:none; position:fixed; top:0; left:0; right:0; bottom:0; background:rgba(0,0,0,0.6); z-index:9999; justify-content:center; align-items:center;">
        <div class="text-center text-white">
            <div class="spinner-border spinner-border-lg mb-3" role="status"></div>
            <h4 id="loadingText">Mengimport peserta dari Moodle...</h4>
            <p class="text-white-50">Mohon tunggu, jangan tutup halaman ini.</p>
        </div>
    </div>
@stop

@section('js')
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

    // Submit overlay
    document.getElementById('formConfirm').addEventListener('submit', function(e) {
        const overlay = document.getElementById('loadingOverlay');
        overlay.style.display = 'flex';

        const messages = [
            'Mengimport peserta dari Moodle...',
            'Membuat data peserta SMART-Q...',
            'Mengisi nilai CBT otomatis...',
            'Menghitung ranking...',
            'Hampir selesai...',
        ];
        let idx = 0;
        setInterval(function() {
            idx = Math.min(idx + 1, messages.length - 1);
            document.getElementById('loadingText').textContent = messages[idx];
        }, 1500);
    });
});
</script>
@stop
