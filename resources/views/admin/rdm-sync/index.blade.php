@extends('adminlte::page')

@section('title', 'Integrasi RDM')

@section('content_header')
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1><i class="fas fa-sync-alt"></i> Integrasi RDM</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item active">Integrasi RDM</li>
            </ol>
        </div>
    </div>
@stop

@section('content')
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            <i class="fas fa-check-circle"></i> {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger">
            <i class="fas fa-exclamation-triangle"></i> Validasi gagal:
            <ul class="mb-0 mt-2">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="row">
        <div class="col-lg-4">
            <div class="card card-primary card-outline">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-filter"></i> Preview Sync RDM</h3>
                </div>
                <form method="POST" action="{{ route('admin.rdm-sync.preview') }}">
                    @csrf
                    <div class="card-body">
                        <div class="alert alert-info py-2">
                            <strong>RDM Aktif:</strong>
                            <div>TA: {{ $rdmPeriod['tahunajaran']->tahunajaran_nama ?? '-' }}</div>
                            <div>Semester: {{ $rdmPeriod['semester']->semester_nama ?? '-' }}</div>
                        </div>

                        <div class="form-group">
                            <label>Tahun Ajaran RDM</label>
                            <select name="rdm_tahunajaran_id" class="form-control" required>
                                @foreach($rdmReference['tahun'] as $item)
                                    <option value="{{ $item->tahunajaran_id }}" {{ old('rdm_tahunajaran_id', $rdmPeriod['tahunajaran']->tahunajaran_id ?? null) == $item->tahunajaran_id ? 'selected' : '' }}>
                                        {{ $item->tahunajaran_nama }} {{ $item->tahunajaran_status ? '(aktif)' : '' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Semester RDM</label>
                            <select name="rdm_semester_id" class="form-control" required>
                                @foreach($rdmReference['semester'] as $item)
                                    <option value="{{ $item->semester_id }}" {{ old('rdm_semester_id', $rdmPeriod['semester']->semester_id ?? null) == $item->semester_id ? 'selected' : '' }}>
                                        {{ $item->semester_nama }} {{ $item->semester_status ? '(aktif)' : '' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Tingkat (opsional)</label>
                            <select name="rdm_tingkat_id" class="form-control">
                                <option value="">Semua Tingkat</option>
                                @foreach($rdmReference['tingkat'] as $item)
                                    <option value="{{ $item->tingkat_id }}" {{ old('rdm_tingkat_id') == $item->tingkat_id ? 'selected' : '' }}>
                                        {{ $item->tingkat_nama }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group mb-0">
                            <label>Nama Kelas RDM (opsional)</label>
                            <input type="text" name="rdm_kelas_nama" class="form-control" value="{{ old('rdm_kelas_nama') }}" placeholder="Contoh: XII MIPA 1">
                            <small class="text-muted">Isi jika ingin membatasi satu kelas spesifik.</small>
                        </div>
                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary btn-block">
                            <i class="fas fa-search"></i> Jalankan Preview
                        </button>
                    </div>
                </form>
            </div>

            <div class="card card-secondary card-outline">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-history"></i> Riwayat Run</h3>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive" style="max-height: 420px;">
                        <table class="table table-sm table-striped mb-0">
                            <thead>
                                <tr>
                                    <th>Waktu</th>
                                    <th>Status</th>
                                    <th>Total</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($latestRuns as $run)
                                    <tr>
                                        <td>
                                            <small>{{ $run->created_at?->format('d/m H:i') }}</small>
                                        </td>
                                        <td>
                                            <span class="badge badge-{{ $run->status === 'applied' ? 'success' : ($run->status === 'failed' ? 'danger' : 'warning') }}">
                                                {{ strtoupper($run->status) }}
                                            </span>
                                        </td>
                                        <td>{{ $run->total_records }}</td>
                                        <td>
                                            <a href="{{ route('admin.rdm-sync.index', ['run' => $run->id]) }}" class="btn btn-xs btn-outline-primary">Lihat</a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="4" class="text-center text-muted py-3">Belum ada run.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="card card-info card-outline">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title mb-0"><i class="fas fa-clipboard-list"></i> Detail Run</h3>
                    @if($selectedRun)
                        <form method="POST" action="{{ route('admin.rdm-sync.apply', $selectedRun) }}" onsubmit="return confirm('Apply sync ini ke nilai_siswa? Pastikan mismatch sudah aman.');">
                            @csrf
                            <button type="submit" class="btn btn-success btn-sm" {{ $selectedRun->status === 'applied' ? 'disabled' : '' }}>
                                <i class="fas fa-check"></i> Apply ke Nilai Siswa
                            </button>
                        </form>
                    @endif
                </div>
                <div class="card-body">
                    @if(!$selectedRun)
                        <p class="text-muted mb-0">Belum ada run untuk ditampilkan.</p>
                    @else
                        <div class="row mb-3">
                            <div class="col-md-3"><div class="small-box bg-light"><div class="inner"><h4>{{ $selectedRun->total_records }}</h4><p>Total</p></div></div></div>
                            <div class="col-md-3"><div class="small-box bg-success"><div class="inner"><h4>{{ $selectedRun->matched_records }}</h4><p>Matched</p></div></div></div>
                            <div class="col-md-2"><div class="small-box bg-danger"><div class="inner"><h4>{{ $selectedRun->mismatch_siswa_count }}</h4><p>Miss Siswa</p></div></div></div>
                            <div class="col-md-2"><div class="small-box bg-warning"><div class="inner"><h4>{{ $selectedRun->mismatch_mapel_count }}</h4><p>Miss Mapel</p></div></div></div>
                            <div class="col-md-2"><div class="small-box bg-secondary"><div class="inner"><h4>{{ $selectedRun->mismatch_tahun_count }}</h4><p>Miss Tahun</p></div></div></div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <table class="table table-sm table-bordered mb-0">
                                    <tr><th width="40%">Tahun Ajaran RDM</th><td>{{ $selectedRun->rdm_tahunajaran_id }}</td></tr>
                                    <tr><th>Semester RDM</th><td>{{ $selectedRun->rdm_semester_id }}</td></tr>
                                    <tr><th>Tingkat RDM</th><td>{{ $selectedRun->rdm_tingkat_id ?? '-' }}</td></tr>
                                    <tr><th>Kelas RDM</th><td>{{ $selectedRun->rdm_kelas_nama ?? '-' }}</td></tr>
                                    <tr><th>Status</th><td><strong>{{ strtoupper($selectedRun->status) }}</strong></td></tr>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <table class="table table-sm table-bordered mb-0">
                                    <tr><th width="40%">Diproses Oleh</th><td>{{ $selectedRun->initiatedBy?->name ?? '-' }}</td></tr>
                                    <tr><th>Mulai</th><td>{{ $selectedRun->started_at?->format('d/m/Y H:i:s') ?? '-' }}</td></tr>
                                    <tr><th>Selesai</th><td>{{ $selectedRun->finished_at?->format('d/m/Y H:i:s') ?? '-' }}</td></tr>
                                    <tr><th>Applied Count</th><td>{{ $selectedRun->applied_count }}</td></tr>
                                    <tr><th>Catatan</th><td>{{ $selectedRun->notes ?? '-' }}</td></tr>
                                </table>
                            </div>
                        </div>

                        <h5 class="mb-2"><i class="fas fa-exclamation-triangle text-warning"></i> Contoh Mismatch (maks 150 baris)</h5>
                        <div class="table-responsive">
                            <table class="table table-sm table-striped table-bordered">
                                <thead>
                                    <tr>
                                        <th>Status</th>
                                        <th>NISN</th>
                                        <th>Nama</th>
                                        <th>Kelas</th>
                                        <th>Mapel</th>
                                        <th>Nilai</th>
                                        <th>Catatan</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($mismatchRows as $row)
                                        <tr>
                                            <td><span class="badge badge-warning">{{ $row->match_status }}</span></td>
                                            <td>{{ $row->rdm_nisn ?: '-' }}</td>
                                            <td>{{ $row->rdm_nama }}</td>
                                            <td>{{ $row->rdm_kelas_nama ?: '-' }}</td>
                                            <td>{{ $row->rdm_mapel_nama }}</td>
                                            <td>{{ $row->rdm_nilai }}</td>
                                            <td>{{ $row->match_notes ?: '-' }}</td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="7" class="text-center text-muted">Tidak ada mismatch untuk run ini.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@stop
