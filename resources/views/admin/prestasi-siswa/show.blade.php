@extends('adminlte::page')

@section('title', 'Detail Prestasi Siswa')

@section('content_header')
    <h1><i class="fas fa-trophy mr-2"></i>Detail Prestasi Siswa</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">{{ $prestasiSiswa->nama_prestasi }}</h3>
                    <div class="card-tools">
                        @if($prestasiSiswa->is_verified)
                            <span class="badge badge-success"><i class="fas fa-check"></i> Terverifikasi</span>
                        @else
                            <span class="badge badge-warning"><i class="fas fa-clock"></i> Belum Diverifikasi</span>
                        @endif
                    </div>
                </div>
                <div class="card-body">
                    <table class="table table-bordered">
                        <tr>
                            <th width="30%">Siswa</th>
                            <td>{{ $prestasiSiswa->siswa?->nama ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th>NISN</th>
                            <td>{{ $prestasiSiswa->siswa?->nisn ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th>Tahun Pelajaran</th>
                            <td>{{ $prestasiSiswa->tahunPelajaran?->nama ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th>Jenis Prestasi</th>
                            <td>{{ $prestasiSiswa->jenis_label }}</td>
                        </tr>
                        <tr>
                            <th>Tingkat</th>
                            <td><span class="badge badge-{{ $prestasiSiswa->tingkat_badge }}">{{ $prestasiSiswa->tingkat_label }}</span></td>
                        </tr>
                        <tr>
                            <th>Peringkat</th>
                            <td><span class="badge badge-{{ $prestasiSiswa->peringkat_badge }}">{{ $prestasiSiswa->peringkat_label }}</span></td>
                        </tr>
                        <tr>
                            <th>Penyelenggara</th>
                            <td>{{ $prestasiSiswa->penyelenggara }}</td>
                        </tr>
                        <tr>
                            <th>Tanggal</th>
                            <td>{{ $prestasiSiswa->tanggal_prestasi?->format('d F Y') }}</td>
                        </tr>
                        <tr>
                            <th>Tempat</th>
                            <td>{{ $prestasiSiswa->tempat ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th>Pembina</th>
                            <td>{{ $prestasiSiswa->pembina?->nama ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th>Deskripsi</th>
                            <td>{{ $prestasiSiswa->deskripsi ?? '-' }}</td>
                        </tr>
                    </table>
                    
                    @if($prestasiSiswa->is_verified)
                        <div class="alert alert-success mt-3">
                            <i class="fas fa-check-circle"></i>
                            Diverifikasi oleh <strong>{{ $prestasiSiswa->verifiedBy?->name ?? '-' }}</strong>
                            pada {{ $prestasiSiswa->verified_at?->format('d M Y H:i') }}
                        </div>
                    @endif
                </div>
                <div class="card-footer">
                    <a href="{{ route('admin.prestasi-siswa.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left mr-1"></i> Kembali
                    </a>
                    @can('edit-prestasi-siswa')
                        <a href="{{ route('admin.prestasi-siswa.edit', $prestasiSiswa->id) }}" class="btn btn-warning float-right">
                            <i class="fas fa-edit mr-1"></i> Edit
                        </a>
                    @endcan
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            @if($prestasiSiswa->foto)
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title"><i class="fas fa-image"></i> Foto Dokumentasi</h5>
                    </div>
                    <div class="card-body text-center">
                        <img src="{{ Storage::disk('public')->url($prestasiSiswa->foto) }}" alt="Foto Prestasi" class="img-fluid rounded">
                    </div>
                </div>
            @endif
            
            @if($prestasiSiswa->sertifikat)
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title"><i class="fas fa-certificate"></i> Sertifikat</h5>
                    </div>
                    <div class="card-body text-center">
                        <a href="{{ Storage::disk('public')->url($prestasiSiswa->sertifikat) }}" target="_blank" class="btn btn-primary btn-block">
                            <i class="fas fa-download mr-1"></i> Download Sertifikat
                        </a>
                    </div>
                </div>
            @endif
        </div>
    </div>
@stop
