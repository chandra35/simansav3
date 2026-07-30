@extends('adminlte::page')

@section('title', 'Integrasi RDM')

@section('content_header')
    <div class="rdm-page-heading">
        <div>
            <span class="rdm-eyebrow">Akademik · Leger Nilai</span>
            <h1><i class="fas fa-sync-alt"></i> Integrasi RDM</h1>
            <p>Preview nilai historis RDM untuk siswa yang masih aktif di SIMANSA.</p>
        </div>
        <a href="{{ route('admin.nilai.export-legger-form', ['tingkat' => 12]) }}" class="btn btn-outline-primary">
            <i class="fas fa-file-excel mr-1"></i> Buka Leger Kelas XII
        </a>
    </div>
@stop

@section('content')
    @foreach(['success', 'error'] as $type)
        @if(session($type))
            <div class="alert alert-{{ $type === 'success' ? 'success' : 'danger' }} alert-dismissible fade show">
                <i class="fas fa-{{ $type === 'success' ? 'check-circle' : 'exclamation-circle' }}"></i>
                {{ session($type) }}
                <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
            </div>
        @endif
    @endforeach

    @if($errors->any())
        <div class="alert alert-danger">
            <strong><i class="fas fa-exclamation-triangle mr-1"></i> Preview belum dapat dijalankan.</strong>
            <ul class="mb-0 mt-2">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
    @endif

    <div class="row rdm-layout">
        <div class="col-xl-4">
            <div class="card rdm-filter-card">
                <div class="card-header">
                    <div>
                        <span class="rdm-step">Buat preview baru</span>
                        <h3 class="card-title">Tentukan roster dan sumber nilai</h3>
                    </div>
                </div>
                <form method="POST" action="{{ route('admin.rdm-sync.preview') }}">
                    @csrf
                    <div class="card-body">
                        <div class="rdm-current-period">
                            <span><i class="fas fa-database"></i> Periode aktif RDM</span>
                            <strong>{{ $rdmPeriod['tahunajaran']->tahunajaran_nama ?? '-' }} · {{ $rdmPeriod['semester']->semester_nama ?? '-' }}</strong>
                        </div>

                        <div class="rdm-form-section">
                            <div class="rdm-form-section__title">
                                <span>1</span>
                                <div><strong>Target SIMANSA</strong><small>Siswa yang saat ini masih aktif</small></div>
                            </div>

                            <div class="form-group">
                                <label for="simansaYear">Tahun roster</label>
                                <select id="simansaYear" name="simansa_tahun_pelajaran_id" class="form-control" required>
                                    @foreach($simansaTahunList as $tahun)
                                        <option value="{{ $tahun->id }}" {{ old('simansa_tahun_pelajaran_id', $simansaTahunList->firstWhere('is_active', true)?->id) == $tahun->id ? 'selected' : '' }}>
                                            {{ $tahun->nama }} {{ $tahun->is_active ? '· Aktif' : '' }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="simansaLevel">Tingkat aktif</label>
                                <select id="simansaLevel" name="simansa_tingkat" class="form-control" required>
                                    @foreach([12, 11, 10] as $tingkat)
                                        <option value="{{ $tingkat }}" {{ (int) old('simansa_tingkat', 12) === $tingkat ? 'selected' : '' }}>Kelas {{ $tingkat }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="form-group mb-0">
                                <label for="simansaClass">Rombel <span class="text-muted font-weight-normal">(opsional)</span></label>
                                <select id="simansaClass" name="simansa_kelas_id" class="form-control">
                                    <option value="">Semua rombel pada tingkat ini</option>
                                    @foreach($simansaKelasList as $kelas)
                                        <option value="{{ $kelas->id }}"
                                                data-year="{{ $kelas->tahun_pelajaran_id }}"
                                                data-level="{{ $kelas->tingkat }}"
                                                {{ old('simansa_kelas_id') == $kelas->id ? 'selected' : '' }}>
                                            {{ $kelas->nama_kelas }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="rdm-form-section">
                            <div class="rdm-form-section__title">
                                <span>2</span>
                                <div><strong>Sumber historis RDM</strong><small>Periode saat siswa berada di tingkat lama</small></div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Tahun RDM</label>
                                        <select name="rdm_tahunajaran_id" class="form-control" required>
                                            @foreach($rdmReference['tahun'] as $item)
                                                <option value="{{ $item->tahunajaran_id }}" {{ old('rdm_tahunajaran_id', $rdmPeriod['tahunajaran']->tahunajaran_id ?? null) == $item->tahunajaran_id ? 'selected' : '' }}>
                                                    {{ $item->tahunajaran_nama }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Semester RDM</label>
                                        <select name="rdm_semester_id" class="form-control" required>
                                            @foreach($rdmReference['semester'] as $item)
                                                <option value="{{ $item->semester_id }}" {{ old('rdm_semester_id', $rdmPeriod['semester']->semester_id ?? null) == $item->semester_id ? 'selected' : '' }}>
                                                    {{ $item->semester_nama }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label>Tingkat saat di RDM</label>
                                <select name="rdm_tingkat_id" class="form-control" required>
                                    <option value="">Pilih tingkat sumber</option>
                                    @foreach($rdmReference['tingkat'] as $item)
                                        <option value="{{ $item->tingkat_id }}" {{ old('rdm_tingkat_id') == $item->tingkat_id ? 'selected' : '' }}>
                                            {{ [12 => 'X', 13 => 'XI', 14 => 'XII'][$item->tingkat_id] ?? $item->tingkat_nama }}
                                        </option>
                                    @endforeach
                                </select>
                                <small class="form-text text-muted">Contoh: siswa aktif kelas XII, sumber 2025/2026 tingkat XI menghasilkan Semester Leger 3 atau 4.</small>
                            </div>

                            <div class="form-group mb-0">
                                <label>Kelas RDM <span class="text-muted font-weight-normal">(opsional)</span></label>
                                <input type="text" name="rdm_kelas_nama" class="form-control" value="{{ old('rdm_kelas_nama') }}" placeholder="Kosongkan untuk semua kelas RDM">
                            </div>
                        </div>

                        <div class="rdm-safety-note">
                            <i class="fas fa-shield-alt"></i>
                            <div><strong>Preview tidak mengubah nilai.</strong><span>Nilai baru hanya masuk staging sampai tombol Apply ditekan.</span></div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary btn-block btn-lg">
                            <i class="fas fa-search mr-1"></i> Jalankan Preview Aman
                        </button>
                    </div>
                </form>
            </div>

            <div class="card rdm-history-card">
                <div class="card-header"><h3 class="card-title"><i class="fas fa-history mr-1"></i> Riwayat Preview</h3></div>
                <div class="list-group list-group-flush rdm-history-list">
                    @forelse($latestRuns as $run)
                        @php
                            $runSemester = data_get($run->meta, 'simansa_semester');
                            $runInsert = data_get($run->meta, 'insert', 0);
                        @endphp
                        <a href="{{ route('admin.rdm-sync.index', ['run' => $run->id]) }}"
                           class="list-group-item list-group-item-action {{ $selectedRun?->id === $run->id ? 'active' : '' }}">
                            <div>
                                <strong>{{ $runSemester ? "Semester Leger {$runSemester}" : "RDM {$run->rdm_tahunajaran_id}/{$run->rdm_semester_id}" }}</strong>
                                <small>{{ $run->created_at?->format('d/m/Y H:i') }} · {{ number_format($run->total_records) }} nilai</small>
                            </div>
                            <span class="badge badge-{{ $run->status === 'applied' ? 'success' : 'light' }}">
                                {{ $run->status === 'applied' ? 'Applied' : number_format($runInsert) . ' baru' }}
                            </span>
                        </a>
                    @empty
                        <div class="text-center text-muted p-4">Belum ada preview.</div>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="col-xl-8">
            @if(!$selectedRun)
                <div class="card rdm-empty-state">
                    <div class="card-body">
                        <i class="fas fa-clipboard-check"></i>
                        <h3>Belum ada hasil preview</h3>
                        <p>Pilih roster SIMANSA dan periode historis RDM di sebelah kiri.</p>
                    </div>
                </div>
            @else
                @php
                    $meta = $selectedRun->meta ?? [];
                    $insertCount = (int) data_get($meta, 'insert', $actionCounts->get('insert', 0));
                    $unchangedCount = (int) data_get($meta, 'unchanged', $actionCounts->get('unchanged', 0));
                    $conflictCount = (int) data_get($meta, 'conflict', $actionCounts->get('conflict', 0));
                    $missingCount = (int) data_get($meta, 'active_students_without_rdm', $selectedRun->mismatch_siswa_count);
                    $blockedCount = $selectedRun->mismatch_mapel_count + $selectedRun->mismatch_tahun_count;
                    $globalSemester = data_get($meta, 'simansa_semester') ?? match((int) $selectedRun->rdm_tingkat_id) {
                        12 => (int) $selectedRun->rdm_semester_id,
                        13 => (int) $selectedRun->rdm_semester_id + 2,
                        14 => (int) $selectedRun->rdm_semester_id + 4,
                        default => null,
                    };
                    $rdmLevel = [12 => 'X', 13 => 'XI', 14 => 'XII'][(int) $selectedRun->rdm_tingkat_id] ?? $selectedRun->rdm_tingkat_id;
                    $rdmSemesterName = (int) $selectedRun->rdm_semester_id === 1 ? 'Ganjil' : 'Genap';
                    $missingSamples = collect(data_get($meta, 'active_students_without_rdm_sample', []));
                @endphp

                <div class="card rdm-result-card">
                    <div class="card-header rdm-result-header">
                        <div>
                            <span class="rdm-step">Hasil preview</span>
                            <h3 class="card-title">Semester Leger {{ $globalSemester ?? '-' }}</h3>
                            <small>Run {{ Str::limit($selectedRun->id, 13, '') }} · {{ $selectedRun->created_at?->format('d M Y, H:i') }}</small>
                        </div>
                        @if($selectedRun->status === 'applied')
                            <span class="rdm-applied-label"><i class="fas fa-check-circle"></i> Sudah diterapkan</span>
                        @else
                            <form method="POST" action="{{ route('admin.rdm-sync.apply', $selectedRun) }}" id="applySyncForm">
                                @csrf
                                <button type="button" class="btn btn-success" id="btnApplySync" {{ $insertCount === 0 ? 'disabled' : '' }}
                                        data-count="{{ $insertCount }}" data-conflicts="{{ $conflictCount }}">
                                    <i class="fas fa-check mr-1"></i> Apply {{ number_format($insertCount) }} Nilai Baru
                                </button>
                            </form>
                        @endif
                    </div>

                    <div class="card-body">
                        <div class="rdm-decision-banner {{ $conflictCount > 0 || $blockedCount > 0 ? 'is-warning' : ($selectedRun->status === 'applied' ? 'is-applied' : 'is-ready') }}">
                            <i class="fas fa-{{ $conflictCount > 0 || $blockedCount > 0 ? 'exclamation-triangle' : 'shield-check' }}"></i>
                            <div>
                                @if($selectedRun->status === 'applied')
                                    <strong>Run ini sudah diterapkan.</strong>
                                    <span>{{ number_format($selectedRun->applied_count) }} nilai baru ditulis; data lama tidak diubah.</span>
                                @elseif($conflictCount > 0 || $blockedCount > 0)
                                    <strong>Preview dapat di-apply, tetapi ada data yang ditahan.</strong>
                                    <span>Hanya {{ number_format($insertCount) }} nilai baru yang ditulis. {{ number_format($conflictCount + $blockedCount) }} record bermasalah tetap dilewati.</span>
                                @else
                                    <strong>Siap di-apply dengan aman.</strong>
                                    <span>Apply hanya menambahkan {{ number_format($insertCount) }} nilai baru. Tidak ada nilai SIMANSA yang akan ditimpa.</span>
                                @endif
                            </div>
                        </div>

                        <div class="rdm-flow">
                            <div><span>Sumber RDM</span><strong>{{ data_get($meta, 'simansa_tahun', $selectedRun->rdm_tahunajaran_id) }} · Kelas {{ $rdmLevel }} · {{ $rdmSemesterName }}</strong></div>
                            <i class="fas fa-arrow-right"></i>
                            <div class="is-primary"><span>Tujuan nilai</span><strong>Semester Leger {{ $globalSemester ?? '-' }}</strong></div>
                            <i class="fas fa-arrow-right"></i>
                            <div><span>Roster aktif</span><strong>Kelas {{ data_get($meta, 'simansa_tingkat', '-') }} · {{ number_format(data_get($meta, 'active_students', 0)) }} siswa</strong></div>
                        </div>

                        <div class="rdm-metric-grid">
                            <div class="rdm-metric is-blue"><i class="fas fa-users"></i><div><strong>{{ number_format(data_get($meta, 'active_students', 0)) }}</strong><span>Siswa aktif target</span></div></div>
                            <div class="rdm-metric is-green"><i class="fas fa-user-check"></i><div><strong>{{ number_format(data_get($meta, 'rdm_students_matched', 0)) }}</strong><span>Siswa cocok di RDM</span></div></div>
                            <div class="rdm-metric {{ $missingCount ? 'is-red' : 'is-green' }}"><i class="fas fa-user-times"></i><div><strong>{{ number_format($missingCount) }}</strong><span>Siswa belum ditemukan</span></div></div>
                            <div class="rdm-metric is-purple"><i class="fas fa-list-ol"></i><div><strong>{{ number_format($selectedRun->total_records) }}</strong><span>Total record nilai</span></div></div>
                        </div>

                        <div class="rdm-impact">
                            <div class="rdm-impact__heading">
                                <div><strong>Dampak jika Apply ditekan</strong><span>Setiap kategori diperlakukan berbeda agar data lama aman.</span></div>
                            </div>
                            <div class="rdm-impact-grid">
                                <div class="is-insert"><strong>{{ number_format($insertCount) }}</strong><span><i class="fas fa-plus-circle"></i> Ditambahkan</span><small>Nilai baru, belum ada di SIMANSA</small></div>
                                <div class="is-same"><strong>{{ number_format($unchangedCount) }}</strong><span><i class="fas fa-equals"></i> Dilewati karena sama</span><small>Tidak ditulis ulang</small></div>
                                <div class="is-conflict"><strong>{{ number_format($conflictCount) }}</strong><span><i class="fas fa-shield-alt"></i> Konflik ditahan</span><small>Nilai SIMANSA tidak ditimpa</small></div>
                                <div class="is-blocked"><strong>{{ number_format($blockedCount) }}</strong><span><i class="fas fa-ban"></i> Mapping bermasalah</span><small>Mapel atau tahun belum cocok</small></div>
                            </div>
                        </div>

                        <ul class="nav nav-tabs rdm-tabs" role="tablist">
                            <li class="nav-item"><a class="nav-link active" data-toggle="tab" href="#nilaiPreview"><i class="fas fa-table mr-1"></i> Sampel Nilai <span class="badge badge-light">{{ $sampleRows->count() }}</span></a></li>
                            <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#masalahPreview"><i class="fas fa-exclamation-circle mr-1"></i> Perlu Dicek <span class="badge badge-{{ $missingCount + $mismatchRows->count() ? 'danger' : 'light' }}">{{ $missingCount + $mismatchRows->count() }}</span></a></li>
                            <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#auditPreview"><i class="fas fa-info-circle mr-1"></i> Detail Run</a></li>
                        </ul>

                        <div class="tab-content rdm-tab-content">
                            <div class="tab-pane fade show active" id="nilaiPreview">
                                <div class="rdm-table-caption">
                                    Menampilkan maksimal 80 record sebagai sampel. Angka Pengetahuan/Keterampilan ditampilkan terpisah untuk K13.
                                </div>
                                <div class="table-responsive">
                                    <table class="table table-hover rdm-preview-table">
                                        <thead><tr><th>Aksi</th><th>Siswa</th><th>Mata Pelajaran</th><th class="text-center">RDM</th><th class="text-center">SIMANSA</th><th>Tujuan</th></tr></thead>
                                        <tbody>
                                            @forelse($sampleRows as $row)
                                                @php
                                                    $actionStyle = ['insert' => 'success', 'unchanged' => 'secondary', 'conflict' => 'danger', 'skip' => 'warning'][$row->apply_action] ?? 'light';
                                                    $actionText = ['insert' => 'Tambah', 'unchanged' => 'Sama', 'conflict' => 'Tahan', 'skip' => 'Lewati'][$row->apply_action] ?? $row->apply_action;
                                                @endphp
                                                <tr>
                                                    <td><span class="badge badge-{{ $actionStyle }}">{{ $actionText }}</span></td>
                                                    <td><strong>{{ $row->siswa?->nama_lengkap ?? $row->rdm_nama }}</strong><small>NISN {{ $row->rdm_nisn ?: '-' }}</small></td>
                                                    <td><strong>{{ $row->mataPelajaran?->kode_mapel ?: $row->rdm_mapel_nama }}</strong><small>{{ $row->mataPelajaran?->nama_mapel }}</small></td>
                                                    <td class="text-center">
                                                        <strong>{{ $row->rdm_nilai !== null ? number_format((float) $row->rdm_nilai, 2) : '-' }}</strong>
                                                        @if($row->rdm_nilai_pengetahuan !== null || $row->rdm_nilai_keterampilan !== null)
                                                            <small>P {{ $row->rdm_nilai_pengetahuan ?? '-' }} · K {{ $row->rdm_nilai_keterampilan ?? '-' }}</small>
                                                        @endif
                                                    </td>
                                                    <td class="text-center"><strong>{{ $row->existing_nilai ?? '-' }}</strong><small>{{ $row->existing_nilai !== null ? 'Sudah ada' : 'Belum ada' }}</small></td>
                                                    <td><strong>Semester {{ $row->simansa_semester }}</strong><small>{{ $row->match_notes ?: 'Siap diproses' }}</small></td>
                                                </tr>
                                            @empty
                                                <tr><td colspan="6" class="text-center text-muted py-4">Tidak ada record nilai pada preview ini.</td></tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <div class="tab-pane fade" id="masalahPreview">
                                <div class="rdm-issue-section">
                                    <h5>Siswa aktif belum ditemukan di periode RDM <span>{{ $missingCount }}</span></h5>
                                    @if($missingSamples->isNotEmpty())
                                        <div class="row">
                                            @foreach($missingSamples as $student)
                                                <div class="col-md-6">
                                                    <div class="rdm-missing-student"><i class="fas fa-user"></i><div><strong>{{ data_get($student, 'nama') }}</strong><span>NISN {{ data_get($student, 'nisn', '-') }}</span></div></div>
                                                </div>
                                            @endforeach
                                        </div>
                                    @elseif($missingCount > 0)
                                        <div class="alert alert-light border mb-0">Run lama belum menyimpan rincian siswa. Jalankan preview ulang untuk melihat nama dan NISN yang belum ditemukan.</div>
                                    @else
                                        <div class="rdm-all-clear"><i class="fas fa-check-circle"></i> Semua siswa aktif ditemukan pada periode RDM ini.</div>
                                    @endif
                                </div>

                                @if($mismatchRows->isNotEmpty())
                                    <div class="rdm-issue-section">
                                        <h5>Record nilai yang ditahan <span>{{ $mismatchRows->count() }}</span></h5>
                                        <div class="table-responsive">
                                            <table class="table table-sm table-bordered">
                                                <thead><tr><th>Status</th><th>Siswa</th><th>Mapel</th><th>RDM</th><th>SIMANSA</th><th>Alasan</th></tr></thead>
                                                <tbody>
                                                @foreach($mismatchRows as $row)
                                                    <tr>
                                                        <td><span class="badge badge-warning">{{ str_replace('_', ' ', $row->match_status) }}</span></td>
                                                        <td>{{ $row->rdm_nama }}<small class="d-block text-muted">{{ $row->rdm_nisn }}</small></td>
                                                        <td>{{ $row->rdm_mapel_nama }}</td>
                                                        <td>{{ $row->rdm_nilai }}</td>
                                                        <td>{{ $row->existing_nilai ?? '-' }}</td>
                                                        <td>{{ $row->match_notes }}</td>
                                                    </tr>
                                                @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                @endif
                            </div>

                            <div class="tab-pane fade" id="auditPreview">
                                <div class="row">
                                    <div class="col-md-6">
                                        <dl class="rdm-audit-list">
                                            <div><dt>Status</dt><dd>{{ strtoupper($selectedRun->status) }}</dd></div>
                                            <div><dt>Tahun RDM</dt><dd>{{ $selectedRun->rdm_tahunajaran_id }}</dd></div>
                                            <div><dt>Semester RDM</dt><dd>{{ $rdmSemesterName }} ({{ $selectedRun->rdm_semester_id }})</dd></div>
                                            <div><dt>Tingkat RDM</dt><dd>Kelas {{ $rdmLevel }}</dd></div>
                                            <div><dt>Kelas RDM</dt><dd>{{ $selectedRun->rdm_kelas_nama ?: 'Semua kelas' }}</dd></div>
                                        </dl>
                                    </div>
                                    <div class="col-md-6">
                                        <dl class="rdm-audit-list">
                                            <div><dt>Diproses oleh</dt><dd>{{ $selectedRun->initiatedBy?->name ?? 'Sistem' }}</dd></div>
                                            <div><dt>Mulai</dt><dd>{{ $selectedRun->started_at?->format('d/m/Y H:i:s') }}</dd></div>
                                            <div><dt>Selesai</dt><dd>{{ $selectedRun->finished_at?->format('d/m/Y H:i:s') }}</dd></div>
                                            <div><dt>Sudah ditulis</dt><dd>{{ number_format($selectedRun->applied_count) }} nilai</dd></div>
                                            <div><dt>Catatan</dt><dd>{{ $selectedRun->notes ?: '-' }}</dd></div>
                                        </dl>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
@stop

@section('css')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<style>
    .rdm-page-heading{display:flex;align-items:flex-end;justify-content:space-between;gap:1rem;margin-bottom:.35rem}
    .rdm-page-heading h1{font-size:1.65rem;font-weight:750;margin:.12rem 0;color:#17233d}.rdm-page-heading p{margin:0;color:#66748f}
    .rdm-eyebrow,.rdm-step{font-size:.7rem;font-weight:800;letter-spacing:.09em;text-transform:uppercase;color:#5b4cf0}
    .rdm-layout>.col-xl-4,.rdm-layout>.col-xl-8{display:flex;flex-direction:column}
    .rdm-filter-card,.rdm-history-card,.rdm-result-card,.rdm-empty-state{border:0;border-radius:20px;box-shadow:0 14px 35px rgba(28,45,85,.08);overflow:hidden}
    .rdm-filter-card{border-top:4px solid #5b4cf0}.rdm-filter-card .card-header,.rdm-result-header{background:#fff;border-bottom:1px solid #edf0f6;padding:1rem 1.25rem}
    .rdm-filter-card .card-title,.rdm-result-header .card-title{display:block;float:none;font-size:1.05rem;font-weight:750;margin:.1rem 0 0}.rdm-filter-card .card-body{padding:1.2rem}
    .rdm-current-period{display:flex;justify-content:space-between;align-items:center;background:#f3f5ff;color:#33416a;border-radius:12px;padding:.75rem .9rem;margin-bottom:1rem}.rdm-current-period span{font-size:.8rem}.rdm-current-period strong{font-size:.84rem}
    .rdm-form-section{border:1px solid #e6eaf2;border-radius:15px;padding:1rem;margin-bottom:1rem}.rdm-form-section__title{display:flex;align-items:center;gap:.7rem;margin-bottom:.9rem}.rdm-form-section__title>span{display:grid;place-items:center;width:28px;height:28px;background:#5b4cf0;color:#fff;border-radius:9px;font-weight:800}.rdm-form-section__title div{display:flex;flex-direction:column}.rdm-form-section__title small{color:#8691a7}
    .rdm-form-section label{font-size:.79rem;color:#39445c;margin-bottom:.32rem}.rdm-form-section .form-control{border-color:#dfe4ee;border-radius:10px;height:40px;font-size:.88rem}
    .rdm-safety-note{display:flex;gap:.7rem;padding:.8rem;background:#eef9f4;border-radius:12px;color:#177651}.rdm-safety-note i{font-size:1.25rem}.rdm-safety-note div{display:flex;flex-direction:column;font-size:.78rem}.rdm-filter-card .card-footer{background:#fff;border:0;padding:0 1.2rem 1.2rem}.rdm-filter-card .btn-lg{border-radius:12px;font-size:.92rem}
    .rdm-history-card .card-header{background:#fff}.rdm-history-list{max-height:330px;overflow:auto}.rdm-history-list .list-group-item{display:flex;align-items:center;justify-content:space-between;gap:.5rem;border-color:#f0f2f7;padding:.8rem 1rem}.rdm-history-list .list-group-item div{display:flex;flex-direction:column}.rdm-history-list small{font-size:.72rem;color:#8490a7}.rdm-history-list .active{background:#5b4cf0;border-color:#5b4cf0}.rdm-history-list .active small{color:#e1ddff}
    .rdm-result-header{display:flex;align-items:center;justify-content:space-between}.rdm-result-header>div{display:flex;flex-direction:column}.rdm-result-header small{color:#7a879d}.rdm-result-header .btn{border-radius:11px;font-weight:700}.rdm-applied-label{color:#16845b;font-weight:750}
    .rdm-decision-banner{display:flex;gap:.85rem;padding:1rem 1.1rem;border-radius:14px;margin-bottom:1rem}.rdm-decision-banner>i{font-size:1.35rem;margin-top:.1rem}.rdm-decision-banner div{display:flex;flex-direction:column}.rdm-decision-banner span{font-size:.82rem}.rdm-decision-banner.is-ready,.rdm-decision-banner.is-applied{background:#eaf8f2;color:#147451}.rdm-decision-banner.is-warning{background:#fff7df;color:#8d6410}
    .rdm-flow{display:grid;grid-template-columns:1fr auto 1fr auto 1fr;align-items:center;gap:.7rem;background:#f7f8fc;border:1px solid #e9edf5;border-radius:14px;padding:.9rem 1rem;margin-bottom:1rem}.rdm-flow div{display:flex;flex-direction:column;text-align:center}.rdm-flow span{font-size:.68rem;text-transform:uppercase;letter-spacing:.06em;color:#8994a8}.rdm-flow strong{font-size:.83rem;color:#2d3850}.rdm-flow>i{color:#a8b0c1}.rdm-flow .is-primary strong{color:#5141df}
    .rdm-metric-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:.75rem;margin-bottom:1rem}.rdm-metric{display:flex;align-items:center;gap:.7rem;border:1px solid #e9edf4;border-radius:14px;padding:.85rem;background:#fff}.rdm-metric>i{display:grid;place-items:center;width:38px;height:38px;border-radius:11px}.rdm-metric div{display:flex;flex-direction:column}.rdm-metric strong{font-size:1.2rem;line-height:1.1}.rdm-metric span{font-size:.7rem;color:#7d889c}.rdm-metric.is-blue i{background:#eaf1ff;color:#3678ea}.rdm-metric.is-green i{background:#e7f8f0;color:#19a36d}.rdm-metric.is-red i{background:#ffeded;color:#e15353}.rdm-metric.is-purple i{background:#f0edff;color:#6950e8}
    .rdm-impact{border:1px solid #e7ebf3;border-radius:15px;overflow:hidden;margin-bottom:1.2rem}.rdm-impact__heading{padding:.8rem 1rem;background:#fafbfe}.rdm-impact__heading div{display:flex;flex-direction:column}.rdm-impact__heading span{font-size:.75rem;color:#7e899d}.rdm-impact-grid{display:grid;grid-template-columns:repeat(4,1fr)}.rdm-impact-grid>div{display:flex;flex-direction:column;padding:1rem;border-right:1px solid #edf0f5}.rdm-impact-grid>div:last-child{border:0}.rdm-impact-grid strong{font-size:1.3rem}.rdm-impact-grid span{font-size:.76rem;font-weight:700}.rdm-impact-grid small{font-size:.68rem;color:#8993a6}.rdm-impact-grid .is-insert strong,.rdm-impact-grid .is-insert span{color:#159266}.rdm-impact-grid .is-conflict strong,.rdm-impact-grid .is-conflict span{color:#d44d4d}.rdm-impact-grid .is-blocked strong,.rdm-impact-grid .is-blocked span{color:#bf850d}
    .rdm-tabs{border-bottom-color:#e5e9f1}.rdm-tabs .nav-link{border:0;color:#6d7890;font-size:.82rem;font-weight:700;padding:.75rem 1rem}.rdm-tabs .nav-link.active{color:#5544e7;border-bottom:3px solid #5b4cf0}.rdm-tab-content{padding-top:.9rem}.rdm-table-caption{font-size:.75rem;color:#7d889c;margin-bottom:.7rem}
    .rdm-preview-table{font-size:.78rem}.rdm-preview-table th{border-top:0;background:#f7f8fb;color:#68758d;font-size:.69rem;text-transform:uppercase;letter-spacing:.04em}.rdm-preview-table td{vertical-align:middle}.rdm-preview-table td>strong,.rdm-preview-table td>small{display:block}.rdm-preview-table td>small{color:#8994a8}.rdm-preview-table .badge{min-width:58px;padding:.42rem}
    .rdm-issue-section{border:1px solid #e7ebf3;border-radius:14px;padding:1rem;margin-bottom:1rem}.rdm-issue-section h5{display:flex;justify-content:space-between;font-size:.9rem;font-weight:750}.rdm-issue-section h5 span{background:#ffeded;color:#d84d4d;border-radius:20px;padding:.18rem .55rem}.rdm-missing-student{display:flex;align-items:center;gap:.6rem;padding:.65rem;background:#fafbfe;border-radius:10px;margin-bottom:.5rem}.rdm-missing-student>i{color:#e15a5a}.rdm-missing-student div{display:flex;flex-direction:column}.rdm-missing-student strong{font-size:.78rem}.rdm-missing-student span{font-size:.69rem;color:#8490a5}.rdm-all-clear{color:#19875f}
    .rdm-audit-list{margin:0}.rdm-audit-list div{display:flex;justify-content:space-between;gap:1rem;padding:.65rem 0;border-bottom:1px solid #edf0f5}.rdm-audit-list dt{font-size:.76rem;color:#7a869b}.rdm-audit-list dd{font-size:.78rem;text-align:right;margin:0;color:#27334b}.rdm-empty-state .card-body{text-align:center;padding:5rem 1rem}.rdm-empty-state i{font-size:3rem;color:#b5bdd0}.rdm-empty-state h3{font-size:1.15rem;margin:1rem 0 .35rem}.rdm-empty-state p{color:#7d889d}
    @media(max-width:1199px){.rdm-metric-grid,.rdm-impact-grid{grid-template-columns:repeat(2,1fr)}.rdm-impact-grid>div:nth-child(2){border-right:0}.rdm-history-card{margin-bottom:1rem}}
    @media(max-width:767px){.rdm-page-heading,.rdm-result-header{align-items:flex-start;flex-direction:column}.rdm-page-heading .btn,.rdm-result-header form,.rdm-result-header .btn{width:100%}.rdm-flow{grid-template-columns:1fr}.rdm-flow>i{transform:rotate(90deg);justify-self:center}.rdm-metric-grid,.rdm-impact-grid{grid-template-columns:1fr}.rdm-impact-grid>div{border-right:0;border-bottom:1px solid #edf0f5}.rdm-result-card .card-body{padding:.85rem}.rdm-tabs{flex-wrap:nowrap;overflow-x:auto}.rdm-tabs .nav-link{white-space:nowrap}}
</style>
@stop

@section('js')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
$(function() {
    function filterClasses() {
        var year = $('#simansaYear').val();
        var level = $('#simansaLevel').val();
        var $class = $('#simansaClass');
        var current = $class.val();
        var currentVisible = false;

        $class.find('option[data-year]').each(function() {
            var visible = String($(this).data('year')) === String(year) && String($(this).data('level')) === String(level);
            $(this).prop('hidden', !visible).prop('disabled', !visible);
            if (visible && $(this).val() === current) currentVisible = true;
        });
        if (!currentVisible) $class.val('');
    }

    $('#simansaYear, #simansaLevel').on('change', filterClasses);
    filterClasses();

    $('#btnApplySync').on('click', function() {
        var $btn = $(this);
        if ($btn.prop('disabled')) return;
        var count = Number($btn.data('count') || 0).toLocaleString('id-ID');
        var conflicts = Number($btn.data('conflicts') || 0).toLocaleString('id-ID');

        Swal.fire({
            title: 'Terapkan ' + count + ' nilai baru?',
            html: '<div class="text-left">' +
                  '<p>SIMANSA hanya akan <strong>menambahkan nilai yang belum ada</strong>.</p>' +
                  '<ul class="pl-3 mb-2"><li>Nilai yang sudah sama dilewati.</li><li>' + conflicts + ' konflik ditahan.</li><li>Nilai lama tidak akan ditimpa.</li></ul>' +
                  '<p class="small text-muted mb-0">Periksa tab Sampel Nilai dan Perlu Dicek sebelum melanjutkan.</p></div>',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#20a66a',
            cancelButtonColor: '#6c757d',
            confirmButtonText: '<i class="fas fa-check"></i> Ya, Apply Nilai Baru',
            cancelButtonText: 'Periksa Lagi',
            reverseButtons: true,
        }).then(function(result) {
            if (result.isConfirmed) {
                $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Memproses...');
                $('#applySyncForm').submit();
            }
        });
    });
});
</script>
@stop
