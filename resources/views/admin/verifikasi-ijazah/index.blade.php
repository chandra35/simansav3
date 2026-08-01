@extends('adminlte::page')

@section('title', 'Verifikasi Ijazah SMP/MTs - SIMANSA')

@section('css')
<style>
    .verif-hero {
        background: linear-gradient(135deg, rgba(79, 70, 229, .94), rgba(59, 130, 246, .9));
        border-radius: 18px;
        padding: 1.1rem 1.25rem;
        margin-bottom: 1rem;
        color: #fff;
        box-shadow: 0 16px 32px rgba(79, 70, 229, .16);
    }
    .verif-hero h4 { font-weight: 800; margin: 0 0 .22rem 0; font-size: 1.28rem; }
    .verif-hero p  { margin: 0; font-size: .88rem; opacity: .9; max-width: 860px; line-height: 1.65; }

    .small-box {
        cursor: pointer;
        border-radius: 14px;
        box-shadow: 0 10px 24px rgba(15, 23, 42, 0.08);
    }

    .verification-filter-card {
        border-radius: 16px;
        border: 1px solid #e2e8f0;
        box-shadow: 0 12px 28px rgba(15, 23, 42, 0.05);
    }

    .verification-filter-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
        gap: .75rem;
        align-items: end;
    }

    .verification-filter-field label {
        display: inline-flex;
        align-items: center;
        gap: .35rem;
        font-size: .72rem;
        font-weight: 700;
        color: #64748b;
        margin-bottom: .35rem;
        text-transform: uppercase;
        letter-spacing: .05em;
    }

    .verification-filter-actions {
        display: flex;
        flex-wrap: wrap;
        gap: .45rem;
        align-items: end;
    }

    .badge-belum   { background: #6c757d; }
    .badge-sesuai  { background: #28a745; }
    .badge-tidak   { background: #dc3545; }
    .badge-perlu   { background: #ffc107; color: #212529; }

    .verification-table-card {
        border-radius: 18px;
        overflow: hidden;
        border: 1px solid #e2e8f0;
        box-shadow: 0 14px 28px rgba(15, 23, 42, 0.06);
    }

    .verification-table-card .card-header {
        background: #fff;
        border-bottom: 1px solid #e2e8f0;
        padding: .95rem 1.15rem;
    }

    .verification-table-card .card-header h3 {
        margin: 0;
        font-size: 1rem;
        font-weight: 800;
        color: #0f172a;
    }

    .verification-table-card .card-footer {
        background: #fff;
        border-top: 1px solid #e2e8f0;
        padding: .9rem 1.15rem;
    }

    #tbl-verifikasi tbody td { vertical-align: middle; }

    .verification-pagination .pagination {
        margin-bottom: 0;
    }

    .verification-pagination .page-link {
        border-radius: 10px !important;
        margin: 0 .15rem;
    }

    @media (max-width: 767.98px) {
        .verification-filter-actions {
            grid-column: 1 / -1;
        }

        .verification-table-card .card-footer .d-flex {
            flex-direction: column;
            align-items: flex-start !important;
            gap: .75rem;
        }
    }
</style>
@endsection

@section('content_header')
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1><i class="fas fa-certificate"></i> Verifikasi Ijazah SMP/MTs</h1>
        </div>
    </div>
@endsection

@section('content')
<div class="container-fluid px-0">

    {{-- Hero --}}
    <div class="verif-hero">
        <h4><i class="fas fa-check-double mr-2"></i>Verifikasi Data Ijazah Siswa</h4>
        <p>Cocokkan data siswa di Simansa dengan data EMIS Kemenag / Kemdikbud. Tandai ketidaksesuaian sebagai acuan perbaikan di Vervalpd.</p>
    </div>

    {{-- Stats: pakai small-box AdminLTE agar tetap stabil --}}
    <div class="row mb-3">
        <div class="col-6 col-md-3">
            <div class="small-box bg-secondary" onclick="filterByStatus('belum_diverifikasi')" style="cursor:pointer;">
                <div class="inner">
                    <h3>{{ $stats['belum'] }}</h3>
                    <p>Belum Diverifikasi</p>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="small-box bg-success" onclick="filterByStatus('sesuai')" style="cursor:pointer;">
                <div class="inner">
                    <h3>{{ $stats['sesuai'] }}</h3>
                    <p>Sesuai</p>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="small-box bg-danger" onclick="filterByStatus('tidak_sesuai')" style="cursor:pointer;">
                <div class="inner">
                    <h3>{{ $stats['tidak_sesuai'] }}</h3>
                    <p>Tidak Sesuai</p>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="small-box bg-warning" onclick="filterByStatus('perlu_perbaikan')" style="cursor:pointer;">
                <div class="inner">
                    <h3>{{ $stats['perlu_perbaikan'] }}</h3>
                    <p>Perlu Perbaikan</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Filter bar --}}
    <div class="card verification-filter-card mb-3">
        <div class="card-body">
        <form method="GET" action="{{ route('admin.verifikasi-ijazah.index') }}" id="filterForm">
            <div class="verification-filter-grid">
                {{-- Cari --}}
                <div class="verification-filter-field">
                    <label><i class="fas fa-search"></i> Cari</label>
                    <input type="text" name="search" class="form-control form-control-sm"
                           placeholder="Nama / NISN / NIK..." value="{{ $search }}">
                </div>
                {{-- Tingkat --}}
                <div class="verification-filter-field">
                    <label><i class="fas fa-layer-group"></i> Tingkat</label>
                    <select name="tingkat" id="selTingkat" class="form-control form-control-sm">
                        <option value="">Semua</option>
                        @foreach($tingkatList as $t)
                            <option value="{{ $t }}" {{ $tingkatFilter == $t ? 'selected' : '' }}>
                                {{ $t == 10 ? 'X' : ($t == 11 ? 'XI' : 'XII') }}
                            </option>
                        @endforeach
                    </select>
                </div>
                {{-- Jurusan --}}
                <div class="verification-filter-field">
                    <label><i class="fas fa-project-diagram"></i> Jurusan</label>
                    <select name="jurusan_id" id="selJurusan" class="form-control form-control-sm">
                        <option value="">Semua</option>
                        @foreach($jurusanAll as $j)
                            <option value="{{ $j->id }}" {{ $jurusanFilter == $j->id ? 'selected' : '' }}
                                    data-tingkat="">
                                {{ $j->singkatan ?? $j->nama_jurusan }}
                            </option>
                        @endforeach
                    </select>
                </div>
                {{-- Kelas --}}
                <div class="verification-filter-field">
                    <label><i class="fas fa-school"></i> Kelas</label>
                    <select name="kelas_id" id="selKelas" class="form-control form-control-sm">
                        <option value="">Semua Kelas</option>
                        @foreach($kelasAll as $kelas)
                            <option value="{{ $kelas->id }}"
                                    data-tingkat="{{ $kelas->tingkat }}"
                                    data-jurusan="{{ $kelas->jurusan_id }}"
                                    {{ $kelasFilter == $kelas->id ? 'selected' : '' }}>
                                {{ $kelas->nama_kelas }}{{ $kelas->jurusan ? ' ('.$kelas->jurusan->singkatan.')' : '' }}{{ $kelas->asrama_suffix }}
                            </option>
                        @endforeach
                    </select>
                </div>
                {{-- Status --}}
                <div class="verification-filter-field">
                    <label><i class="fas fa-check-circle"></i> Status</label>
                    <select name="status" class="form-control form-control-sm" id="statusSelect">
                        <option value="semua" {{ $statusFilter=='semua' ? 'selected' : '' }}>Semua Status</option>
                        <option value="belum_diverifikasi" {{ $statusFilter=='belum_diverifikasi' ? 'selected' : '' }}>Belum Diverifikasi</option>
                        <option value="sesuai" {{ $statusFilter=='sesuai' ? 'selected' : '' }}>Sesuai</option>
                        <option value="tidak_sesuai" {{ $statusFilter=='tidak_sesuai' ? 'selected' : '' }}>Tidak Sesuai</option>
                        <option value="perlu_perbaikan" {{ $statusFilter=='perlu_perbaikan' ? 'selected' : '' }}>Perlu Perbaikan</option>
                    </select>
                </div>
                <div class="verification-filter-actions">
                    <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-search mr-1"></i>Filter</button>
                    <a href="{{ route('admin.verifikasi-ijazah.index') }}" class="btn btn-secondary btn-sm"><i class="fas fa-times mr-1"></i>Reset</a>
                </div>
            </div>
        </form>
    </div>
    </div>

    {{-- Table --}}
    <div class="card verification-table-card">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center flex-wrap" style="gap:.75rem;">
                <h3><i class="fas fa-table mr-2 text-primary"></i>Daftar Siswa</h3>
                <span class="badge badge-light px-3 py-2">Total {{ number_format($siswaList->total()) }} siswa</span>
            </div>
        </div>
        <div class="card-body p-0">
            <table class="table table-hover table-sm mb-0" id="tbl-verifikasi">
                <thead class="thead-light">
                    <tr>
                        <th width="40">#</th>
                        <th>Nama Siswa</th>
                        <th width="110">NISN</th>
                        <th width="120">Kelas</th>
                        <th width="130">Status</th>
                        <th width="150">Diverifikasi Oleh</th>
                        <th width="130">Tgl Verifikasi</th>
                        <th width="100">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($siswaList as $i => $siswa)
                        @php $verif = $siswa->verifikasiIjazah; @endphp
                        <tr>
                            <td>{{ $siswaList->firstItem() + $i }}</td>
                            <td>
                                <strong>{{ $siswa->nama_lengkap }}</strong>
                                @if($siswa->nik)
                                    <br><small class="text-muted">NIK: {{ $siswa->nik }}</small>
                                @endif
                            </td>
                            <td>{!! $siswa->nisn ? e($siswa->nisn) : '<span class="text-danger">-</span>' !!}</td>
                            <td>{{ $siswa->kelasSaatIni?->nama_lengkap ?? $siswa->kelasSaatIni?->nama_kelas ?? '-' }}{!! $siswa->kelasSaatIni?->asrama_badge !!}</td>
                            <td>
                                @if(!$verif || $verif->status === 'belum_diverifikasi')
                                    <span class="badge badge-belum text-white">Belum</span>
                                @elseif($verif->status === 'sesuai')
                                    <span class="badge badge-sesuai text-white">Sesuai</span>
                                @elseif($verif->status === 'tidak_sesuai')
                                    <span class="badge badge-tidak text-white">Tidak Sesuai</span>
                                @else
                                    <span class="badge badge-perlu">Perlu Perbaikan</span>
                                @endif
                            </td>
                            <td>
                                <small>{{ $verif?->verifikator_nama ?? '-' }}</small>
                            </td>
                            <td>
                                <small>{{ $verif?->verified_at?->format('d/m/Y H:i') ?? '-' }}</small>
                            </td>
                            <td>
                                <a href="{{ route('admin.verifikasi-ijazah.show', $siswa->id) }}"
                                   class="btn btn-sm btn-primary">
                                    <i class="fas fa-search"></i> Verifikasi
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">
                                <i class="fas fa-search fa-2x mb-2 d-block opacity-50"></i>
                                Tidak ada data siswa ditemukan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($siswaList->hasPages())
        <div class="card-footer">
            <div class="d-flex justify-content-between align-items-center">
                <small class="text-muted">
                    Menampilkan {{ $siswaList->firstItem() }}-{{ $siswaList->lastItem() }} dari {{ $siswaList->total() }} siswa
                </small>
                <div class="verification-pagination">
                    {{ $siswaList->onEachSide(1)->links('pagination::bootstrap-4') }}
                </div>
            </div>
        </div>
        @endif
    </div>

</div>
@endsection

@php
    $jsKelasData   = $kelasAll->map(fn($k) => [
        'id'      => $k->id,
        'tingkat' => $k->tingkat,
        'jurusan' => $k->jurusan_id,
        'label'   => $k->nama_kelas . ($k->jurusan ? ' ('.$k->jurusan->singkatan.')' : '') . $k->asrama_suffix,
    ])->values();
    $jsJurusanData = $jurusanAll->map(fn($j) => [
        'id'    => $j->id,
        'label' => $j->singkatan ?? $j->nama_jurusan,
    ])->values();
@endphp

@section('js')
<script>
function filterByStatus(status) {
    document.getElementById('statusSelect').value = status;
    document.getElementById('statusSelect').closest('form').submit();
}

(function () {
    const kelasData   = {!! json_encode($jsKelasData) !!};
    const jurusanData = {!! json_encode($jsJurusanData) !!};

    const jurusanByTingkat = {};
    kelasData.forEach(k => {
        if (!k.jurusan) return;
        if (!jurusanByTingkat[k.tingkat]) jurusanByTingkat[k.tingkat] = new Set();
        jurusanByTingkat[k.tingkat].add(k.jurusan);
    });

    const selTingkat = document.getElementById('selTingkat');
    const selJurusan = document.getElementById('selJurusan');
    const selKelas   = document.getElementById('selKelas');

    function rebuildJurusan(tingkat, selectedJurusan) {
        selJurusan.innerHTML = '<option value="">Semua</option>';
        const allowed = tingkat ? jurusanByTingkat[tingkat] : null;
        jurusanData.forEach(j => {
            if (allowed && !allowed.has(j.id)) return;
            const opt = document.createElement('option');
            opt.value = j.id;
            opt.textContent = j.label;
            if (String(j.id) === String(selectedJurusan)) opt.selected = true;
            selJurusan.appendChild(opt);
        });
    }

    function rebuildKelas(tingkat, jurusan, selectedKelas) {
        selKelas.innerHTML = '<option value="">Semua Kelas</option>';
        kelasData.forEach(k => {
            if (tingkat && k.tingkat != tingkat) return;
            if (jurusan && String(k.jurusan) !== String(jurusan)) return;
            const opt = document.createElement('option');
            opt.value = k.id;
            opt.textContent = k.label;
            if (String(k.id) === String(selectedKelas)) opt.selected = true;
            selKelas.appendChild(opt);
        });
    }

    selTingkat.addEventListener('change', function () {
        rebuildJurusan(this.value, '');
        rebuildKelas(this.value, '', '');
    });

    selJurusan.addEventListener('change', function () {
        rebuildKelas(selTingkat.value, this.value, '');
    });

    rebuildJurusan('{{ $tingkatFilter }}', '{{ $jurusanFilter }}');
    rebuildKelas('{{ $tingkatFilter }}', '{{ $jurusanFilter }}', '{{ $kelasFilter }}');
})();
</script>
@endsection
