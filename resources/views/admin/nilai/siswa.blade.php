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
    <section class="simansa-nilai-siswa-hero">
        <div class="simansa-nilai-siswa-hero__content">
            <div>
                <div class="simansa-nilai-siswa-hero__eyebrow">
                    <i class="fas fa-user-graduate"></i> Ringkasan Nilai Siswa
                </div>
                <h2>{{ $siswa->nama_lengkap }}</h2>
                <p>Halaman ini merangkum seluruh semester nilai siswa yang tersimpan, lengkap dengan rata-rata, predikat, dan sumber data.</p>
            </div>
            <div class="simansa-nilai-siswa-chip">
                <span class="simansa-nilai-siswa-chip__label">NISN</span>
                <strong>{{ $siswa->nisn }}</strong>
            </div>
        </div>
    </section>

    <div class="simansa-nilai-siswa-panel">
        <div class="simansa-nilai-siswa-panel__header">
            <div>
                <h3><i class="fas fa-user"></i> Informasi Siswa</h3>
                <p>Data identitas ini membantu operator memastikan nilai yang dibuka sesuai siswa yang dimaksud.</p>
            </div>
        </div>
        <div class="simansa-nilai-siswa-panel__body">
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
    <div class="simansa-nilai-siswa-panel">
        <div class="simansa-nilai-siswa-panel__header simansa-nilai-siswa-panel__header--inline">
            <div>
                <h3>
                    <i class="fas fa-book"></i> 
                    {{ \App\Models\NilaiSiswa::SEMESTER_LABELS[$sem] ?? "Semester {$sem}" }}
                </h3>
                <p>Rincian mata pelajaran dan sumber nilai pada semester ini.</p>
            </div>
            <span class="badge badge-primary">{{ $nilaiSemester->count() }} Mapel</span>
        </div>
        <div class="simansa-nilai-siswa-panel__body">
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

@section('css')
    <style>
        .simansa-nilai-siswa-hero{margin-bottom:1.5rem;padding:1.35rem 1.5rem;border-radius:22px;background:linear-gradient(135deg,#2147cf 0%,#2f8d9c 100%);color:#fff;box-shadow:0 18px 40px rgba(33,71,207,.16)}
        .simansa-nilai-siswa-hero__content{display:flex;justify-content:space-between;gap:1rem;align-items:flex-start}
        .simansa-nilai-siswa-hero__eyebrow{display:inline-flex;align-items:center;gap:.45rem;font-size:.78rem;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:rgba(255,255,255,.82);margin-bottom:.75rem}
        .simansa-nilai-siswa-hero h2{margin:0 0 .35rem;font-size:1.75rem;font-weight:700}
        .simansa-nilai-siswa-hero p{margin:0;max-width:760px;color:rgba(255,255,255,.92)}
        .simansa-nilai-siswa-chip{padding:1rem 1.1rem;border-radius:18px;background:rgba(255,255,255,.12);border:1px solid rgba(255,255,255,.18);min-width:220px}
        .simansa-nilai-siswa-chip__label{display:block;margin-bottom:.35rem;font-size:.72rem;letter-spacing:.05em;text-transform:uppercase;color:rgba(255,255,255,.74)}
        .simansa-nilai-siswa-panel{background:#fff;border-radius:22px;box-shadow:0 14px 34px rgba(15,23,42,.08);margin-bottom:1.5rem;overflow:hidden}
        .simansa-nilai-siswa-panel__header{padding:1.35rem 1.5rem;border-bottom:1px solid rgba(148,163,184,.18)}
        .simansa-nilai-siswa-panel__header--inline{display:flex;justify-content:space-between;align-items:flex-start;gap:1rem}
        .simansa-nilai-siswa-panel__header h3{margin:0 0 .25rem;font-size:1.1rem;font-weight:700;color:#1f2a44}
        .simansa-nilai-siswa-panel__header p{margin:0;color:#60708b;font-size:.92rem}
        .simansa-nilai-siswa-panel__body{padding:1.5rem}
        @media (max-width:992px){.simansa-nilai-siswa-hero__content,.simansa-nilai-siswa-panel__header--inline{flex-direction:column;align-items:stretch}.simansa-nilai-siswa-chip{min-width:0}}
    </style>
@stop
