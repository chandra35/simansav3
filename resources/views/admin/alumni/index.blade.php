@extends('adminlte::page')

@section('title', 'Bank Data Alumni')
@section('plugins.Chartjs', true)

@section('content_header')
    <div class="d-flex flex-wrap align-items-center justify-content-between">
        <div>
            <h1 class="mb-1"><i class="fas fa-user-graduate text-primary mr-2"></i>Bank Data Alumni</h1>
            <p class="text-muted mb-0">Profil alumni mandiri, terhubung ke data siswa bila riwayat digital tersedia.</p>
        </div>
        @can('edit-siswa')
            <button class="btn btn-primary" data-toggle="modal" data-target="#addAlumni">
                <i class="fas fa-plus mr-1"></i>Tambah Arsip Alumni
            </button>
        @endcan
    </div>
@stop

@section('content')
    @php
        $summaryCards = [
            ['label' => 'Total Profil', 'value' => $stats['total'], 'icon' => 'users', 'color' => 'primary'],
            ['label' => 'Terverifikasi', 'value' => $stats['terverifikasi'], 'icon' => 'badge-check', 'color' => 'success'],
            ['label' => 'Kontak Tersedia', 'value' => $stats['kontak'], 'icon' => 'phone', 'color' => 'info'],
            ['label' => 'Arsip Historis', 'value' => $stats['historis'], 'icon' => 'archive', 'color' => 'warning'],
        ];
        $statusOptions = [
            'kuliah' => 'Kuliah',
            'bekerja' => 'Bekerja',
            'wirausaha' => 'Wirausaha',
            'pesantren' => 'Pesantren',
            'belum_terdata' => 'Belum terdata',
        ];
    @endphp

    <div class="alumni-bank">
        <div class="alumni-summary">
            @foreach($summaryCards as $card)
                <div class="alumni-summary-card">
                    <span class="bg-{{ $card['color'] }}"><i class="fas fa-{{ $card['icon'] }}"></i></span>
                    <div>
                        <small>{{ $card['label'] }}</small>
                        <strong>{{ number_format($card['value']) }}</strong>
                    </div>
                </div>
            @endforeach
        </div>

        <section class="card card-outline card-primary shadow-sm">
            <div class="card-header border-0">
                <h3 class="card-title font-weight-bold"><i class="fas fa-chart-bar mr-2"></i>Per Angkatan</h3>
            </div>
            <div class="card-body"><div class="alumni-chart"><canvas id="alumniYearChart"></canvas></div></div>
        </section>

        <section class="card shadow-sm">
            <div class="card-header border-0">
                <h3 class="card-title font-weight-bold"><i class="fas fa-database mr-2 text-primary"></i>Direktori Alumni</h3>
            </div>
            <div class="card-body">
                <form class="alumni-filter" method="get">
                    <div class="form-row">
                        <div class="col-lg-4 mb-2">
                            <label for="alumniSearch">Cari</label>
                            <input id="alumniSearch" class="form-control" name="q" value="{{ request('q') }}" placeholder="Nama, NISN, atau NIK">
                        </div>
                        <div class="col-lg-2 mb-2">
                            <label for="alumniAngkatan">Angkatan</label>
                            <select id="alumniAngkatan" class="form-control" name="angkatan">
                                <option value="">Semua</option>
                                @foreach($angkatanList as $angkatan)
                                    <option value="{{ $angkatan }}" {{ request('angkatan') === $angkatan ? 'selected' : '' }}>{{ $angkatan }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-lg-2 mb-2">
                            <label for="alumniStatus">Status</label>
                            <select id="alumniStatus" class="form-control" name="status_setelah_lulus">
                                <option value="">Semua</option>
                                @foreach($statusOptions as $key => $label)
                                    <option value="{{ $key }}" {{ request('status_setelah_lulus') === $key ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-lg-2 mb-2">
                            <label for="alumniVerification">Verifikasi</label>
                            <select id="alumniVerification" class="form-control" name="status_verifikasi">
                                <option value="">Semua</option>
                                <option value="terverifikasi" {{ request('status_verifikasi') === 'terverifikasi' ? 'selected' : '' }}>Terverifikasi</option>
                                <option value="belum_diverifikasi" {{ request('status_verifikasi') === 'belum_diverifikasi' ? 'selected' : '' }}>Belum</option>
                                <option value="perlu_tinjau" {{ request('status_verifikasi') === 'perlu_tinjau' ? 'selected' : '' }}>Perlu tinjau</option>
                            </select>
                        </div>
                        <div class="col-lg-2 mb-2 d-flex align-items-end">
                            <button class="btn btn-primary mr-2" type="submit" title="Terapkan filter"><i class="fas fa-filter"></i></button>
                            <a class="btn btn-outline-secondary" href="{{ route('admin.alumni.index') }}" title="Reset filter"><i class="fas fa-redo"></i></a>
                        </div>
                    </div>
                </form>

                <div class="table-responsive">
                    <table class="table table-hover alumni-table">
                        <thead><tr><th>Alumni</th><th>Angkatan</th><th>Kontak</th><th>Aktivitas</th><th>Verifikasi</th><th class="text-right">Aksi</th></tr></thead>
                        <tbody>
                            @forelse($alumni as $item)
                                @php
                                    $verificationColor = $item->status_verifikasi === 'terverifikasi'
                                        ? 'success'
                                        : ($item->status_verifikasi === 'perlu_tinjau' ? 'warning' : 'secondary');
                                @endphp
                                <tr>
                                    <td>
                                        <strong>{{ $item->nama_lengkap }}</strong>
                                        <small class="d-block text-muted">
                                            NISN {{ $item->nisn ?: '-' }}
                                            @if($item->siswa)
                                                &middot; Terhubung SIMANSA
                                            @endif
                                        </small>
                                    </td>
                                    <td><span class="badge badge-primary">{{ $item->angkatan ?: ($item->tahun_lulus ?: '-') }}</span></td>
                                    <td>{{ $item->nomor_hp ?: '-' }}<small class="d-block text-muted">{{ $item->kabupaten_kota ?: $item->provinsi ?: '' }}</small></td>
                                    <td><strong>{{ $item->status_label }}</strong><small class="d-block text-muted">{{ $item->institusi_lanjutan ?: $item->instansi ?: $item->pekerjaan ?: '-' }}</small></td>
                                    <td><span class="badge badge-{{ $verificationColor }}">{{ str_replace('_', ' ', $item->status_verifikasi) }}</span></td>
                                    <td class="text-right"><a class="btn btn-sm btn-outline-primary" href="{{ route('admin.alumni.show', $item) }}" title="Buka profil"><i class="fas fa-arrow-right"></i></a></td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="text-center text-muted py-5">Belum ada profil alumni. Jalankan sinkronisasi lulusan atau tambahkan arsip historis.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="mt-3">{{ $alumni->links('pagination::bootstrap-4') }}</div>
            </div>
        </section>
    </div>

    @can('edit-siswa')
        <div class="modal fade" id="addAlumni" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <form class="modal-content" method="post" action="{{ route('admin.alumni.store') }}">
                    @csrf
                    <div class="modal-header"><h5 class="mb-0">Tambah Arsip Alumni Historis</h5><button type="button" class="close" data-dismiss="modal" aria-label="Tutup"><span aria-hidden="true">&times;</span></button></div>
                    <div class="modal-body">
                        <div class="form-row">
                            <div class="form-group col-md-7"><label>Nama lengkap</label><input required name="nama_lengkap" class="form-control"></div>
                            <div class="form-group col-md-3"><label>Angkatan</label><input name="angkatan" class="form-control" placeholder="2017/2018"></div>
                            <div class="form-group col-md-2"><label>Tahun lulus</label><input name="tahun_lulus" type="number" class="form-control" placeholder="2018"></div>
                            <div class="form-group col-md-4"><label>NISN</label><input name="nisn" class="form-control"></div>
                            <div class="form-group col-md-4"><label>Nomor HP</label><input name="nomor_hp" class="form-control"></div>
                            <div class="form-group col-md-4"><label>Status</label><select name="status_setelah_lulus" class="form-control">@foreach($statusOptions as $key => $label)<option value="{{ $key }}">{{ $label }}</option>@endforeach</select></div>
                        </div>
                        <small class="text-muted">Untuk impor backup SQL 2017, gunakan proses staging setelah struktur tabel lama diperiksa.</small>
                    </div>
                    <div class="modal-footer"><button class="btn btn-primary" type="submit"><i class="fas fa-save mr-1"></i>Simpan</button></div>
                </form>
            </div>
        </div>
    @endcan
@stop

@section('css')
    <style>
        .alumni-summary { display:grid; grid-template-columns:repeat(auto-fit,minmax(180px,1fr)); gap:.8rem; margin-bottom:1rem; }
        .alumni-summary-card { display:flex; gap:.7rem; align-items:center; padding:.8rem; border:1px solid #dbe4f0; border-radius:12px; background:#fff; }
        .alumni-summary-card > span { display:grid; place-items:center; width:38px; height:38px; border-radius:10px; color:#fff; }
        .alumni-summary-card small, .alumni-summary-card strong { display:block; }
        .alumni-summary-card small { font-size:.68rem; text-transform:uppercase; color:#64748b; font-weight:800; }
        .alumni-summary-card strong { font-size:1.35rem; color:#0f172a; line-height:1.1; }
        .alumni-chart { height:230px; }
        .alumni-filter { padding:.8rem; border:1px solid #e2e8f0; border-radius:10px; background:#f8fafc; }
        .alumni-filter label, .alumni-table th { font-size:.68rem; font-weight:800; text-transform:uppercase; color:#64748b; }
        .alumni-table th { white-space:nowrap; }
        @media (max-width:575px) { .alumni-chart { height:190px; } }
    </style>
@stop

@section('js')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var chart = document.getElementById('alumniYearChart');
            if (!chart || typeof Chart === 'undefined') return;

            new Chart(chart.getContext('2d'), {
                type: 'bar',
                data: {
                    labels: @json($stats['labels']),
                    datasets: [{ data: @json($stats['values']), backgroundColor: '#3b82f6', borderRadius: 6, maxBarThickness: 54 }]
                },
                options: { maintainAspectRatio: false, legend: { display: false }, scales: { yAxes: [{ ticks: { beginAtZero: true, precision: 0 } }], xAxes: [{ gridLines: { display: false } }] } }
            });
        });
    </script>
@stop
