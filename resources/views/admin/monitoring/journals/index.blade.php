@extends('adminlte::page')
@section('title', 'Monitoring Jurnal Guru')

@section('content_header')
<div class="row mb-2">
    <div class="col-sm-6">
        <h1 class="m-0 text-dark font-weight-bold">
            <i class="fas fa-book-reader text-primary mr-2"></i>Monitoring Jurnal Guru
        </h1>
    </div>
    <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item active">Monitoring Jurnal Guru</li>
        </ol>
    </div>
</div>
@stop

@section('content')
<div class="monitoring-journal-page">
    {{-- Hero Banner --}}
    <div class="card border-0 shadow-sm mb-4 hero-banner">
        <div class="card-body p-4 text-white">
            <div class="row align-items-center">
                <div class="col-lg-8">
                    <span class="hero-kicker mb-2 d-inline-flex align-items-center">
                        <i class="fas fa-shield-alt mr-1"></i> Panel Monitoring & Evaluasi Superadmin
                    </span>
                    <h3 class="font-weight-bold mb-1">Monitoring Pengisian Jurnal Pembelajaran GTK</h3>
                    <p class="mb-0 hero-desc">
                        Pantau kedisiplinan dan keaktifan guru dalam mendokumentasikan kegiatan belajar mengajar dan tugas wali kelas untuk kebutuhan EKIN / SKP madrasah.
                    </p>
                </div>
                <div class="col-lg-4 text-lg-right mt-3 mt-lg-0">
                    <div class="d-inline-flex flex-wrap gap-2 justify-content-lg-end">
                        <a href="{{ route('admin.monitoring-jurnal.export-rekap', ['dari' => $dari, 'sampai' => $sampai, 'kategori' => $kategori, 'status' => $status, 'q' => $q, 'sort' => $sort]) }}"
                           class="btn btn-hero-action btn-hero-white shadow-sm" target="_blank">
                            <i class="fas fa-file-pdf mr-1 text-danger"></i> Export Rekap PDF
                        </a>
                        @if($q || $status !== 'all' || $kategori !== 'pendidik' || $sort !== 'most_active')
                            <a href="{{ route('admin.monitoring-jurnal.index') }}" class="btn btn-hero-action btn-hero-glass ml-2">
                                <i class="fas fa-redo-alt mr-1"></i> Reset
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- KPI Stat Cards --}}
    <div class="row mb-3">
        <div class="col-6 col-lg-3 mb-3">
            <div class="card stat-card border-0 shadow-sm h-100">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <span class="stat-label d-block text-muted">Total Guru Terdata</span>
                            <h3 class="stat-value font-weight-bold mb-0 text-dark">{{ number_format($stats['total_teachers']) }}</h3>
                        </div>
                        <div class="stat-icon-box bg-blue-soft text-primary">
                            <i class="fas fa-chalkboard-teacher fa-lg"></i>
                        </div>
                    </div>
                    <div class="mt-2 pt-2 border-top">
                        <small class="text-muted"><i class="fas fa-users mr-1"></i>Guru & Tenaga Pendidik Aktif</small>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-6 col-lg-3 mb-3">
            <div class="card stat-card border-0 shadow-sm h-100">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <span class="stat-label d-block text-muted">Sudah Mengisi Jurnal</span>
                            <h3 class="stat-value font-weight-bold mb-0 text-success">{{ number_format($stats['active_teachers']) }}</h3>
                        </div>
                        <div class="stat-icon-box bg-teal-soft text-success">
                            <i class="fas fa-check-circle fa-lg"></i>
                        </div>
                    </div>
                    <div class="mt-2 pt-2 border-top">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <small class="text-muted">Partisipasi</small>
                            <small class="font-weight-bold text-success">{{ $stats['participation_rate'] }}%</small>
                        </div>
                        <div class="progress progress-xs">
                            <div class="progress-bar bg-success" style="width: {{ $stats['participation_rate'] }}%"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-6 col-lg-3 mb-3">
            <div class="card stat-card border-0 shadow-sm h-100">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <span class="stat-label d-block text-muted">Belum Ada Jurnal</span>
                            <h3 class="stat-value font-weight-bold mb-0 {{ $stats['inactive_teachers'] > 0 ? 'text-danger' : 'text-muted' }}">
                                {{ number_format($stats['inactive_teachers']) }}
                            </h3>
                        </div>
                        <div class="stat-icon-box bg-red-soft text-danger">
                            <i class="fas fa-exclamation-triangle fa-lg"></i>
                        </div>
                    </div>
                    <div class="mt-2 pt-2 border-top">
                        <small class="text-muted">
                            @if($stats['inactive_teachers'] > 0)
                                <span class="text-danger font-weight-bold"><i class="fas fa-bell mr-1"></i>Perlu difasilitasi</span>
                            @else
                                <span class="text-success"><i class="fas fa-check mr-1"></i>Semua sudah mengisi</span>
                            @endif
                        </small>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-6 col-lg-3 mb-3">
            <div class="card stat-card border-0 shadow-sm h-100">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <span class="stat-label d-block text-muted">Total Entri & Jam</span>
                            <h3 class="stat-value font-weight-bold mb-0 text-info">{{ number_format($stats['total_entries']) }}</h3>
                        </div>
                        <div class="stat-icon-box bg-purple-soft text-indigo">
                            <i class="fas fa-clock fa-lg"></i>
                        </div>
                    </div>
                    <div class="mt-2 pt-2 border-top d-flex justify-content-between">
                        <small class="text-muted"><i class="fas fa-hourglass-half mr-1"></i>{{ $stats['total_hours'] }} Jam</small>
                        @if($stats['overall_avg_attendance'])
                            <small class="text-primary font-weight-bold"><i class="fas fa-user-check mr-1"></i>{{ $stats['overall_avg_attendance'] }}% Hadir</small>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Filter Toolbar --}}
    <div class="card border-0 shadow-sm mb-4 filter-card">
        <div class="card-body p-3">
            <form method="GET" action="{{ route('admin.monitoring-jurnal.index') }}" id="filterForm">
                <div class="row g-2 align-items-end">
                    <div class="col-md-3 col-sm-6 mb-2">
                        <label class="filter-field-label"><i class="far fa-calendar-alt mr-1 text-primary"></i> Dari Tanggal</label>
                        <input type="date" name="dari" value="{{ $dari }}" class="form-control filter-input">
                    </div>
                    <div class="col-md-3 col-sm-6 mb-2">
                        <label class="filter-field-label"><i class="far fa-calendar-check mr-1 text-primary"></i> Sampai Tanggal</label>
                        <input type="date" name="sampai" value="{{ $sampai }}" class="form-control filter-input">
                    </div>
                    <div class="col-md-2 col-sm-6 mb-2">
                        <label class="filter-field-label"><i class="fas fa-filter mr-1 text-muted"></i> Status Jurnal</label>
                        <select name="status" class="form-control filter-input">
                            <option value="all" @selected($status === 'all')>Semua Guru</option>
                            <option value="has_journals" @selected($status === 'has_journals')>Sudah Mengisi (≥ 1)</option>
                            <option value="no_journals" @selected($status === 'no_journals')>Belum Mengisi (0)</option>
                        </select>
                    </div>
                    <div class="col-md-2 col-sm-6 mb-2">
                        <label class="filter-field-label"><i class="fas fa-sort-amount-down mr-1 text-muted"></i> Urutkan</label>
                        <select name="sort" class="form-control filter-input">
                            <option value="most_active" @selected($sort === 'most_active')>Paling Aktif</option>
                            <option value="latest_activity" @selected($sort === 'latest_activity')>Aktivitas Terbaru</option>
                            <option value="name_asc" @selected($sort === 'name_asc')>Nama Guru (A-Z)</option>
                            <option value="avg_attendance" @selected($sort === 'avg_attendance')>Rerata Presensi Tertinggi</option>
                        </select>
                    </div>
                    <div class="col-md-2 col-12 mb-2 d-flex">
                        <button type="submit" class="btn btn-primary filter-btn-apply w-100">
                            <i class="fas fa-search mr-1"></i> Terapkan
                        </button>
                    </div>
                </div>

                {{-- Second Filter Row: Search & Category --}}
                <div class="row g-2 align-items-center mt-1 pt-2 border-top">
                    <div class="col-md-6 mb-2 mb-md-0">
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text bg-white border-right-0" style="border-radius: 8px 0 0 8px;">
                                    <i class="fas fa-search text-muted"></i>
                                </span>
                            </div>
                            <input type="text" name="q" value="{{ $q }}" class="form-control border-left-0"
                                   placeholder="Cari nama guru, NIP, atau NUPTK..." style="border-radius: 0 8px 8px 0; height: 38px;">
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="checkAllGtk" name="kategori" value="all"
                                   @checked($kategori === 'all') onchange="this.form.submit()">
                            <label class="custom-control-label text-muted small" for="checkAllGtk">
                                Sertakan seluruh GTK (bukan hanya guru)
                            </label>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6 text-md-right text-muted small">
                        <span>Menampilkan <strong>{{ $rows->count() }}</strong> guru terdata</span>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Main Teachers Table --}}
    <div class="card border-0 shadow-sm table-card mb-4">
        <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center">
            <h5 class="card-title font-weight-bold mb-0 text-dark">
                <i class="fas fa-users-cog mr-2 text-primary"></i>Daftar Guru & Rekap Jurnal
                <span class="badge badge-light border ml-2">{{ $rows->count() }} GTK</span>
            </h5>
            <div class="card-tools">
                <span class="text-muted small">
                    Periode: <strong>{{ \Carbon\Carbon::parse($dari)->translatedFormat('d M Y') }}</strong> s/d <strong>{{ \Carbon\Carbon::parse($sampai)->translatedFormat('d M Y') }}</strong>
                </span>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 monitoring-table">
                <thead class="bg-light text-muted">
                    <tr>
                        <th class="text-center" style="width: 50px;">No</th>
                        <th>Guru & NIP</th>
                        <th class="text-center" style="width: 140px;">Status</th>
                        <th class="text-center" style="width: 110px;">Mengajar</th>
                        <th class="text-center" style="width: 110px;">Wali Kelas</th>
                        <th class="text-center" style="width: 110px;">Total Jam</th>
                        <th class="text-center" style="width: 120px;">Rerata Hadir</th>
                        <th class="text-center" style="width: 130px;">Jurnal Terakhir</th>
                        <th class="text-center" style="width: 150px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($rows as $index => $row)
                        @php
                            $userModel = $row['user'];
                            $userId = $row['user_id'] ?? $userModel?->id;
                            $hasJournals = $row['has_journals'];
                        @endphp
                        <tr>
                            <td class="text-center text-muted font-weight-bold">{{ $index + 1 }}</td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <img src="{{ $row['gtk']?->foto_profile_url ?? 'https://ui-avatars.com/api/?name='.urlencode($row['nama']).'&size=100&background=e2e8f0&color=475569' }}"
                                         alt="{{ $row['nama'] }}" class="teacher-avatar mr-3 shadow-xs">
                                    <div>
                                        <div class="font-weight-bold text-dark teacher-name">{{ $row['nama'] }}</div>
                                        <div class="text-muted small">
                                            <span>NIP: {{ $row['nip'] }}</span>
                                            @if($row['jabatan'])
                                                <span class="mx-1">•</span>
                                                <span class="badge badge-light border">{{ $row['jabatan'] }}</span>
                                            @endif
                                        </div>
                                        @if($row['subjects']->isNotEmpty())
                                            <div class="mt-1">
                                                @foreach($row['subjects']->take(2) as $subj)
                                                    <span class="badge badge-primary-subtle text-primary mr-1 small">{{ $subj }}</span>
                                                @endforeach
                                                @if($row['subjects']->count() > 2)
                                                    <span class="text-muted small">+{{ $row['subjects']->count() - 2 }}</span>
                                                @endif
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="text-center">
                                @if($hasJournals)
                                    <span class="badge badge-success-soft px-2 py-1 font-weight-bold">
                                        <i class="fas fa-check-circle mr-1"></i> Aktif ({{ $row['total_journals'] }})
                                    </span>
                                @else
                                    <span class="badge badge-danger-soft px-2 py-1">
                                        <i class="fas fa-times-circle mr-1"></i> Belum Mengisi
                                    </span>
                                @endif
                            </td>
                            <td class="text-center">
                                <span class="badge badge-light border font-weight-bold">{{ $row['teaching_count'] }}</span>
                                <small class="d-block text-muted">Sesi</small>
                            </td>
                            <td class="text-center">
                                <span class="badge badge-light border font-weight-bold">{{ $row['homeroom_count'] }}</span>
                                <small class="d-block text-muted">Sesi</small>
                            </td>
                            <td class="text-center">
                                <span class="font-weight-bold text-dark">{{ $row['total_hours'] }}</span>
                                <small class="d-block text-muted">Jam</small>
                            </td>
                            <td class="text-center">
                                @if($row['avg_attendance'])
                                    @php
                                        $attClass = $row['avg_attendance'] >= 90 ? 'text-success font-weight-bold' : ($row['avg_attendance'] >= 75 ? 'text-warning font-weight-bold' : 'text-danger font-weight-bold');
                                    @endphp
                                    <span class="{{ $attClass }}">{{ $row['avg_attendance'] }}%</span>
                                    <small class="d-block text-muted">Kehadiran</small>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if($row['last_journal_date'])
                                    <span class="small font-weight-bold text-dark d-block">
                                        {{ \Carbon\Carbon::parse($row['last_journal_date'])->translatedFormat('d M Y') }}
                                    </span>
                                    <small class="text-muted">{{ \Carbon\Carbon::parse($row['last_journal_date'])->diffForHumans() }}</small>
                                @else
                                    <span class="text-muted small">-</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <div class="btn-group btn-group-sm">
                                    @if($userId)
                                        {{-- Quick Preview Modal Button --}}
                                        <button type="button" class="btn btn-light border text-primary btn-preview-journal"
                                                data-user-id="{{ $userId }}"
                                                data-toggle="tooltip" title="Lihat Jurnal Guru">
                                            <i class="fas fa-eye"></i>
                                        </button>

                                        {{-- Dedicated Show Page Link --}}
                                        <a href="{{ route('admin.monitoring-jurnal.show', [$userId, 'dari' => $dari, 'sampai' => $sampai]) }}"
                                           class="btn btn-light border text-info"
                                           data-toggle="tooltip" title="Halaman Detail Jurnal">
                                            <i class="fas fa-external-link-alt"></i>
                                        </a>

                                        {{-- Export PDF Button --}}
                                        <a href="{{ route('admin.monitoring-jurnal.export-teacher', [$userId, 'dari' => $dari, 'sampai' => $sampai]) }}"
                                           class="btn btn-light border text-danger"
                                           data-toggle="tooltip" title="Unduh PDF EKIN/SKP Guru Ini" target="_blank">
                                            <i class="fas fa-file-pdf"></i>
                                        </a>
                                    @else
                                        <span class="text-muted small" title="User belum terhubung">N/A</span>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center py-5 text-muted">
                                <i class="fas fa-inbox fa-3x mb-3 text-secondary d-block"></i>
                                <h6 class="font-weight-bold">Tidak ada data guru yang sesuai kriteria pencarian</h6>
                                <p class="small mb-3">Coba ubah filter rentang tanggal, status pengisian, atau kata kunci pencarian.</p>
                                <a href="{{ route('admin.monitoring-jurnal.index') }}" class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-sync-alt mr-1"></i> Reset Filter
                                </a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Modal Quick Preview Jurnal Guru --}}
