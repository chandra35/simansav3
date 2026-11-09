@extends('adminlte::page')

@section('title', 'Nilai & Analisis Kuis Moodle')
@section('plugins.Sweetalert2', true)
@section('plugins.Datatables', true)

@section('content_header')
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1 class="m-0 text-dark"><i class="fas fa-poll text-primary mr-2"></i>Nilai & Analisis Kuis Moodle</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.moodle-sync.index') }}">Moodle</a></li>
                <li class="breadcrumb-item active">Nilai & Analisis Kuis</li>
            </ol>
        </div>
    </div>
@endsection

@section('content')
<div class="quiz-analytics-container">
    {{-- Hero Banner --}}
    <div class="simansa-hero__main d-flex flex-wrap align-items-center justify-content-between mb-3 p-4 text-white shadow-sm" style="background: linear-gradient(135deg, #1e1b4b 0%, #312e81 40%, #4338ca 100%); border-radius: 16px;">
        <div class="mr-md-3 mb-2 mb-md-0">
            <div style="font-size: 0.75rem; font-weight: 800; letter-spacing: 0.12em; color: #a5b4fc; text-transform: uppercase;">
                <i class="fas fa-chart-line mr-1"></i> E-Learning Psychometrics & Assessment
            </div>
            <h2 class="font-weight-bold mb-1" style="font-size: 1.65rem;">Rekap Nilai & Analisis Butir Soal</h2>
            <p class="mb-0 text-light" style="font-size: 0.9rem; opacity: 0.9;">
                Tarik perolehan nilai siswa langsung dari Moodle CBT dan evaluasi kualitas soal (tingkat kesukaran, daya pembeda, dan reliabilitas Cronbach's Alpha).
            </p>
        </div>
        <div class="d-flex align-items-center gap-2">
            <button type="button" id="btn-open-history-modal" class="btn btn-light btn-sm font-weight-bold shadow-sm text-indigo">
                <i class="fas fa-history mr-1"></i> Riwayat Arsip SIMANSA
            </button>
            <span class="badge badge-pill badge-light px-3 py-2 shadow-sm font-weight-bold text-indigo" style="font-size: 0.82rem;">
                <i class="fas fa-circle text-success mr-1"></i> Moodle Connected
            </span>
        </div>
    </div>

    {{-- Filter Card --}}
    <div class="card shadow-sm border-0 mb-4 filter-card" style="border-radius: 14px;">
        <div class="card-header bg-white border-bottom py-3 d-flex align-items-center justify-content-between">
            <h5 class="card-title font-weight-bold text-dark mb-0">
                <i class="fas fa-filter text-primary mr-2"></i>Pilih Kategori, Sub Kategori, Kursus, dan Kuis
            </h5>
            <span class="text-muted text-xs font-weight-bold">
                <i class="fas fa-layer-group text-info mr-1"></i>Filter Bertingkat (Cascading)
            </span>
        </div>
        <div class="card-body">
            <div class="row align-items-end">
                {{-- 1. Kategori --}}
                <div class="col-lg-3 col-md-6 mb-3 mb-lg-0">
                    <label class="font-weight-bold text-secondary text-sm d-flex align-items-center justify-content-between mb-1.5">
                        <span><i class="fas fa-folder text-warning mr-1"></i> 1. Kategori</span>
                        <span class="spinner-border spinner-border-sm text-primary d-none" id="spin-category" role="status"></span>
                    </label>
                    <select id="select-category" class="custom-select custom-select-sm filter-select">
                        <option value="">-- Memuat Kategori... --</option>
                    </select>
                </div>

                {{-- 2. Sub Kategori --}}
                <div class="col-lg-3 col-md-6 mb-3 mb-lg-0">
                    <label class="font-weight-bold text-secondary text-sm d-flex align-items-center justify-content-between mb-1.5">
                        <span><i class="fas fa-folder-open text-info mr-1"></i> 2. Sub Kategori</span>
                        <span class="spinner-border spinner-border-sm text-primary d-none" id="spin-subcategory" role="status"></span>
                    </label>
                    <select id="select-subcategory" class="custom-select custom-select-sm filter-select" disabled>
                        <option value="">-- Pilih Kategori Dulu --</option>
                    </select>
                </div>

                {{-- 3. Kursus / Mapel --}}
                <div class="col-lg-3 col-md-6 mb-3 mb-lg-0">
                    <label class="font-weight-bold text-secondary text-sm d-flex align-items-center justify-content-between mb-1.5">
                        <span><i class="fas fa-book text-success mr-1"></i> 3. Kursus / Mapel</span>
                        <span class="spinner-border spinner-border-sm text-primary d-none" id="spin-course" role="status"></span>
                    </label>
                    <select id="select-course" class="custom-select custom-select-sm filter-select" disabled>
                        <option value="">-- Pilih Sub Kategori Dulu --</option>
                    </select>
                </div>

                {{-- 4. Kuis / Ujian --}}
                <div class="col-lg-2 col-md-6 mb-3 mb-lg-0">
                    <label class="font-weight-bold text-secondary text-sm d-flex align-items-center justify-content-between mb-1.5">
                        <span><i class="fas fa-file-alt text-primary mr-1"></i> 4. Kuis</span>
                        <span class="spinner-border spinner-border-sm text-primary d-none" id="spin-quiz" role="status"></span>
                    </label>
                    <select id="select-quiz" class="custom-select custom-select-sm filter-select" disabled>
                        <option value="">-- Pilih Kursus Dulu --</option>
                    </select>
                </div>

                {{-- Tombol Tampilkan --}}
                <div class="col-lg-1 col-md-6">
                    <button type="button" id="btn-load-data" class="btn btn-primary btn-block btn-sm font-weight-bold shadow-sm d-flex align-items-center justify-content-center" disabled style="height: 31px; border-radius: 6px;">
                        <i class="fas fa-chart-bar mr-1"></i> Muat
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Loading Placeholder --}}
    <div id="loading-state" class="text-center py-5 d-none">
        <div class="spinner-border text-primary" style="width: 3.5rem; height: 3.5rem;" role="status">
            <span class="sr-only">Loading...</span>
        </div>
        <h5 class="mt-3 font-weight-bold text-dark">Mengambil Data & Menghitung Statistik dari Moodle...</h5>
        <p class="text-muted">Proses ini menganalisis seluruh jawaban peserta secara mendalam.</p>
    </div>

    {{-- Empty State --}}
    <div id="initial-empty-state" class="card border-0 shadow-sm text-center py-5" style="border-radius: 12px;">
        <div class="card-body">
            <div class="mx-auto mb-3 text-muted" style="font-size: 3.5rem; opacity: 0.4;">
                <i class="fas fa-poll-h"></i>
            </div>
            <h5 class="font-weight-bold text-dark">Belum Ada Kuis yang Dipilih</h5>
            <p class="text-muted mx-auto" style="max-width: 500px;">
                Silakan pilih kategori, kursus, dan kuis pada filter di atas, kemudian klik tombol <b>Tampilkan</b> untuk memuat nilai siswa dan analisis butir soal.
            </p>
        </div>
    </div>

    {{-- Results Section --}}
    <div id="results-section" class="d-none">
        {{-- Overview Cards --}}
        <div class="row">
            <div class="col-lg-3 col-6">
                <div class="small-box bg-info shadow-sm" style="border-radius: 12px;">
                    <div class="inner">
                        <h3 id="stat-attempts">0</h3>
                        <p>Total Peserta Ujian</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-users"></i>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="small-box bg-success shadow-sm" style="border-radius: 12px;">
                    <div class="inner">
                        <h3 id="stat-avg">0</h3>
                        <p>Rata-rata Nilai (Skala 100)</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-calculator"></i>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="small-box bg-warning shadow-sm" style="border-radius: 12px;">
                    <div class="inner">
                        <h3 id="stat-reliability">0%</h3>
                        <p id="stat-reliability-label">Reliabilitas (Cronbach's α)</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-certificate"></i>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="small-box bg-primary shadow-sm" style="border-radius: 12px;">
                    <div class="inner">
                        <h3 id="stat-questions">0</h3>
                        <p>Total Butir Soal</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-question-circle"></i>
                    </div>
                </div>
            </div>
        </div>

        {{-- Action Toolbar: Arsip SIMANSA, Kunci, PDF, Sinkron Nilai --}}
        <div class="card shadow-sm border-0 mb-3 bg-white" style="border-radius: 12px;">
            <div class="card-body p-3 d-flex flex-wrap align-items-center justify-content-between gap-2">
                <div class="d-flex align-items-center gap-2">
                    <span id="archive-status-badge" class="badge badge-light border px-3 py-2 text-sm font-weight-bold">
                        <i class="fas fa-database text-secondary mr-1"></i> <span id="archive-status-text">Belum Diarsipkan di SIMANSA</span>
                    </span>
                    <span id="archive-time-info" class="text-xs text-muted"></span>
                </div>
                <div class="d-flex flex-wrap align-items-center gap-2">
                    <button type="button" id="btn-export-pdf" class="btn btn-danger btn-sm font-weight-bold shadow-sm">
                        <i class="fas fa-file-pdf mr-1"></i> Cetak Laporan (PDF)
                    </button>
                    <button type="button" id="btn-open-save-modal" class="btn btn-success btn-sm font-weight-bold shadow-sm">
                        <i class="fas fa-save mr-1"></i> Simpan ke Arsip SIMANSA
                    </button>
                    <button type="button" id="btn-toggle-lock" class="btn btn-warning btn-sm font-weight-bold shadow-sm d-none">
                        <i class="fas fa-lock mr-1"></i> <span id="btn-toggle-lock-text">Kunci Data</span>
                    </button>
                    <button type="button" id="btn-open-sync-modal" class="btn btn-primary btn-sm font-weight-bold shadow-sm">
                        <i class="fas fa-sync-alt mr-1"></i> Sinkron ke Buku Nilai
                    </button>
                </div>
            </div>
        </div>

        {{-- Main Analytics Tabs --}}
        <div class="card shadow-sm border-0 mb-4" style="border-radius: 12px;">
            <div class="card-header bg-white border-bottom p-0">
                <ul class="nav nav-tabs border-0" id="analytics-tabs" role="tablist" style="padding-left: 1rem; padding-top: 0.5rem;">
                    <li class="nav-item">
                        <a class="nav-link active font-weight-bold px-4 py-3" id="tab-analysis-link" data-toggle="tab" href="#tab-analysis" role="tab" style="font-size: 0.95rem;">
                            <i class="fas fa-chart-bar mr-2 text-primary"></i>Analisis Butir Soal
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link font-weight-bold px-4 py-3" id="tab-scores-link" data-toggle="tab" href="#tab-scores" role="tab" style="font-size: 0.95rem;">
                            <i class="fas fa-user-graduate mr-2 text-success"></i>Rekap Nilai Siswa
                        </a>
                    </li>
                </ul>
            </div>
            <div class="card-body">
                <div class="tab-content" id="analyticsTabContent">
                    {{-- TAB 1: Analisis Butir Soal --}}
                    <div class="tab-pane fade show active" id="tab-analysis" role="tabpanel">
                        {{-- Interpretation Guide Alert --}}
                        <div class="alert alert-light border shadow-sm mb-4" style="border-radius: 10px; background-color: #f8fafc;">
                            <h6 class="font-weight-bold text-dark mb-2">
                                <i class="fas fa-info-circle text-info mr-1"></i> Pedoman Klasifikasi Butir Soal:
                            </h6>
                            <div class="row text-sm">
                                <div class="col-md-6 mb-2 mb-md-0">
                                    <strong class="text-secondary">Tingkat Kesukaran (P):</strong><br>
                                    <span class="badge badge-success mr-1">Mudah</span> &gt; 70% &nbsp;|&nbsp; 
                                    <span class="badge badge-primary mr-1">Sedang</span> 30% - 70% &nbsp;|&nbsp; 
                                    <span class="badge badge-danger mr-1">Sukar</span> &lt; 30%
                                </div>
                                <div class="col-md-6">
                                    <strong class="text-secondary">Daya Pembeda (D):</strong><br>
                                    <span class="badge badge-success mr-1">Sangat Baik</span> &ge; 40% &nbsp;|&nbsp; 
                                    <span class="badge badge-info mr-1">Baik</span> 30% - 39% &nbsp;|&nbsp; 
                                    <span class="badge badge-warning mr-1">Cukup</span> 20% - 29% &nbsp;|&nbsp; 
                                    <span class="badge badge-danger mr-1">Jelek</span> &lt; 20%
                                </div>
                            </div>
                        </div>

                        {{-- Item Quality Summary Badges --}}
                        <div class="d-flex flex-wrap gap-2 mb-3 align-items-center justify-content-between">
                            <div class="d-flex flex-wrap align-items-center gap-2">
                                <span class="badge badge-light border px-3 py-2 font-weight-bold text-secondary" id="badge-total-items">
                                    Total: 0 Soal
                                </span>
                                <span class="badge badge-success px-3 py-2 font-weight-bold" id="badge-accepted-items">
                                    Diterima: 0
                                </span>
                                <span class="badge badge-warning px-3 py-2 font-weight-bold" id="badge-revision-items">
                                    Perlu Revisi: 0
                                </span>
                                <span class="badge badge-danger px-3 py-2 font-weight-bold" id="badge-rejected-items">
                                    Ditolak / Ganti: 0
                                </span>
                                <span class="badge badge-dark px-3 py-2 font-weight-bold" id="badge-error-key-items">
                                    Kunci Salah: 0
                                </span>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <button type="button" class="btn btn-outline-danger btn-sm font-weight-bold btn-print-pdf-trigger">
                                    <i class="fas fa-file-pdf mr-1"></i> Cetak Laporan (PDF)
                                </button>
                                <button type="button" id="btn-force-refresh" class="btn btn-outline-secondary btn-sm font-weight-bold" title="Hitung ulang statistik kuis dari Moodle">
                                    <i class="fas fa-redo-alt mr-1"></i> Hitung Ulang Moodle
                                </button>
                            </div>
                        </div>

                        {{-- Table Analysis --}}
                        <div class="table-responsive">
                            <table id="table-analysis" class="table table-hover table-bordered border-0" style="width: 100%;">
                                <thead class="bg-light">
                                    <tr>
                                        <th class="text-center" style="width: 45px;">No</th>
                                        <th>Pertanyaan / Cuplikan Soal</th>
                                        <th class="text-center" style="width: 80px;">Aksi</th>
                                        <th class="text-center" style="width: 100px;">Tipe</th>
                                        <th class="text-center" style="width: 130px;">Kesukaran (P)</th>
                                        <th class="text-center" style="width: 140px;">Daya Pembeda (D)</th>
                                        <th class="text-center" style="width: 140px;">Rekomendasi</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>

                    {{-- TAB 2: Rekap Nilai Siswa --}}
                    <div class="tab-pane fade" id="tab-scores" role="tabpanel">
                        <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
                            <div>
                                <h6 class="font-weight-bold text-dark mb-0">Daftar Nilai Hasil Percobaan Siswa</h6>
                                <small class="text-muted">Nilai tertinggi dari masing-masing siswa yang telah menyelesaikan kuis.</small>
                            </div>
                            <div>
                                <button type="button" class="btn btn-outline-danger btn-sm font-weight-bold btn-print-pdf-trigger">
                                    <i class="fas fa-file-pdf mr-1"></i> Cetak Laporan (PDF)
                                </button>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table id="table-scores" class="table table-hover table-bordered border-0" style="width: 100%;">
                                <thead class="bg-light">
                                    <tr>
                                        <th class="text-center" style="width: 40px;">No</th>
                                        <th style="width: 110px;">NISN</th>
                                        <th>Nama Siswa (Moodle)</th>
                                        <th>Nama Siswa (SIMANSA)</th>
                                        <th style="width: 110px;">Rombel</th>
                                        <th class="text-center" style="width: 100px;">Nilai Murni</th>
                                        <th class="text-center" style="width: 110px;">Nilai Akhir</th>
                                        <th class="text-center" style="width: 150px;">Waktu Selesai</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- MODAL 1: Lihat Detail Butir Soal (Lengkap dengan Gambar & MathJax) --}}
