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
    <div class="row">
        <div class="col-md-8">
            {{-- Step 1: Pilih Kategori --}}
            <div class="card card-outline card-primary">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-folder"></i> Step 1: Pilih Kategori</h3>
                </div>
                <div class="card-body">
                    <p class="text-muted">Moodle URL: <strong>{{ $smartq->moodle_base_url ?? 'Belum diset' }}</strong></p>
                    <button class="btn btn-primary" onclick="loadCategories()" id="btnLoadCategories">
                        <i class="fas fa-sync"></i> Muat Daftar Kategori
                    </button>
                    <div id="categoryList" class="mt-3" style="display:none">
                        <select class="form-control" id="categorySelect" onchange="loadCourses()">
                            <option value="">-- Pilih Kategori --</option>
                        </select>
                    </div>
                </div>
            </div>

            {{-- Step 2: Pilih Course --}}
            <div class="card card-outline card-info" id="courseCard" style="display:none">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-book"></i> Step 2: Pilih Course</h3>
                </div>
                <div class="card-body">
                    <div id="courseListContainer">
                        <select class="form-control" id="courseSelect" onchange="loadQuizzes()">
                            <option value="">-- Pilih Course --</option>
                        </select>
                    </div>
                </div>
            </div>

            {{-- Step 3: Pilih Quiz --}}
            <div class="card card-outline card-warning" id="quizCard" style="display:none">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-question-circle"></i> Step 3: Pilih Quiz</h3>
                </div>
                <div class="card-body">
                    <div id="quizList"></div>
                </div>
            </div>

            {{-- Current Config --}}
            @if($smartq->moodle_quiz_id)
                <div class="card card-success">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-check-circle"></i> Konfigurasi Aktif</h3>
                    </div>
                    <div class="card-body">
                        <table class="table table-sm">
                            @if($smartq->moodle_category_id)
                                <tr><td class="text-muted" width="150">Kategori</td><td><strong>{{ $smartq->moodle_category_name ?? 'ID ' . $smartq->moodle_category_id }}</strong> <small class="text-muted">(ID: {{ $smartq->moodle_category_id }})</small></td></tr>
                            @endif
                            <tr><td class="text-muted" width="150">Course</td><td><strong>{{ $smartq->moodle_course_name ?? 'ID ' . $smartq->moodle_course_id }}</strong> <small class="text-muted">(ID: {{ $smartq->moodle_course_id }})</small></td></tr>
                            <tr><td class="text-muted">Quiz</td><td><strong>{{ $smartq->moodle_quiz_name }}</strong> <small class="text-muted">(ID: {{ $smartq->moodle_quiz_id }})</small></td></tr>
                        </table>
                        <div class="mt-3">
                            <form action="{{ route('admin.smartq.moodle.sync', $smartq) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-success" onclick="return confirm('Sync nilai CBT dari Moodle?')">
                                    <i class="fas fa-cloud-download-alt"></i> Sync Nilai CBT
                                </button>
                            </form>
                            <a href="{{ route('admin.smartq.moodle.scan', $smartq) }}" class="btn btn-info ml-2">
                                <i class="fas fa-search"></i> Scan Peserta dari Moodle
                            </a>
                        </div>
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
                        <li class="mb-2">Pilih <strong>Kategori</strong> yang berisi course SMART-Q</li>
                        <li class="mb-2">Pilih <strong>Course</strong> tempat test dilaksanakan</li>
                        <li class="mb-2">Pilih <strong>Quiz</strong> yang digunakan untuk CBT</li>
                        <li class="mb-2">Klik <strong>Sync</strong> untuk menarik nilai, atau <strong>Scan</strong> untuk import peserta</li>
                    </ol>
                    <div class="alert alert-info mb-2">
                        <i class="fas fa-sitemap"></i>
                        <strong>Hierarki:</strong> Kategori → Course → Quiz<br>
                        Setiap periode SMART-Q bisa dikonfigurasi ke quiz yang berbeda.
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
<script>
let selectedCategoryName = '';
let selectedCourseName = '';

function loadCategories() {
    const btn = document.getElementById('btnLoadCategories');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Memuat...';

    fetch(`{{ route('admin.smartq.moodle.categories', $smartq) }}`)
        .then(r => r.json())
        .then(data => {
            if (!data.success) { alert(data.error); return; }
            const sel = document.getElementById('categorySelect');
            sel.innerHTML = '<option value="">-- Pilih Kategori --</option>';
            data.data.forEach(c => {
                const indent = '—'.repeat(Math.max(0, c.depth - 1));
                const label = indent + (indent ? ' ' : '') + c.name + ` (${c.coursecount} course)`;
                sel.innerHTML += `<option value="${c.id}" data-name="${c.name}">${label}</option>`;
            });
            document.getElementById('categoryList').style.display = '';

            @if($smartq->moodle_category_id)
                sel.value = '{{ $smartq->moodle_category_id }}';
                if (sel.value) loadCourses();
            @endif
        })
        .catch(e => alert('Error: ' + e.message))
        .finally(() => {
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-sync"></i> Muat Daftar Kategori';
        });
}

