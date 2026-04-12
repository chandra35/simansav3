@extends('adminlte::page')

@section('title', 'Konfigurasi Moodle SMART-Q')

@section('content_header')
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1><i class="fas fa-cloud"></i> Konfigurasi Moodle CBT</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.smartq.index') }}">SMART-Q</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.smartq.show', $smartq) }}">{{ $smartq->nama }}</a></li>
                <li class="breadcrumb-item active">Moodle</li>
            </ol>
        </div>
    </div>
@stop

@section('content')
    @foreach (['success', 'error'] as $msg)
        @if(session($msg))
            <div class="alert alert-{{ $msg === 'error' ? 'danger' : $msg }} alert-dismissible fade show">
                {{ session($msg) }}
                <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
            </div>
        @endif
    @endforeach

    <div class="row">
        <div class="col-md-8">
            {{-- Step 1: Muat & Pilih Kategori --}}
            <div class="card card-outline card-primary">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-folder"></i> Step 1: Pilih Kategori</h3>
                </div>
                <div class="card-body">
                    <p class="text-muted">Moodle URL: <strong>{{ $smartq->moodle_base_url ?? 'Belum diset' }}</strong></p>
                    <button class="btn btn-primary" onclick="loadCategories()" id="btnLoadCategories">
                        <i class="fas fa-sync"></i> Muat Daftar Kategori
                    </button>
                    <div id="categoryList" class="mt-3" style="display:none"></div>
                </div>
            </div>

            {{-- Step 2: Course per Kategori --}}
            <div id="courseSection" style="display:none">
                <div class="card card-outline card-info">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-book"></i> Step 2: Pilih Course</h3>
                        <div class="card-tools">
                            <span class="badge badge-info" id="courseCount">0</span> course dipilih
                        </div>
                    </div>
                    <div class="card-body" id="courseListContainer">
                        <p class="text-muted">Pilih kategori di atas untuk memuat course.</p>
                    </div>
                </div>
            </div>

            {{-- Step 3: Quiz per Course (checkboxes) --}}
            <div id="quizSection" style="display:none">
                <div class="card card-outline card-warning">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-question-circle"></i> Step 3: Pilih Quiz</h3>
                        <div class="card-tools">
                            <span class="badge badge-warning" id="quizCount">0</span> quiz dipilih
                        </div>
                    </div>
                    <div class="card-body" id="quizListContainer">
                        <p class="text-muted">Pilih course di atas untuk memuat quiz.</p>
                    </div>
                </div>
            </div>

            {{-- Save Button --}}
            <div id="saveSection" style="display:none">
                <button class="btn btn-success btn-lg btn-block mb-3" onclick="saveConfig()" id="btnSave">
                    <i class="fas fa-save"></i> Simpan Konfigurasi (<span id="saveQuizCount">0</span> quiz)
                </button>
            </div>

            {{-- Current Config --}}
            @if(!empty($smartq->moodle_quizzes))
                <div class="card card-success">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-check-circle"></i> Konfigurasi Aktif ({{ count($smartq->moodle_quizzes) }} quiz)</h3>
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-sm table-striped mb-0">
                            <thead>
                                <tr>
                                    <th width="30">#</th>
                                    <th>Kategori</th>
                                    <th>Course</th>
                                    <th>Quiz</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($smartq->moodle_quizzes as $i => $q)
                                    <tr>
                                        <td>{{ $i + 1 }}</td>
                                        <td>{{ $q['category_name'] ?? '-' }}</td>
                                        <td>{{ $q['course_name'] ?? '-' }}</td>
                                        <td><strong>{{ $q['quiz_name'] ?? '-' }}</strong></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="card-footer">
                        <form action="{{ route('admin.smartq.moodle.sync', $smartq) }}" method="POST" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-success" onclick="return confirm('Sync nilai CBT dari semua quiz yang dikonfigurasi?')">
                                <i class="fas fa-cloud-download-alt"></i> Sync Nilai CBT
                            </button>
                        </form>
                        @if(count($smartq->moodle_course_ids) > 0)
                            <a href="{{ route('admin.smartq.moodle.scan', $smartq) }}" class="btn btn-info ml-2">
                                <i class="fas fa-search"></i> Scan Peserta dari Moodle
                            </a>
                        @endif
                    </div>
                </div>
            @elseif($smartq->moodle_quiz_id)
                {{-- Legacy single config --}}
                <div class="card card-success">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-check-circle"></i> Konfigurasi Aktif (Legacy)</h3>
                    </div>
                    <div class="card-body">
                        <table class="table table-sm">
                            @if($smartq->moodle_category_name)
                                <tr><td class="text-muted" width="100">Kategori</td><td>{{ $smartq->moodle_category_name }}</td></tr>
                            @endif
                            <tr><td class="text-muted">Course</td><td>{{ $smartq->moodle_course_name ?? 'ID ' . $smartq->moodle_course_id }}</td></tr>
                            <tr><td class="text-muted">Quiz</td><td>{{ $smartq->moodle_quiz_name }}</td></tr>
                        </table>
                        <p class="text-muted small mt-2"><i class="fas fa-info-circle"></i> Pilih ulang quiz di atas untuk migrasi ke format baru (multi-quiz).</p>
                    </div>
                </div>
            @endif
        </div>

        <div class="col-md-4">
            <div class="card card-outline card-secondary">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-info-circle"></i> Cara Kerja</h3>
                </div>
                <div class="card-body">
                    <ol class="pl-3">
                        <li class="mb-2">Klik <strong>Muat Kategori</strong> untuk mengambil data dari Moodle</li>
                        <li class="mb-2">Centang <strong>Kategori</strong> → course otomatis dimuat</li>
                        <li class="mb-2">Centang <strong>Course</strong> → quiz otomatis dimuat</li>
                        <li class="mb-2">Centang <strong>Quiz</strong> yang digunakan untuk CBT</li>
                        <li class="mb-2">Klik <strong>Simpan</strong></li>
                    </ol>
                    <div class="alert alert-info mb-2">
                        <i class="fas fa-sitemap"></i>
                        <strong>Multi-quiz:</strong> Bisa pilih quiz dari beberapa kategori & course sekaligus. Nilai akan di-rata-rata per siswa.
                    </div>
                    <div class="alert alert-warning mb-0">
                        <i class="fas fa-exclamation-triangle"></i>
                        <strong>Penting:</strong> Username Moodle harus = NISN siswa di SIMANSA.
                    </div>
                </div>
            </div>
            <a href="{{ route('admin.smartq.show', $smartq) }}" class="btn btn-secondary btn-block">
                <i class="fas fa-arrow-left"></i> Kembali ke Detail
            </a>
        </div>
    </div>
