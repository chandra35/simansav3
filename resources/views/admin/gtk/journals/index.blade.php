@extends('adminlte::page')
@section('title', 'Jurnal Guru')
@section('content_header') @stop
@section('content')
<div class="journal-hero shadow-sm">
 <div><span class="hero-kicker"><i class="fas fa-graduation-cap mr-1"></i> Pusat dokumentasi guru</span><h1>Jurnal mengajar, lebih terarah.</h1><p>Catat kegiatan, pantau kontribusi, dan siapkan dokumen EKIN/SKP dalam satu tempat.</p></div>
 <div class="hero-actions"><a href="#composer" class="btn btn-light btn-lg js-compose"><i class="fas fa-plus-circle mr-1"></i> Tulis jurnal</a><a class="btn btn-outline-light" href="{{ route('admin.gtk.journals.export', request()->query()) }}"><i class="fas fa-file-pdf mr-1"></i> PDF EKIN/SKP</a></div>
</div>
<div class="row journal-stats">
 <div class="col-sm-6 col-xl-3"><div class="stat-card"><i class="fas fa-book text-primary"></i><div><span>Total entri</span><strong>{{ $summary['total'] }}</strong><small>pada periode dipilih</small></div></div></div>
 <div class="col-sm-6 col-xl-3"><div class="stat-card"><i class="fas fa-chalkboard-teacher text-info"></i><div><span>Mengajar</span><strong>{{ $summary['teaching'] }}</strong><small>catatan pembelajaran</small></div></div></div>
 <div class="col-sm-6 col-xl-3"><div class="stat-card"><i class="fas fa-users text-warning"></i><div><span>Wali kelas</span><strong>{{ $summary['homeroom'] }}</strong><small>pendampingan siswa</small></div></div></div>
 <div class="col-sm-6 col-xl-3"><div class="stat-card"><i class="fas fa-clock text-success"></i><div><span>Waktu tercatat</span><strong>{{ intdiv($summary['minutes'], 60) }}j {{ $summary['minutes'] % 60 }}m</strong><small>akumulasi durasi</small></div></div></div>