<div class="modal fade" id="journalPreviewModal" tabindex="-1" role="dialog" aria-labelledby="journalPreviewModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable" role="document">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 12px; overflow: hidden;">
            <div class="modal-header bg-gradient-primary text-white border-0 py-3">
                <div class="d-flex align-items-center">
                    <img src="" id="modalTeacherPhoto" class="rounded-circle mr-3 border border-white" style="width: 44px; height: 44px; object-fit: cover;">
                    <div>
                        <h5 class="modal-title font-weight-bold mb-0" id="modalTeacherName">Nama Guru</h5>
                        <small class="text-white-50" id="modalTeacherMeta">NIP: - • Guru</small>
                    </div>
                </div>
                <button type="button" class="close text-white opacity-75" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <div class="modal-body p-4 bg-light">
                {{-- Quick Stats Row in Modal --}}
                <div class="row g-2 mb-3" id="modalStatsContainer">
                    <div class="col-3">
                        <div class="card border-0 shadow-xs text-center p-2">
                            <span class="text-muted small d-block">Total Jurnal</span>
                            <strong class="h5 mb-0 text-dark" id="modalStatTotal">0</strong>
                        </div>
                    </div>
                    <div class="col-3">
                        <div class="card border-0 shadow-xs text-center p-2">
                            <span class="text-muted small d-block">Mengajar</span>
                            <strong class="h5 mb-0 text-primary" id="modalStatTeaching">0</strong>
                        </div>
                    </div>
                    <div class="col-3">
                        <div class="card border-0 shadow-xs text-center p-2">
                            <span class="text-muted small d-block">Wali Kelas</span>
                            <strong class="h5 mb-0 text-success" id="modalStatHomeroom">0</strong>
                        </div>
                    </div>
                    <div class="col-3">
                        <div class="card border-0 shadow-xs text-center p-2">
                            <span class="text-muted small d-block">Total Jam</span>
                            <strong class="h5 mb-0 text-info" id="modalStatHours">0</strong>
                        </div>
                    </div>
                </div>

                {{-- Loading Spinner --}}
                <div id="modalLoading" class="text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="sr-only">Memuat data...</span>
                    </div>
                    <p class="text-muted small mt-2">Mengambil riwayat jurnal guru...</p>
                </div>

                {{-- Entries Timeline Container --}}
                <div id="modalEntriesContainer" class="d-none">
                    <div id="modalEntriesList" class="journal-timeline">
                        {{-- Injected via JS --}}
                    </div>
                </div>

                {{-- Empty State in Modal --}}
                <div id="modalEmptyState" class="text-center py-4 d-none">
                    <i class="far fa-clipboard fa-3x text-muted mb-2 d-block"></i>
                    <p class="text-muted font-weight-bold mb-1">Belum Ada Jurnal</p>
                    <small class="text-muted">Guru ini belum menginput jurnal pada rentang tanggal terpilih.</small>
                </div>
            </div>

            <div class="modal-footer bg-white border-top py-2 px-3 justify-content-between">
                <div>
                    <span class="text-muted small">Periode: <strong>{{ \Carbon\Carbon::parse($dari)->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($sampai)->format('d/m/Y') }}</strong></span>
                </div>
                <div>
                    <a href="#" id="modalBtnExport" class="btn btn-sm btn-danger shadow-xs" target="_blank">
                        <i class="fas fa-file-pdf mr-1"></i> Unduh PDF Guru
                    </a>
                    <a href="#" id="modalBtnDetail" class="btn btn-sm btn-outline-primary ml-1">
                        <i class="fas fa-external-link-alt mr-1"></i> Halaman Penuh
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@stop

