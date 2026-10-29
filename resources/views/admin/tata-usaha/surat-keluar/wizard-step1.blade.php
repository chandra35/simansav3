@extends('adminlte::page')

@section('title', 'Buat Surat Keluar - Langkah 1: Pilih Template')

@section('content')
<div class="container-fluid surat-keluar-wizard">
    <div class="surat-wizard-heading d-flex flex-column flex-md-row align-items-md-center justify-content-between">
        <div class="mb-3 mb-md-0">
            <h1 class="m-0 text-dark"><i class="fas fa-paper-plane text-primary mr-2"></i>Surat Keluar</h1>
            <p class="text-muted mb-0 mt-1">Langkah 1 dari 3 — pilih format surat yang akan dibuat.</p>
        </div>
        @can('manage-layanan-surat')
            <a href="{{ route('admin.surat-keterangan.template.create') }}" class="btn btn-outline-primary align-self-start align-self-md-center">
                <i class="fas fa-plus mr-1"></i> Tambah Template
            </a>
        @endcan
    </div>
    <div class="wizard-steps" aria-label="Tahapan pembuatan surat">
        <div class="wizard-step active"><span>1</span><strong>Pilih Template</strong></div>
        <div class="wizard-line"></div>
        <div class="wizard-step"><span>2</span><strong>Isi Data Surat</strong></div>
        <div class="wizard-line"></div>
        <div class="wizard-step"><span>3</span><strong>Periksa & Simpan</strong></div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card card-outline card-primary shadow-sm">
                <div class="card-header border-0 pt-4 px-4">
                    <h2 class="h5 font-weight-bold mb-1">Pilih template surat</h2>
                    <p class="text-muted small mb-0">Template menentukan klasifikasi dan isi awal surat. Isi surat dapat disesuaikan pada langkah berikutnya.</p>
                </div>
                <div class="card-body px-4 pb-4">
                    <form method="GET" action="{{ route('admin.tata-usaha.surat-keluar.create') }}">
                        <input type="hidden" name="step" value="2">
                        <div class="row">
                            @forelse($templates as $template)
                                <div class="col-md-6 mb-3">
                                    <label class="template-option mb-0 w-100">
                                        <input type="radio" name="template_id" value="{{ $template->id }}" required>
                                        <span class="template-option-body">
                                            <span class="template-icon"><i class="far fa-file-alt"></i></span>
                                            <span class="template-copy">
                                                <strong>{{ $template->nama }}</strong>
                                                <small>{{ $template->jenis_label }}</small>
                                                <span class="template-code">{{ $template->kode_klasifikasi }}</span>
                                            </span>
                                            <span class="template-check"><i class="fas fa-check"></i></span>
                                        </span>
                                    </label>
                                </div>
                            @empty
                                <div class="col-12">
                                    <div class="empty-state text-center py-4">
                                        <i class="far fa-folder-open fa-2x text-muted mb-3"></i>
                                        <h3 class="h6 font-weight-bold">Belum ada template aktif</h3>
                                        <p class="text-muted small mb-3">Buat dan aktifkan template terlebih dahulu sebelum membuat surat keluar.</p>
                                        @can('manage-layanan-surat')
                                            <a href="{{ route('admin.surat-keterangan.template.create') }}" class="btn btn-primary btn-sm"><i class="fas fa-plus mr-1"></i> Buat Template</a>
                                        @endcan
                                    </div>
                                </div>
                            @endforelse
                        </div>

                        <div class="d-flex align-items-center justify-content-between border-top pt-3 mt-2">
                            <a href="{{ route('admin.tata-usaha.surat-keluar.index') }}" class="btn btn-light border">
                                <i class="fas fa-arrow-left mr-1"></i> Kembali
                            </a>
                            @if($templates->isNotEmpty())
                                <button type="submit" class="btn btn-primary px-4">
                                    Lanjutkan <i class="fas fa-arrow-right ml-1"></i>
                                </button>
                            @endif
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-lg-4 mt-4 mt-lg-0">
            <div class="card shadow-sm border-0 info-card">
                <div class="card-body p-4">
                    <div class="info-icon mb-3"><i class="fas fa-info"></i></div>
                    <h2 class="h6 font-weight-bold">Tentang surat keluar</h2>
                    <p class="small text-muted">Nomor surat tidak diambil saat masih draft. Nomor resmi diterbitkan ketika surat disimpan dengan status <strong>Menunggu TTD</strong>.</p>
                    <hr>
                    <div class="d-flex align-items-start mb-3"><i class="fas fa-check-circle text-success mt-1 mr-2"></i><small>Pilih template sesuai jenis surat.</small></div>
                    <div class="d-flex align-items-start mb-3"><i class="fas fa-check-circle text-success mt-1 mr-2"></i><small>Lengkapi tujuan, perihal, dan penandatangan.</small></div>
                    <div class="d-flex align-items-start"><i class="fas fa-check-circle text-success mt-1 mr-2"></i><small>Isi surat dapat diperiksa sebelum disimpan.</small></div>
                </div>
            </div>
            <div class="alert alert-light border small mt-3 mb-0">
                <i class="fas fa-lightbulb text-warning mr-1"></i> Template dikelola melalui menu <strong>Template Surat</strong> di Tata Usaha.
            </div>
        </div>
    </div>
