@extends('adminlte::page')

@section('title', 'Dashboard PPDB')

@section('content_header')
    <h1>Dashboard PPDB</h1>
@stop

@section('content')
    <!-- Stats Cards -->
    <div class="row">
        <div class="col-lg-3 col-6">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>{{ $stats['total_pendaftar'] }}</h3>
                    <p>Total Pendaftar</p>
                </div>
                <div class="icon">
                    <i class="fas fa-users"></i>
                </div>
                <a href="#" class="small-box-footer">Lihat Detail <i class="fas fa-arrow-circle-right"></i></a>
            </div>
        </div>

        <div class="col-lg-3 col-6">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3>{{ $stats['pending'] }}</h3>
                    <p>Menunggu Verifikasi</p>
                </div>
                <div class="icon">
                    <i class="fas fa-clock"></i>
                </div>
                <a href="#" class="small-box-footer">Lihat Detail <i class="fas fa-arrow-circle-right"></i></a>
            </div>
        </div>

        <div class="col-lg-3 col-6">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>{{ $stats['verified'] }}</h3>
                    <p>Terverifikasi</p>
                </div>
                <div class="icon">
                    <i class="fas fa-check-circle"></i>
                </div>
                <a href="#" class="small-box-footer">Lihat Detail <i class="fas fa-arrow-circle-right"></i></a>
            </div>
        </div>

        <div class="col-lg-3 col-6">
            <div class="small-box bg-danger">
                <div class="inner">
                    <h3>{{ $stats['rejected'] }}</h3>
                    <p>Ditolak</p>
                </div>
                <div class="icon">
                    <i class="fas fa-times-circle"></i>
                </div>
                <a href="#" class="small-box-footer">Lihat Detail <i class="fas fa-arrow-circle-right"></i></a>
            </div>
        </div>
    </div>

    <!-- Quick Access -->
    <div class="row">
        <div class="col-md-4">
            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title">📸 Slider Management</h3>
                </div>
                <div class="card-body">
                    <p>Kelola slider foto untuk landing page PPDB</p>
                    <strong>Total Slider: {{ $stats['total_slider'] }}</strong>
                </div>
                <div class="card-footer">
                    <a href="{{ route('admin.settings.slider.index') }}" class="btn btn-primary btn-block">
                        <i class="fas fa-images"></i> Kelola Slider
                    </a>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card card-success">
                <div class="card-header">
                    <h3 class="card-title">📰 Berita & Informasi</h3>
                </div>
                <div class="card-body">
                    <p>Kelola berita dan informasi PPDB</p>
                    <strong>Total Berita: {{ $stats['total_berita'] }}</strong>
                </div>
                <div class="card-footer">
                    <a href="{{ route('admin.settings.berita.index') }}" class="btn btn-success btn-block">
                        <i class="fas fa-newspaper"></i> Kelola Berita
                    </a>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card card-warning">
                <div class="card-header">
                    <h3 class="card-title">📄 Dokumen Pending</h3>
                </div>
                <div class="card-body">
                    <p>Dokumen yang menunggu verifikasi</p>
                    <strong>Total: {{ $stats['dokumen_pending'] }}</strong>
                </div>
                <div class="card-footer">
                    <a href="#" class="btn btn-warning btn-block">
                        <i class="fas fa-file-alt"></i> Verifikasi Dokumen
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Pendaftar -->
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">📋 Pendaftar Terbaru</h3>
                </div>
                <div class="card-body">
                    <table class="table table-bordered table-hover">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Nomor Registrasi</th>
                                <th>Nama</th>
                                <th>NISN</th>
                                <th>Email</th>
                                <th>Status</th>
                                <th>Tanggal Daftar</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentPendaftar as $index => $pendaftar)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td><strong>{{ $pendaftar->nomor_registrasi }}</strong></td>
                                    <td>{{ $pendaftar->nama_lengkap }}</td>
                                    <td>{{ $pendaftar->nisn }}</td>
                                    <td>{{ $pendaftar->user->email ?? '-' }}</td>
                                    <td>
                                        @if($pendaftar->status_verifikasi === 'pending')
                                            <span class="badge badge-warning">Pending</span>
                                        @elseif($pendaftar->status_verifikasi === 'verified')
                                            <span class="badge badge-success">Verified</span>
                                        @elseif($pendaftar->status_verifikasi === 'rejected')
                                            <span class="badge badge-danger">Rejected</span>
                                        @endif
                                    </td>
                                    <td>{{ $pendaftar->created_at->format('d M Y H:i') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted">Belum ada pendaftar</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Chart Pendaftar per Bulan -->
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">📊 Statistik Pendaftar {{ date('Y') }}</h3>
                </div>
                <div class="card-body">
                    <canvas id="chartPendaftar" height="80"></canvas>
                </div>
            </div>
        </div>
    </div>
@stop

@section('js')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Chart Pendaftar per Bulan
    const bulanNames = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Oct', 'Nov', 'Des'];
    const chartData = @json($pendaftarPerBulan);
    
    // Prepare data untuk 12 bulan
    const dataPerBulan = Array(12).fill(0);
    chartData.forEach(item => {
        dataPerBulan[item.bulan - 1] = item.jumlah;
    });
    
    const ctx = document.getElementById('chartPendaftar').getContext('2d');
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: bulanNames,
            datasets: [{
                label: 'Jumlah Pendaftar',
                data: dataPerBulan,
                backgroundColor: 'rgba(54, 162, 235, 0.6)',
                borderColor: 'rgba(54, 162, 235, 1)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
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
</script>
@stop
