@extends('adminlte::page')

@section('title', 'Tambah Mutasi Siswa')

@section('content_header')
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1><i class="fas fa-exchange-alt"></i> Tambah Mutasi Siswa</h1>
        </div>
        <div class="col-sm-6">
            <div class="float-sm-right">
                <a href="{{ route('admin.mutasi-siswa.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Kembali
                </a>
            </div>
        </div>
    </div>
@endsection

@section('content')

@if($errors->any())
<div class="alert alert-danger alert-dismissible">
    <button type="button" class="close" data-dismiss="alert">&times;</button>
    <i class="fas fa-exclamation-triangle mr-1"></i> <strong>Terdapat kesalahan:</strong>
    <ul class="mb-0 mt-1">
        @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
    </ul>
</div>
@endif

<form action="{{ route('admin.mutasi-siswa.store') }}" method="POST" enctype="multipart/form-data" id="formMutasi">
    @csrf
    {{-- Hidden fields: final submitted values --}}
    <input type="hidden" name="jenis_mutasi"     id="final_jenis">
    <input type="hidden" name="siswa_id"         id="final_siswa_id">

    {{-- ── Step Indicator (hidden on step 0) ─────────────────────────────── --}}
    <div id="wizard-header" class="d-none mb-4">
        <div class="card mb-0">
            <div class="card-body py-3 px-4">
                <div class="d-flex align-items-center">
                    <div class="step-indicator">
                        <div class="step-circle" id="si-1">1</div>
                        <div class="step-label" id="sl-1">—</div>
                    </div>
                    <div class="step-line flex-grow-1 mx-2" id="line-1"></div>
                    <div class="step-indicator">
                        <div class="step-circle" id="si-2">2</div>
                        <div class="step-label" id="sl-2">—</div>
                    </div>
                    <div class="step-line flex-grow-1 mx-2" id="line-2"></div>
                    <div class="step-indicator">
                        <div class="step-circle" id="si-3">3</div>
                        <div class="step-label" id="sl-3">Dokumen</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ── STEP 0: Pilih Jenis ──────────────────────────────────────────── --}}
    <div id="step-0" class="wizard-step">
        <div class="row justify-content-center">
            <div class="col-md-7 col-lg-6">
                <div class="card">
                    <div class="card-header text-center" style="border-top:3px solid #007bff;">
                        <h3 class="card-title mb-0">
                            <i class="fas fa-exchange-alt mr-2"></i>Pilih Jenis Mutasi
                        </h3>
                        <small class="text-muted">Tentukan arah perpindahan siswa</small>
                    </div>
                    <div class="card-body py-4">
                        <div class="row">
                            <div class="col-6">
                                <div class="jenis-card" id="jcard-masuk" onclick="selectJenis('masuk')">
                                    <div class="jenis-icon" style="background:#17a2b8;">
                                        <i class="fas fa-sign-in-alt"></i>
                                    </div>
                                    <h5 class="mt-3 mb-1 font-weight-bold">Mutasi Masuk</h5>
                                    <p class="text-muted small mb-0">Siswa dari sekolah lain masuk ke sini</p>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="jenis-card" id="jcard-keluar" onclick="selectJenis('keluar')">
                                    <div class="jenis-icon" style="background:#dc3545;">
                                        <i class="fas fa-sign-out-alt"></i>
                                    </div>
                                    <h5 class="mt-3 mb-1 font-weight-bold">Mutasi Keluar</h5>
                                    <p class="text-muted small mb-0">Siswa dari sini pindah ke sekolah lain</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ── STEP 1 MASUK: Sekolah Asal ──────────────────────────────────── --}}
    <div id="step-1-masuk" class="wizard-step d-none">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header bg-info" style="border-top:3px solid #17a2b8;">
                        <h3 class="card-title text-white">
                            <i class="fas fa-school mr-2"></i>Data Sekolah Asal
                        </h3>
                        <small class="text-white-50">Dari mana siswa ini berasal?</small>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>NPSN Sekolah Asal</label>
                                    <input type="text" name="npsn_sekolah_asal" class="form-control"
                                        maxlength="8" placeholder="8 digit NPSN"
                                        value="{{ old('npsn_sekolah_asal') }}">
                                </div>
                            </div>
                            <div class="col-md-8">
                                <div class="form-group">
                                    <label>Nama Sekolah Asal <span class="text-danger">*</span></label>
                                    <input type="text" name="sekolah_asal" id="sekolah_asal"
                                        class="form-control" placeholder="Nama lengkap sekolah asal"
                                        value="{{ old('sekolah_asal') }}">
                                    <div class="invalid-feedback">Nama sekolah asal wajib diisi</div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Kelas Asal</label>
                                    <input type="text" name="kelas_asal" class="form-control"
                                        placeholder="Contoh: VII-A, 10 IPA 1"
                                        value="{{ old('kelas_asal') }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Kota / Alamat Sekolah</label>
                                    <input type="text" name="alamat_sekolah_asal" class="form-control"
                                        placeholder="Kota/Kabupaten" value="{{ old('alamat_sekolah_asal') }}">
                                </div>
                            </div>
                        </div>
                        <div class="form-group mb-0">
                            <label>Alasan Mutasi Masuk</label>
                            <textarea name="alasan_mutasi_masuk" class="form-control" rows="2"
                                placeholder="Alasan pindah ke sekolah ini (opsional)">{{ old('alasan_mutasi_masuk') }}</textarea>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ── STEP 1 KELUAR: Cari Siswa ───────────────────────────────────── --}}
    <div id="step-1-keluar" class="wizard-step d-none">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header" style="border-top:3px solid #007bff;">
                        <h3 class="card-title">
                            <i class="fas fa-search mr-2"></i>Cari Siswa
                        </h3>
                        <small class="text-muted">Siswa mana yang akan dimutasi keluar?</small>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label>Nama / NISN / NIK Siswa <span class="text-danger">*</span></label>
                            <select id="siswa_id_keluar" class="form-control" style="width:100%;"></select>
                            <small class="text-muted"><i class="fas fa-search mr-1"></i>Ketik minimal 2 karakter</small>
                        </div>
                        <div id="siswa-info-keluar" class="d-none">
                            <div class="student-info-card">
                                <div class="d-flex align-items-center">
                                    <div class="student-avatar mr-3">
                                        <i class="fas fa-user-graduate fa-2x text-primary"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <h5 class="mb-1" id="sk-nama">—</h5>
                                        <div class="row">
                                            <div class="col-auto">
                                                <small class="text-muted">NISN: <strong id="sk-nisn">—</strong></small>
                                            </div>
                                            <div class="col-auto">
                                                <small class="text-muted">Status: <span id="sk-status" class="badge badge-secondary">—</span></small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ── STEP 2 MASUK: Tautkan Siswa ─────────────────────────────────── --}}
    <div id="step-2-masuk" class="wizard-step d-none">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header" style="border-top:3px solid #007bff;">
                        <h3 class="card-title">
                            <i class="fas fa-user-plus mr-2"></i>Data Siswa
                        </h3>
                        <small class="text-muted">Tautkan ke data siswa dalam sistem</small>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label>Nama / NISN Siswa <span class="text-danger">*</span></label>
                            <select id="siswa_id_masuk" class="form-control" style="width:100%;"></select>
                            <small class="text-muted"><i class="fas fa-search mr-1"></i>Ketik minimal 2 karakter</small>
                        </div>
                        <div id="siswa-info-masuk" class="d-none">
                            <div class="student-info-card">
                                <div class="d-flex align-items-center">
                                    <div class="student-avatar mr-3">
                                        <i class="fas fa-user-graduate fa-2x text-primary"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <h5 class="mb-1" id="sm-nama">—</h5>
                                        <div class="row">
                                            <div class="col-auto">
                                                <small class="text-muted">NISN: <strong id="sm-nisn">—</strong></small>
                                            </div>
                                            <div class="col-auto">
                                                <small class="text-muted">Status: <span id="sm-status" class="badge badge-secondary">—</span></small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ── STEP 2 KELUAR: Sekolah Tujuan ───────────────────────────────── --}}
    <div id="step-2-keluar" class="wizard-step d-none">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header bg-danger" style="border-top:3px solid #dc3545;">
                        <h3 class="card-title text-white">
                            <i class="fas fa-school mr-2"></i>Sekolah Tujuan
                        </h3>
                        <small class="text-white-50">Ke mana siswa ini akan pindah?</small>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>NPSN Sekolah Tujuan</label>
                                    <input type="text" name="npsn_sekolah_tujuan" class="form-control"
                                        maxlength="8" placeholder="8 digit NPSN"
                                        value="{{ old('npsn_sekolah_tujuan') }}">
                                </div>
                            </div>
                            <div class="col-md-8">
                                <div class="form-group">
                                    <label>Nama Sekolah Tujuan <span class="text-danger">*</span></label>
                                    <input type="text" name="sekolah_tujuan" id="sekolah_tujuan"
                                        class="form-control" placeholder="Nama lengkap sekolah tujuan"
                                        value="{{ old('sekolah_tujuan') }}">
                                    <div class="invalid-feedback">Nama sekolah tujuan wajib diisi</div>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Kota / Alamat Sekolah Tujuan</label>
                            <input type="text" name="alamat_sekolah_tujuan" class="form-control"
                                placeholder="Kota/Kabupaten" value="{{ old('alamat_sekolah_tujuan') }}">
                        </div>
                        <div class="form-group mb-0">
                            <label>Alasan Mutasi Keluar</label>
                            <textarea name="alasan_mutasi_keluar" class="form-control" rows="2"
                                placeholder="Alasan pindah (opsional)">{{ old('alasan_mutasi_keluar') }}</textarea>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ── STEP 3: Tanggal + Dokumen (Shared) ──────────────────────────── --}}
    <div id="step-3" class="wizard-step d-none">
        <div class="row justify-content-center">
            <div class="col-md-8">
                {{-- Summary --}}
                <div class="card card-outline card-primary mb-3">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-clipboard-list mr-1"></i>Ringkasan</h3>
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-sm mb-0">
                            <tr>
                                <td class="text-muted pl-3" width="36%">Jenis Mutasi</td>
                                <td id="sum-jenis">—</td>
                            </tr>
                            <tr>
                                <td class="text-muted pl-3">Siswa</td>
                                <td id="sum-siswa">—</td>
                            </tr>
                            <tr>
                                <td class="text-muted pl-3" id="sum-sekolah-label">Sekolah</td>
                                <td id="sum-sekolah">—</td>
                            </tr>
                        </table>
                    </div>
                </div>

                {{-- Tanggal + Tahun --}}
                <div class="card">
                    <div class="card-header" style="border-top:3px solid #28a745;">
                        <h3 class="card-title">
                            <i class="fas fa-calendar-alt mr-2"></i>Waktu &amp; Dokumen
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Tahun Pelajaran <span class="text-danger">*</span></label>
                                    <select name="tahun_pelajaran_id" id="tahun_pelajaran_id" class="form-control">
                                        <option value="">-- Pilih --</option>
                                        @foreach($tahunPelajarans as $tp)
                                        <option value="{{ $tp->id }}"
                                            {{ old('tahun_pelajaran_id', $tahunAktif?->id) === $tp->id ? 'selected' : '' }}>
                                            {{ $tp->nama_tahun_pelajaran ?? $tp->nama ?? $tp->id }}
                                            {{ $tp->is_active ? '(Aktif)' : '' }}
                                        </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Tanggal Mutasi <span class="text-danger">*</span></label>
                                    <input type="date" name="tanggal_mutasi" id="tanggal_mutasi"
                                        class="form-control" value="{{ old('tanggal_mutasi', date('Y-m-d')) }}">
                                </div>
                            </div>
                        </div>
                        <hr>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Nomor Surat Mutasi <small class="text-muted">(opsional)</small></label>
                                    <input type="text" name="nomor_surat_mutasi" class="form-control"
                                        placeholder="No. surat..." value="{{ old('nomor_surat_mutasi') }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>File Surat <small class="text-muted">(PDF, maks 5MB)</small></label>
                                    <div class="custom-file">
                                        <input type="file" class="custom-file-input" id="file_surat_mutasi"
                                            name="file_surat_mutasi" accept=".pdf">
                                        <label class="custom-file-label" for="file_surat_mutasi">Pilih file PDF...</label>
                                    </div>
                                    <div id="file-preview" class="mt-2 d-none">
                                        <div class="alert alert-success py-2 mb-0">
                                            <i class="fas fa-file-pdf mr-1 text-danger"></i>
                                            <span id="file-name" class="small"></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="form-group mb-0">
                            <label>Catatan <small class="text-muted">(opsional)</small></label>
                            <textarea name="catatan" class="form-control" rows="3"
                                placeholder="Catatan tambahan...">{{ old('catatan') }}</textarea>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ── Navigation ───────────────────────────────────────────────────── --}}
    <div id="wizard-nav" class="row justify-content-center mt-2 d-none">
        <div class="col-md-8">
            <div class="d-flex justify-content-between align-items-center">
                <button type="button" id="btn-prev" class="btn btn-secondary">
                    <i class="fas fa-arrow-left mr-1"></i>Kembali
                </button>
                <div>
                    <a href="{{ route('admin.mutasi-siswa.index') }}" class="btn btn-link text-muted mr-2">Batal</a>
                    <button type="button" id="btn-next" class="btn btn-primary">
                        Selanjutnya <i class="fas fa-arrow-right ml-1"></i>
                    </button>
                    <button type="submit" id="btn-submit" class="btn btn-success d-none">
                        <i class="fas fa-save mr-1"></i>Simpan Mutasi
                    </button>
                </div>
            </div>
        </div>
    </div>

