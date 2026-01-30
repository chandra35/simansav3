@extends('adminlte::page')

@section('title', 'Nilai Siswa - ' . $siswa->nama_lengkap)

@section('content_header')
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1><i class="fas fa-user-graduate"></i> Nilai Siswa</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.nilai.index') }}">Nilai Siswa</a></li>
                <li class="breadcrumb-item active">{{ $siswa->nama_lengkap }}</li>
            </ol>
        </div>
    </div>
@stop

@section('content')
    {{-- Info Siswa --}}
    <div class="card card-primary card-outline">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-user"></i> Informasi Siswa</h3>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <table class="table table-sm table-borderless">
                        <tr>
                            <td width="150"><strong>Nama Lengkap</strong></td>
                            <td>: {{ $siswa->nama_lengkap }}</td>
                        </tr>
                        <tr>
                            <td><strong>NISN</strong></td>
                            <td>: {{ $siswa->nisn }}</td>
                        </tr>
                        <tr>
                            <td><strong>Jenis Kelamin</strong></td>
                            <td>: {{ $siswa->jenis_kelamin == 'L' ? 'Laki-laki' : 'Perempuan' }}</td>
                        </tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <table class="table table-sm table-borderless">
                        <tr>
                            <td width="150"><strong>Tempat, Tgl Lahir</strong></td>
                            <td>: {{ $siswa->tempat_lahir }}, {{ $siswa->tanggal_lahir ? $siswa->tanggal_lahir->format('d F Y') : '-' }}</td>
                        </tr>
                        <tr>
                            <td><strong>Status</strong></td>
                            <td>: <span class="badge badge-{{ $siswa->status_siswa == 'aktif' ? 'success' : 'secondary' }}">{{ ucfirst($siswa->status_siswa ?? 'Aktif') }}</span></td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- Nilai per Semester --}}
    @forelse($nilaiList as $sem => $nilaiSemester)
    <div class="card card-info card-outline">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-book"></i> 
                {{ \App\Models\NilaiSiswa::SEMESTER_LABELS[$sem] ?? "Semester {$sem}" }}
            </h3>
            <div class="card-tools">
                <span class="badge badge-primary">{{ $nilaiSemester->count() }} Mapel</span>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped table-sm">
                    <thead class="bg-light">
                        <tr>
                            <th width="5%">No</th>
                            <th width="10%">Kode</th>
                            <th>Mata Pelajaran</th>
                            <th width="10%">Nilai</th>
                            <th width="10%">Predikat</th>
                            <th width="15%">Sumber Data</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $no = 1; $totalNilai = 0; @endphp
                        @foreach($nilaiSemester as $nilai)
                        <tr>
                            <td class="text-center">{{ $no++ }}</td>
                            <td><code>{{ $nilai->mataPelajaran->kode_mapel }}</code></td>
                            <td>{{ $nilai->mataPelajaran->nama_mapel }}</td>
                            <td class="text-center"><strong>{{ $nilai->nilai }}</strong></td>
                            <td class="text-center">
                                @php
                                    $predikat = $nilai->predikat ?? \App\Models\NilaiSiswa::hitungPredikat($nilai->nilai);
                                    $badgeClass = match($predikat) {
                                        'A' => 'success',
                                        'B' => 'primary',
                                        'C' => 'warning',
                                        'D' => 'danger',
                                        default => 'secondary'
                                    };
                                @endphp
                                <span class="badge badge-{{ $badgeClass }}">{{ $predikat }}</span>
                            </td>
                            <td class="text-center">
                                @if($nilai->sumber_data == 'import_excel')
                                    <span class="badge badge-info"><i class="fas fa-file-excel"></i> Import</span>
                                @else
                                    <span class="badge badge-secondary"><i class="fas fa-keyboard"></i> Manual</span>
                                @endif
                            </td>
                        </tr>
                        @php $totalNilai += $nilai->nilai; @endphp
                        @endforeach
                    </tbody>
                    <tfoot class="bg-light">
                        <tr>
                            <th colspan="3" class="text-right">Rata-rata:</th>
                            <th class="text-center">{{ $nilaiSemester->count() > 0 ? round($totalNilai / $nilaiSemester->count(), 2) : '-' }}</th>
                            <th colspan="2"></th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
    @empty
    <div class="alert alert-info">
        <i class="fas fa-info-circle"></i> Belum ada data nilai untuk siswa ini.
    </div>
    @endforelse

    <div class="card">
        <div class="card-body">
            <a href="{{ $semester ? route('admin.nilai.semester', $semester) : route('admin.nilai.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Kembali
            </a>
        </div>
    </div>
@stop
