@extends('adminlte::page')

@section('title', 'Edit Surat Keluar')

@section('content_header')
<div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between surat-page-header">
    <div class="mb-3 mb-md-0">
        <h1 class="m-0 text-dark"><i class="fas fa-edit text-primary mr-2"></i>Edit Surat Keluar</h1>
        <small class="text-muted">{{ $suratKeluar->nomor_surat ?: 'Draft belum memiliki nomor surat' }}</small>
    </div>
    <a href="{{ route('admin.tata-usaha.surat-keluar.show', $suratKeluar->id) }}" class="btn btn-outline-secondary align-self-start align-self-md-center">
        <i class="fas fa-arrow-left mr-1"></i> Kembali
    </a>
</div>
@stop

@section('content')
<div class="surat-keluar-form">

    <div class="row">
        <div class="col-md-9">
            <form method="POST" action="{{ route('admin.tata-usaha.surat-keluar.update', $suratKeluar->id) }}">
                @csrf
                @method('PUT')

                @if ($errors->any())
                    <div class="alert alert-danger">
                        <h6>Ada kesalahan pada form:</h6>
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="card mb-3">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">Informasi Surat</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="template_surat_id">Template <span class="text-danger">*</span></label>
                                    <select name="template_surat_id" id="template_surat_id" class="form-control @error('template_surat_id') is-invalid @enderror" required>
                                        @foreach($templates as $template)
                                            <option value="{{ $template->id }}" {{ $suratKeluar->template_surat_id === $template->id ? 'selected' : '' }}>
                                                {{ $template->nama }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('template_surat_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="tanggal_surat">Tanggal Surat <span class="text-danger">*</span></label>
                                    <input type="date" name="tanggal_surat" id="tanggal_surat" class="form-control @error('tanggal_surat') is-invalid @enderror" value="{{ $suratKeluar->tanggal_surat?->format('Y-m-d') }}" required>
                                    @error('tanggal_surat')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="perihal">Perihal <span class="text-danger">*</span></label>
                            <input type="text" name="perihal" id="perihal" class="form-control @error('perihal') is-invalid @enderror" value="{{ old('perihal', $suratKeluar->perihal) }}" required>
                            @error('perihal')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="tujuan">Tujuan (Opsional)</label>
                            <input type="text" name="tujuan" id="tujuan" class="form-control" value="{{ old('tujuan', $suratKeluar->tujuan) }}">
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="penandatangan_id">Penandatangan <span class="text-danger">*</span></label>
                                    <select name="penandatangan_id" id="penandatangan_id" class="form-control @error('penandatangan_id') is-invalid @enderror" required>
                                        <option value="">-- Pilih Penandatangan --</option>
                                        @foreach($gtks as $gtk)
                                            <option value="{{ $gtk->id }}" {{ $suratKeluar->penandatangan_id === $gtk->id ? 'selected' : '' }}>
                                                {{ $gtk->nama_lengkap }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('penandatangan_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="jabatan_penandatangan">Jabatan Penandatangan</label>
                                    <input type="text" name="jabatan_penandatangan" id="jabatan_penandatangan" class="form-control" value="{{ old('jabatan_penandatangan', $suratKeluar->jabatan_penandatangan ?? 'Kepala Madrasah') }}">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card mb-3">
                    <div class="card-header bg-secondary text-white">
                        <h5 class="mb-0">Konten Surat</h5>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label for="konten_generate">Konten Surat <span class="text-danger">*</span></label>
                            <textarea name="konten_generate" id="konten_generate" class="form-control @error('konten_generate') is-invalid @enderror" rows="15" required>{{ old('konten_generate', $suratKeluar->konten_generate) }}</textarea>
                            @error('konten_generate')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="card mb-3">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0">Status Surat</h5>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label for="status">Status <span class="text-danger">*</span></label>
                            <select name="status" id="status" class="form-control @error('status') is-invalid @enderror" required>
                                <option value="draft" {{ $suratKeluar->status === 'draft' ? 'selected' : '' }}>Draft</option>
                                <option value="menunggu_ttd" {{ $suratKeluar->status === 'menunggu_ttd' ? 'selected' : '' }}>Menunggu TTD</option>
                                <option value="selesai" {{ $suratKeluar->status === 'selesai' ? 'selected' : '' }}>Selesai</option>
                                <option value="dibatalkan" {{ $suratKeluar->status === 'dibatalkan' ? 'selected' : '' }}>Dibatalkan</option>
                            </select>
                            @error('status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <a href="{{ route('admin.tata-usaha.surat-keluar.show', $suratKeluar->id) }}" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Batal
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>

        <div class="col-md-3">
            <div class="card bg-light">
                <div class="card-header">
                    <h5 class="mb-0">Informasi</h5>
                </div>
                <div class="card-body small">
                    <p><strong>Nomor Surat:</strong> <code>{{ $suratKeluar->nomor_surat }}</code></p>
                    <p><strong>Status:</strong> <span class="badge badge-{{ $suratKeluar->status_badge }}">{{ $suratKeluar->status_label }}</span></p>
                    <p class="mb-0 text-muted">Dibuat {{ $suratKeluar->created_at?->diffForHumans() }}</p>
                </div>
            </div>
        </div>
    </div>
</div>
@stop

@section('css')
<style>
    .surat-page-header { gap: 1rem; }
    .surat-keluar-form .card { box-shadow: 0 2px 8px rgba(31, 41, 55, .07); }
    .surat-keluar-form .card-header h5 { font-size: 1rem; font-weight: 600; }
    .surat-keluar-form textarea { min-height: 260px; resize: vertical; }
    .surat-keluar-form .form-group:last-child { margin-bottom: 0; }
    @media (max-width: 767.98px) { .surat-keluar-form .btn { margin-bottom: .5rem; } }
</style>
@stop
