@extends('adminlte::page')
@section('title', $election->exists ? 'Edit Pemilihan OSIS' : 'Buat Pemilihan OSIS')
@php
    $isPaused = $election->status === 'paused';
    $selectedRoles = old('candidate_roles', $election->exists ? $election->candidateRoleKeys() : ['chairman', 'vice_chairman']);
@endphp

@section('content_header')
<div class="osis-form-hero"><div><span><i class="fas fa-cog mr-2"></i>Pengaturan Pemilihan</span><h1>{{ $isPaused ? 'Edit Pemilihan yang Dijeda' : ($election->exists ? 'Edit Pemilihan OSIS' : 'Buat Pemilihan OSIS') }}</h1><p>{{ $isPaused ? 'Paket, kandidat, tahun pelajaran, dan DPT tetap dikunci agar suara yang sudah masuk tetap aman.' : 'Atur periode, susunan kandidat, jadwal, sasaran pemilih, dan tata tertib.' }}</p></div><a href="{{ $election->exists ? route('admin.osis-election.show',$election) : route('admin.osis-election.index') }}" class="btn btn-light"><i class="fas fa-arrow-left mr-1"></i> Kembali</a></div>
@stop

@section('content')
<form method="POST" action="{{ $election->exists ? route('admin.osis-election.update',$election) : route('admin.osis-election.store') }}" id="electionForm">
@csrf @if($election->exists) @method('PUT') @endif
@if($isPaused)<div class="pause-edit-notice"><i class="fas fa-pause-circle"></i><div><strong>Mode edit aman</strong><span>Voting berhenti sementara. Susunan posisi dan kandidat tidak dapat diubah setelah publikasi.</span></div></div>@endif
<div class="row">
<div class="col-xl-8">
    <section class="form-panel mb-4"><div class="form-panel-title"><i class="fas fa-info-circle"></i><div><h2>Identitas Pemilihan</h2><p>Informasi utama yang akan terlihat oleh siswa.</p></div></div>
        <div class="form-group"><label>Judul Pemilihan</label><input name="title" value="{{ old('title',$election->title) }}" class="form-control @error('title') is-invalid @enderror" placeholder="Contoh: Pemilihan Pengurus OSIS 2026/2027" required>@error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
        <div class="form-group"><label>Tema / Slogan Besar</label><input name="theme" value="{{ old('theme',$election->theme) }}" class="form-control" placeholder="Suara Kita, Masa Depan Kita"></div>
        <div class="form-group"><label>Deskripsi</label><textarea name="description" rows="4" class="form-control" placeholder="Jelaskan tujuan pemilihan...">{{ old('description',$election->description) }}</textarea></div>
        <div class="form-group mb-0"><label>Petunjuk untuk Siswa</label><textarea name="instructions" rows="4" class="form-control" placeholder="Baca visi misi, tentukan pilihan, lalu konfirmasi dengan password akun.">{{ old('instructions',$election->instructions) }}</textarea></div>
    </section>
    <section class="form-panel mb-4"><div class="form-panel-title"><i class="fas fa-user-tie"></i><div><h2>Susunan Kandidat per Paket</h2><p>Pilih minimal dua posisi. Ketua wajib dipilih.</p></div></div>
        <div class="candidate-role-options">@foreach(\App\Models\OsisElection::CANDIDATE_ROLE_DEFINITIONS as $key=>$role)<label><input type="checkbox" name="candidate_roles[]" value="{{ $key }}" @checked(in_array($key,$selectedRoles,true)) @disabled($isPaused)><span><i class="fas {{ $role['icon'] }}"></i><b>{{ $role['label'] }}</b><small>{{ $key === 'chairman' ? 'Wajib' : 'Opsional' }}</small></span></label>@endforeach</div>
        @if($isPaused)
            @foreach($selectedRoles as $role)
                <input type="hidden" name="candidate_roles[]" value="{{ $role }}">
            @endforeach
        @endif
        @error('candidate_roles')<div class="text-danger small mt-2">{{ $message }}</div>@enderror
    </section>
