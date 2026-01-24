@extends('adminlte::page')

@section('title', 'Dashboard Operator')

@section('content_header')
    <h1><i class="fas fa-tachometer-alt mr-2"></i>Dashboard Operator PPDB</h1>
@stop

@section('content')
<!-- Statistik Cards -->
<div class="row">
    <div class="col-lg-2 col-md-4 col-sm-6">
        <div class="small-box bg-info">
            <div class="inner">
                <h3>{{ $stats['total'] }}</h3>
                <p>Total Pendaftar</p>
            </div>
            <div class="icon"><i class="fas fa-users"></i></div>
            <a href="{{ route('operator.pendaftar.index') }}" class="small-box-footer">
                Lihat semua <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>
    <div class="col-lg-2 col-md-4 col-sm-6">
        <div class="small-box bg-warning">
            <div class="inner">
                <h3>{{ $stats['submitted'] }}</h3>
                <p>Menunggu</p>
            </div>
            <div class="icon"><i class="fas fa-clock"></i></div>
            <a href="{{ route('operator.pendaftar.index') }}?status=submitted" class="small-box-footer">
                Lihat <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>
    <div class="col-lg-2 col-md-4 col-sm-6">
        <div class="small-box bg-primary">
            <div class="inner">
                <h3>{{ $stats['verified'] }}</h3>
                <p>Terverifikasi</p>
            </div>
            <div class="icon"><i class="fas fa-check-circle"></i></div>
            <a href="{{ route('operator.pendaftar.index') }}?status=verified" class="small-box-footer">
                Lihat <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>
    <div class="col-lg-2 col-md-4 col-sm-6">
        <div class="small-box bg-success">
            <div class="inner">
                <h3>{{ $stats['accepted'] }}</h3>
                <p>Diterima</p>
            </div>
            <div class="icon"><i class="fas fa-user-check"></i></div>
            <a href="{{ route('operator.pendaftar.index') }}?status=accepted" class="small-box-footer">
                Lihat <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>
    <div class="col-lg-2 col-md-4 col-sm-6">
        <div class="small-box bg-danger">
            <div class="inner">
                <h3>{{ $stats['rejected'] }}</h3>
                <p>Ditolak</p>
            </div>
            <div class="icon"><i class="fas fa-user-times"></i></div>
            <a href="{{ route('operator.pendaftar.index') }}?status=rejected" class="small-box-footer">
                Lihat <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>
    <div class="col-lg-2 col-md-4 col-sm-6">
        <div class="small-box bg-secondary">
            <div class="inner">
                <h3>{{ $stats['draft'] }}</h3>
                <p>Draft</p>
            </div>
            <div class="icon"><i class="fas fa-edit"></i></div>
            <a href="{{ route('operator.pendaftar.index') }}?status=draft" class="small-box-footer">
                Lihat <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>
</div>

