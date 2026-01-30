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

    {{-- Filter Tingkat Kelas --}}
    <div class="card card-primary card-outline">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-filter"></i> Pilih Tingkat Kelas</h3>
        </div>
        <div class="card-body">
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
    <div class="card card-success card-outline">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-cog"></i> Aksi untuk Kelas {{ request('tingkat') }}</h3>
        </div>
        <div class="card-body">
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
            @endif
        </div>
    </div>

    {{-- Summary per Semester untuk tingkat terpilih --}}
    <div class="card card-primary card-outline">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-list"></i> 
                Data Nilai Kelas {{ request('tingkat') }} - Tahun Aktif: {{ $tahunAktif->nama ?? '-' }}
            </h3>
        </div>
        <div class="card-body">
            <div class="row">
                @foreach($semesterList as $sem => $data)
                <div class="col-md-4 col-lg">
                    <div class="info-box {{ $data['jumlah_siswa'] > 0 ? 'bg-success' : 'bg-secondary' }}">
                        <span class="info-box-icon"><i class="fas fa-book"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text" style="font-size: 11px;">{{ $data['label'] }}</span>
                            <span class="info-box-number">{{ $data['jumlah_siswa'] }} Siswa</span>
                            <span class="info-box-text" style="font-size: 10px;">TA: {{ $data['tahun_pelajaran'] ?? '-' }}</span>
                            <a href="{{ route('admin.nilai.semester', $sem) }}?tingkat={{ request('tingkat') }}" class="text-white small">
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
    <div class="card card-info card-outline">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-chart-bar"></i> Overview Data Nilai</h3>
        </div>
        <div class="card-body">
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
