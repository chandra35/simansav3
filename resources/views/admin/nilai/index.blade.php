@extends('adminlte::page')

@section('title', 'Nilai Legger')

@section('content_header')
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1><i class="fas fa-chart-line"></i> Nilai Legger</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item active">Nilai Legger</li>
            </ol>
        </div>
    </div>
@stop

@section('content')
    {{-- Alert Messages --}}
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

    <section class="simansa-nilai-hero">
        <div class="simansa-nilai-hero__content">
            <div>
                <div class="simansa-nilai-hero__eyebrow">
                    <i class="fas fa-chart-line"></i> Legger dan Rekap Akademik
                </div>
                <h2>Nilai Legger</h2>
                <p>Kelola data nilai per tingkat, cek kelengkapan semester, lalu masuk ke upload atau detail rekap tanpa mengubah alur kerja yang sudah dipakai operator.</p>
            </div>
            <div class="simansa-nilai-hero__meta">
                <div class="simansa-nilai-chip">
                    <span class="simansa-nilai-chip__label">Tahun Aktif</span>
                    <strong>{{ $tahunAktif?->nama ?? 'Belum diatur' }}</strong>
                </div>
                <div class="simansa-nilai-chip">
                    <span class="simansa-nilai-chip__label">Semester Aktif</span>
                    <strong>{{ $tahunAktif?->semester_label ?? '-' }}</strong>
                </div>
            </div>
        </div>
    </section>

    <div class="simansa-nilai-panel">
        <div class="simansa-nilai-panel__header">
            <div>
                <h3><i class="fas fa-filter"></i> Pilih Tingkat Kelas</h3>
                <p>Pilih tingkat untuk melihat distribusi semester yang tersedia dan aksi pengelolaan nilai.</p>
            </div>
        </div>
        <div class="simansa-nilai-panel__body">
            <div class="row">
                <div class="col-md-4">
                    <a href="{{ route('admin.nilai.index', ['tingkat' => 12]) }}" 
                       class="btn btn-lg btn-block {{ request('tingkat') == 12 ? 'btn-primary' : 'btn-outline-primary' }}">
                        <i class="fas fa-graduation-cap"></i> Kelas 12
                        <br><small>(Legger SPAN-PTKIN/SNBP)</small>
                    </a>
                </div>
                <div class="col-md-4">
                    <a href="{{ route('admin.nilai.index', ['tingkat' => 11]) }}" 
                       class="btn btn-lg btn-block {{ request('tingkat') == 11 ? 'btn-success' : 'btn-outline-success' }}">
                        <i class="fas fa-user-graduate"></i> Kelas 11
                    </a>
                </div>
                <div class="col-md-4">
                    <a href="{{ route('admin.nilai.index', ['tingkat' => 10]) }}" 
                       class="btn btn-lg btn-block {{ request('tingkat') == 10 ? 'btn-info' : 'btn-outline-info' }}">
                        <i class="fas fa-user"></i> Kelas 10
                    </a>
                </div>
            </div>
        </div>
    </div>

    @if(request('tingkat'))
    {{-- Actions --}}
    <div class="simansa-nilai-panel">
        <div class="simansa-nilai-panel__header">
            <div>
                <h3><i class="fas fa-cog"></i> Aksi untuk Kelas {{ request('tingkat') }}</h3>
                <p>Masuk ke upload, template, dan export sesuai kebutuhan legger untuk tingkat yang sedang dipilih.</p>
            </div>
        </div>
        <div class="simansa-nilai-panel__body">
            <a href="{{ route('admin.nilai.upload-form') }}?tingkat={{ request('tingkat') }}" class="btn btn-success">
                <i class="fas fa-file-excel"></i> Upload Nilai Legger
            </a>
            <a href="{{ route('admin.nilai.template') }}" class="btn btn-info">
                <i class="fas fa-download"></i> Download Template Excel
            </a>
            @if(request('tingkat') == 12)
            <a href="{{ route('admin.nilai.export-legger-form') }}?tingkat=12" class="btn btn-warning">
                <i class="fas fa-file-export"></i> Export Custom
            </a>
            <a href="{{ route('admin.nilai.export-span') }}" class="btn btn-success">
                <i class="fas fa-graduation-cap"></i> Export SPAN-PTKIN
            </a>
            @endif
        </div>
    </div>

    {{-- Summary per Semester untuk tingkat terpilih --}}
    <div class="simansa-nilai-panel">
        <div class="simansa-nilai-panel__header">
            <div>
                <h3>
                <i class="fas fa-list"></i> 
                Data Nilai Kelas {{ request('tingkat') }} - Tahun Aktif: {{ $tahunAktif->nama ?? '-' }}
                </h3>
                <p>Ringkasan semester membantu operator tahu semester mana yang sudah terisi dan mana yang masih perlu dilengkapi.</p>
            </div>
        </div>
        <div class="simansa-nilai-panel__body">
            <div class="row">
                @foreach($semesterList as $sem => $data)
                <div class="col-md-4 col-lg">
                    <div class="info-box {{ $data['jumlah_siswa'] > 0 ? 'bg-success' : 'bg-secondary' }}">
                        <span class="info-box-icon"><i class="fas fa-book"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text" style="font-size: 11px;">{{ $data['label'] }}</span>
                            <span class="info-box-number">{{ $data['jumlah_siswa'] }} Siswa</span>
                            <span class="info-box-text" style="font-size: 10px;">TA: {{ $data['tahun_pelajaran'] ?? '-' }}</span>
                            <a href="{{ route('admin.nilai.semester', [
                                'semester' => $sem,
                                'tingkat' => request('tingkat'),
                                'tahun_pelajaran_id' => $data['tahun_pelajaran_id'],
                            ]) }}" class="text-white small">
                                <i class="fas fa-arrow-right"></i> Lihat Detail
                            </a>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Status Kelengkapan Legger --}}
    @if(request('tingkat') == 12)
    <div class="card card-warning card-outline">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-clipboard-check"></i> Status Kelengkapan Legger Kelas 12</h3>
        </div>
        <div class="card-body">
            @php
                $totalSemester = 5;
                $completedSemester = collect($semesterList)->filter(fn($d) => $d['jumlah_siswa'] > 0)->count();
            @endphp
            <div class="progress mb-3" style="height: 30px;">
                <div class="progress-bar bg-success" role="progressbar" 
                     style="width: {{ ($completedSemester / $totalSemester) * 100 }}%">
                    {{ $completedSemester }}/{{ $totalSemester }} Semester Terisi
                </div>
            </div>
            
            @if($completedSemester < $totalSemester)
            <div class="alert alert-warning">
                <i class="fas fa-exclamation-triangle"></i> 
                <strong>Belum lengkap!</strong> Ada {{ $totalSemester - $completedSemester }} semester yang belum terisi nilai.
                <br>
                Semester yang belum terisi:
                @foreach($semesterList as $sem => $data)
                    @if($data['jumlah_siswa'] == 0)
                        <span class="badge badge-danger">{{ $data['label'] }}</span>
                    @endif
                @endforeach
            </div>
            @else
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> 
                <strong>Lengkap!</strong> Semua 5 semester sudah terisi nilai. Siap untuk export legger SPAN-PTKIN/SNBP.
            </div>
            @endif
        </div>
    </div>
    @endif

    @else
    {{-- Default view: Pilih tingkat kelas --}}
    <div class="alert alert-info">
        <i class="fas fa-hand-point-up"></i> Silakan pilih tingkat kelas di atas untuk melihat data nilai.
    </div>

    {{-- Overview Semua Tingkat --}}
    <div class="simansa-nilai-panel">
        <div class="simansa-nilai-panel__header">
            <div>
                <h3><i class="fas fa-chart-bar"></i> Overview Data Nilai</h3>
                <p>Lihat distribusi data nilai per tingkat sebelum masuk ke semester dan upload nilai.</p>
            </div>
        </div>
        <div class="simansa-nilai-panel__body">
            <table class="table table-bordered">
                <thead class="bg-light">
                    <tr>
                        <th>Tingkat</th>
                        <th>Semester yang Tersedia</th>
                        <th>Total Data Nilai</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><strong>Kelas 12</strong></td>
                        <td>5 Semester (X-1, X-2, XI-1, XI-2, XII-1)</td>
                        <td>{{ $overviewStats['kelas_12'] ?? 0 }} siswa</td>
                        <td><a href="{{ route('admin.nilai.index', ['tingkat' => 12]) }}" class="btn btn-sm btn-primary">Lihat</a></td>
                    </tr>
                    <tr>
                        <td><strong>Kelas 11</strong></td>
                        <td>4 Semester (X-1, X-2, XI-1, XI-2)</td>
                        <td>{{ $overviewStats['kelas_11'] ?? 0 }} siswa</td>
                        <td><a href="{{ route('admin.nilai.index', ['tingkat' => 11]) }}" class="btn btn-sm btn-success">Lihat</a></td>
                    </tr>
                    <tr>
                        <td><strong>Kelas 10</strong></td>
                        <td>2 Semester (X-1, X-2)</td>
                        <td>{{ $overviewStats['kelas_10'] ?? 0 }} siswa</td>
                        <td><a href="{{ route('admin.nilai.index', ['tingkat' => 10]) }}" class="btn btn-sm btn-info">Lihat</a></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
    @endif

    {{-- Keterangan --}}
    <div class="card card-secondary card-outline collapsed-card">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-info-circle"></i> Keterangan Semester Legger</h3>
            <div class="card-tools">
                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                    <i class="fas fa-plus"></i>
                </button>
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <h5>Kelas 12 (5 Semester)</h5>
                    <table class="table table-sm table-bordered">
                        <tr><th>Sem</th><th>Tingkat</th><th>Tahun Pelajaran</th></tr>
                        <tr><td>1</td><td>Kelas X - Sem 1</td><td>2 tahun lalu</td></tr>
                        <tr><td>2</td><td>Kelas X - Sem 2</td><td>2 tahun lalu</td></tr>
                        <tr><td>3</td><td>Kelas XI - Sem 1</td><td>1 tahun lalu</td></tr>
                        <tr><td>4</td><td>Kelas XI - Sem 2</td><td>1 tahun lalu</td></tr>
                        <tr><td>5</td><td>Kelas XII - Sem 1</td><td>Tahun aktif</td></tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <h5>Kegunaan Legger</h5>
                    <ul>
                        <li><strong>SPAN-PTKIN:</strong> Seleksi Prestasi Akademik Nasional PTKIN</li>
                        <li><strong>SNBP:</strong> Seleksi Nasional Berdasarkan Prestasi</li>
                        <li><strong>UTBK:</strong> Ujian Tulis Berbasis Komputer</li>
                        <li><strong>Beasiswa:</strong> Persyaratan pendaftaran beasiswa</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
