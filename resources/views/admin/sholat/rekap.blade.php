@extends('adminlte::page')

@section('title', 'Rekapitulasi Presensi Sholat')

@section('content_header')
<div class="row align-items-center">
    <div class="col-sm-7">
        <h1 class="m-0"><i class="fas fa-chart-line text-primary mr-2"></i>Rekapitulasi Presensi Sholat Siswa</h1>
    </div>
    <div class="col-sm-5">
        <ol class="breadcrumb float-sm-right mb-0">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.sholat.input') }}">Presensi Sholat</a></li>
            <li class="breadcrumb-item active">Rekapitulasi</li>
        </ol>
    </div>
</div>
@stop

@section('content')
<div class="container-fluid pb-4">
    <!-- Filter Card -->
    <div class="card card-default shadow-sm mb-3">
        <div class="card-body">
            <form action="{{ route('admin.sholat.rekap') }}" method="GET" class="form-row align-items-end">
                <div class="form-group col-md-3 mb-2">
                    <label class="font-weight-bold"><i class="fas fa-users mr-1 text-info"></i> Kelas / Rombel</label>
                    <select name="kelas_id" class="form-control select2" required>
                        @foreach($kelasList as $k)
                            <option value="{{ $k->id }}" {{ $selectedKelas && $selectedKelas->id === $k->id ? 'selected' : '' }}>
                                {{ $k->nama_kelas }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group col-md-3 mb-2">
                    <label class="font-weight-bold"><i class="far fa-calendar-alt mr-1 text-primary"></i> Bulan & Tahun</label>
                    <input type="month" name="bulan" class="form-control" value="{{ $bulan }}" required>
                </div>
                <div class="form-group col-md-3 mb-2">
                    <label class="font-weight-bold"><i class="fas fa-mosque mr-1 text-success"></i> Filter Sholat</label>
                    <select name="sholat_setting_id" class="form-control">
                        <option value="">Semua Sholat Aktif</option>
                        @foreach($activeSettings as $s)
                            <option value="{{ $s->id }}" {{ $selectedSettingId === $s->id ? 'selected' : '' }}>
                                {{ $s->nama_sholat }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group col-md-3 mb-2 d-flex" style="gap: .5rem;">
                    <button type="submit" class="btn btn-primary flex-fill">
                        <i class="fas fa-search mr-1"></i> Tampilkan
                    </button>
                    @if($selectedKelas)
                    <a href="{{ route('admin.sholat.export-excel', request()->all()) }}" class="btn btn-success" title="Download Format Excel">
                        <i class="fas fa-file-excel mr-1"></i> Export
                    </a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    @if($selectedKelas)
    <!-- Ringkasan Metrik Rekap -->
    <div class="row">
        <div class="col-md-3 col-sm-6 col-12">
            <div class="info-box shadow-sm">
                <span class="info-box-icon bg-success"><i class="fas fa-check-double"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Total Berjamaah</span>
                    <span class="info-box-number">{{ number_format($totals['berjamaah']) }}</span>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 col-12">
            <div class="info-box shadow-sm">
                <span class="info-box-icon bg-warning"><i class="fas fa-running text-white"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Masbuq / Munfarid</span>
                    <span class="info-box-number">{{ number_format($totals['masbuq'] + $totals['munfarid']) }}</span>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 col-12">
            <div class="info-box shadow-sm">
                <span class="info-box-icon bg-secondary"><i class="fas fa-female"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Udzur Syar'i</span>
                    <span class="info-box-number">{{ number_format($totals['udzur']) }}</span>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 col-12">
            <div class="info-box shadow-sm">
                <span class="info-box-icon bg-danger"><i class="fas fa-times-circle"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Alpa (Tanpa Keterangan)</span>
                    <span class="info-box-number">{{ number_format($totals['alpa']) }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabel Rekapitulasi Siswa -->
    <div class="card card-outline card-primary shadow-sm">
        <div class="card-header d-flex flex-wrap align-items-center justify-content-between py-2">
            <h3 class="card-title font-weight-bold">
                <i class="fas fa-table mr-1"></i> Rekapitulasi Kelas {{ $selectedKelas->nama_kelas }} &middot; Periode {{ $carbonBulan->translatedFormat('F Y') }}
            </h3>
            <span class="badge badge-light border font-weight-bold">Total: {{ count($matrix) }} Siswa</span>
        </div>
        <div class="card-body p-0 table-responsive">
            <table class="table table-hover table-striped mb-0 text-nowrap align-middle">
                <thead class="thead-light">
                    <tr>
                        <th style="width: 40px;" class="text-center">No</th>
                        <th>NISN / NIS</th>
                        <th>Nama Siswa</th>
                        <th class="text-center" style="width: 50px;">L/P</th>
                        <th class="text-center bg-light text-success font-weight-bold" title="Berjamaah">B</th>
                        <th class="text-center bg-light text-warning font-weight-bold" title="Masbuq">M</th>
                        <th class="text-center bg-light text-info font-weight-bold" title="Munfarid">MF</th>
                        <th class="text-center bg-light text-secondary font-weight-bold" title="Udzur Syar'i">U</th>
                        <th class="text-center bg-light text-primary font-weight-bold" title="Sakit">S</th>
                        <th class="text-center bg-light text-dark font-weight-bold" title="Izin">I</th>
                        <th class="text-center bg-light text-danger font-weight-bold" title="Alpa">A</th>
                        <th class="text-center font-weight-bold" style="width: 130px;">Kepatuhan (%)</th>
                        <th class="text-center" style="width: 120px;">Kategori</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($matrix as $idx => $row)
                    @php
                        $siswa = $row['siswa'];
                        $counts = $row['counts'];
                        $pct = $row['persentase'];
                        $alpaCount = $counts['alpa'];
                    @endphp
                    <tr>
                        <td class="text-center">{{ $idx + 1 }}</td>
                        <td><code>{{ $siswa->nisn ?? $siswa->nis_lokal ?? '-' }}</code></td>
                        <td><strong class="text-dark">{{ $siswa->nama_lengkap }}</strong></td>
                        <td class="text-center">
                            @if($siswa->jenis_kelamin === 'L')
                                <span class="badge badge-primary">L</span>
                            @else
                                <span class="badge badge-danger">P</span>
                            @endif
                        </td>
                        <td class="text-center font-weight-bold text-success">{{ $counts['berjamaah'] }}</td>
                        <td class="text-center font-weight-bold text-warning">{{ $counts['masbuq'] }}</td>
                        <td class="text-center font-weight-bold text-info">{{ $counts['munfarid'] }}</td>
                        <td class="text-center font-weight-bold text-secondary">{{ $counts['udzur'] }}</td>
                        <td class="text-center font-weight-bold text-primary">{{ $counts['sakit'] }}</td>
                        <td class="text-center font-weight-bold text-dark">{{ $counts['izin'] }}</td>
                        <td class="text-center font-weight-bold {{ $alpaCount > 0 ? 'text-danger' : 'text-muted' }}">
                            {{ $alpaCount }}
                        </td>
                        <td class="text-center">
                            <div class="progress progress-sm mb-1" style="height: 6px;">
                                <div class="progress-bar {{ $pct >= 85 ? 'bg-success' : ($pct >= 70 ? 'bg-warning' : 'bg-danger') }}" role="progressbar" style="width: {{ $pct }}%"></div>
                            </div>
                            <span class="small font-weight-bold {{ $pct >= 85 ? 'text-success' : ($pct >= 70 ? 'text-warning' : 'text-danger') }}">{{ $pct }}%</span>
                        </td>
                        <td class="text-center">
                            @if($alpaCount >= 3)
                                <span class="badge badge-danger" title="Alpa mencapai 3x atau lebih"><i class="fas fa-exclamation-triangle mr-1"></i>Perlu BK</span>
                            @elseif($pct >= 85)
                                <span class="badge badge-success"><i class="fas fa-star mr-1"></i>Sangat Baik</span>
                            @elseif($pct >= 70)
                                <span class="badge badge-warning text-dark"><i class="fas fa-check mr-1"></i>Cukup</span>
                            @else
                                <span class="badge badge-danger"><i class="fas fa-times mr-1"></i>Kurang</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="13" class="text-center py-4 text-muted">
                            Tidak ada data siswa untuk kelas ini.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer bg-light small text-muted">
            <span class="font-weight-bold">Keterangan Singkatan:</span> B = Berjamaah, M = Masbuq, MF = Munfarid, U = Udzur Syar'i (khusus putri), S = Sakit, I = Izin, A = Alpa. Kepatuhan dihitung dari total kehadiran aktif dibanding kewajiban sholat (mengecualikan hari udzur/sakit/izin).
        </div>
    </div>
    @endif
</div>
@stop

@section('js')
<script>
$(document).ready(function() {
    if ($.fn.select2) {
        $('.select2').select2({ theme: 'bootstrap4' });
    }
});
</script>
@stop