<div class="modal fade" id="modal-view-question" tabindex="-1" role="dialog" aria-labelledby="modalViewQuestionLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable" role="document">
        <div class="modal-content shadow-lg border-0" style="border-radius: 12px;">
            <div class="modal-header bg-gradient-primary text-white">
                <h5 class="modal-title font-weight-bold" id="modalViewQuestionLabel">
                    <i class="fas fa-file-alt mr-2"></i> Detail Butir Soal #<span id="vq-slot"></span>
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-4">
                {{-- Badges Header --}}
                <div class="d-flex flex-wrap align-items-center justify-content-between mb-3 pb-2 border-bottom">
                    <div>
                        <span class="badge badge-secondary px-2 py-1 mr-1" id="vq-type">MULTICHOICE</span>
                        <span class="text-xs text-muted" id="vq-name"></span>
                    </div>
                    <div class="d-flex gap-2">
                        <span class="badge badge-info px-2 py-1" id="vq-facility">Tingkat Kesukaran: -</span>
                        <span class="badge badge-primary px-2 py-1" id="vq-discriminator">Daya Pembeda: -</span>
                        <span class="badge badge-success px-2 py-1" id="vq-recommendation">-</span>
                    </div>
                </div>

                {{-- Question Text Container --}}
                <div class="mb-4">
                    <h6 class="font-weight-bold text-dark mb-2">Teks Soal:</h6>
                    <div id="vq-questiontext" class="question-content border rounded p-3 bg-light shadow-xs" style="min-height: 80px; font-size: 1.05rem; line-height: 1.6;">
                    </div>
                </div>

                {{-- Choices Container --}}
                <div id="vq-choices-section" class="mb-3 d-none">
                    <h6 class="font-weight-bold text-dark mb-2">Pilihan Jawaban:</h6>
                    <div id="vq-choices-list" class="d-flex flex-column gap-2"></div>
                </div>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary font-weight-bold" data-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

