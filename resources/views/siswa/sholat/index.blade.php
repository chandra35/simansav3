@extends('adminlte::page')

@section('title', 'Mutaba\'ah Ibadah - Presensi Sholat')

@section('content_header')
<div class="row align-items-center">
    <div class="col-sm-7">
        <h1 class="m-0"><i class="fas fa-mosque text-success mr-2"></i>Mutaba'ah Ibadah & Presensi Sholat</h1>
    </div>
    <div class="col-sm-5">
        <ol class="breadcrumb float-sm-right mb-0">
            <li class="breadcrumb-item"><a href="{{ route('siswa.dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item active">Presensi Sholat</li>
        </ol>
    </div>
</div>
@stop

@section('content')
<div class="container-fluid pb-4">
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle mr-1"></i> {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle mr-1"></i> {{ session('error') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    {{-- Banner Khusus Jika Siswa Ditugaskan Sebagai Seksi Kerohanian Kelas --}}
    @if($petugasKelas)
    <div class="alert alert-warning border shadow-sm mb-3">
        <div class="d-flex flex-wrap align-items-center justify-content-between">
            <div class="d-flex align-items-center">
                <i class="fas fa-user-shield fa-2x mr-3 text-warning"></i>
                <div>
                    <h5 class="font-weight-bold mb-1">Amanah Petugas Seksi Kerohanian</h5>
                    <p class="mb-0">
                        Anda ditugaskan sebagai petugas presensi ibadah sholat untuk <strong>Kelas {{ $petugasKelas->kelas->nama_kelas ?? '' }}</strong>.
                    </p>
                </div>
            </div>
            <div class="mt-2 mt-md-0">
                <a href="{{ route('admin.sholat.input', ['kelas_id' => $petugasKelas->kelas_id]) }}" class="btn btn-dark font-weight-bold shadow-sm">
                    <i class="fas fa-clipboard-check mr-1 text-warning"></i> Buka Lembar Input Presensi Sholat Kelas
                </a>
            </div>
        </div>
    </div>
    @endif

    <!-- Profile & Hero Card -->
    <div class="card bg-gradient-success text-white shadow-sm mb-3">
        <div class="card-body py-4">
            <div class="row align-items-center">
                <div class="col-lg-8">
                    <h3 class="font-weight-bold mb-1">Assalamu'alaikum, {{ $siswa->nama_lengkap }}!</h3>
                    <p class="mb-0 text-white-50">
                        Pantau ketertiban dan istiqomah ibadah sholat berjamaah harian Anda di madrasah.
                    </p>
                </div>
                <div class="col-lg-4 text-lg-right mt-3 mt-lg-0">
                    <div class="d-inline-block bg-white text-dark rounded px-3 py-2 shadow-sm text-center">
                        <div class="small font-weight-bold text-muted">Kepatuhan Sholat Bulan Ini</div>
                        <div class="h3 font-weight-bold mb-0 text-success">{{ $persentase }}%</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Bulan & Ringkasan -->
    <div class="card card-default shadow-sm mb-3">
        <div class="card-body py-3">
            <form action="{{ route('siswa.sholat.index') }}" method="GET" class="form-row align-items-center">
                <div class="col-auto">
                    <label class="font-weight-bold mb-0 mr-2"><i class="far fa-calendar-alt text-primary mr-1"></i> Pilih Bulan:</label>
                </div>
                <div class="col-auto">
                    <input type="month" name="bulan" class="form-control form-control-sm" value="{{ $bulan }}" onchange="this.form.submit()">
                </div>
            </form>
        </div>
    </div>

    <!-- Metric Stat Boxes -->
    <div class="row">
        <div class="col-md-2 col-4">
            <div class="info-box shadow-sm text-center p-2">
                <div class="info-box-content">
                    <span class="info-box-text text-success font-weight-bold">Berjamaah</span>
                    <span class="info-box-number h4 mb-0 text-success">{{ $summary['berjamaah'] }}</span>
                </div>
            </div>
        </div>
        <div class="col-md-2 col-4">
            <div class="info-box shadow-sm text-center p-2">
                <div class="info-box-content">
                    <span class="info-box-text text-warning font-weight-bold">Masbuq</span>
                    <span class="info-box-number h4 mb-0 text-warning">{{ $summary['masbuq'] }}</span>
                </div>
            </div>
        </div>
        <div class="col-md-2 col-4">
            <div class="info-box shadow-sm text-center p-2">
                <div class="info-box-content">
                    <span class="info-box-text text-info font-weight-bold">Munfarid</span>
                    <span class="info-box-number h4 mb-0 text-info">{{ $summary['munfarid'] }}</span>
                </div>
            </div>
        </div>
        <div class="col-md-2 col-4">
            <div class="info-box shadow-sm text-center p-2">
                <div class="info-box-content">
                    <span class="info-box-text text-secondary font-weight-bold">Udzur</span>
                    <span class="info-box-number h4 mb-0 text-secondary">{{ $summary['udzur'] }}</span>
                </div>
            </div>
        </div>
        <div class="col-md-2 col-4">
            <div class="info-box shadow-sm text-center p-2">
                <div class="info-box-content">
                    <span class="info-box-text text-primary font-weight-bold">Sakit/Izin</span>
                    <span class="info-box-number h4 mb-0 text-primary">{{ $summary['sakit'] + $summary['izin'] }}</span>
                </div>
            </div>
        </div>
        <div class="col-md-2 col-4">
            <div class="info-box shadow-sm text-center p-2">
                <div class="info-box-content">
                    <span class="info-box-text text-danger font-weight-bold">Alpa</span>
                    <span class="info-box-number h4 mb-0 text-danger">{{ $summary['alpa'] }}</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Fitur Udzur Syar'i (Khusus Siswi Putri) --}}
    @if($siswa->jenis_kelamin === 'P')
    <div class="row">
        <div class="col-lg-5">
            <div class="card card-pink card-outline shadow-sm mb-4">
                <div class="card-header">
                    <h3 class="card-title font-weight-bold">
                        <i class="fas fa-female mr-1 text-danger"></i> Formulir Laporan Udzur Syar'i (Haid)
                    </h3>
                </div>
                <div class="card-body">
                    @if($activeUdzurToday)
                        <div class="alert alert-info py-2 small mb-3">
                            <i class="fas fa-info-circle mr-1"></i>
                            Hari ini Anda tercatat dalam periode <strong>Udzur Syar'i</strong> (sampai {{ \Carbon\Carbon::parse($activeUdzurToday->tanggal_selesai)->translatedFormat('d F Y') }}).
                        </div>
                    @endif
                    <p class="text-muted small">
                        Silakan input tanggal mulai dan estimasi selesai masa berhalangan sholat (maksimal 15 hari sesuai fiqih). Petugas kelas otomatis melihat status Anda sebagai <strong>Udzur</strong> saat presensi.
                    </p>
                    <form action="{{ route('siswa.sholat.udzur.store') }}" method="POST">
                        @csrf
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label>Tanggal Mulai</label>
                                <input type="date" name="tanggal_mulai" class="form-control" value="{{ \Carbon\Carbon::today()->toDateString() }}" required>
                            </div>
                            <div class="form-group col-md-6">
                                <label>Tanggal Selesai (Estimasi)</label>
                                <input type="date" name="tanggal_selesai" class="form-control" value="{{ \Carbon\Carbon::today()->addDays(6)->toDateString() }}" required>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Keterangan Tambahan (Opsional)</label>
                            <input type="text" name="keterangan" class="form-control" placeholder="Contoh: Haid hari ke-1">
                        </div>
                        <button type="submit" class="btn btn-danger btn-block font-weight-bold">
                            <i class="fas fa-heart mr-1"></i> Simpan Laporan Udzur Syar'i
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-7">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-light">
                    <h3 class="card-title font-weight-bold">
                        <i class="fas fa-history mr-1"></i> Riwayat Udzur Syar'i Terbaru
                    </h3>
                </div>
                <div class="card-body p-0 table-responsive">
                    <table class="table table-sm table-striped mb-0">
                        <thead class="thead-light">
                            <tr>
                                <th>Periode Tanggal</th>
                                <th>Durasi</th>
                                <th>Keterangan</th>
                                <th class="text-center" style="width: 70px;">Batal</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($udzurList as $u)
                            <tr>
                                <td>
                                    <strong>{{ \Carbon\Carbon::parse($u->tanggal_mulai)->translatedFormat('d M Y') }}</strong>
                                    s/d
                                    <strong>{{ \Carbon\Carbon::parse($u->tanggal_selesai)->translatedFormat('d M Y') }}</strong>
                                </td>
                                <td>
                                    <span class="badge badge-light border">
                                        {{ \Carbon\Carbon::parse($u->tanggal_mulai)->diffInDays(\Carbon\Carbon::parse($u->tanggal_selesai)) + 1 }} Hari
                                    </span>
                                </td>
                                <td>{{ $u->keterangan ?? '-' }}</td>
                                <td class="text-center">
                                    <form action="{{ route('siswa.sholat.udzur.destroy', $u->id) }}" method="POST" onsubmit="return confirm('Batalkan laporan udzur ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-xs btn-outline-danger" title="Batalkan jika suci lebih awal">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="text-center py-3 text-muted">Belum ada riwayat udzur syar'i.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Riwayat Presensi Sholat Bulan Terpilih -->
    <div class="card card-outline card-success shadow-sm">
        <div class="card-header">
            <h3 class="card-title font-weight-bold">
                <i class="fas fa-list-alt mr-1"></i> Catatan Presensi Sholat &middot; Periode {{ $carbonBulan->translatedFormat('F Y') }}
            </h3>
        </div>
        <div class="card-body p-0 table-responsive">
            <table class="table table-hover table-striped mb-0 text-nowrap align-middle">
                <thead class="thead-light">
                    <tr>
                        <th style="width: 40px;" class="text-center">No</th>
                        <th>Hari & Tanggal</th>
                        <th>Sholat</th>
                        <th class="text-center" style="width: 140px;">Status Kehadiran</th>
                        <th>Waktu & Catatan</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($records as $idx => $r)
                    <tr>
                        <td class="text-center">{{ $idx + 1 }}</td>
                        <td>
                            <strong>{{ \Carbon\Carbon::parse($r->session->tanggal)->translatedFormat('l, d F Y') }}</strong>
                        </td>
                        <td>
                            <span class="font-weight-bold text-dark">{{ $r->session->sholatSetting->nama_sholat ?? '-' }}</span>
                        </td>
                        <td class="text-center">
                            @php
                                $badgeClass = [
                                    'berjamaah' => 'badge-success',
                                    'masbuq' => 'badge-warning text-dark',
                                    'munfarid' => 'badge-info',
                                    'udzur' => 'badge-secondary',
                                    'sakit' => 'badge-primary',
                                    'izin' => 'badge-dark',
                                    'alpa' => 'badge-danger',
                                ][$r->status] ?? 'badge-secondary';
                            @endphp
                            <span class="badge {{ $badgeClass }} px-2 py-1">
                                {{ $statusLabels[$r->status] ?? ucfirst($r->status) }}
                            </span>
                        </td>
                        <td>
                            <small class="text-muted">
                                @if($r->waktu_hadir)
                                    <i class="far fa-clock mr-1"></i>{{ substr($r->waktu_hadir, 0, 5) }} WIB &bull;
                                @endif
                                {{ $r->catatan ?: '-' }}
                            </small>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center py-4 text-muted">
                            Belum ada rekaman presensi sholat pada bulan ini.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@stop
