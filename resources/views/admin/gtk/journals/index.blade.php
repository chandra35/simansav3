@extends('adminlte::page')
@section('title', 'Jurnal Guru')
@section('content')
@php
    $periode = $periode ?? 'bulan_ini';
    $dariCarbon = $dari instanceof \Carbon\Carbon ? $dari : \Carbon\Carbon::parse($dari);
    $sampaiCarbon = $sampai instanceof \Carbon\Carbon ? $sampai : \Carbon\Carbon::parse($sampai);
    $dariVal = $dariCarbon->toDateString();
    $sampaiVal = $sampaiCarbon->toDateString();

    if ($periode === 'hari_ini') {
        $periodHumanLabel = 'Hari Ini (' . $dariCarbon->translatedFormat('d F Y') . ')';
    } elseif ($periode === 'minggu_ini') {
        $periodHumanLabel = 'Minggu Ini (' . $dariCarbon->translatedFormat('d M') . ' - ' . $sampaiCarbon->translatedFormat('d M Y') . ')';
    } elseif ($periode === 'bulan_ini') {
        $periodHumanLabel = 'Bulan Ini (' . $dariCarbon->translatedFormat('F Y') . ')';
    } elseif ($periode === 'tahun_ini') {
        $periodHumanLabel = 'Tahun Ajaran (' . $dariCarbon->translatedFormat('d M Y') . ' - ' . $sampaiCarbon->translatedFormat('d M Y') . ')';
    } else {
        $periodHumanLabel = 'Rentang Tanggal (' . $dariCarbon->translatedFormat('d M Y') . ' - ' . $sampaiCarbon->translatedFormat('d M Y') . ')';
    }
@endphp
<div class="journal-hero shadow-sm">
 <div class="hero-content">
  <div class="hero-kicker">
   <i class="fas fa-graduation-cap mr-1"></i> Pusat dokumentasi guru
  </div>
  <h1>Jurnal Mengajar, Lebih Terarah.</h1>
  <p>Catat kegiatan, pantau kontribusi, dan siapkan dokumen EKIN/SKP dalam satu tempat.</p>
 </div>
 <div class="hero-actions">
  <button type="button" class="btn btn-hero-primary js-compose" data-toggle="modal" data-target="#modal-create-journal">
   <i class="fas fa-plus-circle mr-1.5"></i> Tulis Jurnal
  </button>
  <a class="btn btn-hero-secondary" href="{{ route('admin.gtk.journals.export', request()->query()) }}" target="_blank">
   <i class="fas fa-file-pdf mr-1.5 text-danger-soft"></i> PDF EKIN/SKP
  </a>
 </div>
</div>

<div class="row journal-stats">
 <div class="col-sm-6 col-xl-3">
  <div class="stat-card">
   <div class="stat-icon-wrapper stat-icon-blue">
    <i class="fas fa-book"></i>
   </div>
   <div class="stat-info">
    <span class="stat-label">Total Entri</span>
    <strong class="stat-value">{{ $summary['total'] }}</strong>
    <small class="stat-desc">pada periode dipilih</small>
   </div>
  </div>
 </div>
 <div class="col-sm-6 col-xl-3">
  <div class="stat-card">
   <div class="stat-icon-wrapper stat-icon-teal">
    <i class="fas fa-chalkboard-teacher"></i>
   </div>
   <div class="stat-info">
    <span class="stat-label">Mengajar</span>
    <strong class="stat-value">{{ $summary['teaching'] }}</strong>
    <small class="stat-desc">catatan pembelajaran</small>
   </div>
  </div>
 </div>
 <div class="col-sm-6 col-xl-3">
  <div class="stat-card">
   <div class="stat-icon-wrapper stat-icon-amber">
    <i class="fas fa-users"></i>
   </div>
   <div class="stat-info">
    <span class="stat-label">Wali Kelas</span>
    <strong class="stat-value">{{ $summary['homeroom'] }}</strong>
    <small class="stat-desc">pendampingan siswa</small>
   </div>
  </div>
 </div>
 <div class="col-sm-6 col-xl-3">
  <div class="stat-card">
   @if(($summary['minutes'] ?? 0) > 0)
    <div class="stat-icon-wrapper stat-icon-green">
     <i class="fas fa-clock"></i>
    </div>
    <div class="stat-info">
     <span class="stat-label">Waktu Tercatat</span>
     <strong class="stat-value">{{ intdiv($summary['minutes'], 60) }}j {{ $summary['minutes'] % 60 }}m</strong>
     <small class="stat-desc">{{ isset($summary['avg_attendance']) && $summary['avg_attendance'] !== null ? 'Rerata hadir '.$summary['avg_attendance'].'%' : 'akumulasi durasi' }}</small>
    </div>
   @elseif(isset($summary['avg_attendance']) && $summary['avg_attendance'] !== null)
    <div class="stat-icon-wrapper stat-icon-green">
     <i class="fas fa-user-check"></i>
    </div>
    <div class="stat-info">
     <span class="stat-label">Presensi Siswa</span>
     <strong class="stat-value">{{ $summary['avg_attendance'] }}%</strong>
     <small class="stat-desc">rerata kehadiran kelas</small>
    </div>
   @else
    <div class="stat-icon-wrapper stat-icon-green">
     <i class="fas fa-clock"></i>
    </div>
    <div class="stat-info">
     <span class="stat-label">Waktu Tercatat</span>
     <strong class="stat-value">0j 0m</strong>
     <small class="stat-desc">akumulasi durasi</small>
    </div>
   @endif
  </div>
 </div>
</div>

