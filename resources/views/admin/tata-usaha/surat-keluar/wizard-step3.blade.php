@extends('adminlte::page')

@section('title', 'Buat Surat Keluar - Langkah 3: Konten Surat')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-12">
            <h1 class="h3 mb-0">
                <i class="fas fa-magic"></i> Wizard Surat Keluar
                <small class="text-muted">Langkah 3: Konten Surat</small>
            </h1>
        </div>
    </div>

    <form method="POST" action="{{ route('admin.tata-usaha.surat-keluar.store') }}">
        @csrf

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

        <div class="row">
            <div class="col-md-9">
                <div class="card mb-3">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">Konten Surat</h5>
                    </div>
                    <div class="card-body">
                        <input type="hidden" name="template_surat_id" value="{{ $selectedTemplate->id }}">
                        <input type="hidden" name="tanggal_surat" value="{{ old('tanggal_surat', $stepData['tanggal_surat'] ?? '') }}">
                        <input type="hidden" name="perihal" value="{{ old('perihal', $stepData['perihal'] ?? '') }}">
                        <input type="hidden" name="tujuan" value="{{ old('tujuan', $stepData['tujuan'] ?? '') }}">
                        <input type="hidden" name="penandatangan_id" value="{{ old('penandatangan_id', $stepData['penandatangan_id'] ?? '') }}">
                        <input type="hidden" name="nama_penandatangan" value="{{ old('nama_penandatangan', $stepData['nama_penandatangan'] ?? '') }}">
                        <input type="hidden" name="jabatan_penandatangan" value="{{ old('jabatan_penandatangan', $stepData['jabatan_penandatangan'] ?? 'Kepala Madrasah') }}">

                        <div class="form-group">
                            <label for="konten_generate">Konten Surat <span class="text-danger">*</span></label>
                            <textarea name="konten_generate" id="konten_generate" class="form-control @error('konten_generate') is-invalid @enderror" rows="15" required>{{ old('konten_generate', $selectedTemplate->konten ?? '') }}</textarea>
                            @error('konten_generate')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">Anda dapat mengedit konten surat sesuai kebutuhan.</small>
                        </div>

                        <div class="form-group">
                            <label for="status">Status Surat <span class="text-danger">*</span></label>
                            <select name="status" id="status" class="form-control @error('status') is-invalid @enderror" required>
                                <option value="draft" selected>Draft (Simpan untuk nanti)</option>
                                <option value="menunggu_ttd">Menunggu TTD (Siap untuk ditandatangani)</option>
                            </select>
                            @error('status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <a href="{{ route('admin.tata-usaha.surat-keluar.create', ['step' => 2, 'template_id' => $selectedTemplate->id]) }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Kembali
                    </a>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-save"></i> Simpan Surat Keluar
                    </button>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card bg-light mb-3">
                    <div class="card-header">
                        <h5 class="mb-0">Variabel Template</h5>
                    </div>
                    <div class="card-body small">
                        <p>Gunakan variabel ini dalam konten:</p>
                        <ul class="list-unstyled">
                            <li><code>{{'{{'}}nama_pejabat{{'}}'}}</code></li>
                            <li><code>{{'{{'}}nip_pejabat{{'}}'}}</code></li>
                            <li><code>{{'{{'}}jabatan_pejabat{{'}}'}}</code></li>
                            <li><code>{{'{{'}}nama_sekolah{{'}}'}}</code></li>
                            <li><code>{{'{{'}}nomor_surat{{'}}'}}</code></li>
                            <li><code>{{'{{'}}tanggal_surat{{'}}'}}</code></li>
                        </ul>
                    </div>
                </div>

                <div class="card bg-info text-white">
                    <div class="card-header">
                        <h5 class="mb-0">Informasi</h5>
                    </div>
                    <div class="card-body small">
                        <p><strong>Template:</strong> {{ $selectedTemplate->nama }}</p>
                        <p><strong>Jenis:</strong> {{ $selectedTemplate->jenis_label }}</p>
                        <p><strong>Kode Klasifikasi:</strong> {{ $selectedTemplate->kode_klasifikasi }}</p>
                        <p class="mb-0 mt-3">Nomor surat akan di-generate otomatis setelah penyimpanan.</p>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection
