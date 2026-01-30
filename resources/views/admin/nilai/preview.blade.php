@extends('adminlte::page')

@section('title', 'Preview Upload Nilai')

@section('content_header')
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1><i class="fas fa-eye"></i> Preview Data Nilai</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.nilai.index') }}">Nilai Siswa</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.nilai.upload-form') }}">Upload</a></li>
                <li class="breadcrumb-item active">Preview</li>
            </ol>
        </div>
    </div>
@stop

@section('content')
    {{-- Flash Messages --}}
    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show">
        <button type="button" class="close" data-dismiss="alert">&times;</button>
        <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
    </div>
    @endif
    
    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">
        <button type="button" class="close" data-dismiss="alert">&times;</button>
        <i class="fas fa-check-circle"></i> {{ session('success') }}
    </div>
    @endif

    {{-- Summary --}}
    <div class="row">
        <div class="col-md-3">
            <div class="info-box bg-info">
                <span class="info-box-icon"><i class="fas fa-users"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Total Siswa</span>
                    <span class="info-box-number">{{ $totalSiswa }}</span>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="info-box bg-success">
                <span class="info-box-icon"><i class="fas fa-check-circle"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Total Nilai</span>
                    <span class="info-box-number">{{ $totalNilai }}</span>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="info-box bg-primary">
                <span class="info-box-icon"><i class="fas fa-calendar"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Semester</span>
                    <span class="info-box-number">{{ $semesterLabel }}</span>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="info-box bg-secondary">
                <span class="info-box-icon"><i class="fas fa-book"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Tahun Pelajaran</span>
                    <span class="info-box-number">{{ $tahunPelajaranNama }}</span>
                </div>
            </div>
        </div>
    </div>

    {{-- NISN Tidak Ditemukan --}}
    @if(count($notFoundNisn) > 0)
    <div class="alert alert-warning">
        <i class="fas fa-exclamation-triangle"></i> <strong>{{ count($notFoundNisn) }} NISN tidak ditemukan di sistem:</strong>
        <br>
        <small>{{ implode(', ', array_slice($notFoundNisn, 0, 20)) }}{{ count($notFoundNisn) > 20 ? '...' : '' }}</small>
    </div>
    @endif

    {{-- Actions --}}
    <div class="card">
        <div class="card-body">
            <form action="{{ route('admin.nilai.confirm-upload') }}" method="POST" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-success btn-lg">
                    <i class="fas fa-save"></i> Simpan {{ $totalNilai }} Nilai
                </button>
            </form>
            <a href="{{ route('admin.nilai.cancel-upload') }}" class="btn btn-danger btn-lg">
                <i class="fas fa-times"></i> Batal
            </a>
        </div>
    </div>

    {{-- Data Preview Table --}}
    <div class="card card-primary card-outline">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-table"></i> Data yang akan disimpan</h3>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered table-striped table-sm mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="text-center" style="width: 40px;">No</th>
                            <th style="width: 100px;">NISN</th>
                            <th style="width: 200px;">Nama</th>
                            @foreach($urutanMapel as $kode)
                            <th class="text-center" style="width: 45px;" title="{{ $kode }}">{{ $kode }}</th>
                            @endforeach
                            <th class="text-center" style="width: 50px;">Jml</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($previewData as $index => $item)
                        <tr>
                            <td class="text-center">{{ $index + 1 }}</td>
                            <td><code>{{ $item['nisn'] }}</code></td>
                            <td>{{ $item['nama'] }}</td>
                            @foreach($urutanMapel as $kode)
                            <td class="text-center">
                                @if(isset($item['nilai'][$kode]) && $item['nilai'][$kode] !== null)
                                    <span class="badge {{ $item['nilai'][$kode] >= 75 ? 'badge-success' : ($item['nilai'][$kode] >= 60 ? 'badge-warning' : 'badge-danger') }}">
                                        {{ $item['nilai'][$kode] }}
                                    </span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            @endforeach
                            <td class="text-center"><strong>{{ $item['jumlah_mapel'] }}</strong></td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer">
            <form action="{{ route('admin.nilai.confirm-upload') }}" method="POST" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-success">
                    <i class="fas fa-save"></i> Simpan Semua Data
                </button>
            </form>
            <a href="{{ route('admin.nilai.cancel-upload') }}" class="btn btn-danger">
                <i class="fas fa-times"></i> Batal
            </a>
        </div>
    </div>
@stop

@section('css')
<style>
    .table th, .table td {
        vertical-align: middle;
        font-size: 11px;
    }
    .badge {
        font-size: 10px;
        padding: 3px 6px;
    }
</style>
@stop
