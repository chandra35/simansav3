@extends('adminlte::page')

@section('title', $smartq->nama)

@section('content_header')
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1><i class="fas fa-star text-warning"></i> {{ $smartq->nama }}</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.smartq.index') }}">SMART-Q</a></li>
                <li class="breadcrumb-item active">Detail</li>
            </ol>
        </div>
    </div>
@stop

@section('content')
    @foreach (['success', 'error', 'warning'] as $msg)
        @if(session($msg))
            <div class="alert alert-{{ $msg === 'error' ? 'danger' : $msg }} alert-dismissible fade show">
                <i class="fas fa-{{ $msg === 'success' ? 'check-circle' : ($msg === 'error' ? 'exclamation-circle' : 'exclamation-triangle') }}"></i>
                {{ session($msg) }}
                <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
            </div>
        @endif
    @endforeach

    {{-- Info & Actions Bar --}}
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-info-circle"></i> Informasi Periode</h3>
                    <div class="card-tools">{!! $smartq->status_badge !!}</div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-sm table-borderless">
                                <tr><td class="text-muted" width="150">Tahun Pelajaran</td><td><strong>{{ $smartq->tahunPelajaran->nama ?? '-' }}</strong></td></tr>
                                <tr><td class="text-muted">Periode</td><td>{{ $smartq->tanggal_mulai->format('d M Y') }} - {{ $smartq->tanggal_selesai->format('d M Y') }}</td></tr>
                                <tr><td class="text-muted">Kuota</td><td><strong>{{ $smartq->kuota }}</strong> siswa</td></tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-sm table-borderless">
                                <tr><td class="text-muted" width="150">Moodle URL</td><td>{{ $smartq->moodle_base_url ?? '-' }}</td></tr>
                                @if(!empty($smartq->moodle_quizzes))
                                    <tr>
                                        <td class="text-muted">Kuis Terkonfigurasi</td>
                                        <td>
                                            <span class="badge badge-success">{{ count($smartq->moodle_quizzes) }} kuis</span>
                                            dari <span class="badge badge-info">{{ count($smartq->moodle_course_ids) }} course</span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted" colspan="2">
                                            <small>
                                            @foreach($smartq->moodle_quizzes as $qz)
                                                <span class="badge badge-light">{{ $qz['course_name'] ?? '' }} &raquo; {{ $qz['quiz_name'] ?? '' }}</span>
                                            @endforeach
                                            </small>
                                        </td>
                                    </tr>
                                @else
                                    <tr><td class="text-muted">Kategori</td><td>{{ $smartq->moodle_category_name ?? '-' }}</td></tr>
                                    <tr><td class="text-muted">Course</td><td>{{ $smartq->moodle_course_name ?? '-' }}</td></tr>
                                    <tr><td class="text-muted">Quiz Moodle</td><td>{{ $smartq->moodle_quiz_name ?? 'Belum dikonfigurasi' }}</td></tr>
                                @endif
                                <tr><td class="text-muted">Deskripsi</td><td>{{ $smartq->deskripsi ?? '-' }}</td></tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-header bg-gradient-dark text-white">
                    <h3 class="card-title"><i class="fas fa-cogs"></i> Aksi</h3>
                </div>
                <div class="card-body p-2">
                    <div class="list-group list-group-flush">
                        <a href="{{ route('admin.smartq.peserta', $smartq) }}" class="list-group-item list-group-item-action">
                            <i class="fas fa-user-plus text-info"></i> Kelola Peserta
                        </a>
                        <a href="{{ route('admin.smartq.nilai', $smartq) }}" class="list-group-item list-group-item-action">
                            <i class="fas fa-pencil-alt text-success"></i> Input Nilai Manual
                        </a>
                        @if($smartq->moodle_base_url)
                            <a href="{{ route('admin.smartq.moodle.config', $smartq) }}" class="list-group-item list-group-item-action">
                                <i class="fas fa-cloud text-primary"></i> Konfigurasi Moodle
                            </a>
                            @if(!empty($smartq->moodle_quizzes) || $smartq->moodle_quiz_id)
                                <form action="{{ route('admin.smartq.moodle.sync', $smartq) }}" method="POST" class="d-inline" id="formSyncMoodle">
                                    @csrf
                                    <button type="submit" class="list-group-item list-group-item-action text-left border-0">
                                        <i class="fas fa-sync text-warning"></i> Sync Nilai CBT Moodle
                                    </button>
                                </form>
                            @endif
                            @if(!empty($smartq->moodle_quizzes) || $smartq->moodle_course_id)
                                <a href="{{ route('admin.smartq.moodle.scan', $smartq) }}" class="list-group-item list-group-item-action">
                                    <i class="fas fa-cloud-download-alt text-success"></i> Scan Peserta dari Moodle
                                </a>
                            @endif
                            @if($hasScanData ?? false)
                                <a href="{{ route('admin.smartq.nilai-cbt', $smartq) }}" class="list-group-item list-group-item-action list-group-item-info">
                                    <i class="fas fa-chart-line text-primary"></i> <strong>Nilai CBT / Smart Score</strong>
                                    <span class="badge badge-primary float-right">{{ $smartq->last_scan_at?->diffForHumans() }}</span>
                                </a>
                                <a href="{{ route('admin.smartq.moodle.scan.export', ['smartq' => $smartq, 'format' => 'excel']) }}" class="list-group-item list-group-item-action">
                                    <i class="fas fa-file-excel text-success"></i> Export Nilai CBT (Excel)
                                </a>
                                <a href="{{ route('admin.smartq.moodle.scan.export', ['smartq' => $smartq, 'format' => 'excel_hadir']) }}" class="list-group-item list-group-item-action">
                                    <i class="fas fa-file-excel text-success"></i> Export Nilai CBT Hadir Saja (Excel)
                                </a>
                                <a href="{{ route('admin.smartq.moodle.scan.export', ['smartq' => $smartq, 'format' => 'pdf']) }}" class="list-group-item list-group-item-action">
                                    <i class="fas fa-file-pdf text-danger"></i> Export Nilai CBT (PDF)
                                </a>
                            @endif
                        @endif
                        <a href="{{ route('admin.smartq.export', $smartq) }}" class="list-group-item list-group-item-action">
                            <i class="fas fa-file-excel text-success"></i> Export Excel
                        </a>
                        <a href="{{ route('admin.smartq.edit', $smartq) }}" class="list-group-item list-group-item-action">
                            <i class="fas fa-edit text-warning"></i> Edit Periode
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Statistik --}}
    <div class="row">
        <div class="col-lg-2 col-md-4 col-6">
            <div class="small-box bg-info">
                <div class="inner"><h3>{{ $stats['total'] }}</h3><p>Total Peserta</p></div>
                <div class="icon"><i class="fas fa-users"></i></div>
            </div>
        </div>
        <div class="col-lg-2 col-md-4 col-6">
            <div class="small-box bg-success">
                <div class="inner"><h3>{{ $stats['lulus'] }}</h3><p>Lulus</p></div>
                <div class="icon"><i class="fas fa-check-circle"></i></div>
            </div>
        </div>
        <div class="col-lg-2 col-md-4 col-6">
            <div class="small-box bg-danger">
                <div class="inner"><h3>{{ $stats['tidak_lulus'] }}</h3><p>Tidak Lulus</p></div>
                <div class="icon"><i class="fas fa-times-circle"></i></div>
            </div>
        </div>
        <div class="col-lg-2 col-md-4 col-6">
            <div class="small-box bg-primary">
                <div class="inner"><h3>{{ number_format($stats['rata_rata'], 1) }}</h3><p>Rata-rata</p></div>
                <div class="icon"><i class="fas fa-chart-bar"></i></div>
            </div>
        </div>
        <div class="col-lg-2 col-md-4 col-6">
            <div class="small-box bg-warning">
                <div class="inner"><h3>{{ number_format($stats['tertinggi'], 1) }}</h3><p>Tertinggi</p></div>
                <div class="icon"><i class="fas fa-arrow-up"></i></div>
            </div>
        </div>
        <div class="col-lg-2 col-md-4 col-6">
            <div class="small-box bg-secondary">
                <div class="inner"><h3>{{ number_format($stats['terendah'], 1) }}</h3><p>Terendah</p></div>
                <div class="icon"><i class="fas fa-arrow-down"></i></div>
            </div>
        </div>
    </div>

    {{-- Komponen Nilai --}}
    <div class="card card-outline card-info">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-balance-scale"></i> Komponen Penilaian</h3>
        </div>
        <div class="card-body p-0">
            <table class="table table-striped table-sm mb-0">
                <thead>
                    <tr>
                        <th width="40">#</th>
                        <th>Komponen</th>
                        <th width="100">Kode</th>
                        <th width="100">Bobot</th>
                        <th width="120">Nilai Maks</th>
                        <th width="120">Sumber</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($smartq->komponenNilais as $k)
                        <tr>
                            <td>{{ $k->urutan }}</td>
                            <td><strong>{{ $k->nama }}</strong></td>
                            <td><code>{{ $k->kode }}</code></td>
                            <td><span class="badge badge-primary">{{ $k->bobot }}%</span></td>
                            <td>{{ $k->nilai_maksimal }}</td>
                            <td>{!! $k->sumber_badge !!}</td>
                        </tr>
                    @endforeach
                    <tr class="bg-light font-weight-bold">
                        <td colspan="3">Total Bobot</td>
                        <td>
                            <span class="badge badge-{{ $smartq->total_bobot == 100 ? 'success' : 'danger' }}">
                                {{ $smartq->total_bobot }}%
                            </span>
                        </td>
                        <td colspan="2">
                            @if($smartq->total_bobot != 100)
                                <span class="text-danger"><i class="fas fa-exclamation-triangle"></i> Total bobot harus 100%!</span>
                            @else
                                <span class="text-success"><i class="fas fa-check"></i> OK</span>
                            @endif
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    {{-- Proses Kelulusan --}}
    @if($stats['total'] > 0 && $smartq->status !== 'selesai')
        <div class="card card-outline card-warning">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-gavel"></i> Proses Kelulusan</h3>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.smartq.kelulusan', $smartq) }}" method="POST" id="formKelulusan">
                    @csrf
                    <div class="row align-items-end">
                        <div class="col-md-3">
                            <div class="form-group mb-0">
                                <label>Metode</label>
                                <select name="metode" class="form-control" id="metodeKelulusan" onchange="togglePassingGrade()">
                                    <option value="kuota">Berdasarkan Kuota (Top {{ $smartq->kuota }})</option>
                                    <option value="passing_grade">Berdasarkan Passing Grade</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3" id="passingGradeGroup" style="display:none">
                            <div class="form-group mb-0">
                                <label>Passing Grade</label>
                                <input type="number" name="passing_grade" class="form-control" step="0.01" min="0" max="100" value="70">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <button type="submit" class="btn btn-warning">
                                <i class="fas fa-gavel"></i> Proses Kelulusan
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    @endif

    {{-- Tabel Ranking --}}
    <div class="card">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-trophy"></i> Ranking Peserta</h3>
            <div class="card-tools">
                <span class="badge badge-primary">{{ $pesertas->count() }} peserta</span>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered table-striped table-sm mb-0">
                    <thead class="bg-gradient-dark text-white">
                        <tr>
                            <th width="60" class="text-center">Rank</th>
                            <th width="100">No. Peserta</th>
                            <th>Nama Siswa</th>
                            <th width="100">NISN</th>
                            <th width="130">Kelas Asal</th>
                            @foreach($smartq->komponenNilais as $k)
                                <th width="90" class="text-center" title="{{ $k->nama }} ({{ $k->bobot }}%)">
                                    {{ $k->kode }}<br><small>{{ $k->bobot }}%</small>
                                </th>
                            @endforeach
                            <th width="90" class="text-center bg-gradient-primary">Total</th>
                            <th width="110" class="text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($pesertas as $p)
                            <tr class="{{ $p->status === 'lulus' ? 'table-success' : ($p->status === 'tidak_lulus' ? 'table-danger' : '') }}">
                                <td class="text-center">
                                    @if($p->ranking && $p->ranking <= 3)
                                        <span class="badge badge-{{ $p->ranking === 1 ? 'warning' : ($p->ranking === 2 ? 'secondary' : 'info') }}">
                                            <i class="fas fa-trophy"></i> {{ $p->ranking }}
                                        </span>
                                    @else
                                        {{ $p->ranking ?? '-' }}
                                    @endif
                                </td>
                                <td><code>{{ $p->nomor_peserta }}</code></td>
                                <td>
                                    <strong>{{ $p->siswa->nama_lengkap ?? '-' }}</strong>
                                    <br><small class="text-muted">{{ $p->siswa->jenis_kelamin === 'L' ? 'Laki-laki' : 'Perempuan' }}</small>
                                </td>
                                <td><small>{{ $p->siswa->nisn ?? '-' }}</small></td>
                                <td>{{ $p->kelasAsal->nama_lengkap ?? '-' }}</td>
                                @foreach($smartq->komponenNilais as $k)
                                    @php $nilai = $p->getNilaiKomponen($k->id); @endphp
                                    <td class="text-center">
                                        @if($nilai && $nilai->nilai !== null)
                                            <strong>{{ number_format($nilai->nilai, 1) }}</strong>
                                            @if($k->isMoodle() && $nilai->moodle_attempt_id)
                                                <br><small class="text-muted"><i class="fas fa-cloud"></i></small>
                                            @endif
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                @endforeach
                                <td class="text-center">
                                    <strong class="text-primary">{{ $p->total_nilai !== null ? number_format($p->total_nilai, 2) : '-' }}</strong>
                                </td>
                                <td class="text-center">{!! $p->status_badge !!}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ 6 + $smartq->komponenNilais->count() }}" class="text-center py-4 text-muted">
                                    <i class="fas fa-users fa-2x mb-2"></i><br>
                                    Belum ada peserta. <a href="{{ route('admin.smartq.peserta', $smartq) }}">Tambah peserta</a>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@stop