</div>

@section('css')
<style>
    .surat-wizard-heading { gap: 1rem; min-height: 56px; margin: .25rem 0 1.5rem; padding: .25rem 0; }
    .surat-wizard-heading h1 { font-size: 1.35rem; font-weight: 600; line-height: 1.3; }
    .surat-wizard-heading p { font-size: .875rem; }
    .wizard-steps { display: flex; align-items: center; max-width: 720px; margin: .25rem 0 1.5rem; padding: .15rem 0; }
    .wizard-step { display: flex; align-items: center; gap: .5rem; color: #9aa4b2; font-size: .82rem; white-space: nowrap; }
    .wizard-step span { width: 28px; height: 28px; display: inline-flex; justify-content: center; align-items: center; border-radius: 50%; background: #e9ecef; font-weight: 700; }
    .wizard-step.active { color: #3158d4; }
    .wizard-step.active span { background: #3158d4; color: #fff; }
    .wizard-line { height: 2px; flex: 1; background: #e9ecef; margin: 0 .8rem; }
    .template-option { cursor: pointer; }
    .template-option input { position: absolute; opacity: 0; }
    .template-option-body { min-height: 122px; display: flex; align-items: flex-start; position: relative; padding: 1.15rem; border: 1px solid #e4e8ef; border-radius: .65rem; background: #fff; transition: all .18s ease; }
    .template-option:hover .template-option-body { border-color: #8ba5ff; box-shadow: 0 5px 16px rgba(49, 88, 212, .10); transform: translateY(-1px); }
    .template-option input:checked + .template-option-body { border: 2px solid #3158d4; background: #f5f7ff; padding: calc(1.15rem - 1px); }
    .template-icon { width: 38px; height: 38px; display: inline-flex; align-items: center; justify-content: center; color: #3158d4; background: #eaf0ff; border-radius: .55rem; margin-right: .8rem; }
    .template-copy { display: flex; flex-direction: column; padding-right: 1.2rem; }
    .template-copy strong { font-size: .92rem; color: #26354b; line-height: 1.35; }
    .template-copy small { color: #788598; margin-top: .2rem; }
    .template-code { color: #3158d4; background: #eaf0ff; border-radius: .25rem; font-size: .7rem; font-weight: 700; padding: .15rem .4rem; margin-top: .6rem; width: fit-content; }
    .template-check { display: none; position: absolute; right: .85rem; top: .85rem; color: #3158d4; }
    .template-option input:checked + .template-option-body .template-check { display: inline; }
    .info-card { border-radius: .65rem; }
    .info-icon { width: 38px; height: 38px; display: inline-flex; align-items: center; justify-content: center; border-radius: .5rem; color: #3158d4; background: #eaf0ff; }
    .empty-state { border: 1px dashed #cdd5df; border-radius: .65rem; background: #fafbfd; }
    @media (max-width: 575.98px) {
        .surat-wizard-heading h1 { font-size: 1.2rem; }
        .wizard-step strong { display: none; }
        .wizard-line { margin: 0 .45rem; }
    }
</style>
@stop