@stop

@section('js')
@include('admin.smartq._overlay')
<script>
// State
let categoriesData = [];
let coursesLoaded = {}; // categoryId -> courses[]
let quizzesLoaded = {}; // courseId -> quizzes[]

function loadCategories() {
    const btn = document.getElementById('btnLoadCategories');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Memuat...';
    showSmartqOverlay('Mengambil daftar kategori...', 'Menghubungi server Moodle', 'cloud');

    fetch(`{{ route('admin.smartq.moodle.categories', $smartq) }}`)
        .then(r => r.json())
        .then(data => {
            hideSmartqOverlay();
            if (!data.success) { alert(data.error || 'Gagal memuat'); return; }
            categoriesData = data.data;

            let html = '<div class="list-group">';
            data.data.forEach(c => {
                const indent = c.depth > 1 ? 'pl-' + Math.min(c.depth * 2, 5) : '';
                html += `<label class="list-group-item list-group-item-action ${indent} mb-0 py-2" style="cursor:pointer">
                    <input type="checkbox" class="mr-2 cat-check" value="${c.id}"
                           data-name="${c.name}" onchange="onCategoryChange()">
                    <strong>${c.name}</strong>
                    <span class="badge badge-light float-right">${c.coursecount} course</span>
                </label>`;
            });
            html += '</div>';

            document.getElementById('categoryList').innerHTML = html;
            document.getElementById('categoryList').style.display = '';
        })
        .catch(e => { hideSmartqOverlay(); alert('Error: ' + e.message); })
        .finally(() => {
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-sync"></i> Muat Daftar Kategori';
        });
}