@section('js')
@include('admin.smartq._overlay')
<script>
function togglePassingGrade() {
    const metode = document.getElementById('metodeKelulusan').value;
    document.getElementById('passingGradeGroup').style.display = metode === 'passing_grade' ? '' : 'none';
}

document.addEventListener('DOMContentLoaded', function() {
    // Sync Nilai CBT form — SweetAlert confirm + overlay
    var formSync = document.getElementById('formSyncMoodle');
    if (formSync) {
        formSync.addEventListener('submit', function(e) {
            e.preventDefault();
            smartqConfirm(null, {
                title: 'Sync Nilai CBT?',
                text: '<p>Nilai CBT akan ditarik dari Moodle.</p><p class="text-danger mb-0"><small><i class="fas fa-exclamation-triangle"></i> Nilai yang sudah ada akan di-<b>overwrite</b>!</small></p>',
                icon: 'warning',
                confirmText: '<i class="fas fa-sync"></i> Ya, Sync Sekarang',
                confirmColor: '#e6a817',
            }).then(function(result) {
                if (result.isConfirmed) {
                    showSmartqOverlay('Menarik nilai CBT dari Moodle...', 'Memproses data dari semua quiz yang dikonfigurasi', 'cloud-download-alt');
                    smartqOverlayMessages([
                        'Menarik nilai CBT dari Moodle...',
                        'Mengambil skor dari setiap quiz...',
                        'Menghitung rata-rata per siswa...',
                        'Menyimpan nilai ke database...',
                        'Hampir selesai...',
                    ], 2500);
                    formSync.submit();
                }
            });
        });
    }

    // Scan Peserta link
    document.querySelectorAll('a[href*="moodle/scan"]').forEach(function(link) {
        link.addEventListener('click', function() {
            showSmartqOverlay('Scanning peserta dari Moodle...', 'Mengambil data enrolled users dari semua course', 'search');
            smartqOverlayMessages([
                'Scanning peserta dari Moodle...',
                'Mencocokkan NISN dengan database siswa...',
                'Mengumpulkan data skor quiz...',
                'Menyusun hasil scan...',
            ], 2500);
        });
    });

    // Proses Kelulusan form — SweetAlert confirm + overlay
    var formKelulusan = document.getElementById('formKelulusan');
    if (formKelulusan) {
        formKelulusan.addEventListener('submit', function(e) {
            e.preventDefault();
            var metode = document.getElementById('metodeKelulusan').value;
            var metodeLabel = metode === 'kuota' ? 'Berdasarkan Kuota' : 'Berdasarkan Passing Grade';
            smartqConfirm(null, {
                title: 'Proses Kelulusan?',
                text: '<p>Status <b>semua peserta</b> akan diubah berdasarkan metode:</p><p class="text-center"><span class="badge badge-warning px-3 py-2" style="font-size:1rem">' + metodeLabel + '</span></p><p class="text-danger mb-0"><small><i class="fas fa-exclamation-triangle"></i> Tindakan ini akan mengubah status kelulusan!</small></p>',
                icon: 'warning',
                confirmText: '<i class="fas fa-gavel"></i> Ya, Proses Kelulusan',
                confirmColor: '#e6a817',
            }).then(function(result) {
                if (result.isConfirmed) {
                    showSmartqOverlay('Memproses kelulusan...', 'Menghitung ranking dan menentukan status peserta', 'gavel');
                    smartqOverlayMessages([
                        'Memproses kelulusan...',
                        'Menghitung total nilai tertimbang...',
                        'Menentukan ranking peserta...',
                        'Mengupdate status kelulusan...',
                        'Hampir selesai...',
                    ], 1500);
                    formKelulusan.submit();
                }
            });
        });
    }

    // Export Excel link
    document.querySelectorAll('a[href*="export"]').forEach(function(link) {
        link.addEventListener('click', function() {
            showSmartqOverlay('Membuat file Excel...', 'Menyiapkan data ranking dan nilai peserta', 'file-excel');
            setTimeout(hideSmartqOverlay, 8000);
        });
    });
});
</script>
@stop
