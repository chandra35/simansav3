@extends('adminlte::page')

@section('title', 'Verifikasi Ijazah SMP/MTs - SIMANSA')

@section('css')
<style>
    .verif-hero {
        background: linear-gradient(135deg, rgba(124, 58, 237, .90), rgba(79, 70, 229, .85));
        border-radius: 18px;
        padding: 1rem 1.2rem;
        margin-bottom: .75rem;
        color: #fff;
    }
    .verif-hero h4 { font-weight: 800; margin: 0 0 .2rem 0; font-size: 1.3rem; }
    .verif-hero p  { margin: 0; font-size: .85rem; opacity: .88; }

    .stat-card { border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,.12); cursor: pointer; transition: transform .15s; padding: 1rem; }
    .stat-card:hover { transform: translateY(-2px); }
    .stat-card .stat-num { font-size: 2rem; font-weight: 800; line-height: 1; }
    .stat-card .stat-label { font-size: .75rem; text-transform: uppercase; letter-spacing: .04em; margin-top: .25rem; }

    .filter-bar { background: #f8f9fa; border-radius: 10px; padding: .7rem 1rem; margin-bottom: 1rem; }

    .badge-belum   { background: #6c757d; }
    .badge-sesuai  { background: #28a745; }
    .badge-tidak   { background: #dc3545; }
    .badge-perlu   { background: #ffc107; color: #212529; }

    #tbl-verifikasi tbody td { vertical-align: middle; }
</style>
@endsection

@section('content_header')
    <h1 class="m-0 text-dark" style="font-size:1.2rem;font-weight:700;">
        <i class="fas fa-certificate text-purple mr-2"></i> Verifikasi Ijazah SMP/MTs
    </h1>
@endsection

@section('content')
<div class="container-fluid px-0">

    {{-- Hero --}}
    <div class="verif-hero">
        <h4><i class="fas fa-check-double mr-2"></i>Verifikasi Data Ijazah Siswa</h4>
        <p>Cocokkan data siswa di Simansa dengan data EMIS Kemenag / Kemdikbud. Tandai ketidaksesuaian sebagai acuan perbaikan di Vervalpd.</p>
    </div>

    {{-- Stats --}}
    {{-- CATATAN: Jangan pakai class .card untuk stat boxes di AdminLTE — gunakan div biasa dengan inline style penuh --}}
    <div class="row mb-3">
        <div class="col-6 col-md-3 mb-2">
            <div class="stat-card" style="background-color:#6c757d;color:#fff;" onclick="filterByStatus('belum_diverifikasi')">
                <div class="stat-num">{{ $stats['belum'] }}</div>
                <div class="stat-label">Belum Diverifikasi</div>
            </div>
        </div>
        <div class="col-6 col-md-3 mb-2">
            <div class="stat-card" style="background-color:#28a745;color:#fff;" onclick="filterByStatus('sesuai')">
                <div class="stat-num">{{ $stats['sesuai'] }}</div>
                <div class="stat-label">Sesuai</div>
            </div>
        </div>
        <div class="col-6 col-md-3 mb-2">
            <div class="stat-card" style="background-color:#dc3545;color:#fff;" onclick="filterByStatus('tidak_sesuai')">
                <div class="stat-num">{{ $stats['tidak_sesuai'] }}</div>
                <div class="stat-label">Tidak Sesuai</div>
            </div>
        </div>
        <div class="col-6 col-md-3 mb-2">
            <div class="stat-card" style="background-color:#ffc107;color:#212529;" onclick="filterByStatus('perlu_perbaikan')">
                <div class="stat-num">{{ $stats['perlu_perbaikan'] }}</div>
                <div class="stat-label">Perlu Perbaikan</div>
            </div>
        </div>
    </div>

    {{-- Filter bar --}}
    <div class="filter-bar d-flex flex-wrap align-items-center gap-2" style="gap:.5rem;">
        <form method="GET" action="{{ route('admin.verifikasi-ijazah.index') }}" class="d-flex flex-wrap" style="gap:.5rem;width:100%;">
            <input type="text" name="search" class="form-control form-control-sm" style="width:200px;"
                   placeholder="Cari nama / NISN..." value="{{ $search }}">
            <select name="kelas_id" class="form-control form-control-sm" style="width:160px;">
                <option value="">Semua Kelas</option>
                @foreach($kelasOptions as $kelas)
                    <option value="{{ $kelas->id }}" {{ $kelasFilter == $kelas->id ? 'selected' : '' }}>
                        {{ $kelas->nama_lengkap ?? $kelas->nama_kelas }}
                    </option>
                @endforeach
            </select>
            <select name="status" class="form-control form-control-sm" style="width:180px;" id="statusSelect">
                <option value="semua" {{ $statusFilter=='semua' ? 'selected' : '' }}>Semua Status</option>
                <option value="belum_diverifikasi" {{ $statusFilter=='belum_diverifikasi' ? 'selected' : '' }}>Belum Diverifikasi</option>
                <option value="sesuai" {{ $statusFilter=='sesuai' ? 'selected' : '' }}>Sesuai</option>
                <option value="tidak_sesuai" {{ $statusFilter=='tidak_sesuai' ? 'selected' : '' }}>Tidak Sesuai</option>
                <option value="perlu_perbaikan" {{ $statusFilter=='perlu_perbaikan' ? 'selected' : '' }}>Perlu Perbaikan</option>
            </select>
            <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-search mr-1"></i>Filter</button>
            <a href="{{ route('admin.verifikasi-ijazah.index') }}" class="btn btn-secondary btn-sm"><i class="fas fa-times mr-1"></i>Reset</a>
        </form>
    </div>

    {{-- Table --}}
    <div class="card shadow-sm">
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
                            <td>{{ $siswa->nisn ?? '<span class="text-danger">-</span>' }}</td>
                            <td>{{ $siswa->kelasSaatIni?->nama_lengkap ?? $siswa->kelasSaatIni?->nama_kelas ?? '-' }}</td>
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
                                   class="btn btn-sm btn-primary btn-sm">
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
                    Menampilkan {{ $siswaList->firstItem() }}–{{ $siswaList->lastItem() }} dari {{ $siswaList->total() }} siswa
                </small>
                {{ $siswaList->links() }}
            </div>
        </div>
        @endif
    </div>

</div>
@endsection

@section('js')
<script>
function filterByStatus(status) {
    document.getElementById('statusSelect').value = status;
    document.getElementById('statusSelect').closest('form').submit();
}
</script>
@endsection
