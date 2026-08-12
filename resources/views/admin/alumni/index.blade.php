@extends('adminlte::page')

@section('title', 'Data Alumni')
@section('plugins.Chartjs', true)

@section('content_header')
    <div class="d-flex flex-wrap justify-content-between align-items-center">
        <div>
            <h1 class="mb-1"><i class="fas fa-user-friends text-primary mr-2"></i>Data Alumni</h1>
            <p class="text-muted mb-0">Arsip siswa yang sudah lulus, terpisah dari daftar siswa aktif.</p>
        </div>
        <ol class="breadcrumb mb-0 bg-transparent p-0">
            <li class="breadcrumb-item">Kesiswaan</li>
            <li class="breadcrumb-item active">Alumni</li>
        </ol>
    </div>
@stop

@section('content')
    <div class="row alumni-kpis">
        <div class="col-6 col-lg-3">
            <div class="info-box shadow-sm"><span class="info-box-icon bg-primary"><i class="fas fa-users"></i></span><div class="info-box-content"><span class="info-box-text">Total Alumni</span><span class="info-box-number">{{ number_format($stats['total']) }}</span></div></div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="info-box shadow-sm"><span class="info-box-icon bg-info"><i class="fas fa-layer-group"></i></span><div class="info-box-content"><span class="info-box-text">Jumlah Angkatan</span><span class="info-box-number">{{ number_format($stats['angkatan']) }}</span></div></div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="info-box shadow-sm"><span class="info-box-icon bg-success"><i class="fas fa-mars"></i></span><div class="info-box-content"><span class="info-box-text">Laki-laki</span><span class="info-box-number">{{ number_format($stats['laki_laki']) }}</span></div></div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="info-box shadow-sm"><span class="info-box-icon bg-danger"><i class="fas fa-venus"></i></span><div class="info-box-content"><span class="info-box-text">Perempuan</span><span class="info-box-number">{{ number_format($stats['perempuan']) }}</span></div></div>
        </div>
    </div>

    <div class="card card-outline card-primary shadow-sm">
        <div class="card-header border-0">
            <h3 class="card-title font-weight-bold"><i class="fas fa-chart-bar mr-2"></i>Statistik Alumni dari Tahun ke Tahun</h3>
            @if($stats['terbaru_label'])
                <div class="card-tools text-muted"><strong>{{ $stats['terbaru_total'] }}</strong> alumni pada {{ $stats['terbaru_label'] }}</div>
            @endif
        </div>
        <div class="card-body"><div class="alumni-chart"><canvas id="alumniYearChart"></canvas></div></div>
    </div>

    <div class="card shadow-sm">
        <div class="card-header border-0 pb-0">
            <h3 class="card-title font-weight-bold"><i class="fas fa-archive mr-2 text-primary"></i>Arsip Alumni</h3>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('admin.alumni.index') }}" class="alumni-filter mb-4">
                <div class="row">
                    <div class="col-lg-5 mb-2">
                        <label for="q">Cari alumni</label>
                        <div class="input-group"><div class="input-group-prepend"><span class="input-group-text"><i class="fas fa-search"></i></span></div><input id="q" name="q" class="form-control" value="{{ request('q') }}" placeholder="Nama, NISN, atau NIS lokal"></div>
                    </div>
                    <div class="col-lg-3 mb-2">
                        <label for="tahun_pelajaran_id">Tahun kelulusan</label>
                        <select id="tahun_pelajaran_id" name="tahun_pelajaran_id" class="form-control">
                            <option value="">Semua angkatan</option>
                            @foreach($tahunPelajaranList as $tahun)
                                <option value="{{ $tahun->id }}" @selected(request('tahun_pelajaran_id') === $tahun->id)>{{ $tahun->nama }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-lg-2 mb-2">
                        <label for="jenis_kelamin">Jenis kelamin</label>
                        <select id="jenis_kelamin" name="jenis_kelamin" class="form-control"><option value="">Semua</option><option value="L" @selected(request('jenis_kelamin') === 'L')>Laki-laki</option><option value="P" @selected(request('jenis_kelamin') === 'P')>Perempuan</option></select>
                    </div>
                    <div class="col-lg-2 mb-2 d-flex align-items-end"><button class="btn btn-primary mr-2"><i class="fas fa-filter mr-1"></i> Terapkan</button><a href="{{ route('admin.alumni.index') }}" class="btn btn-outline-secondary" title="Reset filter"><i class="fas fa-undo"></i></a></div>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-hover alumni-table">
                    <thead><tr><th>Alumni</th><th>Identitas</th><th>Kelas Terakhir</th><th>Tahun Lulus</th><th>Tanggal Lulus</th><th class="text-center">Riwayat</th></tr></thead>
                    <tbody>
                    @forelse($alumni as $record)
                        <tr>
                            <td><div class="d-flex align-items-center"><img class="alumni-avatar mr-3" src="{{ $record->siswa->foto_profile_url }}" alt="Foto {{ $record->siswa->nama_lengkap }}"><div><strong class="d-block text-dark">{{ $record->siswa->nama_lengkap }}</strong><small class="text-muted"><i class="fas fa-{{ $record->siswa->jenis_kelamin === 'L' ? 'mars text-info' : 'venus text-danger' }} mr-1"></i>{{ $record->siswa->jenis_kelamin === 'L' ? 'Laki-laki' : 'Perempuan' }}</small></div></div></td>
                            <td><div>NISN: <strong>{{ $record->siswa->nisn ?: '-' }}</strong></div><small class="text-muted">NIS: {{ $record->siswa->nis_lokal ?: '-' }}</small></td>
                            <td><strong>{{ $record->kelas?->nama_kelas ?: 'Tanpa rombel' }}</strong><small class="d-block text-muted">{{ $record->kelas?->jurusan?->nama_jurusan ?: 'Jurusan tidak tercatat' }}</small></td>
                            <td><span class="badge badge-primary px-3 py-2">{{ $record->tahunPelajaran?->nama ?: '-' }}</span></td>
                            <td>{{ $record->tanggal_keluar?->translatedFormat('d M Y') ?: '-' }}</td>
                            <td class="text-center"><a href="{{ route('admin.alumni.show', $record->siswa) }}" class="btn btn-sm btn-outline-primary"><i class="fas fa-history mr-1"></i> Detail</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center py-5"><i class="fas fa-user-graduate fa-3x text-muted mb-3 d-block"></i><strong>Data alumni belum ditemukan</strong><div class="text-muted">Alumni otomatis muncul setelah kelulusan kelas XII difinalisasi.</div></td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
            <div class="d-flex flex-wrap justify-content-between align-items-center mt-3"><small class="text-muted">Menampilkan {{ $alumni->firstItem() ?? 0 }}–{{ $alumni->lastItem() ?? 0 }} dari {{ $alumni->total() }} alumni</small>{{ $alumni->links('pagination::bootstrap-4') }}</div>
        </div>
    </div>
@stop

@section('css')
<style>
    .alumni-kpis .info-box{border-radius:.75rem;min-height:88px}.alumni-kpis .info-box-icon{border-radius:.75rem 0 0 .75rem;width:66px}.alumni-chart{height:280px}.alumni-filter{background:#f8fafc;border:1px solid #e5e7eb;border-radius:.75rem;padding:1rem}.alumni-filter label{font-size:.78rem;color:#64748b;text-transform:uppercase;letter-spacing:.04em}.alumni-table thead th{border-top:0;color:#64748b;font-size:.75rem;text-transform:uppercase;white-space:nowrap}.alumni-table td{vertical-align:middle}.alumni-avatar{width:44px;height:44px;border-radius:50%;object-fit:cover;background:#e2e8f0}@media(max-width:767.98px){.alumni-chart{height:220px}.alumni-kpis .info-box-icon{width:52px}.alumni-kpis .info-box-text{font-size:.72rem}}
</style>
@stop

@section('js')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const canvas = document.getElementById('alumniYearChart');
    if (!canvas) return;
    new Chart(canvas.getContext('2d'), {type:'bar',data:{labels:@json($stats['labels']),datasets:[{label:'Jumlah Alumni',data:@json($stats['values']),backgroundColor:'rgba(59, 130, 246, .78)',borderColor:'#2563eb',borderWidth:1,borderRadius:6,maxBarThickness:64}]},options:{maintainAspectRatio:false,responsive:true,legend:{display:false},scales:{yAxes:[{ticks:{beginAtZero:true,precision:0},gridLines:{color:'rgba(148,163,184,.18)'}}],xAxes:[{gridLines:{display:false}}]},tooltips:{callbacks:{label:function(item){return ' '+item.yLabel+' alumni';}}}}});
});
</script>
@stop