@section('css')
<style>
    .monitoring-journal-page {
        padding-bottom: 2rem;
    }

    /* Hero Banner */
    .hero-banner {
        background: linear-gradient(135deg, #1e3a8a 0%, #2563eb 60%, #3b82f6 100%);
        border-radius: 12px;
        position: relative;
        overflow: hidden;
    }
    .hero-banner::after {
        content: '';
        position: absolute;
        top: -40px;
        right: -40px;
        width: 220px;
        height: 220px;
        background: radial-gradient(circle, rgba(255,255,255,0.18) 0%, rgba(255,255,255,0) 70%);
        border-radius: 50%;
        pointer-events: none;
    }
    .hero-kicker {
        display: inline-block;
        padding: 4px 12px;
        font-size: 0.75rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: #93c5fd;
        background: rgba(255,255,255,0.12);
        border: 1px solid rgba(255,255,255,0.22);
        border-radius: 20px;
        backdrop-filter: blur(4px);
    }
    .hero-desc {
        color: rgba(255,255,255,0.85);
        font-size: 0.92rem;
        max-width: 680px;
        line-height: 1.5;
    }
    .btn-hero-action {
        height: 40px;
        padding: 0 16px;
        font-weight: 600;
        border-radius: 8px;
        font-size: 0.88rem;
        display: inline-flex;
        align-items: center;
        transition: all 0.2s ease;
    }
    .btn-hero-white {
        background: #ffffff;
        color: #1e3a8a !important;
        border: 1px solid #ffffff;
    }
    .btn-hero-white:hover {
        background: #f1f5f9;
        color: #1d4ed8 !important;
        transform: translateY(-1px);
    }
    .btn-hero-glass {
        background: rgba(255, 255, 255, 0.15);
        color: #ffffff !important;
        border: 1px solid rgba(255, 255, 255, 0.35);
        backdrop-filter: blur(4px);
    }
    .btn-hero-glass:hover {
        background: rgba(255, 255, 255, 0.25);
        color: #ffffff !important;
    }

    /* Stat Cards */
    .stat-card {
        border-radius: 12px;
        border: 1px solid #eef2f6 !important;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .stat-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 16px rgba(0,0,0,0.06) !important;
    }
    .stat-label {
        font-size: 0.78rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.4px;
    }
    .stat-value {
        font-size: 1.65rem;
    }
    .stat-icon-box {
        width: 48px;
        height: 48px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .bg-blue-soft { background-color: #eff6ff; }
    .bg-teal-soft { background-color: #f0fdf4; }
    .bg-red-soft { background-color: #fef2f2; }
    .bg-purple-soft { background-color: #f5f3ff; }

    /* Filter Card */
    .filter-card {
        border-radius: 12px;
        border: 1px solid #eef2f6;
    }
    .filter-field-label {
        font-size: 0.78rem;
        font-weight: 600;
        color: #475569;
        margin-bottom: 4px;
        text-transform: uppercase;
        letter-spacing: 0.3px;
    }
    .filter-input {
        height: 40px;
        border-radius: 8px;
        border: 1px solid #cbd5e1;
        font-size: 0.88rem;
    }
    .filter-btn-apply {
        height: 40px;
        border-radius: 8px;
        font-weight: 600;
        font-size: 0.88rem;
    }

    /* Table & Avatars */
    .table-card {
        border-radius: 12px;
        overflow: hidden;
        border: 1px solid #eef2f6;
    }
    .monitoring-table th {
        font-size: 0.8rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.4px;
        border-top: none;
        border-bottom: 2px solid #e2e8f0;
        padding: 12px 14px;
    }
    .monitoring-table td {
        padding: 12px 14px;
        vertical-align: middle;
        border-top: 1px solid #f1f5f9;
    }
    .teacher-avatar {
        width: 44px;
        height: 44px;
        border-radius: 50%;
        object-fit: cover;
        border: 2px solid #e2e8f0;
    }
    .teacher-name {
        font-size: 0.94rem;
        color: #1e293b;
    }

    /* Soft Badges */
    .badge-success-soft {
        background-color: #dcfce7;
        color: #166534;
        border: 1px solid #bbf7d0;
        border-radius: 6px;
    }
    .badge-danger-soft {
        background-color: #fee2e2;
        color: #991b1b;
        border: 1px solid #fecaca;
        border-radius: 6px;
    }
    .badge-primary-subtle {
        background-color: #eff6ff;
        border: 1px solid #bfdbfe;
        border-radius: 4px;
    }

    /* Modal Timeline */
    .journal-timeline {
        display: flex;
        flex-direction: column;
        gap: 12px;
    }
    .timeline-item {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        padding: 14px;
        transition: border-color 0.2s ease;
    }
    .timeline-item:hover {
        border-color: #93c5fd;
    }
</style>
@stop

@section('js')
<script>
    $(function () {
        $('[data-toggle="tooltip"]').tooltip();

        // AJAX Quick Preview
        $('.btn-preview-journal').on('click', function () {
            var userId = $(this).data('user-id');
            var dari = $('input[name="dari"]').val();
            var sampai = $('input[name="sampai"]').val();

            $('#journalPreviewModal').modal('show');
            $('#modalLoading').removeClass('d-none');
            $('#modalEntriesContainer').addClass('d-none');
            $('#modalEmptyState').addClass('d-none');

            // Set link destinations
            var baseUrl = "{{ url('admin/monitoring-jurnal') }}";
            $('#modalBtnExport').attr('href', baseUrl + '/' + userId + '/export?dari=' + dari + '&sampai=' + sampai);
            $('#modalBtnDetail').attr('href', baseUrl + '/' + userId + '?dari=' + dari + '&sampai=' + sampai);

            $.ajax({
                url: baseUrl + '/' + userId + '/entries',
                type: 'GET',
                data: { dari: dari, sampai: sampai },
                dataType: 'json',
                success: function (res) {
                    $('#modalLoading').addClass('d-none');

                    if (!res.success) {
                        alert('Gagal mengambil data jurnal.');
                        return;
                    }

                    // Populate teacher header
                    $('#modalTeacherName').text(res.teacher.name);
                    $('#modalTeacherMeta').text('NIP: ' + res.teacher.nip + ' • ' + res.teacher.role);
                    if (res.teacher.foto) {
                        $('#modalTeacherPhoto').attr('src', res.teacher.foto);
                    } else {
                        $('#modalTeacherPhoto').attr('src', 'https://ui-avatars.com/api/?name=' + encodeURIComponent(res.teacher.name) + '&background=e2e8f0&color=475569');
                    }

                    // Populate stats
                    $('#modalStatTotal').text(res.stats.total);
                    $('#modalStatTeaching').text(res.stats.teaching);
                    $('#modalStatHomeroom').text(res.stats.homeroom);
                    $('#modalStatHours').text(res.stats.hours + ' Jam');

                    // Populate entries
                    if (!res.entries || res.entries.length === 0) {
                        $('#modalEmptyState').removeClass('d-none');
                    } else {
                        $('#modalEntriesContainer').removeClass('d-none');
                        var html = '';
                        $.each(res.entries, function (i, item) {
                            var badgeClass = item.jenis === 'mengajar' ? 'badge-primary' : 'badge-success';
                            var attHtml = '';
                            if (item.attendance && item.attendance.total > 0) {
                                attHtml = '<div class="mt-2 p-2 bg-light rounded small d-flex justify-content-between">' +
                                    '<span><i class="fas fa-users mr-1 text-primary"></i> Kehadiran: <strong>' + (item.attendance.hadir || 0) + '/' + item.attendance.total + ' (' + (item.attendance.persentase || 0) + '%)</strong></span>' +
                                    '<span>S: ' + (item.attendance.sakit || 0) + ' | I: ' + (item.attendance.izin || 0) + ' | A: ' + (item.attendance.alpa || 0) + '</span>' +
                                    '</div>';
                            }

                            html += '<div class="timeline-item shadow-xs">' +
                                '<div class="d-flex justify-content-between align-items-center mb-1">' +
                                    '<div>' +
                                        '<span class="badge ' + badgeClass + ' mr-2">' + item.jenis_label + '</span>' +
                                        '<strong class="text-dark">' + item.hari + ', ' + item.tanggal + '</strong>' +
                                    '</div>' +
                                    '<span class="badge badge-light border text-muted"><i class="far fa-clock mr-1"></i>' + item.durasi + '</span>' +
                                '</div>' +
                                '<div class="text-muted small mb-2">' +
                                    '<span><i class="fas fa-school mr-1"></i>Kelas: <strong>' + item.kelas + '</strong></span>' +
                                    (item.mapel && item.mapel !== '-' ? '<span class="mx-2">•</span><span><i class="fas fa-book mr-1"></i>Mapel: <strong>' + item.mapel + '</strong></span>' : '') +
                                '</div>' +
                                '<div class="p-2 rounded" style="background-color: #f8fafc; border-left: 3px solid #3b82f6;">' +
                                    '<div class="font-weight-bold text-dark mb-1">' + item.topik + '</div>' +
                                    '<div class="small text-secondary">' + item.kegiatan + '</div>' +
                                '</div>' +
                                attHtml +
                                (item.asesmen ? '<div class="mt-2 small text-muted"><strong>Asesmen/Tindak Lanjut:</strong> ' + item.asesmen + '</div>' : '') +
                            '</div>';
                        });
                        $('#modalEntriesList').html(html);
                    }
                },
                error: function () {
                    $('#modalLoading').addClass('d-none');
                    alert('Terjadi kesalahan saat menghubungi server.');
                }
            });
        });
    });
</script>
@stop