<div class="card filter-card border-0 shadow-sm mb-4">
 <div class="card-body p-3 p-md-4">
  <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 pb-2 border-bottom">
   <div class="d-flex align-items-center">
    <div class="filter-icon mr-2.5">
     <i class="fas fa-calendar-alt"></i>
    </div>
    <div>
     <h6 class="font-weight-bold text-dark mb-0" style="font-size: 0.95rem;">Periode Jurnal: <span class="text-primary">{{ $periodHumanLabel }}</span></h6>
     <div class="small text-muted">Pilih cepat periode aktivitas atau gunakan rentang tanggal kustom untuk merekap EKIN/SKP.</div>
    </div>
   </div>
   <div class="d-flex align-items-center mt-2 mt-md-0">
    <button type="button" class="btn btn-success btn-sm font-weight-bold shadow-sm rounded-pill px-3 py-1.5" data-toggle="modal" data-target="#modal-create-journal">
     <i class="fas fa-plus mr-1"></i> Tulis Jurnal Baru
    </button>
   </div>
  </div>

  <!-- Quick Period Selection Pills -->
  <div class="d-flex flex-wrap align-items-center mb-3" style="gap: 8px;">
   <span class="small font-weight-bold text-muted mr-1"><i class="fas fa-bolt text-warning mr-1"></i> Periode Cepat:</span>
   <a href="{{ route('admin.gtk.journals.index', array_merge(request()->except(['dari', 'sampai', 'page', 'periode']), ['periode' => 'hari_ini'])) }}" class="btn btn-sm {{ $periode === 'hari_ini' ? 'btn-primary font-weight-bold shadow-sm' : 'btn-outline-secondary bg-white' }} rounded-pill px-3 py-1">
    Hari Ini
   </a>
   <a href="{{ route('admin.gtk.journals.index', array_merge(request()->except(['dari', 'sampai', 'page', 'periode']), ['periode' => 'minggu_ini'])) }}" class="btn btn-sm {{ $periode === 'minggu_ini' ? 'btn-primary font-weight-bold shadow-sm' : 'btn-outline-secondary bg-white' }} rounded-pill px-3 py-1">
    Minggu Ini
   </a>
   <a href="{{ route('admin.gtk.journals.index', array_merge(request()->except(['dari', 'sampai', 'page', 'periode']), ['periode' => 'bulan_ini'])) }}" class="btn btn-sm {{ $periode === 'bulan_ini' ? 'btn-primary font-weight-bold shadow-sm' : 'btn-outline-secondary bg-white' }} rounded-pill px-3 py-1">
    Bulan Ini
   </a>
   <a href="{{ route('admin.gtk.journals.index', array_merge(request()->except(['dari', 'sampai', 'page', 'periode']), ['periode' => 'tahun_ini'])) }}" class="btn btn-sm {{ $periode === 'tahun_ini' ? 'btn-primary font-weight-bold shadow-sm' : 'btn-outline-secondary bg-white' }} rounded-pill px-3 py-1">
    Tahun Ini
   </a>
   <button type="button" class="btn btn-sm {{ $periode === 'rentang' ? 'btn-primary font-weight-bold shadow-sm' : 'btn-outline-secondary bg-white' }} rounded-pill px-3 py-1" onclick="$('#custom-date-range-row').toggleClass('d-none')">
    <i class="far fa-calendar-alt mr-1"></i> Rentang Tanggal {{ $periode === 'rentang' ? '(Aktif)' : '...' }}
   </button>
  </div>

  <form method="GET" action="{{ route('admin.gtk.journals.index') }}">
   <input type="hidden" name="periode" value="rentang">
   <div class="row align-items-end {{ $periode === 'rentang' ? '' : 'd-none' }}" id="custom-date-range-row">
    <div class="col-md-3 col-sm-6 form-group mb-2 mb-md-0">
     <label class="filter-label"><i class="fas fa-layer-group text-muted mr-1"></i> Jenis Jurnal</label>
     <select name="jenis" class="custom-select filter-control">
      <option value="">Semua Jurnal</option>
      <option value="mengajar" @selected($jenis === 'mengajar')>Jurnal Mengajar</option>
      @if($waliClasses->isNotEmpty())
       <option value="wali_kelas" @selected($jenis === 'wali_kelas')>Jurnal Wali Kelas</option>
      @endif
     </select>
    </div>
    <div class="col-md-3 col-sm-6 form-group mb-2 mb-md-0">
     <label class="filter-label"><i class="far fa-calendar-alt text-muted mr-1"></i> Dari Tanggal</label>
     <input type="date" name="dari" value="{{ $dariVal }}" class="form-control filter-control">
    </div>
    <div class="col-md-3 col-sm-6 form-group mb-2 mb-md-0">
     <label class="filter-label"><i class="far fa-calendar-check text-muted mr-1"></i> Sampai Tanggal</label>
     <input type="date" name="sampai" value="{{ $sampaiVal }}" class="form-control filter-control">
    </div>
    <div class="col-md-3 col-sm-6 form-group mb-0">
     <div class="d-flex align-items-center" style="gap: 8px;">
      <button type="submit" class="btn btn-primary btn-filter-submit">
       <i class="fas fa-filter mr-1.5"></i> Terapkan Rentang
      </button>
      <a href="{{ route('admin.gtk.journals.index') }}" class="btn btn-light btn-filter-reset" title="Reset filter">
       <i class="fas fa-undo-alt mr-1"></i> Reset
      </a>
     </div>
    </div>
   </div>

   @if($periode !== 'rentang')
   <!-- Quick Jenis Toggle when in Preset Mode -->
   <div class="d-flex flex-wrap align-items-center pt-2.5 border-top">
    <span class="small text-muted mr-2 font-weight-bold"><i class="fas fa-layer-group text-primary mr-1"></i> Filter Kategori:</span>
    <div class="btn-group btn-group-toggle btn-group-sm" data-toggle="buttons">
      <a href="{{ route('admin.gtk.journals.index', array_merge(request()->except(['jenis', 'page']), [])) }}" class="btn {{ empty($jenis) ? 'btn-secondary active font-weight-bold' : 'btn-outline-secondary bg-white' }} px-3 py-1">
        Semua
      </a>
      <a href="{{ route('admin.gtk.journals.index', array_merge(request()->except(['page']), ['jenis' => 'mengajar'])) }}" class="btn {{ $jenis === 'mengajar' ? 'btn-primary active font-weight-bold' : 'btn-outline-secondary bg-white' }} px-3 py-1">
        Mengajar
      </a>
      @if($waliClasses->isNotEmpty())
      <a href="{{ route('admin.gtk.journals.index', array_merge(request()->except(['page']), ['jenis' => 'wali_kelas'])) }}" class="btn {{ $jenis === 'wali_kelas' ? 'btn-info active font-weight-bold' : 'btn-outline-secondary bg-white' }} px-3 py-1">
        Wali Kelas
      </a>
      @endif
    </div>
   </div>
   @endif
  </form>
 </div>
</div>

<div class="row">
 <div class="col-12">
  <div class="card history-card border-0 shadow-sm">
   <div class="card-header bg-white border-0 pt-4 px-4 pb-2">
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-2">
     <div>
      <span class="text-info font-weight-bold" style="font-size:0.75rem; letter-spacing:0.06em;"><i class="fas fa-history mr-1"></i> RIWAYAT</span>
      <h3 class="mt-1 mb-0 font-weight-bold" style="font-size:1.35rem; color:#1e293b;">Aktivitas jurnal</h3>
     </div>
     <div class="d-flex align-items-center" style="gap: 8px;">
      <span class="badge badge-pill badge-info px-3 py-2 font-weight-bold">{{ $journals->total() }} entri</span>
      <button type="button" class="btn btn-outline-success btn-sm rounded-pill font-weight-bold px-3 py-1.5" data-toggle="modal" data-target="#modal-create-journal">
       <i class="fas fa-plus mr-1"></i> Tulis Jurnal
      </button>
     </div>
    </div>
@if($journals->count() > 0)
<div class="mt-2 mb-1">
    <div class="input-group input-group-sm">
        <div class="input-group-prepend">
            <span class="input-group-text bg-light border-right-0"><i class="fas fa-search text-muted"></i></span>
        </div>
        <input type="text" id="journal-quick-filter" class="form-control bg-light border-left-0" placeholder="Cari cepat topik, kegiatan, kelas, atau mapel...">
        <div class="input-group-append d-none" id="clear-quick-filter-wrapper">
            <button class="btn btn-outline-light text-muted border border-left-0" type="button" id="btn-clear-quick-filter"><i class="fas fa-times"></i></button>
        </div>
    </div>
</div>
@endif
</div>
<div id="journal-items-container">
@forelse($journals as $journal)
    <div class="journal-item mx-3 mx-md-4 py-3 border-top js-journal-row" data-journal-id="{{ $journal->id }}">
        <script type="application/json" class="js-journal-data">{!! json_encode([
            'id' => $journal->id,
            'tanggal_formatted' => $journal->tanggal->translatedFormat('l, d F Y'),
            'hari' => $journal->tanggal->translatedFormat('l'),
            'tanggal_short' => $journal->tanggal->format('d/m/Y'),
            'jenis' => $journal->jenis,
            'jenis_label' => $journal->jenis === 'mengajar' ? 'Mengajar' : 'Wali Kelas',
            'durasi_menit' => $journal->durasi_menit,
            'topik' => $journal->topik,
            'kegiatan' => $journal->kegiatan,
            'asesmen' => $journal->asesmen_tindak_lanjut,
            'catatan' => $journal->catatan,
            'kelas' => $journal->kelas_snapshot ?: '-',
            'mapel' => $journal->mapel_snapshot ?: '-',
            'attendance' => $journal->attendance_summary,
            'edit_url' => route('admin.gtk.journals.edit', $journal),
            'delete_url' => route('admin.gtk.journals.destroy', $journal)
        ]) !!}</script>

        <div class="journal-date">
            <span class="day-abbr text-muted font-weight-bold">{{ strtoupper($journal->tanggal->translatedFormat('D')) }}</span>
            <strong class="date-number">{{ $journal->tanggal->format('d') }}</strong>
            <span class="month-abbr">{{ $journal->tanggal->translatedFormat('M') }}</span>
            <small class="year-num">{{ $journal->tanggal->format('Y') }}</small>
        </div>
        <div class="journal-main pl-1">
            <div class="d-flex flex-wrap align-items-center mb-1">
                <span class="type-badge {{ $journal->jenis === 'mengajar' ? 'teaching' : 'homeroom' }}">
                    <i class="fas {{ $journal->jenis === 'mengajar' ? 'fa-chalkboard-teacher' : 'fa-user-shield' }} mr-1"></i>
                    {{ $journal->jenis === 'mengajar' ? 'Mengajar' : 'Wali kelas' }}
                </span>
                @if($journal->durasi_menit)
                    <span class="badge badge-light border text-secondary font-weight-normal ml-2">
                        <i class="far fa-clock mr-1 text-primary"></i>{{ $journal->durasi_menit }}m
                    </span>
                @endif
                @if(!empty($journal->asesmen_tindak_lanjut))
                    <span class="badge badge-light border text-info font-weight-normal ml-1 d-none d-sm-inline-flex align-items-center" title="Ada catatan asesmen/tindak lanjut">
                        <i class="fas fa-clipboard-check mr-1"></i>Asesmen
                    </span>
                @endif
                @if(!empty($journal->catatan))
                    <span class="badge badge-light border text-warning font-weight-normal ml-1 d-none d-sm-inline-flex align-items-center" title="Ada catatan tambahan">
                        <i class="far fa-comment-dots mr-1"></i>Catatan
                    </span>
                @endif
            </div>

            <h5 class="journal-title mb-1">
                <a href="javascript:void(0)" class="js-open-detail text-dark text-decoration-none">
                    {{ $journal->topik }}
                </a>
            </h5>

            <p class="journal-kegiatan text-muted mb-2 font-weight-normal">
                {{ \Illuminate\Support\Str::limit($journal->kegiatan, 160) }}
            </p>

            <div class="journal-meta d-flex flex-wrap align-items-center text-muted small mb-2">
                <span class="context mr-3">
                    <i class="fas fa-school mr-1 text-secondary"></i>{{ $journal->kelas_snapshot ?: 'Kelas tidak dicatat' }}
                    @if($journal->mapel_snapshot)
                        <span class="mx-1 text-black-50">·</span>
                        <i class="fas fa-book-open mr-1 text-secondary"></i>{{ $journal->mapel_snapshot }}
                    @endif
                </span>
            </div>

            @if(!empty($journal->attendance_summary))
                @php $att = $journal->attendance_summary; @endphp
                <div class="att-summary-chip d-flex flex-wrap align-items-center">
                    <a href="javascript:void(0)" class="js-open-detail badge badge-light border text-dark font-weight-bold mr-1 py-1 px-2 text-decoration-none shadow-none">
                        <i class="fas fa-user-check text-success mr-1"></i> Hadir: {{ $att['hadir'] ?? 0 }}/{{ $att['total'] ?? 0 }} ({{ $att['persentase'] ?? 100 }}%)
                    </a>
                    @if(!empty($att['absent_students']))
                        <a href="javascript:void(0)" class="js-open-detail badge badge-light border text-danger py-1 px-2 text-decoration-none shadow-none" title="{{ collect($att['absent_students'])->map(fn($s) => $s['nama'].' ('.ucfirst($s['status']).')')->implode(', ') }}">
                            <i class="fas fa-user-times mr-1"></i> {{ count($att['absent_students']) }} tidak hadir <i class="fas fa-chevron-right ml-1 font-weight-normal" style="font-size:0.6rem;"></i>
                        </a>
                    @else
                        <span class="badge badge-light border text-success py-1 px-2">
                            <i class="fas fa-check-circle mr-1"></i> 100% hadir
                        </span>
                    @endif
                </div>
            @endif
        </div>
        <div class="journal-actions">
            <button type="button" class="btn btn-sm btn-light border js-open-detail" title="Lihat detail lengkap">
                <i class="fas fa-eye text-info"></i>
            </button>
            <a class="btn btn-sm btn-light border" title="Edit jurnal" href="{{ route('admin.gtk.journals.edit', $journal) }}">
                <i class="fas fa-pen text-primary"></i>
            </a>
            <form method="POST" action="{{ route('admin.gtk.journals.destroy', $journal) }}" onsubmit="return confirm('Hapus jurnal ini?')">
                @csrf
                @method('DELETE')
                <button class="btn btn-sm btn-light border" title="Hapus jurnal">
                    <i class="fas fa-trash text-danger"></i>
                </button>
            </form>
        </div>
    </div>
