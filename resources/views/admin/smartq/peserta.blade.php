@extends('adminlte::page')

@section('title', 'Kelola Peserta SMART-Q')

@section('content_header')
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1><i class="fas fa-user-plus"></i> Kelola Peserta: {{ $smartq->nama }}</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.smartq.index') }}">SMART-Q</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.smartq.show', $smartq) }}">{{ $smartq->nama }}</a></li>
                <li class="breadcrumb-item active">Peserta</li>
            </ol>
        </div>
    </div>
@stop

@section('content')
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            <i class="fas fa-check-circle"></i> {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
        </div>
    @endif

    {{-- Scan dari Moodle --}}
    @if($smartq->moodle_base_url && (!empty($smartq->moodle_quizzes) || $smartq->moodle_course_id))
        <div class="card card-success">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-cloud-download-alt"></i> Tambah Peserta dari Moodle</h3>
            </div>
            <div class="card-body">
                <p>
                    Scan user yang terdaftar di course Moodle dan import sebagai peserta SMART-Q.
                    NISN siswa akan dicocokkan dengan username Moodle.
                    @if(!empty($smartq->moodle_quizzes))
                        <strong>{{ count($smartq->moodle_quizzes) }} kuis</strong> dari <strong>{{ count($smartq->moodle_course_ids) }} course</strong> akan di-scan.
                        Nilai CBT akan dihitung rata-rata dari semua kuis.
                    @elseif($smartq->moodle_quiz_id)
                        Nilai CBT juga akan otomatis terisi jika ada.
                    @endif
                </p>
                <a href="{{ route('admin.smartq.moodle.scan', $smartq) }}" class="btn btn-success">
                    <i class="fas fa-search"></i> Scan dari Moodle
                </a>
            </div>
        </div>
    @else
        <div class="alert alert-warning">
            <i class="fas fa-exclamation-triangle"></i>
            Untuk import peserta dari Moodle, konfigurasi URL dan Course terlebih dahulu di
            <a href="{{ route('admin.smartq.moodle.config', $smartq) }}"><strong>Moodle Config</strong></a>.
        </div>
    @endif

    {{-- Tambah Peserta Manual --}}
    <div class="card card-primary">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-user-plus"></i> Tambah Peserta dari Kelas 10 & 11</h3>
        </div>
        <div class="card-body">
            @if($siswaAvailable->count() > 0)
                <form action="{{ route('admin.smartq.peserta.tambah', $smartq) }}" method="POST">
                    @csrf
                    <div class="form-group">
                        <label>Filter Kelas</label>
                        <select class="form-control" id="filterKelas" onchange="filterSiswa()">
                            <option value="">-- Semua Kelas --</option>
                            @foreach($kelasAktif as $kls)
                                <option value="{{ $kls->id }}">{{ $kls->nama_lengkap }} (Tingkat {{ $kls->getTingkatRomawi() }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Pilih Siswa <small class="text-muted">({{ $siswaAvailable->count() }} siswa tersedia)</small></label>
                        <div class="mb-2">
                            <button type="button" class="btn btn-xs btn-outline-primary" onclick="selectAll()">Pilih Semua</button>
                            <button type="button" class="btn btn-xs btn-outline-secondary" onclick="deselectAll()">Hapus Semua</button>
                            <span class="ml-2 text-muted" id="selectedCount">0 dipilih</span>
                        </div>
                        <div style="max-height: 400px; overflow-y: auto; border: 1px solid #dee2e6; border-radius: 4px; padding: 8px;">
                            @foreach($siswaAvailable as $siswa)
                                @php $kelasSekarang = $siswa->getKelasSekarang(); @endphp
                                <div class="custom-control custom-checkbox siswa-item" data-kelas="{{ $kelasSekarang?->id }}">
                                    <input class="custom-control-input siswa-check" type="checkbox"
                                           name="siswa_ids[]" value="{{ $siswa->id }}" id="siswa_{{ $siswa->id }}">
                                    <label class="custom-control-label" for="siswa_{{ $siswa->id }}">
                                        <strong>{{ $siswa->nama_lengkap }}</strong>
                                        <small class="text-muted ml-2">NISN: {{ $siswa->nisn ?? '-' }}</small>
                                        <small class="text-muted ml-2">{{ $kelasSekarang?->nama_lengkap ?? '-' }}</small>
                                        <small class="text-muted ml-2">{{ $siswa->jenis_kelamin === 'L' ? '♂' : '♀' }}</small>
                                    </label>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-plus-circle"></i> Tambah Peserta Terpilih
                    </button>
                    <a href="{{ route('admin.smartq.show', $smartq) }}" class="btn btn-secondary ml-2">
                        <i class="fas fa-arrow-left"></i> Kembali
                    </a>
                </form>
            @else
                <div class="text-center py-4 text-muted">
                    <i class="fas fa-check-circle fa-2x text-success mb-2"></i>
                    <p>Semua siswa kelas 10 & 11 yang aktif sudah terdaftar sebagai peserta.</p>
                    <a href="{{ route('admin.smartq.show', $smartq) }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Kembali ke Detail
                    </a>
                </div>
            @endif
        </div>
    </div>

    {{-- Daftar Peserta Terdaftar --}}
    <div class="card">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-users"></i> Peserta Terdaftar ({{ $smartq->pesertas->count() }})</h3>
        </div>
        <div class="card-body p-0">
            <table class="table table-striped table-sm mb-0">
                <thead>
                    <tr>
                        <th width="40">#</th>
                        <th width="100">No. Peserta</th>
                        <th>Nama Siswa</th>
                        <th width="100">NISN</th>
                        <th width="130">Kelas Asal</th>
                        <th width="100">Status</th>
                        <th width="80">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @php $pesertaList = $smartq->pesertas()->with(['siswa', 'kelasAsal'])->orderBy('nomor_peserta')->get(); @endphp
                    @forelse($pesertaList as $i => $p)
                        <tr>
                            <td>{{ $i + 1 }}</td>
                            <td><code>{{ $p->nomor_peserta }}</code></td>
                            <td><strong>{{ $p->siswa->nama_lengkap ?? '-' }}</strong></td>
                            <td>{{ $p->siswa->nisn ?? '-' }}</td>
                            <td>{{ $p->kelasAsal->nama_lengkap ?? '-' }}</td>
                            <td>{!! $p->status_badge !!}</td>
                            <td>
                                <form action="{{ route('admin.smartq.peserta.hapus', [$smartq, $p]) }}" method="POST"
                                      onsubmit="return confirm('Hapus peserta ini beserta semua nilainya?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-xs btn-danger"><i class="fas fa-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center py-3 text-muted">Belum ada peserta terdaftar.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@stop

@section('js')
<script>
function filterSiswa() {
    const kelasId = document.getElementById('filterKelas').value;
    document.querySelectorAll('.siswa-item').forEach(el => {
        el.style.display = (!kelasId || el.dataset.kelas === kelasId) ? '' : 'none';
    });
}

function selectAll() {
    document.querySelectorAll('.siswa-item:not([style*="display: none"]) .siswa-check').forEach(cb => cb.checked = true);
    updateCount();
}

function deselectAll() {
    document.querySelectorAll('.siswa-check').forEach(cb => cb.checked = false);
    updateCount();
}

function updateCount() {
    const count = document.querySelectorAll('.siswa-check:checked').length;
    document.getElementById('selectedCount').textContent = count + ' dipilih';
}

document.querySelectorAll('.siswa-check').forEach(cb => cb.addEventListener('change', updateCount));
</script>
@stop
