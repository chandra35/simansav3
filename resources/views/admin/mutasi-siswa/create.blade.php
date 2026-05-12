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
    <ul class="mb-0 mt-1">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
</div>
@endif

<form action="{{ route('admin.mutasi-siswa.store') }}" method="POST" enctype="multipart/form-data" id="formMutasi">
    @csrf
    <input type="hidden" name="jenis_mutasi" id="final_jenis">
    <input type="hidden" name="siswa_id"     id="final_siswa_id">

    {{-- ── Progress Bar ──────────────────────────────────────────────────── --}}
    <div id="wizard-header" class="d-none mb-4">
        <div class="wz-progress-wrap">
            <div class="wz-steps">
                <div class="wz-step-item" id="wsi-1">
                    <div class="wz-step-num">1</div>
                    <div class="wz-step-lbl" id="wsl-1">—</div>
                </div>
                <div class="wz-connector" id="wsc-1"></div>
                <div class="wz-step-item" id="wsi-2">
                    <div class="wz-step-num">2</div>
                    <div class="wz-step-lbl" id="wsl-2">—</div>
                </div>
                <div class="wz-connector" id="wsc-2"></div>
                <div class="wz-step-item" id="wsi-3">
                    <div class="wz-step-num">3</div>
                    <div class="wz-step-lbl" id="wsl-3">Dokumen</div>
                </div>
            </div>
        </div>
    </div>

    {{-- ── STEP 0: Pilih Jenis ──────────────────────────────────────────── --}}
    <div id="step-0" class="wz-pane">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6">
                <div class="wz-card text-center mb-4">
                    <div class="wz-card-icon-top">
                        <i class="fas fa-exchange-alt"></i>
                    </div>
                    <h4 class="font-weight-bold mt-3 mb-1">Pilih Jenis Mutasi</h4>
                    <p class="text-muted small mb-4">Tentukan arah perpindahan siswa terlebih dahulu</p>
                    <div class="row">
                        <div class="col-6 pr-2">
                            <div class="jcard" id="jcard-masuk" onclick="selectJenis('masuk')">
                                <div class="jcard-circle jcard-circle-info">
                                    <i class="fas fa-sign-in-alt"></i>
                                </div>
                                <h5 class="mt-3 mb-1">Mutasi Masuk</h5>
                                <p class="text-muted small mb-0">Siswa dari sekolah lain masuk ke sini</p>
                                <div class="jcard-check d-none"><i class="fas fa-check-circle text-info fa-lg"></i></div>
                            </div>
                        </div>
                        <div class="col-6 pl-2">
                            <div class="jcard" id="jcard-keluar" onclick="selectJenis('keluar')">
                                <div class="jcard-circle jcard-circle-danger">
                                    <i class="fas fa-sign-out-alt"></i>
                                </div>
                                <h5 class="mt-3 mb-1">Mutasi Keluar</h5>
                                <p class="text-muted small mb-0">Siswa dari sini pindah ke sekolah lain</p>
                                <div class="jcard-check d-none"><i class="fas fa-check-circle text-danger fa-lg"></i></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ── STEP 1 MASUK: Sekolah Asal ──────────────────────────────────── --}}
    <div id="step-1-masuk" class="wz-pane d-none">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="wz-card wz-card-info">
                    <div class="wz-card-header wz-header-info">
                        <div class="d-flex align-items-center">
                            <div class="wz-header-icon mr-3"><i class="fas fa-school"></i></div>
                            <div>
                                <h5 class="mb-0 font-weight-bold">Data Sekolah Asal</h5>
                                <small class="opacity-75">Dari mana siswa ini berasal?</small>
                            </div>
                        </div>
                    </div>
                    <div class="wz-card-body">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="wz-label">NPSN Sekolah Asal</label>
                                    <div class="input-group">
                                        <input type="text" id="npsn_sekolah_asal" name="npsn_sekolah_asal" class="form-control" maxlength="8"
                                            placeholder="8 digit NPSN" value="{{ old('npsn_sekolah_asal') }}" autocomplete="off">
                                        <div class="input-group-append">
                                            <span class="input-group-text" id="npsn-asal-spinner" style="display:none;">
                                                <i class="fas fa-spinner fa-spin text-primary"></i>
                                            </span>
                                            <span class="input-group-text" id="npsn-asal-ok" style="display:none;">
                                                <i class="fas fa-check-circle text-success"></i>
                                            </span>
                                        </div>
                                    </div>
                                    <small class="text-muted d-block mt-1"><i class="fas fa-magic mr-1"></i>Isi 8 digit untuk auto-isi nama &amp; kota</small>
                                </div>
                            </div>
                            <div class="col-md-8">
                                <div class="form-group">
                                    <label class="wz-label">Nama Sekolah Asal <span class="text-danger">*</span></label>
                                    <input type="text" name="sekolah_asal" id="sekolah_asal" class="form-control"
                                        placeholder="Nama lengkap sekolah asal" value="{{ old('sekolah_asal') }}">
                                    <div class="invalid-feedback">Nama sekolah asal wajib diisi</div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="wz-label">Kelas Asal</label>
                                    <input type="text" name="kelas_asal" class="form-control"
                                        placeholder="Contoh: VII-A, 10 IPA 1" value="{{ old('kelas_asal') }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="wz-label">Kota / Alamat Sekolah</label>
                                    <input type="text" id="alamat_sekolah_asal" name="alamat_sekolah_asal" class="form-control"
                                        placeholder="Kota/Kabupaten" value="{{ old('alamat_sekolah_asal') }}">
                                </div>
                            </div>
                        </div>
                        <div class="form-group mb-0">
                            <label class="wz-label">Alasan Mutasi Masuk <small class="text-muted font-weight-normal">(opsional)</small></label>
                            <textarea name="alasan_mutasi_masuk" class="form-control" rows="2"
                                placeholder="Alasan pindah ke sekolah ini...">{{ old('alasan_mutasi_masuk') }}</textarea>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ── STEP 1 KELUAR: Cari Siswa ───────────────────────────────────── --}}
    <div id="step-1-keluar" class="wz-pane d-none">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="wz-card wz-card-primary">
                    <div class="wz-card-header wz-header-primary">
                        <div class="d-flex align-items-center">
                            <div class="wz-header-icon mr-3"><i class="fas fa-search"></i></div>
                            <div>
                                <h5 class="mb-0 font-weight-bold">Cari Siswa</h5>
                                <small class="opacity-75">Siswa mana yang akan dimutasi keluar?</small>
                            </div>
                        </div>
                    </div>
                    <div class="wz-card-body">
                        <div class="form-group mb-2">
                            <label class="wz-label">Nama / NISN Siswa <span class="text-danger">*</span></label>
                            <select id="siswa_id_keluar" style="width:100%;"></select>
                            <small class="text-muted mt-1 d-block"><i class="fas fa-keyboard mr-1"></i>Ketik minimal 2 karakter nama atau NISN</small>
                        </div>
                        <div id="siswa-info-keluar" class="d-none siswa-found-card">
                            <div class="d-flex align-items-center">
                                <div class="siswa-avatar mr-3">
                                    <i class="fas fa-user-graduate"></i>
                                </div>
                                <div>
                                    <div class="font-weight-bold" id="sk-nama"></div>
                                    <small class="text-muted">NISN: <strong id="sk-nisn"></strong></small>
                                    &nbsp;<span id="sk-status" class="badge badge-info"></span>
                                </div>
                                <div class="ml-auto">
                                    <i class="fas fa-check-circle fa-2x text-success"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ── STEP 2 MASUK: Tautkan Siswa ─────────────────────────────────── --}}
    <div id="step-2-masuk" class="wz-pane d-none">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="wz-card wz-card-primary">
                    <div class="wz-card-header wz-header-primary">
                        <div class="d-flex align-items-center">
                            <div class="wz-header-icon mr-3"><i class="fas fa-user-plus"></i></div>
                            <div>
                                <h5 class="mb-0 font-weight-bold">Tautkan Data Siswa</h5>
                                <small class="opacity-75">Hubungkan ke data siswa dalam sistem</small>
                            </div>
                        </div>
                    </div>
                    <div class="wz-card-body">
                        <div class="form-group mb-2">
                            <label class="wz-label">Nama / NISN Siswa <span class="text-danger">*</span></label>
                            <select id="siswa_id_masuk" style="width:100%;"></select>
                            <small class="text-muted mt-1 d-block"><i class="fas fa-keyboard mr-1"></i>Ketik minimal 2 karakter nama atau NISN</small>
                        </div>
                        <div id="siswa-info-masuk" class="d-none siswa-found-card">
                            <div class="d-flex align-items-center">
                                <div class="siswa-avatar mr-3">
                                    <i class="fas fa-user-graduate"></i>
                                </div>
                                <div>
                                    <div class="font-weight-bold" id="sm-nama"></div>
                                    <small class="text-muted">NISN: <strong id="sm-nisn"></strong></small>
                                    &nbsp;<span id="sm-status" class="badge badge-info"></span>
                                </div>
                                <div class="ml-auto">
                                    <i class="fas fa-check-circle fa-2x text-success"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ── STEP 2 KELUAR: Sekolah Tujuan ───────────────────────────────── --}}
    <div id="step-2-keluar" class="wz-pane d-none">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="wz-card wz-card-danger">
                    <div class="wz-card-header wz-header-danger">
                        <div class="d-flex align-items-center">
                            <div class="wz-header-icon mr-3"><i class="fas fa-school"></i></div>
                            <div>
                                <h5 class="mb-0 font-weight-bold">Sekolah Tujuan</h5>
                                <small class="opacity-75">Ke mana siswa ini akan pindah?</small>
                            </div>
                        </div>
                    </div>
                    <div class="wz-card-body">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="wz-label">NPSN Sekolah Tujuan</label>
                                    <div class="input-group">
                                        <input type="text" id="npsn_sekolah_tujuan" name="npsn_sekolah_tujuan" class="form-control" maxlength="8"
                                            placeholder="8 digit NPSN" value="{{ old('npsn_sekolah_tujuan') }}" autocomplete="off">
                                        <div class="input-group-append">
                                            <span class="input-group-text" id="npsn-tujuan-spinner" style="display:none;">
                                                <i class="fas fa-spinner fa-spin text-primary"></i>
                                            </span>
                                            <span class="input-group-text" id="npsn-tujuan-ok" style="display:none;">
                                                <i class="fas fa-check-circle text-success"></i>
                                            </span>
                                        </div>
                                    </div>
                                    <small class="text-muted d-block mt-1"><i class="fas fa-magic mr-1"></i>Isi 8 digit untuk auto-isi nama &amp; kota</small>
                                </div>
                            </div>
                            <div class="col-md-8">
                                <div class="form-group">
                                    <label class="wz-label">Nama Sekolah Tujuan <span class="text-danger">*</span></label>
                                    <input type="text" name="sekolah_tujuan" id="sekolah_tujuan" class="form-control"
                                        placeholder="Nama lengkap sekolah tujuan" value="{{ old('sekolah_tujuan') }}">
                                    <div class="invalid-feedback">Nama sekolah tujuan wajib diisi</div>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="wz-label">Kota / Alamat Sekolah Tujuan</label>
                            <input type="text" id="alamat_sekolah_tujuan" name="alamat_sekolah_tujuan" class="form-control"
                                placeholder="Kota/Kabupaten" value="{{ old('alamat_sekolah_tujuan') }}">
                        </div>
                        <div class="form-group mb-0">
                            <label class="wz-label">Alasan Mutasi Keluar <small class="text-muted font-weight-normal">(opsional)</small></label>
                            <textarea name="alasan_mutasi_keluar" class="form-control" rows="2"
                                placeholder="Alasan pindah...">{{ old('alasan_mutasi_keluar') }}</textarea>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ── STEP 3: Dokumen & Ringkasan ─────────────────────────────────── --}}
    <div id="step-3" class="wz-pane d-none">
        <div class="row justify-content-center">
            <div class="col-md-8">
                {{-- Ringkasan --}}
                <div class="wz-summary-card mb-3">
                    <div class="wz-summary-header">
                        <i class="fas fa-clipboard-check mr-2"></i>Ringkasan Data
                    </div>
                    <div class="wz-summary-body">
                        <div class="wz-summary-row">
                            <span class="wz-summary-key">Jenis Mutasi</span>
                            <span id="sum-jenis">—</span>
                        </div>
                        <div class="wz-summary-row">
                            <span class="wz-summary-key">Siswa</span>
                            <span id="sum-siswa" class="font-weight-bold">—</span>
                        </div>
                        <div class="wz-summary-row">
                            <span class="wz-summary-key" id="sum-sekolah-lbl">Sekolah</span>
                            <span id="sum-sekolah">—</span>
                        </div>
                    </div>
                </div>

                {{-- Waktu + Dokumen --}}
                <div class="wz-card">
                    <div class="wz-card-header" style="background: linear-gradient(135deg,#28a745,#20c997); color:#fff; border-left:4px solid #1e7e34;">
                        <div class="d-flex align-items-center">
                            <div class="wz-header-icon mr-3"><i class="fas fa-calendar-check"></i></div>
                            <div>
                                <h5 class="mb-0 font-weight-bold">Waktu &amp; Dokumen</h5>
                                <small class="opacity-75">Lengkapi data waktu dan upload surat</small>
                            </div>
                        </div>
                    </div>
                    <div class="wz-card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="wz-label">Tahun Pelajaran <span class="text-danger">*</span></label>
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
                                    <label class="wz-label">Tanggal Mutasi <span class="text-danger">*</span></label>
                                    <input type="date" name="tanggal_mutasi" id="tanggal_mutasi"
                                        class="form-control" value="{{ old('tanggal_mutasi', date('Y-m-d')) }}">
                                </div>
                            </div>
                        </div>
                        <hr class="my-3">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="wz-label">Nomor Surat <small class="text-muted font-weight-normal">(opsional)</small></label>
                                    <input type="text" name="nomor_surat_mutasi" class="form-control"
                                        placeholder="No. surat..." value="{{ old('nomor_surat_mutasi') }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="wz-label">File Surat PDF <small class="text-muted font-weight-normal">(maks 5MB)</small></label>
                                    <div class="custom-file">
                                        <input type="file" class="custom-file-input" id="file_surat_mutasi"
                                            name="file_surat_mutasi" accept=".pdf">
                                        <label class="custom-file-label" for="file_surat_mutasi">Pilih file PDF...</label>
                                    </div>
                                    <div id="file-preview" class="mt-2 d-none">
                                        <span class="badge badge-success py-2 px-3">
                                            <i class="fas fa-file-pdf mr-1"></i><span id="file-name"></span>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="form-group mb-0">
                            <label class="wz-label">Catatan <small class="text-muted font-weight-normal">(opsional)</small></label>
                            <textarea name="catatan" class="form-control" rows="3"
                                placeholder="Catatan tambahan...">{{ old('catatan') }}</textarea>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ── Navigasi ─────────────────────────────────────────────────────── --}}
    <div id="wizard-nav" class="row justify-content-center mt-3 d-none">
        <div class="col-md-8">
            <div class="d-flex justify-content-between align-items-center">
                <button type="button" id="btn-prev" class="btn btn-outline-secondary">
                    <i class="fas fa-chevron-left mr-1"></i><span id="btn-prev-lbl">Kembali</span>
                </button>
                <div class="d-flex align-items-center">
                    <a href="{{ route('admin.mutasi-siswa.index') }}" class="btn btn-link text-muted mr-3">Batal</a>
                    <button type="button" id="btn-next" class="btn btn-primary px-4">
                        Selanjutnya <i class="fas fa-chevron-right ml-1"></i>
                    </button>
                    <button type="submit" id="btn-submit" class="btn btn-success px-4 d-none">
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
/* ── Wizard Cards ─────────────────────────────────── */
.wz-card {
    background: #fff;
    border-radius: 12px;
    box-shadow: 0 2px 12px rgba(0,0,0,.07);
    overflow: hidden;
    border: 1px solid #e9ecef;
}
.wz-card-header {
    padding: 18px 24px;
    color: #fff;
}
.wz-card-header.wz-header-primary { background: linear-gradient(135deg,#007bff,#0056d6); border-left: 4px solid #004ab5; }
.wz-card-header.wz-header-info    { background: linear-gradient(135deg,#17a2b8,#117a8b); border-left: 4px solid #0f6674; }
.wz-card-header.wz-header-danger  { background: linear-gradient(135deg,#dc3545,#c0392b); border-left: 4px solid #a93226; }
.wz-header-icon {
    width: 42px; height: 42px;
    background: rgba(255,255,255,.2);
    border-radius: 10px;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.2rem; flex-shrink: 0;
}
.wz-card-body { padding: 24px; }
.wz-label { font-weight: 600; font-size: .83rem; color: #495057; margin-bottom: 5px; }

/* Step 0 welcome card */
.wz-card-icon-top {
    width: 72px; height: 72px;
    background: linear-gradient(135deg, #007bff, #6610f2);
    border-radius: 20px;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.8rem; color: #fff;
    margin: 0 auto;
    box-shadow: 0 8px 20px rgba(0,123,255,.35);
}

/* ── Jenis Cards ──────────────────────────────────── */
.jcard {
    border: 2px solid #e9ecef;
    border-radius: 14px;
    padding: 28px 16px 20px;
    cursor: pointer;
    transition: all .25s ease;
    position: relative;
    background: #fff;
}
.jcard:hover { border-color: #adb5bd; transform: translateY(-4px); box-shadow: 0 10px 28px rgba(0,0,0,.10); }
.jcard.sel-masuk  { border-color: #17a2b8; background: linear-gradient(160deg,#e8f7fb 0%,#fff 70%); transform: translateY(-4px); box-shadow: 0 10px 28px rgba(23,162,184,.18); }
.jcard.sel-keluar { border-color: #dc3545; background: linear-gradient(160deg,#fdf0f1 0%,#fff 70%); transform: translateY(-4px); box-shadow: 0 10px 28px rgba(220,53,69,.18); }
.jcard-circle {
    width: 72px; height: 72px; border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.7rem; color: #fff; margin: 0 auto;
    transition: transform .25s;
}
.jcard-circle-info   { background: linear-gradient(135deg,#17a2b8,#0f6674); box-shadow: 0 6px 16px rgba(23,162,184,.4); }
.jcard-circle-danger { background: linear-gradient(135deg,#dc3545,#a93226); box-shadow: 0 6px 16px rgba(220,53,69,.4); }
.jcard:hover .jcard-circle, .jcard.sel-masuk .jcard-circle, .jcard.sel-keluar .jcard-circle { transform: scale(1.08) rotate(-4deg); }
.jcard-check { position: absolute; top: 10px; right: 12px; transition: all .2s; }

/* ── Progress Indicator ──────────────────────────── */
.wz-progress-wrap {
    background: #fff;
    border-radius: 12px;
    padding: 16px 28px;
    box-shadow: 0 2px 10px rgba(0,0,0,.06);
}
.wz-steps {
    display: flex; align-items: center;
}
.wz-step-item { text-align: center; flex-shrink: 0; }
.wz-step-num {
    width: 40px; height: 40px; border-radius: 50%;
    background: #e9ecef; color: #adb5bd;
    display: flex; align-items: center; justify-content: center;
    font-weight: 700; font-size: .875rem; margin: 0 auto 6px;
    transition: all .35s ease; border: 2px solid #dee2e6;
}
.wz-step-item.wz-active .wz-step-num  { background: #007bff; color: #fff; border-color: #007bff; box-shadow: 0 0 0 5px rgba(0,123,255,.15); }
.wz-step-item.wz-done   .wz-step-num  { background: #28a745; color: #fff; border-color: #28a745; }
.wz-step-lbl { font-size: .72rem; color: #adb5bd; white-space: nowrap; transition: color .3s; }
.wz-step-item.wz-active .wz-step-lbl { color: #007bff; font-weight: 600; }
.wz-step-item.wz-done   .wz-step-lbl { color: #28a745; }
.wz-connector { flex-grow: 1; height: 3px; background: #e9ecef; margin: 0 8px 24px; border-radius: 2px; transition: background .4s; }
.wz-connector.wz-done { background: #28a745; }

/* ── Siswa Found Card ─────────────────────────────── */
.siswa-found-card {
    background: linear-gradient(135deg,#f0fff4,#e6ffed);
    border: 1.5px solid #b7dfc0;
    border-radius: 10px;
    padding: 14px 16px;
    margin-top: 10px;
}
.siswa-avatar {
    width: 52px; height: 52px; border-radius: 50%;
    background: linear-gradient(135deg,#007bff,#6610f2);
    display: flex; align-items: center; justify-content: center;
    color: #fff; font-size: 1.3rem; flex-shrink: 0;
}

/* ── Summary Card ─────────────────────────────────── */
.wz-summary-card {
    background: linear-gradient(135deg,#f8f9fc,#fff);
    border: 1px solid #d0d7e2;
    border-radius: 12px; overflow: hidden;
}
.wz-summary-header {
    background: linear-gradient(135deg,#495057,#343a40);
    color: #fff; padding: 12px 20px;
    font-size: .875rem; font-weight: 600;
}
.wz-summary-body { padding: 4px 0; }
.wz-summary-row { display: flex; align-items: center; padding: 10px 20px; border-bottom: 1px solid #f0f2f5; }
.wz-summary-row:last-child { border-bottom: none; }
.wz-summary-key { width: 36%; color: #6c757d; font-size: .82rem; flex-shrink: 0; }

/* ── Wizard Transitions ─────────────────────────── */
.wz-pane { }
.wz-pane.d-none { display: none !important; }
@keyframes wzIn  { from { opacity:0; transform:translateX(24px); } to { opacity:1; transform:none; } }
@keyframes wzOut { from { opacity:0; transform:translateX(-24px); } to { opacity:1; transform:none; } }
.wz-anim-fwd  { animation: wzIn  .28s ease forwards; }
.wz-anim-back { animation: wzOut .28s ease forwards; }

/* ── Select2 overrides ─────────────────────────── */
.select2-container--default .select2-selection--single {
    height: 38px; border: 1px solid #ced4da; border-radius: .25rem;
    padding: 5px 12px; font-size: .875rem;
}
.select2-container--default .select2-selection--single .select2-selection__rendered { line-height: 26px; color: #495057; }
.select2-container--default .select2-selection--single .select2-selection__arrow { height: 36px; }
.select2-container--default.select2-container--open .select2-selection--single { border-color: #80bdff; box-shadow: 0 0 0 .2rem rgba(0,123,255,.25); }
.select2-results__option { padding: 8px 12px; }
.select2-container--default .select2-results__option--highlighted[aria-selected] { background: #007bff; }

/* ── Misc ──────────────────────────────────────── */
.opacity-75 { opacity: .75; }
</style>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css">
@endsection

@section('js')
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.full.min.js"></script>
<script>
(function () {
    var step      = 0;
    var jenis     = null;
    var siswaData = null;
    var STEPS     = 3;
    var s2Inited  = {};
    var stepLabels = {
        masuk:  ['Sekolah Asal', 'Data Siswa',     'Dokumen'],
        keluar: ['Cari Siswa',   'Sekolah Tujuan', 'Dokumen'],
    };

    function paneId(s) {
        if (s === 0) return 'step-0';
        if (s === 3) return 'step-3';
        return 'step-' + s + '-' + jenis;
    }

    function showStep(n, dir) {
        document.querySelectorAll('.wz-pane').forEach(function (el) {
            el.classList.add('d-none');
            el.classList.remove('wz-anim-fwd', 'wz-anim-back');
        });
        var el = document.getElementById(paneId(n));
        if (!el) return;
        el.classList.remove('d-none');
        void el.offsetWidth; // reflow to restart animation
        el.classList.add(dir === 'back' ? 'wz-anim-back' : 'wz-anim-fwd');
        step = n;

        var header = document.getElementById('wizard-header');
        var nav    = document.getElementById('wizard-nav');
        if (n === 0) {
            header.classList.add('d-none');
            nav.classList.add('d-none');
        } else {
            header.classList.remove('d-none');
            nav.classList.remove('d-none');
        }

        document.getElementById('btn-prev-lbl').textContent = (n === 1) ? 'Ubah Jenis' : 'Kembali';

        var btnN = document.getElementById('btn-next');
        var btnS = document.getElementById('btn-submit');
        if (n === STEPS) {
            btnN.classList.add('d-none');
            btnS.classList.remove('d-none');
            updateSummary();
        } else {
            btnN.classList.remove('d-none');
            btnS.classList.add('d-none');
        }

        updateIndicator(n);

        // Init Select2 setelah animasi selesai (animasi = 280ms, tunggu 350ms)
        if (n === 1 && jenis === 'keluar') {
            setTimeout(function () { initS2('keluar'); }, 350);
        }
        if (n === 2 && jenis === 'masuk') {
            setTimeout(function () { initS2('masuk'); }, 350);
        }
    }

    function updateIndicator(s) {
        if (!jenis) return;
        var lbl = stepLabels[jenis];
        [1, 2, 3].forEach(function (i) {
            var item = document.getElementById('wsi-' + i);
            var num  = item.querySelector('.wz-step-num');
            var con  = document.getElementById('wsc-' + i);
            document.getElementById('wsl-' + i).textContent = lbl[i - 1];
            item.classList.remove('wz-active', 'wz-done');
            if (con) con.classList.remove('wz-done');
            if (i < s) {
                item.classList.add('wz-done');
                num.innerHTML = '<i class="fas fa-check" style="font-size:.6rem"></i>';
                if (con) con.classList.add('wz-done');
            } else if (i === s) {
                item.classList.add('wz-active');
                num.textContent = i;
            } else {
                num.textContent = i;
            }
        });
    }

    window.selectJenis = function (j) {
        jenis = j;
        document.getElementById('final_jenis').value = j;
        ['masuk', 'keluar'].forEach(function (x) {
            document.getElementById('jcard-' + x).classList.remove('sel-masuk', 'sel-keluar');
            document.getElementById('jcard-' + x).querySelector('.jcard-check').classList.add('d-none');
        });
        document.getElementById('jcard-' + j).classList.add('sel-' + j);
        document.getElementById('jcard-' + j).querySelector('.jcard-check').classList.remove('d-none');
        setTimeout(function () { showStep(1, 'fwd'); }, 260);
    };

    document.getElementById('btn-next').addEventListener('click', function () {
        if (validate(step)) showStep(step + 1, 'fwd');
    });
    document.getElementById('btn-prev').addEventListener('click', function () {
        if (step === 1) {
            showStep(0, 'back');
        } else {
            showStep(step - 1, 'back');
        }
    });

    function validate(s) {
        if (s === 1 && jenis === 'masuk') {
            var f = document.getElementById('sekolah_asal');
            if (!f.value.trim()) {
                f.classList.add('is-invalid'); f.focus(); return false;
            }
            f.classList.remove('is-invalid');
        }
        if (s === 1 && jenis === 'keluar') {
            if (!document.getElementById('final_siswa_id').value) {
                showToast('warning', 'Pilih siswa terlebih dahulu'); return false;
            }
        }
        if (s === 2 && jenis === 'masuk') {
            if (!document.getElementById('final_siswa_id').value) {
                showToast('warning', 'Pilih siswa terlebih dahulu'); return false;
            }
        }
        if (s === 2 && jenis === 'keluar') {
            var f = document.getElementById('sekolah_tujuan');
            if (!f.value.trim()) {
                f.classList.add('is-invalid'); f.focus(); return false;
            }
            f.classList.remove('is-invalid');
        }
        return true;
    }

    function showToast(icon, msg) {
        if (typeof Swal !== 'undefined') {
            Swal.fire({ icon: icon, title: msg, toast: true, position: 'top-end',
                showConfirmButton: false, timer: 2500, timerProgressBar: true });
        } else { alert(msg); }
    }

    // ── Select2 AJAX ─────────────────────────────────────────────────────
    function initS2(j) {
        if (s2Inited[j]) return;
        s2Inited[j] = true;
        var elId   = j === 'keluar' ? '#siswa_id_keluar' : '#siswa_id_masuk';
        var cardId = j === 'keluar' ? 'siswa-info-keluar' : 'siswa-info-masuk';
        var pfx    = j === 'keluar' ? 'sk' : 'sm';

        // Destroy any existing instance first
        if ($(elId).hasClass('select2-hidden-accessible')) {
            $(elId).select2('destroy');
        }

        $(elId).select2({
            placeholder: 'Ketik nama atau NISN siswa...',
            minimumInputLength: 2,
            allowClear: true,
            width: '100%',
            dropdownParent: $('body'),
            language: {
                inputTooShort: function () { return 'Ketik minimal 2 karakter...'; },
                searching:     function () { return 'Mencari...'; },
                noResults:     function () { return 'Siswa tidak ditemukan'; },
            },
            ajax: {
                url: '{{ route("admin.mutasi-siswa.search-siswa") }}',
                dataType: 'json',
                delay: 350,
                data: function (p) { return { q: p.term }; },
                processResults: function (data) {
                    return {
                        results: data.map(function (s) {
                            return { id: s.id, text: s.nama_lengkap, nisn: s.nisn || '-', status: s.status_siswa || '' };
                        })
                    };
                },
                cache: true,
            },
            templateResult: function (s) {
                if (s.loading) { return $('<span><i class="fas fa-spinner fa-spin mr-2 text-muted"></i>' + s.text + '</span>'); }
                return $('<div style="padding:6px 2px">'
                    + '<strong style="font-size:.875rem">' + s.text + '</strong>'
                    + '<div style="font-size:.78rem;color:#6c757d;margin-top:2px">'
                    + '<i class="fas fa-id-card mr-1"></i>NISN: ' + s.nisn
                    + '&nbsp;&bull;&nbsp;' + s.status + '</div></div>');
            },
            templateSelection: function (s) { return s.text || s.id; },
        });

        $(elId).on('select2:select', function (e) {
            var d = e.params.data;
            siswaData = d;
            document.getElementById('final_siswa_id').value = d.id;
            document.getElementById(pfx + '-nama').textContent   = d.text;
            document.getElementById(pfx + '-nisn').textContent   = d.nisn   || '-';
            document.getElementById(pfx + '-status').textContent = d.status || '-';
            document.getElementById(cardId).classList.remove('d-none');
        }).on('select2:unselect', function () {
            siswaData = null;
            document.getElementById('final_siswa_id').value = '';
            document.getElementById(cardId).classList.add('d-none');
        });

        @if($selectedSiswa)
        if (j === 'keluar') {
            var opt = new Option('{{ addslashes($selectedSiswa->nama_lengkap) }}', '{{ $selectedSiswa->id }}', true, true);
            $(elId).append(opt).trigger('change');
            siswaData = { text: '{{ addslashes($selectedSiswa->nama_lengkap) }}', nisn: '{{ $selectedSiswa->nisn ?? "" }}', status: '{{ $selectedSiswa->status_siswa ?? "" }}' };
            document.getElementById('final_siswa_id').value = '{{ $selectedSiswa->id }}';
            document.getElementById(pfx + '-nama').textContent   = siswaData.text;
            document.getElementById(pfx + '-nisn').textContent   = siswaData.nisn   || '-';
            document.getElementById(pfx + '-status').textContent = siswaData.status || '-';
            document.getElementById(cardId).classList.remove('d-none');
        }
        @endif
    }

    // ── Summary ───────────────────────────────────────────────────────────
    function updateSummary() {
        document.getElementById('sum-jenis').innerHTML = jenis === 'masuk'
            ? '<span class="badge badge-info px-2 py-1"><i class="fas fa-sign-in-alt mr-1"></i>Mutasi Masuk</span>'
            : '<span class="badge badge-danger px-2 py-1"><i class="fas fa-sign-out-alt mr-1"></i>Mutasi Keluar</span>';
        document.getElementById('sum-siswa').textContent = siswaData ? siswaData.text : '—';
        if (jenis === 'masuk') {
            document.getElementById('sum-sekolah-lbl').textContent = 'Sekolah Asal';
            document.getElementById('sum-sekolah').textContent = (document.querySelector('[name=sekolah_asal]') || {}).value || '—';
        } else {
            document.getElementById('sum-sekolah-lbl').textContent = 'Sekolah Tujuan';
            document.getElementById('sum-sekolah').textContent = (document.querySelector('[name=sekolah_tujuan]') || {}).value || '—';
        }
    }

    // ── File preview ──────────────────────────────────────────────────────
    document.getElementById('file_surat_mutasi').addEventListener('change', function () {
        var file = this.files[0];
        if (file) {
            this.nextElementSibling.textContent = file.name;
            document.getElementById('file-name').textContent = file.name + ' (' + (file.size / 1024).toFixed(1) + ' KB)';
            document.getElementById('file-preview').classList.remove('d-none');
        }
    });

    // ── Clear invalid on input ────────────────────────────────────────────
    document.querySelectorAll('.form-control').forEach(function (el) {
        el.addEventListener('input', function () { this.classList.remove('is-invalid'); });
    });

    // ── NPSN Autocomplete ─────────────────────────────────────────────────
    function setupNpsnAutoComplete(npsnId, namaId, kotaId, spinnerId, okId) {
        var timer;
        document.getElementById(npsnId).addEventListener('input', function () {
            var val = this.value.replace(/\D/g, '').slice(0, 8);
            this.value = val;
            document.getElementById(spinnerId).style.display = 'none';
            document.getElementById(okId).style.display = 'none';
            clearTimeout(timer);
            if (val.length !== 8) return;
            document.getElementById(spinnerId).style.display = '';
            timer = setTimeout(function () {
                fetch('{{ route("admin.mutasi-siswa.lookup-npsn") }}?npsn=' + val, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
                })
                .then(function (r) { return r.json(); })
                .then(function (d) {
                    document.getElementById(spinnerId).style.display = 'none';
                    if (d.success) {
                        document.getElementById(namaId).value = d.nama;
                        if (kotaId && d.kota) document.getElementById(kotaId).value = d.kota;
                        document.getElementById(okId).style.display = '';
                        document.getElementById(namaId).classList.remove('is-invalid');
                    } else {
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({ icon: 'warning', title: d.message || 'NPSN tidak ditemukan', toast: true,
                                position: 'top-end', showConfirmButton: false, timer: 2500, timerProgressBar: true });
                        }
                    }
                })
                .catch(function () {
                    document.getElementById(spinnerId).style.display = 'none';
                });
            }, 500);
        });
    }
    setupNpsnAutoComplete('npsn_sekolah_asal',   'sekolah_asal',   'alamat_sekolah_asal',   'npsn-asal-spinner',   'npsn-asal-ok');
    setupNpsnAutoComplete('npsn_sekolah_tujuan',  'sekolah_tujuan', 'alamat_sekolah_tujuan', 'npsn-tujuan-spinner', 'npsn-tujuan-ok');

    // ── Recover from validation error (old()) ────────────────────────────
    @if(old('jenis_mutasi'))
    selectJenis('{{ old("jenis_mutasi") }}');
    setTimeout(function () { showStep(3, 'fwd'); }, 100);
    @if($selectedSiswa)
    siswaData = { text: '{{ addslashes($selectedSiswa->nama_lengkap) }}', nisn: '', status: '' };
    document.getElementById('final_siswa_id').value = '{{ $selectedSiswa->id }}';
    @endif
    @endif

}());
</script>
@endsection