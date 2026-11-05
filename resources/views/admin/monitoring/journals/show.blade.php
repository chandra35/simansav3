@extends('adminlte::page')
@section('title', 'Detail Jurnal - ' . ($gtk?->nama_lengkap ?? $user->name))

@section('content_header')
<div class="row mb-2">
    <div class="col-sm-6">
        <h1 class="m-0 text-dark font-weight-bold">
            <i class="fas fa-book-reader text-primary mr-2"></i>Detail Jurnal Guru
        </h1>
    </div>
    <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.monitoring-jurnal.index') }}">Monitoring Jurnal</a></li>
            <li class="breadcrumb-item active">{{ $gtk?->nama_lengkap ?? $user->name }}</li>
        </ol>
    </div>
</div>
@stop

@section('content')
<div class="teacher-detail-page">
    {{-- Teacher Profile Hero Card --}}
    <div class="card border-0 shadow-sm mb-4 teacher-profile-card">
        <div class="card-body p-4">
            <div class="row align-items-center">
                <div class="col-lg-7 d-flex align-items-center mb-3 mb-lg-0">
                    <img src="{{ $gtk?->foto_profile_url ?? 'https://ui-avatars.com/api/?name='.urlencode($gtk?->nama_lengkap ?? $user->name).'&size=120&background=e2e8f0&color=475569' }}"
                         alt="{{ $gtk?->nama_lengkap ?? $user->name }}" class="detail-avatar mr-4 shadow-sm">
                    <div>
                        <span class="badge badge-primary-subtle text-primary mb-1">
                            {{ $gtk?->jenis_ptk ?: ($gtk?->kategori_ptk ?: 'Tenaga Pendidik') }}
                        </span>
                        <h4 class="font-weight-bold mb-1 text-dark">{{ $gtk?->nama_lengkap ?? $user->name }}</h4>
                        <div class="text-muted small">
                            <span><i class="far fa-id-badge mr-1"></i>NIP: <strong>{{ $gtk?->nip ?: '-' }}</strong></span>
                            @if($gtk?->nuptk)
                                <span class="mx-2">•</span>
                                <span>NUPTK: <strong>{{ $gtk->nuptk }}</strong></span>
                            @endif
                            @if($user->email)
                                <span class="mx-2">•</span>
                                <span><i class="far fa-envelope mr-1"></i>{{ $user->email }}</span>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="col-lg-5 text-lg-right">
                    <div class="d-inline-flex flex-wrap gap-2 justify-content-lg-end">
                        <a href="{{ route('admin.monitoring-jurnal.export-teacher', [$user->id, 'dari' => $dari, 'sampai' => $sampai, 'jenis' => $jenis]) }}"
                           class="btn btn-danger shadow-sm mr-2" target="_blank">
                            <i class="fas fa-file-pdf mr-1"></i> Unduh PDF EKIN/SKP
                        </a>
                        <a href="{{ route('admin.monitoring-jurnal.index', ['dari' => $dari, 'sampai' => $sampai]) }}"
                           class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left mr-1"></i> Kembali
                        </a>
                    </div>
                </div>
            </div>

            {{-- Period Metrics Stats --}}
            <div class="row g-2 mt-3 pt-3 border-top">
                <div class="col-6 col-md-3">
                    <div class="stat-mini p-2 rounded bg-light">
                        <small class="text-muted d-block">Total Jurnal</small>
                        <strong class="h5 mb-0 text-dark">{{ $teacherStats['total'] }} Entri</strong>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="stat-mini p-2 rounded bg-light">
                        <small class="text-muted d-block">Mengajar / Wali Kelas</small>
                        <strong class="h5 mb-0 text-primary">{{ $teacherStats['teaching'] }}</strong>
                        <span class="text-muted">/</span>
                        <strong class="h5 mb-0 text-success">{{ $teacherStats['homeroom'] }}</strong>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="stat-mini p-2 rounded bg-light">
                        <small class="text-muted d-block">Total Jam Tercatat</small>
                        <strong class="h5 mb-0 text-info">{{ $teacherStats['hours'] }} Jam</strong>
                        <small class="text-muted">({{ $teacherStats['minutes'] }} mnt)</small>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="stat-mini p-2 rounded bg-light">
                        <small class="text-muted d-block">Rerata Presensi Siswa</small>
                        @if($teacherStats['avg_attendance'])
                            <strong class="h5 mb-0 text-success">{{ $teacherStats['avg_attendance'] }}%</strong>
                        @else
                            <strong class="h5 mb-0 text-muted">-</strong>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Filter Card --}}
    <div class="card border-0 shadow-sm mb-4 filter-card">
        <div class="card-body p-3">
            <form method="GET" action="{{ route('admin.monitoring-jurnal.show', $user->id) }}">
                <div class="row g-2 align-items-end">
                    <div class="col-md-4 col-sm-6 mb-2">
                        <label class="filter-label"><i class="far fa-calendar-alt mr-1 text-primary"></i> Dari Tanggal</label>
                        <input type="date" name="dari" value="{{ $dari }}" class="form-control form-control-sm">
                    </div>
                    <div class="col-md-4 col-sm-6 mb-2">
                        <label class="filter-label"><i class="far fa-calendar-check mr-1 text-primary"></i> Sampai Tanggal</label>
                        <input type="date" name="sampai" value="{{ $sampai }}" class="form-control form-control-sm">
                    </div>
                    <div class="col-md-2 col-sm-6 mb-2">
                        <label class="filter-label"><i class="fas fa-filter mr-1 text-muted"></i> Jenis</label>
                        <select name="jenis" class="form-control form-control-sm">
                            <option value="" @selected(!$jenis)>Semua Jenis</option>
                            <option value="mengajar" @selected($jenis === 'mengajar')>Mengajar</option>
                            <option value="wali_kelas" @selected($jenis === 'wali_kelas')>Wali Kelas</option>
                        </select>
                    </div>
                    <div class="col-md-2 col-12 mb-2 d-flex">
                        <button type="submit" class="btn btn-primary btn-sm btn-block">
                            <i class="fas fa-search mr-1"></i> Terapkan
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Journal Entries List --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center">
            <h5 class="card-title font-weight-bold mb-0 text-dark">
                <i class="fas fa-clipboard-list mr-2 text-primary"></i>Riwayat Aktivitas Jurnal Pembelajaran
                <span class="badge badge-light border ml-2">{{ $journals->total() }} Sesi</span>
            </h5>
            <div class="card-tools">
                <span class="text-muted small">Menampilkan data halaman {{ $journals->currentPage() }} dari {{ $journals->lastPage() ?: 1 }}</span>
            </div>
        </div>

        <div class="card-body p-3">
            @forelse($journals as $j)
                <div class="journal-card mb-3 p-3 rounded border shadow-xs bg-white">
                    <div class="d-flex flex-wrap justify-content-between align-items-center mb-2">
                        <div>
                            @if($j->jenis === 'mengajar')
                                <span class="badge badge-primary px-2 py-1 mr-2"><i class="fas fa-chalkboard mr-1"></i> Mengajar</span>
                            @else
                                <span class="badge badge-success px-2 py-1 mr-2"><i class="fas fa-user-shield mr-1"></i> Tugas Wali Kelas</span>
                            @endif
                            <strong class="text-dark font-weight-bold">
                                {{ $j->tanggal ? \Carbon\Carbon::parse($j->tanggal)->translatedFormat('l, d F Y') : '-' }}
                            </strong>
                        </div>
                        <div class="d-flex align-items-center">
                            @if($j->durasi_menit)
                                <span class="badge badge-light border text-muted mr-2">
                                    <i class="far fa-clock mr-1"></i>{{ $j->durasi_menit }} Menit
                                </span>
                            @endif
                            <span class="text-muted small">
                                <i class="fas fa-history mr-1"></i>{{ $j->created_at ? $j->created_at->diffForHumans() : '' }}
                            </span>
                        </div>
                    </div>

                    <div class="row g-2 mb-2">
                        <div class="col-md-6 text-muted small">
                            <i class="fas fa-school mr-1 text-secondary"></i>Kelas:
                            <strong class="text-dark">{{ $j->kelas_snapshot ?: ($j->kelas?->nama_kelas ?? '-') }}</strong>
                        </div>
                        @if($j->mapel_snapshot || $j->jadwalPelajaran?->mataPelajaran)
                            <div class="col-md-6 text-muted small">
                                <i class="fas fa-book mr-1 text-secondary"></i>Mata Pelajaran:
                                <strong class="text-dark">{{ $j->mapel_snapshot ?: $j->jadwalPelajaran?->mataPelajaran?->nama_mapel }}</strong>
                            </div>
                        @endif
                    </div>

                    <div class="topic-box p-3 rounded mb-2" style="background-color: #f8fafc; border-left: 3px solid #3b82f6;">
                        <h6 class="font-weight-bold text-dark mb-1">{{ $j->topik }}</h6>
                        <div class="small text-secondary" style="white-space: pre-line;">{{ $j->kegiatan }}</div>
                    </div>

                    @if($j->attendance_summary && !empty($j->attendance_summary['total']))
                        <div class="attendance-box p-2 rounded mb-2 bg-light border small">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <i class="fas fa-users mr-1 text-primary"></i>
                                    <strong>Presensi Siswa:</strong>
                                    <span class="text-success font-weight-bold ml-1">{{ $j->attendance_summary['hadir'] ?? 0 }} Hadir</span>
                                    <span class="text-muted">/ {{ $j->attendance_summary['total'] }} Siswa ({{ $j->attendance_summary['persentase'] ?? 0 }}%)</span>
                                </div>
                                <div class="text-muted">
                                    <span>Sakit: <strong>{{ $j->attendance_summary['sakit'] ?? 0 }}</strong></span>
                                    <span class="mx-1">•</span>
                                    <span>Izin: <strong>{{ $j->attendance_summary['izin'] ?? 0 }}</strong></span>
                                    <span class="mx-1">•</span>
                                    <span>Alpa: <strong>{{ $j->attendance_summary['alpa'] ?? 0 }}</strong></span>
                                </div>
                            </div>
                        </div>
                    @endif

                    @if($j->asesmen_tindak_lanjut)
                        <div class="small text-muted mt-2">
                            <strong class="text-dark"><i class="fas fa-tasks mr-1 text-info"></i>Asesmen & Tindak Lanjut:</strong>
                            <div class="mt-1 pl-3" style="white-space: pre-line;">{{ $j->asesmen_tindak_lanjut }}</div>
                        </div>
                    @endif

                    @if($j->catatan)
                        <div class="small text-muted mt-2">
                            <strong class="text-dark"><i class="far fa-comment-alt mr-1 text-secondary"></i>Catatan Tambahan:</strong>
                            <div class="mt-1 pl-3">{{ $j->catatan }}</div>
                        </div>
                    @endif
                </div>
            @empty
                <div class="text-center py-5 text-muted">
                    <i class="far fa-clipboard fa-3x mb-3 text-secondary d-block"></i>
                    <h6 class="font-weight-bold">Belum Ada Jurnal pada Periode Ini</h6>
                    <p class="small mb-0">Guru ini belum mendokumentasikan kegiatan belajar mengajar atau tugas wali kelas pada rentang tanggal terpilih.</p>
                </div>
            @endforelse

            <div class="mt-3">
                {{ $journals->links() }}
            </div>
        </div>
    </div>
</div>
@stop

@section('css')
<style>
    .teacher-profile-card {
        border-radius: 12px;
        border: 1px solid #eef2f6 !important;
    }
    .detail-avatar {
        width: 68px;
        height: 68px;
        border-radius: 50%;
        object-fit: cover;
        border: 3px solid #e2e8f0;
    }
    .badge-primary-subtle {
        background-color: #eff6ff;
        border: 1px solid #bfdbfe;
        border-radius: 4px;
    }
    .filter-card {
        border-radius: 12px;
        border: 1px solid #eef2f6;
    }
    .filter-label {
        font-size: 0.78rem;
        font-weight: 600;
        color: #475569;
        margin-bottom: 4px;
        text-transform: uppercase;
        letter-spacing: 0.3px;
    }
    .journal-card {
        transition: border-color 0.2s ease, box-shadow 0.2s ease;
    }
    .journal-card:hover {
        border-color: #93c5fd !important;
        box-shadow: 0 4px 12px rgba(0,0,0,0.05);
    }
</style>
@stop