function onCategoryChange() {
    const checked = document.querySelectorAll('.cat-check:checked');
    const section = document.getElementById('courseSection');

    if (checked.length === 0) {
        section.style.display = 'none';
        document.getElementById('quizSection').style.display = 'none';
        document.getElementById('saveSection').style.display = 'none';
        return;
    }

    section.style.display = '';
    const container = document.getElementById('courseListContainer');
    container.innerHTML = '<p class="text-muted"><i class="fas fa-spinner fa-spin"></i> Memuat course...</p>';

    // Load courses for all checked categories
    const promises = [];
    checked.forEach(cb => {
        const catId = cb.value;
        if (coursesLoaded[catId]) {
            promises.push(Promise.resolve({ catId, data: coursesLoaded[catId] }));
        } else {
            promises.push(
                fetch(`{{ route('admin.smartq.moodle.courses', $smartq) }}?category_id=${catId}`)
                    .then(r => r.json())
                    .then(data => {
                        if (data.success) coursesLoaded[catId] = data.data;
                        return { catId, data: data.data || [] };
                    })
            );
        }
    });

    Promise.all(promises).then(results => {
        let html = '';
        results.forEach(({ catId, data }) => {
            const catName = document.querySelector(`.cat-check[value="${catId}"]`)?.dataset.name || catId;
            html += `<h6 class="text-muted mt-2 mb-1"><i class="fas fa-folder-open"></i> ${catName}</h6>`;

            if (!data || data.length === 0) {
                html += '<p class="text-muted small ml-3">Tidak ada course.</p>';
                return;
            }

            html += `<div class="ml-3 mb-1">
                <button type="button" class="btn btn-xs btn-outline-primary" onclick="toggleCourses('${catId}', true)">Centang Semua</button>
                <button type="button" class="btn btn-xs btn-outline-secondary" onclick="toggleCourses('${catId}', false)">Hapus Semua</button>
            </div>`;
            html += '<div class="list-group mb-2">';
            data.forEach(c => {
                html += `<label class="list-group-item list-group-item-action mb-0 py-2" style="cursor:pointer">
                    <input type="checkbox" class="mr-2 course-check" value="${c.id}"
                           data-name="${c.fullname}" data-cat-id="${catId}" data-cat-name="${catName}"
                           onchange="onCourseChange()">
                    <strong>${c.fullname}</strong> <small class="text-muted">(${c.shortname})</small>
                    <span class="float-right">
                        <span class="badge badge-primary">${c.quiz_count} quiz</span>
                        <span class="badge badge-secondary">${c.enrolled_count} siswa</span>
                    </span>
                </label>`;
            });
            html += '</div>';
        });

        container.innerHTML = html;
        updateCounts();
    });
}

function onCourseChange() {
    const checked = document.querySelectorAll('.course-check:checked');
    const section = document.getElementById('quizSection');

    document.getElementById('courseCount').textContent = checked.length;

    if (checked.length === 0) {
        section.style.display = 'none';
        document.getElementById('saveSection').style.display = 'none';
        return;
    }

    section.style.display = '';
    const container = document.getElementById('quizListContainer');
    container.innerHTML = '<p class="text-muted"><i class="fas fa-spinner fa-spin"></i> Memuat quiz...</p>';

    const promises = [];
    checked.forEach(cb => {
        const courseId = cb.value;
        if (quizzesLoaded[courseId]) {
            promises.push(Promise.resolve({ courseId, data: quizzesLoaded[courseId], el: cb }));
        } else {
            promises.push(
                fetch(`{{ route('admin.smartq.moodle.quizzes', $smartq) }}?course_id=${courseId}`)
                    .then(r => r.json())
                    .then(data => {
                        if (data.success) quizzesLoaded[courseId] = data.data;
                        return { courseId, data: data.data || [], el: cb };
                    })
            );
        }
    });

    Promise.all(promises).then(results => {
        let html = '';
        results.forEach(({ courseId, data, el }) => {
            const courseName = el.dataset.name;
            const catName = el.dataset.catName;

            html += `<h6 class="text-muted mt-2 mb-1"><i class="fas fa-book"></i> ${courseName} <small>(${catName})</small></h6>`;

            if (!data || data.length === 0) {
                html += '<p class="text-muted small ml-3">Tidak ada quiz.</p>';
                return;
            }

            html += `<div class="ml-3 mb-2">
                <button type="button" class="btn btn-xs btn-outline-primary mb-1" onclick="toggleQuizzes('${courseId}', true)">Centang Semua</button>
                <button type="button" class="btn btn-xs btn-outline-secondary mb-1" onclick="toggleQuizzes('${courseId}', false)">Hapus Semua</button>
            </div>`;
            html += '<div class="list-group mb-2">';
            data.forEach(q => {
                html += `<label class="list-group-item list-group-item-action mb-0 py-2" style="cursor:pointer">
                    <input type="checkbox" class="mr-2 quiz-check" value="${q.id}"
                           data-course-id="${courseId}" data-course-name="${courseName}"
                           data-cat-id="${el.dataset.catId}" data-cat-name="${catName}"
                           data-name="${q.name}" data-maxgrade="${q.maxgrade}"
                           onchange="updateCounts()" checked>
                    <strong>${q.name}</strong>
                    <span class="float-right">
                        <span class="badge badge-info">Max: ${q.maxgrade}</span>
                        <span class="badge badge-secondary">${q.attempt_count} attempt</span>
                    </span>
                </label>`;
            });
            html += '</div>';
        });

        container.innerHTML = html;
        updateCounts();
    });
}

