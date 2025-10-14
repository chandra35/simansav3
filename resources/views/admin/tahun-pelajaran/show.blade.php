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
    {{-- Header Info --}}
    <div class="row">
        <div class="col-md-12">
            <div class="card card-widget widget-user-2">
                <div class="widget-user-header bg-gradient-primary">
                    <div class="widget-user-image">
                        <span class="elevation-2 bg-white rounded-circle p-3">
                            <i class="fas fa-calendar-alt fa-2x text-primary"></i>
                        </span>
                    </div>
                    <h3 class="widget-user-username">{{ $tahunPelajaran->nama }}</h3>
                    <h5 class="widget-user-desc">{{ $tahunPelajaran->kurikulum->formatted_name }}</h5>
                </div>
                <div class="card-footer p-0">
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <span class="nav-link">
                                <strong>Status:</strong> 
                                <span class="float-right">{!! $tahunPelajaran->status_badge !!}</span>
                            </span>
                        </li>
                        <li class="nav-item">
                            <span class="nav-link">
                                <strong>Semester:</strong>
                                <span class="float-right">{!! $tahunPelajaran->semester_badge !!}</span>
                            </span>
                        </li>
                        <li class="nav-item">
                            <span class="nav-link">
                                <strong>Periode:</strong>
                                <span class="float-right">{{ $tahunPelajaran->tanggal_mulai?->format('d M Y') }} - {{ $tahunPelajaran->tanggal_selesai?->format('d M Y') }}</span>
                            </span>
                        </li>
                        <li class="nav-item">
                            <span class="nav-link">
                                <strong>Durasi:</strong>
                                <span class="float-right">{{ $tahunPelajaran->duration_months }} Bulan</span>
                            </span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    {{-- Statistics --}}
    <div class="row">
        <div class="col-lg-3 col-6">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>{{ $stats['total_kelas'] }}</h3>
                    <p>Total Kelas</p>
                </div>
                <div class="icon">
                    <i class="fas fa-chalkboard-teacher"></i>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-6">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>{{ $stats['total_siswa'] }}</h3>
                    <p>Siswa Aktif</p>
                </div>
                <div class="icon">
                    <i class="fas fa-users"></i>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-6">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3>{{ $stats['kuota_tersedia'] }}</h3>
                    <p>Kuota Tersedia</p>
                </div>
                <div class="icon">
                    <i class="fas fa-user-plus"></i>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-6">
            <div class="small-box bg-danger">
                <div class="inner">
                    <h3>{{ $stats['mutasi_masuk'] + $stats['mutasi_keluar'] }}</h3>
                    <p>Total Mutasi</p>
                </div>
                <div class="icon">
                    <i class="fas fa-exchange-alt"></i>
                </div>
            </div>
        </div>
    </div>

    {{-- Detailed Info --}}
    <div class="row">
        <div class="col-md-6">
            <div class="card card-primary card-outline">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-info-circle"></i> Informasi Kuota PPDB</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-6">
                            <div class="description-block">
                                <h5 class="description-header">{{ $tahunPelajaran->kuota_ppdb }}</h5>
                                <span class="description-text">Total Kuota</span>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="description-block">
                                <h5 class="description-header text-success">{{ $stats['kuota_tersedia'] }}</h5>
                                <span class="description-text">Tersedia</span>
                            </div>
                        </div>
                    </div>

                    <div class="progress-group mt-3">
                        Penggunaan Kuota
                        @php
                            $used = $tahunPelajaran->kuota_ppdb - $stats['kuota_tersedia'];
                            $percentage = $tahunPelajaran->kuota_ppdb > 0 ? round(($used / $tahunPelajaran->kuota_ppdb) * 100) : 0;
                        @endphp
                        <span class="float-right"><b>{{ $used }}</b>/{{ $tahunPelajaran->kuota_ppdb }}</span>
                        <div class="progress progress-sm">
                            <div class="progress-bar bg-{{ $percentage >= 90 ? 'danger' : ($percentage >= 70 ? 'warning' : 'success') }}" 
                                 style="width: {{ $percentage }}%"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card card-info card-outline">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-exchange-alt"></i> Informasi Mutasi</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-6">
                            <div class="description-block border-right">
                                <h5 class="description-header text-success">{{ $stats['mutasi_masuk'] }}</h5>
                                <span class="description-text">Mutasi Masuk</span>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="description-block">
                                <h5 class="description-header text-danger">{{ $stats['mutasi_keluar'] }}</h5>
                                <span class="description-text">Mutasi Keluar</span>
                            </div>
                        </div>
                    </div>

                    <div class="mt-3">
                        <strong><i class="fas fa-calculator"></i> Net Mutasi:</strong>
                        @php
                            $netMutasi = $stats['mutasi_masuk'] - $stats['mutasi_keluar'];
                        @endphp
                        <span class="float-right badge badge-{{ $netMutasi >= 0 ? 'success' : 'danger' }}">
                            {{ $netMutasi > 0 ? '+' : '' }}{{ $netMutasi }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Kelas List --}}
    @if($tahunPelajaran->kelas->count() > 0)
        <div class="card card-success card-outline">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-chalkboard-teacher"></i> Daftar Kelas</h3>
            </div>
            <div class="card-body">
                <div class="row">
                    @foreach($tahunPelajaran->kelas->groupBy('tingkat') as $tingkat => $kelasGroup)
                        <div class="col-md-4">
                            <div class="card card-outline">
                                <div class="card-header">
                                    <h3 class="card-title"><strong>Kelas {{ $tingkat }}</strong></h3>
                                </div>
                                <div class="card-body p-0">
                                    <table class="table table-sm table-hover">
                                        <tbody>
                                            @foreach($kelasGroup as $kelas)
                                                <tr>
                                                    <td>
                                                        {{ $kelas->nama_kelas }}
                                                        @if($kelas->jurusan)
                                                            <small class="text-muted">({{ $kelas->jurusan->singkatan }})</small>
                                                        @endif
                                                    </td>
                                                    <td class="text-right">
                                                        <span class="badge badge-{{ $kelas->capacity_badge_color }}">
                                                            {{ $kelas->jumlah_siswa }}/{{ $kelas->kapasitas }}
                                                        </span>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif

    {{-- Action Buttons --}}
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

