@extends('adminlte::page')

@section('title', 'Data Lulusan')

@section('content_header')
    <h1>Data Lulusan</h1>
@stop

@section('content')
    <div class="card card-outline card-primary">
        <div class="card-header">
            <h3 class="card-title">Filter Rekap Lulusan</h3>
        </div>
        <form method="GET" action="{{ route('admin.lulusan.index') }}">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Tahun Pelajaran</label>
                            <select name="tahun_pelajaran_id" class="form-control">
                                @foreach($tahunPelajaranList as $tahun)
                                    <option value="{{ $tahun->id }}" {{ optional($selectedTahun)->id === $tahun->id ? 'selected' : '' }}>
                                        {{ $tahun->nama }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Status Pengisian</label>
                            <select name="status_pengisian" class="form-control">
                                <option value="">Semua Status</option>
                                <option value="sudah_isi" {{ request('status_pengisian') === 'sudah_isi' ? 'selected' : '' }}>Sudah Isi</option>
                                <option value="belum_isi" {{ request('status_pengisian') === 'belum_isi' ? 'selected' : '' }}>Belum Isi</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Jalur Masuk</label>
                            <select name="jalur_masuk" class="form-control">
                                <option value="">Semua Jalur</option>
                                @foreach($jalurMasukOptions as $jalur)
                                    <option value="{{ $jalur }}" {{ request('jalur_masuk') === $jalur ? 'selected' : '' }}>{{ $jalur }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Pencarian</label>
                            <input type="text" name="q" class="form-control" value="{{ request('q') }}" placeholder="Nama, NISN, kampus, prodi">
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-footer">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-filter mr-1"></i> Terapkan Filter
                </button>
                <a href="{{ route('admin.lulusan.index') }}" class="btn btn-default">
                    Reset
                </a>
            </div>
        </form>
    </div>

    @if($selectedTahun)
        <div class="row">
            <div class="col-md-3">
                <div class="small-box bg-info">
                    <div class="inner">
                        <h3>{{ $summary['total'] }}</h3>
                        <p>Total Siswa Kelas 12</p>
                    </div>
                    <div class="icon"><i class="fas fa-users"></i></div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="small-box bg-success">
                    <div class="inner">
                        <h3>{{ $summary['sudah_isi'] }}</h3>
                        <p>Sudah Mengisi</p>
                    </div>
                    <div class="icon"><i class="fas fa-check-circle"></i></div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="small-box bg-warning">
                    <div class="inner">
                        <h3>{{ $summary['belum_isi'] }}</h3>
                        <p>Belum Mengisi</p>
                    </div>
                    <div class="icon"><i class="fas fa-edit"></i></div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="small-box bg-primary">
                    <div class="inner">
                        <h3>{{ $selectedTahun->nama }}</h3>
                        <p>Tahun Dipilih</p>
                    </div>
                    <div class="icon"><i class="fas fa-calendar-alt"></i></div>
                </div>
            </div>
        </div>

        <div class="card card-outline card-primary">
            <div class="card-header">
                <h3 class="card-title">Daftar Lulusan Kelas 12</h3>
            </div>
            <div class="card-body table-responsive p-0">
                <table class="table table-striped table-hover mb-0">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama Siswa</th>
                            <th>NISN</th>
                            <th>Kelas</th>
                            <th>Status</th>
                            <th>Jalur</th>
                            <th>Universitas</th>
                            <th>Jurusan/Fakultas</th>
                            <th>Program Studi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($siswas as $index => $siswa)
                            @php
                                $kelas = $siswa->getRelation('kelas_target');
                                $lulusan = $siswa->getRelation('lulusan_target');
                            @endphp
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $siswa->nama_lengkap }}</td>
                                <td>{{ $siswa->nisn }}</td>
                                <td>{{ $kelas->nama_lengkap ?? $kelas->nama_kelas ?? '-' }}</td>
                                <td>
                                    @if($lulusan)
                                        <span class="badge badge-success">Sudah Isi</span>
                                    @else
                                        <span class="badge badge-secondary">Belum Isi</span>
                                    @endif
                                </td>
                                <td>{{ $lulusan->jalur_masuk ?? '-' }}</td>
                                <td>{{ $lulusan->nama_universitas ?? '-' }}</td>
                                <td>{{ $lulusan->jurusan_fakultas ?? '-' }}</td>
                                <td>{{ $lulusan->program_studi ?? '-' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center text-muted py-4">Tidak ada data siswa untuk filter ini.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="card-footer">
                <div class="row">
                    @foreach($summary['per_jalur'] as $jalur => $jumlah)
                        <div class="col-md-2 col-6 mb-2">
                            <span class="badge badge-light p-2 d-block text-left">
                                {{ $jalur }}: {{ $jumlah }}
                            </span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @else
        <div class="alert alert-warning">
            Belum ada tahun pelajaran yang tersedia.
        </div>
    @endif
@stop