</div>
<div class="card filter-card border-0 shadow-sm"><div class="card-body"><div class="d-flex align-items-center mb-3"><div class="filter-icon"><i class="fas fa-sliders-h"></i></div><div><strong>Periode laporan</strong><div class="small text-muted">Sesuaikan riwayat dan ringkasan jurnal.</div></div></div><form method="GET" class="row align-items-end"><div class="col-md-3 form-group"><label>Jenis jurnal</label><select name="jenis" class="custom-select"><option value="">Semua jurnal</option><option value="mengajar" @selected($jenis === 'mengajar')>Jurnal mengajar</option>@if($waliClasses->isNotEmpty())<option value="wali_kelas" @selected($jenis === 'wali_kelas')>Jurnal wali kelas</option>@endif</select></div><div class="col-md-3 form-group"><label>Dari tanggal</label><input type="date" name="dari" value="{{ $dari }}" class="form-control"></div><div class="col-md-3 form-group"><label>Sampai tanggal</label><input type="date" name="sampai" value="{{ $sampai }}" class="form-control"></div><div class="col-md-3 form-group"><button class="btn btn-primary mr-1"><i class="fas fa-check mr-1"></i>Terapkan</button><a href="{{ route('admin.gtk.journals.index') }}" class="btn btn-light">Reset</a></div></form></div></div>
<div class="row"><div class="col-xl-5" id="composer"><div class="card compose-card border-0 shadow-sm"><div class="card-header bg-white border-0 pt-4 px-4"><span class="text-success font-weight-bold"><i class="fas fa-pen-nib mr-1"></i> ENTRI BARU</span><h3 class="mt-1 mb-1">Tulis jurnal hari ini</h3><p class="text-muted small mb-0">Lengkapi informasi inti, lalu simpan sebagai bukti aktivitas.</p></div><form method="POST" action="{{ route('admin.gtk.journals.store') }}"><div class="card-body px-4">@csrf
<div class="row"><div class="col-md-6 form-group"><label>Jenis jurnal</label><select name="jenis" id="journal-type" class="custom-select" required><option value="mengajar">Jurnal mengajar</option>@if($waliClasses->isNotEmpty())<option value="wali_kelas">Jurnal wali kelas</option>@endif</select></div><div class="col-md-6 form-group"><label>Tanggal kegiatan</label><input type="date" name="tanggal" value="{{ old('tanggal', now()->toDateString()) }}" class="form-control" required></div></div>
<div id="teaching-fields"><div class="form-group"><label>Jadwal mengajar</label><select name="jadwal_pelajaran_id" class="custom-select"><option value="">Pilih jadwal mengajar</option>@foreach($schedules as $schedule)<option value="{{ $schedule->id }}">{{ $schedule->mataPelajaran?->nama_mapel }} · {{ $schedule->kelas?->nama_kelas }} · {{ $schedule->hari_label }} jam {{ $schedule->jam_ke }}</option>@endforeach</select><small>Gunakan jadwal agar mata pelajaran dan kelas tercatat otomatis.</small></div></div>
@if($waliClasses->isNotEmpty())<div id="homeroom-fields" class="d-none"><div class="form-group"><label>Kelas wali</label><select name="kelas_id" class="custom-select"><option value="">Pilih kelas wali</option>@foreach($waliClasses as $kelas)<option value="{{ $kelas->id }}">{{ $kelas->nama_lengkap ?? $kelas->nama_kelas }}</option>@endforeach</select></div></div>@endif
<div class="form-group"><label>Topik / agenda</label><input name="topik" value="{{ old('topik') }}" maxlength="255" class="form-control" placeholder="Contoh: Persamaan kuadrat dan penerapannya" required></div><div class="form-group"><label>Kegiatan pembelajaran / tugas</label><textarea name="kegiatan" rows="4" class="form-control" placeholder="Jelaskan kegiatan utama yang dilakukan..." required>{{ old('kegiatan') }}</textarea></div><div class="row"><div class="col-md-7 form-group"><label>Asesmen / tindak lanjut</label><textarea name="asesmen_tindak_lanjut" rows="2" class="form-control">{{ old('asesmen_tindak_lanjut') }}</textarea></div><div class="col-md-5 form-group"><label>Durasi (menit)</label><input type="number" min="1" max="720" name="durasi_menit" value="{{ old('durasi_menit') }}" class="form-control" placeholder="90"></div></div><div class="form-group mb-0"><label>Catatan tambahan <span class="text-muted font-weight-normal">(opsional)</span></label><textarea name="catatan" rows="2" class="form-control">{{ old('catatan') }}</textarea></div></div><div class="card-footer bg-white border-0 px-4 pb-4"><button class="btn btn-success btn-lg btn-block"><i class="fas fa-save mr-1"></i> Simpan jurnal</button></div></form></div></div>
<div class="col-xl-7"><div class="card history-card border-0 shadow-sm"><div class="card-header bg-white border-0 pt-4 px-4 d-flex justify-content-between"><div><span class="text-info font-weight-bold"><i class="fas fa-history mr-1"></i> RIWAYAT</span><h3 class="mt-1">Aktivitas jurnal</h3></div><span class="badge badge-pill badge-info align-self-start p-2">{{ $journals->total() }} entri</span></div>@forelse($journals as $journal)
    <div class="journal-item mx-4">
        <div class="journal-date">
            <strong>{{ $journal->tanggal->format('d') }}</strong>
            <span>{{ $journal->tanggal->translatedFormat('M') }}</span>
            <small>{{ $journal->tanggal->format('Y') }}</small>
        </div>
        <div class="journal-main">
            <div class="d-flex flex-wrap align-items-center mb-1">
                <span class="type-badge {{ $journal->jenis === 'mengajar' ? 'teaching' : 'homeroom' }}">
                    {{ $journal->jenis === 'mengajar' ? 'Mengajar' : 'Wali kelas' }}
                </span>
                @if($journal->durasi_menit)
                    <span class="small text-muted ml-2">
                        <i class="far fa-clock mr-1"></i>{{ $journal->durasi_menit }} menit
                    </span>
                @endif
            </div>
            <strong>{{ $journal->topik }}</strong>
            <p>{{ \Illuminate\Support\Str::limit($journal->kegiatan, 135) }}</p>
            <span class="context">
                <i class="fas fa-school mr-1"></i>{{ $journal->kelas_snapshot ?: 'Kelas tidak dicatat' }}
                @if($journal->mapel_snapshot)
                    · {{ $journal->mapel_snapshot }}
                @endif
            </span>
        </div>
        <div class="journal-actions">
            <a class="btn btn-sm btn-light" title="Edit" href="{{ route('admin.gtk.journals.edit', $journal) }}">
                <i class="fas fa-pen text-primary"></i>
            </a>
            <form method="POST" action="{{ route('admin.gtk.journals.destroy', $journal) }}" onsubmit="return confirm('Hapus jurnal ini?')">
                @csrf
                @method('DELETE')
                <button class="btn btn-sm btn-light" title="Hapus">
                    <i class="fas fa-trash text-danger"></i>
                </button>
            </form>
        </div>
    </div>