function loadCourses() {
    const catSel = document.getElementById('categorySelect');
    const categoryId = catSel.value;
    if (!categoryId) {
        document.getElementById('courseCard').style.display = 'none';
        document.getElementById('quizCard').style.display = 'none';
        return;
    }

    selectedCategoryName = catSel.options[catSel.selectedIndex].dataset.name || '';

    const card = document.getElementById('courseCard');
    const container = document.getElementById('courseListContainer');
    card.style.display = '';
    container.innerHTML = '<p class="text-muted"><i class="fas fa-spinner fa-spin"></i> Memuat course...</p>';

    fetch(`{{ route('admin.smartq.moodle.courses', $smartq) }}?category_id=${categoryId}`)
        .then(r => r.json())
        .then(data => {
            if (!data.success) { container.innerHTML = `<div class="alert alert-danger">${data.error}</div>`; return; }
            if (!data.data.length) { container.innerHTML = '<p class="text-muted">Tidak ada course di kategori ini.</p>'; return; }

            let html = '<select class="form-control" id="courseSelect" onchange="loadQuizzes()">';
            html += '<option value="">-- Pilih Course --</option>';
            data.data.forEach(c => {
                html += `<option value="${c.id}" data-name="${c.fullname}">${c.fullname} (${c.shortname}) — ${c.quiz_count} quiz, ${c.enrolled_count} enrolled</option>`;
            });
            html += '</select>';
            container.innerHTML = html;

            @if($smartq->moodle_course_id)
                document.getElementById('courseSelect').value = '{{ $smartq->moodle_course_id }}';
                if (document.getElementById('courseSelect').value) loadQuizzes();
            @endif
        })
        .catch(e => container.innerHTML = `<div class="alert alert-danger">Error: ${e.message}</div>`);
}

function loadQuizzes() {
    const courseSel = document.getElementById('courseSelect');
    const courseId = courseSel.value;
    if (!courseId) {
        document.getElementById('quizCard').style.display = 'none';
        return;
    }

    selectedCourseName = courseSel.options[courseSel.selectedIndex].dataset.name || '';

    const card = document.getElementById('quizCard');
    const list = document.getElementById('quizList');
    card.style.display = '';
    list.innerHTML = '<p class="text-muted"><i class="fas fa-spinner fa-spin"></i> Memuat quiz...</p>';

    fetch(`{{ route('admin.smartq.moodle.quizzes', $smartq) }}?course_id=${courseId}`)
        .then(r => r.json())
        .then(data => {
            if (!data.success) { list.innerHTML = `<div class="alert alert-danger">${data.error}</div>`; return; }
            if (!data.data.length) { list.innerHTML = '<p class="text-muted">Tidak ada quiz di course ini.</p>'; return; }

            let html = '<div class="list-group">';
            data.data.forEach(q => {
                const active = q.id == '{{ $smartq->moodle_quiz_id }}' ? 'list-group-item-success' : '';
                html += `<div class="list-group-item ${active} d-flex justify-content-between align-items-center">
                    <div>
                        <strong>${q.name}</strong><br>
                        <small class="text-muted">Max Grade: ${q.maxgrade} | ${q.attempt_count} attempts</small>
                    </div>
                    <button class="btn btn-sm btn-primary" onclick="selectQuiz(${q.id}, '${q.name.replace(/'/g, "\\'")}')">
                        <i class="fas fa-check"></i> Pilih
                    </button>
                </div>`;
            });
            html += '</div>';
            list.innerHTML = html;
        })
        .catch(e => list.innerHTML = `<div class="alert alert-danger">Error: ${e.message}</div>`);
}

function selectQuiz(quizId, quizName) {
    const categoryId = document.getElementById('categorySelect').value;
    const courseId = document.getElementById('courseSelect').value;

    fetch(`{{ route('admin.smartq.moodle.save', $smartq) }}`, {
        method: 'POST',
        headers: {'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}'},
        body: JSON.stringify({
            moodle_category_id: parseInt(categoryId) || null,
            moodle_category_name: selectedCategoryName,
            moodle_course_id: parseInt(courseId),
            moodle_course_name: selectedCourseName,
            moodle_quiz_id: quizId,
            moodle_quiz_name: quizName,
        })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            alert('Konfigurasi Kategori → Course → Quiz berhasil disimpan!');
            location.reload();
        } else {
            alert(data.error || 'Gagal menyimpan');
        }
    })
    .catch(e => alert('Error: ' + e.message));
}
</script>
@stop
