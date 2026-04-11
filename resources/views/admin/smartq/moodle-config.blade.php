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
            {{-- Step 1: Pilih Course --}}
            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-book"></i> Step 1: Pilih Course</h3>
                </div>
                <div class="card-body">
                    <p class="text-muted">Moodle URL: <strong>{{ $smartq->moodle_base_url }}</strong></p>
                    <button class="btn btn-primary" onclick="loadCourses()" id="btnLoadCourses">
                        <i class="fas fa-sync"></i> Muat Daftar Course
                    </button>
                    <div id="courseList" class="mt-3" style="display:none">
                        <select class="form-control" id="courseSelect" onchange="loadQuizzes()">
                            <option value="">-- Pilih Course --</option>
                        </select>
                    </div>
                </div>
            </div>

            {{-- Step 2: Pilih Quiz --}}
            <div class="card card-info" id="quizCard" style="display:none">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-question-circle"></i> Step 2: Pilih Quiz</h3>
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
                            <tr><td class="text-muted" width="150">Course ID</td><td><strong>{{ $smartq->moodle_course_id }}</strong></td></tr>
                            <tr><td class="text-muted">Quiz ID</td><td><strong>{{ $smartq->moodle_quiz_id }}</strong></td></tr>
                            <tr><td class="text-muted">Quiz Name</td><td><strong>{{ $smartq->moodle_quiz_name }}</strong></td></tr>
                        </table>
                        <form action="{{ route('admin.smartq.moodle.sync', $smartq) }}" method="POST" class="mt-2">
                            @csrf
                            <button type="submit" class="btn btn-success" onclick="return confirm('Sync nilai CBT dari Moodle?')">
                                <i class="fas fa-cloud-download-alt"></i> Sync Nilai Sekarang
                            </button>
                        </form>
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
                        <li class="mb-2">Pilih <strong>Course</strong> yang berisi quiz seleksi SMART-Q</li>
                        <li class="mb-2">Pilih <strong>Quiz</strong> yang digunakan untuk CBT</li>
                        <li class="mb-2">Klik <strong>Sync</strong> untuk menarik nilai siswa</li>
                        <li class="mb-2">Sistem akan mencocokkan <strong>NISN</strong> siswa dengan <strong>username Moodle</strong></li>
                    </ol>
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i>
                        <strong>Penting:</strong> Username di Moodle harus sama dengan NISN siswa di SIMANSA agar matching otomatis berjalan.
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
const periodeId = '{{ $smartq->id }}';

function loadCourses() {
    const btn = document.getElementById('btnLoadCourses');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Memuat...';

    fetch(`{{ route('admin.smartq.moodle.courses', $smartq) }}`)
        .then(r => r.json())
        .then(data => {
            if (!data.success) { alert(data.error); return; }
            const sel = document.getElementById('courseSelect');
            sel.innerHTML = '<option value="">-- Pilih Course --</option>';
            data.data.forEach(c => {
                sel.innerHTML += `<option value="${c.id}">${c.fullname} (${c.shortname})</option>`;
            });
            document.getElementById('courseList').style.display = '';

            @if($smartq->moodle_course_id)
                sel.value = '{{ $smartq->moodle_course_id }}';
            @endif
        })
        .catch(e => alert('Error: ' + e.message))
        .finally(() => {
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-sync"></i> Muat Daftar Course';
        });
}

function loadQuizzes() {
    const courseId = document.getElementById('courseSelect').value;
    if (!courseId) return;

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
                    <button class="btn btn-sm btn-primary" onclick="selectQuiz(${q.id}, ${courseId}, '${q.name.replace(/'/g, "\\'")}')">
                        <i class="fas fa-check"></i> Pilih
                    </button>
                </div>`;
            });
            html += '</div>';
            list.innerHTML = html;
        })
        .catch(e => list.innerHTML = `<div class="alert alert-danger">Error: ${e.message}</div>`);
}

function selectQuiz(quizId, courseId, quizName) {
    fetch(`{{ route('admin.smartq.moodle.save', $smartq) }}`, {
        method: 'POST',
        headers: {'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}'},
        body: JSON.stringify({moodle_quiz_id: quizId, moodle_course_id: courseId, moodle_quiz_name: quizName})
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            alert('Quiz berhasil dipilih! Halaman akan dimuat ulang.');
            location.reload();
        } else {
            alert(data.error || 'Gagal menyimpan');
        }
    })
    .catch(e => alert('Error: ' + e.message));
}
</script>
@stop
