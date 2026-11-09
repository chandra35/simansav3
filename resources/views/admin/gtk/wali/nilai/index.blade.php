@extends('adminlte::page')

@section('title', 'Buku Nilai Kelas ' . $kelas->nama_kelas)

@section('content_header')
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1><i class="fas fa-award mr-2"></i>Buku Nilai Kelas {{ $kelas->nama_kelas }}</h1>
        </div>
        <div class="col-sm-6">
            <div class="float-sm-right">
                <a href="{{ route('admin.gtk.wali.siswa.index', ['kelas_id' => $kelas->id]) }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left mr-1"></i> Data Siswa
                </a>
            </div>
        </div>
    </div>
@endsection

@section('content')
<div class="container-fluid">
    @if($kelasList->count() > 1)
        @include('admin.gtk.wali.partials.kelas-switcher', ['route' => 'admin.gtk.wali.nilai.index'])
    @endif

    {{-- Filter Card --}}
    <div class="card card-outline card-primary mb-3">
        <div class="card-header py-2">
            <h3 class="card-title font-weight-bold"><i class="fas fa-filter mr-1"></i> Filter Nilai Siswa</h3>
        </div>
        <div class="card-body py-2">
            <form method="GET" action="{{ route('admin.gtk.wali.nilai.index') }}" class="form-row align-items-end">
                <input type="hidden" name="kelas_id" value="{{ $kelas->id }}">
                
                <div class="col-md-3 mb-2">
                    <label class="small text-muted mb-1">Mata Pelajaran</label>
                    <select name="mata_pelajaran_id" class="form-control form-control-sm select2">
                        <option value="">-- Semua Mata Pelajaran --</option>
                        @foreach($mapelList as $mp)
                            <option value="{{ $mp->id }}" {{ $selectedMapelId == $mp->id ? 'selected' : '' }}>
                                {{ $mp->nama }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-3 mb-2">
                    <label class="small text-muted mb-1">Kategori Penilaian</label>
                    <select name="kategori" class="form-control form-control-sm">
                        <option value="">-- Semua Kategori --</option>
                        <option value="harian" {{ $selectedKategori === 'harian' ? 'selected' : '' }}>Penilaian Harian (PH)</option>
                        <option value="tengah_semester" {{ $selectedKategori === 'tengah_semester' ? 'selected' : '' }}>Tengah Semester (ATS / PTS)</option>
                        <option value="akhir_semester" {{ $selectedKategori === 'akhir_semester' ? 'selected' : '' }}>Akhir Semester (ASAS / PAS)</option>
                        <option value="tugas" {{ $selectedKategori === 'tugas' ? 'selected' : '' }}>Tugas / PR</option>
                        <option value="proyek" {{ $selectedKategori === 'proyek' ? 'selected' : '' }}>Proyek / Praktik</option>
                    </select>
                </div>

                <div class="col-md-2 mb-2">
                    <label class="small text-muted mb-1">Semester</label>
                    <select name="semester" class="form-control form-control-sm">
                        <option value="1" {{ $semester == 1 ? 'selected' : '' }}>Semester Ganjil</option>
                        <option value="2" {{ $semester == 2 ? 'selected' : '' }}>Semester Genap</option>
                    </select>
                </div>

                <div class="col-md-4 mb-2">
                    <button type="submit" class="btn btn-sm btn-primary">
                        <i class="fas fa-search mr-1"></i> Terapkan Filter
                    </button>
                    <a href="{{ route('admin.gtk.wali.nilai.index', ['kelas_id' => $kelas->id]) }}" class="btn btn-sm btn-secondary ml-1">
                        <i class="fas fa-undo mr-1"></i> Reset
                    </a>
                </div>
            </form>
        </div>
    </div>

    {{-- Ringkasan Statistik Nilai --}}
    <div class="row">
        <div class="col-md-3 col-sm-6 col-12">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>{{ $totalEvaluations }}</h3>
                    <p>Total Nilai Masuk</p>
                </div>
                <div class="icon">
                    <i class="fas fa-file-invoice"></i>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 col-12">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>{{ $averageScore }}</h3>
                    <p>Rata-rata Kelas</p>
                </div>
                <div class="icon">
                    <i class="fas fa-calculator"></i>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 col-12">
            <div class="small-box bg-primary">
                <div class="inner">
                    <h3>{{ $highestScore }}</h3>
                    <p>Nilai Tertinggi</p>
                </div>
                <div class="icon">
                    <i class="fas fa-trophy"></i>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 col-12">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3>{{ $lowestScore }}</h3>
                    <p>Nilai Terendah</p>
                </div>
                <div class="icon">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
            </div>
        </div>
    </div>

    {{-- Tabel Nilai --}}
    <div class="card card-outline card-secondary">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h3 class="card-title font-weight-bold">
                <i class="fas fa-table mr-1"></i> 
                Rekap Penilaian Siswa (T.A. {{ $activeYear?->nama ?? '-' }} - Semester {{ $semester == 1 ? 'Ganjil' : 'Genap' }})
            </h3>
        </div>
        <div class="card-body p-0 table-responsive">
            @if($scores->isEmpty())
                <div class="text-center py-5 text-muted">
                    <i class="fas fa-clipboard-list fa-3x mb-3 text-secondary"></i>
                    <h5>Belum Ada Data Nilai untuk Filter Ini</h5>
                    <p class="small">Guru mata pelajaran dapat mengarsipkan skor kuis dari Moodle CBT atau menginput nilai melalui portal Penilaian.</p>
                </div>
            @else
                <table class="table table-hover table-striped table-bordered mb-0">
                    <thead class="thead-light">
                        <tr class="text-center">
                            <th style="width: 50px;">No</th>
                            <th style="width: 120px;">NISN</th>
                            <th>Nama Siswa</th>
                            <th>Mata Pelajaran</th>
                            <th>Kategori</th>
                            <th>Nama Penilaian</th>
                            <th style="width: 80px;">Skor Asli</th>
                            <th style="width: 80px;">Remedial</th>
                            <th style="width: 80px;">Nilai Akhir</th>
                            <th>Guru Pengampu</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($scores as $index => $row)
                            <tr>
                                <td class="text-center">{{ $index + 1 }}</td>
                                <td class="text-center"><code>{{ $row->siswa?->nisn ?? '-' }}</code></td>
                                <td class="font-weight-bold">{{ $row->siswa?->nama_lengkap ?? '-' }}</td>
                                <td>{{ $row->mataPelajaran?->nama ?? '-' }}</td>
                                <td class="text-center">
                                    @php
                                        $badgeClass = match($row->kategori) {
                                            'harian' => 'badge-info',
                                            'tengah_semester' => 'badge-warning',
                                            'akhir_semester' => 'badge-danger',
                                            'tugas' => 'badge-primary',
                                            default => 'badge-secondary',
                                        };
                                        $badgeLabel = \App\Models\PenilaianSiswa::KATEGORI_LABELS[$row->kategori] ?? ucfirst($row->kategori);
                                    @endphp
                                    <span class="badge {{ $badgeClass }}">{{ $badgeLabel }}</span>
                                </td>
                                <td>
                                    {{ $row->nama_penilaian }}
                                    @if($row->moodle_quiz_archive_id)
                                        <span class="badge badge-light border text-muted ml-1" title="Sinkron dari CBT Moodle">
                                            <i class="fas fa-cloud-download-alt"></i> Moodle
                                        </span>
                                    @endif
                                </td>
                                <td class="text-center font-weight-bold">{{ round($row->nilai, 1) }}</td>
                                <td class="text-center text-muted">{{ $row->remedial !== null ? round($row->remedial, 1) : '-' }}</td>
                                <td class="text-center font-weight-bold {{ $row->nilai_akhir < 75 ? 'text-danger' : 'text-success' }}">
                                    {{ round($row->nilai_akhir, 1) }}
                                </td>
                                <td><small class="text-muted">{{ $row->gtk?->nama_lengkap ?? '-' }}</small></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>
</div>
@endsection