{{-- MODAL 2: Simpan ke Arsip SIMANSA --}}
<div class="modal fade" id="modal-save-archive" tabindex="-1" role="dialog" aria-labelledby="modalSaveArchiveLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content shadow-lg border-0" style="border-radius: 12px;">
            <div class="modal-header bg-gradient-success text-white">
                <h5 class="modal-title font-weight-bold" id="modalSaveArchiveLabel">
                    <i class="fas fa-save mr-2"></i> Simpan ke Arsip Kuis SIMANSA
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="form-save-archive">
                <div class="modal-body p-4">
                    <p class="text-sm text-muted mb-3">
                        Data kuis, analisis butir soal, dan seluruh nilai siswa akan direkam secara permanen di SIMANSA. Data ini tetap dapat diakses dan dicetak meskipun kuis/kursus di Moodle di-hidden atau dihapus oleh admin.
                    </p>

                    <div class="form-group mb-3">
                        <label class="font-weight-bold text-sm">Nama Arsip / Kuis <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="save-assessment-name" required placeholder="Contoh: Asesmen Sumatif Tengah Semester Matematika">
                    </div>

                    <div class="row">
                        <div class="col-md-6 form-group mb-3">
                            <label class="font-weight-bold text-sm">Tahun Ajaran <span class="text-danger">*</span></label>
                            <select class="form-control select2bs4" id="save-tahun-ajaran-id" required>
                                @foreach($tahunAjarans ?? [] as $ta)
                                    <option value="{{ $ta->id }}" {{ ($selectedTahunAjaranId ?? null) == $ta->id ? 'selected' : '' }}>
                                        {{ $ta->nama ?? $ta->tahun_ajaran }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 form-group mb-3">
                            <label class="font-weight-bold text-sm">Semester <span class="text-danger">*</span></label>
                            <select class="form-control select2bs4" id="save-semester" required>
                                <option value="Ganjil" {{ ($selectedSemester ?? '') == 'Ganjil' ? 'selected' : '' }}>Ganjil</option>
                                <option value="Genap" {{ ($selectedSemester ?? '') == 'Genap' ? 'selected' : '' }}>Genap</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group mb-3">
                        <label class="font-weight-bold text-sm">Kategori Penilaian <span class="text-danger">*</span></label>
                        <select class="form-control select2bs4" id="save-category" required>
                            <option value="harian">Penilaian Harian (PH)</option>
                            <option value="tengah_semester">Penilaian Tengah Semester (PTS / ATS)</option>
                            <option value="akhir_semester">Penilaian Akhir Semester (PAS / ASAS)</option>
                            <option value="tugas">Tugas / Kuis Singkat</option>
                            <option value="proyek">Proyek / Praktik</option>
                        </select>
                    </div>

                    <div class="form-group mb-3">
                        <label class="font-weight-bold text-sm">Mata Pelajaran</label>
                        <select class="form-control select2bs4" id="save-mapel-id">
                            <option value="">-- Pilih Mata Pelajaran (Opsional) --</option>
                            @foreach($mapels ?? [] as $mp)
                                <option value="{{ $mp->id }}">{{ $mp->nama_mapel }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group mb-3">
                        <label class="font-weight-bold text-sm">Rombel / Kelas Sasaran</label>
                        <select class="form-control select2bs4" id="save-rombel-id">
                            <option value="">-- Semua Rombel / Gabungan --</option>
                            @foreach($rombels ?? [] as $rb)
                                <option value="{{ $rb->id }}">{{ $rb->nama_kelas }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group mb-0">
                        <label class="font-weight-bold text-sm">Catatan / Keterangan</label>
                        <textarea class="form-control" id="save-notes" rows="2" placeholder="Catatan tambahan guru pengampu (opsional)..."></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary font-weight-bold" data-dismiss="modal">Batal</button>
                    <button type="submit" id="btn-submit-save-archive" class="btn btn-success font-weight-bold">
                        <i class="fas fa-save mr-1"></i> Simpan Sekarang
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- MODAL 3: Sinkronisasi ke Buku Nilai Siswa --}}
<div class="modal fade" id="modal-sync-gradebook" tabindex="-1" role="dialog" aria-labelledby="modalSyncGradebookLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content shadow-lg border-0" style="border-radius: 12px;">
            <div class="modal-header bg-gradient-indigo text-white">
                <h5 class="modal-title font-weight-bold" id="modalSyncGradebookLabel">
                    <i class="fas fa-sync-alt mr-2"></i> Sinkron ke Buku Nilai Siswa
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="form-sync-gradebook">
                <div class="modal-body p-4">
                    <div class="alert alert-info border-0 shadow-sm text-sm mb-3">
                        <i class="fas fa-info-circle mr-1"></i> Nilai hasil kuis siswa yang terhubung dengan NISN SIMANSA akan otomatis dimasukkan ke tabel <strong>Penilaian Siswa</strong>, sehingga langsung terlihat oleh Guru Mata Pelajaran dan Wali Kelas.
                    </div>

                    <div class="form-group mb-3">
                        <label class="font-weight-bold text-sm">Judul Penilaian <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="sync-assessment-name" required placeholder="Contoh: Kuis 1 - Aljabar Linier">
                    </div>

                    <div class="form-group mb-3">
                        <label class="font-weight-bold text-sm">Kategori Penilaian <span class="text-danger">*</span></label>
                        <select class="form-control select2bs4" id="sync-category" required>
                            <option value="harian">Penilaian Harian (PH)</option>
                            <option value="tengah_semester">Penilaian Tengah Semester (PTS / ATS)</option>
                            <option value="akhir_semester">Penilaian Akhir Semester (PAS / ASAS)</option>
                            <option value="tugas">Tugas</option>
                            <option value="proyek">Proyek</option>
                        </select>
                    </div>

                    <div class="row">
                        <div class="col-md-6 form-group mb-3">
                            <label class="font-weight-bold text-sm">Tahun Ajaran <span class="text-danger">*</span></label>
                            <select class="form-control select2bs4" id="sync-tahun-ajaran-id" required>
                                @foreach($tahunAjarans ?? [] as $ta)
                                    <option value="{{ $ta->id }}" {{ ($selectedTahunAjaranId ?? null) == $ta->id ? 'selected' : '' }}>
                                        {{ $ta->nama ?? $ta->tahun_ajaran }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 form-group mb-3">
                            <label class="font-weight-bold text-sm">Semester <span class="text-danger">*</span></label>
                            <select class="form-control select2bs4" id="sync-semester" required>
                                <option value="Ganjil" {{ ($selectedSemester ?? '') == 'Ganjil' ? 'selected' : '' }}>Ganjil</option>
                                <option value="Genap" {{ ($selectedSemester ?? '') == 'Genap' ? 'selected' : '' }}>Genap</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group mb-3">
                        <label class="font-weight-bold text-sm">Mata Pelajaran <span class="text-danger">*</span></label>
                        <select class="form-control select2bs4" id="sync-mapel-id" required>
                            <option value="">-- Pilih Mata Pelajaran --</option>
                            @foreach($mapels ?? [] as $mp)
                                <option value="{{ $mp->id }}">{{ $mp->nama_mapel }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group mb-3">
                        <label class="font-weight-bold text-sm">Rombel / Kelas (Opsional filter)</label>
                        <select class="form-control select2bs4" id="sync-rombel-id">
                            <option value="">-- Otomatis Sesuai Siswa --</option>
                            @foreach($rombels ?? [] as $rb)
                                <option value="{{ $rb->id }}">{{ $rb->nama_kelas }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group mb-0">
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="sync-overwrite" checked>
                            <label class="custom-control-label font-weight-normal text-sm" for="sync-overwrite">
                                Timpa nilai jika siswa sudah memiliki penilaian dengan judul yang sama
                            </label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary font-weight-bold" data-dismiss="modal">Batal</button>
                    <button type="submit" id="btn-submit-sync-gradebook" class="btn btn-primary font-weight-bold">
                        <i class="fas fa-check-double mr-1"></i> Mulai Sinkronisasi
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- MODAL 4: Riwayat Arsip SIMANSA --}}
<div class="modal fade" id="modal-archives-history" tabindex="-1" role="dialog" aria-labelledby="modalArchivesHistoryLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable" role="document">
        <div class="modal-content shadow-lg border-0" style="border-radius: 12px;">
            <div class="modal-header bg-gradient-secondary text-white">
                <h5 class="modal-title font-weight-bold" id="modalArchivesHistoryLabel">
                    <i class="fas fa-history mr-2"></i> Riwayat Arsip Kuis SIMANSA (Data Permanen)
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-4">
                <div class="table-responsive">
                    <table id="table-archives-history" class="table table-hover table-bordered border-0" style="width: 100%;">
                        <thead class="bg-light">
                            <tr>
                                <th class="text-center" style="width: 40px;">No</th>
                                <th>Nama Asesmen / Kuis</th>
                                <th>Mata Pelajaran</th>
                                <th>Kelas</th>
                                <th class="text-center">Kategori</th>
                                <th class="text-center">Tahun/Sem</th>
                                <th class="text-center">Status</th>
                                <th class="text-center" style="width: 140px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary font-weight-bold" data-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('css')
<style>
#analytics-tabs .nav-link {
    color: #64748b;
    border: none;
    border-bottom: 3px solid transparent;
    transition: all 0.2s ease;
}
#analytics-tabs .nav-link.active {
    color: #4f46e5 !important;
    background: transparent;
    border-bottom: 3px solid #4f46e5;
}
#analytics-tabs .nav-link:hover:not(.active) {
    color: #1e293b;
    border-bottom: 3px solid #cbd5e1;
}
.table td, .table th {
    vertical-align: middle !important;
}
.gap-2 {
    gap: 0.5rem;
}
/* Filter smooth styles */
.filter-card {
    transition: box-shadow 0.25s ease;
}
.filter-select {
    height: 34px !important;
    border-radius: 8px !important;
    border: 1px solid #cbd5e1 !important;
    background-color: #ffffff;
    font-size: 0.84rem !important;
    color: #1e293b;
    transition: border-color 0.2s ease, box-shadow 0.2s ease, background-color 0.2s ease;
}
.filter-select:focus {
    border-color: #4f46e5 !important;
    box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.15) !important;
    outline: none;
}
.filter-select:disabled {
    background-color: #f1f5f9 !important;
    color: #94a3b8 !important;
    border-color: #e2e8f0 !important;
    cursor: not-allowed;
}
.filter-select optgroup {
    font-weight: 700;
    color: #334155;
    background: #f8fafc;
    padding: 4px 6px;
}
.filter-select option {
    font-weight: normal;
    color: #0f172a;
    padding: 3px 6px;
}
.btn-pulse {
    animation: btnPulse 1.8s infinite;
}
@keyframes btnPulse {
    0% {
        box-shadow: 0 0 0 0 rgba(79, 70, 229, 0.55);
    }
    70% {
        box-shadow: 0 0 0 8px rgba(79, 70, 229, 0);
    }
    100% {
        box-shadow: 0 0 0 0 rgba(79, 70, 229, 0);
    }
}
/* Question modal & choices styles */
.question-content img {
    max-width: 100%;
    height: auto;
    border-radius: 6px;
    margin: 8px 0;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}