function toggleCourses(catId, checked) {
    document.querySelectorAll(`.course-check[data-cat-id="${catId}"]`).forEach(cb => cb.checked = checked);
    onCourseChange();
}

function toggleQuizzes(courseId, checked) {
    document.querySelectorAll(`.quiz-check[data-course-id="${courseId}"]`).forEach(cb => cb.checked = checked);
    updateCounts();
}

function updateCounts() {
    const quizChecked = document.querySelectorAll('.quiz-check:checked').length;
    document.getElementById('quizCount').textContent = quizChecked;
    document.getElementById('saveQuizCount').textContent = quizChecked;
    document.getElementById('saveSection').style.display = quizChecked > 0 ? '' : 'none';
}

function saveConfig() {
    const quizChecks = document.querySelectorAll('.quiz-check:checked');
    if (quizChecks.length === 0) { alert('Pilih minimal 1 quiz.'); return; }

    const quizzes = [];
    quizChecks.forEach(cb => {
        quizzes.push({
            category_id: parseInt(cb.dataset.catId) || null,
            category_name: cb.dataset.catName || '',
            course_id: parseInt(cb.dataset.courseId),
            course_name: cb.dataset.courseName || '',
            quiz_id: parseInt(cb.value),
            quiz_name: cb.dataset.name || '',
            maxgrade: parseFloat(cb.dataset.maxgrade) || 100,
        });
    });

    const btn = document.getElementById('btnSave');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Menyimpan...';
    showSmartqOverlay('Menyimpan konfigurasi quiz...', quizzes.length + ' quiz akan disimpan', 'save');

    fetch(`{{ route('admin.smartq.moodle.save', $smartq) }}`, {
        method: 'POST',
        headers: {'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}'},
        body: JSON.stringify({ moodle_quizzes: quizzes })
    })
    .then(r => r.json())
    .then(data => {
        hideSmartqOverlay();
        if (data.success) {
            alert(`${quizzes.length} quiz berhasil disimpan!`);
            location.reload();
        } else {
            alert(data.error || 'Gagal menyimpan');
        }
    })
    .catch(e => { hideSmartqOverlay(); alert('Error: ' + e.message); })
    .finally(() => {
        btn.disabled = false;
        btn.innerHTML = `<i class="fas fa-save"></i> Simpan Konfigurasi (<span id="saveQuizCount">${quizzes.length}</span> quiz)`;
    });
}

document.addEventListener('DOMContentLoaded', function() {
    // Sync Nilai CBT form in config footer
    document.querySelectorAll('form[action*="moodle/sync"]').forEach(function(form) {
        form.addEventListener('submit', function() {
            showSmartqOverlay('Menarik nilai CBT dari Moodle...', 'Memproses skor dari semua quiz', 'cloud-download-alt');
            smartqOverlayMessages([
                'Menarik nilai CBT dari Moodle...',
                'Mengambil skor dari setiap quiz...',
                'Menghitung rata-rata per siswa...',
                'Menyimpan ke database...',
            ], 2500);
        });
    });

    // Scan link in config footer
    document.querySelectorAll('a[href*="moodle/scan"]').forEach(function(link) {
        link.addEventListener('click', function() {
            showSmartqOverlay('Scanning peserta dari Moodle...', 'Mengambil data enrolled users', 'search');
        });
    });
});
</script>
@stop
