@extends('adminlte::page')

@section('title', 'Detail Tahun Pelajaran')

@section('content_header')
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1><i class="fas fa-info-circle"></i> Detail Tahun Pelajaran</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.tahun-pelajaran.index') }}">Tahun Pelajaran</a></li>
                <li class="breadcrumb-item active">Detail</li>
            </ol>
        </div>
    </div>
@stop

@section('content')
    <section class="simansa-tp-detail-hero">
        <div class="simansa-tp-detail-hero__main">
            <span class="simansa-tp-detail-hero__eyebrow">Siklus Akademik</span>
            <h2 class="simansa-tp-detail-hero__title">{{ $tahunPelajaran->nama }}</h2>
            <p class="simansa-tp-detail-hero__desc">
                Kurikulum {{ $tahunPelajaran->kurikulum->formatted_name }} dengan periode
                {{ $tahunPelajaran->tanggal_mulai?->format('d M Y') }} sampai {{ $tahunPelajaran->tanggal_selesai?->format('d M Y') }}.
            </p>
        </div>
        <div class="simansa-tp-detail-hero__meta">
            <div class="simansa-tp-detail-chip">
                <span class="simansa-tp-detail-chip__label">Status</span>
                <span class="simansa-tp-detail-chip__value">{{ $statusLabel }}</span>
            </div>
            <div class="simansa-tp-detail-chip">
                <span class="simansa-tp-detail-chip__label">Semester</span>
                <span class="simansa-tp-detail-chip__value">{{ $tahunPelajaran->semester_aktif }}</span>
            </div>
            <div class="simansa-tp-detail-chip">
                <span class="simansa-tp-detail-chip__label">Durasi</span>
                <span class="simansa-tp-detail-chip__value">{{ $tahunPelajaran->duration_months }} bulan</span>
            </div>
        </div>
    </section>

    <div class="row">
        <div class="col-lg-4 col-md-6">
            <div class="simansa-stat-card simansa-stat-card--primary">
                <span class="simansa-stat-card__label">Total Kelas</span>
                <span class="simansa-stat-card__value">{{ $stats['total_kelas'] }}</span>
                <span class="simansa-stat-card__note">Seluruh rombel yang tercatat pada periode ini</span>
            </div>
        </div>
        <div class="col-lg-4 col-md-6">
            <div class="simansa-stat-card simansa-stat-card--success">
                <span class="simansa-stat-card__label">Siswa Aktif</span>
                <span class="simansa-stat-card__value">{{ $stats['total_siswa'] }}</span>
                <span class="simansa-stat-card__note">Diambil dari histori siswa kelas berstatus aktif</span>
            </div>
        </div>
        <div class="col-lg-4 col-md-12">
            <div class="simansa-stat-card simansa-stat-card--neutral">
                <span class="simansa-stat-card__label">Mutasi</span>
                <span class="simansa-stat-card__value">{{ $stats['mutasi_masuk'] + $stats['mutasi_keluar'] }}</span>
                <span class="simansa-stat-card__note">Masuk: {{ $stats['mutasi_masuk'] }} | Keluar: {{ $stats['mutasi_keluar'] }}</span>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-5">
            <div class="card simansa-panel">
                <div class="card-header border-0">
                    <h3 class="card-title"><i class="fas fa-clipboard-list"></i> Ringkasan Periode</h3>
                </div>
                <div class="card-body pt-0">
                    <div class="simansa-summary-list">
                        <div class="simansa-summary-list__item">
                            <span class="simansa-summary-list__label">Kurikulum</span>
                            <span class="simansa-summary-list__value">{{ $tahunPelajaran->kurikulum->formatted_name }}</span>
                        </div>
                        <div class="simansa-summary-list__item">
                            <span class="simansa-summary-list__label">Status</span>
                            <span class="simansa-summary-list__value">{{ $statusLabel }}</span>
                        </div>
                        <div class="simansa-summary-list__item">
                            <span class="simansa-summary-list__label">Semester Aktif</span>
                            <span class="simansa-summary-list__value">{{ $tahunPelajaran->semester_aktif }}</span>
                        </div>
                        <div class="simansa-summary-list__item">
                            <span class="simansa-summary-list__label">Periode Tanggal</span>
                            <span class="simansa-summary-list__value">{{ $tahunPelajaran->tanggal_mulai?->format('d M Y') }} - {{ $tahunPelajaran->tanggal_selesai?->format('d M Y') }}</span>
                        </div>
                        <div class="simansa-summary-list__item">
                            <span class="simansa-summary-list__label">Tahun Numerik</span>
                            <span class="simansa-summary-list__value">{{ $tahunPelajaran->tahun_mulai }}/{{ $tahunPelajaran->tahun_selesai }}</span>
                        </div>
                        <div class="simansa-summary-list__item">
                            <span class="simansa-summary-list__label">Mode Pengelolaan</span>
                            <span class="simansa-summary-list__value">
                                @if($tahunPelajaran->is_active)
                                    Periode ini sedang digunakan sistem
                                @elseif($tahunPelajaran->status === 'selesai')
                                    Periode ini sudah diarsipkan
                                @else
                                    Periode ini siap diaktifkan saat diperlukan
                                @endif
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-7">
            <div class="card simansa-panel">
                <div class="card-header border-0">
                    <h3 class="card-title"><i class="fas fa-exchange-alt"></i> Ringkasan Mutasi</h3>
                </div>
                <div class="card-body pt-0">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="simansa-mutation-card simansa-mutation-card--success">
                                <span class="simansa-mutation-card__label">Mutasi Masuk</span>
                                <span class="simansa-mutation-card__value">{{ $stats['mutasi_masuk'] }}</span>
                                <span class="simansa-mutation-card__note">Siswa yang masuk pada periode ini</span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="simansa-mutation-card simansa-mutation-card--danger">
                                <span class="simansa-mutation-card__label">Mutasi Keluar</span>
                                <span class="simansa-mutation-card__value">{{ $stats['mutasi_keluar'] }}</span>
                                <span class="simansa-mutation-card__note">Siswa yang keluar pada periode ini</span>
                            </div>
                        </div>
                    </div>
                    <div class="simansa-net-mutation">
                        <span class="simansa-net-mutation__label">Net Mutasi</span>
                        @php $netMutasi = $stats['mutasi_masuk'] - $stats['mutasi_keluar']; @endphp
                        <span class="simansa-net-mutation__value simansa-net-mutation__value--{{ $netMutasi >= 0 ? 'positive' : 'negative' }}">
                            {{ $netMutasi > 0 ? '+' : '' }}{{ $netMutasi }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if($tahunPelajaran->kelas->count() > 0)
        <div class="card simansa-panel">
            <div class="card-header border-0">
                <div>
                    <h3 class="card-title mb-1"><i class="fas fa-chalkboard-teacher"></i> Struktur Kelas</h3>
                    <p class="text-muted mb-0">Pembagian rombel ditampilkan per tingkat untuk memudahkan pengecekan cepat.</p>
                </div>
            </div>
            <div class="card-body pt-0">
                <div class="row">
                    @foreach($tahunPelajaran->kelas->groupBy('tingkat') as $tingkat => $kelasGroup)
                        <div class="col-lg-4 col-md-6">
                            <div class="simansa-level-card">
                                <div class="simansa-level-card__header">
                                    <span class="simansa-level-card__title">Kelas {{ $tingkat }}</span>
                                    <span class="simansa-level-card__count">{{ $kelasGroup->count() }} rombel</span>
                                </div>
                                <div class="simansa-level-card__body">
                                    @foreach($kelasGroup as $kelas)
                                        <div class="simansa-level-card__row">
                                            <div>
                                                <strong>{{ $kelas->nama_kelas }}</strong>
                                                @if($kelas->jurusan)
                                                    <div class="text-muted small">{{ $kelas->jurusan->singkatan }}</div>
                                                @endif
                                            </div>
                                            <span class="badge badge-{{ $kelas->capacity_badge_color }}">
                                                {{ $kelas->jumlah_siswa }}/{{ $kelas->kapasitas }}
                                            </span>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif

    <div class="card">
        <div class="card-footer">
            <div class="row">
                <div class="col-md-6">
                    <a href="{{ route('admin.tahun-pelajaran.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Kembali
                    </a>
                </div>
                <div class="col-md-6 text-right">
                    @can('edit-tahun-pelajaran')
                        <a href="{{ route('admin.tahun-pelajaran.edit', $tahunPelajaran->id) }}" class="btn btn-primary">
                            <i class="fas fa-edit"></i> Edit
                        </a>
                    @endcan
                    @can('set-active-tahun-pelajaran')
                        @if(!$tahunPelajaran->is_active && $tahunPelajaran->status !== 'selesai')
                            <button type="button" class="btn btn-success" id="btnSetActive">
                                <i class="fas fa-check-circle"></i> Set Aktif
                            </button>
                        @endif
                    @endcan
                    @can('change-semester-tahun-pelajaran')
                        @if($tahunPelajaran->is_active)
                            <button type="button" class="btn btn-info" id="btnChangeSemester" data-semester="{{ $tahunPelajaran->semester_aktif === 'Ganjil' ? 'Genap' : 'Ganjil' }}">
                                <i class="fas fa-sync-alt"></i> Ganti ke Semester {{ $tahunPelajaran->semester_aktif === 'Ganjil' ? 'Genap' : 'Ganjil' }}
                            </button>
                        @endif
                    @endcan
                </div>
            </div>
        </div>
    </div>
@stop

@section('css')
    <style>
        .simansa-tp-detail-hero {
            display: grid;
            grid-template-columns: 1.6fr 1fr;
            gap: 1rem;
            padding: 2rem;
            margin-bottom: 1.5rem;
            border-radius: 24px;
            background: linear-gradient(135deg, #2647d0 0%, #2e7687 100%);
            color: #fff;
            box-shadow: 0 18px 40px rgba(38, 71, 208, 0.16);
        }
        .simansa-tp-detail-hero__eyebrow {
            display: inline-block;
            margin-bottom: 0.7rem;
            font-size: 0.78rem;
            font-weight: 700;
            letter-spacing: 0.1em;
            text-transform: uppercase;
            opacity: 0.82;
        }
        .simansa-tp-detail-hero__title {
            margin: 0 0 0.55rem;
            font-size: 2rem;
            font-weight: 800;
            line-height: 1.1;
        }
        .simansa-tp-detail-hero__desc {
            margin: 0;
            max-width: 720px;
            color: rgba(255, 255, 255, 0.92);
        }
        .simansa-tp-detail-hero__meta {
            display: grid;
            gap: 0.75rem;
        }
        .simansa-tp-detail-chip {
            padding: 1rem;
            border-radius: 18px;
            background: rgba(255, 255, 255, 0.14);
        }
        .simansa-tp-detail-chip__label {
            display: block;
            margin-bottom: 0.2rem;
            font-size: 0.74rem;
            font-weight: 700;
            letter-spacing: 0.06em;
            text-transform: uppercase;
            opacity: 0.8;
        }
        .simansa-tp-detail-chip__value {
            display: block;
            font-size: 1rem;
            font-weight: 700;
        }
        .simansa-stat-card {
            display: flex;
            flex-direction: column;
            gap: 0.45rem;
            min-height: 154px;
            padding: 1.4rem;
            margin-bottom: 1rem;
            border-radius: 22px;
            color: #fff;
            box-shadow: 0 16px 36px rgba(15, 23, 42, 0.08);
        }
        .simansa-stat-card__label {
            font-size: 0.8rem;
            font-weight: 700;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            opacity: 0.9;
        }
        .simansa-stat-card__value {
            font-size: 2.2rem;
            font-weight: 800;
            line-height: 1;
        }
        .simansa-stat-card__note {
            margin-top: auto;
            font-size: 0.92rem;
            opacity: 0.92;
        }
        .simansa-stat-card--primary {
            background: linear-gradient(135deg, #5b61f2 0%, #6d7cff 100%);
        }
        .simansa-stat-card--success {
            background: linear-gradient(135deg, #39c98a 0%, #67d6a3 100%);
        }
        .simansa-stat-card--neutral {
            background: linear-gradient(135deg, #62748e 0%, #8b9bb0 100%);
        }
        .simansa-panel {
            border: 0;
            border-radius: 22px;
            overflow: hidden;
            box-shadow: 0 18px 36px rgba(15, 23, 42, 0.08);
            margin-bottom: 1rem;
        }
        .simansa-summary-list__item {
            display: flex;
            justify-content: space-between;
            gap: 1rem;
            padding: 0.95rem 0;
            border-bottom: 1px solid #edf2f7;
        }
        .simansa-summary-list__item:last-child {
            border-bottom: 0;
        }
        .simansa-summary-list__label {
            color: #64748b;
            font-weight: 600;
        }
        .simansa-summary-list__value {
            text-align: right;
            font-weight: 700;
            color: #0f172a;
        }
        .simansa-mutation-card {
            display: flex;
            flex-direction: column;
            gap: 0.35rem;
            padding: 1.2rem;
            border-radius: 18px;
            margin-bottom: 1rem;
        }
        .simansa-mutation-card--success {
            background: rgba(57, 201, 138, 0.12);
            color: #116149;
        }
        .simansa-mutation-card--danger {
            background: rgba(238, 92, 112, 0.12);
            color: #9a2737;
        }
        .simansa-mutation-card__label {
            font-size: 0.78rem;
            font-weight: 700;
            letter-spacing: 0.08em;
            text-transform: uppercase;
        }
        .simansa-mutation-card__value {
            font-size: 2rem;
            font-weight: 800;
            line-height: 1;
        }
        .simansa-net-mutation {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-top: 0.75rem;
            border-top: 1px solid #edf2f7;
            font-weight: 700;
        }
        .simansa-net-mutation__label {
            color: #475569;
        }
        .simansa-net-mutation__value--positive {
            color: #15965f;
        }
        .simansa-net-mutation__value--negative {
            color: #c53030;
        }
        .simansa-level-card {
            height: 100%;
            border: 1px solid #e5edf5;
            border-radius: 18px;
            background: #fff;
            overflow: hidden;
            margin-bottom: 1rem;
        }
        .simansa-level-card__header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem 1.1rem;
            background: #f8fbff;
            border-bottom: 1px solid #e5edf5;
        }
        .simansa-level-card__title {
            font-weight: 800;
            color: #1e293b;
        }
        .simansa-level-card__count {
            font-size: 0.85rem;
            color: #64748b;
            font-weight: 700;
        }
        .simansa-level-card__body {
            padding: 0.35rem 1rem 0.85rem;
        }
        .simansa-level-card__row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 1rem;
            padding: 0.8rem 0.1rem;
            border-bottom: 1px solid #edf2f7;
        }
        .simansa-level-card__row:last-child {
            border-bottom: 0;
        }
        @media (max-width: 991.98px) {
            .simansa-tp-detail-hero {
                grid-template-columns: 1fr;
            }
        }
    </style>
@stop

@section('js')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        $(document).ready(function() {
            $('#btnSetActive').on('click', function() {
                Swal.fire({
                    title: 'Aktifkan Tahun Pelajaran?',
                    text: 'Periode aktif yang lama akan otomatis dinonaktifkan.',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#28a745',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: '<i class="fas fa-check"></i> Ya, Aktifkan',
                    cancelButtonText: '<i class="fas fa-times"></i> Batal'
                }).then((result) => {
                    if (!result.isConfirmed) return;

                    $.ajax({
                        url: "{{ route('admin.tahun-pelajaran.set-active', $tahunPelajaran->id) }}",
                        type: 'POST',
                        data: {_token: '{{ csrf_token() }}'},
                        success: function(response) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil',
                                text: response.message,
                            }).then(() => location.reload());
                        },
                        error: function(xhr) {
                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal',
                                text: xhr.responseJSON?.message || 'Terjadi kesalahan'
                            });
                        }
                    });
                });
            });

            $('#btnChangeSemester').on('click', function() {
                const nextSemester = $(this).data('semester');

                Swal.fire({
                    title: 'Ganti Semester?',
                    text: `Semester aktif akan diubah ke ${nextSemester}.`,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#17a2b8',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: '<i class="fas fa-sync-alt"></i> Ya, Ubah',
                    cancelButtonText: '<i class="fas fa-times"></i> Batal'
                }).then((result) => {
                    if (!result.isConfirmed) return;

                    $.ajax({
                        url: "{{ route('admin.tahun-pelajaran.change-semester', $tahunPelajaran->id) }}",
                        type: 'POST',
                        data: {_token: '{{ csrf_token() }}'},
                        success: function(response) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil',
                                text: response.message,
                            }).then(() => location.reload());
                        },
                        error: function(xhr) {
                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal',
                                text: xhr.responseJSON?.message || 'Terjadi kesalahan'
                            });
                        }
                    });
                });
            });
        });
    </script>
@stop