</form>
@endsection

@section('css')
<style>
/* ── Jenis Cards ─────────────────────────────────────── */
.jenis-card {
    border: 2px solid #dee2e6;
    border-radius: 14px;
    padding: 28px 16px 20px;
    text-align: center;
    cursor: pointer;
    transition: all .25s cubic-bezier(.4,0,.2,1);
    user-select: none;
}
.jenis-card:hover {
    border-color: #adb5bd;
    transform: translateY(-4px);
    box-shadow: 0 8px 24px rgba(0,0,0,.1);
}
.jenis-card.selected-masuk {
    border-color: #17a2b8 !important;
    background: linear-gradient(135deg, #e8f7fb, #fff);
    box-shadow: 0 6px 20px rgba(23,162,184,.2);
    transform: translateY(-4px);
}
.jenis-card.selected-keluar {
    border-color: #dc3545 !important;
    background: linear-gradient(135deg, #fdf0f1, #fff);
    box-shadow: 0 6px 20px rgba(220,53,69,.2);
    transform: translateY(-4px);
}
.jenis-icon {
    width: 68px; height: 68px;
    border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    margin: 0 auto;
    font-size: 1.6rem; color: white;
    transition: transform .25s;
}
.jenis-card:hover .jenis-icon,
.jenis-card.selected-masuk .jenis-icon,
.jenis-card.selected-keluar .jenis-icon {
    transform: scale(1.1);
}

/* ── Step Indicator ──────────────────────────────────── */
.step-indicator { text-align: center; flex-shrink: 0; }
.step-circle {
    width: 38px; height: 38px;
    border-radius: 50%;
    background: #dee2e6; color: #adb5bd;
    display: flex; align-items: center; justify-content: center;
    font-weight: 700; font-size: .875rem;
    margin: 0 auto 4px;
    transition: all .35s cubic-bezier(.4,0,.2,1);
}
.step-circle.active  { background: #007bff; color: #fff; box-shadow: 0 0 0 4px rgba(0,123,255,.2); }
.step-circle.done    { background: #28a745; color: #fff; }
.step-label          { font-size: .7rem; color: #adb5bd; white-space: nowrap; transition: color .3s; }
.step-label.active   { color: #007bff; font-weight: 600; }
.step-label.done     { color: #28a745; }
.step-line           { height: 2px; background: #dee2e6; margin-bottom: 22px; transition: background .4s; }
.step-line.done      { background: #28a745; }

/* ── Wizard Transitions ───────────────────────────────── */
.wizard-step.d-none  { display: none !important; }
@keyframes slideInRight {
    from { opacity: 0; transform: translateX(32px); }
    to   { opacity: 1; transform: translateX(0); }
}
@keyframes slideInLeft {
    from { opacity: 0; transform: translateX(-32px); }
    to   { opacity: 1; transform: translateX(0); }
}
.wizard-step.anim-fwd  { animation: slideInRight .3s cubic-bezier(.4,0,.2,1) forwards; }
.wizard-step.anim-back { animation: slideInLeft  .3s cubic-bezier(.4,0,.2,1) forwards; }

/* ── Student Info Card ───────────────────────────────── */
.student-info-card {
    background: #f0f7ff;
    border: 1px solid #b8daff;
    border-radius: 10px;
    padding: 14px 16px;
    margin-top: 8px;
}
.student-avatar {
    width: 52px; height: 52px;
    border-radius: 50%;
    background: #ddeeff;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
}
</style>
@endsection

@section('js')
<script>
(function() {
    // ── State ────────────────────────────────────────────────────────────────
    var step       = 0;
    var jenis      = null;
    var siswaData  = null;
    var STEPS      = 3;
    var stepLabels = {
        masuk:  ['Sekolah Asal', 'Data Siswa',     'Dokumen'],
        keluar: ['Cari Siswa',   'Sekolah Tujuan', 'Dokumen'],
    };

    // ── Get step element ID ──────────────────────────────────────────────────
    function stepId(s) {
        if (s === 0) return 'step-0';
        if (s === 3) return 'step-3';
        return 'step-' + s + '-' + jenis;
    }

    // ── Show step with animation ─────────────────────────────────────────────
    function showStep(newStep, dir) {
        document.querySelectorAll('.wizard-step').forEach(function(el) {
            el.classList.add('d-none');
            el.classList.remove('anim-fwd', 'anim-back');
        });
        var el = document.getElementById(stepId(newStep));
        if (!el) return;
        el.classList.remove('d-none');
        el.classList.add(dir === 'back' ? 'anim-back' : 'anim-fwd');
        step = newStep;

        var header = document.getElementById('wizard-header');
        var nav    = document.getElementById('wizard-nav');
        if (newStep === 0) {
            header.classList.add('d-none');
            nav.classList.add('d-none');
        } else {
            header.classList.remove('d-none');
            nav.classList.remove('d-none');
        }

        // Prev label
        document.getElementById('btn-prev').innerHTML =
            newStep === 1
                ? '<i class="fas fa-arrow-left mr-1"></i>Ubah Jenis'
                : '<i class="fas fa-arrow-left mr-1"></i>Kembali';

        // Next vs Submit
        var btnN = document.getElementById('btn-next');
        var btnS = document.getElementById('btn-submit');
        if (newStep === STEPS) {
            btnN.classList.add('d-none');
            btnS.classList.remove('d-none');
            updateSummary();
        } else {
            btnN.classList.remove('d-none');
            btnS.classList.add('d-none');
        }

        updateIndicator(newStep);

        // Lazy init select2 on relevant steps
        if (newStep === 1 && jenis === 'keluar') initSelect2('keluar');
        if (newStep === 2 && jenis === 'masuk')  initSelect2('masuk');
    }

    // ── Step indicator ───────────────────────────────────────────────────────
    function updateIndicator(s) {
        if (!jenis) return;
        var labels = stepLabels[jenis];
        for (var i = 1; i <= STEPS; i++) {
            var circle = document.getElementById('si-' + i);
            var label  = document.getElementById('sl-' + i);
            var line   = document.getElementById('line-' + i);
            label.textContent = labels[i - 1] || 'Dokumen';
            circle.classList.remove('active', 'done');
            label.classList.remove('active', 'done');
            if (i < s) {
                circle.classList.add('done');
                circle.innerHTML = '<i class="fas fa-check" style="font-size:.65rem"></i>';
                label.classList.add('done');
            } else if (i === s) {
                circle.classList.add('active');
                circle.textContent = i;
                label.classList.add('active');
            } else {
                circle.textContent = i;
            }
            if (line) line.classList.toggle('done', i < s);
        }
    }

    // ── Select jenis ─────────────────────────────────────────────────────────
    window.selectJenis = function(j) {
        jenis = j;
        document.getElementById('final_jenis').value = j;
        document.getElementById('jcard-masuk').classList.remove('selected-masuk', 'selected-keluar');
        document.getElementById('jcard-keluar').classList.remove('selected-masuk', 'selected-keluar');
        document.getElementById('jcard-' + j).classList.add('selected-' + j);
        setTimeout(function() { showStep(1, 'fwd'); }, 220);
    };

    // ── Navigation ───────────────────────────────────────────────────────────
    document.getElementById('btn-next').addEventListener('click', function() {
        if (!validate(step)) return;
        showStep(step + 1, 'fwd');
    });
    document.getElementById('btn-prev').addEventListener('click', function() {
        if (step === 1) {
            step = 0; showStep(0, 'back');
            document.getElementById('jcard-masuk').classList.remove('selected-masuk');
            document.getElementById('jcard-keluar').classList.remove('selected-keluar');
        } else {
            showStep(step - 1, 'back');
        }
    });

    // ── Validation ───────────────────────────────────────────────────────────
    function validate(s) {
        if (s === 1 && jenis === 'masuk') {
            var f = document.getElementById('sekolah_asal');
            if (!f.value.trim()) { f.classList.add('is-invalid'); f.focus(); return false; }
            f.classList.remove('is-invalid');
        }
        if (s === 1 && jenis === 'keluar') {
            if (!document.getElementById('final_siswa_id').value) {
                Swal.fire({ icon: 'warning', title: 'Pilih siswa terlebih dahulu', timer: 2000, showConfirmButton: false });
                return false;
            }
        }
        if (s === 2 && jenis === 'masuk') {
            if (!document.getElementById('final_siswa_id').value) {
                Swal.fire({ icon: 'warning', title: 'Pilih siswa terlebih dahulu', timer: 2000, showConfirmButton: false });
                return false;
            }
        }
        if (s === 2 && jenis === 'keluar') {
            var f = document.getElementById('sekolah_tujuan');
            if (!f.value.trim()) { f.classList.add('is-invalid'); f.focus(); return false; }
            f.classList.remove('is-invalid');
        }
        if (s === 3) {
            var tp = document.getElementById('tahun_pelajaran_id');
            var tg = document.getElementById('tanggal_mutasi');
            var ok = true;
            if (!tp.value) { tp.classList.add('is-invalid'); ok = false; }
            if (!tg.value) { tg.classList.add('is-invalid'); ok = false; }
            if (!ok) { Swal.fire({ icon: 'warning', title: 'Lengkapi tahun pelajaran dan tanggal', timer: 2000, showConfirmButton: false }); return false; }
            tp.classList.remove('is-invalid');
            tg.classList.remove('is-invalid');
        }
        return true;
    }

    // ── Select2 AJAX ─────────────────────────────────────────────────────────
    var s2Init = {};
    function initSelect2(j) {
        if (s2Init[j]) return;
        s2Init[j] = true;
        var sel    = j === 'keluar' ? '#siswa_id_keluar' : '#siswa_id_masuk';
        var card   = j === 'keluar' ? '#siswa-info-keluar' : '#siswa-info-masuk';
        var prefix = j === 'keluar' ? 'sk' : 'sm';

        $(sel).select2({
            placeholder: 'Ketik nama, NISN siswa...',
            minimumInputLength: 2,
            width: '100%',
            language: {
                inputTooShort: function() { return 'Ketik minimal 2 karakter...'; },
                searching:     function() { return 'Mencari...'; },
                noResults:     function() { return 'Siswa tidak ditemukan'; },
            },
            ajax: {
                url: '{{ route("admin.mutasi-siswa.search-siswa") }}',
                dataType: 'json',
                delay: 350,
                data: function(p) { return { q: p.term }; },
                processResults: function(data) {
                    return {
                        results: data.map(function(s) {
                            return { id: s.id, text: s.nama_lengkap, nisn: s.nisn, status: s.status_siswa };
                        })
                    };
                },
                cache: true,
            },
            templateResult: function(s) {
                if (s.loading) return s.text;
                return $('<div class="py-1"><strong>' + s.text + '</strong><br>'
                    + '<small class="text-muted">NISN: ' + (s.nisn || '-') + ' &bull; ' + (s.status || '') + '</small></div>');
            },
            templateSelection: function(s) { return s.text || s.id; },
        });

        $(sel).on('select2:select', function(e) {
            var d = e.params.data;
            siswaData = d;
            document.getElementById('final_siswa_id').value = d.id;
            document.getElementById(prefix + '-nama').textContent   = d.text;
            document.getElementById(prefix + '-nisn').textContent   = d.nisn  || '-';
            document.getElementById(prefix + '-status').textContent = d.status || '-';
            $(card).removeClass('d-none').addClass('anim-fwd');
        }).on('select2:unselect', function() {
            siswaData = null;
            document.getElementById('final_siswa_id').value = '';
            $(card).addClass('d-none');
        });

        @if($selectedSiswa)
        if (j === 'keluar') {
            var opt = new Option('{{ addslashes($selectedSiswa->nama_lengkap) }}', '{{ $selectedSiswa->id }}', true, true);
            $(sel).append(opt).trigger('change');
            siswaData = { text: '{{ addslashes($selectedSiswa->nama_lengkap) }}', nisn: '{{ $selectedSiswa->nisn ?? "" }}', status: '{{ $selectedSiswa->status_siswa ?? "" }}' };
            document.getElementById('final_siswa_id').value = '{{ $selectedSiswa->id }}';
            document.getElementById(prefix + '-nama').textContent   = siswaData.text;
            document.getElementById(prefix + '-nisn').textContent   = siswaData.nisn   || '-';
            document.getElementById(prefix + '-status').textContent = siswaData.status || '-';
            $(card).removeClass('d-none');
        }
        @endif
    }

    // ── Summary ───────────────────────────────────────────────────────────────
    function updateSummary() {
        document.getElementById('sum-jenis').innerHTML = jenis === 'masuk'
            ? '<span class="badge badge-info"><i class="fas fa-sign-in-alt mr-1"></i>Masuk</span>'
            : '<span class="badge badge-danger"><i class="fas fa-sign-out-alt mr-1"></i>Keluar</span>';
        document.getElementById('sum-siswa').textContent = siswaData ? siswaData.text : '—';
        if (jenis === 'masuk') {
            document.getElementById('sum-sekolah-label').textContent = 'Sekolah Asal';
            document.getElementById('sum-sekolah').textContent = (document.querySelector('[name="sekolah_asal"]') || {}).value || '—';
        } else {
            document.getElementById('sum-sekolah-label').textContent = 'Sekolah Tujuan';
            document.getElementById('sum-sekolah').textContent = (document.querySelector('[name="sekolah_tujuan"]') || {}).value || '—';
        }
    }

    // ── File preview ─────────────────────────────────────────────────────────
    document.getElementById('file_surat_mutasi').addEventListener('change', function() {
        var file = this.files[0];
        if (file) {
            this.nextElementSibling.textContent = file.name;
            document.getElementById('file-name').textContent = file.name + ' (' + (file.size / 1024).toFixed(1) + ' KB)';
            document.getElementById('file-preview').classList.remove('d-none');
        }
    });

    // ── Clear invalid on input ────────────────────────────────────────────────
    document.querySelectorAll('.form-control').forEach(function(el) {
        el.addEventListener('input', function() { this.classList.remove('is-invalid'); });
    });

    // ── Recovery from old() on validation error ───────────────────────────────
    @if(old('jenis_mutasi'))
    selectJenis('{{ old("jenis_mutasi") }}');
    setTimeout(function() { showStep(3, 'fwd'); }, 50);
    @if($selectedSiswa)
    siswaData = { text: '{{ addslashes($selectedSiswa->nama_lengkap) }}', nisn: '', status: '' };
    document.getElementById('final_siswa_id').value = '{{ $selectedSiswa->id }}';
    @endif
    @endif

})();
</script>
@endsection