@empty
    <div class="empty-state">
        <div class="empty-icon"><i class="fas fa-book-reader"></i></div>
        <h4>Belum ada jurnal pada periode ini</h4>
        <p>Mulai dokumentasikan aktivitas mengajar atau tugas wali kelas untuk membangun riwayat EKIN/SKP yang lengkap.</p>
        <button type="button" class="btn btn-primary js-compose" data-toggle="modal" data-target="#modal-create-journal">
            <i class="fas fa-plus mr-1"></i> Tulis jurnal pertama
        </button>
    </div>
@endforelse
</div>
<div id="quick-filter-empty" class="text-center py-5 d-none">
    <i class="fas fa-search fa-2x text-muted mb-2"></i>
    <p class="text-muted mb-0">Tidak ada jurnal yang sesuai dengan pencarian cepat.</p>
</div>

@if($journals->hasPages())
    <div class="card-footer bg-white">
        {{ $journals->withQueryString()->links() }}
    </div>
@endif
  </div>
 </div>
</div>

<!-- Modal Create Jurnal Guru -->
<div class="modal fade" id="modal-create-journal" tabindex="-1" role="dialog" aria-labelledby="modalCreateJournalTitle" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable" role="document">
        <div class="modal-content border-0 shadow-lg" style="border-radius:18px; overflow:hidden;">
            <div class="modal-header text-white" style="background:linear-gradient(135deg, #1e3a8a, #0d9488); border-bottom:1px solid rgba(255,255,255,0.1); padding:1.25rem 1.75rem;">
                <div class="d-flex align-items-center">
                    <div class="mr-3" style="width:44px; height:44px; min-width:44px; border-radius:12px; background:rgba(255,255,255,0.18); display:grid; place-items:center; font-size:1.3rem; color:#fff;">
                        <i class="fas fa-pen-nib"></i>
                    </div>
                    <div>
                        <h4 class="modal-title font-weight-bold mb-0 text-white" id="modalCreateJournalTitle" style="font-size:1.2rem;">
                            Tulis Jurnal Hari Ini
                        </h4>
                        <div class="small text-white-50">Lengkapi informasi aktivitas dan sinkronisasi presensi kehadiran siswa.</div>
                    </div>
                </div>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close" style="opacity:0.85;">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form method="POST" action="{{ route('admin.gtk.journals.store') }}" id="form-create-journal">
                @csrf
                <div class="modal-body p-4 bg-light">
                    @if(isset($errors) && $errors->any())
                        <div class="alert alert-danger alert-dismissible fade show mb-3" role="alert">
                            <strong><i class="fas fa-exclamation-triangle mr-1"></i> Mohon periksa isian:</strong>
                            <ul class="mb-0 mt-1 pl-3">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    @endif

                    <div class="row">
                        <!-- Left Column: Journal Form Fields -->
                        <div class="col-lg-6">
                            <div class="bg-white p-3 p-md-4 rounded-lg border shadow-2xs h-100">
                                <h6 class="font-weight-bold text-dark mb-3 border-bottom pb-2">
                                    <i class="fas fa-info-circle text-primary mr-1"></i> Detail Aktivitas
                                </h6>
                                <div class="row">
                                    <div class="col-md-6 form-group">
                                        <label class="font-weight-bold small text-muted">Jenis Jurnal <span class="text-danger">*</span></label>
                                        <select name="jenis" id="journal-type" class="custom-select" required>
                                            <option value="mengajar">Jurnal Mengajar</option>
                                            @if($waliClasses->isNotEmpty())
                                                <option value="wali_kelas">Jurnal Wali Kelas</option>
                                            @endif
                                        </select>
                                    </div>
                                    <div class="col-md-6 form-group">
                                        <label class="font-weight-bold small text-muted">Tanggal Kegiatan <span class="text-danger">*</span></label>
                                        <input type="date" name="tanggal" id="journal-date" value="{{ old('tanggal', now()->toDateString()) }}" class="form-control" required>
                                    </div>
                                </div>

                                <div id="teaching-fields">
                                    <div class="form-group">
                                        <label class="font-weight-bold small text-muted">Jadwal Mengajar <span class="text-danger">*</span></label>
                                        <select name="jadwal_pelajaran_id" id="journal-schedule" class="custom-select">
                                            <option value="">Pilih jadwal mengajar</option>
                                            @foreach($schedules as $schedule)
                                                <option value="{{ $schedule->id }}">
                                                    {{ $schedule->mataPelajaran?->nama_mapel }} · {{ $schedule->kelas?->nama_kelas }} · {{ $schedule->hari_label }} jam {{ $schedule->jam_ke }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <small class="text-muted d-block mt-1">Gunakan jadwal agar mata pelajaran dan kelas tercatat otomatis.</small>
                                    </div>
                                </div>

                                @if($waliClasses->isNotEmpty())
                                    <div id="homeroom-fields" class="d-none">
                                        <div class="form-group">
                                            <label class="font-weight-bold small text-muted">Kelas Wali <span class="text-danger">*</span></label>
                                            <select name="kelas_id" id="journal-homeroom" class="custom-select">
                                                <option value="">Pilih kelas wali</option>
                                                @foreach($waliClasses as $kelas)
                                                    <option value="{{ $kelas->id }}">{{ $kelas->nama_lengkap ?? $kelas->nama_kelas }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                @endif

                                <div class="form-group">
                                    <label class="font-weight-bold small text-muted">Topik / Agenda <span class="text-danger">*</span></label>
                                    <input name="topik" value="{{ old('topik') }}" maxlength="255" class="form-control" placeholder="Contoh: Persamaan kuadrat dan penerapannya" required>
                                </div>

                                <div class="form-group">
                                    <label class="font-weight-bold small text-muted">Kegiatan Pembelajaran / Tugas <span class="text-danger">*</span></label>
                                    <textarea name="kegiatan" rows="4" class="form-control" placeholder="Jelaskan kegiatan utama yang dilakukan..." required>{{ old('kegiatan') }}</textarea>
                                </div>

                                <div class="row">
                                    <div class="col-md-7 form-group">
                                        <label class="font-weight-bold small text-muted">Asesmen / Tindak Lanjut</label>
                                        <textarea name="asesmen_tindak_lanjut" rows="2" class="form-control" placeholder="Hasil penilaian / tindak lanjut...">{{ old('asesmen_tindak_lanjut') }}</textarea>
                                    </div>
                                    <div class="col-md-5 form-group">
                                        <label class="font-weight-bold small text-muted">Durasi (menit)</label>
                                        <input type="number" min="1" max="720" name="durasi_menit" value="{{ old('durasi_menit') }}" class="form-control" placeholder="90">
                                    </div>
                                </div>

                                <div class="form-group mb-0">
                                    <label class="font-weight-bold small text-muted">Catatan Tambahan <span class="text-muted font-weight-normal">(opsional)</span></label>
                                    <textarea name="catatan" rows="2" class="form-control" placeholder="Catatan internal guru...">{{ old('catatan') }}</textarea>
                                </div>
                            </div>
                        </div>

                        <!-- Right Column: Presensi Siswa -->
                        <div class="col-lg-6 mt-3 mt-lg-0">
                            <div class="bg-white p-3 p-md-4 rounded-lg border shadow-2xs h-100 d-flex flex-column">
                                <div class="d-flex justify-content-between align-items-center mb-2 pb-2 border-bottom">
                                    <div>
                                        <span class="font-weight-bold text-dark"><i class="fas fa-user-check text-primary mr-1"></i> Presensi Kehadiran Siswa</span>
                                        <div class="small text-muted" id="att-subtitle">Pilih jadwal atau kelas untuk memuat daftar siswa.</div>
                                    </div>
                                    <div id="att-header-actions" class="d-none">
                                        <button type="button" class="btn btn-sm btn-outline-success rounded-pill font-weight-bold shadow-none py-1 px-3" id="btn-all-present" style="font-size:0.75rem;">
                                            <i class="fas fa-check-double mr-1"></i> Semua Hadir
                                        </button>
                                    </div>
                                </div>

                                <div id="att-source-alert" class="alert alert-info py-1 px-2 small mb-2 d-none">
                                    <i class="fas fa-info-circle mr-1"></i> <span id="att-source-text"></span>
                                </div>

                                <div id="att-stats-pills" class="d-none flex-wrap align-items-center mb-2">
                                    <span class="att-stat-chip stat-h"><span class="att-stat-dot"></span> Hadir: <strong id="stat-hadir" class="ml-1">0</strong></span>
                                    <span class="att-stat-chip stat-s"><span class="att-stat-dot"></span> Sakit: <strong id="stat-sakit" class="ml-1">0</strong></span>
                                    <span class="att-stat-chip stat-i"><span class="att-stat-dot"></span> Izin: <strong id="stat-izin" class="ml-1">0</strong></span>
                                    <span class="att-stat-chip stat-a"><span class="att-stat-dot"></span> Alpa: <strong id="stat-alpa" class="ml-1">0</strong></span>
                                    <span class="att-stat-chip stat-d"><span class="att-stat-dot"></span> Dispen: <strong id="stat-dispen" class="ml-1">0</strong></span>
                                </div>

                                <div id="att-loading" class="text-center py-4 d-none">
                                    <div class="spinner-border spinner-border-sm text-primary" role="status"></div>
                                    <span class="small text-muted ml-2">Menghubungkan presensi siswa...</span>
                                </div>

                                <div id="att-students-list" style="max-height: 480px; overflow-y: auto;" class="pr-1 flex-grow-1">
                                    <div class="text-muted small text-center py-5" id="att-placeholder">
                                        <i class="fas fa-clipboard-list fa-3x mb-2 text-secondary d-block"></i>
                                        Daftar siswa akan otomatis tampil saat jadwal mengajar / kelas dipilih.
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-white px-4 py-3 border-top d-flex justify-content-between">
                    <button type="button" class="btn btn-secondary px-3" data-dismiss="modal">
                        Batal
                    </button>
                    <button type="submit" class="btn btn-success px-4 font-weight-bold">
                        <i class="fas fa-save mr-1"></i> Simpan Jurnal
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Detail Jurnal Guru -->
<div class="modal fade" id="modal-journal-detail" tabindex="-1" role="dialog" aria-labelledby="modalJournalDetailTitle" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable" role="document">
        <div class="modal-content border-0 shadow-lg" style="border-radius:18px; overflow:hidden;">
            <div class="modal-header text-white" style="background:linear-gradient(135deg, #1e293b, #0f172a); border-bottom:1px solid rgba(255,255,255,0.08); padding:1.25rem 1.5rem;">
                <div class="d-flex align-items-center">
                    <div class="mr-3" style="width:44px; height:44px; min-width:44px; border-radius:12px; background:rgba(56,189,248,0.15); display:grid; place-items:center; font-size:1.3rem; color:#38bdf8;">
                        <i class="fas fa-book-open"></i>
                    </div>
                    <div>
                        <div class="d-flex align-items-center mb-1">
                            <span id="modal-journal-type-badge" class="type-badge teaching">
                                <i class="fas fa-chalkboard-teacher mr-1"></i> Mengajar
                            </span>
                            <span id="modal-journal-duration-badge" class="badge badge-light border ml-2 text-secondary font-weight-normal" style="font-size:0.75rem;">
                                <i class="far fa-clock mr-1 text-primary"></i><span id="modal-journal-duration-val">90 menit</span>
                            </span>
                        </div>
                        <h5 class="modal-title font-weight-bold text-white mb-0" id="modalJournalDetailTitle">Detail Jurnal Aktivitas</h5>
                    </div>
                </div>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close" style="opacity:0.8; text-shadow:none;">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <div class="modal-body p-4" style="background:#f8fafc;">
                <!-- Metadata Grid -->
                <div class="row row-cols-2 row-cols-md-4 mb-3" style="margin-left:-6px; margin-right:-6px;">
                    <div class="col-6 col-md-3 px-1 mb-2">
                        <div class="bg-white p-2 rounded-lg border h-100 shadow-2xs">
                            <small class="text-muted text-uppercase font-weight-bold d-block" style="font-size:0.68rem; letter-spacing:0.04em;"><i class="far fa-calendar-alt text-primary mr-1"></i> Tanggal</small>
                            <span id="modal-journal-date" class="font-weight-bold text-dark d-block mt-1" style="font-size:0.83rem;">-</span>
                        </div>
                    </div>
                    <div class="col-6 col-md-3 px-1 mb-2">
                        <div class="bg-white p-2 rounded-lg border h-100 shadow-2xs">
                            <small class="text-muted text-uppercase font-weight-bold d-block" style="font-size:0.68rem; letter-spacing:0.04em;"><i class="fas fa-school text-info mr-1"></i> Kelas</small>
                            <span id="modal-journal-class" class="font-weight-bold text-dark d-block mt-1" style="font-size:0.83rem;">-</span>
                        </div>
                    </div>
                    <div class="col-6 col-md-3 px-1 mb-2">
                        <div class="bg-white p-2 rounded-lg border h-100 shadow-2xs">
                            <small class="text-muted text-uppercase font-weight-bold d-block" style="font-size:0.68rem; letter-spacing:0.04em;"><i class="fas fa-book text-warning mr-1"></i> Mapel</small>
                            <span id="modal-journal-subject" class="font-weight-bold text-dark d-block mt-1 text-truncate" style="font-size:0.83rem;">-</span>
                        </div>
                    </div>
                    <div class="col-6 col-md-3 px-1 mb-2">
                        <div class="bg-white p-2 rounded-lg border h-100 shadow-2xs">
                            <small class="text-muted text-uppercase font-weight-bold d-block" style="font-size:0.68rem; letter-spacing:0.04em;"><i class="fas fa-user-check text-success mr-1"></i> Presensi Siswa</small>
                            <span id="modal-journal-att-summary" class="font-weight-bold text-dark d-block mt-1" style="font-size:0.83rem;">-</span>
                        </div>
                    </div>
                </div>

                <!-- Topik / Agenda Card -->
                <div class="card border-0 shadow-sm mb-3" style="border-radius:12px;">
                    <div class="card-body p-3">
                        <small class="text-muted text-uppercase font-weight-bold d-block mb-1" style="font-size:0.72rem; letter-spacing:0.05em;">
                            <i class="fas fa-heading text-primary mr-1"></i> Topik / Agenda
                        </small>
                        <h5 id="modal-journal-topic" class="font-weight-bold text-dark mb-0" style="font-size:1.05rem; line-height:1.4;">-</h5>
                    </div>
                </div>

                <!-- Kegiatan Pembelajaran / Tugas Card -->
                <div class="card border-0 shadow-sm mb-3" style="border-radius:12px;">
                    <div class="card-body p-3">
                        <small class="text-muted text-uppercase font-weight-bold d-block mb-2" style="font-size:0.72rem; letter-spacing:0.05em;">
                            <i class="fas fa-tasks text-info mr-1"></i> Kegiatan Pembelajaran / Tugas
                        </small>
                        <div id="modal-journal-activity" class="text-secondary" style="white-space:pre-line; line-height:1.6; font-size:0.88rem;">-</div>
                    </div>
                </div>

                <!-- Asesmen & Catatan Rows -->
                <div class="row">
                    <div class="col-md-6 mb-3" id="modal-journal-assessment-wrapper">
                        <div class="card border-0 shadow-sm h-100" style="border-radius:12px; background:#f0f9ff; border:1px solid #bae6fd!important;">
                            <div class="card-body p-3">
                                <small class="text-info text-uppercase font-weight-bold d-block mb-1" style="font-size:0.72rem; letter-spacing:0.05em;">
                                    <i class="fas fa-clipboard-check mr-1"></i> Asesmen / Tindak Lanjut
                                </small>
                                <div id="modal-journal-assessment" class="text-dark" style="white-space:pre-line; font-size:0.85rem; line-height:1.5;">-</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3" id="modal-journal-notes-wrapper">
                        <div class="card border-0 shadow-sm h-100" style="border-radius:12px; background:#fffbeb; border:1px solid #fde68a!important;">
                            <div class="card-body p-3">
                                <small class="text-warning text-uppercase font-weight-bold d-block mb-1" style="font-size:0.72rem; letter-spacing:0.05em; color:#b45309!important;">
                                    <i class="far fa-comment-dots mr-1"></i> Catatan Tambahan
                                </small>
                                <div id="modal-journal-notes" class="text-dark" style="white-space:pre-line; font-size:0.85rem; line-height:1.5;">-</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Rincian Presensi Siswa Card -->
                <div class="card border-0 shadow-sm" id="modal-journal-att-section" style="border-radius:12px; overflow:hidden;">
                    <div class="card-header bg-white border-bottom-0 py-3 px-3 d-flex justify-content-between align-items-center">
                        <div class="font-weight-bold text-dark" style="font-size:0.88rem;">
                            <i class="fas fa-user-friends text-primary mr-1"></i> Rincian Presensi Siswa
                        </div>
                        <span id="modal-journal-att-rate" class="badge badge-pill badge-light border font-weight-bold px-2 py-1">
                            -
                        </span>
                    </div>
                    <div class="card-body p-3 pt-0">
                        <div id="modal-journal-att-absent-container">
                            <!-- Absent students dynamic markup -->
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-footer bg-white border-top-0 px-4 py-3 d-flex justify-content-between">
                <span class="small text-muted"><i class="fas fa-shield-alt text-success mr-1"></i> Terintegrasi data EKIN & Presensi</span>
                <div class="d-flex align-items-center">
                    <a id="modal-journal-edit-btn" href="#" class="btn btn-primary btn-sm px-3 font-weight-bold mr-2" style="border-radius:8px;">
                        <i class="fas fa-pen mr-1"></i> Edit Jurnal
                    </a>
                    <button type="button" class="btn btn-secondary btn-sm px-3" data-dismiss="modal" style="border-radius:8px;">
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@stop
@section('css')<style>
/* Hero Banner Modern */
.journal-hero {
    background: linear-gradient(135deg, #1e3a8a 0%, #2563eb 50%, #0d9488 100%);
    position: relative;
    overflow: hidden;
    color: #fff;
    border-radius: 16px;
    padding: 1.75rem 2rem;
    display: flex;
    justify-content: space-between;
    gap: 1.5rem;
    align-items: center;
    margin-bottom: 1.25rem;
    box-shadow: 0 4px 20px rgba(37, 99, 235, 0.15);
}
.journal-hero::after {
    content: '';
    position: absolute;
    right: -20px;
    top: -40px;
    width: 260px;
    height: 260px;
    background: radial-gradient(circle, rgba(255,255,255,0.12) 0%, rgba(255,255,255,0) 70%);
    border-radius: 50%;
    pointer-events: none;
}
.hero-content {
    position: relative;
    z-index: 1;
}
.hero-kicker {
    display: inline-flex;
    align-items: center;
    background: rgba(255, 255, 255, 0.16);
    border: 1px solid rgba(255, 255, 255, 0.22);
    backdrop-filter: blur(4px);
    padding: 0.25rem 0.75rem;
    border-radius: 20px;
    font-size: 0.72rem;
    font-weight: 700;
    letter-spacing: 0.05em;
    margin-bottom: 0.6rem;
    color: #e0f2fe;
}
.journal-hero h1 {
    font-size: 1.75rem;
    font-weight: 800;
    letter-spacing: -0.02em;
    margin: 0 0 0.4rem;
    line-height: 1.2;
}
.journal-hero p {
    margin: 0;
    opacity: 0.9;
    font-size: 0.88rem;
    max-width: 580px;
    line-height: 1.5;
}
.hero-actions {
    display: flex;
    gap: 0.75rem;
    flex-wrap: wrap;
    align-items: center;
    position: relative;
    z-index: 1;
}
.btn-hero-primary {
    background: #ffffff;
    color: #1e3a8a !important;
    font-weight: 700;
    font-size: 0.88rem;
    height: 42px;
    padding: 0 1.25rem;
    border-radius: 10px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border: none;
    box-shadow: 0 4px 14px rgba(0,0,0,0.12);
    transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
    text-decoration: none !important;
}
.btn-hero-primary:hover {
    background: #f8fafc;
    color: #1d4ed8 !important;
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(0,0,0,0.18);
}
.btn-hero-secondary {
    background: rgba(255, 255, 255, 0.15);
    color: #ffffff !important;
    font-weight: 600;
    font-size: 0.88rem;
    height: 42px;
    padding: 0 1.25rem;
    border-radius: 10px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border: 1px solid rgba(255, 255, 255, 0.35);
    backdrop-filter: blur(8px);
    transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
    text-decoration: none !important;
}
.btn-hero-secondary:hover {
    background: rgba(255, 255, 255, 0.25);
    color: #ffffff !important;
    border-color: rgba(255, 255, 255, 0.55);
    transform: translateY(-2px);
}
.text-danger-soft {
    color: #fca5a5;
}

@media (max-width: 767.98px) {
    .journal-hero {
        flex-direction: column;
        align-items: flex-start;
        padding: 1.5rem;
        gap: 1.25rem;
    }
    .hero-actions {
        width: 100%;
    }
    .btn-hero-primary, .btn-hero-secondary {
        flex: 1;
        text-align: center;
    }
}

/* Stat Cards Modern */
.journal-stats .col-sm-6 {
    margin-bottom: 1.25rem;
}
.stat-card {
    background: #fff;
    border-radius: 14px;
    padding: 1.15rem;
    display: flex;
    gap: 1rem;
    align-items: center;
    border: 1px solid #eef2f6;
    box-shadow: 0 2px 8px rgba(31, 55, 90, 0.04);
    height: 100%;
    transition: all 0.2s ease;
}
.stat-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 18px rgba(31, 55, 90, 0.08);
    border-color: #e2e8f0;
}
.stat-icon-wrapper {
    width: 48px;
    height: 48px;
    min-width: 48px;
    border-radius: 12px;
    display: grid;
    place-items: center;
    font-size: 1.35rem;
}
.stat-icon-blue { background: #eff6ff; color: #2563eb; }
.stat-icon-teal { background: #f0fdfa; color: #0d9488; }
.stat-icon-amber { background: #fffbeb; color: #d97706; }
.stat-icon-green { background: #f0fdf4; color: #16a34a; }

.stat-info {
    min-width: 0;
}
.stat-label {
    display: block;
    color: #64748b;
    font-size: 0.74rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    line-height: 1.2;
}
.stat-value {
    display: block;
    font-size: 1.35rem;
    font-weight: 800;
    color: #0f172a;
    line-height: 1.25;
    margin: 2px 0;
}
.stat-desc {
    display: block;
    color: #94a3b8;
    font-size: 0.72rem;
    line-height: 1.2;
}

/* Filter Card Modern */
.filter-card, .compose-card, .history-card {
    border-radius: 16px;
}
.filter-card {
    border: 1px solid #eef2f6 !important;
}
.filter-icon {
    width: 38px;
    height: 38px;
    display: grid;
    place-items: center;
    background: #eff6ff;
    color: #2563eb;
    border-radius: 10px;
    font-size: 1rem;
}
.filter-label {
    font-size: 0.78rem;
    font-weight: 700;
    color: #475569;
    margin-bottom: 0.35rem;
    display: block;
}
.filter-control {
    border-radius: 10px;
    border: 1px solid #cbd5e1;
    font-size: 0.86rem;
    height: 40px;
    background-color: #f8fafc;
    color: #1e293b;
    transition: all 0.2s ease;
}
.filter-control:focus {
    background-color: #ffffff;
    border-color: #3b82f6;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.15);
}
.btn-filter-submit {
    height: 40px;
    border-radius: 10px;
    font-size: 0.86rem;
    font-weight: 700;
    padding: 0 1.25rem;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    flex: 1;
    background: #2563eb;
    border-color: #2563eb;
    box-shadow: 0 2px 6px rgba(37, 99, 235, 0.25);
    transition: all 0.2s ease;
}
.btn-filter-submit:hover {
    background: #1d4ed8;
    border-color: #1d4ed8;
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(37, 99, 235, 0.35);
}
.btn-filter-reset {
    height: 40px;
    border-radius: 10px;
    font-size: 0.86rem;
    font-weight: 600;
    padding: 0 1rem;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background: #ffffff;
    border: 1px solid #cbd5e1;
    color: #64748b;
    transition: all 0.2s ease;
}
.btn-filter-reset:hover {
    background: #f1f5f9;
    color: #334155;
    border-color: #94a3b8;
    text-decoration: none;
    transform: translateY(-1px);
}
.compose-card label {
    font-size: .82rem;
    font-weight: 700;
    color: #4a5668;
}
.compose-card small {
    color: #8491a2;
    font-size: .74rem;
}

/* History Card & Journal Items */
.history-card{border-radius:16px;overflow:hidden}
.journal-item{padding:1.1rem .5rem;border-top:1px solid #f1f5f9;display:flex;gap:1rem;transition:all .18s ease;border-radius:12px}
.journal-item:hover{background:#f8fafc}
.journal-date{width:56px;min-width:56px;text-align:center;padding:.4rem .2rem;background:#f1f5f9;border:1px solid #e2e8f0;border-radius:12px;color:#475569;line-height:1.15;flex-shrink:0}
.journal-date .day-abbr{display:block;font-size:.64rem;font-weight:800;letter-spacing:.04em;color:#64748b;margin-bottom:2px}
.journal-date .date-number{font-size:1.35rem;font-weight:800;color:#1e293b;display:block}
.journal-date .month-abbr{font-size:.72rem;text-transform:uppercase;font-weight:700;color:#475569;display:block}
.journal-date .year-num{font-size:.62rem;color:#94a3b8;display:block}
.journal-main{flex:1;min-width:0}
.journal-title a{font-size:.96rem;font-weight:700;color:#1e293b;transition:color .15s ease}
.journal-title a:hover{color:#0284c7;text-decoration:none}
.journal-kegiatan{font-size:.83rem;color:#64748b;margin:.25rem 0 .45rem;line-height:1.45}
.journal-meta .context{font-size:.78rem;color:#64748b}
.type-badge{font-size:.68rem;font-weight:700;border-radius:6px;padding:.2rem .55rem;display:inline-flex;align-items:center}
.type-badge.teaching{background:#e0f2fe;color:#0369a1;border:1px solid #bae6fd}
.type-badge.homeroom{background:#fef3c7;color:#b45309;border:1px solid #fde68a}
.journal-actions{display:flex;gap:.3rem;align-items:flex-start}
.journal-actions .btn{width:32px;height:32px;padding:0;display:inline-flex;align-items:center;justify-content:center;border-radius:8px;transition:all .15s ease}
.journal-actions .btn:hover{transform:translateY(-1px);box-shadow:0 2px 5px rgba(0,0,0,.06)}
.journal-actions form{margin:0}
.empty-state{text-align:center;padding:4.5rem 1.5rem;color:#718096}.empty-state h4{color:#35445c;margin-top:1rem}.empty-state p{max-width:420px;margin:.5rem auto 1.25rem;font-size:.9rem}.empty-icon{margin:auto;width:66px;height:66px;border-radius:50%;display:grid;place-items:center;background:#edf5ff;color:#2672c9;font-size:1.7rem}

/* Modal and Utilities */
.shadow-2xs{box-shadow:0 1px 2px rgba(0,0,0,.04)}
.attendance-card{background:#f8fafc;border-color:#e2e8f0!important;border-radius:14px}
.student-att-row{transition:all .18s cubic-bezier(.4,0,.2,1);border:1px solid #edf2f7!important;border-radius:10px!important;background:#fff;margin-bottom:6px;padding:8px 12px;box-shadow:0 1px 2px rgba(0,0,0,.02)}
.student-att-row:hover{background:#f8fafc!important;border-color:#cbd5e1!important;box-shadow:0 2px 6px rgba(0,0,0,.04)}
.student-att-num{width:24px;height:24px;min-width:24px;border-radius:6px;background:#f1f5f9;color:#64748b;font-size:.72rem;font-weight:700;display:inline-flex;align-items:center;justify-content:center;margin-right:8px}
.att-badge-prefilled{font-size:.67rem;background:#fef3c7;color:#92400e;border-radius:4px;padding:1px 6px;display:inline-flex;align-items:center}
.att-status-group{display:inline-flex;align-items:center;gap:3px;background:#f1f5f9;padding:3px;border-radius:30px;border:1px solid #e2e8f0}
.att-status-btn{width:28px;height:28px;min-width:28px;padding:0!important;margin:0!important;display:inline-flex;align-items:center;justify-content:center;border-radius:50%!important;font-size:.76rem;font-weight:800;line-height:1;border:none!important;background:transparent;color:#64748b;transition:all .16s cubic-bezier(.4,0,.2,1);cursor:pointer;box-shadow:none!important;position:relative;user-select:none}
.att-status-btn input[type="radio"]{position:absolute;opacity:0;width:0;height:0;pointer-events:none;margin:0}
.att-status-btn:hover{transform:translateY(-1px)}
.att-status-btn.att-h:not(.active):hover{background:#dcfce7;color:#15803d}
.att-status-btn.att-s:not(.active):hover{background:#fef3c7;color:#b45309}
.att-status-btn.att-i:not(.active):hover{background:#e0f2fe;color:#0369a1}
.att-status-btn.att-a:not(.active):hover{background:#fee2e2;color:#b91c1c}
.att-status-btn.att-d:not(.active):hover{background:#ede9fe;color:#6d28d9}
.att-status-btn.att-h.active{background:linear-gradient(135deg,#10b981,#059669)!important;color:#fff!important;box-shadow:0 2px 7px rgba(16,185,129,.45)!important;transform:scale(1.08)}
.att-status-btn.att-s.active{background:linear-gradient(135deg,#f59e0b,#d97706)!important;color:#fff!important;box-shadow:0 2px 7px rgba(245,158,11,.45)!important;transform:scale(1.08)}
.att-status-btn.att-i.active{background:linear-gradient(135deg,#0ea5e9,#0284c7)!important;color:#fff!important;box-shadow:0 2px 7px rgba(14,165,233,.45)!important;transform:scale(1.08)}
.att-status-btn.att-a.active{background:linear-gradient(135deg,#ef4444,#dc2626)!important;color:#fff!important;box-shadow:0 2px 7px rgba(239,68,68,.45)!important;transform:scale(1.08)}
.att-status-btn.att-d.active{background:linear-gradient(135deg,#8b5cf6,#6d28d9)!important;color:#fff!important;box-shadow:0 2px 7px rgba(139,92,246,.45)!important;transform:scale(1.08)}
.att-stat-chip{font-size:.74rem;font-weight:700;padding:3px 9px;border-radius:20px;display:inline-flex;align-items:center;gap:5px;margin-right:5px;margin-bottom:4px;border:1px solid transparent}
.att-stat-chip.stat-h{background:#ecfdf5;color:#065f46;border-color:#a7f3d0}
.att-stat-chip.stat-s{background:#fffbeb;color:#92400e;border-color:#fde68a}
.att-stat-chip.stat-i{background:#eff6ff;color:#1e40af;border-color:#bfdbfe}
.att-stat-chip.stat-a{background:#fef2f2;color:#991b1b;border-color:#fecaca}
.att-stat-chip.stat-d{background:#f5f3ff;color:#5b21b6;border-color:#ddd6fe}
.att-stat-dot{width:6px;height:6px;border-radius:50%;display:inline-block}
.stat-h .att-stat-dot{background:#10b981}
.stat-s .att-stat-dot{background:#f59e0b}
.stat-i .att-stat-dot{background:#0ea5e9}
.stat-a .att-stat-dot{background:#ef4444}
.stat-d .att-stat-dot{background:#8b5cf6}
@media(max-width:767px){.journal-hero{padding:1.4rem;display:block}.hero-actions{margin-top:1rem}.journal-hero h1{font-size:1.45rem}.journal-item{margin-left:.75rem!important;margin-right:.75rem!important;gap:.75rem}.journal-actions{flex-direction:row}.filter-card .form-group{margin-bottom:.8rem}}
</style>@stop
@section('js')<script>
document.addEventListener('DOMContentLoaded',function(){
    const type=document.getElementById('journal-type');
    const teach=document.getElementById('teaching-fields');
    const home=document.getElementById('homeroom-fields');
    const scheduleSelect=document.getElementById('journal-schedule');
    const homeroomSelect=document.getElementById('journal-homeroom');
    const dateInput=document.getElementById('journal-date');
    const attList=document.getElementById('att-students-list');
    const attSubtitle=document.getElementById('att-subtitle');
    const attLoading=document.getElementById('att-loading');
    const attPlaceholder=document.getElementById('att-placeholder');
    const attHeaderActions=document.getElementById('att-header-actions');
    const attStatsPills=document.getElementById('att-stats-pills');
    const attSourceAlert=document.getElementById('att-source-alert');
    const attSourceText=document.getElementById('att-source-text');
    const btnAllPresent=document.getElementById('btn-all-present');

    function toggle(){
        if(!type)return;
        const isTeach=type.value==='mengajar';
        teach.classList.toggle('d-none',!isTeach);
        if(home)home.classList.toggle('d-none',isTeach);
        fetchAttendance();
    }

    if(type){type.addEventListener('change',toggle);toggle();}

    function updateStats(){
        const statuses={hadir:0,sakit:0,izin:0,alpa:0,dispen:0};
        const rows=attList.querySelectorAll('.student-att-row');
        rows.forEach(row=>{
            const checked=row.querySelector('input[type="radio"]:checked');
            if(checked && statuses.hasOwnProperty(checked.value)){
                statuses[checked.value]++;
            }
        });
        const elH=document.getElementById('stat-hadir');
        const elS=document.getElementById('stat-sakit');
        const elI=document.getElementById('stat-izin');
        const elA=document.getElementById('stat-alpa');
        const elD=document.getElementById('stat-dispen');
        if(elH) elH.textContent=statuses.hadir;
        if(elS) elS.textContent=statuses.sakit;
        if(elI) elI.textContent=statuses.izin;
        if(elA) elA.textContent=statuses.alpa;
        if(elD) elD.textContent=statuses.dispen;
    }
    window.updateAttendanceStats=updateStats;

    if(btnAllPresent){
        btnAllPresent.addEventListener('click',function(){
            const rows=attList.querySelectorAll('.student-att-row');
            rows.forEach(row=>{
                const radioH=row.querySelector('input[value="hadir"]');
                if(radioH){
                    radioH.checked=true;
                    row.querySelectorAll('.att-status-btn').forEach(lbl=>lbl.classList.remove('active'));
                    const parent=radioH.closest('.att-status-btn');
                    if(parent) parent.classList.add('active');
                }
            });
            updateStats();
        });
    }

    if(attList){
        attList.addEventListener('click', function(e){
            const btn=e.target.closest('.att-status-btn');
            if(!btn) return;
            const group=btn.closest('.att-status-group');
            if(group){
                group.querySelectorAll('.att-status-btn').forEach(b=>b.classList.remove('active'));
                btn.classList.add('active');
                const radio=btn.querySelector('input[type="radio"]');
                if(radio){
                    radio.checked=true;
                }
                updateStats();
            }
        });

        attList.addEventListener('change', function(e){
            if(e.target && e.target.type==='radio'){
                const btn=e.target.closest('.att-status-btn');
                const group=e.target.closest('.att-status-group');
                if(btn && group){
                    group.querySelectorAll('.att-status-btn').forEach(b=>b.classList.remove('active'));
                    btn.classList.add('active');
                }
                updateStats();
            }
        });
    }

    let fetchTimer=null;
    function fetchAttendance(){
        if(!dateInput) return;
        const tanggal=dateInput.value;
        const isTeach=!type || type.value==='mengajar';
        const scheduleId=isTeach && scheduleSelect ? scheduleSelect.value : null;
        const kelasId=!isTeach && homeroomSelect ? homeroomSelect.value : null;

        if(!tanggal || (isTeach && !scheduleId) || (!isTeach && !kelasId)){
            if(attPlaceholder) attPlaceholder.classList.remove('d-none');
            if(attHeaderActions) attHeaderActions.classList.add('d-none');
            if(attStatsPills) attStatsPills.classList.remove('d-flex');
            if(attStatsPills) attStatsPills.classList.add('d-none');
            if(attSourceAlert) attSourceAlert.classList.add('d-none');
            if(attSubtitle) attSubtitle.textContent='Pilih jadwal atau kelas untuk memuat daftar siswa.';
            attList.innerHTML='<div class="text-muted small text-center py-3" id="att-placeholder"><i class="fas fa-clipboard-list fa-2x mb-2 text-secondary d-block"></i>Daftar siswa akan otomatis tampil saat jadwal mengajar / kelas dipilih.</div>';
            return;
        }

        clearTimeout(fetchTimer);
        fetchTimer=setTimeout(function(){
            attLoading.classList.remove('d-none');
            attList.classList.add('opacity-50');

            const params=new URLSearchParams();
            params.append('tanggal', tanggal);
            if(scheduleId) params.append('jadwal_pelajaran_id', scheduleId);
            if(kelasId) params.append('kelas_id', kelasId);

            fetch('{{ route("admin.gtk.journals.students-attendance") }}?'+params.toString(),{
                headers:{
                    'X-Requested-With':'XMLHttpRequest',
                    'Accept':'application/json'
                }
            })
            .then(res=>{
                if(!res.ok){
                    return res.json().then(errData => {
                        throw new Error(errData.message || ('Server error: HTTP ' + res.status));
                    }).catch(function(){
                        throw new Error('Server error: HTTP ' + res.status);
                    });
                }
                return res.json();
            })
            .then(data=>{
                attLoading.classList.add('d-none');
                attList.classList.remove('opacity-50');

                if(!data.success || !data.students || data.students.length===0){
                    attList.innerHTML='<div class="text-center text-muted small py-3"><i class="fas fa-exclamation-circle mr-1"></i> Tidak ada siswa aktif terdaftar di kelas ini.</div>';
                    attHeaderActions.classList.add('d-none');
                    attStatsPills.classList.add('d-none');
                    attStatsPills.classList.remove('d-flex');
                    attSourceAlert.classList.add('d-none');
                    return;
                }

                attSubtitle.textContent=data.kelas_name + (data.mapel_name ? ' · ' + data.mapel_name : '') + ' (' + data.total + ' siswa)';
                attHeaderActions.classList.remove('d-none');
                attStatsPills.classList.remove('d-none');
                attStatsPills.classList.add('d-flex');

                if(data.prefilled_count > 0){
                    attSourceAlert.classList.remove('d-none');
                    attSourceText.innerHTML='<strong>Sinkron Presensi Pagi:</strong> ' + data.prefilled_count + ' siswa tidak hadir pada absensi harian pagi disesuaikan otomatis.';
                } else if(data.has_saved_session){
                    attSourceAlert.classList.remove('d-none');
                    attSourceText.innerHTML='<strong>Presensi Mapel Tersimpan:</strong> Memuat data presensi yang sudah dicatat sebelumnya.';
                } else {
                    attSourceAlert.classList.remove('d-none');
                    attSourceText.innerHTML='<strong>Presensi Baru:</strong> Default seluruh siswa diset Hadir. Sesuaikan jika ada yang berhalangan.';
                }

                let html='';
                data.students.forEach((s, idx)=>{
                    html += `
                    <div class="student-att-row d-flex justify-content-between align-items-center" data-id="${s.id}">
                        <div class="d-flex align-items-center mr-2" style="min-width:0; flex:1;">
                            <span class="student-att-num">${idx+1}</span>
                            <div class="text-truncate">
                                <div class="font-weight-bold text-dark text-truncate" style="font-size:0.84rem; letter-spacing:-0.01em;">${s.nama}</div>
                                <div class="text-muted d-flex align-items-center flex-wrap" style="font-size:0.72rem; gap:4px;">
                                    <span>NISN: ${s.nisn || '-'}</span>
                                    ${s.source === 'harian' ? '<span class="att-badge-prefilled font-weight-bold"><i class="fas fa-clock mr-1"></i>Pagi: ' + (s.harian_status || '').toUpperCase() + '</span>' : ''}
                                </div>
                            </div>
                        </div>
                        <div class="att-status-group flex-shrink-0">
                            <label class="att-status-btn att-h ${s.status === 'hadir' ? 'active' : ''}" title="Hadir">
                                <input type="radio" name="student_attendance[${s.id}][status]" value="hadir" ${s.status === 'hadir' ? 'checked' : ''}>
                                <span>H</span>
                            </label>
                            <label class="att-status-btn att-s ${s.status === 'sakit' ? 'active' : ''}" title="Sakit">
                                <input type="radio" name="student_attendance[${s.id}][status]" value="sakit" ${s.status === 'sakit' ? 'checked' : ''}>
                                <span>S</span>
                            </label>
                            <label class="att-status-btn att-i ${s.status === 'izin' ? 'active' : ''}" title="Izin">
                                <input type="radio" name="student_attendance[${s.id}][status]" value="izin" ${s.status === 'izin' ? 'checked' : ''}>
                                <span>I</span>
                            </label>
                            <label class="att-status-btn att-a ${s.status === 'alpa' ? 'active' : ''}" title="Alpa (Tanpa Keterangan)">
                                <input type="radio" name="student_attendance[${s.id}][status]" value="alpa" ${s.status === 'alpa' ? 'checked' : ''}>
                                <span>A</span>
                            </label>
                            <label class="att-status-btn att-d ${s.status === 'dispen' ? 'active' : ''}" title="Dispensasi">
                                <input type="radio" name="student_attendance[${s.id}][status]" value="dispen" ${s.status === 'dispen' ? 'checked' : ''}>
                                <span>D</span>
                            </label>
                        </div>
                    </div>`;
                });

                attList.innerHTML=html;
                updateStats();
            })
            .catch(err=>{
                console.error('Attendance fetch error:', err);
                attLoading.classList.add('d-none');
                attList.classList.remove('opacity-50');
                attList.innerHTML='<div class="text-center text-danger small py-3"><i class="fas fa-exclamation-triangle mr-1"></i> ' + (err.message || 'Gagal memuat data presensi.') + ' Silakan coba lagi.</div>';
            });
        }, 200);
    }

    if(scheduleSelect) scheduleSelect.addEventListener('change', fetchAttendance);
    if(homeroomSelect) homeroomSelect.addEventListener('change', fetchAttendance);
    if(dateInput) dateInput.addEventListener('change', fetchAttendance);

    const modalCreate = $('#modal-create-journal');
    document.querySelectorAll('.js-compose').forEach(el => {
        el.addEventListener('click', function(e) {
            e.preventDefault();
            modalCreate.modal('show');
        });
    });

    modalCreate.on('shown.bs.modal', function() {
        if(dateInput) dateInput.focus();
        if(scheduleSelect && scheduleSelect.value || homeroomSelect && homeroomSelect.value) {
            fetchAttendance();
        }
    });

    @if(isset($errors) && $errors->any())
        modalCreate.modal('show');
    @endif

    // Journal Detail Modal
    const modalDetail = $('#modal-journal-detail');
    function openJournalDetail(data){
        if(!data) return;

        const typeBadge = document.getElementById('modal-journal-type-badge');
        const durBadge = document.getElementById('modal-journal-duration-badge');
        const durVal = document.getElementById('modal-journal-duration-val');

        if(typeBadge){
            typeBadge.className = 'type-badge ' + (data.jenis === 'mengajar' ? 'teaching' : 'homeroom');
            typeBadge.innerHTML = (data.jenis === 'mengajar' ? '<i class="fas fa-chalkboard-teacher mr-1"></i> Mengajar' : '<i class="fas fa-user-shield mr-1"></i> Wali Kelas');
        }

        if(durBadge && durVal){
            if(data.durasi_menit){
                durVal.textContent = data.durasi_menit + ' menit';
                durBadge.classList.remove('d-none');
            } else {
                durBadge.classList.add('d-none');
            }
        }

        const dateEl = document.getElementById('modal-journal-date');
        const classEl = document.getElementById('modal-journal-class');
        const subjEl = document.getElementById('modal-journal-subject');
        const topicEl = document.getElementById('modal-journal-topic');
        const actEl = document.getElementById('modal-journal-activity');
        const editBtn = document.getElementById('modal-journal-edit-btn');

        if(dateEl) dateEl.textContent = data.tanggal_formatted || data.tanggal_short;
        if(classEl) classEl.textContent = data.kelas || '-';
        if(subjEl) {
            subjEl.textContent = data.mapel || '-';
            subjEl.title = data.mapel || '';
        }
        if(topicEl) topicEl.textContent = data.topik || '-';
        if(actEl) actEl.textContent = data.kegiatan || '-';
        if(editBtn) editBtn.href = data.edit_url || '#';

        const asmWrap = document.getElementById('modal-journal-assessment-wrapper');
        const asmEl = document.getElementById('modal-journal-assessment');
        if(asmWrap && asmEl){
            if(data.asesmen && data.asesmen.trim()){
                asmEl.textContent = data.asesmen;
                asmWrap.classList.remove('d-none');
            } else {
                asmWrap.classList.add('d-none');
            }
        }

        const noteWrap = document.getElementById('modal-journal-notes-wrapper');
        const noteEl = document.getElementById('modal-journal-notes');
        if(noteWrap && noteEl){
            if(data.catatan && data.catatan.trim()){
                noteEl.textContent = data.catatan;
                noteWrap.classList.remove('d-none');
            } else {
                noteWrap.classList.add('d-none');
            }
        }

        const attSummary = document.getElementById('modal-journal-att-summary');
        const attSection = document.getElementById('modal-journal-att-section');
        const attRate = document.getElementById('modal-journal-att-rate');
        const attContainer = document.getElementById('modal-journal-att-absent-container');

        if(data.attendance && data.attendance.total > 0){
            const att = data.attendance;
            if(attSummary) attSummary.textContent = `${att.hadir}/${att.total} (${att.persentase}%)`;
            if(attRate){
                attRate.textContent = `${att.persentase}% Hadir`;
                attRate.className = 'badge badge-pill font-weight-bold px-2 py-1 ' + (att.persentase >= 90 ? 'badge-success' : (att.persentase >= 75 ? 'badge-warning' : 'badge-danger'));
            }
            if(attSection) attSection.classList.remove('d-none');

            if(attContainer){
                if(att.absent_students && att.absent_students.length > 0){
                    let tableHtml = `
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered table-hover mb-0" style="font-size:0.83rem;">
                            <thead class="thead-light">
                                <tr>
                                    <th style="width:36px;" class="text-center">#</th>
                                    <th>Nama Siswa</th>
                                    <th style="width:100px;" class="text-center">Status</th>
                                    <th>Keterangan</th>
                                </tr>
                            </thead>
                            <tbody>`;

                    const statusClassMap = {
                        sakit: 'badge-warning',
                        izin: 'badge-info',
                        alpa: 'badge-danger',
                        dispen: 'badge-secondary'
                    };

                    att.absent_students.forEach((s, idx) => {
                        const stBadge = statusClassMap[s.status] || 'badge-secondary';
                        tableHtml += `
                        <tr>
                            <td class="text-center text-muted">${idx+1}</td>
                            <td class="font-weight-bold">${s.nama}</td>
                            <td class="text-center"><span class="badge ${stBadge} text-uppercase px-2">${s.status}</span></td>
                            <td class="text-muted small">${s.catatan || '-'}</td>
                        </tr>`;
                    });

                    tableHtml += `</tbody></table></div>`;
                    attContainer.innerHTML = tableHtml;
                } else {
                    attContainer.innerHTML = `
                    <div class="alert alert-success border-0 mb-0 py-3 text-center" style="background:#ecfdf5; color:#065f46; border-radius:10px;">
                        <i class="fas fa-check-circle fa-lg mr-2 text-success"></i>
                        <strong>100% Hadir</strong> — Seluruh ${att.total} siswa tercatat hadir dalam pembelajaran ini.
                    </div>`;
                }
            }
        } else {
            if(attSummary) attSummary.textContent = 'Tidak dicatat';
            if(attSection) attSection.classList.add('d-none');
        }

        modalDetail.modal('show');
    }

    document.addEventListener('click', function(e){
        const trigger = e.target.closest('.js-open-detail');
        if(!trigger) return;
        e.preventDefault();

        const row = trigger.closest('.js-journal-row');
        if(!row) return;

        const scriptEl = row.querySelector('.js-journal-data');
        if(!scriptEl) return;

        try {
            const data = JSON.parse(scriptEl.textContent);
            openJournalDetail(data);
        } catch(err) {
            console.error('Failed to parse journal data:', err);
        }
    });

    // Quick client-side search filter
    const quickFilterInput = document.getElementById('journal-quick-filter');
    const clearFilterBtn = document.getElementById('btn-clear-quick-filter');
    const clearFilterWrapper = document.getElementById('clear-quick-filter-wrapper');
    const emptyFilterNotice = document.getElementById('quick-filter-empty');

    if(quickFilterInput){
        quickFilterInput.addEventListener('input', function(){
            const q = this.value.trim().toLowerCase();
            const rows = document.querySelectorAll('.js-journal-row');
            let visibleCount = 0;

            if(clearFilterWrapper){
                if(q.length > 0){
                    clearFilterWrapper.classList.remove('d-none');
                } else {
                    clearFilterWrapper.classList.add('d-none');
                }
            }

            rows.forEach(row => {
                if(!q){
                    row.classList.remove('d-none');
                    visibleCount++;
                    return;
                }
                const scriptEl = row.querySelector('.js-journal-data');
                let matches = false;
                if(scriptEl){
                    try {
                        const d = JSON.parse(scriptEl.textContent);
                        const haystack = [
                            d.topik || '',
                            d.kegiatan || '',
                            d.kelas || '',
                            d.mapel || '',
                            d.asesmen || '',
                            d.catatan || '',
                            d.jenis_label || '',
                            d.hari || ''
                        ].join(' ').toLowerCase();
                        if(haystack.indexOf(q) !== -1){
                            matches = true;
                        }
                    } catch(e){}
                } else {
                    if(row.textContent.toLowerCase().indexOf(q) !== -1){
                        matches = true;
                    }
                }

                if(matches){
                    row.classList.remove('d-none');
                    visibleCount++;
                } else {
                    row.classList.add('d-none');
                }
            });

            if(emptyFilterNotice){
                if(visibleCount === 0 && rows.length > 0){
                    emptyFilterNotice.classList.remove('d-none');
                } else {
                    emptyFilterNotice.classList.add('d-none');
                }
            }
        });

        if(clearFilterBtn){
            clearFilterBtn.addEventListener('click', function(){
                quickFilterInput.value = '';
                quickFilterInput.dispatchEvent(new Event('input'));
                quickFilterInput.focus();
            });
        }
    }
});
</script>@stop
