@extends('adminlte::page')

@section('title', 'Kartu Akun TKA - SIMANSA')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="m-0 font-weight-bold text-dark">
                <i class="fas fa-id-card text-primary mr-2"></i>Kartu Login Tes Kemampuan Akademik (TKA)
            </h1>
            <small class="text-muted">Informasi Akun dan Jadwal Ujian TKA Anda</small>
        </div>
        @if($tka)
        <a href="{{ route('siswa.tka.cetak') }}" target="_blank" class="btn btn-primary shadow-sm">
            <i class="fas fa-print mr-1"></i> Cetak Kartu Peserta
        </a>
        @endif
    </div>
@stop

@section('content')
@if(session('error'))
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    <i class="fas fa-exclamation-circle mr-1"></i> {{ session('error') }}
    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
        <span aria-hidden="true">&times;</span>
    </button>
</div>
@endif

@if(!$tka)
<div class="card shadow-sm border-0">
    <div class="card-body text-center py-5">
        <div class="text-muted mb-3" style="font-size: 3.5rem;">
            <i class="fas fa-id-card-alt text-secondary"></i>
        </div>
        <h4 class="font-weight-bold text-dark">Akun TKA Belum Tersedia</h4>
        <p class="text-muted mx-auto" style="max-width: 500px;">
            Data kartu login Tes Kemampuan Akademik (TKA) untuk NISN <strong>{{ $siswa->nisn }}</strong> belum diunggah atau belum terdaftar dalam sistem. Silakan konfirmasi ke panitia ujian / proktor madrasah.
        </p>
    </div>
