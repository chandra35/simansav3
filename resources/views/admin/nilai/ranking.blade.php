@extends('adminlte::page')

@section('title', 'Perangkingan Nilai')

@section('content_header')
    <div class="d-flex flex-wrap justify-content-between align-items-center">
        <div>
            <h1 class="mb-1"><i class="fas fa-trophy"></i> Perangkingan Nilai</h1>
            <p class="text-muted mb-0">Ranking roster aktif berdasarkan rata-rata rapor yang tersimpan di SIMANSA.</p>
        </div>
        <a href="{{ route('admin.nilai.index', ['tingkat' => $tingkat]) }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left"></i> Kembali ke Nilai
        </a>
    </div>
@stop

@section('content')
    <section class="ranking-hero">
        <div>
            <span class="ranking-hero__eyebrow"><i class="fas fa-chart-line"></i> Analisis Akademik</span>
            <h2>{{ $mode === 'semester' ? "Ranking Semester {$semester}" : 'Ranking Akumulasi Semester 1–'.count($ranking['requested_periods']) }}</h2>
            <p>Nilai kumulatif dihitung dari rata-rata tiap semester, sehingga semua semester memiliki bobot yang sama.</p>
        </div>
        <div class="ranking-hero__period">
            <small>Roster aktif</small>
            <strong>{{ $tahunAktif->nama }} · Kelas {{ $tingkat }}</strong>
        </div>
    </section>

    <div class="card ranking-filter">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.nilai.ranking') }}" class="row align-items-end">
                <div class="form-group col-md-2">
                    <label>Tingkat Aktif</label>
                    <select name="tingkat" class="form-control" onchange="this.form.submit()">
                        @foreach([12, 11, 10] as $level)
                            <option value="{{ $level }}" @selected($tingkat === $level)>Kelas {{ $level }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group col-md-3">
                    <label>Jenis Ranking</label>
                    <select name="mode" id="rankingMode" class="form-control">
                        <option value="semester" @selected($mode === 'semester')>Satu semester</option>
                        <option value="cumulative" @selected($mode === 'cumulative')>Akumulasi seluruh semester</option>
                    </select>
                </div>
                <div class="form-group col-md-2" id="semesterFilter">
                    <label>Semester</label>
                    <select name="semester" class="form-control">
                        @foreach(array_keys($ranking['periods']) as $sem)
                            <option value="{{ $sem }}" @selected($semester === $sem)>Semester {{ $sem }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group col-md-3">
                    <label>Cakupan Rombel</label>
                    <select name="kelas_id" class="form-control">
                        <option value="">Semua rombel kelas {{ $tingkat }}</option>
                        @foreach($kelasList as $kelas)
                            <option value="{{ $kelas->id }}" @selected($kelasId === $kelas->id)>{{ $kelas->nama_kelas }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group col-md-2">
                    <button class="btn btn-primary btn-block"><i class="fas fa-filter"></i> Tampilkan</button>
                </div>
            </form>
        </div>
    </div>

    @if($ranking['missing_semesters'])
        <div class="alert alert-warning">
            <i class="fas fa-exclamation-triangle"></i>
            <strong>Data belum lengkap.</strong>
            Semester {{ implode(', ', $ranking['missing_semesters']) }} belum mempunyai nilai pada kohor ini dan belum dihitung.
        </div>
    @endif

    <div class="row">
        <div class="col-md-3">
            <div class="small-box bg-primary">
                <div class="inner"><h3>{{ $rows->count() }}</h3><p>Siswa ditampilkan</p></div>
                <div class="icon"><i class="fas fa-users"></i></div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="small-box bg-success">
                <div class="inner"><h3>{{ $rows->where('is_complete', true)->count() }}</h3><p>Layak diranking</p></div>
                <div class="icon"><i class="fas fa-check-circle"></i></div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="small-box bg-warning">
                <div class="inner"><h3>{{ $rows->where('is_complete', false)->count() }}</h3><p>Nilai belum lengkap</p></div>
                <div class="icon"><i class="fas fa-exclamation-triangle"></i></div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="small-box bg-info">
                <div class="inner"><h3>{{ count($ranking['available_semesters']) }}</h3><p>Semester dihitung</p></div>
                <div class="icon"><i class="fas fa-book-open"></i></div>
            </div>
        </div>
    </div>

    <div class="card ranking-table-card">
        <div class="card-header d-flex flex-wrap align-items-center justify-content-between">
            <div>
                <h3 class="card-title font-weight-bold"><i class="fas fa-trophy text-warning"></i> Hasil Perangkingan</h3>
                <small class="d-block text-muted mt-1">Nilai sama memperoleh peringkat sama dengan pola 1, 1, 3.</small>
            </div>
            <a href="{{ route('admin.nilai.ranking-export', request()->query()) }}" class="btn btn-success">
                <i class="fas fa-file-excel"></i> Export Excel
            </a>
        </div>
        <div class="card-body table-responsive">
            <table class="table table-hover table-bordered" id="rankingTable">
                <thead>
                    <tr>
                        <th>Rank</th>
                        <th>Rank Rombel</th>
                        <th>Rank Tingkat</th>
                        <th>Identitas Siswa</th>
                        <th>Rombel</th>
                        @foreach(array_keys($ranking['requested_periods']) as $sem)
                            <th>S{{ $sem }}</th>
                        @endforeach
                        <th>Nilai Ranking</th>
                        <th>Kelengkapan</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($rows as $row)
                        @php $displayRank = $kelasId ? $row['rank_class'] : $row['rank_grade']; @endphp
                        <tr class="{{ !$row['is_complete'] ? 'table-warning' : '' }}">
                            <td class="rank-main">
                                @if($displayRank)
                                    <span class="rank-badge rank-badge--{{ $displayRank <= 3 ? $displayRank : 'default' }}">{{ $displayRank }}</span>
                                @else
                                    <span class="text-muted">–</span>
                                @endif
                            </td>
                            <td>{{ $row['rank_class'] ?? '–' }}</td>
                            <td>{{ $row['rank_grade'] ?? '–' }}</td>
                            <td class="text-left">
                                <strong>{{ $row['nama'] }}</strong>
                                <small class="d-block text-muted">NISN {{ $row['nisn'] }} · NIS {{ $row['nis'] ?: '-' }}</small>
                            </td>
                            <td>{{ $row['kelas'] }}</td>
                            @foreach(array_keys($ranking['requested_periods']) as $sem)
                                <td>
                                    @if($row['semester_values'][$sem] !== null)
                                        <strong>{{ number_format($row['semester_values'][$sem], 2) }}</strong>
                                        <small class="d-block text-muted">{{ $row['semester_mapel_counts'][$sem] }} mapel</small>
                                    @else
                                        <span class="text-danger">–</span>
                                    @endif
                                </td>
                            @endforeach
                            <td><strong>{{ $row['score'] !== null ? number_format($row['score'], 4) : '–' }}</strong></td>
                            <td>
                                <span class="badge badge-{{ $row['is_complete'] ? 'success' : 'warning' }}">
                                    {{ $row['semester_complete'] }}/{{ $row['semester_expected'] }} semester
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="20" class="text-center text-muted py-5">Belum ada siswa atau nilai pada cakupan ini.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@stop

@section('css')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap4.min.css">
    <style>
        .ranking-hero{display:flex;justify-content:space-between;gap:1rem;align-items:center;margin-bottom:1.5rem;padding:1.5rem;border-radius:22px;background:linear-gradient(135deg,#253fd0,#188a91);color:#fff;box-shadow:0 16px 34px rgba(37,63,208,.18)}
        .ranking-hero h2{margin:.35rem 0;font-weight:700}.ranking-hero p{margin:0;color:rgba(255,255,255,.88)}
        .ranking-hero__eyebrow{text-transform:uppercase;letter-spacing:.08em;font-size:.75rem;color:rgba(255,255,255,.78)}
        .ranking-hero__period{min-width:220px;padding:1rem;border:1px solid rgba(255,255,255,.2);border-radius:16px;background:rgba(255,255,255,.1)}
        .ranking-hero__period small,.ranking-hero__period strong{display:block}
        .ranking-filter,.ranking-table-card{border:0;border-radius:18px;box-shadow:0 12px 30px rgba(15,23,42,.08)}
        #rankingTable th,#rankingTable td{text-align:center;vertical-align:middle;white-space:nowrap}
        .rank-main{min-width:64px}.rank-badge{display:inline-flex;width:34px;height:34px;align-items:center;justify-content:center;border-radius:50%;background:#e8edf5;color:#344054;font-weight:800}
        .rank-badge--1{background:#ffd761;color:#6d4a00}.rank-badge--2{background:#dce2e9;color:#495567}.rank-badge--3{background:#e7b98b;color:#6f3c15}
        @media(max-width:767px){.ranking-hero{align-items:stretch;flex-direction:column}.ranking-hero__period{min-width:0}}
    </style>
@stop

@section('js')
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap4.min.js"></script>
    <script>
        $(function () {
            const toggleSemester = () => $('#semesterFilter').toggle($('#rankingMode').val() === 'semester');
            $('#rankingMode').on('change', toggleSemester);
            toggleSemester();
            $('#rankingTable').DataTable({
                pageLength: 25,
                order: [],
                scrollX: true,
                language: { url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/id.json' }
            });
        });
    </script>
@stop