</div>
<div class="col-xl-4">
    <section class="form-panel mb-4"><div class="form-panel-title"><i class="fas fa-calendar-alt"></i><div><h2>Periode & Jadwal</h2><p>Waktu memakai zona Asia/Jakarta.</p></div></div>
        <div class="form-group"><label>Tahun Pelajaran</label><select name="tahun_pelajaran_id" class="form-control" required @disabled($isPaused)>@foreach($years as $year)<option value="{{ $year->id }}" @selected(old('tahun_pelajaran_id',$election->tahun_pelajaran_id)===$year->id)>{{ $year->nama }} · {{ $year->semester_aktif }}{{ $year->is_active ? ' (Aktif)' : '' }}</option>@endforeach</select>@if($isPaused)<input type="hidden" name="tahun_pelajaran_id" value="{{ $election->tahun_pelajaran_id }}">@endif</div>
        <div class="form-group"><label>Mulai</label><input type="datetime-local" name="starts_at" value="{{ old('starts_at',$election->starts_at?->format('Y-m-d\TH:i')) }}" class="form-control" required></div>
        <div class="form-group"><label>Selesai</label><input type="datetime-local" name="ends_at" value="{{ old('ends_at',$election->ends_at?->format('Y-m-d\TH:i')) }}" class="form-control" required></div>
    </section>
    <section class="form-panel mb-4"><div class="form-panel-title"><i class="fas fa-users"></i><div><h2>Hak Pilih</h2><p>Daftar pemilih dibekukan saat publikasi.</p></div></div>
        <label>Kelompok yang Boleh Memilih</label><div class="level-options">@foreach([10=>'Kelas X',11=>'Kelas XI',12=>'Kelas XII'] as $level=>$label)<label><input type="checkbox" name="eligible_levels[]" value="{{ $level }}" @checked(in_array($level,old('eligible_levels',$election->eligible_levels ?: [10,11,12]))) @disabled($isPaused)><span>{{ $label }}</span></label>@endforeach</div>
        @if($isPaused)@foreach($election->eligible_levels ?: [] as $level)<input type="hidden" name="eligible_levels[]" value="{{ $level }}">@endforeach<input type="hidden" name="include_gtk" value="{{ $election->include_gtk ? 1 : 0 }}">@else<input type="hidden" name="include_gtk" value="0">@endif<label class="gtk-voter-option"><input type="checkbox" name="include_gtk" value="1" @checked((bool) old('include_gtk',$election->include_gtk)) @disabled($isPaused)><span><i class="fas fa-chalkboard-teacher"></i><b>Sertakan GTK</b><small>Guru dan tenaga kependidikan aktif ikut masuk daftar pemilih tetap.</small></span></label>
        <div class="form-group mb-0 mt-3"><label>Hak Pilih Kandidat</label><select name="candidate_voting_policy" class="form-control" @disabled($isPaused)><option value="except_own" @selected(old('candidate_voting_policy',$election->candidate_voting_policy ?: 'except_own')==='except_own')>Boleh memilih, kecuali paket sendiri</option><option value="not_allowed" @selected(old('candidate_voting_policy',$election->candidate_voting_policy)==='not_allowed')>Kandidat tidak boleh memilih</option></select>@if($isPaused)<input type="hidden" name="candidate_voting_policy" value="{{ $election->candidate_voting_policy }}">@endif</div>
    </section>
</div></div>
<div class="form-actions"><a href="{{ route('admin.osis-election.index') }}" class="btn btn-outline-secondary">Batal</a><button class="btn btn-primary"><i class="fas fa-save mr-1"></i> Simpan & Susun Kandidat</button></div>
</form>
@stop

