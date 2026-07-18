@extends('adminlte::page')
@section('title', $election->exists ? 'Edit Pemilihan OSIS' : 'Buat Pemilihan OSIS')

@section('content_header')
<div class="osis-form-hero"><div><span><i class="fas fa-cog mr-2"></i>Pengaturan Pemilihan</span><h1>{{ $election->exists ? 'Edit Pemilihan OSIS' : 'Buat Pemilihan OSIS' }}</h1><p>Atur periode, jadwal, sasaran pemilih, dan tata tertib sebelum menyusun paket kandidat.</p></div><a href="{{ $election->exists ? route('admin.osis-election.show',$election) : route('admin.osis-election.index') }}" class="btn btn-light"><i class="fas fa-arrow-left mr-1"></i> Kembali</a></div>
@stop

@section('content')
<form method="POST" action="{{ $election->exists ? route('admin.osis-election.update',$election) : route('admin.osis-election.store') }}" id="electionForm">
@csrf @if($election->exists) @method('PUT') @endif
<div class="row">
<div class="col-xl-8">
    <section class="form-panel mb-4"><div class="form-panel-title"><i class="fas fa-info-circle"></i><div><h2>Identitas Pemilihan</h2><p>Informasi utama yang akan terlihat oleh siswa.</p></div></div>
        <div class="form-group"><label>Judul Pemilihan</label><input name="title" value="{{ old('title',$election->title) }}" class="form-control @error('title') is-invalid @enderror" placeholder="Contoh: Pemilihan Pengurus OSIS 2026/2027" required>@error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
        <div class="form-group"><label>Tema / Slogan Besar</label><input name="theme" value="{{ old('theme',$election->theme) }}" class="form-control" placeholder="Suara Kita, Masa Depan Kita"></div>
        <div class="form-group"><label>Deskripsi</label><textarea name="description" rows="4" class="form-control" placeholder="Jelaskan tujuan pemilihan...">{{ old('description',$election->description) }}</textarea></div>
        <div class="form-group mb-0"><label>Petunjuk untuk Siswa</label><textarea name="instructions" rows="4" class="form-control" placeholder="Baca visi misi, tentukan pilihan, lalu konfirmasi dengan password akun.">{{ old('instructions',$election->instructions) }}</textarea></div>
    </section>
</div>
<div class="col-xl-4">
    <section class="form-panel mb-4"><div class="form-panel-title"><i class="fas fa-calendar-alt"></i><div><h2>Periode & Jadwal</h2><p>Waktu memakai zona Asia/Jakarta.</p></div></div>
        <div class="form-group"><label>Tahun Pelajaran</label><select name="tahun_pelajaran_id" class="form-control" required>@foreach($years as $year)<option value="{{ $year->id }}" @selected(old('tahun_pelajaran_id',$election->tahun_pelajaran_id)===$year->id)>{{ $year->nama }} · {{ $year->semester_aktif }}{{ $year->is_active ? ' (Aktif)' : '' }}</option>@endforeach</select></div>
        <div class="form-group"><label>Mulai</label><input type="datetime-local" name="starts_at" value="{{ old('starts_at',$election->starts_at?->format('Y-m-d\TH:i')) }}" class="form-control" required></div>
        <div class="form-group"><label>Selesai</label><input type="datetime-local" name="ends_at" value="{{ old('ends_at',$election->ends_at?->format('Y-m-d\TH:i')) }}" class="form-control" required></div>
    </section>
    <section class="form-panel mb-4"><div class="form-panel-title"><i class="fas fa-users"></i><div><h2>Hak Pilih</h2><p>Daftar pemilih dibekukan saat publikasi.</p></div></div>
        <label>Tingkat yang Boleh Memilih</label><div class="level-options">@foreach([10=>'Kelas X',11=>'Kelas XI',12=>'Kelas XII'] as $level=>$label)<label><input type="checkbox" name="eligible_levels[]" value="{{ $level }}" @checked(in_array($level,old('eligible_levels',$election->eligible_levels ?: [10,11,12])))><span>{{ $label }}</span></label>@endforeach</div>
        <div class="form-group mb-0 mt-3"><label>Hak Pilih Kandidat</label><select name="candidate_voting_policy" class="form-control"><option value="except_own" @selected(old('candidate_voting_policy',$election->candidate_voting_policy ?: 'except_own')==='except_own')>Boleh memilih, kecuali paket sendiri</option><option value="not_allowed" @selected(old('candidate_voting_policy',$election->candidate_voting_policy)==='not_allowed')>Kandidat tidak boleh memilih</option></select></div>
    </section>
</div></div>
<div class="form-actions"><a href="{{ route('admin.osis-election.index') }}" class="btn btn-outline-secondary">Batal</a><button class="btn btn-primary"><i class="fas fa-save mr-1"></i> Simpan & Susun Kandidat</button></div>
</form>
@stop

@section('css')
<style>
.osis-form-hero{display:flex;justify-content:space-between;align-items:center;gap:1rem;padding:1.35rem 1.5rem;border-radius:16px;background:linear-gradient(135deg,#2563eb,#0f766e);color:#fff;box-shadow:0 15px 34px rgba(37,99,235,.2)}.osis-form-hero span{font-size:.76rem;font-weight:800;text-transform:uppercase}.osis-form-hero h1{font-size:1.45rem;font-weight:800;color:#fff;margin:.3rem 0}.osis-form-hero p{margin:0;color:rgba(255,255,255,.86)}.osis-form-hero .btn{font-weight:700;border-radius:9px}.form-panel{padding:1.25rem;border:1px solid #dbe4f0;border-radius:15px;background:#fff;box-shadow:0 10px 26px rgba(15,23,42,.05)}.form-panel-title{display:flex;gap:.8rem;align-items:flex-start;margin-bottom:1rem}.form-panel-title>i{display:grid;place-items:center;width:42px;height:42px;border-radius:11px;background:#eff6ff;color:#2563eb}.form-panel-title h2{font-size:1rem;font-weight:800;color:#0f172a;margin:0}.form-panel-title p{font-size:.8rem;color:#64748b;margin:.2rem 0 0}.form-panel label{font-size:.76rem;font-weight:800;color:#475569;text-transform:uppercase;letter-spacing:.025em}.form-panel .form-control{border-color:#dbe4f0;border-radius:9px}.level-options{display:grid;grid-template-columns:repeat(3,1fr);gap:.5rem}.level-options input{position:absolute;opacity:0}.level-options span{display:block;padding:.65rem .35rem;text-align:center;border:1px solid #dbe4f0;border-radius:9px;color:#64748b;cursor:pointer}.level-options input:checked+span{background:#eff6ff;border-color:#3b82f6;color:#1d4ed8}.form-actions{display:flex;justify-content:flex-end;gap:.6rem;padding:1rem;border-radius:13px;background:#fff;border:1px solid #dbe4f0}.form-actions .btn{border-radius:9px;font-weight:700}@media(max-width:767.98px){.osis-form-hero{flex-direction:column;align-items:flex-start}.osis-form-hero .btn{width:100%}.form-actions .btn{flex:1}}
</style>
@stop
