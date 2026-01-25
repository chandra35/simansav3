@extends('adminlte::page')

@section('title', 'Detail Ekstrakurikuler')

@section('content_header')
    <h1><i class="fas fa-running mr-2"></i>Detail Ekstrakurikuler</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">{{ $ekstrakurikuler->nama }}</h3>
                    <div class="card-tools">
                        @if($ekstrakurikuler->is_wajib)
                            <span class="badge badge-warning">Wajib</span>
                        @else
                            <span class="badge badge-info">Pilihan</span>
                        @endif
                        @if($ekstrakurikuler->is_aktif)
                            <span class="badge badge-success">Aktif</span>
                        @else
                            <span class="badge badge-secondary">Tidak Aktif</span>
                        @endif
                    </div>
                </div>
                <div class="card-body">
                    <table class="table table-bordered">
                        <tr>
                            <th width="30%">Tahun Pelajaran</th>
                            <td>{{ $ekstrakurikuler->tahunPelajaran?->nama ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th>Pembina</th>
                            <td>{{ $ekstrakurikuler->pembina?->nama ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th>Hari Kegiatan</th>
                            <td>{{ $ekstrakurikuler->hari_kegiatan ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th>Waktu Kegiatan</th>
                            <td>{{ $ekstrakurikuler->waktu_kegiatan ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th>Tempat</th>
                            <td>{{ $ekstrakurikuler->tempat ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th>Kuota</th>
                            <td>{{ $ekstrakurikuler->jumlah_anggota }} / {{ $ekstrakurikuler->kuota_max ?? '∞' }}</td>
                        </tr>
                        <tr>
                            <th>Biaya</th>
                            <td>Rp {{ number_format($ekstrakurikuler->biaya ?? 0, 0, ',', '.') }}</td>
                        </tr>
                        <tr>
                            <th>Deskripsi</th>
                            <td>{{ $ekstrakurikuler->deskripsi ?? '-' }}</td>
                        </tr>
                    </table>
                </div>
                <div class="card-footer">
                    <a href="{{ route('admin.ekstrakurikuler.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left mr-1"></i> Kembali
                    </a>
                    <a href="{{ route('admin.ekstrakurikuler.anggota', $ekstrakurikuler->id) }}" class="btn btn-primary float-right ml-2">
                        <i class="fas fa-users mr-1"></i> Kelola Anggota
                    </a>
                    <a href="{{ route('admin.ekstrakurikuler.edit', $ekstrakurikuler->id) }}" class="btn btn-warning float-right">
                        <i class="fas fa-edit mr-1"></i> Edit
                    </a>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title"><i class="fas fa-users"></i> Anggota Aktif ({{ $ekstrakurikuler->anggotaAktif->count() }})</h5>
                </div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush">
                        @forelse($ekstrakurikuler->anggotaAktif->take(10) as $anggota)
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <div>
                                    <strong>{{ $anggota->siswa?->nama ?? '-' }}</strong>
                                    @if($anggota->jabatan)
                                        <br><small class="text-muted">{{ $anggota->jabatan }}</small>
                                    @endif
                                </div>
                                @if($anggota->nilai_ekskul)
                                    <span class="badge badge-primary">{{ $anggota->predikat }}</span>
                                @endif
                            </li>
                        @empty
                            <li class="list-group-item text-center text-muted">
                                Belum ada anggota
                            </li>
                        @endforelse
                    </ul>
                </div>
                @if($ekstrakurikuler->anggotaAktif->count() > 10)
                    <div class="card-footer text-center">
                        <a href="{{ route('admin.ekstrakurikuler.anggota', $ekstrakurikuler->id) }}">
                            Lihat semua {{ $ekstrakurikuler->anggotaAktif->count() }} anggota
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
@stop