@empty
    <div class="empty-state">
        <div class="empty-icon"><i class="fas fa-book-reader"></i></div>
        <h4>Belum ada jurnal pada periode ini</h4>
        <p>Mulai dokumentasikan aktivitas mengajar atau tugas wali kelas untuk membangun riwayat EKIN/SKP yang lengkap.</p>
        <a href="#composer" class="btn btn-primary js-compose">
            <i class="fas fa-plus mr-1"></i> Tulis jurnal pertama
        </a>
    </div>
@endforelse

@if($journals->hasPages())
    <div class="card-footer bg-white">
        {{ $journals->withQueryString()->links() }}
    </div>
@endif
</div></div></div>
@stop
@section('css')<style>
.journal-hero{background:linear-gradient(125deg,#183b75,#2365b4 55%,#15a5ad);color:#fff;border-radius:16px;padding:2rem;display:flex;justify-content:space-between;gap:1.5rem;align-items:center;margin-bottom:1.25rem}.hero-kicker{font-size:.78rem;font-weight:700;letter-spacing:.08em;opacity:.85}.journal-hero h1{font-size:1.8rem;font-weight:700;margin:.4rem 0}.journal-hero p{margin:0;opacity:.88;max-width:580px}.hero-actions{display:flex;gap:.5rem;flex-wrap:wrap}.journal-stats .col-sm-6{margin-bottom:1rem}.stat-card{background:#fff;border-radius:13px;padding:1.15rem;display:flex;gap:1rem;align-items:center;box-shadow:0 3px 13px rgba(31,55,90,.08);height:100%}.stat-card>i{font-size:1.55rem;width:45px;height:45px;border-radius:12px;background:#f2f6fc;display:grid;place-items:center}.stat-card span,.stat-card small{display:block;color:#7b8796;font-size:.78rem}.stat-card strong{display:block;font-size:1.25rem;color:#2a3850}.filter-card,.compose-card,.history-card{border-radius:14px}.filter-icon{width:35px;height:35px;display:grid;place-items:center;background:#eaf3ff;color:#1976d2;border-radius:9px;margin-right:.75rem}.compose-card label,.filter-card label{font-size:.82rem;font-weight:700;color:#4a5668}.compose-card small{color:#8491a2;font-size:.74rem}.journal-item{padding:1.05rem 0;border-top:1px solid #eef1f5;display:flex;gap:1rem}.journal-date{width:52px;min-width:52px;text-align:center;padding:.35rem;background:#f4f7fb;border-radius:10px;color:#526174;line-height:1}.journal-date strong{font-size:1.18rem;color:#263c5a;display:block}.journal-date span{font-size:.7rem;text-transform:uppercase;font-weight:700}.journal-date small{font-size:.62rem}.journal-main{flex:1;min-width:0}.journal-main>strong{color:#27364d}.journal-main p{font-size:.83rem;margin:.3rem 0;color:#687587}.context{font-size:.76rem;color:#7d8a9c}.type-badge{font-size:.67rem;font-weight:700;border-radius:20px;padding:.25rem .55rem}.type-badge.teaching{background:#e9f2ff;color:#1763b0}.type-badge.homeroom{background:#fff3dd;color:#b57600}.journal-actions{display:flex;gap:.25rem;align-items:flex-start}.journal-actions form{margin:0}.empty-state{text-align:center;padding:4.5rem 1.5rem;color:#718096}.empty-state h4{color:#35445c;margin-top:1rem}.empty-state p{max-width:420px;margin:.5rem auto 1.25rem;font-size:.9rem}.empty-icon{margin:auto;width:66px;height:66px;border-radius:50%;display:grid;place-items:center;background:#edf5ff;color:#2672c9;font-size:1.7rem}@media(max-width:767px){.journal-hero{padding:1.4rem;display:block}.hero-actions{margin-top:1rem}.journal-hero h1{font-size:1.45rem}.journal-item{margin-left:1rem!important;margin-right:1rem!important}.journal-actions{flex-direction:column}.filter-card .form-group{margin-bottom:.8rem}}
</style>@stop
@section('js')<script>document.addEventListener('DOMContentLoaded',function(){const type=document.getElementById('journal-type'),teach=document.getElementById('teaching-fields'),home=document.getElementById('homeroom-fields');function toggle(){if(!type)return;const isTeach=type.value==='mengajar';teach.classList.toggle('d-none',!isTeach);if(home)home.classList.toggle('d-none',isTeach)}if(type){type.addEventListener('change',toggle);toggle()}document.querySelectorAll('.js-compose').forEach(el=>el.addEventListener('click',()=>setTimeout(()=>document.querySelector('#composer input[name=tanggal]').focus(),350)));});</script>@stop
