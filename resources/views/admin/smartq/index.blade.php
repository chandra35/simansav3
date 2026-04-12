@extends('adminlte::page')

@section('title', 'SMART-Q Kelas Unggulan')

@section('content_header')
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1><i class="fas fa-star text-warning"></i> SMART-Q Kelas Unggulan</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item active">SMART-Q</li>
            </ol>
        </div>
    </div>
@stop

@section('content')
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            <i class="fas fa-check-circle"></i> {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
        </div>
    @endif

    {{-- Hero Section --}}
    <div class="card bg-gradient-primary text-white mb-4">
        <div class="card-body">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h3 class="mb-1"><i class="fas fa-award"></i> Program SMART-Q</h3>
                    <p class="mb-2 text-white-50">
                        <strong>S</strong>piritual · <strong>M</strong>ultitalent · <strong>A</strong>cademic · <strong>R</strong>esearch · <strong>T</strong>echnology · <strong>Q</strong>ur'anic
                    </p>
                    <p class="mb-0">Sistem seleksi dan pengelolaan kelas unggulan MAN 1 Metro. Integrasi CBT Moodle, input nilai manual, dan ranking otomatis.</p>
                </div>
                <div class="col-md-4 text-right">
                    <a href="{{ route('admin.smartq.create') }}" class="btn btn-light btn-lg">
                        <i class="fas fa-plus-circle"></i> Buat Periode Seleksi
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- Daftar Periode --}}
    @forelse ($periodes as $periode)
        <div class="card card-outline {{ $periode->status === 'selesai' ? 'card-success' : ($periode->status === 'seleksi' ? 'card-warning' : 'card-primary') }}">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-clipboard-list"></i> {{ $periode->nama }}
                </h3>
                <div class="card-tools">
                    {!! $periode->status_badge !!}
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <table class="table table-sm table-borderless mb-0">
                            <tr>
                                <td class="text-muted" width="140"><i class="fas fa-calendar"></i> Tahun Pelajaran</td>
                                <td><strong>{{ $periode->tahunPelajaran->nama ?? '-' }}</strong></td>
                            </tr>
                            <tr>
                                <td class="text-muted"><i class="fas fa-clock"></i> Periode</td>
                                <td>{{ $periode->tanggal_mulai->format('d M Y') }} - {{ $periode->tanggal_selesai->format('d M Y') }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted"><i class="fas fa-users"></i> Kuota</td>
                                <td><strong>{{ $periode->kuota }}</strong> siswa</td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-4">
                        <div class="row text-center">
                            <div class="col-4">
                                <div class="text-muted small">Peserta</div>
                                <h3 class="text-primary mb-0">{{ $periode->pesertas_count }}</h3>
                            </div>
                            <div class="col-4">
                                <div class="text-muted small">Lulus</div>
                                <h3 class="text-success mb-0">{{ $periode->peserta_lulus_count }}</h3>
                            </div>
                            <div class="col-4">
                                <div class="text-muted small">Komponen</div>
                                <h3 class="text-info mb-0">{{ $periode->komponenNilais->count() }}</h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 text-right">
                        <div class="btn-group">
                            <a href="{{ route('admin.smartq.show', $periode) }}" class="btn btn-sm btn-primary">
                                <i class="fas fa-eye"></i> Detail
                            </a>
                            <a href="{{ route('admin.smartq.edit', $periode) }}" class="btn btn-sm btn-warning">
                                <i class="fas fa-edit"></i> Edit
                            </a>
                            <a href="{{ route('admin.smartq.peserta', $periode) }}" class="btn btn-sm btn-info">
                                <i class="fas fa-user-plus"></i> Peserta
                            </a>
                            <a href="{{ route('admin.smartq.nilai', $periode) }}" class="btn btn-sm btn-success">
                                <i class="fas fa-pencil-alt"></i> Nilai
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @empty
        <div class="card">
            <div class="card-body text-center py-5">
                <i class="fas fa-star fa-3x text-muted mb-3"></i>
                <h4 class="text-muted">Belum ada periode seleksi SMART-Q</h4>
                <p class="text-muted">Klik tombol di atas untuk membuat periode seleksi baru.</p>
            </div>
        </div>
    @endforelse
@stop

    @section('js')
    @include('admin.smartq._overlay')
    @stop
