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
                            <div>
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
                                        <th class="text-center" style="width: 50px;">No</th>
                                        <th>Pertanyaan / Cuplikan Soal</th>
                                        <th class="text-center" style="width: 120px;">Tipe</th>
                                        <th class="text-center" style="width: 140px;">Kesukaran (P)</th>
                                        <th class="text-center" style="width: 150px;">Daya Pembeda (D)</th>
                                        <th class="text-center" style="width: 160px;">Rekomendasi</th>
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
</style>
@endsection

@section('js')
<script>
$(function () {
    let currentQuizId = null;
    let analysisDataTable = null;
    let scoresDataTable = null;
    let allCategories = [];

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
                        // Calculate total courses in this category and all descendants
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

        // Reset downstream
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
            // Direct category without subcategories
            $('#select-subcategory').html(`<option value="${catId}" selected>— Langsung di Kategori Ini —</option>`).prop('disabled', true);
            loadCourses(catId);
        } else {
            // Build hierarchical tree
            const descendants = allCategories.filter(c => c.id === catId || (c.path && c.path.startsWith(catPath + '/')));
            const totalCourses = descendants.reduce((acc, curr) => acc + (parseInt(curr.coursecount) || 0), 0);
            const baseDepth = rootCat ? rootCat.depth : 1;

            let subHtml = `<option value="${catId}">— Semua Sub Kategori (${totalCourses} kursus) —</option>`;
            subHtml += renderSubcatTreeHtml(catId, baseDepth);

            $('#select-subcategory').html(subHtml).prop('disabled', false);

            // Automatically load courses for the root category (or all subcategories)
            loadCourses(catId);
        }
    });

    // Change Sub Kategori -> Load Courses
    $('#select-subcategory').on('change', function () {
        const subcatId = parseInt($(this).val()) || parseInt($('#select-category').val()) || 0;
        if (!subcatId) return;

        // Reset downstream
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

                    // Group courses by subcategory (catname) like in converter
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
                        html += `<option value="${q.id}">${q.name} (${q.attempt_count} peserta)</option>`;
                    });
                    $('#select-quiz').html(html).prop('disabled', false);

                    // Auto-select if only 1 quiz exists
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
            $('#btn-load-data').prop('disabled', false).addClass('btn-pulse');
        } else {
            $('#btn-load-data').prop('disabled', true).removeClass('btn-pulse');
        }
    });

    // Click Tampilkan
    $('#btn-load-data').on('click', function () {
        currentQuizId = $('#select-quiz').val();
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

        // Parallel fetch: Analysis & Scores
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

        $.when(p1, p2).done(function (resAnalysis, resScores) {
            const dataAnalysis = resAnalysis[0];
            const dataScores = resScores[0];

            $('#loading-state').addClass('d-none');
            $('#results-section').removeClass('d-none');

            renderAnalysisData(dataAnalysis);
            renderScoresData(dataScores);
        }).fail(function (xhr) {
            $('#loading-state').addClass('d-none');
            $('#initial-empty-state').removeClass('d-none');
            Swal.fire('Error', 'Gagal memuat data dari Moodle: ' + (xhr.responseJSON?.error || xhr.statusText), 'error');
        });
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

        // Summary Quality Badges
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

        // DataTables for Analysis
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
});
</script>
@endsection