@section('js')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        $(document).ready(function() {
            // Set Active Handler
            $('#btnSetActive').on('click', function() {
                Swal.fire({
                    title: 'Konfirmasi',
                    text: "Set tahun pelajaran ini sebagai tahun aktif? Tahun pelajaran aktif lainnya akan dinonaktifkan.",
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#28a745',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: '<i class="fas fa-check"></i> Ya, Aktifkan!',
                    cancelButtonText: '<i class="fas fa-times"></i> Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: "{{ route('admin.tahun-pelajaran.set-active', $tahunPelajaran->id) }}",
                            type: 'POST',
                            data: {
                                _token: '{{ csrf_token() }}'
                            },
                            success: function(response) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Berhasil!',
                                    text: response.message,
                                }).then(() => {
                                    location.reload();
                                });
                            },
                            error: function(xhr) {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Gagal!',
                                    text: xhr.responseJSON?.message || 'Terjadi kesalahan'
                                });
                            }
                        });
                    }
                });
            });

            // Change Semester Handler
            $('#btnChangeSemester').on('click', function() {
                const nextSemester = $(this).data('semester');
                
                Swal.fire({
                    title: 'Konfirmasi',
                    text: `Ubah semester menjadi ${nextSemester}?`,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#17a2b8',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: '<i class="fas fa-sync-alt"></i> Ya, Ubah!',
                    cancelButtonText: '<i class="fas fa-times"></i> Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: "{{ route('admin.tahun-pelajaran.change-semester', $tahunPelajaran->id) }}",
                            type: 'POST',
                            data: {
                                _token: '{{ csrf_token() }}'
                            },
                            success: function(response) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Berhasil!',
                                    text: response.message,
                                }).then(() => {
                                    location.reload();
                                });
                            },
                            error: function(xhr) {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Gagal!',
                                    text: xhr.responseJSON?.message || 'Terjadi kesalahan'
                                });
                            }
                        });
                    }
                });
            });
        });
    </script>
@stop
