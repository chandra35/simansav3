@extends('adminlte::page')

@section('title', 'Jurnal Guru')
@section('content_header')
<div class="d-flex flex-wrap justify-content-between align-items-center mb-2">
    <div><h1 class="mb-1"><i class="fas fa-book-open text-primary"></i> Jurnal Guru</h1><p class="text-muted mb-0">Dokumentasi mengajar dan tugas wali kelas untuk EKIN/SKP.</p></div>
    <a class="btn btn-outline-danger mt-2 mt-md-0" href="{{ route('admin.gtk.journals.export', request()->query()) }}"><i class="fas fa-file-pdf mr-1"></i> Export PDF EKIN/SKP</a>
</div>
@stop

@section('content')
<div class="card card-outline card-primary shadow-sm">
 <div class="card-body">
  <form method="GET" class="row align-items-end">
   <div class="col-md-3 form-group"><label>Jenis jurnal</label><select name="jenis" class="form-control"><option value="">Semua jurnal</option><option value="mengajar" @selected($jenis === 'mengajar')>Jurnal mengajar</option>@if($waliClasses->isNotEmpty())<option value="wali_kelas" @selected($jenis === 'wali_kelas')>Jurnal wali kelas</option>@endif</select></div>
   <div class="col-md-3 form-group"><label>Dari tanggal</label><input type="date" name="dari" value="{{ $dari }}" class="form-control"></div>
   <div class="col-md-3 form-group"><label>Sampai tanggal</label><input type="date" name="sampai" value="{{ $sampai }}" class="form-control"></div>
   <div class="col-md-3 form-group"><button class="btn btn-primary"><i class="fas fa-filter mr-1"></i> Terapkan</button><a href="{{ route('admin.gtk.journals.index') }}" class="btn btn-light">Reset</a></div>
  </form>
 </div>
</div>
<div class="row">
 <div class="col-lg-4">
  <div class="card card-outline card-success"><div class="card-header"><h3 class="card-title font-weight-bold">Tambah jurnal</h3></div><form method="POST" action="{{ route('admin.gtk.journals.store') }}"><div class="card-body">@csrf
   <div class="form-group"><label>Jenis</label><select name="jenis" id="jenis" class="form-control" required><option value="mengajar">Jurnal mengajar</option>@if($waliClasses->isNotEmpty())<option value="wali_kelas">Jurnal wali kelas</option>@endif</select></div>
   <div class="form-group"><label>Tanggal</label><input type="date" name="tanggal" value="{{ old('tanggal', now()->toDateString()) }}" class="form-control" required></div>
   <div class="form-group"><label>Jadwal mengajar</label><select name="jadwal_pelajaran_id" class="form-control"><option value="">Pilih jadwal (opsional)</option>@foreach($schedules as $schedule)<option value="{{ $schedule->id }}">{{ $schedule->mataPelajaran?->nama_mapel }} · {{ $schedule->kelas?->nama_kelas }} · {{ $schedule->hari_label }} jam {{ $schedule->jam_ke }}</option>@endforeach</select><small class="text-muted">Wajib untuk jurnal mengajar bila jadwal tersedia.</small></div>
   @if($waliClasses->isNotEmpty())<div class="form-group"><label>Kelas wali</label><select name="kelas_id" class="form-control"><option value="">Pilih kelas wali</option>@foreach($waliClasses as $kelas)<option value="{{ $kelas->id }}">{{ $kelas->nama_lengkap ?? $kelas->nama_kelas }}</option>@endforeach</select></div>@endif
   <div class="form-group"><label>Topik / agenda</label><input name="topik" value="{{ old('topik') }}" maxlength="255" class="form-control" required></div>
   <div class="form-group"><label>Kegiatan pembelajaran / tugas</label><textarea name="kegiatan" rows="4" class="form-control" required>{{ old('kegiatan') }}</textarea></div>
   <div class="form-group"><label>Asesmen / tindak lanjut</label><textarea name="asesmen_tindak_lanjut" rows="2" class="form-control">{{ old('asesmen_tindak_lanjut') }}</textarea></div>
   <div class="form-group"><label>Durasi (menit)</label><input type="number" min="1" max="720" name="durasi_menit" value="{{ old('durasi_menit') }}" class="form-control"></div>
   <div class="form-group mb-0"><label>Catatan</label><textarea name="catatan" rows="2" class="form-control">{{ old('catatan') }}</textarea></div>
  </div><div class="card-footer"><button class="btn btn-success btn-block"><i class="fas fa-save mr-1"></i> Simpan jurnal</button></div></form></div>
 </div>
 <div class="col-lg-8"><div class="card card-outline card-info"><div class="card-header"><h3 class="card-title font-weight-bold">Riwayat jurnal</h3><div class="card-tools"><span class="badge badge-info">{{ $journals->total() }} entri</span></div></div><div class="table-responsive"><table class="table table-hover mb-0"><thead><tr><th>Tanggal</th><th>Jenis</th><th>Topik dan kegiatan</th><th>Kelas / mapel</th><th></th></tr></thead><tbody>@forelse($journals as $journal)<tr><td class="text-nowrap">{{ $journal->tanggal->translatedFormat('d M Y') }}</td><td><span class="badge badge-{{ $journal->jenis === 'mengajar' ? 'primary' : 'warning' }}">{{ $journal->jenis === 'mengajar' ? 'Mengajar' : 'Wali kelas' }}</span></td><td><strong>{{ $journal->topik }}</strong><div class="small text-muted">{{ \Illuminate\Support\Str::limit($journal->kegiatan, 110) }}</div></td><td>{{ $journal->kelas_snapshot ?: '-' }}<div class="small text-muted">{{ $journal->mapel_snapshot ?: '-' }}</div></td><td class="text-nowrap"><a class="btn btn-sm btn-outline-primary" href="{{ route('admin.gtk.journals.edit', $journal) }}"><i class="fas fa-edit"></i></a> <form class="d-inline" method="POST" action="{{ route('admin.gtk.journals.destroy', $journal) }}" onsubmit="return confirm('Hapus jurnal ini?')">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button></form></td></tr>@empty<tr><td colspan="5" class="text-center text-muted py-5"><i class="fas fa-book-open fa-2x d-block mb-2"></i>Belum ada jurnal pada periode ini.</td></tr>@endforelse</tbody></table></div>@if($journals->hasPages())<div class="card-footer">{{ $journals->withQueryString()->links() }}</div>@endif</div></div>
</div>
@stop
