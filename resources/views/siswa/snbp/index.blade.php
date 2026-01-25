@extends('adminlte::page')

@section('title', $snbpMenu->nama_menu)

@section('content_header')
    <h1><i class="fas fa-graduation-cap"></i> {{ $snbpMenu->nama_menu }}</h1>
@stop

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-10">
            @php
                $countdownData = $snbpMenu->getCountdownData();
            @endphp

            @if($countdownData)
            <!-- Countdown Section -->
            <div class="card card-outline card-info mb-3">
                <div class="card-body text-center py-3">
                    <p class="mb-2 text-muted">
                        <i class="fas fa-clock"></i> {{ $countdownData['message'] }}
                    </p>
                    @if($countdownData['target'])
                        <div id="countdown" class="d-flex justify-content-center" data-target="{{ $countdownData['target'] }}" data-type="{{ $countdownData['type'] }}">
                            <div class="countdown-item mx-2">
                                <div class="countdown-value display-4" id="days">--</div>
                                <div class="countdown-label text-muted small">Hari</div>
                            </div>
                            <div class="countdown-item mx-2">
                                <div class="countdown-value display-4" id="hours">--</div>
                                <div class="countdown-label text-muted small">Jam</div>
                            </div>
                            <div class="countdown-item mx-2">
                                <div class="countdown-value display-4" id="minutes">--</div>
                                <div class="countdown-label text-muted small">Menit</div>
                            </div>
                            <div class="countdown-item mx-2">
                                <div class="countdown-value display-4" id="seconds">--</div>
                                <div class="countdown-label text-muted small">Detik</div>
                            </div>
                        </div>
                        @if($countdownData['type'] === 'not_started')
                            <p class="text-info mt-2 mb-0"><small>Mulai: {{ $snbpMenu->tanggal_mulai->format('d M Y H:i') }}</small></p>
                        @else
                            <p class="text-warning mt-2 mb-0"><small>Berakhir: {{ $snbpMenu->tanggal_berakhir->format('d M Y H:i') }}</small></p>
                        @endif
                    @else
                        <div class="alert alert-warning mb-0">
                            <i class="fas fa-exclamation-triangle"></i> Periode informasi telah berakhir pada {{ $snbpMenu->tanggal_berakhir->format('d M Y H:i') }}
                        </div>
                    @endif
                </div>
            </div>
            @endif

            @if($countdownData && $countdownData['type'] === 'not_started')
            <!-- Content not yet available -->
            <div class="card card-secondary">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-hourglass-start"></i> Menunggu Waktu Tayang
                    </h3>
                </div>
                <div class="card-body text-center py-5">
                    <div class="mb-3">
                        <i class="fas fa-calendar-clock fa-5x text-secondary"></i>
                    </div>
                    <h4 class="text-secondary">Informasi Belum Tersedia</h4>
                    <p class="text-muted">
                        Informasi {{ $snbpMenu->nama_menu }} akan ditampilkan mulai<br>
                        <strong>{{ $snbpMenu->tanggal_mulai->format('d F Y, H:i') }} WIB</strong>
                    </p>
                </div>
            </div>
            @elseif($countdownData && $countdownData['type'] === 'ended')
            <!-- Period ended -->
            <div class="card card-secondary">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-calendar-times"></i> Periode Telah Berakhir
                    </h3>
                </div>
                <div class="card-body text-center py-5">
                    <div class="mb-3">
                        <i class="fas fa-calendar-xmark fa-5x text-secondary"></i>
                    </div>
                    <h4 class="text-secondary">Periode Informasi Telah Berakhir</h4>
                    <p class="text-muted">
                        Periode {{ $snbpMenu->nama_menu }} telah berakhir pada<br>
                        <strong>{{ $snbpMenu->tanggal_berakhir->format('d F Y, H:i') }} WIB</strong>
                    </p>
                </div>
            </div>
            @else
            <!-- Normal content display -->
            @if($status === true)
            <!-- Eligible Status -->
            <div class="card card-success">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-check-circle"></i> Selamat! Anda Eligible untuk {{ $snbpMenu->nama_menu }}
                    </h3>
                </div>
                <div class="card-body">
                    <div class="text-center mb-4">
                        <div class="mb-3">
                            <i class="fas fa-award fa-5x text-success"></i>
                        </div>
                        <h4 class="text-success">Status: <strong>ELIGIBLE</strong></h4>
                        <p class="text-muted">Tahun Pelajaran: {{ $snbpMenu->tahunPelajaran->nama ?? '-' }}</p>
                    </div>
                    
                    <hr>

                    <div class="content-area">
                        {!! $content !!}
                    </div>
                </div>
            </div>

            @elseif($status === false)
            <!-- Not Eligible Status -->
            <div class="card card-danger">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-times-circle"></i> {{ $snbpMenu->nama_menu }}
                    </h3>
                </div>
                <div class="card-body">
                    <div class="text-center mb-4">
                        <div class="mb-3">
                            <i class="fas fa-exclamation-triangle fa-5x text-danger"></i>
                        </div>
                        <h4 class="text-danger">Status: <strong>TIDAK ELIGIBLE</strong></h4>
                        <p class="text-muted">Tahun Pelajaran: {{ $snbpMenu->tahunPelajaran->nama ?? '-' }}</p>
                    </div>
                    
                    <hr>

                    <div class="content-area">
                        {!! $content !!}
                    </div>
                </div>
            </div>

            @else
            <!-- Status Not Yet Determined -->
            <div class="card card-warning">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-clock"></i> {{ $snbpMenu->nama_menu }}
                    </h3>
                </div>
                <div class="card-body">
                    <div class="text-center py-5">
                        <div class="mb-3">
                            <i class="fas fa-hourglass-half fa-5x text-warning"></i>
                        </div>
                        <h4 class="text-warning">Status Belum Ditentukan</h4>
                        <p class="text-muted">
                            Status eligibility Anda untuk {{ $snbpMenu->nama_menu }} belum ditentukan oleh pihak sekolah.<br>
                            Silakan hubungi Bimbingan Konseling atau Wali Kelas untuk informasi lebih lanjut.
                        </p>
                    </div>
                </div>
            </div>
            @endif

            <!-- Student Info Card -->
            <div class="card card-outline card-primary">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-user"></i> Data Siswa
                    </h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-sm table-borderless">
                                <tr>
                                    <td width="35%"><strong>Nama</strong></td>
                                    <td>: {{ $siswa->nama_lengkap }}</td>
                                </tr>
                                <tr>
                                    <td><strong>NISN</strong></td>
                                    <td>: {{ $siswa->nisn }}</td>
                                </tr>
                                <tr>
                                    <td><strong>NIS</strong></td>
                                    <td>: {{ $siswa->nis ?? '-' }}</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-sm table-borderless">
                                <tr>
                                    <td width="35%"><strong>Kelas</strong></td>
                                    <td>: {{ $siswa->kelasSaatIni->nama_kelas ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Jurusan</strong></td>
                                    <td>: {{ $siswa->kelasSaatIni->jurusan->nama ?? ($siswa->kelasSaatIni->jurusan->singkatan ?? '-') }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Tahun Pelajaran</strong></td>
                                    <td>: {{ $snbpMenu->tahunPelajaran->nama ?? '-' }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@stop

@section('css')
<style>
    .content-area {
        padding: 1rem;
        background: #f9f9f9;
        border-radius: 5px;
        min-height: 150px;
    }
    .content-area img {
        max-width: 100%;
        height: auto;
    }
    .countdown-item {
        text-align: center;
        min-width: 60px;
    }
    .countdown-value {
        font-weight: bold;
        line-height: 1;
    }
    .countdown-label {
        font-size: 0.75rem;
        text-transform: uppercase;
    }
</style>
@stop

@section('js')
<script>
$(document).ready(function() {
    var countdownEl = document.getElementById('countdown');
    if (countdownEl) {
        var targetDate = new Date(countdownEl.dataset.target);
        var countdownType = countdownEl.dataset.type;
        
        function updateCountdown() {
            var now = new Date();
            var diff = targetDate - now;
            
            if (diff <= 0) {
                // Reload page when countdown reaches zero
                location.reload();
                return;
            }
            
            var days = Math.floor(diff / (1000 * 60 * 60 * 24));
            var hours = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            var minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
            var seconds = Math.floor((diff % (1000 * 60)) / 1000);
            
            document.getElementById('days').textContent = days.toString().padStart(2, '0');
            document.getElementById('hours').textContent = hours.toString().padStart(2, '0');
            document.getElementById('minutes').textContent = minutes.toString().padStart(2, '0');
            document.getElementById('seconds').textContent = seconds.toString().padStart(2, '0');
        }
        
        updateCountdown();
        setInterval(updateCountdown, 1000);
    }
});
</script>
@stop
