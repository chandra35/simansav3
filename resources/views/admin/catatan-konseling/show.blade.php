@extends('adminlte::page')

@section('title', 'Detail Catatan Konseling')

@section('content_header')
    <h1><i class="fas fa-clipboard-list mr-2"></i>Detail Catatan Konseling</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Catatan Konseling</h3>
                    <div class="card-tools">
                        @if($catatanKonseling->is_rahasia)
                            <span class="badge badge-dark"><i class="fas fa-lock"></i> Rahasia</span>
                        @endif
                        <span class="badge badge-{{ $catatanKonseling->status_badge }}">
                            {{ $catatanKonseling->status_label }}
                        </span>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <table class="table table-borderless table-sm">
                                <tr>
                                    <th width="40%">Siswa</th>
                                    <td>{{ $catatanKonseling->siswa?->nama ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th>NIS</th>
                                    <td>{{ $catatanKonseling->siswa?->nis ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th>Kelas</th>
                                    <td>{{ $catatanKonseling->siswa?->kelasSaatIni?->nama ?? '-' }}</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-borderless table-sm">
                                <tr>
                                    <th width="40%">Konselor</th>
                                    <td>{{ $catatanKonseling->konselor?->nama ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th>Tanggal</th>
                                    <td>{{ $catatanKonseling->tanggal_konseling?->format('d F Y') }}</td>
                                </tr>
                                <tr>
                                    <th>Jenis</th>
                                    <td>{{ $catatanKonseling->jenis_label }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                    
                    <div class="callout callout-info">
                        <h5><i class="fas fa-tag mr-2"></i>Kategori Masalah</h5>
                        <p class="mb-0">{{ $catatanKonseling->kategori_label }}</p>
                    </div>
                    
                    <div class="callout callout-warning">
                        <h5><i class="fas fa-exclamation-triangle mr-2"></i>Permasalahan</h5>
                        <p class="mb-0">{{ $catatanKonseling->deskripsi_masalah }}</p>
                    </div>
                    
                    @if($catatanKonseling->hasil_konseling)
                        <div class="callout callout-success">
                            <h5><i class="fas fa-check-circle mr-2"></i>Hasil Konseling</h5>
                            <p class="mb-0">{{ $catatanKonseling->hasil_konseling }}</p>
                        </div>
                    @endif
                    
                    @if($catatanKonseling->tindak_lanjut)
                        <div class="callout callout-primary">
                            <h5><i class="fas fa-lightbulb mr-2"></i>Tindak Lanjut / Rekomendasi</h5>
                            <p class="mb-0">{{ $catatanKonseling->tindak_lanjut }}</p>
                        </div>
                    @endif
                    
                    @if($catatanKonseling->jadwal_tindak_lanjut)
                        <div class="alert alert-info">
                            <i class="fas fa-calendar-alt mr-2"></i>
                            <strong>Jadwal Tindak Lanjut:</strong> {{ $catatanKonseling->jadwal_tindak_lanjut->format('d F Y') }}
                        </div>
                    @endif
                </div>
                <div class="card-footer">
                    <a href="{{ route('admin.catatan-konseling.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left mr-1"></i> Kembali
                    </a>
                    <a href="{{ route('admin.catatan-konseling.edit', $catatanKonseling->id) }}" class="btn btn-warning float-right">
                        <i class="fas fa-edit mr-1"></i> Edit
                    </a>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card card-primary card-outline">
                <div class="card-header">
                    <h5 class="card-title"><i class="fas fa-user-graduate"></i> Profil Siswa</h5>
                </div>
                <div class="card-body box-profile">
                    <div class="text-center">
                        <img class="profile-user-img img-fluid img-circle" 
                            src="{{ $catatanKonseling->siswa?->foto_url ?? asset('vendor/adminlte/dist/img/user4-128x128.jpg') }}" 
                            alt="User profile picture">
                    </div>
                    <h3 class="profile-username text-center">{{ $catatanKonseling->siswa?->nama ?? '-' }}</h3>
                    <p class="text-muted text-center">{{ $catatanKonseling->siswa?->kelasSaatIni?->nama ?? '-' }}</p>
                </div>
            </div>
            
            @if($riwayatKonseling->count() > 0)
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title"><i class="fas fa-history"></i> Riwayat Konseling</h5>
                    </div>
                    <div class="card-body p-0">
                        <ul class="list-group list-group-flush">
                            @foreach($riwayatKonseling as $riwayat)
                                <li class="list-group-item">
                                    <a href="{{ route('admin.catatan-konseling.show', $riwayat->id) }}">
                                        {{ $riwayat->tanggal_konseling->format('d M Y') }}
                                    </a>
                                    <span class="badge badge-{{ $riwayat->status_badge }} float-right">
                                        {{ $riwayat->status_label }}
                                    </span>
                                    <br>
                                    <small class="text-muted">{{ $riwayat->kategori_label }}</small>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endif
        </div>
    </div>
@stop
