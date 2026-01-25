@extends('adminlte::page')

@section('title', 'Laporan Pembayaran')

@section('content_header')
    <h1><i class="fas fa-chart-bar mr-2"></i>Laporan Pembayaran</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Filter Laporan</h3>
        </div>
        <div class="card-body">
            <form method="GET" class="row">
                <div class="col-md-3">
                    <div class="form-group">
                        <label>Tahun Pelajaran</label>
                        <select name="tahun_pelajaran_id" class="form-control">
                            @foreach($tahunPelajaran as $tp)
                                <option value="{{ $tp->id }}" {{ $tp->id == $selectedTahunPelajaran ? 'selected' : '' }}>
                                    {{ $tp->nama }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label>Jenis Pembayaran</label>
                        <select name="jenis_pembayaran_id" class="form-control">
                            <option value="">-- Semua Jenis --</option>
                            @foreach($jenisPembayaran as $jp)
                                <option value="{{ $jp->id }}" {{ $jp->id == $selectedJenis ? 'selected' : '' }}>
                                    {{ $jp->nama }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label>Bulan</label>
                        <select name="bulan" class="form-control">
                            <option value="">-- Semua Bulan --</option>
                            @foreach(['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'] as $index => $bulan)
                                <option value="{{ $index + 1 }}" {{ ($index + 1) == $selectedBulan ? 'selected' : '' }}>
                                    {{ $bulan }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label>&nbsp;</label>
                        <button type="submit" class="btn btn-primary btn-block">
                            <i class="fas fa-filter mr-1"></i> Filter
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    
    <div class="row">
        <div class="col-md-3">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>Rp {{ number_format($totalTagihan ?? 0, 0, ',', '.') }}</h3>
                    <p>Total Tagihan</p>
                </div>
                <div class="icon">
                    <i class="fas fa-file-invoice-dollar"></i>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>Rp {{ number_format($totalTerbayar ?? 0, 0, ',', '.') }}</h3>
                    <p>Total Terbayar</p>
                </div>
                <div class="icon">
                    <i class="fas fa-money-bill-wave"></i>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3>Rp {{ number_format($totalBelumBayar ?? 0, 0, ',', '.') }}</h3>
                    <p>Belum Terbayar</p>
                </div>
                <div class="icon">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="small-box bg-primary">
                <div class="inner">
                    <h3>{{ number_format($persentaseBayar ?? 0, 1) }}%</h3>
                    <p>Persentase Terbayar</p>
                </div>
                <div class="icon">
                    <i class="fas fa-percentage"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-chart-pie mr-1"></i> Status Tagihan</h3>
                </div>
                <div class="card-body">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Status</th>
                                <th class="text-right">Jumlah</th>
                                <th class="text-right">Nominal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><span class="badge badge-success">Lunas</span></td>
                                <td class="text-right">{{ $countLunas ?? 0 }}</td>
                                <td class="text-right">Rp {{ number_format($nominalLunas ?? 0, 0, ',', '.') }}</td>
                            </tr>
                            <tr>
                                <td><span class="badge badge-warning">Cicilan</span></td>
                                <td class="text-right">{{ $countCicilan ?? 0 }}</td>
                                <td class="text-right">Rp {{ number_format($nominalCicilan ?? 0, 0, ',', '.') }}</td>
                            </tr>
                            <tr>
                                <td><span class="badge badge-danger">Belum Bayar</span></td>
                                <td class="text-right">{{ $countBelumBayar ?? 0 }}</td>
                                <td class="text-right">Rp {{ number_format($nominalBelumBayar ?? 0, 0, ',', '.') }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-list mr-1"></i> Per Jenis Pembayaran</h3>
                </div>
                <div class="card-body">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Jenis</th>
                                <th class="text-right">Tagihan</th>
                                <th class="text-right">Terbayar</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($perJenis ?? [] as $item)
                                <tr>
                                    <td>{{ $item->nama }}</td>
                                    <td class="text-right">Rp {{ number_format($item->total_tagihan, 0, ',', '.') }}</td>
                                    <td class="text-right">Rp {{ number_format($item->total_terbayar, 0, ',', '.') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center">Tidak ada data</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <div class="card">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-users mr-1"></i> Siswa dengan Tunggakan</h3>
            <div class="card-tools">
                <a href="{{ route('admin.pembayaran.laporan', array_merge(request()->all(), ['export' => 'excel'])) }}" class="btn btn-sm btn-success">
                    <i class="fas fa-file-excel mr-1"></i> Export Excel
                </a>
            </div>
        </div>
        <div class="card-body table-responsive p-0">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Siswa</th>
                        <th>Kelas</th>
                        <th class="text-right">Total Tagihan</th>
                        <th class="text-right">Terbayar</th>
                        <th class="text-right">Sisa</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($siswaMenunggak ?? [] as $index => $item)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $item->siswa?->nama ?? '-' }}</td>
                            <td>{{ $item->siswa?->kelasSaatIni?->nama ?? '-' }}</td>
                            <td class="text-right">Rp {{ number_format($item->total_tagihan, 0, ',', '.') }}</td>
                            <td class="text-right">Rp {{ number_format($item->total_terbayar, 0, ',', '.') }}</td>
                            <td class="text-right text-danger font-weight-bold">Rp {{ number_format($item->sisa, 0, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center">Tidak ada siswa dengan tunggakan</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@stop