<div class="row">
    <!-- Status Pendaftaran -->
    <div class="col-lg-4">
        <div class="card {{ $pengaturan->isPendaftaranDibuka() ? 'card-success' : 'card-secondary' }}">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-info-circle"></i> Status Pendaftaran</h3>
            </div>
            <div class="card-body">
                <h4 class="text-center mb-3">
                    @if($pengaturan->isPendaftaranDibuka())
                        <span class="badge badge-success badge-lg p-3">PENDAFTARAN DIBUKA</span>
                    @else
                        <span class="badge badge-secondary badge-lg p-3">PENDAFTARAN DITUTUP</span>
                    @endif
                </h4>
                @if($pengaturan->id)
                    <table class="table table-sm table-borderless">
                        <tr>
                            <td>Periode</td>
                            <td class="text-right">{{ $pengaturan->periode_pendaftaran }}</td>
                        </tr>
                        <tr>
                            <td>Tahun Pelajaran</td>
                            <td class="text-right">{{ $pengaturan->tahunPelajaran->tahun_pelajaran ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td>Biaya</td>
                            <td class="text-right">{{ $pengaturan->formatted_biaya }}</td>
                        </tr>
                    </table>
                @else
                    <p class="text-center text-muted">Pengaturan PPDB belum dikonfigurasi</p>
                @endif
            </div>
        </div>
    </div>

    <!-- Kuota Jurusan -->
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-graduation-cap"></i> Kuota Jurusan</h3>
            </div>
            <div class="card-body p-0">
                <table class="table table-sm mb-0">
                    <thead>
                        <tr>
                            <th>Jurusan</th>
                            <th class="text-center" width="100">Kuota</th>
                            <th class="text-center" width="100">Terisi</th>
                            <th class="text-center" width="100">Sisa</th>
                            <th width="200">Progress</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($jurusan as $j)
                            @php
                                $percentage = $j->kuota > 0 ? round(($j->terisi / $j->kuota) * 100) : 0;
                                $progressClass = $percentage >= 90 ? 'bg-danger' : ($percentage >= 70 ? 'bg-warning' : 'bg-success');
                            @endphp
                            <tr>
                                <td>{{ $j->nama }}</td>
                                <td class="text-center">{{ $j->kuota }}</td>
                                <td class="text-center">{{ $j->terisi }}</td>
                                <td class="text-center">{{ $j->sisa }}</td>
                                <td>
                                    <div class="progress progress-sm">
                                        <div class="progress-bar {{ $progressClass }}" style="width: {{ $percentage }}%"></div>
                                    </div>
                                    <small class="text-muted">{{ $percentage }}%</small>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted">Belum ada data jurusan</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Menunggu Verifikasi -->
    <div class="col-lg-8">
        <div class="card card-warning">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-clock"></i> Menunggu Verifikasi</h3>
                <div class="card-tools">
                    <span class="badge badge-warning">{{ $stats['submitted'] }}</span>
                </div>
            </div>
            <div class="card-body p-0">
                <table class="table table-sm table-hover mb-0">
                    <thead>
                        <tr>
                            <th>No. Pendaftaran</th>
                            <th>Nama</th>
                            <th>Asal Sekolah</th>
                            <th>Tanggal</th>
                            <th width="80">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($pendingVerifikasi as $p)
                            <tr>
                                <td><code>{{ $p->nomor_pendaftaran }}</code></td>
                                <td>{{ $p->nama_lengkap }}</td>
                                <td>{{ $p->asal_sekolah ?? '-' }}</td>
                                <td>{{ $p->created_at->format('d/m/Y H:i') }}</td>
                                <td>
                                    <a href="{{ route('operator.pendaftar.show', $p) }}" class="btn btn-xs btn-info">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted p-3">
                                    <i class="fas fa-check-circle text-success fa-2x mb-2"></i><br>
                                    Tidak ada pendaftaran yang menunggu verifikasi
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($stats['submitted'] > 10)
                <div class="card-footer text-center">
                    <a href="{{ route('operator.pendaftar.index') }}?status=submitted" class="text-warning">
                        Lihat semua ({{ $stats['submitted'] }}) <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
            @endif
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-bolt"></i> Aksi Cepat</h3>
            </div>
            <div class="card-body">
                <a href="{{ route('operator.pendaftar.index') }}" class="btn btn-primary btn-block mb-2">
                    <i class="fas fa-users mr-2"></i>Kelola Semua Pendaftar
                </a>
                <a href="{{ route('operator.pendaftar.export') }}" class="btn btn-success btn-block mb-2">
                    <i class="fas fa-file-excel mr-2"></i>Export ke Excel
                </a>
                <a href="{{ route('ppdb.landing') }}" class="btn btn-outline-info btn-block" target="_blank">
                    <i class="fas fa-external-link-alt mr-2"></i>Lihat Halaman PPDB
                </a>
            </div>
        </div>

        <!-- Chart -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-chart-line"></i> Pendaftar 7 Hari Terakhir</h3>
            </div>
            <div class="card-body">
                <canvas id="chartPendaftar" height="200"></canvas>
            </div>
        </div>
    </div>
</div>
@stop

@section('css')
<style>
    .small-box .inner h3 {
        font-size: 2rem;
    }
    .badge-lg {
        font-size: 1rem;
    }
</style>
@stop

@section('js')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
$(function() {
    // Chart pendaftar 7 hari terakhir
    var chartData = @json($chartData);
    var labels = [];
    var data = [];
    
    // Generate last 7 days
    for (var i = 6; i >= 0; i--) {
        var date = new Date();
        date.setDate(date.getDate() - i);
        var dateStr = date.toISOString().split('T')[0];
        labels.push(date.toLocaleDateString('id-ID', { weekday: 'short', day: 'numeric' }));
        data.push(chartData[dateStr] || 0);
    }

    new Chart(document.getElementById('chartPendaftar'), {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: 'Pendaftar',
                data: data,
                borderColor: '#007bff',
                backgroundColor: 'rgba(0, 123, 255, 0.1)',
                fill: true,
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1
                    }
                }
            }
        }
    });
});
</script>
@stop
