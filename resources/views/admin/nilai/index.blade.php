@extends('adminlte::page')

@section('title', 'Nilai Siswa')

@section('content_header')
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1><i class="fas fa-chart-line"></i> Nilai Siswa</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item active">Nilai Siswa</li>
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

    {{-- Actions --}}
    <div class="card card-primary card-outline">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-cog"></i> Aksi</h3>
        </div>
        <div class="card-body">
            <a href="{{ route('admin.nilai.upload-form') }}" class="btn btn-success">
                <i class="fas fa-file-excel"></i> Upload Nilai dari Excel
            </a>
            <a href="{{ route('admin.nilai.template') }}" class="btn btn-info">
                <i class="fas fa-download"></i> Download Template Excel
            </a>
        </div>
    </div>

    {{-- Summary per Semester --}}
    <div class="card card-primary card-outline">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-list"></i> Data Nilai per Semester</h3>
        </div>
        <div class="card-body">
            <div class="row">
                @foreach($summary as $sem => $data)
                <div class="col-md-4 col-lg-2">
                    <div class="info-box bg-{{ $data['jumlah_siswa'] > 0 ? 'success' : 'secondary' }}">
                        <span class="info-box-icon"><i class="fas fa-book"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">{{ $data['label'] }}</span>
                            <span class="info-box-number">{{ $data['jumlah_siswa'] }} Siswa</span>
                            <a href="{{ route('admin.nilai.semester', $sem) }}" class="text-white small">
                                <i class="fas fa-arrow-right"></i> Lihat Detail
                            </a>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Keterangan --}}
    <div class="card card-info card-outline">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-info-circle"></i> Keterangan</h3>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <h5>Semester Mapping</h5>
                    <table class="table table-sm table-bordered">
                        <tr><th>Semester</th><th>Keterangan</th></tr>
                        <tr><td>1</td><td>Kelas X - Semester 1</td></tr>
                        <tr><td>2</td><td>Kelas X - Semester 2</td></tr>
                        <tr><td>3</td><td>Kelas XI - Semester 1</td></tr>
                        <tr><td>4</td><td>Kelas XI - Semester 2</td></tr>
                        <tr><td>5</td><td>Kelas XII - Semester 1</td></tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <h5>Format Upload Excel</h5>
                    <ul>
                        <li>Format file: <strong>.xlsx</strong> atau <strong>.xls</strong></li>
                        <li>Kolom wajib: <strong>NISN</strong></li>
                        <li>Kode mapel di header harus sesuai dengan data di sistem</li>
                        <li>Nilai harus berupa angka (0-100)</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
@stop