@stop

@section('css')
    <style>
        .simansa-nilai-hero{margin-bottom:1.5rem;padding:1.75rem 1.8rem;border-radius:24px;background:linear-gradient(135deg,#1f4fd1 0%,#2f8ca3 100%);color:#fff;box-shadow:0 20px 45px rgba(31,79,209,.18)}
        .simansa-nilai-hero__content{display:flex;justify-content:space-between;gap:1.5rem;align-items:flex-start}
        .simansa-nilai-hero__eyebrow{display:inline-flex;align-items:center;gap:.45rem;font-size:.78rem;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:rgba(255,255,255,.84);margin-bottom:.7rem}
        .simansa-nilai-hero h2{margin:0 0 .4rem;font-size:2rem;font-weight:700}
        .simansa-nilai-hero p{margin:0;max-width:760px;color:rgba(255,255,255,.9)}
        .simansa-nilai-hero__meta{display:grid;grid-template-columns:repeat(2,minmax(170px,1fr));gap:.9rem;min-width:360px}
        .simansa-nilai-chip{padding:1rem 1.1rem;border-radius:18px;background:rgba(255,255,255,.12);border:1px solid rgba(255,255,255,.18)}
        .simansa-nilai-chip__label{display:block;margin-bottom:.35rem;font-size:.72rem;letter-spacing:.05em;text-transform:uppercase;color:rgba(255,255,255,.74)}
        .simansa-nilai-chip strong{color:#fff}
        .simansa-nilai-panel{background:#fff;border-radius:22px;box-shadow:0 14px 34px rgba(15,23,42,.08);margin-bottom:1.5rem;overflow:hidden}
        .simansa-nilai-panel__header{padding:1.35rem 1.5rem;border-bottom:1px solid rgba(148,163,184,.18)}
        .simansa-nilai-panel__header h3{margin:0 0 .25rem;font-size:1.1rem;font-weight:700;color:#1f2a44}
        .simansa-nilai-panel__header p{margin:0;color:#60708b;font-size:.92rem}
        .simansa-nilai-panel__body{padding:1.5rem}
        @media (max-width:992px){.simansa-nilai-hero__content{flex-direction:column;align-items:stretch}.simansa-nilai-hero__meta{grid-template-columns:1fr;min-width:0}}
    </style>
@stop