@section('css')
<style>
.osis-form-hero{display:flex;justify-content:space-between;align-items:center;gap:1rem;padding:1.35rem 1.5rem;border-radius:16px;background:linear-gradient(135deg,#2563eb,#0f766e);color:#fff;box-shadow:0 15px 34px rgba(37,99,235,.2)}.osis-form-hero span{font-size:.76rem;font-weight:800;text-transform:uppercase}.osis-form-hero h1{font-size:1.45rem;font-weight:800;color:#fff;margin:.3rem 0}.osis-form-hero p{margin:0;color:rgba(255,255,255,.86)}.osis-form-hero .btn{font-weight:700;border-radius:9px}.form-panel{padding:1.25rem;border:1px solid #dbe4f0;border-radius:15px;background:#fff;box-shadow:0 10px 26px rgba(15,23,42,.05)}.form-panel-title{display:flex;gap:.8rem;align-items:flex-start;margin-bottom:1rem}.form-panel-title>i{display:grid;place-items:center;width:42px;height:42px;border-radius:11px;background:#eff6ff;color:#2563eb}.form-panel-title h2{font-size:1rem;font-weight:800;color:#0f172a;margin:0}.form-panel-title p{font-size:.8rem;color:#64748b;margin:.2rem 0 0}.form-panel label{font-size:.76rem;font-weight:800;color:#475569;text-transform:uppercase;letter-spacing:.025em}.form-panel .form-control{border-color:#dbe4f0;border-radius:9px}.level-options{display:grid;grid-template-columns:repeat(3,1fr);gap:.5rem}.level-options input,.gtk-voter-option>input{position:absolute;opacity:0}.level-options span{display:block;padding:.65rem .35rem;text-align:center;border:1px solid #dbe4f0;border-radius:9px;color:#64748b;cursor:pointer}.level-options input:checked+span{background:#eff6ff;border-color:#3b82f6;color:#1d4ed8}.gtk-voter-option{display:block;margin:1rem 0 0!important}.gtk-voter-option span{display:grid;grid-template-columns:38px 1fr;column-gap:.7rem;align-items:center;padding:.7rem;border:1px solid #dbe4f0;border-radius:10px;cursor:pointer;text-transform:none}.gtk-voter-option i{grid-row:1/3;display:grid;place-items:center;width:38px;height:38px;border-radius:9px;background:#f1f5f9;color:#64748b}.gtk-voter-option b{color:#334155}.gtk-voter-option small{color:#64748b}.gtk-voter-option input:checked+span{border-color:#14b8a6;background:#f0fdfa}.gtk-voter-option input:checked+span i{background:#ccfbf1;color:#0f766e}.form-actions{display:flex;justify-content:flex-end;gap:.6rem;padding:1rem;border-radius:13px;background:#fff;border:1px solid #dbe4f0}.form-actions .btn{border-radius:9px;font-weight:700}@media(max-width:767.98px){.osis-form-hero{flex-direction:column;align-items:flex-start}.osis-form-hero .btn{width:100%}.form-actions .btn{flex:1}}
.pause-edit-notice{display:flex;align-items:center;gap:.8rem;padding:.8rem 1rem;margin-bottom:1rem;border:1px solid #fde68a;border-radius:12px;background:#fffbeb;color:#92400e}.pause-edit-notice>i{font-size:1.35rem}.pause-edit-notice strong,.pause-edit-notice span{display:block}.pause-edit-notice span{font-size:.78rem}.candidate-role-options{display:grid;grid-template-columns:repeat(2,1fr);gap:.65rem}.candidate-role-options input{position:absolute;opacity:0}.candidate-role-options span{display:grid;grid-template-columns:40px 1fr auto;align-items:center;gap:.65rem;padding:.75rem;border:1px solid #dbe4f0;border-radius:11px;cursor:pointer;text-transform:none}.candidate-role-options i{display:grid;place-items:center;width:40px;height:40px;border-radius:10px;background:#f1f5f9;color:#64748b}.candidate-role-options b{color:#334155}.candidate-role-options small{color:#94a3b8}.candidate-role-options input:checked+span{border-color:#3b82f6;background:#eff6ff}.candidate-role-options input:checked+span i{background:#dbeafe;color:#2563eb}.candidate-role-options input:disabled+span,.level-options input:disabled+span,.gtk-voter-option input:disabled+span{cursor:not-allowed;opacity:.76}@media(max-width:767.98px){.candidate-role-options{grid-template-columns:1fr}}
</style>
@stop