.choice-item {
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    padding: 10px 14px;
    background: #ffffff;
    display: flex;
    align-items: flex-start;
    gap: 12px;
    transition: all 0.2s ease;
}
.choice-item.is-correct {
    border-color: #22c55e;
    background: #f0fdf4;
}
.choice-badge {
    width: 28px;
    height: 28px;
    border-radius: 50%;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: 0.85rem;
    flex-shrink: 0;
}
.choice-item.is-correct .choice-badge {
    background: #16a34a;
    color: #fff;
}
.choice-item:not(.is-correct) .choice-badge {
    background: #f1f5f9;
    color: #475569;
}
.choice-text {
    flex: 1;
    font-size: 0.95rem;
    line-height: 1.5;
}
.choice-text img {
    max-width: 100%;
    height: auto;
    border-radius: 4px;
}
</style>
@endsection

@section('js')
<script>
window.MathJax = {
    tex: {
        inlineMath: [['$', '$'], ['\\(', '\\)']],
        displayMath: [['$$', '$$'], ['\\[', '\\]']]
    },
    svg: { fontCache: 'global' }
};
</script>
<script src="https://cdn.jsdelivr.net/npm/mathjax@3/es5/tex-mml-chtml.js" id="MathJax-script" async></script>

<script>
$(function () {
    let currentQuizId = null;
    let currentQuizName = '';
    let currentArchive = null;
    let cachedAnalysis = null;
    let cachedScores = null;
    let analysisDataTable = null;
    let scoresDataTable = null;
    let historyDataTable = null;
    let allCategories = [];

    // CSRF Setup for AJAX POST
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    // Load Categories on init
    loadCategories();

    function loadCategories() {
        $('#spin-category').removeClass('d-none');
        $.ajax({
            url: "{{ route('admin.moodle-sync.quizzes.categories') }}",
            type: 'GET',
            dataType: 'json',
            success: function (res) {
                $('#spin-category').addClass('d-none');
                if (res.success && res.data) {
                    allCategories = res.data;
                    let html = '<option value="">-- Pilih Kategori --</option>';

                    // Filter root categories (parent === 0 or depth === 1)
                    const rootCats = allCategories.filter(c => c.parent === 0 || c.depth === 1);
                    rootCats.forEach(function (cat) {
                        const catPath = cat.path || ('/' + cat.id);
                        const descendants = allCategories.filter(c => c.id === cat.id || (c.path && c.path.startsWith(catPath + '/')));
                        const totalCourses = descendants.reduce((acc, curr) => acc + (parseInt(curr.coursecount) || 0), 0);
                        const courseLabel = totalCourses > 0 ? ` (${totalCourses} kursus)` : '';
                        html += `<option value="${cat.id}">${cat.name}${courseLabel}</option>`;
                    });

                    $('#select-category').html(html).prop('disabled', false);
                } else {
                    $('#select-category').html('<option value="">-- Gagal Memuat Kategori --</option>');
                }
            },
            error: function () {
                $('#spin-category').addClass('d-none');
                $('#select-category').html('<option value="">-- Error Koneksi Moodle --</option>');
            }
        });
    }

    // Recursive helper to render hierarchical subcategory options (like converter)
    function renderSubcatTreeHtml(parentId, baseDepth) {
        let html = '';
        const children = allCategories.filter(c => c.parent === parentId);
        children.forEach(function (child) {
            const relDepth = child.depth - baseDepth;
            const indent = '　'.repeat(Math.max(0, relDepth - 1));
            const prefix = relDepth > 1 ? '└─ ' : '';
            const cCount = child.coursecount > 0 ? ` (${child.coursecount} kursus)` : '';
            html += `<option value="${child.id}">${indent}${prefix}${child.name}${cCount}</option>`;
            html += renderSubcatTreeHtml(child.id, baseDepth);
        });
        return html;
    }

    // Change Kategori -> Populate Sub Kategori Bertingkat & Auto-load Courses
    $('#select-category').on('change', function () {
        const catId = parseInt($(this).val()) || 0;

        $('#select-subcategory').html('<option value="">-- Memuat Sub Kategori... --</option>').prop('disabled', true);
        $('#select-course').html('<option value="">-- Pilih Sub Kategori Dulu --</option>').prop('disabled', true);
        $('#select-quiz').html('<option value="">-- Pilih Kursus Dulu --</option>').prop('disabled', true);
        $('#btn-load-data').prop('disabled', true).removeClass('btn-pulse');

        if (!catId) {
            $('#select-subcategory').html('<option value="">-- Pilih Kategori Dulu --</option>');
            return;
        }

        const rootCat = allCategories.find(c => c.id === catId);
        const catPath = rootCat && rootCat.path ? rootCat.path : ('/' + catId);
        const subcats = allCategories.filter(c => c.id !== catId && c.path && (c.path.startsWith(catPath + '/') || c.parent === catId));

        if (subcats.length === 0) {
            $('#select-subcategory').html(`<option value="${catId}" selected>— Langsung di Kategori Ini —</option>`).prop('disabled', true);
            loadCourses(catId);
        } else {
            const descendants = allCategories.filter(c => c.id === catId || (c.path && c.path.startsWith(catPath + '/')));
            const totalCourses = descendants.reduce((acc, curr) => acc + (parseInt(curr.coursecount) || 0), 0);
            const baseDepth = rootCat ? rootCat.depth : 1;

            let subHtml = `<option value="${catId}">— Semua Sub Kategori (${totalCourses} kursus) —</option>`;
            subHtml += renderSubcatTreeHtml(catId, baseDepth);

            $('#select-subcategory').html(subHtml).prop('disabled', false);
            loadCourses(catId);
        }
    });

    // Change Sub Kategori -> Load Courses
    $('#select-subcategory').on('change', function () {
        const subcatId = parseInt($(this).val()) || parseInt($('#select-category').val()) || 0;
        if (!subcatId) return;

        $('#select-course').html('<option value="">-- Memuat Kursus... --</option>').prop('disabled', true);
        $('#select-quiz').html('<option value="">-- Pilih Kursus Dulu --</option>').prop('disabled', true);
        $('#btn-load-data').prop('disabled', true).removeClass('btn-pulse');

        loadCourses(subcatId);
    });

    function loadCourses(targetCategoryId) {
        $('#spin-course').removeClass('d-none');
        $('#select-course').html('<option value="">Memuat Kursus / Mapel...</option>').prop('disabled', true);

        $.ajax({
            url: "{{ route('admin.moodle-sync.quizzes.courses') }}",
            type: 'GET',
            data: { category: targetCategoryId },
            dataType: 'json',
            success: function (res) {
                $('#spin-course').addClass('d-none');
                if (res.success && res.data && res.data.length > 0) {
                    const courses = res.data;

                    const groups = {};
                    courses.forEach(function (c) {
                        const key = c.catname || 'Lainnya';
                        if (!groups[key]) groups[key] = [];
                        groups[key].push(c);
                    });

                    let html = `<option value="">-- Pilih Kursus / Mapel (${courses.length}) --</option>`;
                    const groupKeys = Object.keys(groups);

                    if (groupKeys.length > 1) {
                        groupKeys.forEach(function (g) {
                            html += `<optgroup label="📁 ${g}">`;
                            groups[g].forEach(function (c) {
                                const qCount = c.quiz_count > 0 ? ` [${c.quiz_count} kuis]` : ' [0 kuis]';
                                html += `<option value="${c.id}">${c.fullname}${qCount}</option>`;
                            });
                            html += '</optgroup>';
                        });
                    } else {
                        courses.forEach(function (c) {
                            const qCount = c.quiz_count > 0 ? ` [${c.quiz_count} kuis]` : ' [0 kuis]';
                            html += `<option value="${c.id}">${c.fullname}${qCount}</option>`;
                        });
                    }

                    $('#select-course').html(html).prop('disabled', false);
                } else {
                    $('#select-course').html('<option value="">-- Tidak ada kursus ditemukan --</option>').prop('disabled', true);
                }
            },
            error: function () {
                $('#spin-course').addClass('d-none');
                $('#select-course').html('<option value="">-- Gagal Memuat Kursus --</option>').prop('disabled', true);
            }
        });
    }

    // Change Course -> Load Quizzes
    $('#select-course').on('change', function () {
        const courseId = parseInt($(this).val()) || 0;
        $('#select-quiz').html('<option value="">-- Memuat Kuis... --</option>').prop('disabled', true);
        $('#btn-load-data').prop('disabled', true).removeClass('btn-pulse');

        if (!courseId) {
            $('#select-quiz').html('<option value="">-- Pilih Kursus Dulu --</option>');
            return;
        }

        $('#spin-quiz').removeClass('d-none');

        $.ajax({
            url: "{{ route('admin.moodle-sync.quizzes.list') }}",
            type: 'GET',
            data: { course: courseId },
            dataType: 'json',
            success: function (res) {
                $('#spin-quiz').addClass('d-none');
                if (res.success && res.data && res.data.length > 0) {
                    let html = '<option value="">-- Pilih Kuis / Ujian --</option>';
                    res.data.forEach(function (q) {
                        html += `<option value="${q.id}" data-name="${q.name}">${q.name} (${q.attempt_count} peserta)</option>`;
                    });
                    $('#select-quiz').html(html).prop('disabled', false);

                    if (res.data.length === 1) {
                        $('#select-quiz').val(res.data[0].id).trigger('change');
                    }
                } else {
                    $('#select-quiz').html('<option value="">-- Tidak ada kuis pada kursus ini --</option>').prop('disabled', true);
                }
            },
            error: function () {
                $('#spin-quiz').addClass('d-none');
                $('#select-quiz').html('<option value="">-- Gagal Memuat Kuis --</option>').prop('disabled', true);
            }
        });
    });

    // Change Quiz -> Enable Button with Pulse
    $('#select-quiz').on('change', function () {
        const quizId = $(this).val();
        if (quizId) {
            currentQuizName = $(this).find(':selected').data('name') || '';
            $('#btn-load-data').prop('disabled', false).addClass('btn-pulse');
        } else {
            $('#btn-load-data').prop('disabled', true).removeClass('btn-pulse');
        }
    });

    // Click Tampilkan
    $('#btn-load-data').on('click', function () {
        currentQuizId = $('#select-quiz').val();
        currentQuizName = $('#select-quiz').find(':selected').data('name') || '';
        if (!currentQuizId) return;
        $(this).removeClass('btn-pulse');
        loadQuizAllData(currentQuizId, false);
    });

    // Click Hitung Ulang Moodle
    $('#btn-force-refresh').on('click', function () {
        if (!currentQuizId) return;
        Swal.fire({
            title: 'Hitung Ulang Statistik?',
            text: 'Moodle akan menghitung ulang formula psikometrik untuk kuis ini dari database attempts terbaru.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#4f46e5',
            confirmButtonText: 'Ya, Hitung Ulang',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                loadQuizAllData(currentQuizId, true);
            }
        });
    });

    function loadQuizAllData(quizId, forceRefresh) {
        $('#initial-empty-state').addClass('d-none');
        $('#results-section').addClass('d-none');
        $('#loading-state').removeClass('d-none');

        // Parallel fetch: Analysis, Scores, and Archive status
        const p1 = $.ajax({
            url: "{{ route('admin.moodle-sync.quizzes.analysis') }}",
            type: 'GET',
            data: { quiz: quizId, refresh: forceRefresh ? 1 : 0 },
            dataType: 'json'
        });

        const p2 = $.ajax({
            url: "{{ route('admin.moodle-sync.quizzes.scores') }}",
            type: 'GET',
            data: { quiz: quizId },
            dataType: 'json'
        });

        const p3 = $.ajax({
            url: "{{ route('admin.moodle-sync.quizzes.archives') }}",
            type: 'GET',
            data: { quiz_id: quizId },
            dataType: 'json'
        });

        $.when(p1, p2, p3).done(function (resAnalysis, resScores, resArchives) {
            cachedAnalysis = resAnalysis[0];
            cachedScores = resScores[0];
            const archivesData = resArchives[0]?.data || [];

            $('#loading-state').addClass('d-none');
            $('#results-section').removeClass('d-none');

            renderAnalysisData(cachedAnalysis);
            renderScoresData(cachedScores);

            if (archivesData.length > 0) {
                currentArchive = archivesData[0];
                updateArchiveToolbarUI(currentArchive);
            } else {
                currentArchive = null;
                updateArchiveToolbarUI(null);
            }
        }).fail(function (xhr) {
            $('#loading-state').addClass('d-none');
            $('#initial-empty-state').removeClass('d-none');
            Swal.fire('Error', 'Gagal memuat data dari Moodle: ' + (xhr.responseJSON?.error || xhr.statusText), 'error');
        });
    }

    function updateArchiveToolbarUI(archive) {
        if (archive) {
            let statusText = archive.is_locked ? 'Terkunci Permanen' : 'Tersimpan (Bisa Diperbarui)';
            let statusClass = archive.is_locked ? 'badge-danger' : 'badge-success';
            let icon = archive.is_locked ? 'fa-lock' : 'fa-check-circle';

            $('#archive-status-badge')
                .removeClass('badge-light badge-success badge-danger')
                .addClass(statusClass);
            $('#archive-status-text').html(`<i class="fas ${icon} mr-1"></i> ${archive.assessment_name} — ${statusText}`);
            $('#archive-time-info').text(`Disimpan: ${archive.created_at || ''}`);

            // PDF export button
            const pdfUrl = "{{ route('admin.moodle-sync.quizzes.export-pdf', ':id') }}".replace(':id', archive.id);
            $('#btn-export-pdf').attr('data-pdf-url', pdfUrl).removeClass('d-none');

            // Lock / Unlock button
            $('#btn-toggle-lock').removeClass('d-none');
            if (archive.is_locked) {
                $('#btn-toggle-lock')
                    .removeClass('btn-warning')
                    .addClass('btn-secondary')
                    .html('<i class="fas fa-lock-open mr-1"></i> Buka Kunci');
            } else {
                $('#btn-toggle-lock')
                    .removeClass('btn-secondary')
                    .addClass('btn-warning')
                    .html('<i class="fas fa-lock mr-1"></i> Kunci Data');
            }
        } else {
            $('#archive-status-badge')
                .removeClass('badge-success badge-danger')
                .addClass('badge-light');
            $('#archive-status-text').html('<i class="fas fa-database text-secondary mr-1"></i> Belum Diarsipkan di SIMANSA');
            $('#archive-time-info').text('');
            $('#btn-export-pdf').removeAttr('data-pdf-url').removeClass('d-none');
            $('#btn-toggle-lock').addClass('d-none');
        }
    }

    function renderAnalysisData(res) {
        if (!res.success) {
            Swal.fire('Perhatian', res.error || 'Statistik kuis belum dapat dihitung.', 'warning');
            return;
        }

        const stats = res.statistics || {};
        const questions = res.questions || [];

        $('#stat-attempts').text(stats.attempts_count || 0);
        $('#stat-avg').text(stats.avg_grade || 0);
        $('#stat-questions').text(questions.length);

        const cic = stats.cronbach_alpha !== undefined ? stats.cronbach_alpha : 0;
        let cicLabel = 'Rendah';
        let cicClass = 'badge-danger';
        if (cic >= 80) {
            cicLabel = 'Sangat Tinggi';
            cicClass = 'badge-success';
        } else if (cic >= 60) {
            cicLabel = 'Tinggi';
            cicClass = 'badge-info';
        } else if (cic >= 40) {
            cicLabel = 'Sedang';
            cicClass = 'badge-warning';
        }
        $('#stat-reliability').html(`${cic}% <span class="badge ${cicClass} ml-1" style="font-size:0.5em; vertical-align:middle;">${cicLabel}</span>`);

        let accepted = 0, revision = 0, rejected = 0, errorKey = 0;
        questions.forEach(function (q) {
            if (q.status_badge === 'success' || q.status_badge === 'info') accepted++;
            else if (q.status_badge === 'warning') revision++;
            else if (q.status_badge === 'danger') rejected++;
            else if (q.status_badge === 'dark') errorKey++;
        });

        $('#badge-total-items').text(`Total: ${questions.length} Soal`);
        $('#badge-accepted-items').text(`Diterima: ${accepted}`);
        $('#badge-revision-items').text(`Perlu Revisi: ${revision}`);
        $('#badge-rejected-items').text(`Ditolak: ${rejected}`);
        $('#badge-error-key-items').text(`Kunci Salah: ${errorKey}`);

        if (analysisDataTable) {
            analysisDataTable.destroy();
        }

        let rowsHtml = '';
        questions.forEach(function (q) {
            rowsHtml += `
                <tr>
                    <td class="text-center font-weight-bold">${q.slot}</td>
                    <td>
                        <div class="font-weight-bold text-dark">${q.name}</div>
                        <div class="text-muted text-sm mt-1" style="font-style: italic;">${q.preview || '-'}</div>
                    </td>
                    <td class="text-center">
                        <button type="button" class="btn btn-xs btn-outline-primary btn-view-question font-weight-bold" data-slot="${q.slot}">
                            <i class="fas fa-eye mr-1"></i> Soal
                        </button>
                    </td>
                    <td class="text-center">
                        <span class="badge badge-light border text-secondary px-2 py-1">${q.type}</span>
                    </td>
                    <td class="text-center">
                        <span class="badge badge-${q.kesukaran_badge} px-2 py-1 font-weight-bold mb-1">${q.kesukaran}</span>
                        <div class="text-xs text-muted font-weight-bold">${q.facility_percent}%</div>
                    </td>
                    <td class="text-center">
                        <span class="badge badge-${q.pembeda_badge} px-2 py-1 font-weight-bold mb-1">${q.pembeda}</span>
                        <div class="text-xs text-muted font-weight-bold">${q.discrimination_index !== null ? q.discrimination_index + '%' : '-'}</div>
                    </td>
                    <td class="text-center">
                        <span class="badge badge-${q.status_badge} px-2 py-1 font-weight-bold">${q.status}</span>
                    </td>
                </tr>
            `;
        });

        $('#table-analysis tbody').html(rowsHtml);
        analysisDataTable = $('#table-analysis').DataTable({
            pageLength: 25,
            language: {
                search: "Cari Soal:",
                lengthMenu: "Tampilkan _MENU_ butir",
                info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ butir soal",
                paginate: { first: "Awal", last: "Akhir", next: "→", previous: "←" }
            }
        });
    }

    function renderScoresData(res) {
        if (!res.success) return;

        const scores = res.data || [];

        if (scoresDataTable) {
            scoresDataTable.destroy();
        }

        let rowsHtml = '';
        scores.forEach(function (s, index) {
            const simansa = s.simansa || {};
            const simansaName = simansa.nama ? `<span class="font-weight-bold text-dark">${simansa.nama}</span>` : `<span class="text-muted font-italic">(Belum cocok di SIMANSA)</span>`;
            const rombelBadge = simansa.rombel && simansa.rombel !== '-' 
                ? `<span class="badge badge-info px-2 py-1">${simansa.rombel}</span>` 
                : `<span class="badge badge-secondary px-2 py-1">-</span>`;

            rowsHtml += `
                <tr>
                    <td class="text-center font-weight-bold">${index + 1}</td>
                    <td><code>${s.username}</code></td>
                    <td>${s.fullname}</td>
                    <td>${simansaName}</td>
                    <td class="text-center">${rombelBadge}</td>
                    <td class="text-center font-weight-bold text-secondary">${s.raw_score} / ${s.max_score}</td>
                    <td class="text-center">
                        <span class="badge badge-success px-2 py-1" style="font-size: 0.95rem;">${s.normalized_100}</span>
                    </td>
                    <td class="text-center text-sm text-muted">${s.finished_at || '-'}</td>
                </tr>
            `;
        });

        $('#table-scores tbody').html(rowsHtml);
        scoresDataTable = $('#table-scores').DataTable({
            pageLength: 25,
            language: {
                search: "Cari Siswa / NISN:",
                lengthMenu: "Tampilkan _MENU_ siswa",
                info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ siswa",
                paginate: { first: "Awal", last: "Akhir", next: "→", previous: "←" }
            }
        });
    }

    // CLICK: Lihat Detail Butir Soal (Lengkap dengan Gambar & MathJax)
    $(document).on('click', '.btn-view-question', function () {
        const slot = $(this).data('slot');
        if (!cachedAnalysis || !cachedAnalysis.questions) return;

        const q = cachedAnalysis.questions.find(item => item.slot == slot);
        if (!q) return;

        $('#vq-slot').text(q.slot);
        $('#vq-name').text(q.name);
        $('#vq-type').text(q.type.toUpperCase());
        $('#vq-facility').text(`Tingkat Kesukaran: ${q.facility_percent}% (${q.kesukaran})`);
        $('#vq-discriminator').text(`Daya Pembeda: ${q.discrimination_index !== null ? q.discrimination_index + '%' : '-'} (${q.pembeda})`);
        $('#vq-recommendation').text(q.status)
            .removeClass('badge-success badge-warning badge-danger badge-info badge-dark')
            .addClass('badge-' + q.status_badge);

        // Render Question Text (HTML with proxy images)
        const qHtml = q.questiontext_html || q.preview || '<span class="text-muted">(Tidak ada konten teks soal)</span>';
        $('#vq-questiontext').html(qHtml);

        // Render Choices if available
        if (q.choices && q.choices.length > 0) {
            $('#vq-choices-section').removeClass('d-none');
            let choicesHtml = '';
            const labels = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H'];

            q.choices.forEach(function (c, idx) {
                const label = labels[idx] || (idx + 1);
                const isCorrect = c.is_correct || c.fraction > 0;
                const correctClass = isCorrect ? 'is-correct' : '';
                const correctBadge = isCorrect ? '<span class="badge badge-success ml-2"><i class="fas fa-check mr-1"></i> Kunci Benar (100%)</span>' : '';

                choicesHtml += `
                    <div class="choice-item ${correctClass}">
                        <div class="choice-badge">${label}</div>
                        <div class="choice-text">
                            ${c.text || '-'} ${correctBadge}
                        </div>
                    </div>
                `;
            });

            $('#vq-choices-list').html(choicesHtml);
        } else {
            $('#vq-choices-section').addClass('d-none');
            $('#vq-choices-list').html('');
        }

        $('#modal-view-question').modal('show');

        // MathJax 3 Typeset
        setTimeout(function () {
            if (window.MathJax && window.MathJax.typesetPromise) {
                window.MathJax.typesetPromise([document.querySelector('#modal-view-question')]).catch(function (err) {
                    console.warn('MathJax typeset error:', err);
                });
            }
        }, 150);
    });

    // CLICK: Cetak Laporan PDF
    $(document).on('click', '#btn-export-pdf, .btn-print-pdf-trigger', function (e) {
        e.preventDefault();
        if (currentArchive && currentArchive.id) {
            const pdfUrl = "{{ route('admin.moodle-sync.quizzes.export-pdf', ':id') }}".replace(':id', currentArchive.id);
            window.open(pdfUrl, '_blank');
            return;
        }

        if (!currentQuizId) {
            Swal.fire('Perhatian', 'Silakan pilih kuis dan klik Tampilkan Analisis terlebih dahulu.', 'warning');
            return;
        }

        Swal.fire({
            title: 'Simpan ke Arsip SIMANSA?',
            text: 'Untuk mencetak dokumen laporan analisis resmi (ber-kop madrasah, butir soal, dan tanda tangan), data kuis perlu disimpan ke Arsip terlebih dahulu.',
            icon: 'info',
            showCancelButton: true,
            confirmButtonColor: '#28a745',
            cancelButtonColor: '#6c757d',
            confirmButtonText: '<i class="fas fa-save mr-1"></i> Simpan & Buka PDF',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                $('#modal-save-archive').data('auto-open-pdf', true);
                $('#save-assessment-name').val(currentArchive ? currentArchive.assessment_name : currentQuizName);
                $('#modal-save-archive').modal('show');
            }
        });
    });

    // OPEN: Modal Simpan Arsip
    $('#btn-open-save-modal').on('click', function () {
        if (!currentQuizId) {
            Swal.fire('Perhatian', 'Silakan pilih kuis dan klik Tampilkan Analisis terlebih dahulu.', 'warning');
            return;
        }
        $('#modal-save-archive').data('auto-open-pdf', false);
        $('#save-assessment-name').val(currentArchive ? currentArchive.assessment_name : currentQuizName);
        $('#modal-save-archive').modal('show');
    });

    // SUBMIT: Simpan Arsip
    $('#form-save-archive').on('submit', function (e) {
        e.preventDefault();
        if (!currentQuizId) return;

        const payload = {
            quiz_id: currentQuizId,
            quiz_name: currentQuizName || $('#select-quiz option:selected').text().trim(),
            course_id: $('#select-course').val(),
            course_name: $('#select-course option:selected').text().trim(),
            category_id: $('#select-subcategory').val() || $('#select-category').val(),
            category_name: ($('#select-subcategory option:selected').text() || $('#select-category option:selected').text()).trim(),
            tahun_pelajaran_id: $('#save-tahun-ajaran-id').val(),
            semester: $('#save-semester').val(),
            kategori_penilaian: $('#save-category').val(),
            nama_penilaian: $('#save-assessment-name').val(),
            mata_pelajaran_id: $('#save-mapel-id').val() || null,
            kelas_id: $('#save-rombel-id').val() || null,
            catatan: $('#save-notes').val(),
            statistics: cachedAnalysis ? (cachedAnalysis.statistics || {}) : {},
            items_analysis: cachedAnalysis ? (cachedAnalysis.questions || []) : [],
            scores: cachedScores ? (cachedScores.data || []) : []
        };

        const submitBtn = $('#btn-submit-save-archive');
        submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Menyimpan...');

        $.ajax({
            url: "{{ route('admin.moodle-sync.quizzes.save-archive') }}",
            type: 'POST',
            data: payload,
            dataType: 'json',
            success: function (res) {
                submitBtn.prop('disabled', false).html('<i class="fas fa-save mr-1"></i> Simpan Sekarang');
                if (res.success && (res.data || res.archive)) {
                    currentArchive = res.data || res.archive;
                    updateArchiveToolbarUI(currentArchive);
                    $('#modal-save-archive').modal('hide');

                    const shouldOpenPdf = $('#modal-save-archive').data('auto-open-pdf');
                    $('#modal-save-archive').data('auto-open-pdf', false);

                    if (shouldOpenPdf) {
                        const pdfUrl = "{{ route('admin.moodle-sync.quizzes.export-pdf', ':id') }}".replace(':id', currentArchive.id);
                        window.open(pdfUrl, '_blank');
                    }

                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil Diarsipkan!',
                        text: shouldOpenPdf ? 'Data kuis berhasil disimpan dan laporan PDF sedang dibuka.' : 'Data kuis dan analisis butir soal telah direkam secara permanen di SIMANSA.',
                        timer: 2000,
                        showConfirmButton: false
                    });
                } else {
                    Swal.fire('Gagal Menyimpan', res.error || 'Terjadi kesalahan sistem.', 'error');
                }
            },
            error: function (xhr) {
                submitBtn.prop('disabled', false).html('<i class="fas fa-save mr-1"></i> Simpan Sekarang');
                Swal.fire('Gagal', xhr.responseJSON?.error || xhr.statusText, 'error');
            }
        });
    });

    // CLICK: Kunci / Buka Kunci Arsip
    $('#btn-toggle-lock').on('click', function () {
        if (!currentArchive) return;

        if (currentArchive.is_locked) {
            Swal.fire({
                title: 'Buka Kunci Arsip?',
                text: 'Arsip ini dapat diperbarui kembali jika kuis di Moodle dijalankan ulang.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#4f46e5',
                confirmButtonText: 'Ya, Buka Kunci',
                cancelButtonText: 'Batal'
            }).then((res) => {
                if (res.isConfirmed) {
                    const unlockUrl = "{{ route('admin.moodle-sync.quizzes.unlock-archive', ':id') }}".replace(':id', currentArchive.id);
                    $.post(unlockUrl, {}, function (resp) {
                        if (resp.success) {
                            currentArchive.is_locked = false;
                            updateArchiveToolbarUI(currentArchive);
                            Swal.fire('Terbuka', 'Kunci arsip berhasil dibuka.', 'success');
                        }
                    });
                }
            });
        } else {
            Swal.fire({
                title: 'Kunci Data Arsip?',
                text: 'Data analisis soal dan nilai siswa akan dibekukan permanen. Sangat disarankan setelah ujian selesai dilaksanakan.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d97706',
                confirmButtonText: 'Ya, Kunci Permanen',
                cancelButtonText: 'Batal'
            }).then((res) => {
                if (res.isConfirmed) {
                    const lockUrl = "{{ route('admin.moodle-sync.quizzes.lock-archive', ':id') }}".replace(':id', currentArchive.id);
                    $.post(lockUrl, {}, function (resp) {
                        if (resp.success) {
                            currentArchive.is_locked = true;
                            updateArchiveToolbarUI(currentArchive);
                            Swal.fire('Terkunci', 'Data arsip berhasil dikunci secara permanen.', 'success');
                        }
                    });
                }
            });
        }
    });

    // OPEN: Modal Sinkron ke Buku Nilai
    $('#btn-open-sync-modal').on('click', function () {
        if (!currentQuizId) {
            Swal.fire('Perhatian', 'Silakan pilih kuis dan klik Tampilkan Analisis terlebih dahulu.', 'warning');
            return;
        }
        if (!currentArchive) {
            Swal.fire({
                title: 'Simpan Arsip Terlebih Dahulu',
                text: 'Kuis ini belum diarsipkan ke SIMANSA. Silakan klik tombol "Simpan ke SIMANSA" terlebih dahulu sebelum menyinkronkan ke Buku Nilai.',
                icon: 'info',
                confirmButtonColor: '#4f46e5',
                confirmButtonText: 'Buka Form Simpan Arsip',
                showCancelButton: true,
                cancelButtonText: 'Batal'
            }).then((res) => {
                if (res.isConfirmed) {
                    $('#btn-open-save-modal').click();
                }
            });
            return;
        }
        $('#sync-assessment-name').val(currentArchive ? (currentArchive.nama_penilaian || currentArchive.moodle_quiz_name) : currentQuizName);
        $('#modal-sync-gradebook').modal('show');
    });

    // SUBMIT: Sinkron ke Buku Nilai
    $('#form-sync-gradebook').on('submit', function (e) {
        e.preventDefault();
        if (!currentArchive) {
            Swal.fire('Perhatian', 'Silakan simpan arsip kuis terlebih dahulu.', 'warning');
            return;
        }

        const payload = {
            archive_id: currentArchive.id,
            kategori: $('#sync-category').val(),
            nama_penilaian: $('#sync-assessment-name').val(),
            mata_pelajaran_id: $('#sync-mapel-id').val(),
            kelas_id: $('#sync-rombel-id').val() || null,
            overwrite: $('#sync-overwrite').is(':checked') ? 1 : 0
        };

        const submitBtn = $('#btn-submit-sync-gradebook');
        submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Menyinkronkan...');

        $.ajax({
            url: "{{ route('admin.moodle-sync.quizzes.sync-to-penilaian') }}",
            type: 'POST',
            data: payload,
            dataType: 'json',
            success: function (res) {
                submitBtn.prop('disabled', false).html('<i class="fas fa-check-double mr-1"></i> Mulai Sinkronisasi');
                if (res.success) {
                    $('#modal-sync-gradebook').modal('hide');
                    Swal.fire({
                        icon: 'success',
                        title: 'Sinkronisasi Berhasil!',
                        html: `Berhasil mencatat <strong>${res.synced_count}</strong> nilai siswa ke dalam Buku Penilaian Siswa SIMANSA.`,
                        confirmButtonText: 'Tutup'
                    });
                } else {
                    Swal.fire('Gagal', res.error || 'Terjadi kesalahan sistem.', 'error');
                }
            },
            error: function (xhr) {
                submitBtn.prop('disabled', false).html('<i class="fas fa-check-double mr-1"></i> Mulai Sinkronisasi');
                Swal.fire('Gagal', xhr.responseJSON?.error || xhr.statusText, 'error');
            }
        });
    });

    // OPEN: Modal Riwayat Arsip SIMANSA
    $('#btn-open-history-modal').on('click', function () {
        $('#modal-archives-history').modal('show');
        loadArchivesHistory();
    });

    function loadArchivesHistory() {
        if (historyDataTable) {
            historyDataTable.destroy();
        }

        $('#table-archives-history tbody').html('<tr><td colspan="8" class="text-center py-4"><i class="fas fa-spinner fa-spin mr-2"></i> Memuat riwayat arsip...</td></tr>');

        $.ajax({
            url: "{{ route('admin.moodle-sync.quizzes.archives') }}",
            type: 'GET',
            dataType: 'json',
            success: function (res) {
                const list = res.data || [];
                let rowsHtml = '';

                if (list.length === 0) {
                    rowsHtml = '<tr><td colspan="8" class="text-center text-muted py-4">Belum ada arsip kuis yang tersimpan di SIMANSA.</td></tr>';
                } else {
                    list.forEach(function (arc, idx) {
                        const statusBadge = arc.is_locked 
                            ? '<span class="badge badge-danger px-2 py-1"><i class="fas fa-lock mr-1"></i> Terkunci</span>'
                            : '<span class="badge badge-success px-2 py-1"><i class="fas fa-unlock mr-1"></i> Terbuka</span>';

                        const pdfUrl = "{{ route('admin.moodle-sync.quizzes.export-pdf', ':id') }}".replace(':id', arc.id);

                        rowsHtml += `
                            <tr>
                                <td class="text-center font-weight-bold">${idx + 1}</td>
                                <td>
                                    <div class="font-weight-bold text-dark">${arc.assessment_name || arc.nama_penilaian || arc.moodle_quiz_name}</div>
                                    <small class="text-muted">Kuis: ${arc.moodle_quiz_name || arc.quiz_name}</small>
                                </td>
                                <td>${arc.mata_pelajaran?.nama_mapel || arc.moodle_course_name || '-'}</td>
                                <td>${arc.kelas?.nama_kelas || 'Semua'}</td>
                                <td class="text-center"><span class="badge badge-info px-2 py-1">${arc.kategori_penilaian || '-'}</span></td>
                                <td class="text-center text-sm">${arc.tahun_pelajaran?.tahun_ajaran || '-'} (${arc.semester})</td>
                                <td class="text-center">${statusBadge}</td>
                                <td class="text-center">
                                    <div class="btn-group">
                                        <a href="${pdfUrl}" target="_blank" class="btn btn-xs btn-danger" title="Unduh PDF">
                                            <i class="fas fa-file-pdf"></i>
                                        </a>
                                        <button type="button" class="btn btn-xs btn-primary btn-load-archive" data-id="${arc.id}" data-quiz-id="${arc.quiz_id || arc.moodle_quiz_id}" title="Muat di Dashboard">
                                            <i class="fas fa-folder-open"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        `;
                    });
                }

                $('#table-archives-history tbody').html(rowsHtml);
                historyDataTable = $('#table-archives-history').DataTable({
                    pageLength: 10,
                    language: {
                        search: "Cari Arsip:",
                        lengthMenu: "Tampilkan _MENU_ data",
                        info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ arsip",
                        paginate: { first: "Awal", last: "Akhir", next: "→", previous: "←" }
                    }
                });
            },
            error: function () {
                $('#table-archives-history tbody').html('<tr><td colspan="8" class="text-center text-danger py-4">Gagal memuat riwayat arsip.</td></tr>');
            }
        });
    }

    // CLICK: Muat Arsip dari Riwayat
    $(document).on('click', '.btn-load-archive', function () {
        const archiveId = $(this).data('id');
        const detailUrl = "{{ route('admin.moodle-sync.quizzes.archive-detail', ':id') }}".replace(':id', archiveId);

        $('#modal-archives-history').modal('hide');
        $('#initial-empty-state').addClass('d-none');
        $('#results-section').addClass('d-none');
        $('#loading-state').removeClass('d-none');

        $.ajax({
            url: detailUrl,
            type: 'GET',
            dataType: 'json',
            success: function (res) {
                $('#loading-state').addClass('d-none');
                $('#results-section').removeClass('d-none');

                if (res.success && res.data) {
                    currentArchive = res.data;
                    currentQuizId = currentArchive.quiz_id;
                    currentQuizName = currentArchive.quiz_name;

                    // Format analysis & score objects to match render functions
                    cachedAnalysis = {
                        success: true,
                        statistics: currentArchive.statistics || {},
                        questions: currentArchive.items_analysis || []
                    };

                    const scoresData = (currentArchive.scores || []).map(function (s) {
                        return {
                            username: s.moodle_username,
                            fullname: s.moodle_fullname,
                            raw_score: s.raw_score,
                            max_score: s.max_score,
                            normalized_100: s.normalized_100,
                            finished_at: s.finished_at,
                            simansa: {
                                nama: s.nama_siswa || s.siswa?.nama_lengkap || s.moodle_fullname,
                                rombel: s.nama_kelas || s.siswa?.kelasAktif?.nama_kelas || '-'
                            }
                        };
                    });

                    cachedScores = {
                        success: true,
                        data: scoresData
                    };

                    renderAnalysisData(cachedAnalysis);
                    renderScoresData(cachedScores);
                    updateArchiveToolbarUI(currentArchive);
                }
            },
            error: function (xhr) {
                $('#loading-state').addClass('d-none');
                $('#initial-empty-state').removeClass('d-none');
                Swal.fire('Error', 'Gagal memuat arsip: ' + (xhr.responseJSON?.error || xhr.statusText), 'error');
            }
        });
    });
});
</script>
@endsection