</div>
@else
<div class="row">
    <!-- Kartu Digital Siswa -->
    <div class="col-lg-7 mb-4">
        <div class="card shadow-sm border-primary" style="border-top: 4px solid #007bff; border-radius: 8px;">
            <div class="card-header bg-white pb-2 pt-3 border-bottom">
                <div class="text-center">
                    <span class="badge badge-primary px-3 py-1 mb-1 font-weight-normal" style="font-size: 0.85rem; letter-spacing: 1px;">KARTU LOGIN</span>
                    <h5 class="font-weight-bold text-dark mb-0">TES KEMAMPUAN AKADEMIK SMA/MA</h5>
                    <div class="font-weight-bold text-primary">TAHUN {{ $tka->tahun }}</div>
                </div>
            </div>
            <div class="card-body p-4">
                <!-- Info Siswa -->
                <table class="table table-sm table-borderless mb-4">
                    <tbody>
                        <tr>
                            <td class="text-muted" style="width: 32%;">Nama Peserta</td>
                            <td style="width: 3%;">:</td>
                            <td class="font-weight-bold text-dark">{{ $tka->nama_peserta }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">NISN</td>
                            <td>:</td>
                            <td class="font-weight-bold text-monospace">{{ $tka->nisn }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Tempat, Tanggal Lahir</td>
                            <td>:</td>
                            <td>{{ $tka->tempat_tanggal_lahir ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Kelas di SIMANSA</td>
                            <td>:</td>
                            <td><span class="badge badge-info">{{ $kelas->nama_kelas ?? 'Kelas 12' }}</span></td>
                        </tr>
                    </tbody>
                </table>

                <!-- Box Akun Login (Highlight) -->
                <div class="rounded p-3 mb-4" style="background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%); border: 1px solid #ced4da;">
                    <div class="row align-items-center">
                        <div class="col-md-6 mb-2 mb-md-0">
                            <label class="text-muted small text-uppercase mb-1 font-weight-bold d-block">Username / ID Peserta</label>
                            <div class="d-flex align-items-center">
                                <span class="font-weight-bold text-primary text-monospace mr-2" style="font-size: 1.25rem;" id="txtUsername">{{ $tka->username }}</span>
                                <button type="button" class="btn btn-outline-primary btn-sm py-0 px-2 btn-copy" data-target="txtUsername" title="Salin Username">
                                    <i class="far fa-copy"></i>
                                </button>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small text-uppercase mb-1 font-weight-bold d-block">Password TKA</label>
                            <div class="d-flex align-items-center">
                                <span class="font-weight-bold text-danger text-monospace mr-2" style="font-size: 1.25rem;" id="txtPassword">{{ $tka->password }}</span>
                                <button type="button" class="btn btn-outline-danger btn-sm py-0 px-2 btn-copy" data-target="txtPassword" title="Salin Password">
                                    <i class="far fa-copy"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Lokasi & Sesi -->
                <div class="row mb-4">
                    <div class="col-md-4 mb-2 mb-md-0">
                        <div class="border rounded p-2 text-center bg-light">
                            <span class="text-muted small d-block">Ruang Ujian</span>
                            <strong class="text-dark">{{ $tka->ruang ?: '-' }}</strong>
                            @if($tka->id_proktor)
                            <div class="small text-muted font-italic">{{ $tka->id_proktor }}</div>
                            @endif
                        </div>
                    </div>
                    <div class="col-md-4 mb-2 mb-md-0">
                        <div class="border rounded p-2 text-center bg-light">
                            <span class="text-muted small d-block">Gelombang</span>
                            <strong class="text-dark">{{ $tka->gelombang ?: '-' }}</strong>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="border rounded p-2 text-center bg-light">
                            <span class="text-muted small d-block">Sesi Pelaksanaan</span>
                            <strong class="text-success">{{ $tka->sesi ?: '-' }}</strong>
                        </div>
                    </div>
                </div>

                <!-- Tabel Jadwal -->
                <h6 class="font-weight-bold text-dark mb-2">
                    <i class="far fa-calendar-alt text-primary mr-1"></i> Jadwal Ujian
                </h6>
                <div class="table-responsive">
                    <table class="table table-bordered table-sm text-center mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th style="width: 25%;">Tanggal</th>
                                <th class="text-left">Mata Pelajaran</th>
                                <th style="width: 20%;">Sesi</th>
                                <th style="width: 25%;">Waktu</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if(!empty($tka->jadwal))
                                @foreach($tka->jadwal as $j)
                                <tr>
                                    <td class="font-weight-bold text-muted align-middle">{{ $j['tanggal'] ?? '-' }}</td>
                                    <td class="text-left align-middle">
                                        @if(isset($j['mapel']) && is_array($j['mapel']))
                                            @foreach($j['mapel'] as $m)
                                                <div>• {{ $m }}</div>
                                            @endforeach
                                        @else
                                            {{ $j['mapel'] ?? '-' }}
                                        @endif
                                    </td>
                                    <td class="align-middle"><span class="badge badge-success">{{ $j['sesi'] ?? $tka->sesi }}</span></td>
                                    <td class="text-monospace text-primary font-weight-bold align-middle">{{ $j['waktu'] ?? '-' }}</td>
                                </tr>
                                @endforeach
                            @else
                                <tr>
                                    <td colspan="4" class="text-muted py-2">Jadwal belum tercatat.</td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer bg-light d-flex justify-content-between align-items-center">
                <small class="text-muted">Simpan dan catat username serta password untuk hari pelaksanaan.</small>
                <a href="{{ route('siswa.tka.cetak') }}" target="_blank" class="btn btn-primary btn-sm">
                    <i class="fas fa-print mr-1"></i> Cetak Kartu
                </a>
            </div>
        </div>
    </div>

    <!-- Informasi / Petunjuk Ujian -->
    <div class="col-lg-5 mb-4">
        <div class="card shadow-sm border-0 mb-3">
            <div class="card-header bg-info text-white">
                <h6 class="card-title font-weight-bold mb-0">
                    <i class="fas fa-info-circle mr-1"></i> Petunjuk Pelaksanaan TKA
                </h6>
            </div>
            <div class="card-body">
                <ol class="pl-3 mb-0" style="line-height: 1.8;">
                    <li>Peserta wajib membawa <strong>Kartu Login TKA</strong> yang telah dicetak pada setiap sesi ujian.</li>
                    <li>Hadir di ruang ujian <strong>15 menit</strong> sebelum waktu sesi dimulai.</li>
                    <li>Gunakan <strong>Username</strong> dan <strong>Password</strong> yang tertera pada kartu untuk masuk ke aplikasi ujian.</li>
                    <li>Pastikan Anda hadir sesuai dengan <strong>Ruang</strong>, <strong>Gelombang</strong>, dan <strong>Sesi</strong> yang telah ditentukan.</li>
                    <li>Jika mengalami kendala login saat ujian, segera laporkan kepada <strong>Proktor / Pengawas Ruang</strong>.</li>
                </ol>
            </div>
        </div>

        <div class="card shadow-sm border-0">
            <div class="card-body bg-light rounded text-center p-4">
                <i class="fas fa-qrcode text-secondary mb-2" style="font-size: 2.5rem;"></i>
                <h6 class="font-weight-bold text-dark">Akses Cepat di Ponsel</h6>
                <p class="text-muted small mb-0">
                    Anda dapat membuka halaman ini melalui browser HP saat hari ujian untuk melihat ulang username dan password Anda jika kartu cetak tertinggal.
                </p>
            </div>
        </div>
    </div>
</div>
@endif
@stop

@section('js')
<script>
$(document).ready(function() {
    $('.btn-copy').on('click', function() {
        var targetId = $(this).data('target');
        var text = $('#' + targetId).text().trim();
        var $btn = $(this);
        
        navigator.clipboard.writeText(text).then(function() {
            var originalHtml = $btn.html();
            $btn.html('<i class="fas fa-check text-success"></i>');
            setTimeout(function() {
                $btn.html(originalHtml);
            }, 1500);
        }).catch(function(err) {
            alert('Gagal menyalin text: ' + text);
        });
    });
});
</script>
@stop
